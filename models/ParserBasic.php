<?php
/**
 * Родительский класс для остальных парсеров конкретных интернет-ресурсов
 */

class ParserBasic {

    protected $mode; // Режим работы парсера (определяется сайтом, который парсим)
    protected $login; // Логин для доступа к сайту (используется только, если он необходим)
    protected $password; // Пароль для доступа к сайту (используется только, если он необходим)
    protected $actualDayAmountForAdvert = 2; // Количество дней, за которые парсер проверяет объявления из списка. Если ему попадается объявление, опубликованное ранее данного количества дней назад, то он прекращает свою работу. 1 - только сегодняшние объявления, 2 - сегодняшние и вчерашние и так далее.
    protected $handledAdverts = NULL; // Содержит ассоциативный массив с идентификаторами обработанных объявлений за срок = $actualDayAmountForAdvert от текущего момента
    protected $advertsListDOM; // DOM-объект страницы со списком объявлений, обрабатываемой в данный момент
    protected $advertsListNumber = 0; // Номер страницы со списком объявлений, обрабатываемой в данный момент (находится в $advertsListDOM). Первоначальное значение = 0 означает, что в переменной $advertsListDOM еще нет DOM объекта (мы еще не получали страницу со списком объявлений)
    protected $advertShortDescriptionDOM; // DOM-объект строки таблицы (единичного элемента списка объявлений). Содержит краткое описание объявления, обрабатываемого в данный момент
    protected $advertShortDescriptionNumber = -1; // Номер строки таблицы (единичного элемента списка объявлений), обрабатываемого в данный момент (находится в $advertShortDescriptionDOM) Непосредственно перед получением сведений по новому объявлению счетчик увеличивается на 1
    protected $id; // Идентификатор объявления на сайте
    protected $advertFullDescriptionDOM; // DOM-объект страницы с подробным описанием объявления

    /**
     * КОНСТРУКТОР
     */
    protected function __construct() {}

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
            curl_setopt($ch, CURLOPT_PROXY, '195.34.237.159:3128'); // адрес прокси-сервера для анонимности
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

}