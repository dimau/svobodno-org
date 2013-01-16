<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserIncoming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
if (!$userIncoming->login()) {
	header('Location: login.php');
	exit();
}

/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 ************************************************************************************/

// Получаем команду из строки запроса
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

// Получаем идентификатор объявления для редактирования из строки запроса
$propertyId = "";
if (isset($_GET['propertyId'])) $propertyId = intval(htmlspecialchars($_GET['propertyId'], ENT_QUOTES));

// Если в строке не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет
if ($propertyId == "" || $propertyId == 0) {
	header('Location: personal.php?tabsId=3');
	exit();
}

/*************************************************************************************
 * Инициализируем объект для работы с параметрами недвижимости
 ************************************************************************************/

$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB() || !$property->readFotoInformationFromDB()) {
	die('Ошибка при работе с базой данных (. Попробуйте зайти к нам немного позже.'); // Если получить данные из БД не удалось, то просим пользователя зайти к нам немного позже
}

// Готовим массив со списком районов в городе пользователя
$allDistrictsInCity = DBconnect::selectDistrictsForCity("Екатеринбург");

// Инициализируем массив для хранения ошибок проверки данных объекта недвижимости
$errors = array();

/**************************************************************************************************************
 * Проверяем, что пользователь имеет право редактировать данное объявление - он является собственником данного объекта недвижимости или админом
 **************************************************************************************************************/

$isAdmin = $userIncoming->isAdmin();
if ($property->getUserId() != $userIncoming->getId() AND !$isAdmin['searchUser'] AND !$isAdmin['newAdvertAlien']) {
	header('Location: personal.php?tabsId=3');
	exit();
}

/*************************************************************************************
 * Если пользователь заполнил и отослал форму - проверяем ее
 ************************************************************************************/

if ($action == "saveAdvert") {

    // Если редактирует объявление админ, то его поля для редактирования не ограничены. Для собственника есть ограничения на редактируемые поля
	if ($isAdmin['searchUser'] || $isAdmin['newAdvertAlien']) {
        $property->writeCharacteristicFromPOST("full");
    } else {
        $property->writeCharacteristicFromPOST("limited");
    }
	$property->writeFotoInformationFromPOST();

	// Проверяем корректность данных объявления. Функции validate() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
	// Если мы имеем дело с редактированием чужого объявления администратором, то проверки данных происходят по упрощенному способу
	if ($property->getCompleteness() == "0") {
		$errors = $property->validate("editAlienAdvert");
	} else {
		$errors = $property->validate("editAdvert");
	}

	// Если данные, указанные пользователем, корректны, сохраним данные объявления в базу данных
	if (is_array($errors) && count($errors) == 0) {

		// Сохраняем отредактированные параметры объявления на текущего пользователя
		$correctSaveCharacteristicToDB = $property->saveCharacteristicToDB("edit");

		if ($correctSaveCharacteristicToDB) {

			// Сохраним информацию о фотографиях объекта недвижимости
			$correctSaveFotoInformationToDB = $property->saveFotoInformationToDB();

			if ($correctSaveFotoInformationToDB) {

				// Пересылаем пользователя на страницу с подробным описанием его объявления - хороший способ убедиться в том, что все данные указаны верно
				header('Location: property.php?propertyId=' . $property->getId());
				exit();

			} else {

				$errors[] = 'К сожалению, при сохранении данных о фотографиях произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку';
				// Сохранении данных о фотках в БД не прошло - сами изменения в объявлении сохранене, но изменения в данных о фотографиях не сохранены.
			}


		} else {

			$errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку';
			// Сохранении данных в БД не прошло - объявление не сохранено
		}

	}
}

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $userIncoming->login(); // Используется в templ_header.php
$amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$propertyCharacteristic = $property->getCharacteristicData();
$propertyFotoInformation = $property->getFotoInformationData();
$compId = GlobFunc::idToCompId($propertyCharacteristic['userId']);
if ($isAdmin['searchUser'] || $isAdmin['newAdvertAlien']) { // Определяет доступность полей для редактирования. Все поля доступны для админов, ограниченное количество полей доступны для редактирования собственникам
    $mode = "editFull";
} else {
    $mode = "editLimited";
}
//$allDistrictsInCity
//$errors
//$isAdmin

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_changeadvert.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();