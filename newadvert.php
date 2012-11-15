<?php
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

    // Инициализируем массив для хранения ошибок проверки данных объекта недвижимости
    $errors = array();

    /*************************************************************************************
     * Проверка доступности страницы для данного пользователя
     ************************************************************************************/

    // Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
    if (!$incomingUser->login()) {
        header('Location: login.php');
    }

    // TODO: сделать проверку на право администратора у данного пользователя

    /*************************************************************************************
     * Инициализируем объект для работы с параметрами недвижимости
     ************************************************************************************/

    $property = new Property($globFunc, $DBlink);

    // Готовим массив со списком районов в городе пользователя
    $allDistrictsInCity = $globFunc->getAllDistrictsInCity("Екатеринбург");

    /*************************************************************************************
     * Отправлена форма с параметрами объекта недвижимости
     ************************************************************************************/

    if (isset($_POST['saveAdvertButton'])) {

        $property->writeCharacteristicFromPOST();
        $property->writeFotoInformationFromPOST();

        // Проверяем корректность данных нового объявления. Функции isAdvertCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = $property->isAdvertCorrect("newAdvert");

        // Если данные, указанные пользователем, корректны, запишем объявление в базу данных
        if (is_array($errors) && count($errors) == 0) {

            // Сохраняем новое объявление на текущего пользователя
            $correctSaveCharacteristicToDB = $property->saveCharacteristicToDB("new", $incomingUser->getId());

            if ($correctSaveCharacteristicToDB) {

                // Узнаем id объекта недвижимости - необходимо при сохранении информации о фотках в постоянную базу */
                $property->getIdUseAddress();

                // Сохраним информацию о фотографиях объекта недвижимости
                $property->saveFotoInformationToDB();

                // Пересылаем пользователя на страницу с подробным описанием его объявления - хороший способ убедиться в том, что все данные указаны верно
                header('Location: objdescription.php?propertyId='.$property->id);

            } else {

                $errors[] = 'Не прошел запрос к БД. К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку';
                // Сохранении данных в БД не прошло - объявление не сохранено
            }

        }

    }

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View($globFunc, $DBlink);
    $view->generate("templ_newadvert.php", array('propertyCharacteristic' => $property->getCharacteristicData(),
                                                 'propertyFotoInformation' => $property->getFotoInformationData(),
                                                 'errors' => $errors,
                                                 'allDistrictsInCity' => $allDistrictsInCity,
                                                 'isLoggedIn' => $incomingUser->login()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    $globFunc->closeConnectToDB($DBlink);

    //TODO: В будущем необходимо будет проверять личные данные пользователя на полноту для его работы в качестве собственника, если у него typeOwner != "true"