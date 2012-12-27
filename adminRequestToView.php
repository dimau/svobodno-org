<?php
/* Страница администратора для отображения данных о конкретной заявке на просмотр и всех остальных заявках на данный объект недвижимости */

// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserFull.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $userIncoming->isAdmin();

/*************************************************************************************
 * Проверяем - может ли данный пользователь просматривать данную страницу
 ************************************************************************************/

// Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
if (!$userIncoming->login()) {
	header('Location: login.php');
	exit();
}

// Если пользователь не является администратором, то доступ к странице ему запрещен - разавторизуем его и перекинем на главную (в идеале нужно перекидывать на login.php)
// Кроме того, проверяем, что у данного администратора есть право на поиск пользователей и вход в их Личные кабинеты
if (!$isAdmin['searchUser']) {
	header('Location: out.php');
	exit();
}

/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Идентификатор объекта, по которому нас интересуют заявки на просмотр
$propertyId = "";
if (isset($_GET['propertyId'])) $propertyId = intval(htmlspecialchars($_GET['propertyId'], ENT_QUOTES));

// Идентификатор заявки на просмотр, которая нас интересует
$requestToViewId = "";
if (isset($_GET['requestToViewId'])) $requestToViewId = intval(htmlspecialchars($_GET['requestToViewId'], ENT_QUOTES));

// Если в запросе не указан идентификатор объекта, то выдаем пользователю спец страницу с описанием ошибки
if ($propertyId == "" || $propertyId == 0) {
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "notfound";
    require $_SERVER['DOCUMENT_ROOT'] . '/templates/templ_error.php';
	exit();
}

/********************************************************************************
 * ПОЛУЧИМ ВСЕ ЗАЯВКИ НА ПРОСМОТР ЭТОГО ОБЪЕКТА ($propertyId)
 *******************************************************************************/

$allRequestsToView = DBconnect::selectRequestsToViewForProperties($propertyId);

/********************************************************************************
 * ПОЛУЧИМ СВЕДЕНИЯ ОБ АРЕНДАТОРАХ, ПОДАВШИХ ЗАЯВКИ НА ПРОСМОТР ЭТОГО ОБЪЕКТА ($propertyId)
 *******************************************************************************/

// Выделим идентификаторы всех арендаторов, отправивших заявки на просмотр
$allTenants = array();
foreach ($allRequestsToView as $value) {
	$allTenants[] = $value['tenantId'];
}

// Получим полные данные по всем этим арендаторам
$allTenants = DBconnect::getAllDataAboutCharacteristicUsers($allTenants);

// Дополним сведения о заявках на просмотр недостающими данными об их отправителях
for ($i = 0, $s = count($allRequestsToView); $i < $s; $i++) {
	foreach ($allTenants as $value) {
		if ($allRequestsToView[$i]['tenantId'] == $value['id']) {
			$allRequestsToView[$i]['name'] = $value['name'];
			$allRequestsToView[$i]['secondName'] = $value['secondName'];
			$allRequestsToView[$i]['surname'] = $value['surname'];
			break;
		}
	}
}

/********************************************************************************
 * ПОЛУЧИМ СВЕДЕНИЯ О САМОМ ОБЪЕКТЕ ($propertyId)
 *******************************************************************************/

$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB() && !$property->readCharacteristicFromArchive()) {
	die("Ошибка получения данных об объекте недвижимости");
}

/********************************************************************************
 * ПОЛУЧИМ СВЕДЕНИЯ О СОБСТВЕННИКЕ ОБЪЕКТА ($propertyId)
 *******************************************************************************/

$user = new UserFull($property->getUserId());
if (!$user->readCharacteristicFromDB()) die("Ошибка получения данных о собственнике недвижимости");

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$userCharacteristic = $user->getCharacteristicData();	// массив со сведениями о собственнике объекта недвижимости (его характеристика)
$propertyCharacteristic = $property->getCharacteristicData();	// массив со сведениями о самом объекте недвижимости (его характеристика)
//$allRequestsToView	массив, каждый элемент которого представляет собой еще один массив параметров конкретной заявки на просмотр
//$requestToViewId

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminRequestToView.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();