<?php
/**
 * Полуавтоматический парсинг сайта bazab2b.ru
 * Парсит объявления только за текущий день!
 * Сценарий должен запускаться автоматически каждые несколько минут с помощью cron
 * Задача - выявить новые объявления появившиеся на сайте http://bazab2b.ru (еще не сохранявшиеся в мою базу)
 * Выявленные новые объявления сохраняются в базу и отправляется e-mail оператору, которое предлагает пройти по ссылке отредактировать данные и опубликовать объявление на моем портале
 *
 */

// Подключаем необходимые модели, классы
if (!isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == "") $_SERVER['DOCUMENT_ROOT'] = "/var/www/dimau/data/www/svobodno.org"; // так как cron не инициализирует переменную окружения $_SERVER['DOCUMENT_ROOT'] (а точнее инициализирует ее пустой строкой), приходиться использовать костыль
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/simple_html_dom.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/class.phpmailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/ParserBazaB2B.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';

// Фиксируем в логах факт запуска парсинга
Logger::getLogger(GlobFunc::$loggerName)->log("parseURLbazab2b.php:1 Запуск процесса парсинга bazaB2B");

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) {
    Logger::getLogger(GlobFunc::$loggerName)->log("parseURLbazab2b.php:1 Ошибка инициализации соединения с БД:");
    exit();
}

// Создаем модель парсера, который собственно и содержит все необходимые сведения и методы для парсинга сайта bazaB2B
$parser = new ParserBazaB2B();

/********************************************************************************
 * Парсим сайт bazab2b.ru
 *******************************************************************************/

//TODO: test
function curl($URLServer, $postdata = "", $cookieFile = null, $proxy = true, $proxyRetry = 0) {
    global $proxyCache;
    //sleep(20);
    $agent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.12) Gecko/20101027 Ubuntu/10.10 (maverick) Firefox/3.6.12";
    $cURL_Session = curl_init();

    curl_setopt($cURL_Session, CURLOPT_URL, $URLServer);
    curl_setopt($cURL_Session, CURLOPT_USERAGENT, $agent);
    if ($postdata != "") {
        curl_setopt($cURL_Session, CURLOPT_POST, 1);
        curl_setopt($cURL_Session, CURLOPT_POSTFIELDS, $postdata);
    }
    curl_setopt($cURL_Session, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($cURL_Session, CURLOPT_FOLLOWLOCATION, 1);
    if ($cookieFile != null) {
        curl_setopt($cURL_Session, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($cURL_Session, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($proxy == true) {
        if ($proxyCache == "") {
            $c = curl("http://www.proxylist.net/", "", null, false);
            preg_match_all("/([0-9]*).([0-9]*).([0-9]*).([0-9]*):([0-9]*)/", $c, $matches);
            $matches = $matches [0];
            $proxyCache = $matches [rand(0, (count($matches) - 1))];
        }

        echo    "proxy:$proxyCache<br>";

        list ($proxy_ip, $proxy_port) = explode(":", $proxyCache);
        curl_setopt($cURL_Session, CURLOPT_PROXYPORT, $proxy_port);
        curl_setopt($cURL_Session, CURLOPT_PROXYTYPE, 'HTTP');
        curl_setopt($cURL_Session, CURLOPT_PROXY, $proxy_ip);
    }

    $result = curl_exec($cURL_Session);

    if ($result === false) {
        echo    'Curl error: ' . curl_error($cURL_Session) . "<br>";

        if ($proxy == true && $proxyRetry <= 5) curl($URLServer, $postdata = "", $cookieFile, $proxy, $proxyRetry++);
    }
    curl_close($cURL_Session);

    return $result;
}

//TODO: test
//$res = curl("http://e1.ru", "", NULL, TRUE, 0);
//exit($res);

// Для начала необходимо отправить параметры пользователя для авторизации
$parser->authorization();

// В цикле закачиваем страницы со списками объявлений и обрабатываем каждую из них (начиная с самой актуальной - первой).
// Вплоть до момента, пока не доберемся до объявления со временем публикации позже, чем наша граница актуальности (задается в классе ParserBazaB2B в переменной actualDayAmountForAdvert)
while ($parser->loadNextAdvertsList()) {

    // Перебираем последовательно все объявления с текущей страницы, содержащей список объявлений (начиная с самого актуального объявления по дате публикации).
    // Формируем соответствующие объявления в нашей базе (если ранее они не были сформированы).
    while ($parser->getNextAdvertShortDescription()) {

        // Если мы достигли конца временного диапазона актуальности объявлений, то необходимо остановить обработку страницы на этом объявлении
        if ($parser->isStopHandling()) {
            DBconnect::closeConnectToDB();
            exit();
        }

        // Проверить, работали ли мы с этим объявлением уже. Если да, то сразу переходим к следующему
        if ($parser->isAdvertAlreadyHandled()) {
            continue;
        }

        // Загрузим подробные сведения по этому объявлению
        if (!$parser->loadFullAdvertDescription()) {
            continue;
        }

        // Преобразуем подробные сведения по объявлению к виду ассоциативного массива
        if (!($paramsArr = $parser->parseFullAdvert())) {
            $parser->authorization();
            continue;
        }

        // Инициализируем модель и сохраняем данные в БД
        $property = new Property($paramsArr);
        $property->setCompleteness("0");
        $property->setStatus("не опубликовано");
        $property->setOwnerLogin("owner"); // Используем в качестве логина собственника логин служебного собственника owner, на которого сохраняются все чужие объявления
        $correctSaveCharacteristicToDB = $property->saveCharacteristicToDB("new");

        // Добавим объявление в список успешно обработанных, чтобы избежать в будущем его повторной обработки
        if (!$parser->setAdvertIsHandled()) {
            DBconnect::closeConnectToDB();
            exit();
        }

        // Оповещаем операторов о новом объявлении на сайте bazab2b.ru
        $subject = 'Объявление на bazab2b.ru';
        $msgHTML = "Новое объявление на bazab2b.ru: <a href='http://svobodno.org/editadvert.php?propertyId=" . $property->getId() . "'>" . $property->getAddress() . "</a>";
        GlobFunc::sendEmailToOperator($subject, $msgHTML);
    }

    // Когда мы переберем все объявления на текущей странице списка объявлений, то следующим шагом автоматически загрузится следующая страница - и все по новой
}

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();