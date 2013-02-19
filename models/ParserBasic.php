<?php
/**
 * Родительский класс для остальных парсеров конкретных интернет-ресурсов
 */

class ParserBasic {

    protected $mode; // Режим работы парсера (определяется сайтом, который парсим и категорией объявлений, которые в этот раз парсятся на данном сайте)
    protected $lastSuccessfulHandledAdvertsId; // 3 идентификатора для последних успешно обработанных объявлений (это те объявления с которых в прошлый успешный раз начал обработку парсер, а значит дальше которых не имеет смысла ходить текущему парсеру - все, что расположено дальше этих 3-х объявлений уже успешно обработано)
    protected $newSuccessfulHandledAdvertsId = array(); // Идентификаторы первых 3-х объявлений, которые обрабатываются в этой сессии парсера. В случае успешного окончания парсинга они заменят в БД $lastSuccessfulHandledAdvertsId.
    protected $login; // Логин для доступа к сайту (используется только, если он необходим)
    protected $password; // Пароль для доступа к сайту (используется только, если он необходим)
    protected $actualDayAmountForAdvert = 2; // Количество дней, за которые парсер проверяет объявления из списка. Если ему попадается объявление, опубликованное ранее данного количества дней назад, то он прекращает свою работу. 1 - только сегодняшние объявления, 2 - сегодняшние и вчерашние и так далее.
    protected $handledAdverts = NULL; // Содержит ассоциативный массив с идентификаторами обработанных объявлений за срок = $actualDayAmountForAdvert от текущего момента
    protected $advertsListDOM; // DOM-объект страницы со списком объявлений, обрабатываемой в данный момент
    protected $advertsListNumber = 0; // Номер страницы со списком объявлений, обрабатываемой в данный момент (находится в $advertsListDOM). Первоначальное значение = 0 означает, что в переменной $advertsListDOM еще нет DOM объекта (мы еще не получали страницу со списком объявлений)
    protected $advertShortDescriptionDOM; // DOM-объект строки таблицы (единичного элемента списка объявлений). Содержит краткое описание объявления, обрабатываемого в данный момент
    protected $advertShortDescriptionNumber = -1; // Номер строки таблицы (единичного элемента списка объявлений), обрабатываемого в данный момент (находится в $advertShortDescriptionDOM) Непосредственно перед получением сведений по новому объявлению счетчик увеличивается на 1
    protected $id; // Идентификатор объявления на сайте
    protected $phoneNumber; // Телефон контактного лица по обрабатываемому объявлению
    protected $advertFullDescriptionDOM; // DOM-объект страницы с подробным описанием объявления

