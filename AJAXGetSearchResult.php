<?php
/******
 * Скрипт возвращает максимум 20 значений.
 * Скрипт возвращает значения в формате JSON с помощью команды json_encode!
 *
 * $typeOperation == 'FullBalloons' - вернуть содержимое баллунов для объявлений с данными id
 * $typeOperation == 'FullData' - вернуть содержимое баллунов, а также HTML код для таблиц с краткими и полными параметрами объявлений с данными id
 *****/

/*************************************************************************************
 * Получаем POST запрос и проверяем его корректность
 ************************************************************************************/

// Получаем запрос на предоставление данных
if (isset($_POST['propertyId'])) $propertiesId = $_POST['propertyId']; // Массив, содержащий идентификаторы объектов недвижимости, по которым нужно получить полные данные. Отсортирован в требуемом порядке.
if (isset($_POST['typeOperation'])) $typeOperation = $_POST['typeOperation']; // Тип запроса (только данные баллунов или данные по баллунам + краткий список + полный список)
if (isset($_POST['number'])) $number = intval($_POST['number']); // На каком числе закончилась нумерация в списке в браузере клиента?

// Получили ли мы необходиые для работы данные и корректны ли они
if (!isset($propertiesId) || !is_array($propertiesId) || count($propertiesId) == 0) die("Входные данные не корректны"); // TODO: вывести сообщение об ошибке или пустые данные
// Выдаем значения максимум по 20-ти объектам за раз
if (count($propertiesId) > 20) $propertiesId = array_slice($propertiesId, 0, 20);
for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
	$propertiesId[$i] = intval($propertiesId[$i]);
	if ($propertiesId[$i] == 0) die("Входные данные не корректны"); // TODO: вывести сообщение об ошибке или пустые данные
}
if (!isset($typeOperation) || ($typeOperation != "FullBalloons" && $typeOperation != "FullData")) die("Входные данные не корректны"); // TODO: вывести сообщение об ошибке или пустые данные
if ($typeOperation == "FullData" && !isset($number)) die("Входные данные не корректны"); // TODO: вывести сообщение об ошибке или пустые данные

/*************************************************************************************
 * Инициализируем нужные модели
 ************************************************************************************/

// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.'); // TODO: Вернуть ошибку

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

/*************************************************************************************
 * Получаем данные из БД по запрашиваемым объектам недвижимости (если они еще сдаются)
 ************************************************************************************/

// По запрашиваемым объектам недвижимости
$propertyFullArr = DBconnect::getFullDataAboutProperties($propertiesId, "published");

// По избранным объявлениям нашего пользователя
$favoritePropertiesId = $userIncoming->getFavoritePropertiesId();

/*************************************************************************************
 * Оформляем в HTML данные по полученным объектам недвижимости
 ************************************************************************************/

// Из полученного массива с подробными данными по объектам нужно сформировать ассоциированный массив, в качестве ключей в котором будут выступать id объектов недвижимости, а в качестве значений - HTML для баллунов
$arrayOfBalloonList = array();
if (is_array($propertyFullArr) && count($propertyFullArr) != 0) {
	foreach ($propertyFullArr as $value) {
		$arrayOfBalloonList[$value['id']] = View::getFullBalloonHTML($value, $favoritePropertiesId);
	}
}

if ($typeOperation == "FullData" && is_array($propertyFullArr) && count($propertyFullArr) != 0) {
	$matterOfShortList = View::getMatterOfShortList($propertyFullArr, $favoritePropertiesId, $number + 1, "search");
	$matterOfFullParametersList = View::getMatterOfFullParametersList($propertyFullArr, $favoritePropertiesId, $number + 1, "search");
} else {
	$matterOfShortList = "";
	$matterOfFullParametersList = "";
}

/*************************************************************************************
 * Возвращаем данные по всем объявлениям в формате JSON
 *
 * Возвращается объект (на клиенте этот объект скорее всего называтеся - data), который содержит:
 * data.arrayOfBalloonList - объект, содержащий пары - {идентификтатор объекта недвижимости: HTML код баллуна, ...}
 * data.matterOfShortList - HTML код строк таблицы, которые нужно добавить к #shortListOfRealtyObjects
 * data.matterOfFullParametersList - HTML код строк таблицы, которые нужно добавить к #fullParametersListOfRealtyObjects
 ************************************************************************************/

echo json_encode(array('arrayOfBalloonList' => $arrayOfBalloonList, 'matterOfShortList' => $matterOfShortList, 'matterOfFullParametersList' => $matterOfFullParametersList));