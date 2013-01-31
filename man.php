<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserFull.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SearchRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Получаем идентификатор интересующего (целевого) пользователя
$compId = "";
if (isset($_GET['compId'])) $compId = intval(htmlspecialchars($_GET['compId'], ENT_QUOTES));

// Вычисляем истинный идентификатор целевого пользователя из $compId
$targetUserId = "0";
if ($compId != "" && $compId != 0) {
    $targetUserId = GlobFunc::compIdToId($compId); // Получаем идентификатор пользователя для показа его страницы
} else { // Если в строке GET запроса не указан идентификатор интересующего (целевого) пользователя, то пересылаем нашего пользователя на спец. страницу
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "notfound";
    require $_SERVER['DOCUMENT_ROOT'] . '/templates/templ_error.php';
    exit();
}

/*************************************************************************************
 * ИНИЦИАЛИЗАЦИЯ ПОЛНОЙ МОДЕЛИ ЦЕЛЕВОГО ПОЛЬЗОВАТЕЛЯ
 ************************************************************************************/

// Инициализируем полную модель для целевого пользователя по его идентификатору из GET строки
$user = new UserFull($targetUserId);
$user->readCharacteristicFromDB();
$user->readFotoInformationFromDB();

// Инициализируем модель поискового запроса пользователя
$searchRequest = new SearchRequest($targetUserId);
$searchRequest->writeFromDB();

/*************************************************************************************
 * ПРОВЕРКА ПРАВ ДОСТУПА К СТРАНИЦЕ
 *
 * Правила следующие:
 *
 * Неавторизованный пользователь не имеет права смотреть чью-либо анкету
 * Авторизованный пользователь может смотреть как минимум свою анкету
 *
 * Собственник может смотреть анкеты арендаторов, которые заинтересовались его объектом недвижимости (нажали на кнопку "Контакты собственника").
 * TODO: Собственник теряет право смотреть анкету арендатора, если тот удалил свой поисковый запрос (то есть перестал быть арендатором)
 *
 * Возможно в будущем: Арендатор может смотреть анкеты собственников тех объектов недвижимости, у которых он нажал на кнопку "Контакты собственника" и получил их.
 * Возможно в будущем: Если собственник снял с публикации объект недвижимости, которым интересовался арендатор, то арендатор теряет право смотреть анкету этого собственника
 * Возможно в будущем: Если арендатор удалил поисковый запрос (то есть перестал быть арендатором), то он теряет право смотреть любые анкеты собственников, к которым имел доступ ранее
 *
 ************************************************************************************/

// Если пользователь не авторизован, то он не сможет посмотреть ни одной анкеты
if (!$userIncoming->login()) {
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "accessdenied";
    require $_SERVER['DOCUMENT_ROOT'] . '/templates/templ_error.php';
    exit();
}

// Получаем список пользователей, которые интересовались недвижимостью нашего пользователя ($userIncoming->getId). Он выступает в качестве собственника
$tenantsWithRequestsForOwnerContacts = array();
// Формировать список имеет смысл только, если целевой пользователь на текущий момент времени является арендатором. В ином случае, доступ к анкете целевого пользователя для собственников - закрыт. Таким образом реализуется правило: собственник может видеть только анкеты тех пользователей, которые заинтересовались его недвижимостью и в текущий момент времени являются арендаторами (= имеют поисковый запрос)
if ($user->isTenant()) {
    $tenantsWithRequestsForOwnerContacts = $userIncoming->getAllTenantsId();
}

// Проверяем, есть ли среди этого списка текущий целевой пользователь ($targetUserId)
// Проверка вынесена в отдельный блок, так как это позволяет одновременно проверить несколько условий на доступ к данной странице
// Админы имеют доступ к странице всегда
$isAdmin = $userIncoming->isAdmin();
if (!in_array($targetUserId, $tenantsWithRequestsForOwnerContacts)
    AND $userIncoming->getId() != $targetUserId
    AND !$isAdmin['searchUser']
) {
    // Инициализируем используемые в шаблоне(ах) переменные
    $isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
    $amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
    $mode = "accessdenied";
    require $_SERVER['DOCUMENT_ROOT'] . '/templates/templ_error.php';
    exit();
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
$amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$userCharacteristic = $user->getCharacteristicData();
$userFotoInformation = $user->getFotoInformationData();
$userSearchRequest = $searchRequest->getSearchRequestData();
$mode = "tenantForOwner"; // Режим в котором будет работать шаблон анкеты пользователя (templ_notEditedProfile.php)
//$isAdmin

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_man.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();