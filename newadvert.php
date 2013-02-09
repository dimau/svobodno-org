<?php
// Стартуем сессию с пользователем - сделать доступными переменные сессии
session_start();

// Подключаем нужные модели и представления
$websiteRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/models/User.php';
require_once $websiteRoot . '/models/UserIncoming.php';
require_once $websiteRoot . '/models/Property.php';
require_once $websiteRoot . '/views/View.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Инициализируем модель для запросившего страницу пользователя
$userIncoming = new UserIncoming();

// Инициализируем массив для хранения ошибок проверки данных объекта недвижимости
$errors = array();

/*************************************************************************************
 * ПРОВЕРКА ПРАВ ДОСТУПА К СТРАНИЦЕ
 ************************************************************************************/

// Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
if (!$userIncoming->login()) {
    header('Location: login.php');
    exit();
}

// Если пользователь не является администратором, то доступ к странице ему запрещен - разавторизуем его и перекинем на главную (в идеале нужно перекидывать на login.php)
// Кроме того, проверяем, что у данного администратора есть право на создание новых объектов недвижимости
$isAdmin = $userIncoming->isAdmin();
if (!$isAdmin['newOwner'] && !$isAdmin['newAdvertAlien']) {
    header('Location: out.php');
    exit();
}

/*************************************************************************************
 * ПОЛУЧИМ GET ПАРАМЕТРЫ
 * Для защиты от XSS атаки и для использования в коде более простого имени для переменной
 ************************************************************************************/

// Команда пользователя
$action = "";
if (isset($_GET['action'])) $action = htmlspecialchars($_GET['action'], ENT_QUOTES);

// Режим регистрации объявления из чужой базы
$completeness = "";
if (isset($_GET['completeness'])) $completeness = htmlspecialchars($_GET['completeness'], ENT_QUOTES);

/*************************************************************************************
 * Инициализируем объект для работы с параметрами недвижимости
 ************************************************************************************/

$property = new Property(NULL);

// Готовим массив со списком районов в городе пользователя
$allDistrictsInCity = DBconnect::selectDistrictsForCity("Екатеринбург");

/*************************************************************************************
 * Отправлена форма с параметрами объекта недвижимости
 ************************************************************************************/

if ($action == "saveAdvert") {

    $property->writeCharacteristicFromPOST("full");
    $property->writeFotoInformationFromPOST();

    // Проверяем корректность данных нового объявления. Функции validate() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
    // Если мы имеем дело с созданием нового чужого объявления администратором, то проверки данных происходят по упрощенному способу
    if ($isAdmin['newAdvertAlien'] && $completeness == "0") {
        $property->setCompleteness("0");
        $errors = $property->validate("newAlienAdvert");
    } else {
        $property->setCompleteness("1");
        $errors = $property->validate("newAdvert");
    }

    // Если данные, указанные пользователем, корректны, запишем объявление в базу данных
    if (is_array($errors) && count($errors) == 0) {

        // Сохраняем новое объявление на текущего пользователя
        $correctSaveCharacteristicToDB = $property->saveCharacteristicToDB("new");

        if ($correctSaveCharacteristicToDB) {

            // Сохраним информацию о фотографиях объекта недвижимости
            $property->saveFotoInformationToDB();

            // Оповестим арендаторов о появлении нового объекта недвижимости
            if ($property->getStatus() == "опубликовано") $property->notifyUsersAboutNewProperty();

            // Пересылаем пользователя на страницу с подробным описанием его объявления - хороший способ убедиться в том, что все данные указаны верно
            header('Location: property.php?propertyId=' . $property->getId());
            exit();

        } else {

            $errors[] = 'Не прошел запрос к БД. К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку';
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
$mode = "new";
//$allDistrictsInCity
//$errors
//$isAdmin
//$completeness

// Подсоединяем нужный основной шаблон
require $websiteRoot . "/templates/templ_changeadvert.php";

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();