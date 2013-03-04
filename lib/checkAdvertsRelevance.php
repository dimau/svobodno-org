<?php
/**
 * Сценарий-уборщик для проверки актуальности объявлений, собранных с чужих ресурсов и чистки базы от неактуальных объявлений
 * Сценарий должен запускаться регулярно автоматически с помощью cron
 * Задача - выявить объявления, которые уже сняты собственниками на ресурсах-донорах и снять соответствующие объявления с публикации на моем ресурсе
 *
 * Общий алгоритм работы:
 * 1. Берем 50 самых старых (по last_act) объявлений с полнотой (completeness) = 0 (собранные с ресурсов-доноров)
 * 2. В цикле по каждому отобранному на проверку объявлению получаем страницу с его подробным описанием на ресурсе-доноре
 * 3. Проверяем полученную страницу на признаки неактуальности
 * 4. Если признаки неактуальности обнаружены (собственник уже снял свое объявление не ресурсе-доноре), то снимаем с публикации наше объявления
 * 5. Если признаки неактуальности не обнаружены (объявление еще опубликовано и доступно на ресурсе-доноре), то меняем last_act у нашего объявления на текущее время
 *
 */

/********************************************************************************
 * ИНИЦИАЛИЗАЦИЯ УБОРЩИКА
 *******************************************************************************/

// Подключаем необходимые модели, классы
if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "") $websiteRoot = $_SERVER['DOCUMENT_ROOT']; else $websiteRoot = "/var/www/dimau/data/www/svobodno.org";
require_once $websiteRoot . '/lib/simple_html_dom.php';
require_once $websiteRoot . '/lib/class.phpmailer.php';
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/models/AdvertsRelevanceChecker.php';
require_once $websiteRoot . '/models/Property.php';

// Фиксируем в логах факт запуска уборщика
Logger::getLogger(GlobFunc::$loggerName)->log("checkAdvertsRelevance.php:1 Уборщик запущен");

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) {
    Logger::getLogger(GlobFunc::$loggerName)->log("checkAdvertsRelevance.php:2 Ошибка инициализации соединения с БД");
    exit();
}

// Получим идентификаторы 50 самых старых объявлений
$advertsId = DBconnect::selectPropertiesIdForLastAct(20);
if (count($advertsId) == 0) {
    Logger::getLogger(GlobFunc::$loggerName)->log("checkAdvertsRelevance.php:3 Не удалось получить список id объявлений для проверки их актуальности");
    DBconnect::closeConnectToDB();
    exit();
}

// TODO: test
$test = "";

// Перебираем все полученные объявления и проверяем каждое на актуальность
foreach ($advertsId as $propertyId) {

    // Инициализируем модель для работы с объявлением
    $property = new Property($propertyId);
    // Если получить данные по объекту недвижимости из БД не удалось, то идем к следующему id
    if (!$property->readCharacteristicFromDB()) continue;

    // Получим ссылку на объявление-источник
    $sourceURL = $property->getSourceOfAdvert();

    // Проверяем актуальность объявления-источника
    $isRelevance = AdvertsRelevanceChecker::checkAdvertRelevance($sourceURL);

    if ($isRelevance) { // Если объявление еще актуально
        // Меняем дату последней проверки актуальности / редактирования объявления на текущую.
        // Изменение даты последнего редактирования (last_act) происходит автоматически при пересохранении объявления
        $property->saveCharacteristicToDB("edit");
    } else { // Если объявление уже НЕ актуально - снимаем его с публикации на нашем ресурсе
        $property->unpublishAdvert();
    }

    // TODO: test
    $test .= "Адрес источника: '" . $sourceURL . "'. Результат проверки: '" . $isRelevance . "' Время последней проверки: " . GlobFunc::timestampFromDBToView(time()) . "    <br>";
}

// TODO: test
$subject = 'Отчет по неактуальным объявлениям';
$msgHTML = $test;
GlobFunc::sendEmailToOperator($subject, $msgHTML);

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();