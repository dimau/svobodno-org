<?php
/* Скрипт делает уведомление прочитанным или удаленным, если пользователь, отправивший запрос, имеет право на изменение статуса данного уведомления */

// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
include 'models/DBconnect.php';
include 'models/GlobFunc.php';
include 'models/Logger.php';
include 'models/IncomingUser.php';
include 'models/MessageNewProperty.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.'); // TODO: Вернуть ошибку

// Инициализируем модель для запросившего страницу пользователя
$incomingUser = new IncomingUser();

/*************************************************************************************
 * ПРОВЕРКА ПРАВ ДОСТУПА К СКРИПТУ
 ************************************************************************************/

// Проверяем, залогинен ли пользователь, если нет - то отказываем в доступе
if (!$incomingUser->login()) {
	GlobFunc::accessDenied();
}

/*************************************************************************************
 * ПОЛУЧИМ POST ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Получаем идентификатор уведомления
$messageId = "";
if (isset($_POST['messageId'])) $messageId = intval(htmlspecialchars($_POST['messageId'], ENT_QUOTES));

// Получаем тип уведомления
$messageType = "";
if (isset($_POST['messageType'])) $messageType = htmlspecialchars($_POST['messageType'], ENT_QUOTES);

// Команда пользователя
$action = "";
if (isset($_POST['action'])) $action = htmlspecialchars($_POST['action'], ENT_QUOTES);

// Валидация входных параметров
if ($messageId == "" || $messageId == 0 || $messageType != "newProperty" || $action == "") GlobFunc::accessDenied();

/*************************************************************************************
 * ИНИЦИАЛИЗАЦИЯ МОДЕЛИ УВЕДОМЛЕНИЯ (в зависимости от его типа)
 *************************************************************************************/

switch ($messageType) {
	case "newProperty":
		$message = new MessageNewProperty($messageId);
		break;
}

/*************************************************************************************
 * УДАЛЕНИЕ УВЕДОМЛЕНИЯ
 *************************************************************************************/

if ($action == "remove") {
	// Эта новость принадлежит пользователю, который запросил ее удаление
	if ($message->referToUser($incomingUser->getId())) {
		if (!$message->remove()) GlobFunc::accessDenied();
	} else {
		GlobFunc::accessDenied();
	}
}

/*************************************************************************************
 * УВЕДОМЛЕНИЕ ПРОЧИТАНО
 *************************************************************************************/

if ($action == "isReadedTrue") {
	// Эта новость принадлежит пользователю, который запросил изменение ее статуса прочитанности
	if ($message->referToUser($incomingUser->getId())) {
		if (!$message->changeIsReadedTrue()) GlobFunc::accessDenied();
	} else {
		GlobFunc::accessDenied();
	}
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