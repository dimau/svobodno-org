<?php
/**
 * Сценарий должен запускаться автоматически каждые несколько минут с помощью cron
 * Задача - выявить новые объявления появившиеся на сайте http://bazab2b.ru (еще не сохранявшиеся в мою базу)
 * Выявленные новые объявления сохраняются в базу и отправляется e-mail оператору, которое предлагает пройти по ссылке отредактировать данные и опубликовать объявление на моем портале
 *
 */

// Устанавливаем логин и пароль для доступа к bazaB2B
$login = "testagent";
$password = "tsettest";

// Подключаем необходимые модели, классы
if (!isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == "") $_SERVER['DOCUMENT_ROOT'] = "/var/www/dimau/data/www/svobodno.org"; // так как cron не инициализирует переменную окружения $_SERVER['DOCUMENT_ROOT'] (а точнее инициализирует ее пустой строкой), приходиться использовать костыль
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/simple_html_dom.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/class.phpmailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) {
    Logger::getLogger(GlobFunc::$loggerName)->log("parseURLbazab2b.php:1 Ошибка инициализации соединения с БД:");
    exit();
}

/********************************************************************************
 * Функция для парсинга данных по конкретному объявлению с сайта bazab2b.ru
 * Возвращает ассоциированный массив с параметрами объявления
 *******************************************************************************/

function parseFullAdvert($html) {
    // Валидация входных параметров
    if (!isset($html)) return array();

    // Преобразуем HTML строку в DOM-объект, пригодный для парсинга
    $html = str_get_html($html);

    // Собираем массив, каждый член которого - некоторый параметр объекта недвижимости
    $tableRows = $html->find("table tr");

    // Готовим массив, в который сложим параметры объявления
    $params = array();

    // Перебираем все имеющиеся параметры объявления и заполняет соответствующие параметры ассоциативного массива
    foreach ($tableRows as $oneParam) {
        // Получим название параметра
        $paramName = $oneParam->find("td b", 0)->plaintext;

        // Стоимость аренды
        if ($paramName == "Цена:") {
            $params['costOfRenting'] = intval($oneParam->find("td", 1)->plaintext);
            continue;
        }

        // Количество комнат
        if ($paramName == "Комнат:") {
            $params['amountOfRooms'] = intval($oneParam->find("td", 1)->plaintext);
            continue;
        }

        // Смежные комнаты
        if ($paramName == "Смежных комнат:") {
            $params['adjacentRooms'] = "да";
            $params['amountOfAdjacentRooms'] = intval($oneParam->find("td", 1)->plaintext);
            continue;
        }

        // Смежные комнаты
        //TODO: реализовать
        if ($paramName == "Площадь:") {
            continue;
        }

        // Этаж
        if ($paramName == "Этаж:") {
            $floorArr = explode("/", $oneParam->find("td", 1)->plaintext);
            if (isset($floorArr[0])) $params['floor'] = intval($floorArr[0]);
            if (isset($floorArr[1])) $params['totalAmountFloor'] = intval($floorArr[1]);
            continue;
        }

        // Источник
        $paramName = $oneParam->find("td", 0)->plaintext;
        if ($paramName == "Источник") {
            $params['comment'] = $oneParam->find("td a font b", 0)->plaintext;
            if ($params['comment'] == "" && $oneParam->find("td", 1)->plaintext == "Добавлено на наш сайт") $params['comment'] = "bazaB2B"; // Для объявлений добавленных напрямую в базуБ2Б
            continue;
        }
    }


    return $params;
}

/********************************************************************************
 * Функции, позволяющие достать HTML с сайта bazab2b.ru
 *******************************************************************************/

// Функция возвращает страницу с таблицей всех объявлений сайта bazaB2B
function getAdvertsListHTML() {
    // Иницализация библиотеки curl.
    if (!($ch = curl_init())) return "";

    //Устанавливаем URL запроса
    curl_setopt($ch, CURLOPT_URL, 'http://bazab2b.ru/?pagx=baza');
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

    return $data;
}

// Функция возвращает страницу с подробным описание опеределенного объявления сайта bazaB2B
function getAdvertDescriptionHTML($href) {
    // Иницализация библиотеки curl.
    if (!($ch = curl_init())) return "";

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

    return $data;
}

/********************************************************************************
 * Парсим сайт bazab2b.ru
 *******************************************************************************/

// Получаем параметры последнего занесенного в нашу базу объявления
$lastTime = "18:23";
$lastDate = "13.01";
$lastAddress = "***";

// Инициализируем счетчик текущего объявления (считаем за 0 самое последнее по времени публикации на сайте bazab2b.ru объявление)
$n = 0;

//TODO: test
$html = getAdvertsListHTML();
if ($html == "") {
    // TODO: записать в лог ошибку получения данных
    exit();
}
// Преобразуем полученную html строку в DOM-объект
$html = str_get_html($html);
$currentShortAdvert = $html->find('.poisk .chr-wite', 0); // Берем первое объявление из списка и работаем с ним
$href = $currentShortAdvert->find('.modal', 0)->href; // Получаем ссылку на страницу подробного описания
$html = getAdvertDescriptionHTML($href);
// Получаем подробные данные по первому объявлению
exit(json_encode(parseFullAdvert($html)));

// Перебираем последовательно все последние объявления (начиная с самого последнего) и формируем соответствующие объявления в нашей базе. Пока не дойдем до конца списка или до объявления, которое уже было опубликовано на нашем портале
while ($currentShortAdvert = $html->find('.poisk .chr-wite', $n)) {
    // Выясняем, публиковали ли на нашем портале данное объявление, если да, то заканчиваем выполнение скрипта
    $publicationTime = $currentShortAdvert->find('td', 0)->find('font', 0)->plaintext;
    $publicationDate = $currentShortAdvert->find('td', 0)->find('center', 0)->plaintext;

    // Получим подробные сведения по этому объявлению
    // Нужно выполнить запрос дополнительной страницы и ее парсинг

    //TODO: test
    echo $currentShortAdvert->innertext . "    Дата: " . $publicationDateOrTime;

    // Инициализируем модель и сохраняем данные в БД
    $property = new Property(NULL);

    // Оповещаем операторов о новом объявлении на сайте bazab2b.ru
    $subject = 'Объявление на bazab2b.ru';
    $msgHTML = "Новое объявление на bazab2b.ru: <a href='http://svobodno.org/editadvert.php?propertyId=" . $property->getId() . "'>Перейти к редактированию</a>";
    GlobFunc::sendEmailToOperator($subject, $msgHTML);

    $n++;
}

// Подчищаем за собой, чтобы гарантированно избежать утечек памяти
$html->clear();
unset($html);

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();