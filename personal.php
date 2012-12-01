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
include 'models/CollectionProperty.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$incomingUser = new IncomingUser();

// Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
if (!$incomingUser->login()) {
	header('Location: login.php');
}

/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Команда пользователя
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

// Идентификатор объекта для просмотра
$propertyId = "";
if (isset($_GET['propertyId'])) $propertyId = htmlspecialchars($_GET['propertyId'], ENT_QUOTES);

// Вкладка, которая будет открыта по умолчанию при загрузке страницы
$tabsId = "tabs-1";
if (isset($_GET['tabsId'])) $tabsId = htmlspecialchars($_GET['tabsId'], ENT_QUOTES);

// Скрытый идентификатор целевого пользователя (передается только если админ хочет поработать в его личном кабинете)
$compId = "";
if (isset($_GET['compId'])) $compId = htmlspecialchars($_GET['compId'], ENT_QUOTES);

// Проверяем, что у администратора есть право на поиск пользователей и вход в их Личные кабинеты: $isAdmin['searchUser'] == TRUE
$isAdmin = $incomingUser->isAdmin();
if ($isAdmin['searchUser'] && $compId != "" && is_int($compId)) {
	$userId = GlobFunc::compIdToId($compId);
} else {
	$userId = $incomingUser->getId();
}

/*************************************************************************************
 * Получаем информацию о пользователе из БД сервера
 ************************************************************************************/

// Инициализируем полную модель пользователя
$user = new User($userId);

// Анкетные (основные персональные) данные пользователя
$user->writeCharacteristicFromDB();

// Данные поискового запроса
if ($action == 'deleteSearchRequest') {
	// Если пользователь пожелал удалить поисковый запрос, то это нужно сделать вместо получения данных из БД
	$user->removeSearchRequest();
} else {
	// Иначе получим данные из БД по поисковому запросу данного пользователя в параметры объекта $user
	$user->writeSearchRequestFromDB();
}

// Информация о фотографиях пользователя. Метод вызывается во всех случаях, кроме того, когда пользователь отредактировал свои личные параметры и нажал на кнопку "Сохранить"
if ($action != "saveProfileParameters") $user->writeFotoInformationFromDB();

// Данные по объектам недвижимости данного пользователя (для которых он является собственником)
$collectionProperty = new CollectionProperty();
$collectionProperty->buildFromOwnerId($user->getId());

// Готовим массив со списком районов в городе пользователя
$allDistrictsInCity = GlobFunc::getAllDistrictsInCity("Екатеринбург");

// Инициализируем переменные корректности - используется при формировании нового Запроса на поиск
$errors = array();
$correctNewSearchRequest = NULL; // Отражает корректность и полноту личных данных пользователя, необходимую для создания НОВОГО поискового запроса.
$correctEditSearchRequest = NULL; // Отражает корректность отредактированных пользователем параметров поиска
$correctEditProfileParameters = NULL; // Корректность личных данных пользователя. Работает, если он пытается изменить личные данные своего профайла. Проверка осуществляется в соответствии со статусом пользователя (арендатор или собственник)

/********************************************************************************
 * РЕДАКТИРОВАНИЕ ЛИЧНЫХ ДАННЫХ ПРОФИЛЯ. Если пользователь отправил редактированные параметры своего профиля
 *******************************************************************************/

if ($action == "saveProfileParameters") {

	// Записываем POST параметры в параметры объекта пользователя
	$user->writeCharacteristicFromPOST();
	$user->writeFotoInformationFromPOST();

	// Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
	$errors = $user->userDataCorrect("validateProfileParameters");

	// Установим признак корректности введенных пользователем новых личных параметров
	if (is_array($errors) && count($errors) == 0) {
		$correctEditProfileParameters = TRUE;
	} else {
		$correctEditProfileParameters = FALSE;
	}

	// Если данные верны, сохраним их в БД
	if ($correctEditProfileParameters == TRUE) {

		// Личная информация
		$correctSaveCharacteristicToDB = $user->saveCharacteristicToDB("edit");

		if ($correctSaveCharacteristicToDB) {
			// Сохраним информацию о фотографиях пользователя
			$user->saveFotoInformationToDB();
		} else {
			$errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и нажмите кнопку Сохранить';
			// Сохранении данных в БД не прошло - данные пользователя не сохранены
		}

	}

	// По умолчанию откроем вкладку 1 (Профайл)
	$tabsId = "tabs-1";
}

/********************************************************************************
 * ПУБЛИКАЦИЯ ОБЪЯВЛЕНИЯ. Если пользователь отправил команду на публикацию одного из своих объявлений
 *******************************************************************************/

if ($action == "publicationOn" && $propertyId != "" && is_int($propertyId)) {

	// Проверяем: имеет ли данный пользователь право на выполнение изменения статуса объявления
	if ($collectionProperty->hasPropertyId($propertyId)) {
		$collectionProperty->setPublicationStatus("опубликовано", $propertyId);
	}

	// По умолчанию откроем вкладку 3 (Мои объявления)
	$tabsId = "tabs-3";
}

