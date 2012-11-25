<?php

    /*************************************************************************************
     * Если в строке не указан идентификатор интересующего (целевого) пользователя, то пересылаем нашего пользователя на спец. страницу
     ************************************************************************************/

    $targetUserId = "0";
    if (isset($_GET['compId'])) {
        $targetUserId = ($_GET['compId'] - 2) / 5; // Получаем идентификатор пользователя для показа его страницы из строки запроса
    } else {
        header('Location: 404.html'); // Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет к списку его объявлений??
    }

    /*************************************************************************************
     * Инициализация нужных моделей
     ************************************************************************************/

    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/DBconnect.php';
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'views/View.php';
    include 'models/User.php';

    // Удалось ли подключиться к БД?
    if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser();

    // Инициализируем полную модель для целевого пользователя по его идентификатору из GET строки
    $user = new User($targetUserId);
    $user->writeCharacteristicFromDB();
    $user->writeSearchRequestFromDB();
    $user->writeFotoInformationFromDB();

    /*************************************************************************************
     * Проверяем, имеет ли право данный пользователь смотреть анкету целевого пользователя
     *
     * Правила следующие:
     *
     * Неавторизованный пользователь не имеет права смотреть чью-либо анкету
     * Авторизованный пользователь может смотреть как минимум свою анкету
     *
     * Собственник может смотреть анкеты арендаторов, которые заинтересовались его объектом недвижимости (нажали на кнопку "Записаться на просмотр").
     * Собственник теряет право смотреть анкету арендатора, если тот удалил свой поисковый запрос (то есть перестал быть арендатором)
     * TODO: Если собственник снял с публикации свое объявление, то информация о всех арендаторах, интересовавшихся этим объектом удаляется через некоторое время (предположительно - 10 дней), таким образом собственник, в том числе, и теряет право смотреть их анкеты
     *
     * Возможно в будущем: Арендатор может смотреть анкеты собственников тех объектов недвижимости, у которых он нажал на кнопку "Получить контакты собственника" и получил их.
     * Возможно в будущем: Если собственник снял с публикации объект недвижимости, которым интересовался арендатор, то арендатор теряет право смотреть анкету этого собственника
     * Возможно в будущем: Если арендатор удалил поисковый запрос (то есть перестал быть арендатором), то он теряет право смотреть любые анкеты собственников, к которым имел доступ ранее
     *
     ************************************************************************************/

    // Если пользователь не авторизован, то он не сможет посмотреть ни одной анкеты
    if (!$incomingUser->login()) {
        header('Location: 404.html'); //TODO: реализовать страницу Отказано в доступе
    }

    // Получаем список пользователей, которые интересовались недвижимостью нашего пользователя ($incomingUser->getId). Он выступает в качестве собственника
    $tenantsWithSignUpToViewRequest = array();
    // Формировать список имеет смысл только, если целевой пользователь на текущий момент времени является арендатором. В ином случае, доступ к анкете целевого пользователя для собственников - закрыт. Таким образом реализуется правило: собственник может видеть только анкеты тех пользователей, которые заинтересовались его недвижимостью и в текущий момент времени являются арендаторами (= имеют поисковый запрос)
    if ($user->typeTenant) {
        if ($res = $incomingUser->getAllVisibleUsersId()) $tenantsWithSignUpToViewRequest = $res;
    }

    // Проверяем, есть ли среди этого списка текущий целевой пользователь ($targetUserId)
    // Проверка вынесена в отдельный блок, так как это позволяет одновременно проверить несколько условий на доступ к данной странице
    if (!in_array($targetUserId, $tenantsWithSignUpToViewRequest) && $incomingUser->getId() != $targetUserId) {
        header('Location: 404.html'); //TODO: реализовать страницу Отказано в доступе
    }

    /*************************************************************************************
     * Получаем заголовок страницы
     ************************************************************************************/
    $strHeaderOfPage = $user->surname." ".$user->name." " .$user->secondName;

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View();
    $view->generate("templ_man.php", array('userCharacteristic' => $user->getCharacteristicData(),
                                           'userFotoInformation' => $user->getFotoInformationData(),
                                           'userSearchRequest' => $user->getSearchRequestData(),
                                           'strHeaderOfPage' => $strHeaderOfPage,
                                           'isLoggedIn' => $incomingUser->login(),
                                           'mode' => "tenantForOwner",
                                           'amountUnreadMessages' => $incomingUser->getAmountUnreadMessages()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    DBconnect::closeConnectToDB();