<?php
/**
 * Класс для проверки актуальности объявлений, сграбленных с чужих ресурсов
 */

class AdvertsRelevanceChecker {

    /**
     * КОНСТРУКТОР
     */
    private function __construct() { }

    /**
     * Проверяет актуальность объявления, подробное описание которого на ресурсе источнике расположено по адресу $sourceURL
     * @param $sourceURL ссылка на подробное описание объявления на ресурсе источнике
     * @return bool TRUE в случае, если объявление еще актуально (или проверить его актуальность не удалось) и FALSE в противном случае
     */
    public static function checkAdvertRelevance($sourceURL) {

        // Понимаем, какой именно ресурс является источником для данного объявления
        if (preg_match('~^http://www\.e1\.ru~', $sourceURL)) {
            $sourceName = "e1.ru";
        } elseif (preg_match('~^http://www\.66\.ru~', $sourceURL)) {
            $sourceName = "66.ru";
        } elseif (preg_match('~^http://ekaterinburg\.sve\.slando\.ru~', $sourceURL)) { // TODO: только для Екатеринбурга
            $sourceName = "slando.ru";
        } elseif (preg_match('~^http://www\.avito\.ru~', $sourceURL)) {
            $sourceName = "avito.ru";
        } else {
            Logger::getLogger(GlobFunc::$loggerName)->log("AdvertsRelevanceChecker->checkAdvertRelevance():1 Не удалось получить название ресурса источника для " . $sourceURL);
            return TRUE;
        }

        // Получаем страницу с подробным описание объявления
        $pageHTML = AdvertsRelevanceChecker::curlRequest($sourceURL, "", "", FALSE);
        // Если получить HTML страницы не удалось
        if (!$pageHTML) {
            Logger::getLogger(GlobFunc::$loggerName)->log("AdvertsRelevanceChecker.php->checkAdvertRelevance():2 Не удалось получить страницу с подробным описанием объявления по адресу: " . $sourceURL);
            return TRUE;
        }

        // Если это необходимо, меняем кодировку страницы перед получением ее DOM модели
        if ($sourceName == "e1.ru") {
            // Для e1 меняем кодировку с windows-1251 на utf-8
            $pageHTML = iconv("windows-1251", "UTF-8", $pageHTML);
        }

        // Получаем DOM-объект для страницы
        $sourcePage = str_get_html($pageHTML);
        if (!isset($sourcePage)) {
            Logger::getLogger(GlobFunc::$loggerName)->log("AdvertsRelevanceChecker.php->checkAdvertRelevance():3 не удалось разобрать страницу с полным описанием объявления");
            return TRUE;
        }

        // Вызываем соответствующий метод для проверки признаков неактуального объявления (снятого с публикации на ресурсе источнике)
        switch ($sourceName) {
            case "e1.ru": return AdvertsRelevanceChecker::checkAdvertForE1($sourcePage);
            case "66.ru": return AdvertsRelevanceChecker::checkAdvertFor66($sourcePage);
            case "slando.ru": return AdvertsRelevanceChecker::checkAdvertForSlando($sourcePage);
            case "avito.ru": return AdvertsRelevanceChecker::checkAdvertForAvito($sourcePage);
            default:
                Logger::getLogger(GlobFunc::$loggerName)->log("AdvertsRelevanceChecker->checkAdvertRelevance():4 Не существует метода проверки актуальности для ресурса с таким названием: " . $sourceName . ". Таким образом, не удалось обработать объявление по адресу: " . $sourceURL);
                return TRUE;
        }
    }

    /**
     * Проверяет наличие признаков неактуальности на странице с подробным описанием объявления с сайта e1.ru
     * @param $sourcePage DOM модель страницы с подробным описанием текущего проверяемого объявления (с сайта источника)
     * @return bool TRUE, если объявление остается актуальным и FALSE в противном случае (если обнаружены признаки неактуальности)
     */
    private static function checkAdvertForE1($sourcePage) {

        $pretenders = $sourcePage->find("td[valign=top]");
        foreach ($pretenders as $pretender) {
            if (($one = $pretender->children(4)) !== NULL) {
                if (($one->plaintext == "Извините, объявление было удалено или потеряло актуальность") OR
                    ((($one = $one->children(0)) !== NULL) AND ($one->plaintext == "Извините, объявление потеряло акуальность или было удалено"))
                   )
                {
                    // Объявление потеряло свою актуальность
                    return FALSE;
                }
            }
        }

        // Признаки неактуальности у объявления не обнаружены
        return TRUE;
    }

    /**
     * Проверяет наличие признаков неактуальности на странице с подробным описанием объявления с сайта 66.ru
     * @param $sourcePage DOM модель страницы с подробным описанием текущего проверяемого объявления (с сайта источника)
     * @return bool TRUE, если объявление остается актуальным и FALSE в противном случае (если обнаружены признаки неактуальности)
     */
    private static function checkAdvertFor66($sourcePage) {

        $pretender = $sourcePage->find(".steps_head", 0);
        if (isset($pretender) && $pretender->plaintext == "404 - нет такой страницы ") {
            // Объявление потеряло свою актуальность
            return FALSE;
        }

        $pretender = $sourcePage->find(".b-content-card_item__hightline-20", 0)->parent()->children(8);
        if (isset($pretender) && preg_match('~Это объявление устарело или потеряло актуальность~', $pretender->innertext)) {
            // Объявление потеряло свою актуальность
            return FALSE;
        }

        // Признаки неактуальности у объявления не обнаружены
        return TRUE;
    }

    /**
     * Проверяет наличие признаков неактуальности на странице с подробным описанием объявления с сайта slando.ru
     * @param $sourcePage DOM модель страницы с подробным описанием текущего проверяемого объявления (с сайта источника)
     * @return bool TRUE, если объявление остается актуальным и FALSE в противном случае (если обнаружены признаки неактуальности)
     */
    private static function checkAdvertForSlando($sourcePage) {

        if ((($pretender = $sourcePage->find(".box note", 0)) !== NULL) AND
            (($pretender = $pretender->children(0)) !== NULL) AND
            (preg_match('~Это объявление закрыто\. Оно более не актуально\.~', $pretender->innertext))
        ) {
            // Объявление потеряло свою актуальность
            return FALSE;
        }

        // Признаки неактуальности у объявления не обнаружены
        return TRUE;
    }

    /**
     * Проверяет наличие признаков неактуальности на странице с подробным описанием объявления с сайта avito.ru
     * @param $sourcePage DOM модель страницы с подробным описанием текущего проверяемого объявления (с сайта источника)
     * @return bool TRUE, если объявление остается актуальным и FALSE в противном случае (если обнаружены признаки неактуальности)
     */
    private static function checkAdvertForAvito($sourcePage) {

        if ($sourcePage->f) {
            // Объявление потеряло свою актуальность
            return FALSE;
        }

        // Признаки неактуальности у объявления не обнаружены
        return TRUE;
    }

    /**
     * Метод для получения DOM страницы (такой же как у ParserBasic.php)
     * @param string $url - адрес страницы, которую должен вернуть метод
     * @param string $post - строка с пост параметрами для запроса
     * @param string $cookieFileName - название файла, в котором хранятся куки для использования в запросе
     * @param bool $proxy - логический параметр, указывает, нужно ли использовать анонимный прокси-сервер
     * @return bool|mixed возвращает DOM полученной страницы
     */
    private static function curlRequest($url, $post = "", $cookieFileName = "", $proxy = FALSE) {

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