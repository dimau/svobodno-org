<?php
/**
 * Класс для проверки актуальности объявлений, сграбленных с чужих ресурсов
 */

class AdvertsRelevanceChecker {

    /**
     * КОНСТРУКТОР
     */
    private function __construct() {

    }


    public static function checkAdvertRelevance($sourceURL) {

        // Понимаем, какой именно ресурс является источником для данного объявления
        if (preg_match('/^http://www.e1.ru/', $sourceURL)) {
            $sourceName = "e1.ru";
        } elseif (preg_match('/^http://www.66.ru', $sourceURL)) {
            $sourceName = "66.ru";
        } else {
            Logger::getLogger(GlobFunc::$loggerName)->log("AdvertsRelevanceChecker->checkAdvertRelevance():1 Не удалось получить название ресурса источника для " . $sourceURL);
            return FALSE; //TODO: или что-то другое возвращать при ошибке?
        }

        // Получаем страницу с подробным описание объявления
        $pageHTML = AdvertsRelevanceChecker::curlRequest($sourceURL, "", "", FALSE);
        // Если получить HTML страницы не удалось
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("AdvertsRelevanceChecker.php->loadNextAdvertsList():3 Не удалось получить страницу с подробным описанием объявления");
            return FALSE;
        }
        // Получаем DOM-объект
        $pageHTML = str_get_html($pageHTML);

        // Вызываем соответствующий метод для проверки признаков неактуального объявления (снятого с публикации на ресурсе источнике)
        switch ($sourceName) {
            case "e1.ru":
                AdvertsRelevanceChecker::checkE1Advert();
                break;
            case "66.ru":

                break;
        }




    }

    public static function checkE1Advert() {

    }

    /**
     * Метод для получения DOM страницы (такой же как у ParserBasic.php)
     * @param string $url - адрес страницы, которую должен вернуть метод
     * @param string $post - строка с пост параметрами для запроса
     * @param string $cookieFileName - название файла, в котором хранятся куки для использования в запросе
     * @param bool $proxy - логический параметр, указывает, нужно ли использовать анонимный прокси-сервер
     * @return bool|mixed возвращает DOM полученной страницы
     */
    private function curlRequest($url, $post = "", $cookieFileName = "", $proxy = FALSE) {

        // Инициализация библиотеки curl.
        if (!($ch = curl_init())) {
            Logger::getLogger(GlobFunc::$loggerName)->log("AdvertsRelevanceChecker->curlRequest():1 Ошибка при инициализации curl. Не удалось получить страницу по адресу: " . $url);
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_URL, $url); // Устанавливаем URL запроса
        curl_setopt($ch, CURLOPT_HEADER, false); // При значении true CURL включает в вывод результата заголовки, которые нам не нужны (мы их на сервере не обрабатываем).
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // При значении = true полученный код страницы возвращается как результат выполнения curl_exec.
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Следовать за редиректами
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Максимальное время ожидания ответа от сервера в секундах
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); // Установим значение поля User-agent для маскировки под обычного пользователя
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, '84.47.172.129:3128'); // адрес прокси-сервера для анонимности
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