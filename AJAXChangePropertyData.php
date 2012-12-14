<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.'); // TODO: Вернуть ошибку

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $userIncoming->isAdmin();

/*************************************************************************************
 * ПРОВЕРКА ПРАВ ДОСТУПА К СКРИПТУ
 ************************************************************************************/

// Проверяем, залогинен ли пользователь, если нет - то отказываем в доступе
if (!$userIncoming->login()) {
	GlobFunc::accessDenied();
}

// Если пользователь не является администратором, то доступ к скрипту ему запрещен
if (!$isAdmin['searchUser']) {
	GlobFunc::accessDenied();
}

/*************************************************************************************
 * ПОЛУЧИМ POST ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Получаем идентификатор объекта недвижимости для манипуляций
$propertyId = "";
if (isset($_POST['propertyId'])) $propertyId = intval(htmlspecialchars($_POST['propertyId'], ENT_QUOTES));

// Команда пользователя
$action = "";
if (isset($_POST['action'])) $action = htmlspecialchars($_POST['action'], ENT_QUOTES);

// Новые значения, которые нужно присвоить параметрам объекта недвижимости
$newValueArr = array();
if (isset($_POST['newValueArr'])) {
	$newValueArr = json_decode($_POST['newValueArr'], TRUE);
	foreach ($newValueArr as $value) {
		$value = htmlspecialchars($value, ENT_QUOTES);
	}
}

// Если в запросе не указан идентификатор объекта недвижимости или команда, которую нужно выполнить, то отказываем в доступе
if ($propertyId == "" || $propertyId == 0 || $action == "") GlobFunc::accessDenied();
// Если нужно изменить дату/время ближайшего просмотра, а массив новых значений пуст, то отказываем в доступе
if ($action == "changeEarliestDate" && (!is_array($newValueArr) || count($newValueArr) == 0)) GlobFunc::accessDenied();

/*************************************************************************************
 * ИНИЦИАЛИЗАЦИЯ МОДЕЛИ ОБЪЕКТА НЕДВИЖИМОСТИ
 *************************************************************************************/

$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB()) GlobFunc::accessDenied();

/*************************************************************************************
 * НОВАЯ ДАТА ПРОСМОТРА ОБЪЕКТА
 *************************************************************************************/

if ($action == "changeEarliestDate") {
	if ($property->changeEarliestDate($newValueArr['earliestDate'], $newValueArr['earliestTimeHours'], $newValueArr['earliestTimeMinutes'])) {
		// Параметры объекта сохраняются в БД только в том случае, если удалось успешно изменить дату и время ближайшего просмотра
		if (!$property->saveCharacteristicToDB()) GlobFunc::accessDenied();
	} else {
		GlobFunc::accessDenied();
	}
}

/*************************************************************************************
 * СНЯТИЕ С ПУБЛИКАЦИИ ОБЪЕКТА (ПЕРЕНОС В АРХИВ ДЛЯ ЧУЖИХ ОБЪЯВЛЕНИЙ)
 *************************************************************************************/

if ($action == "unpublishAdvert") {
	if (!$property->unpublishAdvert()) GlobFunc::accessDenied();
}

/*************************************************************************************
 * Если все хорошо - возвращаем положительный статус выполнения операции
 *************************************************************************************/

header('Content-Type: text/xml; charset=UTF-8');
echo "<xml><span status='successful'></span></xml>";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();