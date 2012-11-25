<?php
    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/DBconnect.php';
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'views/View.php';
    include 'models/SearchRequest.php';

    // Удалось ли подключиться к БД?
    if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser();

    /*************************************************************************************
     * Инициализируем поисковый запрос значениями по умолчанию, и другие переменные
     ************************************************************************************/

    $searchRequest = new SearchRequest();

    // Готовим массив со списком районов в городе пользователя
    $allDistrictsInCity = GlobFunc::getAllDistrictsInCity("Екатеринбург");

    /***************************************************************************************************************
     * ОТПРАВЛЕНА ФОРМА ПОИСКА
     **************************************************************************************************************/

    if (isset($_GET['fastSearchButton'])) {
        $searchRequest->writeParamsFastFromPOST();
    }

    if (isset($_GET['extendedSearchButton'])) {
        $searchRequest->writeParamsExtendedFromPOST();
    }

    /***************************************************************************************************************
     * Если пользователь залогинен и указал в личном кабинете параметры поиска, но еще не нажимал кнопки Поиск на этой странице
     **************************************************************************************************************/

    if (!isset($_GET['fastSearchButton']) && !isset($_GET['extendedSearchButton']) && $incomingUser->login()) {
        $searchRequest->writeParamsFromDB();
    }

    /***************************************************************************************************************
     * Получаем данные (массив массивов, каждый из которых представляет отдельный объект недвижимости) по ВСЕМ соответствующим запросу объектам недвижимости из БД
     **************************************************************************************************************/

    $propertyLightArr = $searchRequest->getArrResultSQLrequest();

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View();
    $view->generate("templ_search.php", array('whatPage' => "forSearchPage",
                                              'propertyLightArr' => $propertyLightArr,
                                              'userSearchRequest' => $searchRequest->getSearchRequestData(),
                                              'allDistrictsInCity' => $allDistrictsInCity,
                                              'isLoggedIn' => $incomingUser->login(),
                                              'favoritesPropertysId' => $incomingUser->getFavoritesPropertysId(),
                                              'amountUnreadMessages' => $incomingUser->getAmountUnreadMessages()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    DBconnect::closeConnectToDB();