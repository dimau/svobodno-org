<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
$websiteRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $websiteRoot . '/lib/class.phpmailer.php';
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/models/User.php';
require_once $websiteRoot . '/models/UserIncoming.php';
require_once $websiteRoot . '/models/Property.php';

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

// Если пользователь не является администратором, то не сможет снять с публикации объявление
if ($action == "unpublishAdvert" && !$isAdmin['searchUser']) {
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

/*************************************************************************************
 * ИНИЦИАЛИЗАЦИЯ МОДЕЛИ ОБЪЕКТА НЕДВИЖИМОСТИ
 *************************************************************************************/

$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB()) GlobFunc::accessDenied();

/*************************************************************************************
 * СНЯТИЕ С ПУБЛИКАЦИИ ОБЪЕКТА (ПЕРЕНОС В АРХИВ ДЛЯ ЧУЖИХ ОБЪЯВЛЕНИЙ)
 *************************************************************************************/

if ($action == "unpublishAdvert") {
    if (count($property->unpublishAdvert()) != 0) GlobFunc::accessDenied();
}

/*************************************************************************************
 * ОБЪЯВЛЕНИЕ НЕ АКТУАЛЬНО (ПОЛЬЗОВАТЕЛЬ СООБЩАЕТ О ПОТЕРЕ АКТУАЛЬНОСТИ ОБЪЯВЛЕНИЕМ)
 *************************************************************************************/

if ($action == "lostRelevance") {
    // Оповещаем операторов о неактуальном объявлении
    $subject = 'Объявление потеряло актуальность';
    $msgHTML = "Пользователь <a href='http://svobodno.org/man.php?compId=" . GlobFunc::idToCompId($userIncoming->getId()) . "'>" . $userIncoming->getName() . " (id = " . $userIncoming->getId() . ")</a> сообщил, что объявление <a href='http://svobodno.org/editadvert.php?propertyId=" . $property->getId() . "'>" . $property->getAddress() . "</a> больше не является актуальным";
    GlobFunc::sendEmailToOperator($subject, $msgHTML);
}

/*************************************************************************************
 * ОБЪЯВЛЕНИЕ СОДЕРЖИТ ОШИБКУ В ОПИСАНИИ (ПОЛЬЗОВАТЕЛЬ СООБЩАЕТ ОБ ЭТОМ)
 *************************************************************************************/

if ($action == "errorInDescription") {
    // Оповещаем операторов об ошибке в объявлении
    $subject = 'Ошибка в описании объявления';
    $msgHTML = "Пользователь <a href='http://svobodno.org/man.php?compId=" . GlobFunc::idToCompId($userIncoming->getId()) . "'>" . $userIncoming->getName() . " (id = " . $userIncoming->getId() . ")</a> сообщил, что объявление <a href='http://svobodno.org/editadvert.php?propertyId=" . $property->getId() . "'>" . $property->getAddress() . "</a> содержит ошибки";
    GlobFunc::sendEmailToOperator($subject, $msgHTML);
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