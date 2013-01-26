<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/RequestToView.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.'); // TODO: Вернуть ошибку

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $userIncoming->isAdmin();

// Инициализируем переменную для сохранения результата записи нового значения в БД
$res = FALSE;

/*************************************************************************************
 * ПРОВЕРКА ПРАВ ДОСТУПА К СКРИПТУ
 ************************************************************************************/

// Проверяем, залогинен ли пользователь, если нет - то отказываем в доступе
if (!$userIncoming->login()) {
    GlobFunc::accessDenied();
}

// Если пользователь не является администратором, то доступ к скрипту ему запрещен
if (!$isAdmin['searchUser']) {
    GlobFunc::accessDenied();
}

/*************************************************************************************
 * ПОЛУЧИМ POST ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Получаем идентификатор заявки на показ
$requestToViewId = "";
if (isset($_POST['requestToViewId'])) $requestToViewId = intval(htmlspecialchars($_POST['requestToViewId'], ENT_QUOTES));

// Команда пользователя
$action = "";
if (isset($_POST['action'])) $action = htmlspecialchars($_POST['action'], ENT_QUOTES);

// Новое значение, которое нужно присвоить параметру заявки на просмотр
$newValue = "";
if (isset($_POST['newValue'])) $newValue = htmlspecialchars($_POST['newValue'], ENT_QUOTES);

// Если в запросе не указан идентификатор заявки на показ, то отказываем в доступе
if ($requestToViewId == "" || $requestToViewId == 0) {
    GlobFunc::accessDenied();
}

// TODO: проверка нового значения при режиме сохранения статуса на Белый список
// TODO: Проверка action на белый список

/*************************************************************************************
 * ИНИЦИАЛИЗАЦИЯ ЗАПРОСА НА ПРОСМОТР
 *************************************************************************************/

$requestToView = new RequestToView(NULL, NULL, $requestToViewId);

/*************************************************************************************
 * НОВЫЙ СТАТУС ДЛЯ ЗАЯВКИ
 *************************************************************************************/

if ($action == "changeStatus") {
    $requestToView->setStatus($newValue);
    $res = $requestToView->saveParamsToDB();
}

if ($action == "changeTenantTime") {
    $requestToView->setTenantTime($newValue);
    $res = $requestToView->saveParamsToDB();
}

if ($action == "changeTenantComment") {
    $requestToView->setTenantComment($newValue);
    $res = $requestToView->saveParamsToDB();
}

/*************************************************************************************
 * Если все хорошо - возвращаем положительный статус выполнения операции
 *************************************************************************************/

if ($res) {
    header('Content-Type: text/xml; charset=UTF-8');
    echo "<xml><span status='successful'></span></xml>";
} else {
    GlobFunc::accessDenied();
}


/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();