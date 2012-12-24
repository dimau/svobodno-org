<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/RequestFromOwner.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Инициализируем модель для работ с запросом на новое объявление от собственника
$requestFromOwner = new RequestFromOwner($userIncoming);

// Инициализируем переменную для сохранения ошибок, связанных с обработкой заявки собственника (которые не позволили ее принять)
$errors = NULL;

/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Команда пользователя
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

/********************************************************************************
 * ЗАПРОС НА ПОДАЧУ ОБЪЯВЛЕНИЯ. Если пользователь отправил заполненную форму заявки на подачу объявления
 *******************************************************************************/

if ($action == "takeRequest") {

	$requestFromOwner->writeParamsFromPOST();

	$errors = $requestFromOwner->requestFromOwnerDataValidate();

	if (is_array($errors) && count($errors) == 0) {

		// Сохраняем запрос собственника в БД
		if ($requestFromOwner->saveParamsToDB()) {

            $subject = 'Заявка от собственника: '.$requestFromOwner->getName();

            $msgHTML = "Поступила новая заявка от собственника:<br>
            Дата: ".date('d.m.Y H:i')."<br>
            Кто: ".$requestFromOwner->getName()."<br>
            Адрес: ".$requestFromOwner->getAddress()."<br>
            <a href='http://svobodno.org/adminAllRequestsFromOwners.php'>Все заявки от собственников</a>";

            GlobFunc::sendEmailToOperator($subject, $msgHTML);

		} else {
            // Сохранении данных в БД не прошло - заявка не принята
            $errors[] = 'К сожалению, при сохранении данных произошла ошибка: попробуйте еще раз или сообщите нам о Вашей недвижимости по телефону: 8-922-143-16-15';
        }
	}
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
$amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$requestFromOwnerData = $requestFromOwner->getRequestFromOwnerData();
//$errors

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_forowner.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();