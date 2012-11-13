<?php
    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'models/RequestFromOwner.php';
    include 'views/View.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser($globFunc, $DBlink);

    // Инициализируем модель для работ с запросом на новое объявление от собственника
    $requestFromOwner = new RequestFromOwner($globFunc, $DBlink);

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

    $view = new View($globFunc, $DBlink);
    $view->generate("templ_forowner.php", array('requestFromOwnerData' => $requestFromOwner->getRequestFromOwnerData(),
                                                'statusOfSaveParamsToDB' => $statusOfSaveParamsToDB,
                                                'isLoggedIn' => $incomingUser->login()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    $globFunc->closeConnectToDB($DBlink);