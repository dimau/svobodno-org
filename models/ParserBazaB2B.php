<?php
/**
 * Класс содержит методы, используемые для парсинга сайта bazaB2B
 */

class ParserBazaB2B {

    private $login = "testagent"; // Логин для доступа к сайту
    private $password = "tsettest"; // Пароль для доступа к сайту
    private $actualDayAmountForAdvert = 1; // Количество дней, за которые парсер проверяет объявления из списка. Если ему попадается объявление, опубликованное ранее данного количества дней назад, то он прекращает свою работу. 1 - только сегодняшние объявления, 2 - сегодняшние и вчерашние и так далее.
    public $handledAdverts = NULL; // Содержит ассоциативный массив с идентификаторами обработанных объявлений за срок = $actualDayAmountForAdvert от текущего момента
    public $advertsListDOM; // DOM-объект страницы со списком объявлений, обрабатываемой в данный момент
    private $advertsListNumber = 0; // Номер страницы со списком объявлений, обрабатываемой в данный момент (находится в $advertsListDOM). Первоначальное значение = 0 означает, что в переменной $advertsListDOM еще нет DOM объекта (мы еще не получали страницу со списком объявлений)
    public $advertShortDescriptionDOM; // DOM-объект строки таблицы (единичного элемента списка объявлений). Содержит краткое описание объявления, обрабатываемого в данный момент
    private $advertShortDescriptionNumber = 0; // Номер строки таблицы (единичного элемента списка объявлений), обрабатываемого в данный момент (находится в $advertShortDescriptionDOM)
    public $advertFullDescriptionDOM; // DOM-объект страницы с подробным описанием объявления

    /**
     * КОНСТРУКТОР
     */
    public function __construct() {

        // Получить идентификаторы всех обработанных объявлений за срок = actualDayAmountForAdvert от текущего дня
        $finalDate = new DateTime();
        $finalDate = $finalDate->format('d.m.Y');
        $initialDate = new DateTime();
        $initialDate->modify('-'.$this->actualDayAmountForAdvert.' day');
        $initialDate = $initialDate->format('d.m.Y');
        $this->handledAdverts = DBconnect::selectHandledAdvertsFromBazab2b($initialDate, $finalDate);

        // Если получить список уже обработанных объявлений с сайта bazab2b получить не удалось, то прекращаем выполнение скрипта от греха подальше
        if ($this->handledAdverts === NULL || !is_array($this->handledAdverts)) {
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

        // Получаем следующую страницу со списком объявлений
        $this->advertsListNumber++;
        $pageHTML = $this->getAdvertsListHTML($this->advertsListNumber);

        // Если получить HTML страницы не удалось
        if (!$pageHTML) return FALSE;

        // Получаем DOM-объект и сохраняем его в параметры
        $this->advertsListDOM = str_get_html($pageHTML);

        // Сбрасываем счетчик текущего обрабатываемого краткого описания объявления
        $this->advertShortDescriptionNumber = 0;
        $this->advertShortDescriptionDOM = NULL;

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

        return TRUE;
    }

    /**
     * Загружает страницу с подробным описанием объявления и помещает ее в $this->advertFullDescriptionDOM в виде DOM-объекта
     * @return bool TRUE в случае успешной загрузки и FALSE в противном случае
     */
    public function loadFullAdvertDescription() {

        // Говорят, что в библиотеке SimpleHTMLDOM могут наблюдаться утечки памяти, на всякий случай чистим после каждого цикла работы
        if (isset($this->advertFullDescriptionDOM)) $this->advertFullDescriptionDOM->clear();

        // Получаем ссылку из краткого описания объявления для загрузки его подробного описания
        $href = $this->advertShortDescriptionDOM->find('.modal', 0)->href;

        // Загрузим страницу с подробным описанием
        $pageHTML = $this->getFullAdvertDescriptionHTML($href);

        // Если загрузить страницу не удалось - сообщим об этом
        if (!$pageHTML) return FALSE;

        $this->advertFullDescriptionDOM = str_get_html($pageHTML);

        return TRUE;
    }

    /**
     * Функция для парсинга данных по конкретному объявлению с сайта bazab2b.ru
     * @return array ассоциативный массив параметров объекта недвижимости
     */
    public function parseFullAdvert() {

        // Собираем массив, каждый член которого - некоторый параметр объекта недвижимости
        $tableRows = $this->advertFullDescriptionDOM->find("table tr");

        // Готовим массив, в который сложим параметры объявления
        $params = array();

        // Тип объекта
        $params['typeOfObject'] = $this->getTypeOfObject();

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
                    $params['compensationMoney'] = intval($params['costOfRenting']*0.3);
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
                $value = explode("/", $oneParam->find("td", 1)->plaintext);
                if ($params['typeOfObject'] == "комната") {
                    if (isset($value[0])) $param['roomSpace'] = $value[0];
                }
                if ($params['typeOfObject'] == "квартира" || $params['typeOfObject'] == "0") {
                    if (isset($value[0])) $param['totalArea'] = floatval($value[0]);
                    if (isset($value[1])) $param['livingSpace'] = floatval($value[1]);
                    if (isset($value[2])) $param['kitchenSpace'] = floatval($value[2]);
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
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value != "") $params['district'] = $value; else $params['district'] = "0";
                continue;
            }

            // Адрес
            if ($paramName == "Адрес:") {
                $value = $oneParam->find("td", 1)->plaintext;
                if ($value != "") $params['address'] = $value; else $params['district'] = "0";
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
                if ($value == "" && $oneParam->find("td", 1)->plaintext == "Добавлено на наш сайт") $value = "Сайт bazaB2B"; // Для объявлений добавленных напрямую в базуБ2Б
                $params['sourceOfAdvert'] = $value;
                if (!isset($params['comment']) || $params['comment'] == "") $params['comment'] = $value; else $params['comment'] .= $value;
                continue;
            }
        }

