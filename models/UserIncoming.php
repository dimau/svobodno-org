<?php

// Класс (модель) для хранения и обработки ключевой информации по текущему пользователю (запросившему страницу)
// Позволяет узнать авторизован пользователь или нет, имеет ли он статус арендатора, собственника или администратора
class UserIncoming extends User {

    private $isLoggedIn = NULL; // В переменную сохраняется функцией login() значение FALSE или TRUE после первого вызова на странице. Для уменьшения обращений к БД
    private $amountUnreadMessages = ""; // В переменную функцией getAmountUnreadMessages() сохраняется количество непрочитанных уведомлений пользователя

    /* Данные параметры используются только для Личного кабинета, для вкладки с Избранными объектами */
    private $propertyLightArr; // Массив массивов. После выполнения метода searchProperties содержит минимальные данные по ВСЕМ избранным объектам
    private $propertyFullArr; // Массив массивов. После выполнения метода searchProperties содержит полные данные, включая фотографии, по нескольким первым в выборке объектам (количество указывается в качестве первого параметра к методу searchProperties)

    // КОНСТРУКТОР
    public function __construct() {
        // Проверяем, авторизован ли пользователь, и если да, инициализируем параметры объекта (id, typeTenant, typeOwner...) соответствующими значениями из БД
        $this->login();
        // Получим количество непрочитанных уведомлений (новостей) для данного пользователя
        $this->getAmountUnreadMessages();
    }

    /**
     * Метод для инициализации параметров объекта конкретными значениями
     *
     * @param array $params ассоциативный массив, содержащий значения параметров для инициализации
     * @return bool TRUE в случае успешного перебора и FALSE в противном случае.
     */
    private function initialization($params) {

        // Валидация исходных данных
        if (!isset($params) || !is_array($params)) return FALSE;

        // Перебираем полученный ассоциативный массив и присваиваем значения его параметров параметрам объекта
        foreach ($params as $key => $value) {
            if (isset($this->$key)) $this->$key = $value;
        }

        return TRUE;
    }

    // Метод добавляет в избранные у данного пользователя идентификатор объекта недвижимости $propertyId
    public function addFavoritePropertiesId($propertyId = FALSE) {

        // Валидация исходных параметров
        if (!$this->login()) return FALSE;
        if ($propertyId == FALSE) return FALSE;
        if (in_array($propertyId, $this->favoritePropertiesId)) return TRUE;

        $this->favoritePropertiesId[] = $propertyId;
        $favoritePropertiesIdSerialized = serialize($this->favoritePropertiesId);

        // Сохраняем новые изменения в БД в таблицу поисковых запросов
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE users SET favoritePropertiesId=? WHERE id=?") === FALSE)
            OR ($stmt->bind_param("ss", $favoritePropertiesIdSerialized, $this->id) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            return FALSE;
        }

        return TRUE;
    }

