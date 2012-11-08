<?php

    /*************************************************************************************
     * Если в строке не указан идентификатор объявления, то пересылаем пользователя на спец. страницу
     ************************************************************************************/

    $propertyId = "0";
    if (isset($_GET['propertyId']) && $_GET['propertyId'] != "") {
        $propertyId = $_GET['propertyId']; // Получаем идентификатор объявления для показа из строки запроса
    } else {
        header('Location: 404.html'); // Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет к списку его объявлений
    }

    /*************************************************************************************
     * Инициализируем требуемые модели
     ************************************************************************************/

    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'views/View.php';
    include 'models/Property.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser($globFunc, $DBlink);

    /*************************************************************************************
     * Получаем данные объявления для просмотра, а также другие данные из БД
     ************************************************************************************/

    $property = new Property($globFunc, $DBlink, $propertyId);

    // Анкетные данные и данные о фотографиях объекта недвижимости
    $property->writeCharacteristicFromDB();
    $property->writeFotoInformationFromDB();

    /*************************************************************************************
     * Проверяем - может ли данный пользователь просматривать данное объявление
     ************************************************************************************/

    // Если объявление опубликовано, то его может просматривать каждый
    // Если объявление закрыто (снято с публикации), то его может просматривать только сам собственник
    if ($property->status == "не опубликовано" && $property->userId != $incomingUser->getId()) header('Location: 404.html');
    //TODO: реализовать соответствующую 404 страницу

    /*************************************************************************************
     * Получаем заголовок страницы
     ************************************************************************************/
    $strHeaderOfPage = $globFunc->getFirstCharUpper($property->typeOfObject)." по адресу: ".$property->address;

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View($globFunc, $DBlink);
    $view->generate("templ_objdescription.php", array('propertyCharacteristic' => $property->getCharacteristicData(),
                                                 'propertyFotoInformation' => $property->getFotoInformationData(),
                                                 'isLoggedIn' => $incomingUser->login(),
                                                 'strHeaderOfPage' => $strHeaderOfPage));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    $globFunc->closeConnectToDB($DBlink);

    /*************************************************************************************
     * Проверяем, добавлено ли данное объявление в избранные у данного пользователя
     ************************************************************************************/
//TODO: реализовать как надо работу с избранностью объявления!
    /* // Получаем идентификаторы избранных объявлений для данного пользователя
   $favoritesPropertysId = array();
   if ($userId != FALSE) {
       $rowUsers = FALSE;
       $rezUsers = mysql_query("SELECT favoritesPropertysId FROM users WHERE id = '" . $userId . "'");
       if ($rezUsers != FALSE) $rowUsers = mysql_fetch_assoc($rezUsers);
       if ($rowUsers != FALSE) $favoritesPropertysId = unserialize($rowUsers['favoritesPropertysId']);
   }

   // Проверяем: добавлено данное объявление в избранные или нет. Соответствующим образом вычисляем тексты и оформление команды (на удаление/добавление в избранное)
   $favoritesParam = array();
   $favoritesParam['actionFavorites'] = "";
   $favoritesParam['imgFavorites'] = "";
   $favoritesParam['textFavorites'] = "";
   if ($userId != FALSE) {
       // Проверяем наличие данного объявления среди избранных у авторизованного пользователя
       if (in_array($propertyId, $favoritesPropertysId)) {
           $favoritesParam['actionFavorites'] = "removeFromFavorites";
           $favoritesParam['imgFavorites'] = "img/gold_star.png";
           $favoritesParam['textFavorites'] = "убрать из избранного";
       } else {
           $favoritesParam['actionFavorites'] = "addToFavorites";
           $favoritesParam['imgFavorites'] = "img/blue_star.png";
           $favoritesParam['textFavorites'] = "добавить в избранное";
       }
   } else {
       $favoritesParam['actionFavorites'] = "addToFavorites";
       $favoritesParam['imgFavorites'] = "img/blue_star.png";
       $favoritesParam['textFavorites'] = "добавить в избранное";
   } */
