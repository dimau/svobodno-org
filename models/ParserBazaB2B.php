<?php
/**
 * Класс содержит методы, используемые для парсинга сайта bazaB2B
 */

class ParserBazaB2B {

    private $login = "testagent"; // Логин для доступа к сайту
    private $password = "tsettest"; // Пароль для доступа к сайту
    private $actualDayAmountForAdvert = 1; // Количество дней, за которые парсер проверяет объявления из списка. Если ему попадается объявление, опубликованное ранее данного количества дней назад, то он прекращает свою работу. 1 - только сегодняшние объявления, 2 - сегодняшние и вчерашние и так далее.
    private $handledAdverts = NULL; // Содержит ассоциативный массив с идентификаторами обработанных объявлений за срок = $actualDayAmountForAdvert от текущего момента
    private $advertsListDOM; // DOM-объект страницы со списком объявлений, обрабатываемой в данный момент
    private $advertsListNumber = 0; // Номер страницы со списком объявлений, обрабатываемой в данный момент (находится в $advertsListDOM). Первоначальное значение = 0 означает, что в переменной $advertsListDOM еще нет DOM объекта (мы еще не получали страницу со списком объявлений)
    private $advertShortDescriptionDOM; // DOM-объект строки таблицы (единичного элемента списка объявлений). Содержит краткое описание объявления, обрабатываемого в данный момент
    private $advertShortDescriptionNumber = 0; // Номер строки таблицы (единичного элемента списка объявлений), обрабатываемого в данный момент (находится в $advertShortDescriptionDOM)
    private $c_id; // Один из 2-х идентификаторов объявления на сайте bazab2b
    private $id; // Один из 2-х идентификаторов объявления на сайте bazab2b
    private $advertFullDescriptionDOM; // DOM-объект страницы с подробным описанием объявления

    /**
     * КОНСТРУКТОР
     */
    public function __construct() {

        // Получить идентификаторы всех обработанных объявлений за срок = actualDayAmountForAdvert от текущего дня
        $finalDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $finalDate = $finalDate->format('d.m.Y');
        $initialDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $initialDate->modify('-' . $this->actualDayAmountForAdvert . ' day');
        $initialDate = $initialDate->format('d.m.Y');
        $this->handledAdverts = DBconnect::selectHandledAdvertsFromBazab2b($initialDate, $finalDate);

        // Если получить список уже обработанных объявлений с сайта bazab2b получить не удалось, то прекращаем выполнение скрипта от греха подальше
        if ($this->handledAdverts === NULL || !is_array($this->handledAdverts)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->__construct():1 Парсинг сайта bazaB2B остановлен, так как не удалось получить сведения о ранее загруженных объявлениях");
            DBconnect::closeConnectToDB();
            exit();
        }
    }

