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
        private $typeTenant = "";
        private $typeOwner = "";
        private $emailReg = "";
        private $user_hash = "";
        private $last_act = "";
        private $reg_date = "";
        private $favoritesPropertysId = array();
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

        // Записать в качестве параметров user-а значения, полученные через POST запрос
        public function writePOSTparameters()
        {
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
            if ($this->uploadedFoto == NULL) $this->uploadedFoto = array();
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
            if (isset($_POST['odnoklassniki'])) $this->odnoklassniki = htmlspecialchars($this->$_POST['odnoklassniki']);
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

            if ($this->email == "" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "createSearchRequest") || ($typeOfValidation == "validateSearchRequest") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true"))) $errors[] = 'Укажите e-mail';
            if ($this->email != "" && !preg_match("/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/", $this->email)) $errors[] = 'Укажите, пожалуйста, Ваш настоящий e-mail (указанный Вами e-mail не прошел проверку формата)';

            // Проверки для блока "Образование"
            if ($this->currentStatusEducation == "0" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше образование (текущий статус)';
            if ($this->almamater == "" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите учебное заведение';
            if (isset($this->almamater) && strlen($this->almamater) > 100) $errors[] = 'Слишком длинное название учебного заведения (используйте не более 100 символов)';
            if ($this->speciality == "" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите специальность';
            if (isset($this->speciality) && strlen($this->speciality) > 100) $errors[] = 'Слишком длинное название специальности (используйте не более 100 символов)';
            if ($this->kurs == "" && $this->currentStatusEducation == "сейчас учусь" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите курс обучения';
            if (isset($this->kurs) && strlen($this->kurs) > 30) $errors[] = 'Курс. Указана слишком длинная строка (используйте не более 30 символов)';
            if ($this->ochnoZaochno == "0" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите форму обучения (очная, заочная)';
            if ($this->yearOfEnd == "" && $this->currentStatusEducation == "закончил" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите год окончания учебного заведения';
            if ($this->yearOfEnd != "" && !preg_match("/^[12]{1}[0-9]{3}$/", $this->yearOfEnd)) $errors[] = 'Укажите год окончания учебного заведения в формате: "гггг". Например: 2007';

            // Проверки для блока "Работа"
            if ($this->statusWork == "0" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите статус занятости';
            if ($this->placeOfWork == "" && $this->statusWork == "работаю" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше место работы (название организации)';
            if (isset($this->placeOfWork) && strlen($this->placeOfWork) > 100) $errors[] = 'Слишком длинное наименование места работы (используйте не более 100 символов)';
            if ($this->workPosition == "" && $this->statusWork == "работаю" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Вашу должность';
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
            if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $this->minCost)) $errors[] = 'Неправильный формат числа в поле минимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
            if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $this->maxCost)) $errors[] = 'Неправильный формат числа в поле максимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
            if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $this->pledge)) $errors[] = 'Неправильный формат числа в поле максимальной величины залога (проверьте: только числа, не более 8 символов)';
            if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && $this->minCost > $this->maxCost) $errors[] = 'Минимальная стоимость аренды не может быть больше, чем максимальная. Исправьте поля, в которых указаны Ваши требования к диапазону стоимости аренды';
            if ($this->withWho == "0" && $this->typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, как Вы собираетесь проживать в арендуемой недвижимости (с кем)';
            if ($this->children == "0" && $this->typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с детьми или без них';
            if ($this->animals == "0" && $this->typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с животными или без них';
            if ($this->termOfLease == "0" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите предполагаемый срок аренды';

            // Проверка согласия пользователя с лицензией
            if ($typeOfValidation == "registration" && $this->lic != "yes") $errors[] = 'Регистрация возможна только при согласии с условиями лицензионного соглашения'; //приняты ли правила

            return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
        }

        // Является ли пользователь арендатором (то есть имеет действующий поисковый запрос или регистрируется в качестве арендатора)
        public function isTenant()
        {
            if ($this->typeTenant != "") {
                return $this->typeTenant;
            }

            // Если пользователь авторизован, то значение typeTenant будет записано в переменную $this->typeTenant из БД автоматически
            if ($this->login()) return $this->typeTenant;

            // Если пользователь еще только регистрируется, то возвращаем значение из get параметров
            if (isset($_GET['typeTenant'])) {
                $this->typeTenant = "true";
            } else {
                $this->typeTenant = "false";
            }
            if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {
                $this->typeTenant = "true";
            }
            return $this->typeTenant;
        }

        // Является ли пользователь собственником (то есть имеет хотя бы 1 объявление или регистрируется в качестве собственника)
        public function isOwner()
        {
            if ($this->typeOwner != "") {
                return $this->typeOwner;
            }

            // Если пользователь авторизован, то значение typeOwner будет записано в переменную $this->typeOwner из БД автоматически
            if ($this->login()) return $this->typeOwner;

            // Если пользователь еще только регистрируется, то возвращаем значение из get параметров
            if (isset($_GET['typeOwner'])) {
                $this->typeOwner = "true";
            } else {
                $this->typeOwner = "false";
            }
            if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {
                $this->typeOwner = "true";
            }
            return $this->typeOwner;
        }

        // Функция сохраняет текущие параметры пользователя (хранящиеся в данном объекте) в базу данных
        // $typeOfUser = "new" - режим сохранения для нового (регистрируемого пользователя)
        // $typeOfUser = "edit" - режим сохранения для редактируемых параметров (для существующего пользователя)
        public function saveToDB($typeOfUser = "new")
        {

            // Инициализируем массив для возвращения в качестве результата функции
            $error = array();

            // Корректируем дату дня рождения для того, чтобы сделать ее пригодной для сохранения в базу данных
            $birthdayDB = $this->globFunc->dateFromViewToDB($this->birthday);
            // Получаем текущее время для сохранения в качестве даты регистрации и даты последнего действия
            $tm = time();
            $last_act = $tm;
            $reg_date = $tm;
            // Сериализуем массив с избранными объявлениями
            $favoritesPropertysId = serialize($this->favoritesPropertysId);

            // Для простоты технической поддержки пользователей пойдем на небольшой риск с точки зрения безопасности и будем хранить пароли пользователей на сервере в БД без соли и шифрования
            /*$salt = mt_rand(100, 999);
              $password = md5(md5($password) . $salt);*/

            // Формируем запрос в зависимости от того: сохраняем данные нового пользователя (при регистрации) или редактированные параметры существующего пользователя
            if ($typeOfUser == "new") {
                $query = "INSERT INTO users (typeTenant,typeOwner,name,secondName,surname,sex,nationality,birthday,login,password,telephon,emailReg,email,currentStatusEducation,almamater,speciality,kurs,ochnoZaochno,yearOfEnd,statusWork,placeOfWork,workPosition,regionOfBorn,cityOfBorn,shortlyAboutMe,vkontakte,odnoklassniki,facebook,twitter,lic,last_act,reg_date,favoritesPropertysId) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $typeForParams = "sssssssssssssssssssssssssssssssiib";
                $paramsArr = array(&$this->typeTenant, &$this->typeOwner, &$this->name, &$this->secondName, &$this->surname, &$this->sex, &$this->nationality, &$birthdayDB, &$this->login, &$this->password, &$this->telephon, &$this->email, &$this->email, &$this->currentStatusEducation, &$this->almamater, &$this->speciality, &$this->kurs, &$this->ochnoZaochno, &$this->yearOfEnd, &$this->statusWork, &$this->placeOfWork, &$this->workPosition, &$this->regionOfBorn, &$this->cityOfBorn, &$this->shortlyAboutMe, &$this->vkontakte, &$this->odnoklassniki, &$this->facebook, &$this->twitter, &$this->lic, &$last_act, &$reg_date, &$favoritesPropertysId);
            }
            if ($typeOfUser == "edit") {
                // TODO: реализовать для режима edit
            }

            // Пишем данные нового пользователя в БД. При успехе в $res сохраняем TRUE, иначе - FALSE
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare($query) === FALSE)
                OR ($stmt->bind_param($typeForParams, $paramsArr) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($res === 0)
                OR ($stmt->close() === FALSE)
            ) {
                $res = FALSE;
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            } else {
                $res = TRUE;
            }

            // Если сохранение Личных данных пользователя прошло успешно, то
            if ($res == TRUE) {

                /******* Переносим информацию о фотографиях пользователя в таблицу для постоянного хранения, лишние файлы удаляем *******/

                if (is_array($this->uploadedFoto) && count($this->uploadedFoto) != 0) {
                    // Узнаем id пользователя - необходимо при сохранении информации о фотке в постоянную базу
                    if ($this->id == "") {
                        // Получим из БД данные ($res) по пользователю с логином = $login
                        $stmt = $this->DBlink->stmt_init();
                        if (($stmt->prepare("SELECT id FROM users WHERE login=?") === FALSE)
                            OR ($stmt->bind_param("s", $this->login) === FALSE)
                            OR ($stmt->execute() === FALSE)
                            OR (($res = $stmt->get_result()) === FALSE)
                            OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                            OR (count($res) === 0)
                            OR ($stmt->close() === FALSE)
                        ) {
                            // Для того, чтобы при сохранении фотографий и условий поиска не напороться на неожиданное поведение, присвоим идентификатору пользователя 0, таким образом любая выборка из БД с таким id даст нулевые результаты
                            $this->id = 0;
                            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                        } else {
                            $this->id = $res[0]['id'];
                        }
                    }

                    // Соберем условие WHERE для 2-х SQL запросов к БД: один для получения данных из таблицы tempFotos по всем фотографиям, содержащимся в $uploadedFoto (то есть которые пользователь хочет сохранить на постоянное хранение), второй - для запроса тоже к таблице tempFotos, но с целью выявить записи, соответствующие данной сессии взаимодействия с пользователем (совпадает fileUploadId), но не содержащиеся в $uploadedFoto (пользователь удалил соответствующие фотографии на клиенте при редактировании списка фотографий). Второй запрос нужен для того, чтобы выявить ненужные файлы фотографий, которые хранятся на сервере, и удалить их.
                    // TODO: лень сделать как надо - через подготовленные запросы - prepare и bind_param. Как-нибудь потом
                    $strWHEREforLife = " (";
                    $strWHEREforDead = " (";
                    for ($i = 0; $i < count($this->uploadedFoto); $i++) {

                        $strWHEREforLife .= " id = '" . $this->uploadedFoto[$i]['fotoid'] . "'";
                        $strWHEREforDead .= " id != '" . $this->uploadedFoto[$i]['fotoid'] . "'";

                        if ($i < count($this->uploadedFoto) - 1) {
                            $strWHEREforLife .= " OR";
                            $strWHEREforDead .= " AND";
                        }

                    }
                    $strWHEREforLife .= " )";
                    $strWHEREforDead .= " ) AND (fileUploadId = '" . $this->fileUploadId . "')";

                    // Получаем данные по фотографиям, предназначенным для переноса на постоянное хранение
                    $fotoForLifeArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив данных по конкретной фотографии
                    if ($strWHEREforLife != "") {
                        // Получим из БД данные ($res) по фотографиям с указанными id
                        $res = $this->DBlink->query("SELECT * FROM tempFotos WHERE " . $strWHEREforLife);
                        if (($this->DBlink->errno)
                            OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                        ) {
                            $res = array();
                            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                        }
                    }

                    // Дополним данные, полученные из БД ($fotoForLifeArr) актуальными сведениями с клиентского места о статусе каждой фотографии (основная или нет)
                    $primaryFotoExists = 0; // Инициализируем переменную, по которой после прохода по всем фотографиям, полученным в форме, сможем сказать была ли указана пользователем основная фотка (число) или нет (0)
                    for ($i = 0; $i < count($fotoForLifeArr); $i++) {
                        // В массиве $uploadedFoto также содержится актуальная информация по всем статусам фотографий, но легче получить id основной фотки из формы, а не из этого массива
                        if ($fotoForLifeArr[$i]['id'] == $this->primaryFotoId) {
                            $fotoForLifeArr[$i]['status'] = 'основная';
                            $primaryFotoExists++;
                        } else {
                            $fotoForLifeArr[$i]['status'] = '';
                        }

                        // Подготовим данные о пути к каталогу хранения фотографии в вид, пригодный для перезаписи в БД
                        $fotoForLifeArr[$i]['folder'] = str_replace('\\', '\\\\', $fotoForLifeArr[$i]['folder']); // Переменная folder уже содержит в себе один или несколько '\', но для того, чтобы при сохранении в БД не возникло проблем, к нему нужно добавить еще один символ '\', в этом случае mysql будет воспринимать "\\" как один знак "\" и не будет считать его служебгым символом
                    }

                    // Если пользователь не указал основное фото, то укажем первую попавшуюся фотографию в качестве основной
                    if ($primaryFotoExists == 0 && isset($fotoForLifeArr[0])) $fotoForLifeArr[0]['status'] = 'основная';

                    // Сформируем запрос к БД (таблица userFotos) для записи данных о фотографиях
                    $strINSERTvalues = "";
                    for ($i = 0; $i < count($fotoForLifeArr); $i++) {
                        $strINSERTvalues .= "('" . $fotoForLifeArr[$i]['id'] . "','" . $fotoForLifeArr[$i]['folder'] . "','" . $fotoForLifeArr[$i]['filename'] . "','" . $fotoForLifeArr[$i]['extension'] . "','" . $fotoForLifeArr[$i]['filesizeMb'] . "','" . $this->id . "','" . $fotoForLifeArr[$i]['status'] . "')";
                        if ($i < count($fotoForLifeArr) - 1) $strINSERTvalues .= ",";
                    }
                    // Сохраняем на постоянное хранение информацию о загруженных пользователем фотографиях
                    $res = $this->DBlink->query("INSERT INTO userFotos (id, folder, filename, extension, filesizeMb, userId, status) VALUES " . $strINSERTvalues);
                    if (($this->DBlink->errno)
                        OR (($res = $res->affected_rows) === -1)
                        OR ($res === 0)
                    ) {
                        $res = FALSE;
                        // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    }
                }









                // В любом случае проверяем есть ли в таблице tempFotos данные о фотографиях, загруженных пользователем во время этой сессии взаимодействия.
                if (!isset($strWHEREforDead)) $strWHEREforDead = "fileUploadId = '" . $this->fileUploadId . "'";
                // Выполним запрос к таблице tempFotos с целью выявить записи, соответствующие данной сессии взаимодействия с пользователем (совпадает fileUploadId), но не содержащиеся в $uploadedFoto (пользователь удалил соответствующие фотографии на клиенте при редактировании списка фотографий). Этот запрос нужен для того, чтобы выявить ненужные файлы фотографий, которые хранятся на сервере, и удалить их.
                $tempFotosForDead = getResultSQLSelect($this->DBlink, "SELECT * FROM tempFotos WHERE " . $strWHEREforDead);
                for ($i = 0; $i < count($tempFotosForDead); $i++) {
                    // Удаляем файлы, которые пользователь на клиенте пометил на удаление (нажал на кнопку/ссылку удалить)
                    // TODO: сделать сохранение статусов отработки команд по удалению фоток в лог файл. Каждый unlink выдает true, если все хорошо и false, если плохо
                    unlink($tempFotosForDead['folder'] . '\\small\\' . $tempFotosForDead['id'] . "." . $tempFotosForDead['extension']);
                    unlink($tempFotosForDead['folder'] . '\\middle\\' . $tempFotosForDead['id'] . "." . $tempFotosForDead['extension']);
                    unlink($tempFotosForDead['folder'] . '\\big\\' . $tempFotosForDead['id'] . "." . $tempFotosForDead['extension']);
                }

                // Удаляем все записи о фотках в таблице для временного хранения данных по данной сессии
                $rez = mysqli_query($this->DBlink, mysqli_real_escape_string($this->DBlink, "DELETE FROM tempFotos WHERE fileUploadId = '" . $this->fileUploadId . "'"));
                if ($rez == FALSE) {
                    // TODO: сохраняем в лог ошибку обращения к БД
                }

                /******* Сохраняем поисковый запрос, если он был указан пользователем *******/

                // Преобразование формата инфы об искомом кол-ве комнат и районах, так как MySQL не умеет хранить массивы
                $amountOfRoomsSerialized = serialize($this->amountOfRooms);
                $districtSerialized = serialize($this->district);

                // Готовим пустой массив с идентификаторами объектов, которыми заинтересовался пользователь - на будущее
                $interestingPropertysId = array();
                $interestingPropertysId = serialize($interestingPropertysId);

                // Непосредственное сохранение данных о поисковом запросе
                if ($typeTenant == "true") {
                    $rez = mysqli_query($this->DBlink, mysqli_real_escape_string($this->DBlink, "INSERT INTO searchRequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, minCost, maxCost, pledge, prepayment, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, termOfLease, additionalDescriptionOfSearch, interestingPropertysId) VALUES ('" . $userId . "','" . $typeOfObject . "','" . $amountOfRoomsSerialized . "','" . $adjacentRooms . "','" . $floor . "','" . $minCost . "','" . $maxCost . "','" . $pledge . "','" . $prepayment . "','" . $districtSerialized . "','" . $withWho . "','" . $linksToFriends . "','" . $children . "','" . $howManyChildren . "','" . $animals . "','" . $howManyAnimals . "','" . $termOfLease . "','" . $additionalDescriptionOfSearch . "','" . $interestingPropertysId . "')")); // Поисковый запрос пользователя сохраняется в специальной таблице
                    if ($rez == FALSE) {
                        // TODO: сохраняем в лог ошибку обращения к БД
                    }
                }


                /******* Авторизовываем пользователя *******/
                $error = enter($this->DBlink);
                if (count($error) == 0) //если нет ошибок, отправляем уже авторизованного пользователя на страницу успешной регистрации
                {
                    header('Location: successfullRegistration.php'); //после успешной регистрации - переходим на соответствующую страницу
                } else {
                    // TODO:что-то нужно делать в случае, если возникли ошибки при авторизации во время регистрации - как минимум вывести их текст во всплывающем окошке
                }

            } else { // Если сохранить личные данные пользователя в БД не удалось

                $errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку регистрации';
                // Сохранении данных в БД не прошло - пользователь не зарегистрирован
            }

            return $errors;
        }

        // Функция проверяет - залогинен ли пользователь сейчас (взвращает TRUE или FALSE). И если залогинен, то обновляет его личные параметры в соответствии с указанными в БД
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

        // Функция для авторизации (входа) пользователя на сайте
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

?>