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
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/CollectionProperty.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SearchRequest.php';
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
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Команда пользователя
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

// Идентификатор объекта, с которым нужно выполнить действия, указанные в action
$propertyId = "";
if (isset($_GET['propertyId'])) $propertyId = intval(htmlspecialchars($_GET['propertyId'], ENT_QUOTES));

// Вкладка, которая будет открыта по умолчанию при загрузке страницы
$tabsId = "tabs-1";
if (isset($_GET['tabsId'])) $tabsId = htmlspecialchars($_GET['tabsId'], ENT_QUOTES);

// Скрытый идентификатор целевого пользователя (передается только если админ хочет поработать в его личном кабинете)
$compId = "";
if (isset($_GET['compId'])) $compId = intval(htmlspecialchars($_GET['compId'], ENT_QUOTES));

// Проверяем, что у администратора есть право на поиск пользователей и вход в их Личные кабинеты: $isAdmin['searchUser'] == TRUE
$isAdmin = $userIncoming->isAdmin();
if ($isAdmin['searchUser'] && $compId != "" && $compId != 0) {
	$userId = GlobFunc::compIdToId($compId);
} else {
	$userId = $userIncoming->getId();
}
//TODO: дырка в безопасности - если какой-либо из админов узнает мой id, то сможет открыть мой личный кабинет и сделать все что захочет - в том числе получить мой пароль без палева

/*************************************************************************************
 * Получаем информацию о пользователе из БД сервера
 ************************************************************************************/

// Инициализируем полную модель пользователя
$user = new UserFull($userId);
$user->readCharacteristicFromDB();

// Данные поискового запроса
$searchRequest = new SearchRequest($userId);
$searchRequest->writeFromDB();

// Информация о фотографиях пользователя. Метод вызывается во всех случаях, кроме того, когда пользователь отредактировал свои личные параметры и нажал на кнопку "Сохранить"
if ($action != "saveProfileParameters") $user->readFotoInformationFromDB();

// Данные по объектам недвижимости данного пользователя (для которых он является собственником)
$collectionProperty = new CollectionProperty();
$collectionProperty->buildFromOwnerId($user->getId());

// Готовим массив со списком районов в городе пользователя
$allDistrictsInCity = DBconnect::selectDistrictsForCity("Екатеринбург");

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

	// Проверяем корректность данных пользователя.
	$errors = $user->validate("validateProfileParameters");

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

if ($action == "publishAdvert" && $propertyId != "" && $propertyId != 0) {

	// Проверяем: имеет ли данный пользователь право на выполнение изменения статуса объявления
	if ($collectionProperty->hasPropertyId($propertyId)) {

		// Создаем специльный объект для работы с данным объявлением
		$property = new Property($propertyId);
		if ($property->readCharacteristicFromDB() && $property->readFotoInformationFromDB()) {
			$errors = array_merge($errors, $property->publishAdvert());
		} else {
			$errors[] = "Не удалось опубликовать объявление - не получены данные по этому объекту из базы. Повторите попытку немного позже или свяжитесь с нами: 8-922-160-95-14";
		}

		// Переинициализируем коллекцию объектов недвижимости данного пользователя
		$collectionProperty->buildFromOwnerId($user->getId());

	} else {
		$errors[] = "У Вас недостаточно прав для публикации этого объявления";
	}

	// По умолчанию откроем вкладку 3 (Мои объявления)
	$tabsId = "tabs-3";
}

/********************************************************************************
 * СНЯТИЕ С ПУБЛИКАЦИИ ОБЪЯВЛЕНИЯ. Если пользователь отправил команду на снятие с публикации одного из своих объявлений
 *******************************************************************************/

if ($action == "unpublishAdvert" && $propertyId != "" && $propertyId != 0) {

	// Проверяем: имеет ли данный пользователь право на выполнение изменения статуса объявления
	if ($collectionProperty->hasPropertyId($propertyId)) {

		// Создаем специльный объект для работы с данным объявлением
		$property = new Property($propertyId);
		if ($property->readCharacteristicFromDB()) {
			$errors = array_merge($errors, $property->unpublishAdvert());
		} else {
			$errors[] = "Не удалось снять с публикации объявление - ошибка обращения к базе. Повторите попытку немного позже или свяжитесь с нами: 8-922-160-95-14";
		}

		// Переинициализируем коллекцию объектов недвижимости данного пользователя
		$collectionProperty->buildFromOwnerId($user->getId());

	} else {
		$errors[] = "У Вас недостаточно прав для снятия с публикации этого объявления";
	}

	// По умолчанию откроем вкладку 3 (Мои объявления)
	$tabsId = "tabs-3";
}

/********************************************************************************
 * РЕДАКТИРОВАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь отправил редактированные параметры поискового запроса
 *******************************************************************************/

