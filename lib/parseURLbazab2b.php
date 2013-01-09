<?php
/**
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

function parseFullAdvert($c_id, $id) {
    // Валидация входных параметров
    if (!isset($c_id) || !isset($id)) return array();

    // Получаем HTML объявления с сайта
    // TODO: сделать проверку на успех получения html
    $html = file_get_html("http://bazab2b.ru/?c_id=".$c_id."&&id=".$id."&modal=1");
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
    }




    return $params;
}

/********************************************************************************
 * Парсим сайт bazab2b.ru
 *******************************************************************************/

// Получаем свежий HTML сайта
$html = file_get_html('http://bazab2b.ru/?pagx=baza');

// Получаем параметры последнего занесенного в нашу базу объявления
$lastTime = "09:23";
$lastDate = "29.12";
$lastAddress = "***";

// Инициализируем счетчик текущего объявления (считаем за 0 самое последнее по времени публикации на сайте bazab2b.ru объявление)
$n = 0;

// Перебираем последовательно все последние объявления (начиная с самого последнего) и формируем соответствующие объявления в нашей базе. Пока не дойдем до конца списка или до объявления, которое уже было опубликовано на нашем портале
while ($currentShortAdvert = $html->find('.poisk .chr-wite', $n)) {
    // Выясняем, публиковали ли на нашем портале данное объявление, если да, то заканчиваем выполнение скрипта
    $publicationDateOrTime = $currentShortAdvert->find('td', 0)->find('font', 0)->plaintext;
    //$address = $currentShortAdvert->find('td', 4)->find('center', 0)->plaintext;

    // Получим подробные сведения по этому объявлению
    // Нужно выполнить запрос дополнительной страницы и ее парсинг



    //TODO: test
    echo $currentShortAdvert->innertext."    Дата: ".$publicationDateOrTime;

    // Инициализируем модель и сохраняем данные в БД
    $property = new Property(NULL);

    // Оповещаем операторов о новом объявлении на сайте bazab2b.ru
    $subject = 'Объявление на bazab2b.ru';
    $msgHTML = "Новое объявление на bazab2b.ru: <a href='http://svobodno.org/editadvert.php?propertyId=".$property->getId()."'>Перейти к редактированию</a>";
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