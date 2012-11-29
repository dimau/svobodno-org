<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
include 'models/DBconnect.php';
include 'models/GlobFunc.php';
include 'models/Logger.php';
include 'models/IncomingUser.php';
include 'views/View.php';
include 'models/User.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$incomingUser = new IncomingUser();

$isAdmin = $incomingUser->isAdmin();
// Проверим, быть может пользователь уже авторизирован. Если это так и он не является администратором, перенаправим его на главную страницу сайта
if ($incomingUser->login() && !$isAdmin['newOwner'] && !$isAdmin['newAdvertAlien']) {
	header('Location: personal.php');
}

// Инициализируем полную модель неавторизованного пользователя
$user = new User(FALSE);
// На странице регистрации важно получить роль, в которой регистрируется пользователь - арендатор или собственник (от этого зависит набор обязательных для заполнения параметров). Инициализируем параметры модели typeTenant и typeOwner в соответствии с ролью регистрируемого пользователя
$user->setTypeTenantOwnerFromGET();

// Инициализируем переменную для сохранения ошибок, связанных с регистрационными данными пользователя, и других ошибок, которые не позволили успешно закончить его регистрацию
$errors = array();

// Готовим массив со списком районов в городе пользователя
$allDistrictsInCity = GlobFunc::getAllDistrictsInCity("Екатеринбург");

// Запоминаем в параметры сессии адрес URL, с которого на регистрацию пришел пользователь (это позволит вернуть его после регистрации на ту же страницу)
if (isset($_SERVER['HTTP_REFERER'])) {
	$hostName = explode("?", $_SERVER['HTTP_REFERER']);

	// Важно модифицировать адрес, с которого пользователь попал на страницу регистрации только, если он не перегружал саму страницу регистрации (например, в случае отправк иошибочной формы)
	if ($hostName[0] != "http://svobodno.org/registration.php" && $hostName[0] != "http://localhost/registration.php") {
		$_SESSION['url_initial'] = $_SERVER['HTTP_REFERER'];
	}
}

// Возможно администратор хочет зарегистрировать чужого собственника? Если это так, то применяем минимум проверок к данным о регистрируемом пользователе
if ($isAdmin['newAdvertAlien'] && isset($_GET['alienOwner']) && $_GET['alienOwner'] == "true") {
	$isAlienOwnerRegistration = TRUE;
} else {
	$isAlienOwnerRegistration = FALSE;
}

/********************************************************************************
 * ОТПРАВЛЕНА ФОРМА РЕГИСТРАЦИИ
 *******************************************************************************/

if (isset($_POST['submitButton'])) {

	// Записываем POST параметры в параметры объекта пользователя
	$user->writeCharacteristicFromPOST();
	$user->writeFotoInformationFromPOST();
	$user->writeSearchRequestFromPOST();

	// Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
	// Если мы имеем дело с созданием нового чужого объявления администратором, то проводим минимальную проверку данных
	if ($isAlienOwnerRegistration) {
		$errors = $user->userDataCorrect("newAlienOwner");
	} else {
		$errors = $user->userDataCorrect("registration");
	}

	// Если данные, указанные пользователем, корректны, запишем их в базу данных
	if (is_array($errors) && count($errors) == 0) {

		$correctSaveCharacteristicToDB = $user->saveCharacteristicToDB("new");

		// Если сохранение Личных данных пользователя прошло успешно, то считаем, что пользователь уже зарегистрирован, выполняем сохранение в БД остальных данных (фотографии и поисковый запрос)
		if ($correctSaveCharacteristicToDB) {

			// Узнаем id пользователя - необходимо при сохранении информации о фотке в постоянную базу
			$user->getIdUseLogin();

			// Сохраним информацию о фотографиях пользователя
			// Функция вызывать необходимо независимо от того, есть ли в uploadedFoto информация о фотографиях или нет, так как при регистрации пользователь мог сначала выбрать фотографии, а затем их удалить. В этом случае $this->saveFotoInformationToDB почистит БД и серве от удаленных пользователем файлов
			$user->saveFotoInformationToDB();

			// Сохраняем поисковый запрос, если пользователь регистрируется в качестве арендатора
			if ($user->typeTenant) {
				$user->saveSearchRequestToDB("new");
			}

			/******* Авторизовываем пользователя *******/
			// Если админ заводил нового пользователя, то авторизация под новым пользователем нам не нужна
			if ($isAdmin['newOwner'] || $isAdmin['newAdvertAlien'] || $isAdmin['searchUser']) {
				$correctEnter = array();
			} else {
				$correctEnter = $incomingUser->enter();
			}

			if (count($correctEnter) == 0) //если нет ошибок, отправляем уже авторизованного пользователя на страницу успешной регистрации
			{
				header('Location: successfullRegistration.php');
			} else {
				// TODO:что-то нужно делать в случае, если возникли ошибки при авторизации во время регистрации - как минимум вывести их текст во всплывающем окошке
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
$isLoggedIn = $incomingUser->login(); // Используется в templ_header.php
$amountUnreadMessages = $incomingUser->getAmountUnreadMessages(); // Количество непрочитанных сообщений пользователя
$userCharacteristic = $user->getCharacteristicData();
$userFotoInformation = $user->getFotoInformationData();
$userSearchRequest = $user->getSearchRequestData();
$mode = "registration";
//$isAlienOwnerRegistration
//$errors
//$allDistrictsInCity

// Подсоединяем нужный основной шаблон
include "templates/"."templ_registration.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();