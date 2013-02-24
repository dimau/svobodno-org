<?php
/**
 * Класс для парсинга 66.ru
 */

class Parser66ru extends ParserBasic {

    /**
     * КОНСТРУКТОР
     */
    public function __construct($mode) {

        // Выполняем конструктор базового класса
        parent::__construct($mode);

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
        $this->handledAdverts = DBconnect::selectHandledAdverts($this->mode, $initialDate, $finalDate);

        // Если получить список уже обработанных объявлений с сайта bazab2b получить не удалось, то прекращаем выполнение скрипта от греха подальше
        if ($this->handledAdverts === NULL || !is_array($this->handledAdverts)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->readHandledAdverts:1 Парсинг сайта 66.ru остановлен, так как не удалось получить сведения о ранее загруженных объявлениях");
            DBconnect::closeConnectToDB();
            exit();
        }
    }

    /**
     * Загружает следующую страницу со списком объявлений с сайта 66.ru.
     * При первом использовании загружает первую страницу списка объявлений.
     * Сохраняет загруженную страницу в $advertsListDOM
     * @return bool TRUE в случае успешной загрузки и FALSE в противном случае
     */
    public function loadNextAdvertsList() {

        // Очищаем данные от предыдущего списка объявлений
        $this->advertsListDOM = NULL;

        // Говорят, что в библиотеке SimpleHTMLDOM могут наблюдаться утечки памяти, на всякий случай чистим после каждого цикла работы
        if (isset($this->advertsListDOM)) $this->advertsListDOM->clear();

        // Увеличиваем счетчик текущей страницы списка объявлений
        $this->advertsListNumber++;

        // Вычисляем URL запрашиваемой страницы
        switch ($this->mode) {
            case "66ruKv":
                $url = 'http://www.66.ru/realty/doska/live/?sort_dir=-1&price_to=&object_type=kv&full_area=&location=ekb&action_type=lease&sort_by=shuffle_order_2&price_from=&page=' . $this->advertsListNumber;
                break;
            case "66ruKom":
                $url = 'http://www.66.ru/realty/doska/live/?sort_dir=-1&price_to=&object_type=room&full_area=&location=ekb&action_type=lease&sort_by=shuffle_order_2&price_from=&page=' . $this->advertsListNumber;
                break;
            default:
                Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->loadNextAdvertsList():1 Не удалось определить адрес для загрузки списка объявлений с сайта 66.ru для режима: '" . $this->mode . "'");
                return FALSE;
        }

        // Фиксируем в логах факт загрузки новой страницы со списком объявлений
        Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->loadNextAdvertsList():2 Загружаем новую страницу со списком объявлений с 66.ru, url: '" . $url . "'");

        // Непосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, "", "", FALSE);

        // Если получить HTML страницы не удалось
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->loadNextAdvertsList():3 Не удалось получить страницу со списком объявлений с сайта 66.ru по адресу: '" . $url . "', получена страница: '" . $pageHTML . "'");
            return FALSE;
        }

        // Получаем DOM-объект и сохраняем его в параметры
        $this->advertsListDOM = str_get_html($pageHTML);

        // Найдем таблицу со списком объявлений
        if (isset($this->advertsListDOM)) $this->advertsListDOM = $this->advertsListDOM->find(".b-content-table__items", 0);

        // Убедимся, что на странице есть список объявлений. Иначе мы можем бесконечно загружать 404 страницу или подобные ей.
        if (!isset($this->advertsListDOM)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->loadNextAdvertsList():4 Полученная страница со списком объявлений с сайта 66.ru не содержит список объявлений, по адресу: '" . $url . "'");
            return FALSE;
        }

        // Сбрасываем счетчик текущего обрабатываемого краткого описания объявления на значение по умолчанию. Первый tr относится к заголовку таблицы.
        $this->advertShortDescriptionNumber = 0;

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
        $currentShortAdvert = $this->advertsListDOM->find('tr', $this->advertShortDescriptionNumber);

