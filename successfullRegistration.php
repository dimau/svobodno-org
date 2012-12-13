<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/IncomingUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$incomingUser = new IncomingUser();

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
$isLoggedIn = $incomingUser->login(); // Используется в templ_header.php
$amountUnreadMessages = $incomingUser->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
//$url_initial

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_successfullRegistration.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();