    // Метод удаляет из избранного у данного пользователя идентификатор объекта недвижимости $propertyId
    public function removeFavoritePropertiesId($propertyId = FALSE) {

        // Валидация исходных параметров
        if (!$this->login()) return FALSE;
        if ($propertyId == FALSE) return FALSE;
        if (!in_array($propertyId, $this->favoritePropertiesId)) return TRUE;

        // Ищем id нашего объекта среди id избранных объектов. Если он там есть, то получим номер позиции, если нет - FALSE
        $key = array_search($propertyId, $this->favoritePropertiesId);
        if ($key === FALSE) return FALSE; // Если наш объект находится в массиве избранных объектов на 0 позиции, то нужно, чтобы условие срабатывало и этот объект можно было удалить из массива избранных, поэтому используется строгое равенство

        // Удаляем $propertyId из списка избранных объявлений
        array_splice($this->favoritePropertiesId, $key, 1);
        $favoritePropertiesIdSerialized = serialize($this->favoritePropertiesId);

        // Сохраняем новые изменения в БД в таблицу поисковых запросов
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE users SET favoritePropertiesId=? WHERE id=?") === FALSE)
            OR ($stmt->bind_param("ss", $favoritePropertiesIdSerialized, $this->id) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Вычисляет массивы:
     * 1. C краткими данными (id, coordX, coordY) о ВСЕХ избранных объектах недвижимости
     * 2. Кроме того по первым $amountFullProperties объектам недвижимости вычисляет полные данные, даже с фотографиями.
     * @param $amountFullProperties количество первых объявлений, по которым нужно получить полные данные
     * @param $typeOfSorting тип сортировки результатов: costAscending, costDescending, publicationDateDescending
     * @return bool TRUE в случае успешного завершения выполнения алгоритма и FALSE в противном случае
     */
    public function searchProperties($amountFullProperties, $typeOfSorting) {

        // Валидация входных данных
        if (!isset($amountFullProperties) || !isset($typeOfSorting)) return FALSE;

        // Получим минимальные данные (id, coordX, coordY) по всем объектам недвижимости, подходящим под параметры поискового запроса
        $this->findPropertyLightArr($typeOfSorting);

        // Получим полные данные по первым $amountFullProperties объектам недвижимости
        $this->findPropertyFullArr($amountFullProperties);

        // Если по каким-то объектам из $this->propertyLightArr получить полные данные не удалось, удалим их
        if ($amountFullProperties <= count($this->propertyLightArr)) $limit = $amountFullProperties; else $limit = count($this->propertyLightArr);
        $markForSort = FALSE; // Метка, говорящая, требуется ли пересортировка массиву $this->propertyLightArr (если будет удален хотя бы 1 элемент из него), или нет
        for ($i = 0; $i < $limit; $i++) {
            $markForRemove = TRUE;
            foreach ($this->propertyFullArr as $value) {
                if ($this->propertyLightArr[$i]['id'] == $value['id']) {
                    $markForRemove = FALSE;
                    break;
                }
            }
            // Проверяем метку на удаление. Если для элемент $this->propertyLightArr[$i] не нашелся соответствующий в $this->propertyFullArr, значит не удалось получить данные по этому объекту недвижимости
            if ($markForRemove) {
                unset($this->propertyLightArr[$i]);
                $markForSort = TRUE;
            }
        }
        // Если был удален хотя бы 1 элемент, переиндексируем массив
        if ($markForSort) $this->propertyLightArr = array_values($this->propertyLightArr);

        return TRUE;
    }

    // Метод записывает в параметр $this->propertyLightArr массив массивов, содержащий минимальные данные по всем объектам недвижимости, соответствующим данным условиям поиска
    private function findPropertyLightArr($typeOfSorting) {

        // Убедимся, что список идентификаторов объектов недвижимости представляет собой массив и его длина не равна нулю
        if (!is_array($this->favoritePropertiesId) || count($this->favoritePropertiesId) == 0) {
            $this->propertyLightArr = array();
            return FALSE;
        }

        // Выбираем вариант сортировки
        switch ($typeOfSorting) {
            case "costAscending":
                $typeOfSortingString = "realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting ASC";
                break;
            case "costDescending":
                $typeOfSortingString = "realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting DESC";
                break;
            case "publicationDateDescending":
                $typeOfSortingString = "reg_date DESC";
                break;
            default:
                return FALSE;
        }

        // Собираем строку WHERE для поискового запроса к БД
        $strWHERE = " (";
        for ($i = 0, $s = count($this->favoritePropertiesId); $i < $s; $i++) {
            $strWHERE .= " id = '" . $this->favoritePropertiesId[$i] . "'";
            if ($i < $s - 1) $strWHERE .= " OR";
        }
        $strWHERE .= ") AND (status = 'опубликовано')"; //TODO: сделать особое отображение (засеренное) для не опубликованных объявлений, тогда можно будет снять это ограничение на показ пользователю в избранных только еще опубликованных объектов

        // Получаем данные из БД - ВСЕ объекты недвижимости, которые являются избранными для данного пользователя
        // Сортируем по стоимости аренды и не ограничиваем количество объявлений - все, добавленные в избранные
        // В итоге получим массив ($this->propertyLightArr), каждый элемент которого представляет собой также массив значений конкретного объявления по недвижимости
        $res = DBconnect::get()->query("SELECT id, coordX, coordY FROM property WHERE" . $strWHERE . " ORDER BY " . $typeOfSortingString);
        if ((DBconnect::get()->errno)
            OR (($this->propertyLightArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
        ) {
            // Логируем ошибку
            //TODO: сделать логирование ошибки
            $this->propertyLightArr = array();
            return FALSE;
        }

        return TRUE;
    }

    // Метод записывает в параметр $this->propertyFullArr массив массивов, содержащий полные данные (в том числе с фото) по первым $amountFullProperties объектам недвижимости из массива $this->propertyLightArr
    private function findPropertyFullArr($amountFullProperties) {

        // Проверим входные параметры
        if (!isset($amountFullProperties) || $amountFullProperties == 0) $this->propertyFullArr = array();

        // Сколько всего будет объектов с полными данными в итоге
        if ($amountFullProperties <= count($this->propertyLightArr)) $limit = $amountFullProperties; else $limit = count($this->propertyLightArr);

        // Вычислим массив id объектов, по которым требуется получить полные данные
        $propertiesIdForFullData = array();
        for ($i = 0; $i < $limit; $i++) {
            $propertiesIdForFullData[] = $this->propertyLightArr[$i]['id'];
        }

        // Получим массив с полными данными (в том числе с фото) по требующимся объявлениям
        $this->propertyFullArr = DBconnect::getFullDataAboutProperties($propertiesIdForFullData, "all");
        // Если полные данные получить не удалось - запишем пустой массив в результат
        if ($this->propertyFullArr == FALSE) $this->propertyFullArr = array();

    }

    // Возвращает массив массивов $this->propertyLightArr
    public function getPropertyLightArr() {
        return $this->propertyLightArr;
    }

    // Возвращает массив массивов $this->propertyFullArr
    public function getPropertyFullArr() {
        return $this->propertyFullArr;
    }

    // Метод возвращает массив идентификаторов арендаторов, которые заинтересовались недвижимостью данного пользователя
    // Если ничего не найдено или произошла ошибка, вернет пустой массив
    public function getAllTenantsId() {

        // Проверим, что пользователь авторизован и является собственником
        if (!$this->login() || !$this->isOwner()) return array();

        // Получим из БД данные о всех объектах недвижимости собственника
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("SELECT id FROM property WHERE userId = ?") === FALSE)
            OR ($stmt->bind_param("s", $this->id) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->get_result()) === FALSE)
            OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
            OR ($stmt->close() === FALSE)
        ) {
            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            return array();
        }

        // Соберем все идентификаторы в одномерный массив
        $propertiesId = array();
        foreach ($res as $value) {
            $propertiesId[] = $value['id'];
        }

        // Получим все заявки на контакты собственника для этих объектов недвижимости
        $allRequestForOwnerContacts = DBconnect::selectRequestsForOwnerContactsForProperties($propertiesId);

        // Перебираем массив, полученный из БД и собираем все id арендаторов, отправивших заявки на просмотр, в одномерный массив - без повторов
        $tenantsId = array();
        foreach ($allRequestForOwnerContacts as $value) {
            $tenantsId[] = $value['tenantId'];
        }

        // Уберем повторяющиеся элементы
        $tenantsId = array_unique($tenantsId);
        sort($tenantsId);

        // Вернем одномерный массив, состоящий из идентификаторов арендаторов, отправивших запрос на просмотр одного из объектов недвижимости данного пользователя (собственника)
        return $tenantsId;
    }

    // Возвращает количество непрочитанных уведомлений пользователя
    public function getAmountUnreadMessages() {

        // Если пользователь не авторизован (у него нет id), то возвращаем 0
        if ($this->id == "") return 0;

        // Если переменная, содержащая кол-во непрочитанных уведомлений, уже проинициализирована, то возвращаем ее значение
        if (isset($this->amountUnreadMessages) && $this->amountUnreadMessages != "") {
            return $this->amountUnreadMessages;
        }

        // Если во время этой сессии уже подсчитали количество непрочитанных уведомлений - вернем его
        // Это решение приводило к тому, что пока не был перегружен браузер кол-во непрочитанных уведомлений не изменялось
        /*if (isset($_SESSION['amountUnreadMessages'])) {
            $this->amountUnreadMessages = $_SESSION['amountUnreadMessages'];
            return $_SESSION['amountUnreadMessages'];
        }*/

        // Считаем количество непрочитанных уведомлений пользователя
        $result = DBconnect::countUnreadMessagesForUser($this->id);
        //TODO: сделать подсчет количества уведомлений и по другим таблицам уведомлений (когда они появятся)

        // Сохраним также результат в переменную объекта пользователя
        $this->amountUnreadMessages = $result;

        return $result;
    }

    // Функция проверяет - залогинен ли пользователь сейчас (возвращает TRUE или FALSE).
    // И если пользователь залогинен, то обновляет его ключевые личные параметры (параметры класса User) в соответствии с указанными в БД
    public function login() {

        // Если данная функция уже вызывалась на этой странице, то результат ее работы сохранен в приватной переменной, достаточно выдать его
        if ($this->isLoggedIn !== NULL) return $this->isLoggedIn;

        // Если сессия еще не была запущена - запускаем.
        if (!isset($_SESSION)) {
            session_start();
        }

        // Массив для хранения параметров (характеристики) пользователя
        $res = array();

        // Если у пользователя есть куки - попробуем его авторизовать с их помощью
        if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) {

            $cookieLogin = $_COOKIE['login'];
            $cookiePassword = $_COOKIE['password'];

            // Пытаемся получить характеристику пользователя по логину из куки
            $res = DBconnect::selectUserCharacteristicForLogin($cookieLogin);

            // Если данные из БД получить не удалось или пароль из БД не совпадает с паролем, зашифрованным в куках - считаем, что с помощью куки авторизовать пользователя не удалось
            if (count($res) == 0 || md5($res['login'] . $res['password']) != $cookiePassword) {
                $res = array();
            }
        }

        // Если у пользователя есть переменная сессии, а по кукам авторизация не прошла - попробуем его авторизовать с помощью сессии
        // Этот запасной вариант полезен для пользователей, у которых выключены куки
        if (count($res) == 0 && isset($_SESSION['id'])) {

            $sessionId = $_SESSION['id'];

            // Пытаемся получить характеристику пользователя по хэшу (идентификатору) сессии
            $res = DBconnect::selectUserCharacteristicForHash($sessionId);

            // Если данные из БД получить не удалось - считаем, что с помощью сессии авторизовать пользователя не удалось
            if (count($res) == 0) {
                $res = array();
            }
        }

        // Если у пользователя нет идентификатора сессии и нет куки (логин + пароль), или они устарели, следовательно получить его характеристику не удалось
        if (count($res) == 0) {
            // На всякий случай удаляем id сессии (если он конечно был указан)
            unset($_SESSION['id']);

            // На всякий случай удаляем куки (если они были конечно)
            setcookie("login", "", time() - 1, '/');
            setcookie("password", "", time() - 1, '/');

            $this->isLoggedIn = FALSE;
            return FALSE;
        }

        // Сохраняем ключевые параметры пользователя, полученные из БД, в параметры объекта
        if (!$this->initialization($res)) return FALSE;

        // Обновим куки (или добавим, если их ранее не было), чтобы после перезапуска браузера сессия не слетала
        setcookie("login", "", time() - 1, '/');
        setcookie("password", "", time() - 1, '/');
        setcookie("login", $res['login'], time() + 60 * 60 * 24 * 7);
        setcookie("password", md5($res['login'] . $res['password']), time() + 60 * 60 * 24 * 7);

        // Запускаем новую сессию и фиксируем время последнего действия пользователя
        $this->newSession($res['id']);
        $this->lastAct($res['id']);

        // Вернули ответ - пользователь залогинен
        $this->isLoggedIn = TRUE;
        return TRUE;
    }

