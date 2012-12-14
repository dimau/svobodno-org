<?php
/* Страница администратора для отображения данных о найденных пользователях и их объектах недвижимости */

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

// Инициализируем ассоциативный массив, в который будем складывать все параметры искомого пользователя
$goalUser = array();

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

/********************************************************************************
 * ПОЛУЧИМ ДАННЫЕ ЗАПРОСА НА ПОИСК ПОЛЬЗОВАТЕЛЯ
 *******************************************************************************/

if (isset($_POST['surname'])) $goalUser['surname'] = htmlspecialchars($_POST['surname'], ENT_QUOTES);
if (isset($_POST['name'])) $goalUser['name'] = htmlspecialchars($_POST['name'], ENT_QUOTES);
if (isset($_POST['secondName'])) $goalUser['secondName'] = htmlspecialchars($_POST['secondName'], ENT_QUOTES);
if (isset($_POST['login'])) $goalUser['login'] = htmlspecialchars($_POST['login'], ENT_QUOTES);
if (isset($_POST['telephon'])) $goalUser['telephon'] = htmlspecialchars($_POST['telephon'], ENT_QUOTES);
if (isset($_POST['email'])) $goalUser['email'] = htmlspecialchars($_POST['email'], ENT_QUOTES);
if (isset($_POST['address'])) $goalUser['address'] = htmlspecialchars($_POST['address'], ENT_QUOTES);

/********************************************************************************
 * ПОЛУЧАЕМ РЕЗУЛЬТАТЫ ПОИСКА ИЗ БД
 *******************************************************************************/

// Инициализируем массивы для хранения результатов
$allUsers = array();
$allProperties = array();
$allRequestsToView = array();