/********************************************************************************
 * СНЯТИЕ С ПУБЛИКАЦИИ ОБЪЯВЛЕНИЯ. Если пользователь отправил команду на снятие с публикации одного из своих объявлений
 *******************************************************************************/

if ($action == "publicationOff" && $propertyId != "" && is_int($propertyId)) {

	// Проверяем: имеет ли данный пользователь право на выполнение изменения статуса объявления
	if ($collectionProperty->hasPropertyId($propertyId)) {
		$collectionProperty->setPublicationStatus("не опубликовано", $propertyId);
	}

	// По умолчанию откроем вкладку 3 (Мои объявления)
	$tabsId = "tabs-3";
}

/********************************************************************************
 * РЕДАКТИРОВАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь отправил редактированные параметры поискового запроса
 *******************************************************************************/

if ($action == "saveSearchParameters") {

	// Записываем POST параметры в параметры объекта пользователя
	$user->writeSearchRequestFromPOST();

	// Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
	$errors = $user->userDataCorrect("validateSearchRequest"); // Параметр validateSearchRequest задает режим проверки "Проверка корректности уже существующих параметров поиска", который активирует только соответствующие ему проверки
	if (count($errors) == 0) $correctEditSearchRequest = TRUE; else $correctEditSearchRequest = FALSE; // Считаем ошибки, если 0, то можно принять и сохранить новые параметры поиска

	// Если данные верны, сохраним их в БД
	// Кроме сохранение данных поискового запроса метод перезапишет статус пользователя (typeTenant), так как он теперь точно стал арендатором
	if ($correctEditSearchRequest == TRUE) {
		$user->saveSearchRequestToDB("edit");
	}

	// При любом искходе валидации параметров поискового запроса открываем вкладку 4 (Поисковый запрос)
	$tabsId = "tabs-4";
}

/********************************************************************************
 * ЗАПРОС НА СОЗДАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь нажал на кнопку Формирования нового поискового запроса
 *******************************************************************************/

if ($action == "createSearchRequest") {

	// Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
	$errors = $user->userDataCorrect("createSearchRequest"); // Параметр createSearchRequest задает режим проверки "Создание запроса на поиск", который активирует только соответствующие ему проверки
	if (count($errors) == 0) $correctNewSearchRequest = TRUE; else $correctNewSearchRequest = FALSE; // Считаем ошибки, если 0, то можно выдать пользователю форму для ввода параметров Запроса поиска

	// Если создание поискового запроса одобрено (успешно прошли валидации параметров пользователя) открываем вкладку 4 (Поисковый запрос), в противном случае вкладку 1 (Личные параметры пользователя)
	if ($correctNewSearchRequest === TRUE) $tabsId = "tabs-4"; else $tabsId = "tabs-1";
}

/***************************************************************************************************************
 * ИЗБРАННОЕ. Получаем данные по каждому избранному объявлению из БД (это позволит наполнить вкладку tabs-5)
 **************************************************************************************************************/

$incomingUser->searchProperties(20);

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $incomingUser->login(); // Используется в templ_header.php (при просмотре страницы админом, показывает его статус, а не того пользователя, чьи данные он смотрит)
$amountUnreadMessages = $incomingUser->getAmountUnreadMessages(); // Количество непрочитанных сообщений пользователя (при просмотре админом - показывает количество непросмотренных сообщений админа)
$userCharacteristic = $user->getCharacteristicData();
$userFotoInformation = $user->getFotoInformationData();
$userSearchRequest = $user->getSearchRequestData();
$allPropertiesCharacteristic = $collectionProperty->getAllPropertiesCharacteristic();
$allPropertiesFotoInformation = $collectionProperty->getAllPropertiesFotoInformation();
$allPropertiesTenantPretenders = $collectionProperty->getAllPropertiesTenantPretenders();
$propertyLightArr = $incomingUser->getPropertyLightArr();
$propertyFullArr = $incomingUser->getPropertyFullArr();
$favoritesPropertysId = $incomingUser->getFavoritesPropertysId();
$mode = "personal"; // Режим в котором будут работать ряд шаблонов: анкеты пользователя на вкладке №1 (templ_notEditedProfile.php), шаблон для редактирования поискового запроса (templ_editableSearchRequest.php)
$messagesArr = $user->getAllMessagesSorted(); // массив массивов, каждый из которых представляет инфу по 1-ому сообщению (новости пользователя)
$compId = GlobFunc::idToCompId($userId);
//$errors
//$correctNewSearchRequest
//$correctEditSearchRequest
//$correctEditProfileParameters
//$allDistrictsInCity
//$tabsId // Идентификатор вкладки, которая будет открыта по умолчанию после загрузки страницы

// Подсоединяем нужный основной шаблон
include "templates/"."templ_personal.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();