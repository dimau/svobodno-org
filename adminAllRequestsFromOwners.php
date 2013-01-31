<?php
/* Страница администратора для отображения данных о всех сохраненных в БД заявках собственников */

// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
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

// Команда админа
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

// Идентификатор заявки от собственника
$requestFromOwnerId = "";
if (isset($_GET['requestFromOwnerId'])) $requestFromOwnerId = intval(htmlspecialchars($_GET['requestFromOwnerId'], ENT_QUOTES));

/********************************************************************************
 * УДАЛЕНИЕ ЗАЯВКИ СОБСТВЕННИКА
 *******************************************************************************/

if ($action == "remove" && $requestFromOwnerId != "" && $requestFromOwnerId != 0) {
    DBconnect::deleteRequestFromOwnerForId($requestFromOwnerId);
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$allRequestsFromOwners = DBconnect::selectRequestsFromOwners();

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminAllRequestsFromOwners.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();