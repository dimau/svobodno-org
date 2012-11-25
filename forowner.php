<?php
    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/DBconnect.php';
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'models/RequestFromOwner.php';
    include 'views/View.php';

    // Удалось ли подключиться к БД?
    if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser();

    // Инициализируем модель для работ с запросом на новое объявление от собственника
    $requestFromOwner = new RequestFromOwner($incomingUser);

    // Инициализируем переменную, в которую будет сохранен статус записи запроса собственника в БД
    $statusOfSaveParamsToDB = NULL;

    /********************************************************************************
     * ЗАПРОС НА ПОДАЧУ ОБЪЯВЛЕНИЯ. Если пользователь отправил заполненную форму заявки на подачу объявления
     *******************************************************************************/

    if (isset($_POST['submitButton'])) {
        $requestFromOwner->writeParamsFromPOST();

        //TODO: проверять данные на заполненность

        // Сохраняем запрос собственника в БД
        $statusOfSaveParamsToDB = $requestFromOwner->saveParamsToDB();

        //TODO: оповестить опрератора о новом запросе собственника
    }

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View();
    $view->generate("templ_forowner.php", array('requestFromOwnerData' => $requestFromOwner->getRequestFromOwnerData(),
                                                'statusOfSaveParamsToDB' => $statusOfSaveParamsToDB,
                                                'isLoggedIn' => $incomingUser->login(),
                                                'amountUnreadMessages' => $incomingUser->getAmountUnreadMessages()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    DBconnect::closeConnectToDB();