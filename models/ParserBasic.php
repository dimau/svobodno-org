<?php
/**
 * Родительский класс для остальных парсеров конкретных интернет-ресурсов
 */

class ParserBasic {

    protected $mode; // Режим работы парсера (определяется сайтом, который парсим и категорией объявлений, которые в этот раз парсятся на данном сайте). Допустимые варианты: e1Kv1k (e1 квартиры однокомнатные), e1Kv2k, e1Kv3k, e1Kv4k, e1Kv5k, e1Kom (e1 комнаты), 66ruKv (66.ru квартиры), 66ruKom (66.ru комнаты), bazab2b (сайт bazaB2B)
    protected $actualDayAmountForAdvert = 2; // Количество дней, за которые парсер проверяет объявления из списка. Если ему попадается объявление, опубликованное ранее данного количества дней назад, то он прекращает свою работу. 1 - только сегодняшние объявления, 2 - сегодняшние и вчерашние и так далее.
    protected $minAdvertsListForHandling = 0; // Упрощенно этот параметр можно воспринимать как минмальное количество страниц со списками объявлений, которые должны быть обработаны за 1 сессию парсинга. Более точное определение: если текущий $this->advertsListNumber <= $this->minAdvertsListForHandling, то обработка объявлений на этой странице будет продолжена, даже несмотря на наступление признаков прекращения парсинга (поздняя дата публикации текущего обрабатываемого объявления или нахождение объявления из разряда lastSuccessfulHandledAdvertsId). Этот параметр позволяет успешно игнорировать признаки наступления прекращения парсинга для рекламных объявлений, которые обычно постоянно находятся вверху списка (пример - avito), а также игнорировать то, что среди рекламных самых верхних в списке объявлений могут попадаться объявления с крайне старой датой публикации (пример - avito).
    protected $maxAdvertsListForHandling = 10; // Упрощенно этот параметр можно воспринимать как максимальное количество страниц со списками объявлений, которые могут быть загружены парсером за 1 сессию парсинга. Более точное определение: если текущий $this->advertsListNumber >= $this->maxAdvertsListForHandling, то работа парсера прекращается. Этот параметр обеспечивает защиту от зависания в работе парсера, когда по той или иной причине он может как сумасшедший начать запрашивать без остановки все новые и новые страницы со списками объявлений.
    protected $login; // Логин для доступа к сайту (используется только, если он необходим)
    protected $password; // Пароль для доступа к сайту (используется только, если он необходим)
    protected $lastSuccessfulHandledAdvertsId; // 3 идентификатора для последних успешно обработанных объявлений (это те объявления с которых в прошлый успешный раз начал обработку парсер, а значит дальше которых не имеет смысла ходить текущему парсеру - все, что расположено дальше этих 3-х объявлений уже успешно обработано)
    protected $newSuccessfulHandledAdvertsId = array(); // Идентификаторы первых 3-х объявлений, которые обрабатываются в этой сессии парсера. В случае успешного окончания парсинга они заменят в БД $lastSuccessfulHandledAdvertsId.
    protected $handledAdverts = NULL; // Содержит массив с идентификаторами обработанных объявлений за срок = $actualDayAmountForAdvert от текущего момента
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
     * Получение списка уже обработанных объявлений с сайта источника за указанный период (период указывается в датах публикации объявлений)
     */
    protected function readHandledAdverts() {

        // Получить идентификаторы всех обработанных объявлений за период времени от текущего дня и на actualDayAmountForAdvert дней назад. Например, если actualDayAmountForAdvert = 1, то метод вернет идентификаторы всех объявлений, бработанных за текущий день. Если actualDayAmountForAdvert = 2, то метод вернет идентификаторы всех объявлений, обработанных за текущий и вчерашний день.
        $finalDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $finalDate = $finalDate->format('d.m.Y');
        $initialDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $initialDate->modify('-' . $this->actualDayAmountForAdvert . ' day');
        $initialDate = $initialDate->format('d.m.Y');
        $this->handledAdverts = DBconnect::selectHandledAdverts($this->mode, $initialDate, $finalDate);

        // Если получить список уже обработанных объявлений получить не удалось, то прекращаем выполнение скрипта от греха подальше
        if ($this->handledAdverts === NULL || !is_array($this->handledAdverts)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->readHandledAdverts:1 Парсинг в режиме " . $this->mode . " остановлен, так как не удалось получить сведения о ранее загруженных объявлениях");
            DBconnect::closeConnectToDB();
            exit();
        }
    }

