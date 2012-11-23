<?php

    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'views/View.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser($globFunc, $DBlink);

    // Проверим, быть может пользователь уже авторизирован. Если это так, перенаправим его в личный кабинет
    if ($incomingUser->login()) {
        header('Location: personal.php');
    }

    // Инициализируем массив для хранения ошибок входа (авторизации)
    $errors = array();

    // Если пользователь не авторизирован, то проверим, была ли нажата кнопка входа на сайт
        if (isset($_POST['buttonSubmit'])) {

            $errors = $incomingUser->enter(); //функция входа на сайт

            if (is_array($errors) && count($errors) == 0) //если нет ошибок, отправляем пользователя в личный кабинет
            {
                header('Location: personal.php');
            }
            // Если при авторизации возникли ошибки, мы их покажем в специальном всплывающем сверху блоке вместе со страницей авторизации
        }

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View($globFunc, $DBlink);
    $view->generate("templ_login.php", array('errors' => $errors,
                                            'isLoggedIn' => $incomingUser->login(),
                                            'amountUnreadMessages' => $incomingUser->getAmountUnreadMessages()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    $globFunc->closeConnectToDB($DBlink);