<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
$websiteRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/models/User.php';
require_once $websiteRoot . '/models/UserIncoming.php';
require_once $websiteRoot . '/models/Property.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');
// TODO: Вернуть ошибку

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $userIncoming->isAdmin();

// Проверяем, залогинен ли пользователь, если нет - то отказываем в доступе
if (!$userIncoming->login()) {
    echo json_encode(array('access' => 'denied'));
}

/*************************************************************************************
 * ПОЛУЧИМ POST ПАРАМЕТРЫ
 ************************************************************************************/

// Получаем идентификатор объекта недвижимости, контакты собственника которого интересуют пользователя
$propertyId = "";
if (isset($_POST['propertyId'])) $propertyId = intval(htmlspecialchars($_POST['propertyId'], ENT_QUOTES));

// Если в запросе не указан идентификатор объекта недвижимости, то отказываем в доступе
if ($propertyId == "" || $propertyId == 0) echo json_encode(array('access' => 'denied'));

/*************************************************************************************
 * ИНИЦИАЛИЗАЦИЯ МОДЕЛИ ОБЪЕКТА НЕДВИЖИМОСТИ
 *************************************************************************************/

$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB()) echo json_encode(array('access' => 'denied'));

/*************************************************************************************
 * ПРАВА ДОСТУПА
 *************************************************************************************/

// Если объявление не опубликовано, то получить по нему контакты собственника нельзя
if ($property->getStatus() != "опубликовано") echo json_encode(array('access' => 'denied'));
$propertyData = $property->getCharacteristicData();

// Если пользователь не оплатил доступ к контактам собственников по такого рода объявлениям, то отказываем в доступе
if ($propertyData['typeOfObject'] == "квартира" && $userIncoming->getReviewFlats() < time()) echo json_encode(array('access' => 'denied'));
if ($propertyData['typeOfObject'] == "комната" && $userIncoming->getReviewRooms() < time()) echo json_encode(array('access' => 'denied'));
//TODO: проверить чтобы время сравнивалось в одинаковом часовом поясе

/*************************************************************************************
 * СОХРАНЯЕМ ИНФУ О ЗАПРОСЕ КОНТАКТОВ СОБСТВЕННИКА, ЕСЛИ РАНЕЕ НЕ СОХРАНЯЛИ
 *************************************************************************************/

$existRequest = DBconnect::selectRequestForOwnerContactsForTenantAndProperty($userIncoming->getId(), $property->getId());
if (count($existRequest) == 0) DBconnect::insertRequestForOwnerContacts(array("tenantId" => $userIncoming->getId(), "propertyId" => $property->getId()));

/*************************************************************************************
 * TODO: УЗНАЕМ ИМЯ И ОТЧЕСТВО СОБСТВЕННИКА
 *************************************************************************************/

/*************************************************************************************
 * ВОЗВРАЩАЕМ КОНТАКТЫ СОБСТВЕННИКА
 *************************************************************************************/

echo json_encode(array('access' => 'successful', 'name' => '', 'secondName' => '', 'contactTelephonNumber' => $propertyData['contactTelephonNumber'], 'sourceOfAdvert' => $propertyData['sourceOfAdvert']));

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();