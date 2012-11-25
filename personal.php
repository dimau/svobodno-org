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

    // Вычисляем вкладку, которая будет открыта по умолчанию при загрузке страницы
    if (isset($_GET['tabsId'])) {
        $tabsId = $_GET['tabsId'];
    } else {
        $tabsId = "tabs-1";
    }

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser();

    // Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
    if (!$incomingUser->login()) {
        header('Location: login.php');
    }

    /*************************************************************************************
     * Получаем информацию о пользователе из БД сервера
     ************************************************************************************/

    // Инициализируем полную модель пользователя
    $user = new User($incomingUser->getId());

    // Анкетные (основные персональные) данные пользователя
    $user->writeCharacteristicFromDB();

    // Данные поискового запроса
    if (isset($_GET['action']) && $_GET['action'] == 'deleteSearchRequest') {
        // Если пользователь пожелал удалить поисковый запрос, то это нужно сделать вместо получения данных из БД
        $user->removeSearchRequest();
    } else {
        // Иначе получим данные из БД по поисковому запросу данного пользователя в параметры объекта $user
        $user->writeSearchRequestFromDB();
    }

    // Информация о фотографиях пользователя. Метод вызывается во всех случаях, кроме того, когда пользователь отредактировал свои личные параметры и нажал на кнопку "Сохранить"
    if (!isset($_POST['saveProfileParameters'])) $user->writeFotoInformationFromDB();

    // Данные по объектам недвижимости данного пользователя (для которых он является собственником)
    $collectionProperty = new CollectionProperty();
    $collectionProperty->buildFromOwnerId($incomingUser->getId());

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

    if (isset($_POST['saveProfileParameters'])) {

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

    if (isset($_GET['action']) && $_GET['action'] == "publicationOn" && isset($_GET['propertyId'])) {

        // Проверяем: имеет ли данный пользователь право на выполнение изменения статуса объявления
        if ($collectionProperty->hasPropertyId($_GET['propertyId'])) {
            $collectionProperty->setPublicationStatus("опубликовано", $_GET['propertyId']);
        }

        // По умолчанию откроем вкладку 3 (Мои объявления)
        $tabsId = "tabs-3";
    }

    /********************************************************************************
     * СНЯТИЕ С ПУБЛИКАЦИИ ОБЪЯВЛЕНИЯ. Если пользователь отправил команду на снятие с публикации одного из своих объявлений
     *******************************************************************************/

    if (isset($_GET['action']) && $_GET['action'] == "publicationOff" && isset($_GET['propertyId'])) {

        // Проверяем: имеет ли данный пользователь право на выполнение изменения статуса объявления
        if ($collectionProperty->hasPropertyId($_GET['propertyId'])) {
            $collectionProperty->setPublicationStatus("не опубликовано", $_GET['propertyId']);
        }

        // По умолчанию откроем вкладку 3 (Мои объявления)
        $tabsId = "tabs-3";
    }

    /********************************************************************************
     * РЕДАКТИРОВАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь отправил редактированные параметры поискового запроса
     *******************************************************************************/

    if (isset($_POST['saveSearchParametersButton'])) {

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

    if (isset($_POST['createSearchRequestButton'])) {

        // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = $user->userDataCorrect("createSearchRequest"); // Параметр createSearchRequest задает режим проверки "Создание запроса на поиск", который активирует только соответствующие ему проверки
        if (count($errors) == 0) $correctNewSearchRequest = TRUE; else $correctNewSearchRequest = FALSE; // Считаем ошибки, если 0, то можно выдать пользователю форму для ввода параметров Запроса поиска

        // Если создание поискового запроса одобрено (успешно прошли валидации параметров пользователя) открываем вкладку 4 (Поисковый запрос), в противном случае вкладку 1 (Личные параметры пользователя)
        if ($correctNewSearchRequest === TRUE) $tabsId = "tabs-4"; else $tabsId = "tabs-1";
    }

    /***************************************************************************************************************
     * ИЗБРАННОЕ. Получаем данные по каждому избранному объявлению из БД (это позволит наполнить вкладку tabs-5)
     **************************************************************************************************************/

    $propertyLightArr = $incomingUser->getPropertyLightArrForFavorites();

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View();
    $view->generate("templ_personal.php", array('userCharacteristic' => $user->getCharacteristicData(),
                                                'userFotoInformation' => $user->getFotoInformationData(),
                                                'userSearchRequest' => $user->getSearchRequestData(),
                                                'allPropertiesCharacteristic' => $collectionProperty->getAllPropertiesCharacteristic(),
                                                'allPropertiesFotoInformation' => $collectionProperty->getAllPropertiesFotoInformation(),
                                                'allPropertiesTenantPretenders' => $collectionProperty->getAllPropertiesTenantPretenders(),
                                                'errors' => $errors,
                                                'correctNewSearchRequest' => $correctNewSearchRequest,
                                                'correctEditSearchRequest' => $correctEditSearchRequest,
                                                'correctEditProfileParameters' => $correctEditProfileParameters,
                                                'allDistrictsInCity' => $allDistrictsInCity,
                                                'isLoggedIn' => $incomingUser->login(),
                                                'propertyLightArr' => $propertyLightArr,
                                                'favoritesPropertysId' => $incomingUser->getFavoritesPropertysId(),
                                                'whatPage' => "forPersonalPage",
                                                'tabsId' => $tabsId, // Указывает вкладку (идентификатор), которая откроется при загрузке страницы
                                                'mode' => "personal",
                                                'messagesArr' => $user->getAllMessagesSorted(),
                                                'amountUnreadMessages' => $incomingUser->getAmountUnreadMessages()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    DBconnect::closeConnectToDB();