// ЗАПРОС К ТАБЛИЦЕ USERS. Если хотя одно поле из тех, что связаны с параметрами пользователя, а не его недвижимости заполнено
if ($goalUser['surname'] != "" || $goalUser['name'] != "" || $goalUser['secondName'] != "" || $goalUser['login'] != "" || $goalUser['telephon'] != "" || $goalUser['email'] != "") {

	// Инициализируем массив, в который будем собирать условия поиска
	$searchLimits = array(); // массив условий для поиска в таблице users

	// Ограничение на ФИО и логин
	$searchLimits['surname'] = "";
	if (isset($goalUser['surname']) && $goalUser['surname'] != "") $searchLimits['surname'] = " (surname = '" . $goalUser['surname'] . "')";
	$searchLimits['name'] = "";
	if (isset($goalUser['name']) && $goalUser['name'] != "") $searchLimits['name'] = " (name = '" . $goalUser['name'] . "')";
	$searchLimits['secondName'] = "";
	if (isset($goalUser['secondName']) && $goalUser['secondName'] != "") $searchLimits['secondName'] = " (secondName = '" . $goalUser['secondName'] . "')";
	$searchLimits['login'] = "";
	if (isset($goalUser['login']) && $goalUser['login'] != "") $searchLimits['login'] = " (login = '" . $goalUser['login'] . "')";

	// Ограничение на телефон и e-mail
	$searchLimits['telephon'] = "";
	if (isset($goalUser['telephon']) && $goalUser['telephon'] != "") $searchLimits['telephon'] = " (telephon = '" . $goalUser['telephon'] . "')";
	$searchLimits['email'] = "";
	if (isset($goalUser['email']) && $goalUser['email'] != "") $searchLimits['email'] = " (email = '" . $goalUser['email'] . "')";


	// Собираем строку WHERE для поискового запроса к таблице users
	$strWHERE = "";
	foreach ($searchLimits as $value) {
		if ($value == "") continue;
		if ($strWHERE != "") $strWHERE .= " AND" . $value; else $strWHERE .= $value;
	}

	// Получаем данные из БД
	// Количество результатов ограничено первыми 20-тью, чтобы не перегружать БД
	// В итоге получим массив ($allUsers), каждый элемент которого представляет собой еще один массив параметров конкретного пользователя
	if ($strWHERE != "") {
		$res = DBconnect::get()->query("SELECT id, typeTenant, typeOwner, name, secondName, surname, login, password, telephon, email FROM users WHERE".$strWHERE." LIMIT 20");
		if ((DBconnect::get()->errno)
			OR (($allUsers = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			$allUsers = array();
		}
	}


	// Собираем строку WHERE для поискового запроса к БД по соответствующим объектам недвижимости
	$strWHERE = "";
	if (is_array($allUsers) && count($allUsers) != 0) {
		foreach ($allUsers as $value) {
			if ($strWHERE != "") $strWHERE .= " OR (userId = '" . $value['id'] . "')"; else $strWHERE .= " (userId = '" . $value['id'] . "')";
		}
	}

	// Получим информацию по заявкам на просмотр от найденных пользователей
	// Часть WHERE у данного запроса к таблице requestToView аналогична ранее собранному $strWHERE с той лишь разницей, что вместо userId используется tenantId
	$strWHEREForRequestToView = str_replace("userId", "tenantId", $strWHERE);
	if ($strWHEREForRequestToView != "") {
		$res = DBconnect::get()->query("SELECT id, tenantId, propertyId, status FROM requestToView WHERE".$strWHEREForRequestToView);
		if ((DBconnect::get()->errno)
			OR (($allRequestsToView = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			$allRequestsToView = array();
		}
	}

	// Все полученные заявки на просмотр нужно дополнить информацией об адресах - для этого добавим в строку $strWHERE условия по нужным нам объектам
	if (is_array($allRequestsToView) && count($allRequestsToView) != 0) {
		foreach ($allRequestsToView as $value) {
			if ($strWHERE != "") $strWHERE .= " OR (id = '" . $value['propertyId'] . "')"; else $strWHERE .= " (id = '" . $value['propertyId'] . "')";
		}
	}

	// Получим информацию по объектам недвижимости, которые принадлежат найденным пользователям
	// В итоге получим массив ($allProperties), каждый элемент которого представляет собой еще один массив параметров конкретного объекта недвижимости, принадлежащего одному из найденных выше пользователей
	if ($strWHERE != "") {
		$res = DBconnect::get()->query("SELECT id, userId, typeOfObject, address, apartmentNumber, status, earliestDate, earliestTimeHours, earliestTimeMinutes, adminComment, completeness FROM property WHERE".$strWHERE);
		if ((DBconnect::get()->errno)
			OR (($allProperties = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			$allProperties = array();
		}
	}

	// Подкорректируем полученные данные для их нормального вывода на экран
	for ($i = 0, $s = count($allProperties); $i < $s; $i++) {
		$allProperties[$i]['earliestDate'] = GlobFunc::dateFromDBToView($allProperties[$i]['earliestDate']);
	}

	// Дополним данные о заявках на просмотр информацией об адресе соответствующего объекта (на который поступила заявка)
	for ($i = 0, $s = count($allRequestsToView); $i < $s; $i++) {
		foreach ($allProperties as $property) {
			if ($allRequestsToView[$i]['propertyId'] == $property['id']) {
				$allRequestsToView[$i]['address'] = $property['address'];
				$allRequestsToView[$i]['apartmentNumber'] = $property['apartmentNumber'];
				break;
			}
		}
	}

} elseif ($goalUser['address'] != "") { // ЗАПРОС К ТАБЛИЦЕ PROPERTY. Если поля, связанные с параметрами пользователя пусты, а поле с адресом недвижимости заполнено

	// Инициализируем массив, в который будем собирать условия поиска
	$searchLimits = array(); // массив условий для поиска в таблице property

	// Ограничение на адрес объекта недвижимости (работает только для собственников)
	$searchLimits['address'] = "";
	if (isset($goalUser['address']) && $goalUser['address'] != "") $searchLimits['address'] = " (address LIKE '%" . $goalUser['address'] . "%')";

	// Собираем строку WHERE для поискового запроса к таблице property
	$strWHERE = "";
	foreach ($searchLimits as $value) {
		if ($value == "") continue;
		if ($strWHERE != "") $strWHERE .= " AND" . $value; else $strWHERE .= $value;
	}

	// Получим информацию по объектам недвижимости, чьи адреса похожи на тот, что указал администратор
	// Количество результатов ограничено первыми 40-ка, чтобы не перегружать БД
	// В итоге получим массив ($allProperties), каждый элемент которого представляет собой еще один массив параметров конкретного объекта недвижимости
	if ($strWHERE != "") {
		$res = DBconnect::get()->query("SELECT id, userId, typeOfObject, address, apartmentNumber, status, earliestDate, earliestTimeHours, earliestTimeMinutes, adminComment, completeness FROM property WHERE".$strWHERE." LIMIT 40");
		if ((DBconnect::get()->errno)
			OR (($allProperties = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			$allProperties = array();
		}
	}

	// Соберем уникальные id пользователей из полученного массива
	$allUsersId = array();
	foreach ($allProperties as $value) {
		$allUsersId[] = $value['userId'];
	}
	$allUsersId = array_unique($allUsersId);

	// Сформируем строку WHERE для получения данных по собственникам найденных объектов
	$strWHERE = "";
	if (is_array($allUsersId) && count($allUsersId) != 0) {
		foreach ($allUsersId as $value) {
			if ($strWHERE != "") $strWHERE .= " OR (id = '" . $value . "')"; else $strWHERE .= " (id = '" . $value . "')";
		}
	}

	// Получаем информацию о собственниках той недвижимости, что мы ранее отобрали по адресу ($allProperties)
	// В итоге получим массив ($allUsers), каждый элемент которого представляет собой еще один массив параметров конкретного пользователя
	if ($strWHERE != "") {
		$res = DBconnect::get()->query("SELECT id, typeTenant, typeOwner, name, secondName, surname, login, password, telephon, email FROM users WHERE".$strWHERE);
		if ((DBconnect::get()->errno)
			OR (($allUsers = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			$allUsers = array();
		}
	}

	// Если поиск производится по адресу недвижимости, то в выдаче будут только пользователи-собственники. Скорее всего у них запросов на просмотр (если только они одновременно не сдают и не снимают жилье), поэтому поиск по заявкам не будем осуществлять
	$allRequestsToView = array();
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

//$allUsers  массив, каждый элемент которого представляет собой еще один массив параметров конкретного пользователя
//$allProperties  массив, каждый элемент которого представляет собой еще один массив параметров конкретного объекта недвижимости, принадлежащего одному из найденных пользователей
//$allRequestsToView	массив, каждый элемент которого представляет собой еще один массив параметров конкретной заявки на просмотр, принадлежащего одному из найденных пользователей

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminFindUser.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();