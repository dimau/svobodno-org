<?php
/**
 * Сценарий запускается при сохранении нового объявления (newadvert.php) и при новой публикации имеющегося
 * Задача - асинхронно (не задерживая окончание запустившего его сценария) выполнить формирование соответствующих уведомлений,
 * рассылку e-mail и смс по претендентам, у которых поисковый запрос соответствует новому опубликованному объявлению
 */

// Подключаем необходимые модели, классы
if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "") $websiteRoot = $_SERVER['DOCUMENT_ROOT']; else $websiteRoot = "/var/www/dimau/data/www/svobodno.org";
require_once $websiteRoot . '/lib/class.phpmailer.php';
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/views/View.php';
require_once $websiteRoot . '/models/Property.php';

// Получаем id объекта недвижимости, рассылку о котором нужно выполнить
if (isset($_POST['propertyId']) && intval($_POST['propertyId']) != 0) {
    $propertyId = intval($_POST['propertyId']);
} else {
    Logger::getLogger(GlobFunc::$loggerName)->log("Обращение к notificationAboutNewProperty.php без указания propertyId");
    exit();
}

// Инициализируем модель для этого объекта недвижимости
$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB() || !$property->readFotoInformationFromDB()) {
    Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось выполнить считывание данных из БД по объекту:" . $propertyId);
    exit();
}

// Получим список пользователей-арендаторов, под чьи поисковые запросы подходит этот объект
$listOfTargetUsers = $property->whichTenantsAppropriate();
if ($listOfTargetUsers == FALSE) {
    Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось получить список id потенциальных арендаторов по объекту:" . $propertyId);
    exit();
}

// Формируем уведомления по данному объекту
if (!$property->sendMessagesAboutNewProperty($listOfTargetUsers)) {
    Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось сформировать уведомления для потенциальных арендаторов по объекту:" . $propertyId);
    exit();
}

// Формируем и рассылаем email по тем пользователям, которые подписаны на такую рассылку
$listOfTargetUsersForEmail = array();
foreach ($listOfTargetUsers as $value) {
    if ($value['email'] != "" && $value['needEmail'] == 1 && $value['reviewFull'] >= time()) $listOfTargetUsersForEmail[] = $value;
}
$property->sendEmailAboutNewProperty($listOfTargetUsersForEmail);

// Формируем и рассылаем sms по тем пользователям, которые подписаны на такую рассылку
$listOfTargetUsersForSMS = array();
foreach ($listOfTargetUsers as $value) {
    if ($value['needSMS'] == 1) $listOfTargetUsersForSMS[] = $value;
}
$property->sendSMSAboutNewProperty($listOfTargetUsersForSMS);

// Оповестить оператора о подходящем варианте для клиента
/*foreach ($listOfTargetUsers as $value) {
    $subject = 'Подходящий вариант';
    $msgHTML = "Новый подходящий вариант:<br>
                Для пользователя: <a href='http://svobodno.org/man.php?compId=" . GlobFunc::idToCompId($value['userId']) . "'>" . $value['name'] . " " . $value['telephon'] . "</a><br>
                Подробное объявление: <a href='http://svobodno.org/property.php?propertyId=" . $property->getId() . "'>" . $property->getAddress() . "</a>";
    GlobFunc::sendEmailToOperator($subject, $msgHTML);
}*/