    /**
     * Загружает следующую страницу со списком объявлений с сайта bazaB2B.
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
        $url = 'http://bazab2b.ru/?pagx=baza&p=' . $this->advertsListNumber;

        // Фиксируем в логах факт загрузки новой страницы со списком объявлений
        Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->loadNextAdvertsList():1 Загружаем новую страницу со списком объявлений с bazaB2B, url: '" . $url . "'");

        // Вычисляем POST параметры, которые могут понадобиться для авторизации на сайте
        $post = "user_name=" . $this->login . "&user_password=" . $this->password;

        // Неспосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, $post, 'cookieBazaB2B.txt', TRUE);

        // Если получить HTML страницы не удалось
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->loadNextAdvertsList():2 Не удалось получить страницу со списком объявлений с сайта bazaB2B по адресу: '" . $url . "', получена страница: '" . $pageHTML . "'");
            return FALSE;
        }

        // Получаем DOM-объект и сохраняем его в параметры
        $this->advertsListDOM = str_get_html($pageHTML);

        // Убедимся, что на странице есть список объявлений. Иначе мы можем бесконечно загружать 404 страницу или подобные ей.
        if ($this->advertsListDOM->find('.poisk .chr-wite', 0) === NULL) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->loadNextAdvertsList():3 Полученная страница со списком объявлений с сайта bazaB2B не содержит список объявлений, по адресу: '" . $url . "'");
            return FALSE;
        }

        // Сбрасываем параметры текущего обрабатываемого краткого описания объявления на значения по умолчанию
        $this->advertShortDescriptionNumber = 0;
        $this->advertShortDescriptionDOM = NULL;
        $this->c_id = NULL;
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
        $currentShortAdvert = $this->advertsListDOM->find('.poisk .chr-wite', $this->advertShortDescriptionNumber - 1);

        // Если получить DOM-модель краткого описания объявления не удалось
        if ($currentShortAdvert === NULL) return FALSE;

        // Сохраняем результат в параметры
        $this->advertShortDescriptionDOM = $currentShortAdvert;

        // Сохраняем идентификаторы соответствующего объявления на сайте bazab2b в параметры объекта
        $href = $this->advertShortDescriptionDOM->find('.modal', 0)->href;
        preg_match('/^\?c_id=([0-9]*)&.*$/', $href, $val);
        $this->c_id = intval($val[1]);
        preg_match('/&id=([0-9]*)$/', $href, $val);
        $this->id = intval($val[1]);

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
        $url = "http://bazab2b.ru/?c_id=" . $this->c_id . "&&id=" . $this->id . "&modal=1";

        // Вычисляем POST параметры, которые могут понадобиться для авторизации на сайте
        //$post = "user_name=".$this->login."&user_password=".$this->password;
        // Не передаем POST параметры, так как авторизация производится только при первоначальной загрузке списка объявлений, затем достаточно передавать только куки
        $post = 0;

        // Неспосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, $post, 'cookieBazaB2B.txt', TRUE);

        // Если загрузить страницу не удалось - сообщим об этом
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->loadFullAdvertDescription():1 Не удалось получить страницу с подробным описанием объекта с сайта bazaB2B по адресу:" . $url);
            return FALSE;
        }

        // Меняем кодировку с windows-1251 на utf-8
        $pageHTML = iconv("windows-1251", "UTF-8", $pageHTML);

        // Сохраним в параметры объекта DOM-объект страницы со списком объявлений
        $this->advertFullDescriptionDOM = str_get_html($pageHTML);

        return TRUE;
    }

    /**
     * Функция для парсинга данных по конкретному объявлению с сайта bazab2b.ru
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
                    $params['compensationMoney'] = intval($params['costOfRenting'] * 0.3);
                    $params['compensationPercent'] = 30;
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
     * Функция возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     * @return bool возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     */
    public function isStopHandling() {

        // Получим текущую дату
        $currentDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));

        // Получим значения времени и даты публикации для данного объявления
        $publicationTime = $this->advertShortDescriptionDOM->find('td', 0)->find('font', 0);
        if (isset($publicationTime) && $publicationTime->plaintext != "") {
            $date = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg')); // Получаем текущую дату
        } else {
            // Если объявление не сегодняшнее, то вычисляем дату его публикации
            $value = $this->advertShortDescriptionDOM->find('td', 0)->find('center', 0);
            if (isset($value) && $value = explode(".", $value->plaintext)) {
                $publicationDate = intval($value['0']);
                $publicationMonth = intval($value['1']);
                $date = new DateTime(date("Y") . "-" . $publicationMonth . "-" . $publicationDate, new DateTimeZone('Asia/Yekaterinburg'));
            } else {
                // Если не удалось получить ни время, ни дату публикации
                Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->isStopHandling():1 Не удалось получить ни время, ни дату публикации объекта с сайта bazaB2B по адресу: " . "http://bazab2b.ru/?c_id=" . $this->c_id . "&&id=" . $this->id . "&modal=1");
                return TRUE;
            }
        }

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
     * Функция проверяет, обрабатывалось ли данное объявление ранее
     * @return bool возвращает TRUE, если текущее объявление уже обрабатывалось, FALSE в случае, если не обрабатывалось
     */
    public function isAdvertAlreadyHandled() {

        /* Опознавательные идентификаторы всех обработанных за последнее время объявлений сохраняются в БД по мере обработки каждого объявления
           Сравнение идентификаторов текущего объявления с сохраненными позволяет гарантированно убедиться в том, что данное объявление еще не сохранялось в мою БД */

        // Проверяем по массиву $this->handledAdverts - было ли данное объявление уже обработано или нет
        foreach ($this->handledAdverts as $key => $value) {
            if ($key == $this->id && $value == $this->c_id) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Функция запоминает в БД, что данное объявление успешно обработано, что позволит избежать его повторной обработки
     * @return bool возвращает TRUE в случае успеха и FALSE в противном случае
     */
    public function setAdvertIsHandled() {

        // Проверяем - объявление сегодняшнее? Если да, то дату устанавливаем также сегодняшнюю
        $publicationTime = $this->advertShortDescriptionDOM->find('td', 0)->find('font', 0);
        if (isset($publicationTime) && $publicationTime->plaintext != "") {
            $date = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg')); // Получаеми текущую дату
            $date = $date->format("d.m.Y");
        } else {
            // Если объявление не сегодняшнее, то вычисляем дату его публикации
            $value = $this->advertShortDescriptionDOM->find('td', 0)->find('center', 0);
            if (isset($value) && $value = explode(".", $value->plaintext)) {
                $publicationDate = intval($value['0']);
                $publicationMonth = intval($value['1']);
                $date = new DateTime(date("Y") . "-" . $publicationMonth . "-" . $publicationDate, new DateTimeZone('Asia/Yekaterinburg'));
                $date = $date->format("d.m.Y");
            } else {
                // Если не удалось получить ни время, ни дату публикации, то дальнейшие действия бесполезны
                Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->setAdvertIsHandled():1 Не удалось получить ни время, ни дату публикации объекта с сайта bazaB2B по адресу: " . "http://bazab2b.ru/?c_id=" . $this->c_id . "&&id=" . $this->id . "&modal=1");
                return FALSE;
            }
        }

        // Сохраняем идентификаторы объявления в БД и сразу выдаем результат
        return DBconnect::insertHandledAdvertFromBazab2b($this->c_id, $this->id, $date);
    }

    /**
     * Метод запускается в начале выполнения скрипта для авторизации и получения доступа к полным данным
     * @return bool возвращает TRUE в случае успеха и FALSE в противном случае
     */
    public function authorization() {

        // Вычисляем URL запрашиваемой страницы, на которой производится авторизация
        $url = 'http://bazab2b.ru/?pagx=baza&p=1';
        //TODO: test
        //$url = 'http://seasonvar.ru';

        // Вычисляем POST параметры, которые могут понадобиться для авторизации на сайте
        $post = "user_name=" . $this->login . "&user_password=" . $this->password;

        // Непосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, $post, 'cookieBazaB2B.txt', TRUE);
        //$pageHTML = $this->curlRequest($url, "", "", TRUE);

        // TODO: test
        //exit($pageHTML);

        // Возвращаем результат
        if ($pageHTML) {
            return TRUE;
        } else {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->authorization():1 Не удалось получить страницу в процессе авторизации на сайте bazaB2B");
            return FALSE;
        }
    }

    private function curlRequest($url, $post = "", $cookieFileName = "", $proxy = FALSE) {

        // Инициализация библиотеки curl.
        if (!($ch = curl_init())) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->curlRequest():1 Ошибка при инициализации curl. Не удалось получить страницу с сайта bazaB2B по адресу: " . $url);
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_URL, $url); // Устанавливаем URL запроса
        curl_setopt($ch, CURLOPT_HEADER, false); // При значении true CURL включает в вывод результата заголовки, которые нам не нужны (мы их на сервере не обрабатываем).
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // При значении = true полученный код страницы возвращается как результат выполнения curl_exec.
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Следовать за редиректами
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Максимальное время ожидания ответа от сервера в секундах
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); // Установим значение поля User-agent для маскировки под обычного пользователя
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, '92.255.185.161:3128'); // адрес прокси-сервера для анонимности
            //curl_setopt($ch, CURLOPT_PROXYUSERPWD,'user:pass'); // если необходимо предоставить имя пользователя и пароль для прокси
        }
        if ($cookieFileName != "") {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/logs/' . $cookieFileName); // Сохранять куки в указанный файл
            curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . '/logs/' . $cookieFileName); // При запросе передавать значения кук из указанного файла
        }
        if ($post != "") {
            curl_setopt($ch, CURLOPT_POST, TRUE); // Если указаны POST параметры, то включаем их использование
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        // Выполнение запроса
        $data = curl_exec($ch);
        // Особождение ресурса
        curl_close($ch);

        // Меняем кодировку с windows-1251 на utf-8
        //$data = iconv("windows-1251", "UTF-8", $data);

        // Выдаем результат работы, в случае ошибки FALSE
        return $data;
    }

    /**
     * Функция возвращает тип объекта недвижимости для текущего объявления
     * @return string тип объекта недвижимости
     */
    private function getTypeOfObject() {

        // Получаем тип объекта недвижимости в формате сайта bazaB2B
        $typeOfObject = $this->advertShortDescriptionDOM->find('td', 1)->find('center', 0)->plaintext;

        // Преобразуем формат сайта bazaB2B в формат моего сайта
        switch ($typeOfObject) {
            case "ком/":
            case "ком/1":
            case "ком/2":
            case "ком/3":
            case "ком/4":
            case "ком/5":
            case "ком/6":
            case "ком/6+":
                $typeOfObject = "комната";
                break;
            case "1кв":
            case "2кв":
            case "3кв":
            case "4кв":
            case "5кв":
            case "6кв":
                $typeOfObject = "квартира";
                break;
            default:
                $typeOfObject = "0";
        }

        return $typeOfObject;
    }

}