        // Если получить DOM-модель краткого описания объявления не удалось или мы достигли подвала таблицы с объявлениями - прекращаем
        if ($currentShortAdvert === NULL) return FALSE;

        // Сохраняем результат в параметры
        $this->advertShortDescriptionDOM = $currentShortAdvert;

        // Важно помнить о том, что tr не всегда содержит информацию по конкретному объявлению, поэтому нужно проверять, есть ли у этой строки id объявления.
        // Кроме того, проверяем, что данное объявление не является предложением от агентства. Предложения от агентств игнорируем.
        // Сохраняем идентификатор соответствующего объявления на сайте 66.ru в параметры объекта
        if ($href = $this->advertShortDescriptionDOM->find('td a', 0)) {

            // Проверка на агентство
            $agencyStatus = $this->advertShortDescriptionDOM->find('td', 1)->innertext;
            $agencyStatus = explode("<br />", $agencyStatus);
            $agencyStatus = $agencyStatus[1];
            if ($agencyStatus == "агентство                                               ") {

                //TODO: test
                Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: Объявление оказалось от агентства: '".$agencyStatus."'");

                // Если полученный в $this->advertShortDescriptionDOM элемент оказался объявлением от агентства, то рекурсивно вызываем этот же метод - пока не найдем краткое описание объявления или пока не достигнем конца списка
                return $this->getNextAdvertShortDescription();
            }

            // Получим идентификатор объявления. Необходимо удалить вот эту общую часть url (/realty/doska/live/) подробного описания объявления для выделения уникального минимального идентификатора объявления
            $this->id = mb_substr($href->href, 19, iconv_strlen($href->href, 'UTF-8') - 19, 'UTF-8');

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: работаем с объявлением номер X, объявление не от агентства: '".$agencyStatus."'");

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
        $url = "http://www.66.ru/realty/doska/live/" . $this->id;

        // Непосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, "", "", FALSE);

        // Если загрузить страницу не удалось - сообщим об этом
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->loadFullAdvertDescription():1 Не удалось получить страницу с подробным описанием объекта с сайта 66.ru по адресу:" . $url);
            return FALSE;
        }

        // Меняем кодировку с windows-1251 на utf-8
        //$pageHTML = iconv("windows-1251", "UTF-8", $pageHTML);

        // Сохраним в параметры объекта DOM-объект страницы с подробным описанием объявления
        $this->advertFullDescriptionDOM = str_get_html($pageHTML);
        if (!isset($this->advertFullDescriptionDOM)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->loadFullAdvertDescription():2 не удалось разобрать страницу с полным описанием объявления");
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: удалось успешно загрузить страницу с полное описание объявления");

        return TRUE;
    }

    /**
     * Метод достает из подробного описания объявления телефон контактного лица
     * @return bool TRUE в случае успешного нахождения телефонного номера и FALSE в противном случае
     */
    public function getPhoneNumber() {

        // Найдем на странице телефон контактного лица
        if ($phoneNumber = $this->advertFullDescriptionDOM->find(".goods-card__contacts-phones__item", 0)) {
            $phoneNumber = $phoneNumber->phone;
        }

        // Если достать номер телефона со страницы не удалось
        if (!isset($phoneNumber)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->getPhoneNumber():1 не удалось получить телефонный номер из объявления с id = ".$this->id);
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: телефонный номер: ".$phoneNumber);

        // Приведем телефонный номер к стандартному виду
        if (!($phoneNumber = $this->phoneNumberNormalization($phoneNumber, "Екатеринбург"))) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->getPhoneNumber():2 не удалось нормализовать телефонный номер из объявления с id = ".$this->id);
            return FALSE;
        }

        // Есть телефонный номер!
        $this->phoneNumber = $phoneNumber;

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: преобразованный телефонный номер: ".$this->phoneNumber);

        // Задача успешно выполнена
        return TRUE;
    }

    /**
     * Метод проверяет наличие признаков агента в подробном описании объявления
     * @return bool TRUE в случае успешного нахождения признаков агента и FALSE в противном случае
     */
    public function hasSignsAgent() {
        // Объявления от агентств отсеиваются еще на этапе перебора краткого описания.
        return FALSE;
    }