        return $params;
    }

    /**
     * Функция возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     * @return bool возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     */
    public function isStopHandling() {

        // Получим текущую дату и месяц
        $currentDate = date('j');
        $currentMonth = date('n');

        // Получим значения времени и даты публикации для данного объявления
        $publicationTime = $this->advertShortDescriptionDOM->find('td', 0)->find('font', 0)->plaintext;
        $value = $this->advertShortDescriptionDOM->find('td', 0)->find('center', 0)->plaintext;
        if (isset($value) && $value = explode(".", $value)) {
            $publicationDate = intval($value['0']);
            $publicationMonth = intval($value['1']);
        } else {
            $publicationDate = 0;
            $publicationMonth = 0;
        }

        // Если объявление было опубликовано ранее, чем 3 дня назад, то нужно остановить парсинг
        if (isset($publicationTime)) return FALSE;
        if ($publicationDate == 0 || $publicationMonth == 0) return TRUE;
        if ($publicationMonth < $currentMonth || $publicationDate < $currentDate - $this->actualDayAmountForAdvert) {
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

        // Получим идентификаторы текущего объявления
        $href = $this->advertShortDescriptionDOM->find('.modal', 0)->href;
        preg_match('/^\?c_id=([0-9]*)&.*$/', $href, $val);
        $c_id = $val[1];
        preg_match('/&id=([0-9]*)$/', $href, $val);
        $id = $val[1];

        // Проверяем по массиву $this->handledAdverts - было ли данное объявление уже обработано или нет
        foreach ($this->handledAdverts as $key => $value) {
            if ($key == $id && $value == $c_id) {
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

        // Получим идентификаторы текущего объявления
        $href = $this->advertShortDescriptionDOM->find('.modal', 0)->href;
        preg_match('/^\?c_id=([0-9]*)&.*$/', $href, $val);
        $c_id = $val[1];
        preg_match('/&id=([0-9]*)$/', $href, $val);
        $id = $val[1];

        // Получим дату публикации для данного объявления
        $publicationTime = $this->advertShortDescriptionDOM->find('td', 0)->find('font', 0)->plaintext;
        $value = $this->advertShortDescriptionDOM->find('td', 0)->find('center', 0)->plaintext;
        if (isset($value) && $value = explode(".", $value)) {
            $publicationDate = intval($value['0']);
            $publicationMonth = intval($value['1']);
        } else {
            $publicationDate = 0;
            $publicationMonth = 0;
        }

        // Преобразуем дату публикации к виду: 27.01.1987
        if (isset($publicationTime)) {
            $date = new DateTime();
        } else {
            $date = new DateTime(date("Y")."-".$publicationMonth."-".$publicationDate);
        }
        $date = $date->format("d.m.Y");

        // Сохраняем идентификаторы объявления в БД и сразу выдаем результат
        return DBconnect::insertHandledAdvertFromBazab2b($c_id, $id, $date);
    }

    /**
     * Функция возвращает HTML страницы с таблицей всех объявлений сайта bazaB2B
     * @param int $pageNumber номер страницы со списком объявлений, которую необходимо загрузить
     * @return bool|string HTML страницы с таблицей всех объявлений сайта bazaB2B, в случае ошибки FALSE
     */
    private function getAdvertsListHTML($pageNumber) {

        // Валидация входных параметров
        if (!isset($pageNumber) || !is_int($pageNumber)) return FALSE;

        // Иницализация библиотеки curl.
        if (!($ch = curl_init())) return FALSE;

        //Устанавливаем URL запроса
        curl_setopt($ch, CURLOPT_URL, 'http://bazab2b.ru/?pagx=baza&p='.$pageNumber);
        // Включаем работу с сессиями от этого сайта
        curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=4dd4d06ee0acb2d4ac0bd24f808c97a1");
        //При значении true CURL включает в вывод заголовки.
        curl_setopt($ch, CURLOPT_HEADER, false);
        //Куда помещать результат выполнения запроса:
        //  false – в стандартный поток вывода,
        //  true – в виде возвращаемого значения функции curl_exec.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //Максимальное время ожидания в секундах
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        //Установим значение поля User-agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3');
        //Выполнение запроса
        $data = curl_exec($ch);
        //Особождение ресурса
        curl_close($ch);

        // Меняем кодировку с windows-1251 на utf-8
        //$data = iconv("windows-1251", "UTF-8", $data);

        // Выдаем результат работы, в случае ошибки FALSE
        return $data;
    }

    /**
     * Функция возвращает HTML страницы с подробным описание опеределенного объявления сайта bazaB2B
     * @param string $href ссылка на подробное описание объявления
     * @return string|bool HTML страницы с подробным описание определенного объявления сайта bazaB2B, в случае ошибки FALSE
     */
    private function getFullAdvertDescriptionHTML($href) {

        // Иницализация библиотеки curl.
        if (!($ch = curl_init())) return FALSE;

        //Устанавливаем URL запроса
        curl_setopt($ch, CURLOPT_URL, 'http://bazab2b.ru/'.$href."&modal=1");
        // Включаем работу с сессиями от этого сайта
        curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=4dd4d06ee0acb2d4ac0bd24f808c97a1");
        //При значении true CURL включает в вывод заголовки.
        curl_setopt($ch, CURLOPT_HEADER, false);
        //Куда помещать результат выполнения запроса:
        //  false – в стандартный поток вывода,
        //  true – в виде возвращаемого значения функции curl_exec.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //Максимальное время ожидания в секундах
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        //Установим значение поля User-agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3');
        //Выполнение запроса
        $data = curl_exec($ch);
        //Особождение ресурса
        curl_close($ch);

        // Меняем кодировку с windows-1251 на utf-8
        $data = iconv("windows-1251", "UTF-8", $data);

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
