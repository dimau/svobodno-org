<?php
    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/DBconnect.php';
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'views/View.php';

    // Удалось ли подключиться к БД?
    if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser();

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View();
    $view->generate("templ_index.php", array('isLoggedIn' => $incomingUser->login(),
                                             'amountUnreadMessages' => $incomingUser->getAmountUnreadMessages()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    DBconnect::closeConnectToDB();