    /**
     * КОНСТРУКТОР
     */
    protected function __construct($mode) {

        // Устанавливаем режим работы парсера
        $this->mode = $mode;

        // Получим список 3-х идентификаторов для последних успешно обработанных объявлений в данном режиме
        $this->lastSuccessfulHandledAdvertsId = DBconnect::selectLastSuccessfulHandledAdvertsId($mode);

        if (count($this->lastSuccessfulHandledAdvertsId) != 3) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->__construct:1 Парсинг в режиме " . $mode . " остановлен, так как не удалось получить сведения о 3-х последних успешно обработанных объявлениях");
            DBconnect::closeConnectToDB();
            exit();
        }

    }

    public function getId() {
        return $this->id;
    }

    /**
     * Проверяет, совпало ли данное объявление из одним из трех объявлений, первыми обраотанных в прошлый успешный раз парсинга
     * @return bool TRUE, в случае если данное объявление относится к трем объявлениям, первыми обработанным в прошлый успешный раз парсинга. FALSE в противном случае
     */
    public function isAdvertLastSuccessfulHandled() {
        foreach ($this->lastSuccessfulHandledAdvertsId as $value) {
            if ($value == $this->id) {

                //TODO: test
                Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: Достигли объявления, успешно обработанного в прошлый раз!");

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Ограничивает парсер загрузкой максимум 90 страниц со списком объявлений за 1 раз.
     * Позволяет обезопасить нас и ресурс-источник объявлений от шибок в парсере или на самом ресурсе, связанных с запросом за короткий промежуток времени большого количества страниц
     * @return bool TRUE если парсер загрузил уже 90-ую страницу со списком объявлений, FALSE в противном случае
     */
    public function isTooManyAdvertsLists() {
        // TODO: test - настоящий лимит = 90
        if ($this->advertsListNumber >= 2) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->isTooManyAdvertsLists():1 Достигли лимита в 90 страниц со списком объявлений за 1 раз работы парсера в режиме: ".$this->mode);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Проверяет, является ли данное объявление одним из первых 3-х, обрабатываемых в данную сессию парсинга. Если является, то метод запоминает идентификатор объявления
     * @return bool Всегда возвращает TRUE
     */
    public function checkAdvertForOneOfFirst() {

        $count = count($this->newSuccessfulHandledAdvertsId);

        // Если в текущую сессию парсинга мы уже поймали 3 первых обработанных объявления, то более ничего не требуется
        if ($count >= 3) return TRUE;

        // Если в текущую сессию парсинга мы еще не запомнили 3-х объявлений, то добавляем
        $this->newSuccessfulHandledAdvertsId[$count] = $this->id;

        return TRUE;
    }

    /**
     * Метод вызывается при успешном окончании сессии парсинга для сохранения идентификаторов первых трех объявлений, с которых данная сессия была начата
     * @return bool TRUE в случае успеха и FALSE в противном случае
     */
    public function saveNewLastSuccessfulHandledAdvertsId() {

        // Загружаем следующие объявления и достаем из них идентификаторы, пока их количество не станет = 3
        while (($count = count($this->newSuccessfulHandledAdvertsId)) < 3) {

            // Загружаем следующее объявление и достаем из него идентификатор
            if ($this->getNextAdvertShortDescription()) {
                $this->newSuccessfulHandledAdvertsId[$count] = $this->id;
            } else {
                Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->saveNewLastSuccessfulHandledAdvertsId():1 Не удалось получить краткое описание объявления для того, чтобы достать из него id. В режиме: '" . $this->mode . "'. Попытка достать идентификатор №" . $count);
                return FALSE;
            }
        }

        // Сохраняем в БД идентификаторы первых 3-х объявлений, с которых началась данная успешная сессия парсинга
        return DBconnect::updateLastSuccessfulHandledAdvertsId($this->mode, $this->newSuccessfulHandledAdvertsId);

    }

    /**
     * Метод для получения DOM страницы
     * @param string $url - адрес страницы, которую должен вернуть метод
     * @param string $post - строка с пост параметрами для запроса
     * @param string $cookieFileName - название файла, в котором хранятся куки для использования в запросе
     * @param bool $proxy - логический параметр, указывает, нужно ли использовать анонимный прокси-сервер
     * @return bool|mixed возвращает DOM полученной страницы
     */
    protected function curlRequest($url, $post = "", $cookieFileName = "", $proxy = FALSE) {

        // Инициализация библиотеки curl.
        if (!($ch = curl_init())) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->curlRequest():1 Ошибка при инициализации curl. Не удалось получить страницу с сайта bazaB2B по адресу: " . $url);
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_URL, $url); // Устанавливаем URL запроса
        curl_setopt($ch, CURLOPT_HEADER, false); // При значении true CURL включает в вывод результата заголовки, которые нам не нужны (мы их на сервере не обрабатываем).
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // При значении = true полученный код страницы возвращается как результат выполнения curl_exec.
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Следовать за редиректами
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Максимальное время ожидания ответа от сервера в секундах
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); // Установим значение поля User-agent для маскировки под обычного пользователя
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, '195.209.100.4:3128'); // адрес прокси-сервера для анонимности
            //curl_setopt($ch, CURLOPT_PROXYUSERPWD,'user:pass'); // если необходимо предоставить имя пользователя и пароль для прокси
        }
        if ($cookieFileName != "") {
            if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "") $websiteRoot = $_SERVER['DOCUMENT_ROOT']; else $websiteRoot = "/var/www/dimau/data/www/svobodno.org"; // так как cron не инициализирует переменную окружения $_SERVER['DOCUMENT_ROOT'] (а точнее инициализирует ее пустой строкой), приходиться использовать костыль
            curl_setopt($ch, CURLOPT_COOKIEJAR, $websiteRoot . '/logs/' . $cookieFileName); // Сохранять куки в указанный файл
            curl_setopt($ch, CURLOPT_COOKIEFILE, $websiteRoot . '/logs/' . $cookieFileName); // При запросе передавать значения кук из указанного файла
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
     * Метод нормализует телефонный номер, приводя его к виду: 9221431615 или 3432801542
     * @param string $phoneNumber строка, содержащая телефонный номер в формате UTF-8
     * @param string $mode строка, содержащая название города, для которого мы нормализуем номер телефона.
     * @return string|bool строка, соответствующая телефонному номеру в нормализованном виде, либо FALSE в случае неудачи. К сожалению, телефонный номер не может быть представлен как число из-за ограничения на максимальное целое число в php
     */
    protected function phoneNumberNormalization($phoneNumber, $mode = "Екатеринбург") {

        // Убираем лишние символы
        $phoneNumber = str_replace(array(" ", "+", "(", ")", "-"), "", $phoneNumber);

        // Количество цифр в телефонном номере
        $phoneNumberLength = iconv_strlen($phoneNumber, 'UTF-8');

        // Убираем лишнее или дополняем телефонный номер до нормального состояния
        if ($phoneNumberLength == 11) {

            // Если номер содержит 8 или 7 в качестве 11-ой цифры - убираем ее
            $firstChar = mb_substr($phoneNumber, 0, 1, 'UTF-8');
            if ($firstChar == "7" || $firstChar == "8") $phoneNumber = mb_substr($phoneNumber, 1, 10, 'UTF-8');

        } elseif ($phoneNumberLength == 7) {

            // Если номер слишком короткий (городкой для Екб), то дополняем его кодом города 343
            if ($mode == "Екатеринбург") $phoneNumber = "343" . $phoneNumber;
        }

        // Проверим - цифр в телефонном номере должно быть ровно 10
        if (preg_match('/^[0-9]{10}$/', $phoneNumber)) {

            // Возвращаем результат
            return $phoneNumber;

        } else {

            // Получен некорректный телефонный номер
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->phoneNumberNormalization():1 получен некорректный телефонный номер: ".$phoneNumber);
            return FALSE;
        }

    }

    /**
     * Возвращает ключевые данные о телефонном номере (статус обладателя [агент/собственник/..] и дата последнего использования)
     * @return array
     */
    public function getDataAboutPhoneNumber() {

        // Вернем ассоциативный массив с данными о телефоне, если он ранее был уже сохранен в БД, либо пустой массив
        $res = DBconnect::selectKnownPhoneNumber($this->phoneNumber);

        //TODO: test
        Logger::getLogger(GlobFunc::$loggerName)->log("Тестирование парсера e1: проверили телефонный номер по БД - ".json_encode($res));

        return $res;
    }

    /**
     * Запоминает новый телефонный номер и его статус в БД
     * @param string $status строка, содержащая тип контактного лица по этому телефонному номеру
     * @return bool Возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
    public function newKnownPhoneNumber($status) {

        // Проверка наличия необходимых данных
        if (!isset($this->phoneNumber)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->newKnownPhoneNumber():1 не указан телефонный номер");
            return FALSE;
        }
        if (!isset($status) || ($status != "агент" && $status != "собственник" && $status != "арендатор")) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->newKnownPhoneNumber():2 не указан статус контактного лица для телефонного номера или он не корректен");
            return FALSE;
        }

        // Добавляем данные в БД и сразу возвращаем результат
        return DBconnect::insertKnownPhoneNumber(array("phoneNumber" => $this->phoneNumber, "status" => $status, "dateOfLastPublication" => time()));
    }

    /**
     * Обновляет дату последнего использования для телефонного номера
     * @return bool Возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
    public function updateDateKnownPhoneNumber() {
        return DBconnect::updateKnownPhoneNumberDate($this->phoneNumber, time());
    }

    /**
     * Изменяет статус контактного лица по телефонному номеру
     * @param string $status новый статус контактного лица по телефонному номеру
     * @return bool Возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
    public function changeStatusKnownPhoneNumber($status) {
        return DBconnect::updateKnownPhoneNumberStatus($this->phoneNumber, $status);
    }

    /*************************************************************************************
     * ПЕРЕОПРЕДЕЛЯЕМЫЕ В КЛАССАХ ПОТОМКАХ ФУНКЦИИ
     * Перечисление ключевых функций, которые должны быть опеределены в каждмо классе потомке, внутри базового класса позволяет вызывать эти методы классов потомков из методов базового класса (это называется виртуальные функции, полиморфизм)
     ************************************************************************************/

    protected function readHandledAdverts() {}

    protected function loadNextAdvertsList() {}

    protected function getNextAdvertShortDescription() {}

    protected function loadFullAdvertDescription() {}

    protected function parseFullAdvert() {}

    protected function isAdvertAlreadyHandled() {}

    protected function isTooLateDate() {}

    protected function setAdvertIsHandled() {}
}