    /**
     * Проверяет, совпало ли данное объявление из одним из трех объявлений, первыми обработанных в прошлый успешный раз парсинга
     * @return bool TRUE, в случае если данное объявление относится к трем объявлениям, первыми обработанным в прошлый успешный раз парсинга. FALSE в противном случае
     */
    public function isAdvertLastSuccessfulHandled() {

        // Если парсер работает со страницей списка объявлений, номер которой меньше или равен номеру страницы , до которого парсер обязан доходить за 1 сессию парсинга, то данная причина окончания парсинга не применяется
        if ($this->advertsListNumber <= $this->minAdvertsListForHandling) return FALSE;

        foreach ($this->lastSuccessfulHandledAdvertsId as $value) {
            if ($value == $this->id) {
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
        if ($this->advertsListNumber >= $this->maxAdvertsListForHandling) {
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->isTooManyAdvertsLists():1 Достигли лимита в " . $this->maxAdvertsListForHandling . " страниц со списком объявлений за 1 раз работы парсера в режиме: ".$this->mode);
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
            Logger::getLogger(GlobFunc::$loggerName)->log("ParserBasic.php->curlRequest():1 Ошибка при инициализации curl. Не удалось получить страницу по адресу: " . $url);
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_URL, $url); // Устанавливаем URL запроса
        curl_setopt($ch, CURLOPT_HEADER, false); // При значении true CURL включает в вывод результата заголовки, которые нам не нужны (мы их на сервере не обрабатываем).
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // При значении = true полученный код страницы возвращается как результат выполнения curl_exec.
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Следовать за редиректами
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Максимальное время ожидания ответа от сервера в секундах
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); // Установим значение поля User-agent для маскировки под обычного пользователя
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, '188.64.128.1:3128'); // адрес прокси-сервера для анонимности
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
        if (!isset($status) || ($status != "агент" && $status != "собственник" && $status != "арендатор" && $status != "не определен")) {
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

        // В качестве даты использования объявления указываем текущую. В отличие от способа с указанием даты публикации объявления, фиксация объявления в базе с указанием текущей даты позволит избежать многократной повторной обработки оплаченных объявлений и поднятых вверх списка (несмотря на древнюю дату публикации) на сайтах avito, 66.ru ...
        $date = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $date = $date->format("d.m.Y");

        // Сохраняем идентификаторы объявления в БД и выдаем результат
        return DBconnect::insertHandledAdvert($this->mode, $this->id, $date);
    }

    /**
     * Метод проверяет, похожи ли 2 адреса
     * @param $firstAddress первый адрес для проверки
     * @param $secondAddress второй адрес для проверки
     * @return bool возвращает TRUE в случае хорошего коэффициента совпадения (адреса одинаковые) и FALSE в противном случае
     */
    public function isAddressSimilar($firstAddress, $secondAddress) {

        // Преобразуем строки с адресами в нижний регистр
        $firstAddress = strtolower($firstAddress);
        $secondAddress = strtolower($secondAddress);

        // Убираем лишние символы
        $firstAddress = str_replace(array("ул.", "ул ", "улица ", ",", ".", " "), "", $firstAddress);
        $secondAddress = str_replace(array("ул.", "ул ", "улица ", ",", ".", " "), "", $secondAddress);

        // Разбираем строки в массивы посимвольно
        $firstAddress = str_split($firstAddress);
        $secondAddress = str_split($secondAddress);

        // Инициализируем счетчик совпадений букв в адресах
        $counter = 0;

        // Берем поочередно буквы из первого массива и удаляем первую попавшуюся такую же букву во втором, считаем совпадения
        foreach ($firstAddress as $char) {
            for ($i = 0, $s = count($secondAddress); $i < $s; $i++) {
                if ($char == $secondAddress[$i]) {
                    $counter++;
                    unset($secondAddress[$i]);
                    $secondAddress = array_values($secondAddress);
                    break;
                }
            }
        }

        // Считаем коэффициент совпадения строк (сколько букв из первого адреса присутствуют во втором по отношению к их общему количеству в первом адресе)
        $ratio = $counter / count($firstAddress);

        // Если коэффициент совпадения адресов выше, чем 0.9, то можно считать их одинаковыми
        if ($ratio >= 0.9) return TRUE; else return FALSE;
    }

    /*************************************************************************************
     * ПЕРЕОПРЕДЕЛЯЕМЫЕ В КЛАССАХ ПОТОМКАХ ФУНКЦИИ
     * Перечисление ключевых функций, которые должны быть опеределены в каждмо классе потомке, внутри базового класса позволяет вызывать эти методы классов потомков из методов базового класса (это называется виртуальные функции, полиморфизм)
     ************************************************************************************/

    protected function loadNextAdvertsList() {}

    protected function getNextAdvertShortDescription() {}

    protected function loadFullAdvertDescription() {}

    protected function parseFullAdvert() {}

    protected function isTooLateDate() {}
}