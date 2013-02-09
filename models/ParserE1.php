<?php
/**
 * Класс для парсинга E1.ru
 */

class ParserE1 extends ParserBasic {

    /**
     * КОНСТРУКТОР
     */
    public function __construct() {

        // Выполняем конструктор базового класса
        parent::__construct();

        // Устанавливаем режим работы парсера
        $this->mode = "e1";

        // Для e1 нумерация листов со списками объявлений начинается с 0. При первом использовании счетчик увеличит -1 до 0
        $this->advertsListNumber = -1;

        // Получим список уже ранее обработанных объявлений
        $this->readHandledAdverts();
    }

    /**
     * Получение списка уже обработанных объявлений с данного сайта
     */
    private function readHandledAdverts() {

        // Получить идентификаторы всех обработанных объявлений за срок = actualDayAmountForAdvert от текущего дня
        $finalDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $finalDate = $finalDate->format('d.m.Y');
        $initialDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $initialDate->modify('-' . $this->actualDayAmountForAdvert . ' day');
        $initialDate = $initialDate->format('d.m.Y');
        $this->handledAdverts = DBconnect::selectHandledAdverts("e1", $initialDate, $finalDate);

        // Если получить список уже обработанных объявлений с сайта bazab2b получить не удалось, то прекращаем выполнение скрипта от греха подальше
        if ($this->handledAdverts === NULL || !is_array($this->handledAdverts)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->readHandledAdverts:1 Парсинг сайта e1 остановлен, так как не удалось получить сведения о ранее загруженных объявлениях");
            DBconnect::closeConnectToDB();
            exit();
        }
    }

    /**
     * Загружает следующую страницу со списком объявлений с сайта e1.
     * При первом использовании загружает первую страницу списка объявлений.
     * Сохраняет загруженную страницу в $advertsListDOM
     * @return bool TRUE в случае успешной загрузки и FALSE в противном случае
     */
    public function loadNextAdvertsList() {

        // Говорят, что в библиотеке SimpleHTMLDOM могут наблюдаться утечки памяти, на всякий случай чистим после каждого цикла работы
        if (isset($this->advertsListDOM)) $this->advertsListDOM->clear();

        // Увеличиваем счетчик текущей страницы списка объявлений
        $this->advertsListNumber++;

        // Вычисляем URL запрашиваемой страницы
        $url = 'http://www.e1.ru/business/realty/search.php?s_obj_type=1&rq=0&op_type=2&city_id=1&region_id=0&area_all=-1&sb=8&ob=2&p=' . $this->advertsListNumber;

        // Фиксируем в логах факт загрузки новой страницы со списком объявлений
        Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadNextAdvertsList():1 Загружаем новую страницу со списком объявлений с e1, url: '" . $url . "'");

        // Неспосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, "", "", FALSE);

        // Если получить HTML страницы не удалось
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadNextAdvertsList():2 Не удалось получить страницу со списком объявлений с сайта e1 по адресу: '" . $url . "', получена страница: '" . $pageHTML . "'");
            return FALSE;
        }

        // Получаем DOM-объект и сохраняем его в параметры
        $this->advertsListDOM = str_get_html($pageHTML);

        // Получим таблицу с кратким описанием объявлений
        $this->advertsListDOM = $this->advertsListDOM->find('body table', 3)->find('tr td', 3)->find('table', 1);

