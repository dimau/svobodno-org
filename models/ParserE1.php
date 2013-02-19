<?php
/**
 * Класс для парсинга E1.ru
 */

class ParserE1 extends ParserBasic {

    /**
     * КОНСТРУКТОР
     */
    public function __construct($mode) {

        // Выполняем конструктор базового класса
        parent::__construct($mode);

        // Для e1 нумерация листов со списками объявлений начинается с 0. При первом использовании счетчик увеличит -1 до 0
        $this->advertsListNumber = -1;

        // Получим список уже ранее обработанных объявлений
        $this->readHandledAdverts();
    }

    /**
     * Получение списка уже обработанных объявлений с данного сайта
     */
    protected function readHandledAdverts() {

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

        // Сбрасываем счетчик текущего обрабатываемого краткого описания объявления на значение по умолчанию. Первые 3 tr относятся к заголовку таблицы.
        $this->advertShortDescriptionNumber = 2;

        return TRUE;
    }

    /**
     * Достает следующее краткое описание объявления из текущего списка.
     * При первом использовании достает самое первое краткое описание объявления из текущего списка.
     * Сохраняет полученный DOM-объект в $advertShortDescriptionDOM, а также сохраняет идентификатор загруженного объявления в $id
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

        // TODO: test
        /*if ($this->advertShortDescriptionNumber >= 12) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: достигли 10-ого объявления - заканчиваем парсинг");
            DBconnect::closeConnectToDB();
            exit();
        }*/

        // Если получить DOM-модель краткого описания объявления не удалось или мы достигли подвала таблицы с объявлениями - прекращаем
        if ($currentShortAdvert === NULL) return FALSE;

        // Сохраняем результат в параметры
        $this->advertShortDescriptionDOM = $currentShortAdvert;

        // Важно помнить о том, что tr не всегда содержит информацию по конкретному объявлению, поэтому нужно проверять, есть ли у этой строки id объявления.
        // Сохраняем идентификаторы соответствующего объявления на сайте e1 в параметры объекта
        if ($href = $this->advertShortDescriptionDOM->find('td nobr a', 0)) {
            $this->id = $href->href;

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: работаем с объявлением номер X");

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
        if (!isset($this->advertFullDescriptionDOM)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->loadFullAdvertDescription():2 не удалось разобрать страницу с полным описанием объявления");
            return FALSE;
        }

        // Находим основной раздел страницы, помещенной в таблицу, в которую помещены все блоки с описанием объявления - именно с этой частью страницы мы и будем дальше работать
        // TODO: test
        //$this->advertFullDescriptionDOM = $this->advertFullDescriptionDOM->find("body table", 3)->find("tr", 0)->find("td", 3);
        //$this->advertFullDescriptionDOM = $this->advertFullDescriptionDOM->find('body', 0)->children(10)->children(0)->children(0)->children(3);

        // TODO: test
        //Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: успешно загрузили полное описание объявления: ".$pageHTML);
        //exit();

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: удалось успешно загрузить страницу с полное описание объявления");

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
        if (!isset($phoneNumber)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->getPhoneNumber():1 не удалось получить телефонный номер из объявления с id = ".$this->id);
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: телефонный номер: ".$phoneNumber);

        // Приведем телефонный номер к стандартному виду
        if (!($phoneNumber = $this->phoneNumberNormalization($phoneNumber, "Екатеринбург"))) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->getPhoneNumber():2 не удалось нормализовать телефонный номер из объявления с id = ".$this->id);
            return FALSE;
        }

        // Есть телефонный номер!
        $this->phoneNumber = $phoneNumber;

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: преобразованный телефонный номер: ".$this->phoneNumber);

        // Задача успешно выполнена
        return TRUE;
    }

