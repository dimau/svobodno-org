<?php

    // Класс (модель) для хранения и обработки ключевой информации по текущему пользователю (запросившему страницу)
    // Позволяет узнать авторизован пользователь или нет, имеет ли он статус арендатора, собственника или администратора
    class IncomingUser
    {
        public $name = "";
        public $secondName = "";
        public $surname = "";
        public $telephon = "";

        private $id = "";
        private $typeTenant = NULL;
        private $typeOwner = NULL;
        private $typeAdmin = NULL;
        private $favoritesPropertysId = array();

        private $isLoggedIn = NULL; // В переменную сохраняется функцией login() значение FALSE или TRUE после первого вызова на странице. Для уменьшения обращений к БД
        private $amountUnreadMessages = ""; // В переменную функцией getAmountUnreadMessages() сохраняется количество непрочитанных уведомлений пользователя

        /* Данные параметры используются только для Личного кабинета, для вкладки с Избранными объектами */
        private $propertyLightArr; // Массив массивов. После выполнения метода searchProperties содержит минимальные данные по ВСЕМ избранным объектам
        private $propertyFullArr; // Массив массивов. После выполнения метода searchProperties содержит полные данные, включая фотографии, по нескольким первым в выборке объектам (количество указывается в качестве первого параметра к методу searchProperties)

        // КОНСТРУКТОР
        public function __construct()
        {
            // Проверяем, авторизован ли пользователь, и если да, инициализируем параметры объекта (id, typeTenant, typeOwner...) соответствующими значениями из БД
            $this->login();

            // Получим количество непрочитанных уведомлений (новостей) для данного пользователя
            $this->getAmountUnreadMessages();

            // Инициализируем переменные typeTenant и typeOwner
            $this->isTenant();
            $this->isOwner();
        }

        // ДЕСТРУКТОР
        public function __destruct() {}

        // Является ли пользователь арендатором (то есть имеет действующий поисковый запрос или регистрируется в качестве арендатора)
        public function isTenant()
        {
            if ($this->typeTenant !== NULL) {
                return $this->typeTenant;
            }

            // Если пользователь авторизован, то значения typeTenant и typeOwner будут записаны в переменные объекта из БД автоматически
            if ($this->login()) return $this->typeTenant;

            return FALSE;
        }

        // Является ли пользователь собственником (то есть имеет хотя бы 1 объявление или регистрируется в качестве собственника)
        public function isOwner()
        {
            if ($this->typeOwner !== NULL) {
                return $this->typeOwner;
            }

            // Если пользователь авторизован, то значения typeTenant и typeOwner будут записаны в переменные объекта из БД автоматически
            if ($this->login()) return $this->typeOwner;

            return FALSE;
        }

		// Является ли пользователь администратором. Возвращает ассоциированный массив с правами доступа
		// Если пользователь не является администратором, то все права у него будут с флагами FALSE
		public function isAdmin() {
			if ($this->typeAdmin === NULL || $this->typeAdmin == FALSE) return array('newOwner' => FALSE, 'newAdvertAlien' => FALSE, 'searchUser' => FALSE);

			if (substr($this->typeAdmin, 0, 1) == "1") $result['newOwner'] = TRUE; else $result['newOwner'] = FALSE;
			if (substr($this->typeAdmin, 1, 1) == "1") $result['newAdvertAlien'] = TRUE; else $result['newAdvertAlien'] = FALSE;
			if (substr($this->typeAdmin, 2, 1) == "1") $result['searchUser'] = TRUE; else $result['searchUser'] = FALSE;

			return $result;
		}

        // Метод возвращает id пользователя
        public function getId()
        {
            return $this->id;
        }

        // Метод возвращает массив идентификаторов избранных объявлений текущего пользователя (если он не авторизован, то пустой массив)
        public function getFavoritesPropertysId()
        {
            return $this->favoritesPropertysId;
        }

        // Метод добавляет в избранные у данного пользователя идентификатор объекта недвижимости $propertyId
        public function addFavoritesPropertysId($propertyId = FALSE)
        {

            if (!$this->login()) return FALSE;
            if ($propertyId == FALSE) return FALSE;
            if (in_array($propertyId, $this->favoritesPropertysId)) return TRUE;

            $this->favoritesPropertysId[] = $propertyId;
            $favoritesPropertysIdSerialized = serialize($this->favoritesPropertysId);

            // Сохраняем новые изменения в БД в таблицу поисковых запросов
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("UPDATE users SET favoritesPropertysId=? WHERE id=?") === FALSE)
                OR ($stmt->bind_param("ss", $favoritesPropertysIdSerialized, $this->id) === FALSE)
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
        public function removeFavoritesPropertysId($propertyId = FALSE)
        {

            if (!$this->login()) return FALSE;
            if ($propertyId == FALSE) return FALSE;
            if (!in_array($propertyId, $this->favoritesPropertysId)) return TRUE;

            // Ищем id нашего объекта среди id избранных объектов. Если он там есть, то получим номер позиции, если нет - FALSE
            $key = array_search($propertyId, $this->favoritesPropertysId);
            if ($key === FALSE) return FALSE; // Если наш объект находится в массиве избранных объектов на 0 позиции, то нужно, чтобы условие срабатывало и этот объект можно было удалить из массива избранных, поэтому используется строгое равенство

            // Удаляем $propertyId из списка избранных объявлений
            array_splice($this->favoritesPropertysId, $key, 1);
            $favoritesPropertysIdSerialized = serialize($this->favoritesPropertysId);

            // Сохраняем новые изменения в БД в таблицу поисковых запросов
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("UPDATE users SET favoritesPropertysId=? WHERE id=?") === FALSE)
                OR ($stmt->bind_param("ss", $favoritesPropertysIdSerialized, $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            return TRUE;
        }

        // Вычисляет массивы: 1. C краткими данными (id, coordX, coordY) о ВСЕХ избранных объектах недвижимости
        // 2. Кроме того по первым $amountFullProperties объектам недвижимости вычисляет полные данные, даже с фотографиями.
        // Объекты недвижимости отсортированы в обоих массивах по увеличению стоимости аренды с учетом коммунальных платежей
        public function searchProperties($amountFullProperties)
        {
            // Получим минимальные данные (id, coordX, coordY) по всем объектам недвижимости, подходящим под параметры поискового запроса
            $this->findPropertyLightArr();

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

        }

        // Метод записывает в параметр $this->propertyLightArr массив массивов, содержащий минимальные данные по всем объектам недвижимости, соответствующим данным условиям поиска
        private function findPropertyLightArr() {

            // Убедимся, что список идентификаторов объектов недвижимости представляет собой массив и его длина не равна нулю
            if (!is_array($this->favoritesPropertysId) || count($this->favoritesPropertysId) == 0) $this->propertyLightArr = array();

            // Собираем строку WHERE для поискового запроса к БД
            $strWHERE = " (";
            for ($i = 0, $s = count($this->favoritesPropertysId); $i < $s; $i++) {
                $strWHERE .= " id = '" . $this->favoritesPropertysId[$i] . "'";
                if ($i < $s - 1) $strWHERE .= " OR";
            }
            $strWHERE .= ") AND (status = 'опубликовано')"; //TODO: сделать особое отображение (засеренное) для не опубликованных объявлений, тогда можно будет снять это ограничение на показ пользователю в избранных только еще опубликованных объектов

            // Получаем данные из БД - ВСЕ объекты недвижимости, которые являются избранными для данного пользователя
            // Сортируем по стоимости аренды и не ограничиваем количество объявлений - все, добавленные в избранные
            // В итоге получим массив ($this->propertyLightArr), каждый элемент которого представляет собой также массив значений конкретного объявления по недвижимости
            $res = DBconnect::get()->query("SELECT id, coordX, coordY FROM property WHERE".$strWHERE." ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting");
            if ((DBconnect::get()->errno)
                OR (($this->propertyLightArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
            ) {
                // Логируем ошибку
                //TODO: сделать логирование ошибки
                $this->propertyLightArr = array();
            }

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

			// Получим все заявки на просмотр для этих объектов недвижимости
			$allRequestToView = DBconnect::getAllRequestToViewForProperties($propertiesId);

            // Перебираем массив, полученный из БД и собираем все id арендаторов, отправивших заявки на просмотр, в одномерный массив - без повторов
            $tenantsId = array();
			foreach ($allRequestToView as $value) {
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
            if (isset($_SESSION['amountUnreadMessages'])) {
                $this->amountUnreadMessages = $_SESSION['amountUnreadMessages'];
                return $_SESSION['amountUnreadMessages'];
            }

            // Инициализируем переменную для возвращения
            $result = 0;

            // Считаем количество непрочитанных уведомлений пользователя
            $res = DBconnect::get()->query("SELECT COUNT(*) FROM messagesNewProperty WHERE userId = '".$this->id."' AND isReaded = 'не прочитано'");
            if ((DBconnect::get()->errno)
                OR (($res = $res->fetch_row()) === NULL)
            ) {
                //TODO: сделать логирование ошибки
                $result = 0;
            } else {
                $result = $res[0];

                // Сохраним результат в переменную сессии. Сохранение результата только в случае успеха позволит при загрузке следующей страницы переполучить значение для тех случаев, когда попытка подсчета закончилась неудачей
                $_SESSION['amountUnreadMessages'] = $result;
            }

            //TODO: сделать подсчет количества уведомлений и по другим таблицам уведомлений

            // Сохраним также результат в переменную объекта пользователя
            $this->amountUnreadMessages = $result;

            return $result;
        }

        // Функция проверяет - залогинен ли пользователь сейчас (возвращает TRUE или FALSE).
        // И если пользователь залогинен, то обновляет его ключевые личные параметры (id, typeTenant, typeOwner) в соответствии с указанными в БД
        public function login()
        {
            // Если данная функция уже вызывалась на этой странице, то результат ее работы сохранен в приватной переменной, достаточно выдать его
            if ($this->isLoggedIn !== NULL) return $this->isLoggedIn;

            // Если сессия еще не была запущена - запускаем.
            if (!isset($_SESSION)) {
                session_start();
            }

            // Инициализируем переменную для проверки сессии пользователя. Если какая-то сесcия есть - проверим ее актуальность: если найдется пользователь у которого идентификатор последней сессии совпадет с этим - значит это он и есть
            if (isset($_SESSION['id'])) $sessionId = $_SESSION['id']; else $sessionId = "крокодил"; // Если id сессии не определен, то инициализируем соответствующую переменную комбинацией символов, которая точно не встречается в БД в качестве идентификатора сессии
            // Инициализируем переменную для проверки куки пользователя. Как запасной вариант для того, чтобы убедиться в авторизованности данного пользователя на этой машине
            if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) $cookieLogin = $_COOKIE['login']; else $cookieLogin = NULL; // Если в куки логин не определен, то инициализируем соответствующую переменную комбинацией символов, которая точно не встречается в БД в качестве логина пользователя

            // Если у пользователя нет идентификатора сессии и нет куки (логин + пароль), то он точно не авторизован
            if ($sessionId === "крокодил" && $cookieLogin === NULL) {
                $this->isLoggedIn = FALSE;
                return FALSE;
            }

            // Получим из БД данные ($res) по пользователю с идентификатором сессии = $_SESSION['id'] или логином = $_COOKIE['login']
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("SELECT id, typeTenant, typeOwner, typeAdmin, name, secondName, surname, telephon, login, password, user_hash, favoritesPropertysId FROM users WHERE user_hash=? OR login=?") === FALSE)
                OR ($stmt->bind_param("ss", $sessionId, $cookieLogin) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                $res = array();
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            }

            // Если никого не нашли или нашли данные больше чем по 1 пользователю - значит наш user не авторизован
            if (!is_array($res) || count($res) != 1) {

                // На всякий случай удаляем id сессии (если он конечно был указан)
                unset($_SESSION['id']);

                // На всякий случай удаляем куки (если они были конечно)
                setcookie("login", "", time() - 1, '/');
                setcookie("password", "", time() - 1, '/');

                $this->isLoggedIn = FALSE;
                return FALSE;

            }

            // Убедимся, что данные пользователя (id сессиии или куки) не устарели - соответствуют данным из БД
            $user_hashFromDB = $res[0]['user_hash'];
            $idFromDB = $res[0]['id'];
            $loginFromDB = $res[0]['login'];
            $passwordFromDB = $res[0]['password'];

            if ($user_hashFromDB == $sessionId || md5($loginFromDB . $passwordFromDB) == $_COOKIE['password']) {

                // Сохраняем ключевые параметры пользователя, полученные из БД в параметры объекта
                $this->id = $res[0]['id'];
                if (isset($res[0]['typeTenant'])) {
                    if ($res[0]['typeTenant'] == "TRUE") $this->typeTenant = TRUE;
                    if ($res[0]['typeTenant'] == "FALSE") $this->typeTenant = FALSE;
                }
                if (isset($res[0]['typeOwner'])) {
                    if ($res[0]['typeOwner'] == "TRUE") $this->typeOwner = TRUE;
                    if ($res[0]['typeOwner'] == "FALSE") $this->typeOwner = FALSE;
                }
                if (isset($res[0]['typeAdmin'])) $this->typeAdmin = $res[0]['typeAdmin'];
                if (isset($res[0]['favoritesPropertysId'])) {
                    if (($unserializedData = unserialize($res[0]['favoritesPropertysId'])) != FALSE && is_array($unserializedData)) $this->favoritesPropertysId = $unserializedData;
                }
                if (isset($res[0]['name'])) $this->name = $res[0]['name'];
                if (isset($res[0]['secondName'])) $this->secondName = $res[0]['secondName'];
                if (isset($res[0]['surname'])) $this->surname = $res[0]['surname'];
                if (isset($res[0]['telephon'])) $this->telephon = $res[0]['telephon'];

                // Обновим куки (или добавим, если их ранее не было), чтобы после перезапуска браузера сессия не слетала
                setcookie("login", "", time() - 1, '/');
                setcookie("password", "", time() - 1, '/');
                setcookie("login", $loginFromDB, time() + 60 * 60 * 24 * 7, '/');
                setcookie("password", md5($loginFromDB . $passwordFromDB), time() + 60 * 60 * 24 * 7, '/');

                // Запускаем новую сессию и фиксируем время последнего действия пользователя
                $this->newSession($idFromDB);
                $this->lastAct($idFromDB);

                // Вернули ответ - пользователь залогинен
                $this->isLoggedIn = TRUE;
                return TRUE;

            } else {

                // На всякий случай удаляем id сессии (если он конечно был указан)
                unset($_SESSION['id']);

                // На всякий случай удаляем куки (если они были конечно)
                setcookie("login", "", time() - 1, '/');
                setcookie("password", "", time() - 1, '/');

                $this->isLoggedIn = FALSE;
                return FALSE;

            }

        }

        // Функция для авторизации (входа) пользователя на сайте.
        // Возвращает массив с ошибками в случае невозможности авторизации пользователя и пустой массив при успехе
        function enter()
        {
            $error = array(); // Массив для ошибок

            if ($_POST['login'] != "" && $_POST['password'] != "") //если поля заполнены
            {
                $login = $_POST['login'];
                $password = $_POST['password'];

                // Получим из БД данные ($res) по пользователю с логином = $login
                $stmt = DBconnect::get()->stmt_init();
                if (($stmt->prepare("SELECT id, typeTenant, typeOwner, typeAdmin, login, password FROM users WHERE login=?") === FALSE)
                    OR ($stmt->bind_param("s", $login) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $error[] = "Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.";
                    return $error;
                }

                // Если нашлась одна строка, значит такой юзер существует в БД
                if (is_array($res) && count($res) == 1) {

                    $idFromDB = $res[0]['id'];
                    $loginFromDB = $res[0]['login'];
                    $passwordFromDB = $res[0]['password'];

                    if ($passwordFromDB == $password) // Cравниваем указанный пользователем пароль с паролем из БД
                    {
						// Сохраняем ключевые параметры пользователя, полученные из БД в параметры объекта
						if (isset($res[0]['typeTenant'])) {
							if ($res[0]['typeTenant'] == "TRUE") $this->typeTenant = TRUE;
							if ($res[0]['typeTenant'] == "FALSE") $this->typeTenant = FALSE;
						}
						if (isset($res[0]['typeOwner'])) {
							if ($res[0]['typeOwner'] == "TRUE") $this->typeOwner = TRUE;
							if ($res[0]['typeOwner'] == "FALSE") $this->typeOwner = FALSE;
						}
						if (isset($res[0]['typeAdmin'])) $this->typeAdmin = $res[0]['typeAdmin'];

                        // Пишем логин и хэшированный пароль в cookie, также создаём переменную сессии
                        setcookie("login", "", time() - 1, '/');
                        setcookie("password", "", time() - 1, '/');
                        setcookie("login", $loginFromDB, time() + 60 * 60 * 24 * 7);
                        setcookie("password", md5($loginFromDB . $passwordFromDB), time() + 60 * 60 * 24 * 7);
                        $this->newSession($idFromDB);
                        $this->lastAct($idFromDB);

                        return $error;

                    } else //если пароли не совпали
                    {
                        $error[] = "Неверный логин или пароль";
                        return $error;
                    }
                } else // Если такого пользователя не найдено в БД
                {
                    $error[] = "Неверный логин или пароль";
                    return $error;
                }

            } else {
                $error[] = "Укажите Ваш логин и пароль";
                return $error;
            }
        }

        // Формирует уникальный идентификатор сессии пользователя, записывает его в БД и назначает в переменные сессии
        private function newSession($userId)
        {
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
        private function lastAct($userId)
        {
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