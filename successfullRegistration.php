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
require_once $websiteRoot . '/models/Payment.php';
require_once $websiteRoot . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

/********************************************************************************
 * ОТКУДА К НАМ ПРИШЕЛ ПОЛЬЗОВАТЕЛЬ
 *******************************************************************************/

// Попробуем получить адрес страницы, с которой пользователь попал на регистрацию. Проверим, что этот адрес относится к нашему домену
if (isset($_SESSION['url_initial']) && preg_match('~^((http://svobodno.org)|(http://localhost))~', $_SESSION['url_initial'])) {
    $url_initial = $_SESSION['url_initial'];
} else {
    $url_initial = "";
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
$amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$userCharacteristic = array("id" => $userIncoming->getId());
//$url_initial

// Подсоединяем нужный основной шаблон
require $websiteRoot . "/templates/templ_successfullRegistration.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();