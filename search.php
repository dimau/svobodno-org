<?php
    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'views/View.php';
    include 'models/SearchRequest.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser($globFunc, $DBlink);

    /*************************************************************************************
     * Инициализируем поисковый запрос значениями по умолчанию, и другие переменные
     ************************************************************************************/

    $searchRequest = new SearchRequest($globFunc, $DBlink);

    // Готовим массив со списком районов в городе пользователя
    $allDistrictsInCity = $globFunc->getAllDistrictsInCity("Екатеринбург");

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

    $view = new View($globFunc, $DBlink);
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

    $globFunc->closeConnectToDB($DBlink);