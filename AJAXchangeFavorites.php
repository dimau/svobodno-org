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

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.'); // TODO: Вернуть ошибку

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Проверяем, залогинен ли пользователь, если нет - то отказываем в доступе
if (!$userIncoming->login()) GlobFunc::accessDenied();

// Получаем идентификатор объявления, которое пользователь хочет добавить/удалить в Избранное и действие, которое нужно совершить с объявлением (добавить в избранное или удалить)
$propertyId = "";
if (isset($_POST['propertyId'])) $propertyId = htmlspecialchars($_POST['propertyId'], ENT_QUOTES); else GlobFunc::accessDenied();
$action = "";
if (isset($_POST['action'])) $action = htmlspecialchars($_POST['action'], ENT_QUOTES); else GlobFunc::accessDenied();

// Если требуемое действие = Добавить в избранное, то записываем id объявления в БД, в поле favoritePropertiesId пользователя - тем самым фиксируем, что он добавил данное объявление к себе в избранные
if ($action == "addToFavorites") {
    if (!$userIncoming->addFavoritePropertiesId($propertyId)) GlobFunc::accessDenied();
}

// Если требуемое действие = Удалить из избранного, то удаляем id объявления из БД, из поля favoritePropertiesId пользователя
if ($action == "removeFromFavorites") {
    if (!$userIncoming->removeFavoritePropertiesId($propertyId)) GlobFunc::accessDenied();
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