        // Убедимся, что на странице есть список объявлений. Иначе мы можем бесконечно загружать 404 страницу или подобные ей.
        if ($this->advertsListDOM->find('tr', 2) === NULL) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadNextAdvertsList():3 Полученная страница со списком объявлений с сайта e1 не содержит список объявлений, по адресу: '" . $url . "'");
            return FALSE;
        }

        // Сбрасываем параметры текущего обрабатываемого краткого описания объявления на значения по умолчанию
        $this->advertShortDescriptionNumber = 1;
        $this->advertShortDescriptionDOM = NULL;
        $this->id = NULL;
        $this->advertFullDescriptionDOM = NULL;

        return TRUE;
    }

    /**
     * Достает следующее краткое описание объявления из текущего списка.
     * При первом использовании достает самое первое краткое описание объявления из текущего списка.
     * Сохраняет полученный DOM-объект в $advertShortDescriptionDOM
     * @return bool TRUE в случае успешной загрузки и FALSE в противном случае
     */
    public function getNextAdvertShortDescription() {

        $this->advertShortDescriptionNumber++;
        $currentShortAdvert = $this->advertsListDOM->find('tr', $this->advertShortDescriptionNumber);

        // Если получить DOM-модель краткого описания объявления не удалось или мы достигли подвала таблицы с объявлениями - прекращаем
        if ($currentShortAdvert === NULL || $this->advertShortDescriptionNumber == 28) return FALSE;

        // Сохраняем результат в параметры
        $this->advertShortDescriptionDOM = $currentShortAdvert;

        // Сохраняем идентификаторы соответствующего объявления на сайте e1 в параметры объекта
        $href = $this->advertShortDescriptionDOM->find('td nobr a', 0)->href;
        $this->id = $href;

        return TRUE;
    }

    /**
     * Загружает страницу с подробным описанием объявления и помещает ее в $this->advertFullDescriptionDOM в виде DOM-объекта
     * @return bool TRUE в случае успешной загрузки и FALSE в противном случае
     */
    public function loadFullAdvertDescription() {

        // Говорят, что в библиотеке SimpleHTMLDOM могут наблюдаться утечки памяти, на всякий случай чистим после каждого цикла работы
        if (isset($this->advertFullDescriptionDOM)) $this->advertFullDescriptionDOM->clear();

        // Вычисляем URL запрашиваемой страницы
        $url = "http://www.e1.ru/business/realty/" . $this->id;

        // Непосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, "", "", FALSE);

        // Если загрузить страницу не удалось - сообщим об этом
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadFullAdvertDescription():1 Не удалось получить страницу с подробным описанием объекта с сайта e1 по адресу:" . $url);
            return FALSE;
        }

        // Меняем кодировку с windows-1251 на utf-8
        //$pageHTML = iconv("windows-1251", "UTF-8", $pageHTML);

        // Сохраним в параметры объекта DOM-объект страницы со списком объявлений
        $this->advertFullDescriptionDOM = str_get_html($pageHTML);

        return TRUE;
    }





    /**
     * Функция для парсинга данных по конкретному объявлению с сайта e1.ru
     * @return array|bool ассоциативный массив параметров объекта недвижимости, если отсутствют ключевые параметры (сейчас только источник объявления), то возвращает FALSE
     */
    public function parseFullAdvert() {

        // Собираем массив, каждый член которого - некоторый параметр объекта недвижимости
        $tableRows = $this->advertFullDescriptionDOM->find("table tr");

        // Готовим массив, в который сложим параметры объявления
        $params = array();

        // Тип объекта
        $params['typeOfObject'] = $this->getTypeOfObject();

        // Номер квартиры - его необходимо обязательно указывать и указывать уникальное значение, иначе объявление невозможно будет уникально идентифицировать
        $params['apartmentNumber'] = mt_rand(1000, 100000);

        // Перебираем все имеющиеся параметры объявления и заполняет соответствующие параметры ассоциативного массива
        foreach ($tableRows as $oneParam) {

            // Получим название параметра
            $paramName = $oneParam->find("td b", 0)->plaintext;

            // Стоимость аренды и комиссия
            if ($paramName == "Цена:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value != "") {
                    $params['costOfRenting'] = intval($value);
                    $params['currency'] = "руб.";
                    $params['compensationMoney'] = 0;
                    $params['compensationPercent'] = 0;
                }
                continue;
            }

            // Количество комнат
            if ($paramName == "Комнат:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value != "") $params['amountOfRooms'] = intval($value);
                continue;
            }

            // Смежные комнаты
            if ($paramName == "Смежных комнат:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value == "ни одной") {
                    $params['adjacentRooms'] = "нет";
                } else {
                    $params['adjacentRooms'] = "да";
                    $params['amountOfAdjacentRooms'] = intval($oneParam->find("td", 1)->plaintext);
                }
                continue;
            }

            // Площадь
            if ($paramName == "Площадь:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value != "") $value = explode("/", $value); else continue;
                if ($params['typeOfObject'] == "комната") {
                    if (isset($value[0])) $params['roomSpace'] = floatval($value[0]);
                }
                if ($params['typeOfObject'] == "квартира" || $params['typeOfObject'] == "0") {
                    if (isset($value[0])) $params['totalArea'] = floatval($value[0]);
                    if (isset($value[1])) $params['livingSpace'] = floatval($value[1]);
                    if (isset($value[2])) $params['kitchenSpace'] = floatval($value[2]);
                }
                continue;
            }

            // Этаж
            if ($paramName == "Этаж:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value != "") $floorArr = explode("/", $value); else continue;
                if (isset($floorArr[0])) $params['floor'] = intval($floorArr[0]);
                if (isset($floorArr[1])) $params['totalAmountFloor'] = intval($floorArr[1]);
                continue;
            }

            // Район
            if ($paramName == "Район:") {
                $value = $oneParam->find("td", 1);
                if (isset($value)) $value = $value->plaintext; else $value = "0";
                if ($value == "Новая Сортировка") $value = "Сортировка новая";
                if ($value == "Старая Сортировка") $value = "Сортировка старая";
                if ($value == "Виз") $value = "ВИЗ";
                if ($value == "Юго-Западный") $value = "Юго-запад";
                $params['district'] = $value;
                continue;
            }

            // Адрес
            if ($paramName == "Адрес:" || $paramName == "Адрес: ") {
                $value = $oneParam->find("td", 1)->find("a", 0);
                if (isset($value)) $params['address'] = $value->plaintext;
                continue;
            }

            // Доп. сведения
            if ($paramName == "Доп. сведения:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if (!isset($params['comment']) || $params['comment'] == "") $params['comment'] = $value; else $params['comment'] .= $value;
                continue;
            }

            // Ком. платежи
            if ($paramName == "Ком. платежи:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value == "Оплачиваются дополнительно") $params['utilities'] = "да";
                if ($value == "Включены в стоимость") $params['utilities'] = "нет";
                continue;
            }

            // Мебель
            if ($paramName == "Мебель:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value == "Есть") {
                    $params['furnitureInLivingAreaExtra'] = "Есть";
                    $params['furnitureInKitchenExtra'] = "Есть";
                }
                continue;
            }

            // Техника
            if ($paramName == "Техника: ") {
                $params['appliancesExtra'] = "";
                $value = $oneParam->find("td", 1)->find("img");
                foreach ($value as $one) {
                    if ($one->src == "/img/hol.png") $params['appliancesExtra'] .= "холодильник, ";
                    if ($one->src == "/img/tv.png") $params['appliancesExtra'] .= "телевизор, ";
                    if ($one->src == "/img/stir.png") $params['appliancesExtra'] .= "стиральная машина, ";
                }
                continue;
            }

            // Источник
            $paramName = $oneParam->find("td", 0)->plaintext;
            if ($paramName == "Источник") {
                $value = $oneParam->find("td a font b", 0)->plaintext;
                if ($value == "" && $oneParam->find("td", 1)->plaintext == "Добавлено на наш сайт") $value = "http://bazab2b.ru/?c_id=" . $this->c_id . "&&id=" . $this->id . "&modal=1"; // Для объявлений добавленных напрямую в базуБ2Б
                $params['sourceOfAdvert'] = $value;
            }
        }

        // Проверяем, удалось ли получить ссылку на источник объявления, если нет - значит пользователь, под которым мы запросили данные не авторизован, нужно повторить процедуру
        if (!isset($params['sourceOfAdvert']) || $params['sourceOfAdvert'] == "") {
            return FALSE;
        }

        return $params;
    }






    /**
     * Функция проверяет, обрабатывалось ли данное объявление ранее
     * @return bool возвращает TRUE, если текущее объявление уже обрабатывалось, FALSE в случае, если не обрабатывалось
     */
    public function isAdvertAlreadyHandled() {

        /* Опознавательные идентификаторы всех обработанных за последнее время объявлений сохраняются в БД по мере обработки каждого объявления
           Сравнение идентификаторов текущего объявления с сохраненными позволяет гарантированно убедиться в том, что данное объявление еще не сохранялось в мою БД */

        // Проверяем по массиву $this->handledAdverts - было ли данное объявление уже обработано или нет
        foreach ($this->handledAdverts as $value) {
            if ($value == $this->id) return TRUE;
        }

        return FALSE;
    }

    /**
     * Функция возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     * @return bool возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     */
    public function isStopHandling() {

        // Получим текущую дату
        $currentDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));

        // Получим значения времени и даты публикации для данного объявления
        $publicationData = $this->advertShortDescriptionDOM->find('td', 7)->plaintext;
        $publicationData = explode(".", $publicationData);
        $date = new DateTime(date("Y") . "-" . $publicationData[1] . "-" . $publicationData[0], new DateTimeZone('Asia/Yekaterinburg'));

        // Если объявление было опубликовано ранее, чем $this->actualDayAmountForAdvert дня назад, то нужно остановить парсинг
        $interval = $currentDate->diff($date);
        $interval = intval($interval->format("%d"));
        if ($interval >= $this->actualDayAmountForAdvert) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    /**
     * Функция запоминает в БД, что данное объявление успешно обработано, что позволит избежать его повторной обработки
     * @return bool возвращает TRUE в случае успеха и FALSE в противном случае
     */
    public function setAdvertIsHandled() {

        // Получим дату публикации для данного объявления
        $publicationData = $this->advertShortDescriptionDOM->find('td', 7)->plaintext;
        $publicationData = explode(".", $publicationData);
        $date = new DateTime(date("Y") . "-" . $publicationData[1] . "-" . $publicationData[0], new DateTimeZone('Asia/Yekaterinburg'));

        // Сохраняем идентификаторы объявления в БД и сразу выдаем результат
        return DBconnect::insertHandledAdvertFromE1($this->id, $date);
    }

}