if ($action == "saveSearchParameters") {

	// Записываем POST параметры в параметры объекта пользователя
	$searchRequest->writeParamsFromPOST();

	// Проверяем корректность данных пользователя.
	$errors = $searchRequest->validate("personalRequest");
	if (count($errors) == 0) $correctEditSearchRequest = TRUE; else $correctEditSearchRequest = FALSE; // Считаем ошибки, если 0, то можно принять и сохранить новые параметры поиска

	// Если данные верны, сохраним их в БД
	// Кроме сохранение данных поискового запроса метод перезапишет статус пользователя (typeTenant), так как он теперь точно стал арендатором
	if ($correctEditSearchRequest == TRUE) {
		if ($user->isTenant()) {
			$searchRequest->saveToDB("edit");
		} else {
			$searchRequest->saveToDB("new");
			$user->setTypeTenant(TRUE);
		}
	}

	// При любом искходе валидации параметров поискового запроса открываем вкладку 4 (Поисковый запрос)
	$tabsId = "tabs-4";
}

/********************************************************************************
 * УДАЛЕНИЕ УСЛОВИЙ ПОИСКА
 *******************************************************************************/

if ($action == 'deleteSearchRequest') {

	$err = $searchRequest->remove();

	if (count($err) == 0) {
		$user->setTypeTenant(FALSE);
	}

	$errors = array_merge($errors, $err);
}

/********************************************************************************
 * ЗАПРОС НА СОЗДАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь нажал на кнопку Формирования нового поискового запроса
 *******************************************************************************/

if ($action == "createSearchRequest") {

	// Проверяем корректность данных пользователя.
	$errors = $user->validate("createSearchRequest");
	if (count($errors) == 0) $correctNewSearchRequest = TRUE; else $correctNewSearchRequest = FALSE; // Считаем ошибки, если 0, то можно выдать пользователю форму для ввода параметров Запроса поиска

	// Если создание поискового запроса одобрено (успешно прошли валидации параметров пользователя) открываем вкладку 4 (Поисковый запрос), в противном случае вкладку 1 (Личные параметры пользователя)
	if ($correctNewSearchRequest === TRUE) $tabsId = "tabs-4"; else $tabsId = "tabs-1";
}

/***************************************************************************************************************
 * ИЗБРАННОЕ. Получаем данные по каждому избранному объявлению из БД (это позволит наполнить вкладку tabs-5)
 *
 * Объявления, добавленные в избранное пропадают автоматически из списка после того, как объявление становится неопубликованным или удаляется
 * Но если это же объявление будет опубликовано вновь, то в списке избранного опять оно же у пользователей арендаторов появится.
 * это позволяет арендатору, который вновь стал искать недвижимость сразу в избранном увидеть те объекты, которые ему когда-то нравились и в данный момент сдаются. Скорее всего, ему один из этих объектов и может подойти
 **************************************************************************************************************/

$userIncoming->searchProperties(20);

/********************************************************************************
 * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
 *******************************************************************************/

// Инициализируем используемые в шаблоне(ах) переменные
$isLoggedIn = $userIncoming->login(); // Используется в templ_header.php (при просмотре страницы админом, показывает его статус, а не того пользователя, чьи данные он смотрит)
$userCharacteristic = $user->getCharacteristicData();
$userFotoInformation = $user->getFotoInformationData();
$userSearchRequest = $searchRequest->getSearchRequestData();
$allPropertiesCharacteristic = $collectionProperty->getAllPropertiesCharacteristic();
$allPropertiesFotoInformation = $collectionProperty->getAllPropertiesFotoInformation();
$allPropertiesTenantPretenders = $collectionProperty->getAllPropertiesTenantPretenders();
$propertyLightArr = $userIncoming->getPropertyLightArr();
$propertyFullArr = $userIncoming->getPropertyFullArr();
$favoritePropertiesId = $userIncoming->getFavoritePropertiesId();
$mode = "personal"; // Режим в котором будут работать ряд шаблонов: анкеты пользователя на вкладке №1 (templ_notEditedProfile.php), шаблон для редактирования поискового запроса (templ_editableSearchRequest.php)
$messagesArr = $user->getAllMessagesSorted(); // массив массивов, каждый из которых представляет инфу по 1-ому уведомлению пользователя
$amountUnreadMessages = $userIncoming->getAmountUnreadMessages(); // Количество непрочитанных уведомлений пользователя
$compId = GlobFunc::idToCompId($userId);
//$errors
//$correctNewSearchRequest
//$correctEditSearchRequest
//$correctEditProfileParameters
//$allDistrictsInCity
//$tabsId Идентификатор вкладки, которая будет открыта по умолчанию после загрузки страницы
//$isAdmin

// Подсоединяем нужный основной шаблон
require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_personal.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();