<?php
/**
 * Сценарий запускается при сохранении нового объявления (newadvert.php) и при новой публикации имеющегося
 * Задача - асинхронно (не задерживая окончание запустившего его сценария) выполнить формирование соответствующих уведомлений,
 * рассылку e-mail и смс по претендентам, у которых поисковый запрос соответствует новому опубликованному объявлению
 */

// Подключаем необходимые модели, классы
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/class.phpmailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';

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
	Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось выполнить считывание данных из БД по объекту:".$propertyId);
	exit();
}

// Получим список пользователей-арендаторов, под чьи поисковые запросы подходит этот объект
$listOfTargetUsers = $property->whichTenantsAppropriate();
if ($listOfTargetUsers == FALSE) {
	Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось получить список id потенциальных арендаторов по объекту:".$propertyId);
	exit();
}

// Формируем уведомления по данному объекту
if (!$property->sendMessagesAboutNewProperty($listOfTargetUsers)) {
	Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось сформировать уведомления для потенциальных арендаторов по объекту:".$propertyId);
	exit();
}

// Формируем и рассылаем email по тем пользователям, которые подписаны на такую рассылку
$listOfTargetUsersForEmail = array();
foreach ($listOfTargetUsers as $value) {
	if ($value['needEmail'] = 1) $listOfTargetUsersForEmail[] = $value;
}
$property->sendEmailAboutNewProperty($listOfTargetUsersForEmail);