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
    // TODO: сейчас отрабатывает список только для ОДНОКОМНАТНЫХ КВАРТИР
    public function loadNextAdvertsList() {

        // Очищаем данные от предыдущего списка объявлений
        $this->advertsListDOM = NULL;

        // Говорят, что в библиотеке SimpleHTMLDOM могут наблюдаться утечки памяти, на всякий случай чистим после каждого цикла работы
        if (isset($this->advertsListDOM)) $this->advertsListDOM->clear();

        // Увеличиваем счетчик текущей страницы списка объявлений
        $this->advertsListNumber++;

        // Вычисляем URL запрашиваемой страницы
        $url = 'http://www.e1.ru/business/realty/search.php?s_obj_type=1&rq=1&op_type=2&city_id=1&region_id=0&area_all=-1&sb=8&ob=2&p=' . $this->advertsListNumber;

        // Фиксируем в логах факт загрузки новой страницы со списком объявлений
        Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadNextAdvertsList():1 Загружаем новую страницу со списком объявлений с e1, url: '" . $url . "'");

        // Непосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, "", "", FALSE);

        // Если получить HTML страницы не удалось
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadNextAdvertsList():2 Не удалось получить страницу со списком объявлений с сайта e1 по адресу: '" . $url . "', получена страница: '" . $pageHTML . "'");
            return FALSE;
        }

        // Получаем DOM-объект и сохраняем его в параметры
        $this->advertsListDOM = str_get_html($pageHTML);

        // Убедимся, что на странице есть список объявлений. Иначе мы можем бесконечно загружать 404 страницу или подобные ей.
        if (!isset($this->advertsListDOM)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadNextAdvertsList():3 Полученная страница со списком объявлений с сайта e1 не содержит список объявлений, по адресу: '" . $url . "'");
            return FALSE;
        }

        // Убедимся, что парсер не застрял на какой-либо ошибке на странице и не превысил лимит в 90 скачиваний страницы со списком объявлений за 1 раз
        // TODO: test - настоящий лимит = 90
        if ($this->advertsListNumber >= 4) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadNextAdvertsList():4 Достигли лимита в 90 страниц со списком объявлений за 1 раз работы парсера");
            return FALSE;
        }

        // Сбрасываем счетчик текущего обрабатываемого краткого описания объявления на значение по умолчанию. Первые 3 tr относятся к заголовку таблицы.
        $this->advertShortDescriptionNumber = 2;

        return TRUE;
    }

    /**
     * Достает следующее краткое описание объявления из текущего списка.
     * При первом использовании достает самое первое краткое описание объявления из текущего списка.
     * Сохраняет полученный DOM-объект в $advertShortDescriptionDOM
     * @return bool TRUE в случае успешного выделения кратких сведений по объявлению. FALSE в противном случае. Важно, что tr не всегда на самом деле содержит краткие сведения по объявлению, иногда это просто заголовок таблицы, в этом случае, у него не будет id.
     */
    public function getNextAdvertShortDescription() {

        // Очищаем данные о предыдущем объявлении
        $this->advertShortDescriptionDOM = NULL;
        $this->id = NULL;
        $this->phoneNumber = NULL;
        $this->advertFullDescriptionDOM = NULL;

        $this->advertShortDescriptionNumber++;
        $currentShortAdvert = $this->advertsListDOM->find('tr[valign=top]', $this->advertShortDescriptionNumber);

        // Если получить DOM-модель краткого описания объявления не удалось или мы достигли подвала таблицы с объявлениями - прекращаем
        if ($currentShortAdvert === NULL) return FALSE;

        // Сохраняем результат в параметры
        $this->advertShortDescriptionDOM = $currentShortAdvert;

        // Важно помнить о том, что tr не всегда содержит информацию по конкретному объявлению, поэтому нужно проверять, есть ли у этой строки id объявления.
        // Сохраняем идентификаторы соответствующего объявления на сайте e1 в параметры объекта
        if ($href = $this->advertShortDescriptionDOM->find('td nobr a', 0)) {
            $this->id = $href->href;
            return TRUE;
        } else {
            // Если полученный в $this->advertShortDescriptionDOM элемент оказался не кратким описанием объявления, а чем-то иным, то рекурсивно вызываем этот же метод - пока не найдем краткое описание объявления или пока не достигнем конца списка
            return $this->getNextAdvertShortDescription();
        }
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
        $pageHTML = iconv("windows-1251", "UTF-8", $pageHTML);

        // Сохраним в параметры объекта DOM-объект страницы со списком объявлений
        $this->advertFullDescriptionDOM = str_get_html($pageHTML);
        if (!isset($this->advertFullDescriptionDOM)) return FALSE;

        // Находим основной раздел страницы, помещенной в таблицу, в которую помещены все блоки с описанием объявления - именно с этой частью страницы мы и будем дальше работать
        // TODO: test
        //$this->advertFullDescriptionDOM = $this->advertFullDescriptionDOM->find("body table", 3)->find("tr", 0)->find("td", 3);
        //$this->advertFullDescriptionDOM = $this->advertFullDescriptionDOM->find('body', 0)->children(10)->children(0)->children(0)->children(3);

        // TODO: test
        //Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: успешно загрузили полное описание объявления: ".$this->advertFullDescriptionDOM);
        //exit();

        return TRUE;
    }

    /**
     * Метод достает из подробного описания объявления телефон контактного лица
     * @return bool TRUE в случае успешного нахождения телефонного номера и FALSE в противном случае
     */
    public function getPhoneNumber() {

        // Найдем на странице телефон контактного лица
        $pretenders = $this->advertFullDescriptionDOM->find("tr[valign=top]");
        foreach ($pretenders as $value) {
            if ($value->children(0) !== NULL && $value->children(0)->plaintext == "Телефон:") {
                $phoneNumber = $value->children(1)->plaintext;
            }
        }

        // Если достать номер телефона со страницы не удалось
        if (!isset($phoneNumber)) return FALSE;

        // Приведем телефонный номер к стандартному виду
        if (!($phoneNumber = $this->phoneNumberNormalization($phoneNumber, "Екатеринбург"))) return FALSE;

        // Есть телефонный номер!
        $this->phoneNumber = $phoneNumber;

        // Задача успешно выполнена
        return TRUE;
    }

    /**
     * Метод проверяет наличие признаков агента в подробном описании объявления
     * @return bool TRUE в случае успешного нахождения признаков агента и FALSE в противном случае
     */
    public function hasSignsAgent() {

        // Проверяем наличие блока с заголовком "Информация об агентстве:"
        if ($this->advertFullDescriptionDOM->find("table", 5)->find("tr td b", 0)->plaintext == "Информация об агентстве:"
            OR $this->advertFullDescriptionDOM->find("table", 4)->find("tr td b", 0)->plaintext == "Информация об агентстве:"
        ) {
            return TRUE;
        }

        // Признаки агентства не обнаружены
        return FALSE;
    }

    /**
     * Функция для парсинга данных по конкретному объявлению с сайта e1.ru
     * @return array|bool ассоциативный массив параметров объекта недвижимости, если отсутствют ключевые параметры (сейчас только источник объявления), то возвращает FALSE
     */
    public function parseFullAdvert() {

        // Валидация исходных данных
        if (!$this->advertFullDescriptionDOM || !$this->id) return FALSE;

        // Готовим массив, в который сложим параметры объявления
        $params = array();

        // TODO: добавить в модель новый параметр - наличие фотографий в исходном объявлении
        // Выясним - есть ли в объявлении фотографии
        if ($this->advertFullDescriptionDOM->find("table", 2)->find("tr td b", 0)->plaintext == "Фотографии:") {
            $params['sourceAdvertWithPhotos'] = TRUE;
        } else {
            $params['sourceAdvertWithPhotos'] = FALSE;
        }

        // Достанем комментарий
        if ($params['sourceAdvertWithPhotos']) {
            if ($this->advertFullDescriptionDOM->find("table", 3)->find("tr td b", 0)->plaintext == "Дополнительные сведения:") {
                $params['comment'] = $this->advertFullDescriptionDOM->find("table", 3)->find("tr td p", 0)->plaintext;
            }
        } else {
            if ($this->advertFullDescriptionDOM->find("table", 2)->find("tr td b", 0)->plaintext == "Дополнительные сведения:") {
                $params['comment'] = $this->advertFullDescriptionDOM->find("table", 2)->find("tr td p", 0)->plaintext;
            }
        }

        // РАЗБИРАЕМ СТРУКТУРИРОВАННЫЕ ДАННЫЕ ОБЪЯВЛЕНИЯ

        // Собираем массив, каждый член которого - некоторый параметр объекта недвижимости
        $tableRows = $this->advertFullDescriptionDOM->find("table", 1)->find("tr", 1)->find("tr");

        // Тип объекта
        // TODO: Сделать более универсальный способ определения типа объекта
        $params['typeOfObject'] = "квартира";

        // Номер квартиры - его необходимо обязательно указывать и указывать уникальное значение, иначе объявление невозможно будет уникально идентифицировать
        $params['apartmentNumber'] = mt_rand(1000, 100000);

        // Источник
        $params['sourceOfAdvert'] = "http://www.e1.ru/business/realty/" . $this->id;

        // Перебираем все имеющиеся параметры объявления и заполняет соответствующие параметры ассоциативного массива
        foreach ($tableRows as $oneParam) {

            // Получим название параметра
            $paramName = $oneParam->find("td", 0)->plaintext;

            // Смежные комнаты
            if ($paramName == "Количество смежных комнат:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if ($value == "ни одной") {
                    $params['adjacentRooms'] = "нет";
                } else {
                    $params['adjacentRooms'] = "да";
                    $params['amountOfAdjacentRooms'] = intval($value);
                }
                continue;
            }

            // Количество комнат
            if ($paramName == "Кол-во комнат в квартире:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (isset($value) && $value != "") $params['amountOfRooms'] = intval($value);
                continue;
            }

            // Площадь
            if ($paramName == "Общая площадь, кв.м.") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (isset($value) && $value != "") $value = explode("/", $value); else continue;
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

            // Стоимость аренды и комиссия
            if ($paramName == "Цена:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (isset($value) && $value != "") {
                    $value = str_replace(array(" ", "р"), "", $value);
                    $params['costOfRenting'] = intval($value);
                    $params['currency'] = "руб.";
                    $params['compensationMoney'] = 0;
                    $params['compensationPercent'] = 0;
                }
                continue;
            }

            // Ком. платежи
            if ($paramName == "Коммунальные платежи:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if ($value == "Оплачиваются дополнительно") $params['utilities'] = "да";
                if ($value == "Включены в стоимость") $params['utilities'] = "нет";
                continue;
            }

            // Адрес
            if ($paramName == "Адрес:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (isset($value)) $params['address'] = $value;
                continue;
            }

            // Этаж
            if ($paramName == "Этаж:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (isset($value) && $value != "") $floorArr = explode("/", $value); else continue;
                if (isset($floorArr[0])) $params['floor'] = intval($floorArr[0]);
                if (isset($floorArr[1])) $params['totalAmountFloor'] = intval($floorArr[1]);
                continue;
            }

            // Санузел
            if ($paramName == "Сан узел:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (!isset($value) && $value == "") continue;
                if ($value == "Раздельный") $params['typeOfBathrooms'] = "раздельный";
                continue;
            }

            // Мебель
            if ($paramName == "Мебель:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if ($value == "Есть") {
                    $params['furnitureInLivingAreaExtra'] = "Есть";
                    $params['furnitureInKitchenExtra'] = "Есть";
                }
                continue;
            }

            // Балкон/лоджия
            if ($paramName == "Лоджия:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (!isset($value) && $value == "") continue;
                if ($value == "Лоджия") $params['typeOfBalcony'] = "лоджия";
                if ($value == "Балкон") $params['typeOfBalcony'] = "балкон";
                continue;
            }

            // Город
            if ($paramName == "Город:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (isset($value) && $value != "") $params['city'] = $value;
                continue;
            }

            // Район
            if ($paramName == "Район:") {
                $value = $oneParam->find("td", 1)->find("b")->plaintext;
                if (!isset($value) || $value == "") $value = "0";
                /*if ($value == "Новая Сортировка") $value = "Сортировка новая";
                if ($value == "Старая Сортировка") $value = "Сортировка старая";
                if ($value == "Виз") $value = "ВИЗ";
                if ($value == "Юго-Западный") $value = "Юго-запад";*/
                $params['district'] = $value;
                continue;
            }
        }

        // Проверяем, удалось ли получить ссылку на источник объявления
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