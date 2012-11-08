<?php
    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include '../models/GlobFunc.php';
    include '../models/Logger.php';
    include '../models/IncomingUser.php';
    include '../views/View.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser($globFunc, $DBlink);

    // Вспомогательная функция отказа в доступе
    function accessDenied()
    {
        header('Content-Type: text/xml; charset=UTF-8');
        echo "<xml><span status='denied'></span></xml>";
        exit();
    }

    // Проверяем, залогинен ли пользователь, если нет - то отказываем в доступе
    if (!$incomingUser->login()) {
        accessDenied();
    }

    // Получаем идентификатор объявления, которое пользователь хочет добавить/удалить в Избранное и действие, которое нужно совершить с объявлением (добавить в избранное или удалить)
    $propertyId = "";
    if (isset($_POST['propertyId'])) $propertyId = $_POST['propertyId']; else accessDenied();
    $action = "";
    if (isset($_POST['action'])) $action = $_POST['action']; else accessDenied();

    // Если требуемое действие = Добавить в избранное, то записываем id объявления в БД, в поле favoritesPropertysId пользователя - тем самым фиксируем, что он добавил данное объявление к себе в избранные
    if ($action == "addToFavorites") {

        if (!$incomingUser->addFavoritesPropertysId($propertyId)) accessDenied();
    }

    // Если требуемое действие = Удалить из избранного, то удаляем id объявления из БД, из поля favoritesPropertysId пользователя
    if ($action == "removeFromFavorites") {

        if (!$incomingUser->removeFavoritesPropertysId($propertyId)) accessDenied();
    }

    /*************************************************************************************
     * Если все хорошо - возвращаем положительный статус выполнения операции
     *************************************************************************************/

    header('Content-Type: text/xml; charset=UTF-8');
    echo "<xml><span status='successful'></span></xml>";

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    $globFunc->closeConnectToDB($DBlink);