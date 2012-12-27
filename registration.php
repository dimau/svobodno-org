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

// Уточняем - имеет ли пользователь права админа.
$isAdmin = $userIncoming->isAdmin();

// Проверим, быть может пользователь уже авторизирован. Если это так и он не является администратором, перенаправим его на главную страницу сайта
if ($userIncoming->login() && !$isAdmin['newOwner'] && !$isAdmin['newAdvertAlien']) {
	header('Location: personal.php');
	exit();
}

// Инициализируем полную модель неавторизованного пользователя
$user = new UserFull(NULL);
// На странице регистрации важно получить роль, в которой регистрируется пользователь - арендатор или собственник (от этого зависит набор обязательных для заполнения параметров). Инициализируем параметры модели typeTenant и typeOwner в соответствии с ролью регистрируемого пользователя
$user->setTypeTenantOwnerFromGET();

// Инициализируем модель поискового запроса (в него будут писаться параметры поиска пользователя)
$searchRequest = new SearchRequest(NULL);

// Инициализируем переменную для сохранения ошибок, связанных с регистрационными данными пользователя, и других ошибок, которые не позволили успешно закончить его регистрацию
$errors = array();

// Готовим массив со списком районов в городе пользователя
$allDistrictsInCity = DBconnect::selectDistrictsForCity("Екатеринбург");

/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Команда пользователя
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

// Режим регистрации собственника из чужой базы
$alienOwner = "";
if (isset($_GET['alienOwner'])) $alienOwner = htmlspecialchars($_GET['alienOwner'], ENT_QUOTES);

/********************************************************************************
 * ОТКУДА К НАМ ПРИШЕЛ ПОЛЬЗОВАТЕЛЬ
 *******************************************************************************/

// Запоминаем в параметры сессии адрес URL, с которого на регистрацию пришел пользователь (это позволит вернуть его после регистрации на ту же страницу)
if (isset($_SERVER['HTTP_REFERER'])) {
	$hostName = explode("?", $_SERVER['HTTP_REFERER']);
	// Важно модифицировать адрес, с которого пользователь попал на страницу регистрации только, если он не перегружал саму страницу регистрации (например, в случае отправки ошибочной формы)
	if ($hostName[0] != "http://svobodno.org/registration.php" && $hostName[0] != "http://localhost/registration.php") {
		$_SESSION['url_initial'] = $_SERVER['HTTP_REFERER'];
	}
}
// Если вдруг в переменную сессии попал адрес, который не относится к нашему домену, то удалим его от греха подальше
if (isset($_SESSION['url_initial']) && !preg_match('~((http://svobodno.org)|(http://localhost))~', $_SESSION['url_initial'])) {
	unlink($_SESSION['url_initial']);
}

/********************************************************************************
 * ОТПРАВЛЕНА ФОРМА РЕГИСТРАЦИИ
 *******************************************************************************/

if ($action == "registration") {

	// Запишем POST параметры в модели
	$user->writeCharacteristicFromPOST();
	$user->writeFotoInformationFromPOST();
	$searchRequest->writeParamsFromPOST();

	// Проверяем корректность данных пользователя.
	// Если мы имеем дело с созданием нового чужого объявления администратором, то проводим минимальную проверку данных
	if ($isAdmin['newAdvertAlien'] && $alienOwner == "true") {
		$errors = $user->validate("newAlienOwner");
	} else {
		$errors = $user->validate("registration");
		if ($user->isTenant()) $errors = array_merge($errors, $searchRequest->validate("personalRequest"));
	}

	// Если данные, указанные пользователем, корректны, запишем их в базу данных
	if (is_array($errors) && count($errors) == 0) {

		// Если сохранение Личных данных пользователя прошло успешно, то считаем, что пользователь уже зарегистрирован, выполняем сохранение в БД остальных данных (фотографии и поисковый запрос)
		if ($user->saveCharacteristicToDB("new")) {

			// Сохраним информацию о фотографиях пользователя
			// Функция вызывать необходимо независимо от того, есть ли в uploadedFoto информация о фотографиях или нет, так как при регистрации пользователь мог сначала выбрать фотографии, а затем их удалить. В этом случае $this->saveFotoInformationToDB почистит БД и серве от удаленных пользователем файлов
			$user->saveFotoInformationToDB();

			// Сохраняем поисковый запрос, если пользователь регистрируется в качестве арендатора
			if ($user->isTenant()) {
				$searchRequest->setUserId($user->getId());
				$searchRequest->saveToDB("new");
			}

            // Сообщаем операторам о появлении нового зарегистрированного пользователя
            $compId = GlobFunc::idToCompId($user->getId());
            $subject = 'Новый зарегистрированный пользователь: '.$user->getSurname()." ".$user->getName()." ".$user->getSecondName();
            $msgHTML = "Новый пользователь зарегистрировался на сайте:<br>
                Дата: ".date('d.m.Y H:i')."<br>
                Кто: ".$user->getSurname()." ".$user->getName()." ".$user->getSecondName()."<br>
                Арендатор: ".$user->isTenant()." Собственник: ".$user->isOwner()."<br>
                Подписан на e-mail рассылку: ".$searchRequest->getNeedEmail()."<br>
                <a href='http://svobodno.org/personal.php?compId=".$compId."'>Детально о пользователе</a>";
            GlobFunc::sendEmailToOperator($subject, $msgHTML);

			/******* Авторизовываем пользователя *******/
			// Если админ заводил нового пользователя, то авторизация под новым пользователем нам не нужна
			if ($isAdmin['newOwner'] || $isAdmin['newAdvertAlien'] || $isAdmin['searchUser']) {
				$correctEnter = array();
			} else {
				$correctEnter = $userIncoming->enter();
			}

			if (count($correctEnter) == 0) //если нет ошибок, отправляем уже авторизованного пользователя на страницу успешной регистрации
			{
				header('Location: successfullRegistration.php');
				exit();
			} else {
				// Здесь можно закодить действия при возникновении ошибки авторизации
			}

		} else { // Если сохранить личные данные пользователя в БД не удалось

			$errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку регистрации';
			// Сохранении данных в БД не прошло - пользователь не зарегистрирован
		}

	}
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
$mode = "registration";
//$alienOwner
//$isAdmin
//$errors
//$allDistrictsInCity

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_registration.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();