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
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "notfound";
    require $_SERVER['DOCUMENT_ROOT'] . '/templates/templ_error.php';
    exit();
}

/*************************************************************************************
 * Инициализируем требуемые модели
 ************************************************************************************/

// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/RequestToView.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $userIncoming->isAdmin();

// Инициализируем модель запроса на просмотр данного объекта данным пользователем.
// Если он уже записался на просмотр, то в модели будут содержаться данные его запроса (время, комментарий...)
$signUpToView = new RequestToView($userIncoming->getId(), $propertyId);

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
if (!$property->readCharacteristicFromDB()) {
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "notfound";
    require $_SERVER['DOCUMENT_ROOT'] . '/templates/templ_error.php';
    exit();
}

// Если анкетные данные по объекту недвижимости получить удалось - получим инфу о его фотках
$property->readFotoInformationFromDB();

/*************************************************************************************
 * ПРОВЕРКА ПРАВ ДОСТУПА К СТРАНИЦЕ
 ************************************************************************************/

// Если объявление опубликовано, то его может просматривать каждый
// Если объявление закрыто (снято с публикации), то его может просматривать только сам собственник и админы
if ($property->getStatus() == "не опубликовано"
	AND $property->getUserId() != $userIncoming->getId()
	AND !$isAdmin['searchUser'])
{
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "accessdenied";
    require $_SERVER['DOCUMENT_ROOT'] . '/templates/templ_error.php';
    exit();
}

/************************************************************************************
 * НОВЫЙ ЗАПРОС НА ПРОСМОТР. Если пользователь отправил форму запроса на просмотр объекта
 ***********************************************************************************/

if ($action == "signUpToView") {

	$signUpToView->writeParamsFromPOST();
	$errors = $signUpToView->isParamsCorrect();
	$statusOfSaveParamsToDB = $signUpToView->saveParamsToDB(); // вне зависимости от полноты заполнения формы (корректности) она будет отправлена на сервер и обработана. Это решение связано с тем, что сложно отобразить на клиенте пользователю - что он не заполнил поле в модальном окне, лень это реализовывать

    // Оповестим операторов о появлении новой заявки на просмотр
    if ($statusOfSaveParamsToDB) {

        $subject = 'Заявка на просмотр: '.$property->getAddress();

        $msgHTML = "Поступила новая заявка на просмотр:<br>
        Дата: ".date('d.m.Y H:i')."<br>
        Кто: ".$userIncoming->getSurname()." ".$userIncoming->getName()." ".$userIncoming->getSecondName()."<br>
        Объект: ".$property->getAddress()."<br>
        <a href='http://svobodno.org/adminAllRequestsToView.php?action=Новая'>Все новые заявки на просмотр</a>";

        GlobFunc::sendEmailToOperator($subject, $msgHTML);
    }

	//TODO: новость для собственника о новом претенденте
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
$amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$userCharacteristic = array('typeTenant' => $userIncoming->isTenant(), 'typeOwner' => $userIncoming->isOwner(), 'name' => $userIncoming->getName(), 'secondName' => $userIncoming->getSecondName(), 'surname' => $userIncoming->getSurname(), 'telephon' => $userIncoming->getTelephon()); // Но для данной страницы данный массив содержит только имя, отчество, фамилию, телефон пользователя
$propertyCharacteristic = $property->getCharacteristicData();
$propertyFotoInformation = $property->getFotoInformationData();
$favoritePropertiesId = $userIncoming->getFavoritePropertiesId();
$signUpToViewData = $signUpToView->getParams(); // Используется в templ_signUpToViewItem.php
$furnitureInLivingArea = $property->getFurnitureInLivingAreaAll();
$furnitureInKitchen = $property->getFurnitureInKitchenAll();
$appliances = $property->getAppliancesAll();
//$statusOfSaveParamsToDB // Используется в templ_signUpToViewItem.php
//$errors

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_property.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();