    /**
     * Метод проверяет наличие признаков агента в подробном описании объявления
     * @return bool TRUE в случае успешного нахождения признаков агента и FALSE в противном случае
     */
    public function hasSignsAgent() {

        // Проверяем наличие блока с заголовком "Информация об агентстве:"
        $pretenders = $this->advertFullDescriptionDOM->find("td[bgcolor=#bababa]");
        foreach ($pretenders as $value) {
            if ($value->children(0) !== NULL && $value->children(0)->plaintext == "Информация об агентстве:") {
                return TRUE;
            }
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
        if (!$this->advertFullDescriptionDOM || !$this->id) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->parseFullAdvert():1 не удалось запустить парсинг объявления - не хватает исходных данных");
            return FALSE;
        }

        // Готовим массив, в который сложим параметры объявления
        $params = array();

        // TODO: добавить в модель новый параметр - наличие фотографий в исходном объявлении
        // Выясним - есть ли в объявлении фотографии и комментарий
        $params['sourceAdvertWithPhotos'] = FALSE;
        $params['comment'] = "";
        $pretenders = $this->advertFullDescriptionDOM->find("td[bgcolor=#bababa]");
        foreach ($pretenders as $value) {
            if ($value->children(0) !== NULL && $value->children(0)->plaintext == "Фотографии:") {
                $params['sourceAdvertWithPhotos'] = TRUE;
            }
            if ($value->children(0) !== NULL && $value->children(0)->plaintext == "Дополнительные сведения:") {
                $params['comment'] = $value->parent()->next_sibling()->children(0)->children(0)->plaintext;
            }
        }

        // РАЗБИРАЕМ СТРУКТУРИРОВАННЫЕ ДАННЫЕ ОБЪЯВЛЕНИЯ

        // Собираем массив, каждый член которого - некоторый параметр объекта недвижимости
        $tableRows = $this->advertFullDescriptionDOM->find("tr[valign=top]");

        // Тип объекта
        // TODO: Сделать более универсальный способ определения типа объекта
        $params['typeOfObject'] = "квартира";

        // Номер квартиры - его необходимо обязательно указывать и указывать уникальное значение, иначе объявление невозможно будет уникально идентифицировать
        $params['apartmentNumber'] = mt_rand(1000, 100000);

        // Источник
        $params['sourceOfAdvert'] = "http://www.e1.ru/business/realty/" . $this->id;

        // Телефон контактного лица
        $params['contactTelephonNumber'] = $this->phoneNumber;

        // Перебираем все имеющиеся параметры объявления и заполняет соответствующие параметры ассоциативного массива
        foreach ($tableRows as $oneParam) {

            // Получим название параметра
            if ($oneParam->find("td", 0) !== NULL) {
                $paramName = $oneParam->find("td", 0)->plaintext;

                // TODO: test
                Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: парсим структурированную строку: ".$paramName);

            } else {

                // TODO: test
                Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: парсим структурированную строку - не нашли название параметра!");

                continue;
            }

            // Смежные комнаты
            if ($paramName == "Количество смежных комнат:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
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
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['amountOfRooms'] = intval($value);
                continue;
            }

            // Площадь
            if ($paramName == "Общая площадь, кв.м.") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
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
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
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
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if ($value == "Оплачиваются дополнительно") $params['utilities'] = "да";
                if ($value == "Включены в стоимость") $params['utilities'] = "нет";
                continue;
            }

            // Адрес
            if ($paramName == "Адрес:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->find('text', 0)->plaintext;
                if (isset($value)) $params['address'] = $value;
                continue;
            }

            // Этаж
            if ($paramName == "Этаж:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $floorArr = explode("/", $value); else continue;
                if (isset($floorArr[0])) $params['floor'] = intval($floorArr[0]);
                if (isset($floorArr[1])) $params['totalAmountFloor'] = intval($floorArr[1]);
                continue;
            }

            // Санузел
            if ($paramName == "Сан узел:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (!isset($value) && $value == "") continue;
                if ($value == "Раздельный") $params['typeOfBathrooms'] = "раздельный";
                continue;
            }

            // Мебель
            if ($paramName == "Мебель:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if ($value == "Есть") {
                    $params['furnitureInLivingAreaExtra'] = "Есть";
                    $params['furnitureInKitchenExtra'] = "Есть";
                }
                continue;
            }

            // Балкон/лоджия
            if ($paramName == "Лоджия:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (!isset($value) && $value == "") continue;
                if ($value == "Лоджия") $params['typeOfBalcony'] = "лоджия";
                if ($value == "Балкон") $params['typeOfBalcony'] = "балкон";
                continue;
            }

            // Город
            if ($paramName == "Город:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['city'] = $value;
                continue;
            }

            // Район
            if ($paramName == "Район:") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                // Если строку с районом получили, то уберем пробел в начале (особенность e1)
                if (!isset($value) || $value == "") $value = "0"; else $value = mb_substr($value, 1);
                if ($value == "С.Сортировка") $value = "Сортировка старая";
                if ($value == "Юго-Западный") $value = "Юго-запад";
                /*if ($value == "Старая Сортировка") $value = "Сортировка старая";
                if ($value == "Виз") $value = "ВИЗ";
                */
                $params['district'] = $value;

                //TODO: test
                Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: район: '". $params['district'] ."'");

                continue;
            }
        }

        // Проверяем, удалось ли получить ссылку на источник объявления
        if (!isset($params['sourceOfAdvert']) || $params['sourceOfAdvert'] == "") {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserE1.php->parseFullAdvert():2 не удалось успешно завершить парсинг объявления - не определена ссылка на исходное объявление");
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: удалось распарсить полное объявление: ".json_encode($params));

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
            if ($value == $this->id) {

                //TODO: test
                Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: объявление ранее обработано");

                return TRUE;
            }
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: объявление еще не обработано");

        return FALSE;
    }

    /**
     * Функция возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     * @return bool возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     */
    public function isTooLateDate() {

        // Получим текущую дату
        $currentDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));

        // Получим значения времени и даты публикации для данного объявления
        $publicationData = $this->advertShortDescriptionDOM->find('td', 7)->plaintext;
        $publicationData = explode(".", $publicationData);
        $date = new DateTime(date("Y") . "-" . $publicationData[1] . "-" . $publicationData[0], new DateTimeZone('Asia/Yekaterinburg'));

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: дата публикации объявления: ".$date->format('Y-m-d H:i:s'));

        // Если объявление было опубликовано ранее, чем $this->actualDayAmountForAdvert дня назад, то нужно остановить парсинг
        $interval = $currentDate->diff($date);
        $interval = intval($interval->format("%d"));
        if ($interval >= $this->actualDayAmountForAdvert) {

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: дата публикации объявления слишком поздняя");

            return TRUE;

        } else {

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: подходящая дата публикации - работаем далее");

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
        $date = $date->format("d.m.Y");

        // Сохраняем идентификаторы объявления в БД и выдаем результат
        $res = DBconnect::insertHandledAdvertFromE1($this->id, $date);

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: статус отметить объявление как обработанное: '". $res ."'");

        return $res;
    }

}