    // Функция для авторизации (входа) пользователя на сайте.
    // Возвращает массив с ошибками в случае невозможности авторизации пользователя и пустой массив при успехе
    function enter() {

        // Массив для сбора ошибок
        $errors = array();

        // Если пользователь не указал логин или пароль
        if (!isset($_POST['telephon']) || $_POST['telephon'] == "" || !isset($_POST['password']) || $_POST['password'] == "") {
            $errors[] = "Укажите Ваш логин (номер телефона) и пароль";
            return $errors;
        }

        // Для удобства значения POST параметров перепишем в переменные
        $login = $_POST['telephon'];
        $password = $_POST['password'];

        // Пытаемся получить из БД характеристику пользователя по указанному логину
        $res = DBconnect::selectUserCharacteristicForLogin($login);

        // Если получили пустой массив - в БД нет пользователя с таким логином, либо произошла ошибка при работе с БД
        if (count($res) == 0) {
            $errors[] = "Пользователя с таким логином (номером телефона) и паролем у нас нет :(";
            return $errors;
        }

        // Проверяем правильный ли пароль указал пользователь
        if ($res['password'] != $password) {
            $errors[] = "Пользователя с таким логином (номером телефона) и паролем у нас нет :(";
            return $errors;
        }

        // Сохраняем ключевые параметры пользователя, полученные из БД в параметры объекта
        $this->initialization($res);

        // Обновим куки (или добавим, если их ранее не было), чтобы после перезапуска браузера сессия не слетала
        setcookie("login", "", time() - 1, '/');
        setcookie("password", "", time() - 1, '/');
        setcookie("login", $res['login'], time() + 60 * 60 * 24 * 7);
        setcookie("password", md5($res['login'] . $res['password']), time() + 60 * 60 * 24 * 7);

        // Запускаем новую сессию и фиксируем время последнего действия пользователя
        $this->newSession($res['id']);
        $this->lastAct($res['id']);

        return $errors;
    }

    // Формирует уникальный идентификатор сессии пользователя, записывает его в БД и назначает в переменные сессии
    private function newSession($userId) {
        // Генерируем случайное 32-х значное число - идентификатор сессии
        $hash = md5(GlobFunc::generateCode(10));

        // Обновляем данные в БД по пользователю с id = $userId
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE users SET user_hash=? WHERE id=?") === FALSE)
            OR ($stmt->bind_param("ss", $hash, $userId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
        }

        $_SESSION['id'] = $hash; //записываем id сессии
    }

    // Фиксирует в БД время последней активности пользователя
    private function lastAct($userId) {
        $tm = time();

        // Обновляем данные в БД по пользователю с id = $userId
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE users SET last_act=? WHERE id=?") === FALSE)
            OR ($stmt->bind_param("ss", $tm, $userId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
        }

    }
}