    /**
     * Функция для парсинга данных по конкретному объявлению с сайта 66.ru
     * @return array|bool ассоциативный массив параметров объекта недвижимости, если отсутствют ключевые параметры (сейчас только источник объявления), то возвращает FALSE
     */
    public function parseFullAdvert() {

        // Валидация исходных данных
        if (!$this->advertFullDescriptionDOM || !$this->id) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->parseFullAdvert():1 не удалось запустить парсинг объявления - не хватает исходных данных");
            return FALSE;
        }

        // Готовим массив, в который сложим параметры объявления
        $params = array();

        // Выясним - есть ли в объявлении фотографии
        if ($this->advertFullDescriptionDOM->find(".b-content-card_item__wrap__picture", 0)) {
            $params['hasPhotos'] = TRUE;
        } else {
            $params['hasPhotos'] = FALSE;
        }

        // Выясним - есть ли в объявлении комментарий
        $params['comment'] = "";
        $comment = $this->advertFullDescriptionDOM->find(".b-content-card_item__hightline-20", 0)->parent()->children(7);
        if (isset($comment) && $comment->tag == "p") {
            $params['comment'] = $comment->innertext;
            // Для удаления на конце строки служебных символов <br/> выполним:
            $lengthComment = iconv_strlen($params['comment'], 'UTF-8');
            $params['comment'] = mb_substr($params['comment'], 0, $lengthComment - 5, 'UTF-8');
        }

        // РАЗБИРАЕМ СТРУКТУРИРОВАННЫЕ ДАННЫЕ ОБЪЯВЛЕНИЯ

        // Собираем массив, каждый член которого - некоторый параметр объекта недвижимости
        $tableRows = $this->advertFullDescriptionDOM->find(".b-content-card_item__features tr");

        // Тип объекта
        $params['typeOfObject'] = $this->getTypeOfObject();

        // Номер квартиры - его необходимо обязательно указывать и указывать уникальное значение, иначе объявление невозможно будет уникально идентифицировать
        $params['apartmentNumber'] = mt_rand(1000, 100000);

        // Источник
        $params['sourceOfAdvert'] = "http://www.66.ru/realty/doska/live/" . $this->id;

        // Телефон контактного лица
        $params['contactTelephonNumber'] = $this->phoneNumber;

        // Стоимость аренды
        $value = $this->advertFullDescriptionDOM->find(".b-content-card_item__cost b", 0)->plaintext;
        $params['costOfRenting'] = intval($value);
        $params['currency'] = "руб.";
        $params['compensationMoney'] = 0;
        $params['compensationPercent'] = 0;

        // Адрес
        $value = $this->advertFullDescriptionDOM->find("h1", 0)->plaintext;
        if ($this->mode == "66ruKv") {
            // Пропускаем начало строки: "Сдам X-к. квартиру, "
            $params['address'] = mb_substr($value, 20, iconv_strlen($value, 'UTF-8') - 20, 'UTF-8');
        } elseif ($this->mode == "66ruKom") {
            // Пропускаем начало строки: "Сдам комнату в 4-к. квартире, "
            $params['address'] = mb_substr($value, 30, iconv_strlen($value, 'UTF-8') - 30, 'UTF-8');
        }

        // Перебираем все имеющиеся параметры объявления и заполняет соответствующие параметры ассоциативного массива
        foreach ($tableRows as $oneParam) {

            // Получим название параметра
            if ($oneParam->find("td", 0) !== NULL) {
                $paramName = $oneParam->find("td", 0)->innertext;
            } else {
                continue;
            }

            // Район
            if ($paramName == "Район") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (!isset($value) || $value == "") $value = "0";
                /*if ($value == "С.Сортировка") $value = "Сортировка старая";*/
                if ($value == "Новая Сортировка") $value = "Сортировка новая";
                if ($value == "Юго-Западный") $value = "Юго-запад";
                $params['district'] = $value;
                continue;
            }

