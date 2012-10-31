<?php

    class User
    {
        public $name = "";
        public $secondName = "";
        public $surname = "";
        public $sex = "0";
        public $nationality = "0";
        public $birthday = "";
        public $login = "";
        public $password = "";
        public $telephon = "";
        public $email = "";

        public $fileUploadId = "";
        public $uploadedFoto = array(); // В переменной будет храниться информация о загруженных фотографиях. Представляет собой массив ассоциированных массивов
        public $primaryFotoId = "";

        public $currentStatusEducation = "0";
        public $almamater = "";
        public $speciality = "";
        public $kurs = "";
        public $ochnoZaochno = "0";
        public $yearOfEnd = "";
        public $statusWork = "0";
        public $placeOfWork = "";
        public $workPosition = "";
        public $regionOfBorn = "";
        public $cityOfBorn = "";
        public $shortlyAboutMe = "";

        public $vkontakte = "";
        public $odnoklassniki = "";
        public $facebook = "";
        public $twitter = "";

        public $typeOfObject = "0";
        public $amountOfRooms = array();
        public $adjacentRooms = "0";
        public $floor = "0";
        public $minCost = "";
        public $maxCost = "";
        public $pledge = "";
        public $prepayment = "0";
        public $district = array();
        public $withWho = "0";
        public $linksToFriends = "";
        public $children = "0";
        public $howManyChildren = "";
        public $animals = "0";
        public $howManyAnimals = "";
        public $termOfLease = "0";
        public $additionalDescriptionOfSearch = "";

        public $lic = "";

        private $id = "";
        private $typeTenant = NULL;
        private $typeOwner = NULL;
        private $emailReg = "";
        private $user_hash = "";
        private $last_act = "";
        private $reg_date = "";
        private $favoritesPropertysId = array();

        private $interestingPropertysId = array();

        private $isLoggedIn = ""; // В переменную сохраняется функцией login() значение FALSE или TRUE после первого вызова на странице. Для уменьшения обращений к БД
        private $DBlink = FALSE; // Переменная для хранения объекта соединения с базой данных
        private $globFunc = FALSE; // Переменная для хранения глобальных функций

        // КОНСТРУКТОР
        // В качестве входных параметров: $DBlink объект соединения с базой данных
        public function __construct($globFunc = FALSE, $DBlink = FALSE)
        {
            // Если объект с глобальными функциями получен - сделаем его доступным для всех методов класса
            if ($globFunc != FALSE) {
                $this->globFunc = $globFunc;
            }

            // Если объект соединения с БД получен - сделаем его доступным для всех методов класса
            if ($DBlink != FALSE) {
                $this->DBlink = $DBlink;
            }

            // Инициализируем переменную "сессии" для временного сохранения фотографий
            $this->fileUploadId = $this->globFunc->generateCode(7);

            // Проверяем, авторизован ли пользователь, и если да, инициализируем параметры объекта соответствующими значениями из БД
            $this->login();

            // Инициализируем переменные typeTenant и typeOwner
            $this->isTenant();
            $this->isOwner();
        }

        // ДЕСТРУКТОР
        public function __destruct()
        {

        }

        // Является ли пользователь арендатором (то есть имеет действующий поисковый запрос или регистрируется в качестве арендатора)
        public function isTenant()
        {
            if ($this->typeTenant != NULL) {
                return $this->typeTenant;
            }

            // Если пользователь авторизован, то значение typeTenant будет записано в переменную $this->typeTenant из БД автоматически
            if ($this->login()) return $this->typeTenant;

            // Если пользователь еще только регистрируется, то возвращаем значение из get параметров
            if (isset($_GET['typeTenant'])) {
                $this->typeTenant = TRUE;
            } else {
                $this->typeTenant = FALSE;
            }
            if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {
                $this->typeTenant = TRUE;
            }
            return $this->typeTenant;
        }

        // Является ли пользователь собственником (то есть имеет хотя бы 1 объявление или регистрируется в качестве собственника)
        public function isOwner()
        {
            if ($this->typeOwner != NULL) {
                return $this->typeOwner;
            }

            // Если пользователь авторизован, то значение typeOwner будет записано в переменную $this->typeOwner из БД автоматически
            if ($this->login()) return $this->typeOwner;

            // Если пользователь еще только регистрируется, то возвращаем значение из get параметров
            if (isset($_GET['typeOwner'])) {
                $this->typeOwner = TRUE;
            } else {
                $this->typeOwner = FALSE;
            }
            if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {
                $this->typeOwner = TRUE;
            }
            return $this->typeOwner;
        }

        // Метод возвращает id пользователя
        public function getId() {

            if ($this->id == "") return FALSE;

            return $this->id;
        }

        // Функция сохраняет личные параметры пользователя (текущие значения параметров данного объекта) в БД. Все параметры, кроме поискового запроса (у него отдельная функция)
        // $typeOfUser = "new" - режим сохранения для нового (регистрируемого пользователя)
        // $typeOfUser = "edit" - режим сохранения для редактируемых параметров (для существующего пользователя)
        // Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
        public function saveCharacteristicToDB($typeOfUser)
        {

            // Если запись данные в БД требуется не для нового пользователя (не на странице регистрации) и данный пользователь не авторизован, то функция не выполняется
            if ($typeOfUser == "edit" && !$this->login()) return FALSE;
            if ($typeOfUser == "edit" && $this->id == "") return FALSE;

            // Корректируем дату дня рождения для того, чтобы сделать ее пригодной для сохранения в базу данных
            $birthdayDB = $this->globFunc->dateFromViewToDB($this->birthday);
            // Получаем текущее время для сохранения в качестве даты регистрации и даты последнего действия
            $tm = time();
            $last_act = $tm;
            $reg_date = $tm; // Сохранится в БД только для нового пользователя $typeOfUser = "new"
            // Сериализуем массив с избранными объявлениями
            $favoritesPropertysId = serialize($this->favoritesPropertysId);
            // Преобразуем из логических в строковые (MySQL почему-то не поддерживает сохранение логических параметров)
            if ($this->typeTenant === TRUE) $typeTenant = "TRUE";
            if ($this->typeTenant === FALSE) $typeTenant = "FALSE";
            if ($this->typeTenant === NULL) $typeTenant = NULL;
            if ($this->typeOwner === TRUE) $typeOwner = "TRUE";
            if ($this->typeOwner === FALSE) $typeOwner = "FALSE";
            if ($this->typeOwner === NULL) $typeOwner = NULL;

            // Для простоты технической поддержки пользователей пойдем на небольшой риск с точки зрения безопасности и будем хранить пароли пользователей на сервере в БД без соли и шифрования
            /*$salt = mt_rand(100, 999);
              $password = md5(md5($password) . $salt);*/

            // Пишем данные пользователя в БД. При успехе в $res сохраняем TRUE, иначе - FALSE
            // Код для сохранения данных разный: для нового пользователя и при редактировании параметров существующего пользователя
            if ($typeOfUser == "new") {

                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("INSERT INTO users (typeTenant,typeOwner,name,secondName,surname,sex,nationality,birthday,login,password,telephon,emailReg,email,currentStatusEducation,almamater,speciality,kurs,ochnoZaochno,yearOfEnd,statusWork,placeOfWork,workPosition,regionOfBorn,cityOfBorn,shortlyAboutMe,vkontakte,odnoklassniki,facebook,twitter,lic,last_act,reg_date,favoritesPropertysId) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
                    OR ($stmt->bind_param("ssssssssssssssssssssssssssssssiib", $typeTenant, $typeOwner, $this->name, $this->secondName, $this->surname, $this->sex, $this->nationality, $birthdayDB, $this->login, $this->password, $this->telephon, $this->email, $this->email, $this->currentStatusEducation, $this->almamater, $this->speciality, $this->kurs, $this->ochnoZaochno, $this->yearOfEnd, $this->statusWork, $this->placeOfWork, $this->workPosition, $this->regionOfBorn, $this->cityOfBorn, $this->shortlyAboutMe, $this->vkontakte, $this->odnoklassniki, $this->facebook, $this->twitter, $this->lic, $last_act, $reg_date, $favoritesPropertysId) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->affected_rows) === -1)
                    OR ($res === 0)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

                return TRUE;

            }

            if ($typeOfUser == "edit") {

                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("UPDATE users SET name=?, secondName=?, surname=?, sex=?, nationality=?, birthday=?, password=?, telephon=?, email=?, currentStatusEducation=?, almamater=?, speciality=?, kurs=?, ochnoZaochno=?, yearOfEnd=?, statusWork=?, placeOfWork=?, workPosition=?, regionOfBorn=?, cityOfBorn=?, shortlyAboutMe=?, vkontakte=?, odnoklassniki=?, facebook=?, twitter=?, last_act=? WHERE id=?") === FALSE)
                    OR ($stmt->bind_param("sssssssssssssssssssssssssis", $this->name, $this->secondName, $this->surname, $this->sex, $this->nationality, $birthdayDB, $this->password, $this->telephon, $this->email, $this->currentStatusEducation, $this->almamater, $this->speciality, $this->kurs, $this->ochnoZaochno, $this->yearOfEnd, $this->statusWork, $this->placeOfWork, $this->workPosition, $this->regionOfBorn, $this->cityOfBorn, $this->shortlyAboutMe, $this->vkontakte, $this->odnoklassniki, $this->facebook, $this->twitter, $last_act, $this->id) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->affected_rows) === -1)
                    OR ($res === 0)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

                return TRUE;
            }

            // Пользователь не является ни новым, ни существующим - видимо какая-то ошибка была допущена при передаче параметров методу
            return FALSE;

        }

        // Функция сохраняет актуальные данные о фотографиях пользователя в БД. Если какие-то из ранее загруженных фотографий были удалены пользователем (помечены в браузере на удаление), то функция удаляет их с сервера и из БД
        // TODO: функция пока ничего не возвращает, а может нужно добавить TRUE или FALSE?
        public function saveFotoInformationToDB()
        {

            // ВАЖНО:
            // Функция считает, что если пользователь залогинен, то он уже был зарегистрирован и требуется отредактировать его фотографии
            // Если же пользователь не залогинен, то функция считает его Новым пользователем (а значит у него нет сохраненных фоток в userFotos)
            // ВАЖНО:
            // Схема работы функции:
            // 1. Проверить наличие массива данных о фотографиях ($this->uploadedFoto), а также id пользователя
            // 2. Собираем инфу по всем фотографиям пользователя из БД tempFotos (по $this->fileUploadId) и userFotos (по id пользователя)
            // 3. Добавляем в полученные из БД данные актуалную инфу по статусом (основная/неосновная) и помечаем те фотки, которые нужно удалить
            // 4. Перебираем массив и удаляем ненужные фотки с жесткого диска
            // 5. Редактируем данные по нужным фоткам (UPDATE для userFotos)
            // 6. Добавляем данные по нужным фоткам (INSERT для userFotos)
            // 7. Удаляем ненужные фотки (DELETE для userFotos и для tempFotos)

            // На всякий случай, проверим на массив
            if (!is_array($this->uploadedFoto)) return FALSE;

            // Для выполнения функция у пользователя обязательно должен быть id
            if ($this->id = "") return FALSE;

            // Получаем данные по всем фоткам с нашим $this->fileUploadId
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("SELECT * FROM tempFotos WHERE fileUploadId=?") === FALSE)
                OR ($stmt->bind_param("s", $this->fileUploadId) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($allFotos = $stmt->get_result()) === FALSE)
                OR (($allFotos = $allFotos->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                $allFotos = array();
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            }

            // Пометим все члены массива признаком их получения из таблицы tempFotos и дополним id пользователя
            foreach ($allFotos as $value) {
                $value['fromTable'] = "tempFotos";
            }

            // Получаем данные по всем фоткам пользователя (с идентификатором $this->id)
            // Но только для существующего - авторизованного пользователя (не для нового)
            if ($this->login()) {
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT * FROM userFotos WHERE userId=?") === FALSE)
                    OR ($stmt->bind_param("s", $this->id) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                } else {

                    // Пометим все члены массива признаком их получения из таблицы userFotos
                    foreach ($res as $value) {
                        $value['fromTable'] = "userFotos";
                    }

                    $allFotos = array_merge($allFotos, $res);
                }
            }


            // Перебираем все имеющиеся фотографии пользователя и актуализируем их параметры
            $primaryFotoExists = 0; // Инициализируем переменную, по которой после прохода по всем фотографиям, полученным в форме, сможем сказать была ли указана пользователем основная фотка (число - сколько фоток со статусом основная мы получили с клиента) или нет (0)
            for ($i = 0; $i < count($allFotos); $i++) {

                // Для сокращения количества запросов на UPDATE будем отмечать особым признаком те фотографии, по которым требуется выполнения этого запроса к БД
                $allFotos[$i]['updated'] = FALSE;

                // На заметку: в массиве $uploadedFoto также содержится актуальная информация по всем статусам фотографий, но легче получить id основной фотки из формы, а не из этого массива
                if ($allFotos[$i]['id'] == $this->primaryFotoId) {
                    // Проверяем - нужно ли для данной фотографии проводить UPDATE
                    if ($allFotos[$i]['fromTable'] == "userFotos" && $allFotos[$i]['status'] != 'основная') {
                        $allFotos[$i]['updated'] = TRUE;
                    }
                    $allFotos[$i]['status'] = 'основная';
                    // Признак наличия основной фотографии
                    $primaryFotoExists++;
                } else {
                    if ($allFotos[$i]['fromTable'] == "userFotos" && $allFotos[$i]['status'] != '') {
                        $allFotos[$i]['updated'] = TRUE;
                    }
                    $allFotos[$i]['status'] = '';
                }

                // Отмечаем фотографии на удаление
                $allFotos[$i]['forRemove'] = TRUE;
                foreach ($this->uploadedFoto as $value) {
                    if ($allFotos[$i]['id'] == $value['id']) {
                        $allFotos[$i]['forRemove'] = FALSE;
                        break;
                    }
                }

                // Подготовим данные о пути к каталогу хранения фотографии в вид, пригодный для перезаписи в БД
                //$$allFotos[$i]['folder'] = str_replace('\\', '\\\\', $$allFotos[$i]['folder']); // Переменная folder уже содержит в себе один или несколько '\', но для того, чтобы при сохранении в БД не возникло проблем, к нему нужно добавить еще один символ '\', в этом случае mysql будет воспринимать "\\" как один знак "\" и не будет считать его служебгым символом

            }

            // Если пользователь не указал основное фото, то укажем первую попавшуюся фотографию (не помеченную на удаление) в качестве основной
            if ($primaryFotoExists == 0) {
                for ($i = 0; $i < count($allFotos); $i++) {
                    if ($allFotos[$i]['forRemove'] == FALSE) {
                        $allFotos[$i]['status'] = 'основная';
                        break;
                    }
                }
            }


            // Удаляем файлы фотографий (помеченных признаком удаления) с сервера
            for ($i = 0; $i < count($allFotos); $i++) {
                if ($allFotos[$i]['forRemove'] == FALSE) continue;
                if ((unlink($allFotos[$i]['folder'] . '\\small\\' . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension']) === FALSE)
                    OR unlink($allFotos[$i]['folder'] . '\\middle\\' . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension'])
                    OR unlink($allFotos[$i]['folder'] . '\\big\\' . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension'])
                ) {
                    // TODO: сделать сохранение статусов отработки команд по удалению фоток в лог файл. Каждый unlink выдает TRUE, если все хорошо и FALSE, если плохо
                }
            }

            // Выполним запросы на UPDATE данных в userFotos
            $stmt = $this->DBlink->stmt_init();
            if ($stmt->prepare("UPDATE userFotos SET status=? WHERE id=?") === FALSE) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            }
            for ($i = 0; $i < count($allFotos); $i++) {
                if ($allFotos[$i]['fromTable'] == "userFotos" && $allFotos[$i]['updated'] == TRUE && $allFotos[$i]['forRemove'] == FALSE) {
                    if (($stmt->bind_param("ss", $allFotos[$i]['status'], $allFotos[$i]['id']) === FALSE)
                        OR ($stmt->execute() === FALSE)
                        OR (($res = $stmt->affected_rows) === -1)
                        OR ($res === 0)
                        OR ($stmt->close() === FALSE)
                    ) {
                        // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    }
                }
            }

            // Для уменьшения запросов к БД соберем 2 общих заспроса на изменение сразу всех нужных строк
            // Соберем условия WHERE для SQL запросов к БД:
            // на INSERT новых строк в userFotos
            // на DELETE более ненужных фоток из userFotos
            $strINSERT = "";
            $strDELETE = "";
            for ($i = 0; $i < $count = count($allFotos); $i++) {

                if ($allFotos[$i]['fromTable'] == "tempFotos" && $allFotos[$i]['forRemove'] == FALSE) {
                    if ($strINSERT != "") $strINSERT .= ",";
                    $strINSERT .= "('" . $allFotos[$i]['id'] . "','" . $allFotos[$i]['folder'] . "','" . $allFotos[$i]['filename'] . "','" . $allFotos[$i]['extension'] . "','" . $allFotos[$i]['filesizeMb'] . "','" . $this->id . "','" . $allFotos[$i]['status'] . "')";
                }

                if ($allFotos[$i]['forRemove'] == TRUE) {
                    if ($strDELETE != "") $strDELETE .= " OR";
                    $strDELETE .= " id = '" . $allFotos[$i]['id'] . "'";
                }

            }

            // Выполним сформированные запросы
            // INSERT
            if ($strINSERT != "") {
                $res = $this->DBlink->query("INSERT INTO userFotos (id, folder, filename, extension, filesizeMb, userId, status) VALUES " . $strINSERT);
                if (($this->DBlink->errno)
                    OR (($res = $res->affected_rows) === -1)
                    OR ($res === 0)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                }
            }
            // DELETE
            if ($strDELETE != "") {
                $res = $this->DBlink->query("DELETE FROM userFotos WHERE " . $strDELETE);
                if (($this->DBlink->errno)
                    OR (($res = $res->affected_rows) === -1)
                    OR ($res === 0)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                }
            }


            // Удаляем инфу о всех фотках с fileUploadId из tempFotos
            // TODO: Не очень безопасно
            if ($this->fileUploadId != "") {
                $res = $this->DBlink->query("DELETE FROM tempFotos WHERE fileUploadId = '" . $this->fileUploadId . "'");
                if (($this->DBlink->errno)
                    OR ($res->affected_rows === -1)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                }
            }

        }

        // Функция для сохранения параметров поискового запроса пользователя
        public function saveSearchRequestToDB()
        {

            if ($this->isTenant() != TRUE || $this->id == "") return FALSE;

            // Преобразование формата инфы об искомом кол-ве комнат и районах, так как MySQL не умеет хранить массивы
            $amountOfRoomsSerialized = serialize($this->amountOfRooms);
            $districtSerialized = serialize($this->district);
            $interestingPropertysIdSerialized = serialize($this->interestingPropertysId);

            // Непосредственное сохранение данных о поисковом запросе
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("INSERT INTO searchRequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, minCost, maxCost, pledge, prepayment, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, termOfLease, additionalDescriptionOfSearch, interestingPropertysId) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
                OR ($stmt->bind_param("ssbssiiisbssssssssb", $this->id, $this->typeOfObject, $amountOfRoomsSerialized, $this->adjacentRooms, $this->floor, $this->minCost, $this->maxCost, $this->pledge, $this->prepayment, $districtSerialized, $this->withWho, $this->linksToFriends, $this->children, $this->howManyChildren, $this->animals, $this->howManyAnimals, $this->termOfLease, $this->additionalDescriptionOfSearch, $interestingPropertysIdSerialized) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($res === 0)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            return TRUE;
        }

        public function writeCharacteristicFromDB() {

            // Если идентификатор пользователя неизвестен, то дальнейшие действия не имеют смысла
            if ($this->id == "") return FALSE;

            // Получим из БД данные ($res) по пользователю с идентификатором = $this->id
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("SELECT * FROM users WHERE id=?") === FALSE)
                OR ($stmt->bind_param("s", $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Если получено меньше или больше одной строки (одного пользователя) из БД, то сообщаем об ошибке
            if (!is_array($res) || count($res) != 1) {
                // TODO: Сохранить в лог ошибку получения данных пользователя из БД
                return FALSE;
            }

            // Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
            $oneUserDataArr = $res[0];

            // Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию.
            if (isset($oneUserDataArr['id'])) $this->id = $oneUserDataArr['id'];

            if (isset($oneUserDataArr['typeTenant'])) {
                if ($oneUserDataArr['typeTenant'] == "TRUE") $this->typeTenant = TRUE;
                if ($oneUserDataArr['typeTenant'] == "FALSE") $this->typeTenant = FALSE;
            }
            if (isset($oneUserDataArr['typeOwner']))
            {
                if ($oneUserDataArr['typeOwner'] == "TRUE") $this->typeOwner = TRUE;
                if ($oneUserDataArr['typeOwner'] == "FALSE") $this->typeOwner = FALSE;
            }
            if (isset($oneUserDataArr['name'])) $this->name = $oneUserDataArr['name'];
            if (isset($oneUserDataArr['secondName'])) $this->secondName = $oneUserDataArr['secondName'];
            if (isset($oneUserDataArr['surname'])) $this->surname = $oneUserDataArr['surname'];
            if (isset($oneUserDataArr['sex'])) $this->sex = $oneUserDataArr['sex'];
            if (isset($oneUserDataArr['nationality'])) $this->nationality = $oneUserDataArr['nationality'];
            if (isset($oneUserDataArr['birthday'])) $this->birthday = $this->globFunc->dateFromDBToView($oneUserDataArr['birthday']);
            if (isset($oneUserDataArr['login'])) $this->login = $oneUserDataArr['login'];
            if (isset($oneUserDataArr['password'])) $this->password = $oneUserDataArr['password'];
            if (isset($oneUserDataArr['telephon'])) $this->telephon = $oneUserDataArr['telephon'];
            if (isset($oneUserDataArr['emailReg'])) $this->emailReg = $oneUserDataArr['emailReg'];
            if (isset($oneUserDataArr['email'])) $this->email = $oneUserDataArr['email'];

            if (isset($oneUserDataArr['currentStatusEducation'])) $this->currentStatusEducation = $oneUserDataArr['currentStatusEducation'];
            if (isset($oneUserDataArr['almamater'])) $this->almamater = $oneUserDataArr['almamater'];
            if (isset($oneUserDataArr['speciality'])) $this->speciality = $oneUserDataArr['speciality'];
            if (isset($oneUserDataArr['kurs'])) $this->kurs = $oneUserDataArr['kurs'];
            if (isset($oneUserDataArr['ochnoZaochno'])) $this->ochnoZaochno = $oneUserDataArr['ochnoZaochno'];
            if (isset($oneUserDataArr['yearOfEnd'])) $this->yearOfEnd = $oneUserDataArr['yearOfEnd'];
            if (isset($oneUserDataArr['statusWork'])) $this->statusWork = $oneUserDataArr['statusWork'];
            if (isset($oneUserDataArr['placeOfWork'])) $this->placeOfWork = $oneUserDataArr['placeOfWork'];
            if (isset($oneUserDataArr['workPosition'])) $this->workPosition = $oneUserDataArr['workPosition'];
            if (isset($oneUserDataArr['regionOfBorn'])) $this->regionOfBorn = $oneUserDataArr['regionOfBorn'];
            if (isset($oneUserDataArr['cityOfBorn'])) $this->cityOfBorn = $oneUserDataArr['cityOfBorn'];
            if (isset($oneUserDataArr['shortlyAboutMe'])) $this->shortlyAboutMe = $oneUserDataArr['shortlyAboutMe'];
            if (isset($oneUserDataArr['vkontakte'])) $this->vkontakte = $oneUserDataArr['vkontakte'];
            if (isset($oneUserDataArr['odnoklassniki'])) $this->odnoklassniki = $oneUserDataArr['odnoklassniki'];
            if (isset($oneUserDataArr['facebook'])) $this->facebook = $oneUserDataArr['facebook'];
            if (isset($oneUserDataArr['twitter'])) $this->twitter = $oneUserDataArr['twitter'];

            if (isset($oneUserDataArr['lic'])) $this->lic = $oneUserDataArr['lic'];
            if (isset($oneUserDataArr['user_hash'])) $this->user_hash = $oneUserDataArr['user_hash'];
            if (isset($oneUserDataArr['last_act'])) $this->last_act = $oneUserDataArr['last_act'];
            if (isset($oneUserDataArr['reg_date'])) $this->reg_date = $oneUserDataArr['reg_date'];
            if (isset($oneUserDataArr['favoritesPropertysId'])) $this->favoritesPropertysId = unserialize($oneUserDataArr['favoritesPropertysId']);

            return TRUE;
        }

        // Метод читает данные о фотографиях из БД и записывает их в параметры пользователя
        public function writeFotoInformationFromDB() {

            // Если идентификатор пользователя неизвестен, то дальнейшие действия не имеют смысла
            if ($this->id == "") return FALSE;

            // Получим из БД данные ($res) по пользователю с идентификатором = $this->id
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("SELECT * FROM userfotos WHERE userId=?") === FALSE)
                OR ($stmt->bind_param("s", $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Сохраняем в параметры объекта массив массивов, каждый из которых содержит данные по 1 фотографии
            $this->uploadedFoto = $res;

            // Сохраняем идентификатор основной фотографии пользователя в параметры объекта
            foreach ($res as $value) {
                if ($value['status'] == 'основная') {
                    $this->primaryFotoId = $value['id'];
                    break;
                }
            }

            return TRUE;

        }

        // Метод читает данные о поисковом запросе из БД и записывает их в параметры пользователя
        public function writeSearchRequestFromDB() {

            // Если идентификатор пользователя неизвестен, то дальнейшие действия не имеют смысла
            if ($this->id == "") return FALSE;

            // Получим из БД данные ($res) по пользователю с идентификатором = $this->id
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("SELECT * FROM searchrequests WHERE userId=?") === FALSE)
                OR ($stmt->bind_param("s", $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Если получено меньше или больше одной строки (а на одного пользователя строго приходится 1 поисковый запрос) из БД, то либо произошла шибка, либо (что значительно более вероятно) у данного пользователя нет поискового запроса
            if (!is_array($res) || count($res) != 1) {
                if ($this->isTenant() && count($res) == 0) {
                    // TODO: Сохранить в лог ошибку - пользователь является арендатором, но не имеет поискового запроса!
                }
                if (count($res) > 1) {
                    // TODO: Сохранить в лог ошибку - у пользователя заведено более 1 поискового запроса!
                }

                // В любом случае, при таком раскладе записать данные поискового запроса в параметры пользователя не представляется возможным
                return FALSE;
            }

            if (!$this->isTenant() && count($res) != 0) {
                // TODO: Сохранить в лог ошибку - пользователь НЕ является арендатором, но имеет поисковый запрос!
            }

            // Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
            $oneUserDataArr = $res[0];

            // Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию
            if (isset($oneUserDataArr['typeOfObject'])) $this->typeOfObject = $oneUserDataArr['typeOfObject'];
            if (isset($oneUserDataArr['amountOfRooms'])) $this->amountOfRooms = unserialize($oneUserDataArr['amountOfRooms']);
            if (isset($oneUserDataArr['adjacentRooms'])) $this->adjacentRooms = $oneUserDataArr['adjacentRooms'];
            if (isset($oneUserDataArr['floor'])) $this->floor = $oneUserDataArr['floor'];
            if (isset($oneUserDataArr['minCost'])) $this->minCost = $oneUserDataArr['minCost'];
            if (isset($oneUserDataArr['maxCost'])) $this->maxCost = $oneUserDataArr['maxCost'];
            if (isset($oneUserDataArr['pledge'])) $this->pledge = $oneUserDataArr['pledge'];
            if (isset($oneUserDataArr['prepayment'])) $this->prepayment = $oneUserDataArr['prepayment'];
            if (isset($oneUserDataArr['district'])) $this->district = unserialize($oneUserDataArr['district']);
            if (isset($oneUserDataArr['withWho'])) $this->withWho = $oneUserDataArr['withWho'];
            if (isset($oneUserDataArr['linksToFriends'])) $this->linksToFriends = $oneUserDataArr['linksToFriends'];
            if (isset($oneUserDataArr['children'])) $this->children = $oneUserDataArr['children'];
            if (isset($oneUserDataArr['howManyChildren'])) $this->howManyChildren = $oneUserDataArr['howManyChildren'];
            if (isset($oneUserDataArr['animals'])) $this->animals = $oneUserDataArr['animals'];
            if (isset($oneUserDataArr['howManyAnimals'])) $this->howManyAnimals = $oneUserDataArr['howManyAnimals'];
            if (isset($oneUserDataArr['termOfLease'])) $this->termOfLease = $oneUserDataArr['termOfLease'];
            if (isset($oneUserDataArr['additionalDescriptionOfSearch'])) $this->additionalDescriptionOfSearch = $oneUserDataArr['additionalDescriptionOfSearch'];
            if (isset($oneUserDataArr['interestingPropertysId'])) $this->interestingPropertysId = unserialize($oneUserDataArr['interestingPropertysId']);

            return TRUE;

        }

        // Метод удаляет параметры поискового запроса пользователя из БД, сбрасывает соответствующие настройки объекта на "по-умолчанию", а также меняет статус isTenant на FALSE.
        public function removeSearchRequest() {

            // Проверка на наличие id пользователя
            if ($this->id == "") return FALSE;

            // Удалим данные поискового запроса по данному пользователю из БД
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("DELETE FROM searchrequests WHERE userId=?") === FALSE)
                OR ($stmt->bind_param("s", $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Обновляем статус данного пользователю (он больше не арендатор)
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("UPDATE users SET typeTenant='FALSE' WHERE id=?") === FALSE)
                OR ($stmt->bind_param("s", $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($res === 0)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Внесем соответствующие изменения в статус объекта пользователя
            $this->typeTenant = "FALSE";

            // Скинем на дефолтные параметры поискового запроса данного пользователя
            $this->typeOfObject = "0";
            $this->amountOfRooms = array();
            $this->adjacentRooms = "0";
            $this->floor = "0";
            $this->minCost = "";
            $this->maxCost = "";
            $this->pledge = "";
            $this->prepayment = "0";
            $this->district = array();
            $this->withWho = "0";
            $this->linksToFriends = "";
            $this->children = "0";
            $this->howManyChildren = "";
            $this->animals = "0";
            $this->howManyAnimals = "";
            $this->termOfLease = "0";
            $this->additionalDescriptionOfSearch = "";
            $this->interestingPropertysId = array();

            return TRUE;

        }

        // Записать в качестве параметров user-а значения, полученные через POST запрос
        public function writePOSTparameters()
        {
            //TODO: не проверять и не менять $_POST['login'], если происходит редактирование существующего пользователя (теоретически можно через POST параметр заслать новый логин и метод его поменяет для ранее зарегистрированного пользователя)
            //TODO: убедиться, что если на клиенте удалить все фотки, то при перезагрузке они снова не появятся (из-за того, что $uploadedFoto не придет в POST параметрах и останется предыдущая версия - которая не будет перезатерта)

            if (isset($_POST['name'])) $this->name = htmlspecialchars($_POST['name']);
            if (isset($_POST['secondName'])) $this->secondName = htmlspecialchars($_POST['secondName']);
            if (isset($_POST['surname'])) $this->surname = htmlspecialchars($_POST['surname']);
            if (isset($_POST['sex'])) $this->sex = htmlspecialchars($_POST['sex']);
            if (isset($_POST['nationality'])) $this->nationality = htmlspecialchars($_POST['nationality']);
            if (isset($_POST['birthday'])) $this->birthday = htmlspecialchars($_POST['birthday']);
            if (isset($_POST['login'])) $this->login = htmlspecialchars($_POST['login']);
            if (isset($_POST['password'])) $this->password = htmlspecialchars($_POST['password']);
            if (isset($_POST['telephon'])) $this->telephon = htmlspecialchars($_POST['telephon']);
            if (isset($_POST['email'])) $this->email = htmlspecialchars($_POST['email']);

            if (isset($_POST['fileUploadId'])) $this->fileUploadId = $_POST['fileUploadId'];
            if (isset($_POST['uploadedFoto'])) $this->uploadedFoto = json_decode($_POST['uploadedFoto'], TRUE); // Массив объектов со сведениями о загруженных фотографиях сериализуется в JSON формат на клиенте и передается как содержимое атрибута value одного единственного INPUT hidden
            if (isset($_POST['primaryFotoRadioButton'])) $this->primaryFotoId = htmlspecialchars($_POST['primaryFotoRadioButton']);

            if (isset($_POST['currentStatusEducation'])) $this->currentStatusEducation = htmlspecialchars($_POST['currentStatusEducation']);
            if (isset($_POST['almamater'])) $this->almamater = htmlspecialchars($_POST['almamater']);
            if (isset($_POST['speciality'])) $this->speciality = htmlspecialchars($_POST['speciality']);
            if (isset($_POST['kurs'])) $this->kurs = htmlspecialchars($_POST['kurs']);
            if (isset($_POST['ochnoZaochno'])) $this->ochnoZaochno = htmlspecialchars($_POST['ochnoZaochno']);
            if (isset($_POST['yearOfEnd'])) $this->yearOfEnd = htmlspecialchars($_POST['yearOfEnd']);
            if (isset($_POST['statusWork'])) $this->statusWork = htmlspecialchars($_POST['statusWork']);
            if (isset($_POST['placeOfWork'])) $this->placeOfWork = htmlspecialchars($_POST['placeOfWork']);
            if (isset($_POST['workPosition'])) $this->workPosition = htmlspecialchars($_POST['workPosition']);
            if (isset($_POST['regionOfBorn'])) $this->regionOfBorn = htmlspecialchars($_POST['regionOfBorn']);
            if (isset($_POST['cityOfBorn'])) $this->cityOfBorn = htmlspecialchars($_POST['cityOfBorn']);
            if (isset($_POST['shortlyAboutMe'])) $this->shortlyAboutMe = htmlspecialchars($_POST['shortlyAboutMe']);

            if (isset($_POST['vkontakte'])) $this->vkontakte = htmlspecialchars($_POST['vkontakte']);
            if (isset($_POST['odnoklassniki'])) $this->odnoklassniki = htmlspecialchars($_POST['odnoklassniki']);
            if (isset($_POST['facebook'])) $this->facebook = htmlspecialchars($_POST['facebook']);
            if (isset($_POST['twitter'])) $this->twitter = htmlspecialchars($_POST['twitter']);

            if (isset($_POST['typeOfObject'])) $this->typeOfObject = htmlspecialchars($_POST['typeOfObject']);
            if (isset($_POST['amountOfRooms']) && is_array($_POST['amountOfRooms'])) $this->amountOfRooms = $_POST['amountOfRooms'];
            if (isset($_POST['district']) && is_array($_POST['district'])) $this->district = $_POST['district'];
            if (isset($_POST['adjacentRooms'])) $this->adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
            if (isset($_POST['floor'])) $this->floor = htmlspecialchars($_POST['floor']);
            if (isset($_POST['minCost'])) $this->minCost = htmlspecialchars($_POST['minCost']);
            if (isset($_POST['maxCost'])) $this->maxCost = htmlspecialchars($_POST['maxCost']);
            if (isset($_POST['pledge'])) $this->pledge = htmlspecialchars($_POST['pledge']);
            if (isset($_POST['prepayment'])) $this->prepayment = htmlspecialchars($_POST['prepayment']);
            if (isset($_POST['withWho'])) $this->withWho = htmlspecialchars($_POST['withWho']);
            if (isset($_POST['linksToFriends'])) $this->linksToFriends = htmlspecialchars($_POST['linksToFriends']);
            if (isset($_POST['children'])) $this->children = htmlspecialchars($_POST['children']);
            if (isset($_POST['howManyChildren'])) $this->howManyChildren = htmlspecialchars($_POST['howManyChildren']);
            if (isset($_POST['animals'])) $this->animals = htmlspecialchars($_POST['animals']);
            if (isset($_POST['howManyAnimals'])) $this->howManyAnimals = htmlspecialchars($_POST['howManyAnimals']);
            if (isset($_POST['termOfLease'])) $this->termOfLease = htmlspecialchars($_POST['termOfLease']);
            if (isset($_POST['additionalDescriptionOfSearch'])) $this->additionalDescriptionOfSearch = htmlspecialchars($_POST['additionalDescriptionOfSearch']);

            if (isset($_POST['lic'])) $this->lic = htmlspecialchars($_POST['lic']);
        }

        // Записывает параметры переданные через ассоциированный массив ($paramsArr) в параметры объекта
        private function writeParametersPersonal($paramsArr)
        {
            $this->id = $paramsArr['id'];
            $this->typeTenant = $paramsArr['typeTenant'];
            $this->typeOwner = $paramsArr['typeOwner'];
            $this->name = $paramsArr['name'];
            $this->secondName = $paramsArr['secondName'];
            $this->surname = $paramsArr['surname'];
            $this->sex = $paramsArr['sex'];
            $this->nationality = $paramsArr['nationality'];
            $this->birthday = $paramsArr['birthday'];
            $this->login = $paramsArr['login'];
            $this->password = $paramsArr['password'];
            $this->telephon = $paramsArr['telephon'];
            $this->emailReg = $paramsArr['emailReg'];
            $this->email = $paramsArr['email'];
            $this->currentStatusEducation = $paramsArr['currentStatusEducation'];
            $this->almamater = $paramsArr['almamater'];
            $this->speciality = $paramsArr['speciality'];
            $this->kurs = $paramsArr['kurs'];
            $this->ochnoZaochno = $paramsArr['ochnoZaochno'];
            $this->yearOfEnd = $paramsArr['yearOfEnd'];
            $this->statusWork = $paramsArr['statusWork'];
            $this->placeOfWork = $paramsArr['placeOfWork'];
            $this->workPosition = $paramsArr['workPosition'];
            $this->regionOfBorn = $paramsArr['regionOfBorn'];
            $this->cityOfBorn = $paramsArr['cityOfBorn'];
            $this->shortlyAboutMe = $paramsArr['shortlyAboutMe'];
            $this->vkontakte = $paramsArr['vkontakte'];
            $this->odnoklassniki = $paramsArr['odnoklassniki'];
            $this->facebook = $paramsArr['facebook'];
            $this->twitter = $paramsArr['twitter'];
            $this->lic = $paramsArr['lic'];
            $this->user_hash = $paramsArr['user_hash'];
            $this->last_act = $paramsArr['last_act'];
            $this->reg_date = $paramsArr['reg_date'];
            $this->favoritesPropertysId = $paramsArr['favoritesPropertysId'];
        }

        // Проверка корректности параметров пользователя
        // $typeOfValidation = registration - режим проверки при поступлении данных на регистрацию пользователя (включает в себя проверки параметров профиля и поискового запроса как для арендатора, так и для собственника)
        // $typeOfValidation = createSearchRequest - режим проверки при потуплении команды на создание поискового запроса (нет проверки данных поисковой формы, проверка параметров профиля как у арендатора)
        // $typeOfValidation = validateSearchRequest - режим проверки указанных пользователем параметров поиска в совокупности с данными Профиля (причем вне зависимости от того, является ли пользователь арендатором, проверка осуществляется как будто бы является, так как он желает стать арендатором, формируя поисковый запрос)
        // $typeOfValidation = validateProfileParameters - режим проверки отредактированных пользователем данных Профиля (учитывается, является ли пользователь арендатором, или собственником)
        public function userDataCorrect($typeOfValidation)
        {
            // Подготовим массив для сохранения сообщений об ошибках
            $errors = array();

            // Является ли данный пользователь арендатором или регистрируется в качестве арендатора
            $typeTenant = $this->isTenant();

            // Проверки для блока "Личные данные"
            if ($this->name == "") $errors[] = 'Укажите имя';
            if (strlen($this->name) > 50) $errors[] = 'Слишком длинное имя. Можно указать не более 50-ти символов';
            if ($this->secondName == "") $errors[] = 'Укажите отчество';
            if (strlen($this->secondName) > 50) $errors[] = 'Слишком длинное отчество. Можно указать не более 50-ти символов';
            if ($this->surname == "") $errors[] = 'Укажите фамилию';
            if (strlen($this->surname) > 50) $errors[] = 'Слишком длинная фамилия. Можно указать не более 50-ти символов';
            if ($this->sex == "0") $errors[] = 'Укажите пол';
            if ($this->nationality == "0") $errors[] = 'Укажите внешность';

            if ($this->birthday != "") {
                if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $this->birthday)) $errors[] = 'Неправильный формат даты рождения, должен быть: дд.мм.гггг'; else {
                    if (substr($this->birthday, 0, 2) < "01" || substr($this->birthday, 0, 2) > "31") $errors[] = 'Проверьте дату Дня рождения (допустимо от 01 до 31)';
                    if (substr($this->birthday, 3, 2) < "01" || substr($this->birthday, 3, 2) > "12") $errors[] = 'Проверьте месяц Дня рождения (допустимо от 01 до 12)';
                    if (substr($this->birthday, 6, 4) < "1800" || substr($this->birthday, 6, 4) > "2100") $errors[] = 'Проверьте год Дня рождения (допустимо от 1800 до 2100)';
                }
            } else {
                $errors[] = 'Укажите дату рождения';
            }

            if ($this->login == "") $errors[] = 'Укажите логин';
            if (strlen($this->login) > 50) $errors[] = "Слишком длинный логин. Можно указать не более 50-ти символов";
            // Проверяем логин на занятость. Это нужно делать только прирегистрации, так как в дальнейшем логин пользователя невозможно изменить
            if ($this->login != "" && strlen($this->login) < 50 && $typeOfValidation == "registration") {
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT id FROM users WHERE login=?") === FALSE)
                    OR ($stmt->bind_param("s", $this->login) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $error[] = "Не удалось проверить логин на занятость: ошибка обращения к базе данных (. Попробуйте зайти к нам немного позже.";
                } else {
                    if (count($res) != 0) $errors[] = 'Пользователь с таким логином уже существует, укажите другой логин';
                }
            }
            if ($this->password == "" && ($typeOfValidation == "registration" || $typeOfValidation == "validateProfileParameters")) $errors[] = 'Укажите пароль';

            if ($this->telephon != "") {
                if (!preg_match('/^[0-9]{10}$/', $this->telephon)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019';
            } else {
                $errors[] = 'Укажите контактный (мобильный) телефон';
            }

            if ($this->email == "" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "createSearchRequest") || ($typeOfValidation == "validateSearchRequest") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE))) $errors[] = 'Укажите e-mail';
            if ($this->email != "" && !preg_match("/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/", $this->email)) $errors[] = 'Укажите, пожалуйста, Ваш настоящий e-mail (указанный Вами e-mail не прошел проверку формата)';

            // Проверки для блока "Образование"
            if ($this->currentStatusEducation == "0" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше образование (текущий статус)';
            if ($this->almamater == "" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите учебное заведение';
            if (isset($this->almamater) && strlen($this->almamater) > 100) $errors[] = 'Слишком длинное название учебного заведения (используйте не более 100 символов)';
            if ($this->speciality == "" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите специальность';
            if (isset($this->speciality) && strlen($this->speciality) > 100) $errors[] = 'Слишком длинное название специальности (используйте не более 100 символов)';
            if ($this->kurs == "" && $this->currentStatusEducation == "сейчас учусь" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите курс обучения';
            if (isset($this->kurs) && strlen($this->kurs) > 30) $errors[] = 'Курс. Указана слишком длинная строка (используйте не более 30 символов)';
            if ($this->ochnoZaochno == "0" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите форму обучения (очная, заочная)';
            if ($this->yearOfEnd == "" && $this->currentStatusEducation == "закончил" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите год окончания учебного заведения';
            if ($this->yearOfEnd != "" && !preg_match("/^[12]{1}[0-9]{3}$/", $this->yearOfEnd)) $errors[] = 'Укажите год окончания учебного заведения в формате: "гггг". Например: 2007';

            // Проверки для блока "Работа"
            if ($this->statusWork == "0" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите статус занятости';
            if ($this->placeOfWork == "" && $this->statusWork == "работаю" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше место работы (название организации)';
            if (isset($this->placeOfWork) && strlen($this->placeOfWork) > 100) $errors[] = 'Слишком длинное наименование места работы (используйте не более 100 символов)';
            if ($this->workPosition == "" && $this->statusWork == "работаю" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Вашу должность';
            if (isset($this->workPosition) && strlen($this->workPosition) > 100) $errors[] = 'Слишком длинное название должности (используйте не более 100 символов)';

            // Проверки для блока "Коротко о себе"
            if (isset($this->regionOfBorn) && strlen($this->regionOfBorn) > 50) $errors[] = 'Слишком длинное наименование региона, в котором Вы родились (используйте не более 50 символов)';
            if (isset($this->cityOfBorn) && strlen($this->cityOfBorn) > 50) $errors[] = 'Слишком длинное наименование города, в котором Вы родились (используйте не более 50 символов)';

            // Проверки для блока "Социальные сети"
            if (strlen($this->vkontakte) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу Вконтакте (используйте не более 100 символов)';
            if (strlen($this->vkontakte) > 0 && !preg_match("/vk\.com/", $this->vkontakte)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу Вконтакте, либо оставьте поле пустым (ссылка должна содержать строчку "vk.com")';
            if (strlen($this->odnoklassniki) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Одноклассниках (используйте не более 100 символов)';
            if (strlen($this->odnoklassniki) > 0 && !preg_match("/www\.odnoklassniki\.ru\/profile\//", $this->odnoklassniki)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Одноклассниках, либо оставьте поле пустым (ссылка должна содержать строчку "www.odnoklassniki.ru/profile/")';
            if (strlen($this->facebook) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу на Facebook (используйте не более 100 символов)';
            if (strlen($this->facebook) > 0 && !preg_match("/www\.facebook\.com\/profile\.php/", $this->facebook)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу на Facebook, либо оставьте поле пустым (ссылка должна содержать строчку с "www.facebook.com/profile.php")';
            if (strlen($this->twitter) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Twitter (используйте не более 100 символов)';
            if (strlen($this->twitter) > 0 && !preg_match("/twitter\.com/", $this->twitter)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Twitter, либо оставьте поле пустым (ссылка должна содержать строчку "twitter.com")';

            // Проверки для блока "Параметры поиска"
            if ((($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $this->minCost)) $errors[] = 'Неправильный формат числа в поле минимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
            if ((($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $this->maxCost)) $errors[] = 'Неправильный формат числа в поле максимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
            if ((($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $this->pledge)) $errors[] = 'Неправильный формат числа в поле максимальной величины залога (проверьте: только числа, не более 8 символов)';
            if ((($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") && $this->minCost > $this->maxCost) $errors[] = 'Минимальная стоимость аренды не может быть больше, чем максимальная. Исправьте поля, в которых указаны Ваши требования к диапазону стоимости аренды';
            if ($this->withWho == "0" && $this->typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, как Вы собираетесь проживать в арендуемой недвижимости (с кем)';
            if ($this->children == "0" && $this->typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с детьми или без них';
            if ($this->animals == "0" && $this->typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с животными или без них';
            if ($this->termOfLease == "0" && (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите предполагаемый срок аренды';

            // Проверка согласия пользователя с лицензией
            if ($typeOfValidation == "registration" && $this->lic != "yes") $errors[] = 'Регистрация возможна только при согласии с условиями лицензионного соглашения'; //приняты ли правила

            return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
        }

        // Функция проверяет - залогинен ли пользователь сейчас (возвращает TRUE или FALSE).
        // И если пользователь залогинен, то обновляет его личные параметры в соответствии с указанными в БД (но не обновляет параметры поиска)
        // TODO: Оптимизировать код - делать только 1 запрос к БД с параметром идентификатор сессии и одновременно логин пользователя
        public function login()
        {
            // Если данная функция уже вызывалась на этой странице, то результат ее работы сохранен в приватной переменной, достаочно выдать его
            if ($this->isLoggedIn != "") return $this->isLoggedIn;

            // Если сессия еще не была запущена - запускаем.
            if (!isset($_SESSION)) {
                session_start();
            }

            // СНАЧАЛА ПРОВЕРЯЕМ СЕССИЮ ПОЛЬЗОВАТЕЛЯ. Если какая-то сесcия есть - проверим ее актуальность: если найдется пользователь у которого идентификатор последней сессии совпадет с этим - значит это он и есть
            if (isset($_SESSION['id'])) {

                // Получим из БД данные ($res) по пользователю с идентификатором сессии = $_SESSION['id']
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT * FROM users WHERE user_hash=?") === FALSE)
                    OR ($stmt->bind_param("s", $_SESSION['id']) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    $res = array();
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                }

                // Если нашли 1 строку в БД - значит все хорошо и пользователь является авторизованным.
                // Сохраним данные пользователя в свойствах объекта
                if (count($res) == 1) {

                    $idFromDB = $res[0]['id'];
                    $loginFromDB = $res[0]['login'];
                    $passwordFromDB = $res[0]['password'];

                    $this->writeParametersPersonal($res[0]);

                    // Если текущая сессия актуальна - добавим куки, чтобы после перезапуска браузера сессия не слетала
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
                }
            }

            // ЗАТЕМ ПРОВЕРЯЕМ КУКИ. Если сессия уже потеряла актуальность или не существовала
            if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) // смотрим куки, если cookie есть, то проверим их актуальность
            {

                // Получим из БД данные ($res) по пользователю с логином = $_COOKIE['login']
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT * FROM users WHERE login=?") === FALSE)
                    OR ($stmt->bind_param("s", $_COOKIE['login']) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    $res = array();
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $this->isLoggedIn = FALSE;
                    return FALSE;
                }

                // Сохраним данные пользователя в свойствах объекта
                if (count($res) == 1) {

                    $idFromDB = $res[0]['id'];
                    $loginFromDB = $res[0]['login'];
                    $passwordFromDB = $res[0]['password'];

                    if (md5($loginFromDB . $passwordFromDB) == $_COOKIE['password']) {

                        $this->writeParametersPersonal($res[0]);

                        // Обновим куки
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

                        setcookie("login", "", time() - 360000, '/');
                        setcookie("password", "", time() - 360000, '/');

                        $this->isLoggedIn = FALSE;
                        return FALSE;
                    }

                } else {

                    setcookie("login", "", time() - 360000, '/');
                    setcookie("password", "", time() - 360000, '/');

                    $this->isLoggedIn = FALSE;
                    return FALSE;
                }

            } else // Если сессия не актуальна и куки не существуют
            {
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
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT id, login, password FROM users WHERE login=?") === FALSE)
                    OR ($stmt->bind_param("s", $login) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    $res = array();
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $error[] = "Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.";
                    return $error;
                }

                // Если нашлась одна строка, значит такой юзер существует в БД
                if (count($res) == 1) {

                    $idFromDB = $res[0]['id'];
                    $loginFromDB = $res[0]['login'];
                    $passwordFromDB = $res[0]['password'];

                    if ($passwordFromDB == $password) // Cравниваем указанный пользователем пароль с паролем из БД
                    {
                        // Пишем логин и хэшированный пароль в cookie, также создаём переменную сессии
                        setcookie("login", $loginFromDB, time() + 60 * 60 * 24 * 7);
                        setcookie("password", md5($loginFromDB . $passwordFromDB), time() + 60 * 60 * 24 * 7);
                        $this->newSession($idFromDB);
                        $this->lastAct($idFromDB);

                        return $error;

                    } else //если пароли не совпали
                    {
                        $error[] = "Неверный пароль";
                        return $error;
                    }
                } else // Если такого пользователя не найдено в БД
                {
                    $error[] = "Неверный логин и пароль";
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
            $hash = md5($this->globFunc->generateCode(10));

            // Обновляем данные в БД по пользователю с id = $userId
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("UPDATE users SET user_hash=? WHERE id=?") === FALSE)
                OR ($stmt->bind_param("ss", $hash, $userId) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($res === 0)
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
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("UPDATE users SET last_act=? WHERE id=?") === FALSE)
                OR ($stmt->bind_param("ss", $tm, $userId) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($res === 0)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            }

        }

    }