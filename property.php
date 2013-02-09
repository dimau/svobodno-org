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

// Определим корень сайта
$websiteRoot = $_SERVER['DOCUMENT_ROOT'];

// Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя на спец страницу
if ($propertyId == "" || $propertyId == 0) {
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "notfound";
    require $websiteRoot . '/templates/templ_error.php';
    exit();
}

/*************************************************************************************
 * Инициализируем требуемые модели
 ************************************************************************************/

// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/models/User.php';
require_once $websiteRoot . '/models/UserIncoming.php';
require_once $websiteRoot . '/models/Property.php';
require_once $websiteRoot . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $userIncoming->isAdmin();

// TODO: узнаем - отправлял ли ранее пользователь заявку на получение контактов по этому объявлению


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
    require $websiteRoot . '/templates/templ_error.php';
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
        AND !$isAdmin['searchUser']
) {
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "accessdenied";
    require $websiteRoot . '/templates/templ_error.php';
    exit();
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
$amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$userCharacteristic = array('id' => $userIncoming->getId(), 'typeTenant' => $userIncoming->isTenant(), 'typeOwner' => $userIncoming->isOwner(), 'name' => $userIncoming->getName(), 'secondName' => $userIncoming->getSecondName(), 'surname' => $userIncoming->getSurname(), 'telephon' => $userIncoming->getTelephon(), 'reviewRooms' => $userIncoming->getReviewRooms(), 'reviewFlats' => $userIncoming->getReviewFlats()); // Но для данной страницы данный массив содержит только имя, отчество, фамилию, телефон пользователя
$propertyCharacteristic = $property->getCharacteristicData();
$propertyFotoInformation = $property->getFotoInformationData();
$favoritePropertiesId = $userIncoming->getFavoritePropertiesId();
$furnitureInLivingArea = $property->getFurnitureInLivingAreaAll();
$furnitureInKitchen = $property->getFurnitureInKitchenAll();
$appliances = $property->getAppliancesAll();
//$isAdmin
//TODO: передавать параметр - смотрел ли данный пользовтаель ранее контакты собственника

// Подсоединяем нужный основной шаблон
require $websiteRoot . "/templates/templ_property.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();