            // Город
            if ($paramName == "Город") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['city'] = $value;
                continue;
            }

            // Площадь
            if ($paramName == "Общая площадь") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['totalArea'] = $value;
                continue;
            }
            if ($paramName == "Жилая площадь") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['livingSpace'] = $value;
                continue;
            }
            if ($paramName == "Площадь комнаты") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['roomSpace'] = $value;
                continue;
            }

            // Количество комнат
            if ($paramName == "Количество комнат") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['amountOfRooms'] = intval($value);
                continue;
            }

            // Этаж
            if ($paramName == "Этажей в доме") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['totalAmountFloor'] = intval($value);
                continue;
            }
            if ($paramName == "Этаж") {
                $value = $oneParam->find("td", 1)->find("b", 0)->plaintext;
                if (isset($value) && $value != "") $params['floor'] = intval($value);
                continue;
            }

        }

        // Проверяем, удалось ли получить ссылку на источник объявления
        if (!isset($params['sourceOfAdvert']) || $params['sourceOfAdvert'] == "") {
            Logger::getLogger(GlobFunc::$loggerName)->log("Parser66ru.php->parseFullAdvert():2 не удалось успешно завершить парсинг объявления - не определена ссылка на исходное объявление");
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: удалось распарсить полное объявление: ".json_encode($params));

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
                Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: объявление ранее обработано");

                return TRUE;
            }
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: объявление еще не обработано");

        return FALSE;
    }

    /**
     * Функция возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     * @return bool возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     */
    public function isTooLateDate() {

        // Получим текущую дату
        $currentDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));

        // Получим значения даты публикации для данного объявления
        $publicationData = $this->advertShortDescriptionDOM->find('.b-content-table__items-date', 0)->innertext;
        if ($publicationData == "<em>сегодня</em>") {
            $date = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        } else {
            $publicationData = explode(".", $publicationData);
            $date = new DateTime(date("Y") . "-" . $publicationData[1] . "-" . $publicationData[0], new DateTimeZone('Asia/Yekaterinburg'));
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: дата публикации объявления: ".$date->format('Y-m-d H:i:s'));

        // Если объявление было опубликовано ранее, чем $this->actualDayAmountForAdvert дня назад, то нужно остановить парсинг
        $interval = $currentDate->diff($date);
        $interval = intval($interval->format("%d"));
        if ($interval >= $this->actualDayAmountForAdvert) {

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: дата публикации объявления слишком поздняя");

            return TRUE;

        } else {

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: подходящая дата публикации - работаем далее");

            return FALSE;
        }

    }

    /**
     * Функция запоминает в БД, что данное объявление успешно обработано, что позволит избежать его повторной обработки
     * @return bool возвращает TRUE в случае успеха и FALSE в противном случае
     */
    public function setAdvertIsHandled() {

        // Получим дату публикации для данного объявления
        $publicationData = $this->advertShortDescriptionDOM->find('.b-content-table__items-date', 0)->innertext;
        if ($publicationData == "<em>сегодня</em>") {
            $date = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        } else {
            $publicationData = explode(".", $publicationData);
            $date = new DateTime(date("Y") . "-" . $publicationData[1] . "-" . $publicationData[0], new DateTimeZone('Asia/Yekaterinburg'));
        }
        $date = $date->format("d.m.Y");

        // Сохраняем идентификаторы объявления в БД и выдаем результат
        $res = DBconnect::insertHandledAdvert($this->mode, $this->id, $date);

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера 66ru: статус отметить объявление как обработанное: '". $res ."'");

        return $res;
    }

    /**
     * Функция возвращает тип объекта недвижимости для текущего объявления
     * @return string тип объекта недвижимости
     */
    private function getTypeOfObject() {

        // Определяем тип объекта недвижимости на основе режима работы парсинга
        switch ($this->mode) {
            case "66ruKv":
                $typeOfObject = "квартира";
                break;
            case "66ruKom":
                $typeOfObject = "комната";
                break;
            default:
                $typeOfObject = "0";
        }

        return $typeOfObject;
    }

}