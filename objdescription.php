<?php
/*************************************************************************************
 * Если в строке не указан идентификатор объявления, то пересылаем пользователя на спец. страницу
 ************************************************************************************/

$propertyId = "0";
if (isset($_GET['propertyId']) && $_GET['propertyId'] != "") {
	$propertyId = $_GET['propertyId']; // Получаем идентификатор объявления для показа из строки запроса
} else {
	header('Location: 404.html'); // Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет к списку его объявлений
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

$property = new Property($propertyId);

// Анкетные данные и данные о фотографиях объекта недвижимости
$statusOfWriteCharacteristicFromDB = $property->writeCharacteristicFromDB();
// Если получить данные по объекты недвижимости из БД не удалось, то скорее всего не верно указан id объекта, перенаправляем пользователя на 404 страницу
if ($statusOfWriteCharacteristicFromDB == FALSE) {
	header('Location: 404.html');
}

// Если анкетные данные по объекту недвижимости получить удалось - получим инфу о его фотках
$property->writeFotoInformationFromDB();

/*************************************************************************************
 * Проверяем - может ли данный пользователь просматривать данное объявление
 ************************************************************************************/

// Если объявление опубликовано, то его может просматривать каждый
// Если объявление закрыто (снято с публикации), то его может просматривать только сам собственник и админы
$isAdmin = $incomingUser->isAdmin();
if ($property->status == "не опубликовано"
	AND $property->userId != $incomingUser->getId()
	AND !$isAdmin['searchUser'])
{
	header('Location: 404.html');
}
//TODO: реализовать соответствующую 404 страницу

/*************************************************************************************
 * Получаем заголовок страницы
 ************************************************************************************/
$strHeaderOfPage = GlobFunc::getFirstCharUpper($property->typeOfObject) . " по адресу: " . $property->address;

/************************************************************************************
 * НОВЫЙ ЗАПРОС НА ПРОСМОТР. Если пользователь отправил форму запроса на просмотр объекта
 ***********************************************************************************/

if (isset($_POST['signUpToViewDialogButton'])) {

	$signUpToView->writeParamsFromPOST();
	$errors = $signUpToView->isParamsCorrect();
	$statusOfSaveParamsToDB = $signUpToView->saveParamsToDB(); // вне зависимости от полноты заполнения формы (корректности) она будет отправлена на сервер и обработана. Это решение связано с тем, что сложно отобразить на клиенте пользователю - что он не заполнил поле в модальном окне, лень это реализовывать

	//TODO: оповестить оператора о новом запросе на просмотр
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $incomingUser->login(); // Используется в templ_header.php
$amountUnreadMessages = $incomingUser->getAmountUnreadMessages(); // Количество непрочитанных сообщений пользователя
$userCharacteristic = array('typeTenant' => $incomingUser->isTenant(), 'name' => $incomingUser->name, 'secondName' => $incomingUser->secondName, 'surname' => $incomingUser->surname, 'telephon' => $incomingUser->telephon); // Но для данной страницы данный массив содержит только имя, отчество, фамилию, телефон пользователя
$propertyCharacteristic = $property->getCharacteristicData();
$propertyFotoInformation = $property->getFotoInformationData();
$favoritesPropertysId = $incomingUser->getFavoritesPropertysId();
$signUpToViewData = $signUpToView->getParams(); // Используется в templ_signUpToViewItem.php
$furnitureInLivingArea = $property->getFurnitureInLivingAreaAll();
$furnitureInKitchen = $property->getFurnitureInKitchenAll();
$appliances = $property->getAppliancesAll();
//$strHeaderOfPage
//$statusOfSaveParamsToDB // Используется в templ_signUpToViewItem.php
//$errors

// Подсоединяем нужный основной шаблон
include "templates/"."templ_objdescription.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();