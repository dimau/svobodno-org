<?php
/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Команда пользователя
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

// Идентификатор объекта для просмотра
$propertyId = "";
if (isset($_GET['propertyId'])) $propertyId = intval(htmlspecialchars($_GET['propertyId'], ENT_QUOTES));

// Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя на спец страницу
if ($propertyId == "" || $propertyId == 0) {
	header('Location: 404.html');
	exit();
}

/*************************************************************************************
 * Инициализируем требуемые модели
 ************************************************************************************/

// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
include 'models/DBconnect.php';
include 'models/GlobFunc.php';
include 'models/Logger.php';
include 'models/IncomingUser.php';
include 'views/View.php';
include 'models/Property.php';
include 'models/SignUpToView.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$incomingUser = new IncomingUser();

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $incomingUser->isAdmin();

// Инициализируем модель запроса на просмотр данного объекта данным пользователем.
// Если он уже записался на просмотр, то в модели будут содержаться данные его запроса (время, комментарий...)
$signUpToView = new SignUpToView($incomingUser->getId(), $propertyId);

// Инициализируем переменную для хранения информации об успешности/неуспешности отправки запроса на просмотр в БД
$statusOfSaveParamsToDB = NULL;

// Инициализируем массив для хранения данных об ошибках валидации формы запроса на просмотр
$errors = array();

/*************************************************************************************
 * Получаем данные объявления для просмотра, а также другие данные из БД
 ************************************************************************************/

// Инициализация модели по умолчанию
$property = new Property($propertyId);

// Анкетные данные объекта недвижимости
// Если получить данные по объекту недвижимости из БД не удалось, то скорее всего не верно указан id объекта, перенаправляем пользователя на 404 страницу
if (!$property->writeCharacteristicFromDB()) {
	header('Location: 404.html');
	exit();
}

// Если анкетные данные по объекту недвижимости получить удалось - получим инфу о его фотках
$property->writeFotoInformationFromDB();

/*************************************************************************************
 * ПРОВЕРКА ПРАВ ДОСТУПА К СТРАНИЦЕ
 ************************************************************************************/

// Если объявление опубликовано, то его может просматривать каждый
// Если объявление закрыто (снято с публикации), то его может просматривать только сам собственник и админы
if ($property->status == "не опубликовано"
	AND $property->userId != $incomingUser->getId()
	AND !$isAdmin['searchUser'])
{
	header('Location: 404.html');
	exit();
}
//TODO: реализовать соответствующую 404 страницу

/************************************************************************************
 * НОВЫЙ ЗАПРОС НА ПРОСМОТР. Если пользователь отправил форму запроса на просмотр объекта
 ***********************************************************************************/

if ($action == "signUpToView") {

	$signUpToView->writeParamsFromPOST();
	$errors = $signUpToView->isParamsCorrect();
	$statusOfSaveParamsToDB = $signUpToView->saveParamsToDB(); // вне зависимости от полноты заполнения формы (корректности) она будет отправлена на сервер и обработана. Это решение связано с тем, что сложно отобразить на клиенте пользователю - что он не заполнил поле в модальном окне, лень это реализовывать

	//TODO: оповестить оператора о новом запросе на просмотр
	//TODO: новость для собственника о новом претенденте
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $incomingUser->login(); // Используется в templ_header.php
$amountUnreadMessages = $incomingUser->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$userCharacteristic = array('typeTenant' => $incomingUser->isTenant(), 'name' => $incomingUser->name, 'secondName' => $incomingUser->secondName, 'surname' => $incomingUser->surname, 'telephon' => $incomingUser->telephon); // Но для данной страницы данный массив содержит только имя, отчество, фамилию, телефон пользователя
$propertyCharacteristic = $property->getCharacteristicData();
$propertyFotoInformation = $property->getFotoInformationData();
$favoritesPropertysId = $incomingUser->getFavoritesPropertysId();
$signUpToViewData = $signUpToView->getParams(); // Используется в templ_signUpToViewItem.php
$furnitureInLivingArea = $property->getFurnitureInLivingAreaAll();
$furnitureInKitchen = $property->getFurnitureInKitchenAll();
$appliances = $property->getAppliancesAll();
$strHeaderOfPage = GlobFunc::getFirstCharUpper($property->typeOfObject) . " по адресу: " . $property->address; // Получаем заголовок страницы
//$statusOfSaveParamsToDB // Используется в templ_signUpToViewItem.php
//$errors

// Подсоединяем нужный основной шаблон
include "templates/"."templ_objdescription.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();