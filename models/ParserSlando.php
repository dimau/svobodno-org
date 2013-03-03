<?php
/**
 * Класс для парсинга slando.ru
 */

class ParserSlando extends ParserBasic {

    /**
     * КОНСТРУКТОР
     */
    public function __construct($mode) {

        // Выполняем конструктор базового класса
        parent::__construct($mode);

        // На slando парсим только сегодняшние объявления
        $this->actualDayAmountForAdvert = 1;

        // Для slando нумерация страниц со списками объявлений начинается с 1. При первом использовании счетчик увеличится с 0 до 1
        $this->advertsListNumber = 0;

        // Для slando игнорируем платные объявления на верху списка, остальные расположены в порядке даты публикации. Это позволяет нам не накладывать обязательства полностью проходить 1 или несколько страниц.
        $this->minAdvertsListForHandling = 0;

        // Определим максимальное количество страниц со списками объявлений для парсинга в 1 сессию
        $this->maxAdvertsListForHandling = 12;

        // Получим список уже ранее обработанных объявлений
        $this->readHandledAdverts();
    }

    /**
     * Загружает следующую страницу со списком объявлений с сайта slando.
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

        // Проверка на превышение лимита по количеству загрузок страниц со списком объявлений. Это защита от ошибок в парсере, которая призвана обезопасить ресурс донор от падения
        if ($this->isTooManyAdvertsLists()) return FALSE;

        // Вычисляем URL запрашиваемой страницы
        switch ($this->mode) {
            case "slandoKvEkat":
                $url = 'http://ekaterinburg.sve.slando.ru/nedvizhimost/arenda-kvartir/dolgosrochnaya-arenda-kvartir/?search%5Bprivate_business%5D=private&page=' . $this->advertsListNumber;
                break;
            case "slandoKomEkat":
                $url = 'http://ekaterinburg.sve.slando.ru/nedvizhimost/arenda-komnat/dolgosrochnaya-arenda-komnat/?search%5Bprivate_business%5D=private&page=' . $this->advertsListNumber;
                break;
            default:
                Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->loadNextAdvertsList():1 Не удалось определить адрес для загрузки списка объявлений с сайта slando для режима: '" . $this->mode . "'");
                return FALSE;
        }

        // Фиксируем в логах факт загрузки новой страницы со списком объявлений
        Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->loadNextAdvertsList():2 Загружаем новую страницу со списком объявлений в режиме " . $this->mode . ", url: '" . $url . "'");

        // Непосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, "", "", FALSE);

        // Если получить HTML страницы не удалось
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->loadNextAdvertsList():3 Работа парсера в режиме " . $this->mode . " остановлена. Не удалось получить страницу со списком объявлений, по адресу: '" . $url . "', получена страница: '" . $pageHTML . "'");
            return FALSE;
        }

        // Получаем DOM-объект и сохраняем его в параметры
        $this->advertsListDOM = str_get_html($pageHTML);

        // Найдем таблицу со списком обычных (не проплаченных) объявлений
        if (isset($this->advertsListDOM)) $this->advertsListDOM = $this->advertsListDOM->find("#offers_table", 0);

        // Убедимся, что на странице есть список объявлений. Иначе мы можем бесконечно загружать 404 страницу или подобные ей.
        if (!isset($this->advertsListDOM)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->loadNextAdvertsList():4 Работа парсера в режиме " . $this->mode . " остановлена. Полученная страница со списком объявлений с сайта slando не содержит список объявлений, по адресу: '" . $url . "'");
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
        $currentShortAdvert = $this->advertsListDOM->find('[summary=Объявление] tr', $this->advertShortDescriptionNumber);

        // Если получить DOM-модель краткого описания объявления не удалось или мы достигли подвала таблицы с объявлениями - прекращаем
        if ($currentShortAdvert === NULL) return FALSE;

        // Сохраняем результат в параметры
        $this->advertShortDescriptionDOM = $currentShortAdvert;

        // Важно помнить о том, что полученный $currentShortAdvert может не содержать информацию по конкретному объявлению, поэтому нужно проверять, есть ли у этой строки id объявления.
        // Важно помнить о том, что мы разбираем таблицу с обычными объявлениями, а верхние топовые проплаченные объявления не трогаем. Это удобно с технической точки зрения: чтобы не отрабатывать дату публикации и обработанность для них (иначе мы закончим так и не начав парсить страницу)
        // Сохраняем идентификатор соответствующего объявления на сайте slando в параметры объекта
        if ($href = $this->advertShortDescriptionDOM->find('td', 2)->find("a", 0)) {

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: работаем с объявлением номер X");

            // Получим идентификатор объявления.
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
        $url = "http://www.avito.ru" . $this->id;

        // Непосредственно выполняем запрос к серверу
        $pageHTML = $this->curlRequest($url, "", "", FALSE);

        // Если загрузить страницу не удалось - сообщим об этом
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->loadFullAdvertDescription():1 Не удалось получить страницу с подробным описанием объекта с сайта slando по адресу:" . $url);
            return FALSE;
        }

        // Меняем кодировку с windows-1251 на utf-8
        //$pageHTML = iconv("windows-1251", "UTF-8", $pageHTML);

        // Сохраним в параметры объекта DOM-объект страницы с подробным описанием объявления
        $this->advertFullDescriptionDOM = str_get_html($pageHTML);
        if (!isset($this->advertFullDescriptionDOM)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->loadFullAdvertDescription():2 не удалось разобрать страницу с полным описанием объявления");
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: удалось успешно загрузить страницу с полное описание объявления");

        return TRUE;
    }

    /**
     * Метод достает из подробного описания объявления телефон контактного лица
     * @return bool TRUE в случае успешного нахождения телефонного номера и FALSE в противном случае
     */
    public function getPhoneNumber() {

        // Найдем на странице ссылку на картинку с телефоном контактного лица
        if ($phoneNumber = $this->advertFullDescriptionDOM->find(".goods-card__contacts-phones__item", 0)) {
            $phoneNumber = $phoneNumber->phone;
        }

        // Если достать номер телефона со страницы не удалось
        if (!isset($phoneNumber)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->getPhoneNumber():1 не удалось получить телефонный номер из объявления с id = ".$this->id);
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: телефонный номер: ".$phoneNumber);

        // Приведем телефонный номер к стандартному виду
        if (!($phoneNumber = $this->phoneNumberNormalization($phoneNumber, "Екатеринбург"))) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->getPhoneNumber():2 не удалось нормализовать телефонный номер из объявления с id = ".$this->id);
            return FALSE;
        }

        // Есть телефонный номер!
        $this->phoneNumber = $phoneNumber;

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: преобразованный телефонный номер: ".$this->phoneNumber);

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
     * Функция для парсинга данных по конкретному объявлению с сайта slando
     * @return array|bool ассоциативный массив параметров объекта недвижимости, если отсутствют ключевые параметры (сейчас только источник объявления), то возвращает FALSE
     */
    public function parseFullAdvert() {

        // Валидация исходных данных
        if (!$this->advertFullDescriptionDOM || !$this->id) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->parseFullAdvert():1 не удалось запустить парсинг объявления - не хватает исходных данных");
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
            // Удалим служебные символы "<br/>", которые slando ставит после каждого абзаца текста в комментарии:
            $params['comment'] = str_replace("<br/>", "", $params['comment']);
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
        $value = $this->advertFullDescriptionDOM->find(".b-content-card_item__cost b", 0)->innertext;
        if ($value == "договорная") {
            $params['costOfRenting'] = 0;
        } else {
            $value = str_replace("&nbsp;", "", $value); // Убираем пробел между тысячами и оставшейся частью стоимости аренды
            $params['costOfRenting'] = intval($value);
        }
        $params['currency'] = "руб.";
        $params['compensationMoney'] = 0;
        $params['compensationPercent'] = 0;

        // Адрес
        $value = $this->advertFullDescriptionDOM->find("h1", 0)->plaintext;
        // Берем только ту часть, которая расположена между "Сдам комнату...," _________________ и "(район)"
        $beginOfAddressTemplate = "~^(.*?)(,){1}\s~"; // Шаблон начала адрес в виде: "Сдам 1-к. квартиру, "
        $endOfAddressTemplate = "~\s\((.*?)\)$~"; // Шаблон конца адреса, в котором отмечен район в виде: " (Центр)"
        if ($value = preg_replace(array($beginOfAddressTemplate, $endOfAddressTemplate), "", $value)) {
            $params['address'] = $value;
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
                if ($value == "ЖБИ (Комсомольский)") $value = "ЖБИ";
                if ($value == "Старая Сортировка") $value = "Сортировка старая";
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
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserSlando.php->parseFullAdvert():2 не удалось успешно завершить парсинг объявления - не определена ссылка на исходное объявление");
            return FALSE;
        }

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: удалось распарсить полное объявление: ".json_encode($params));

        return $params;
    }

    /**
     * Функция возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     * @return bool возвращает TRUE, если данное объявление по дате публикации уже не попадает во временное окно актуальности объявлений.
     */
    public function isTooLateDate() {

        // Если парсер работает со страницей списка объявлений, номер которой меньше или равен номеру страницы, до которого парсер обязан доходить за 1 сессию парсинга, то данная причина окончания парсинга не применяется
        if ($this->advertsListNumber <= $this->minAdvertsListForHandling) return FALSE;

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
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: дата публикации объявления: ".$date->format('Y-m-d H:i:s'));

        // Если объявление было опубликовано ранее, чем $this->actualDayAmountForAdvert дня назад, то нужно остановить парсинг
        $interval = $currentDate->diff($date);
        $interval = intval($interval->format("%d"));
        if ($interval >= $this->actualDayAmountForAdvert) {

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: дата публикации объявления слишком поздняя");

            return TRUE;

        } else {

            //TODO: test
            Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера Slando: подходящая дата публикации - работаем далее");

            return FALSE;
        }

    }

    /**
     * Функция возвращает тип объекта недвижимости для текущего объявления
     * @return string тип объекта недвижимости
     */
    private function getTypeOfObject() {

        // Определяем тип объекта недвижимости на основе режима работы парсинга
        switch ($this->mode) {
            case "slandoKvEkat":
                $typeOfObject = "квартира";
                break;
            case "slandoKomEkat":
                $typeOfObject = "комната";
                break;
            default:
                $typeOfObject = "0";
        }

        return $typeOfObject;
    }

}