<?php

    class Property
    {

        public $typeOfObject = "0";
        public $dateOfEntry = "";
        public $termOfLease = "0";
        public $dateOfCheckOut = "";
        public $amountOfRooms = "0";
        public $adjacentRooms = "0";
        public $amountOfAdjacentRooms = "0";
        public $typeOfBathrooms = "0";
        public $typeOfBalcony = "0";
        public $balconyGlazed = "0";
        public $roomSpace = "";
        public $totalArea = "";
        public $livingSpace = "";
        public $kitchenSpace = "";
        public $floor = "";
        public $totalAmountFloor = "";
        public $numberOfFloor = "";
        public $concierge = "0";
        public $intercom = "0";
        public $parking = "0";
        public $city = "Екатеринбург";
        public $district = "0";
        public $coordX = "";
        public $coordY = "";
        public $address = "";
        public $apartmentNumber = "";
        public $subwayStation = "0";
        public $distanceToMetroStation = "";
        public $currency = "0";
        public $costOfRenting = "";
        public $utilities = "0";
        public $costInSummer = "";
        public $costInWinter = "";
        public $electricPower = "0";
        public $bail = "0";
        public $bailCost = "";
        public $prepayment = "0";
        public $compensationMoney = "";
        public $compensationPercent = "";
        public $repair = "0";
        public $furnish = "0";
        public $windows = "0";
        public $internet = "0";
        public $telephoneLine = "0";
        public $cableTV = "0";
        public $furnitureInLivingArea = array();
        public $furnitureInLivingAreaExtra = "";
        public $furnitureInKitchen = array();
        public $furnitureInKitchenExtra = "";
        public $appliances = array();
        public $appliancesExtra = "";
        public $sexOfTenant = "";
        public $relations = "";
        public $children = "0";
        public $animals = "0";
        public $contactTelephonNumber = "";
        public $timeForRingBegin = "0";
        public $timeForRingEnd = "0";
        public $checking = "0";
        public $responsibility = "";
        public $comment = "";

        public $realCostOfRenting = "";
        public $last_act = "";
        public $reg_date = "";
        public $status = "";
        public $visibleUsersId = array();
        public $schemeOfWork = "";
        public $id = "";
        public $userId = "";

        public $fileUploadId = "";
        public $uploadedFoto = array(); // В переменной будет храниться информация о загруженных фотографиях. Представляет собой массив ассоциированных массивов
        public $primaryFotoId = "";

        private $DBlink = FALSE; // Переменная для хранения объекта соединения с базой данных
        private $globFunc = FALSE; // Переменная для хранения глобальных функций

        // КОНСТРУКТОР
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

        }

        // ДЕСТРУКТОР
        public function __destruct()
        {

        }

        // Функция сохраняет текущие параметры объекта недвижимости в БД
        // $typeOfProperty = "new" - режим сохранения для нового объекта недвижимости
        // $typeOfProperty = "edit" - режим сохранения для редактируемых параметров объекта недвижимости
        // Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
        public function saveParametersToDB($typeOfProperty) {

            // Корректируем даты для того, чтобы сделать их пригодными для сохранения в базу данных
            $dateOfEntryForDB = $this->globFunc->dateFromViewToDB($this->dateOfEntry);
            $dateOfCheckOutForDB = $this->globFunc->dateFromViewToDB($this->dateOfCheckOut);

            // Для хранения массивов в БД, их необходимо сериализовать
            $furnitureInLivingAreaSerialized = serialize($this->furnitureInLivingArea);
            $furnitureInKitchenSerialized = serialize($this->furnitureInKitchen);
            $appliancesSerialized = serialize($this->appliances);
            $sexOfTenantImploded = implode("_", $this->sexOfTenant);
            $relationsImploded = implode("_", $this->relations);

            // Проверяем в какой валюте сохраняется стоимость аренды, формируем переменную realCostOfRenting
            if ($this->currency == 'руб.') $realCostOfRenting = $this->costOfRenting;
            if ($this->currency != 'руб.') {
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT value FROM currencies WHERE name=?") === FALSE)
                    OR ($stmt->bind_param("s", $this->currency) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

                $realCostOfRenting = $this->costOfRenting * $res[0]['value'];
            }

            $tm = time();
            $last_act = $tm; // время последнего редактирования объявления
            $reg_date = $tm; // время регистрации ("рождения") объявления

            // Пишем данные объекта недвижимости в БД.
            // Код для сохранения данных разный: для нового объявления и при редактировании параметров существующего объявления
            if ($typeOfProperty == "new") {
                //TODO: переделать как надо сохранение
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

            if ($typeOfProperty == "edit") {
                //TODO: переделать как надо сохранение
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("UPDATE users SET name=?, secondName=?, surname=?, sex=?, nationality=?, birthday=?, password=?, telephon=?, email=?, currentStatusEducation=?, almamater=?, speciality=?, kurs=?, ochnoZaochno=?, yearOfEnd=?, statusWork=?, placeOfWork=?, workPosition=?, regionOfBorn=?, cityOfBorn=?, shortlyAboutMe=?, vkontakte=?, odnoklassniki=?, facebook=?, twitter=?, last_act=? WHERE id=?") === FALSE)
                    OR ($stmt->bind_param("sssssssssssssssssssssssssis", $this->name, $this->secondName, $this->surname, $this->sex, $this->nationality, $birthdayDB, $this->password, $this->telephon, $this->email, $this->currentStatusEducation, $this->almamater, $this->speciality, $this->kurs, $this->ochnoZaochno, $this->yearOfEnd, $this->statusWork, $this->placeOfWork, $this->workPosition, $this->regionOfBorn, $this->cityOfBorn, $this->shortlyAboutMe, $this->vkontakte, $this->odnoklassniki, $this->facebook, $this->twitter, $last_act, $this->id) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->affected_rows) === -1)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

                return TRUE;
            }

            // Объявление не является ни новым, ни существующим - видимо какая-то ошибка была допущена при передаче параметров методу
            return FALSE;

        }

        // Записать в качестве параметров объекта недвижимости значения, полученные через POST запрос
        public function writeParametersFromPOST() {

            if (isset($_POST['typeOfObject'])) $this->typeOfObject = htmlspecialchars($_POST['typeOfObject']);
            if (isset($_POST['dateOfEntry'])) $this->dateOfEntry = htmlspecialchars($_POST['dateOfEntry']);
            if (isset($_POST['termOfLease'])) $this->termOfLease = htmlspecialchars($_POST['termOfLease']);
            if (isset($_POST['dateOfCheckOut'])) $this->dateOfCheckOut = htmlspecialchars($_POST['dateOfCheckOut']);
            if (isset($_POST['amountOfRooms'])) $this->amountOfRooms = htmlspecialchars($_POST['amountOfRooms']);
            if (isset($_POST['adjacentRooms'])) $this->adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
            if (isset($_POST['amountOfAdjacentRooms'])) $this->amountOfAdjacentRooms = htmlspecialchars($_POST['amountOfAdjacentRooms']);
            if (isset($_POST['typeOfBathrooms'])) $this->typeOfBathrooms = htmlspecialchars($_POST['typeOfBathrooms']);
            if (isset($_POST['typeOfBalcony'])) $this->typeOfBalcony = htmlspecialchars($_POST['typeOfBalcony']);
            if (isset($_POST['balconyGlazed'])) $this->balconyGlazed = htmlspecialchars($_POST['balconyGlazed']);
            if (isset($_POST['roomSpace'])) $this->roomSpace = htmlspecialchars($_POST['roomSpace']);
            if (isset($_POST['totalArea'])) $this->totalArea = htmlspecialchars($_POST['totalArea']);
            if (isset($_POST['livingSpace'])) $this->livingSpace = htmlspecialchars($_POST['livingSpace']);
            if (isset($_POST['kitchenSpace'])) $this->kitchenSpace = htmlspecialchars($_POST['kitchenSpace']);
            if (isset($_POST['floor'])) $this->floor = htmlspecialchars($_POST['floor']);
            if (isset($_POST['totalAmountFloor'])) $this->totalAmountFloor = htmlspecialchars($_POST['totalAmountFloor']);
            if (isset($_POST['numberOfFloor'])) $this->numberOfFloor = htmlspecialchars($_POST['numberOfFloor']);
            if (isset($_POST['concierge'])) $this->concierge = htmlspecialchars($_POST['concierge']);
            if (isset($_POST['intercom'])) $this->intercom = htmlspecialchars($_POST['intercom']);
            if (isset($_POST['parking'])) $this->parking = htmlspecialchars($_POST['parking']);
            if (isset($_POST['district'])) $this->district = htmlspecialchars($_POST['district']);
            if (isset($_POST['coordX'])) $this->coordX = htmlspecialchars($_POST['coordX']);
            if (isset($_POST['coordY'])) $this->coordY = htmlspecialchars($_POST['coordY']);
            if (isset($_POST['address'])) $this->address = htmlspecialchars($_POST['address']);
            if (isset($_POST['apartmentNumber'])) $this->apartmentNumber = htmlspecialchars($_POST['apartmentNumber']);
            if (isset($_POST['subwayStation'])) $this->subwayStation = htmlspecialchars($_POST['subwayStation']);
            if (isset($_POST['distanceToMetroStation'])) $this->distanceToMetroStation = htmlspecialchars($_POST['distanceToMetroStation']);
            if (isset($_POST['currency'])) $this->currency = htmlspecialchars($_POST['currency']);
            if (isset($_POST['costOfRenting'])) $this->costOfRenting = htmlspecialchars($_POST['costOfRenting']);
            if (isset($_POST['utilities'])) $this->utilities = htmlspecialchars($_POST['utilities']);
            if (isset($_POST['costInSummer'])) $this->costInSummer = htmlspecialchars($_POST['costInSummer']);
            if (isset($_POST['costInWinter'])) $this->costInWinter = htmlspecialchars($_POST['costInWinter']);
            if (isset($_POST['electricPower'])) $this->electricPower = htmlspecialchars($_POST['electricPower']);
            if (isset($_POST['bail'])) $this->bail = htmlspecialchars($_POST['bail']);
            if (isset($_POST['bailCost'])) $this->bailCost = htmlspecialchars($_POST['bailCost']);
            if (isset($_POST['prepayment'])) $this->prepayment = htmlspecialchars($_POST['prepayment']);
            if (isset($_POST['compensationMoney'])) $this->compensationMoney = htmlspecialchars($_POST['compensationMoney']);
            if (isset($_POST['compensationPercent'])) $this->compensationPercent = htmlspecialchars($_POST['compensationPercent']);
            if (isset($_POST['repair'])) $this->repair = htmlspecialchars($_POST['repair']);
            if (isset($_POST['furnish'])) $this->furnish = htmlspecialchars($_POST['furnish']);
            if (isset($_POST['windows'])) $this->windows = htmlspecialchars($_POST['windows']);
            if (isset($_POST['internet'])) $this->internet = htmlspecialchars($_POST['internet']);
            if (isset($_POST['telephoneLine'])) $this->telephoneLine = htmlspecialchars($_POST['telephoneLine']);
            if (isset($_POST['cableTV'])) $this->cableTV = htmlspecialchars($_POST['cableTV']);
            if (isset($_POST['furnitureInLivingArea'])) $this->furnitureInLivingArea = $_POST['furnitureInLivingArea'];
            if (isset($_POST['furnitureInLivingAreaExtra'])) $this->furnitureInLivingAreaExtra = htmlspecialchars($_POST['furnitureInLivingAreaExtra']);
            if (isset($_POST['furnitureInKitchen'])) $this->furnitureInKitchen = $_POST['furnitureInKitchen'];
            if (isset($_POST['furnitureInKitchenExtra'])) $this->furnitureInKitchenExtra = htmlspecialchars($_POST['furnitureInKitchenExtra']);
            if (isset($_POST['appliances'])) $this->appliances = $_POST['appliances'];
            if (isset($_POST['appliancesExtra'])) $this->appliancesExtra = htmlspecialchars($_POST['appliancesExtra']);
            if (isset($_POST['sexOfTenant'])) $this->sexOfTenant = $_POST['sexOfTenant'];
            if (isset($_POST['relations'])) $this->relations = $_POST['relations'];
            if (isset($_POST['children'])) $this->children = htmlspecialchars($_POST['children']);
            if (isset($_POST['animals'])) $this->animals = htmlspecialchars($_POST['animals']);
            if (isset($_POST['contactTelephonNumber'])) $this->contactTelephonNumber = htmlspecialchars($_POST['contactTelephonNumber']);
            if (isset($_POST['timeForRingBegin'])) $this->timeForRingBegin = htmlspecialchars($_POST['timeForRingBegin']);
            if (isset($_POST['timeForRingEnd'])) $this->timeForRingEnd = htmlspecialchars($_POST['timeForRingEnd']);
            if (isset($_POST['checking'])) $this->checking = htmlspecialchars($_POST['checking']);
            if (isset($_POST['responsibility'])) $this->responsibility = htmlspecialchars($_POST['responsibility']);
            if (isset($_POST['comment'])) $this->comment = htmlspecialchars($_POST['comment']);

            if (isset($_POST['fileUploadId'])) $this->fileUploadId = $_POST['fileUploadId'];
            if (isset($_POST['uploadedFoto'])) $this->uploadedFoto = json_decode($_POST['uploadedFoto'], TRUE); // Массив объектов со сведениями о загруженных фотографиях сериализуется в JSON формат на клиенте и передается как содержимое атрибута value одного единственного INPUT hidden
            if (isset($_POST['primaryFotoRadioButton'])) $this->primaryFotoId = htmlspecialchars($_POST['primaryFotoRadioButton']);

        }

        // $typeOfValidation = newAdvert - режим первичной (для нового объявления) проверки указанных пользователем параметров объекта недвижимости
        // $typeOfValidation = editAdvert - режим вторичной (при редактировании уже существующего объявления) проверки указанных пользователем параметров объекта недвижимости
        function isAdvertCorrect($typeOfValidation)
        {

            // Подготовим массив для сохранения сообщений об ошибках
            $errors = array();

            // Проверяем переменные
            if ($this->typeOfObject == "0") $errors[] = 'Укажите тип объекта';
            if ($this->dateOfEntry == "") $errors[] = 'Укажите с какого числа арендатору можно въезжать в вашу недвижимость';
            if ($this->dateOfEntry != "") {
                if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $this->dateOfEntry)) $errors[] = 'Неправильный формат даты въезда для арендатора, должен быть: дд.мм.гггг';
                if (substr($this->dateOfEntry, 0, 2) < "01" || substr($this->dateOfEntry, 0, 2) > "31") $errors[] = 'Проверьте число даты въезда (допустимо от 01 до 31)';
                if (substr($this->dateOfEntry, 3, 2) < "01" || substr($this->dateOfEntry, 3, 2) > "12") $errors[] = 'Проверьте месяц даты въезда (допустимо от 01 до 12)';
                if (substr($this->dateOfEntry, 6, 4) < "1000" || substr($this->dateOfEntry, 6, 4) > "9999") $errors[] = 'Проверьте год даты въезда (допустимо от 1000 до 9999)';
            }
            if ($this->termOfLease == "0") $errors[] = 'Укажите на какой срок сдается недвижимость';
            if ($this->dateOfCheckOut == "" && $this->termOfLease != "0" && $this->termOfLease != "длительный срок") $errors[] = 'Укажите крайний срок выезда для арендатора(ов)';
            if ($this->dateOfCheckOut != "") {
                if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $this->dateOfCheckOut)) $errors[] = 'Неправильный формат крайней даты выезда для арендатора, должен быть: дд.мм.гггг';
                if (substr($this->dateOfCheckOut, 0, 2) < "01" || substr($this->dateOfCheckOut, 0, 2) > "31") $errors[] = 'Проверьте число даты выезда (допустимо от 01 до 31)';
                if (substr($this->dateOfCheckOut, 3, 2) < "01" || substr($this->dateOfCheckOut, 3, 2) > "12") $errors[] = 'Проверьте месяц даты выезда (допустимо от 01 до 12)';
                if (substr($this->dateOfCheckOut, 6, 4) < "1000" || substr($this->dateOfCheckOut, 6, 4) > "9999") $errors[] = 'Проверьте год даты выезда (допустимо от 1000 до 9999)';
            }

            // Проверяем наличие хотя бы 1 фотографии объекта недвижимости
            if ($typeOfValidation == "newAdvert" && $this->fileUploadId != "") {
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT * FROM tempFotos WHERE fileuploadid=?") === FALSE)
                    OR ($stmt->bind_param("s", $this->fileUploadId) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
                    return $errors;
                } else {
                    if (!is_array($res) || count($res) == 0) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений';
                }
            }
            if ($typeOfValidation == "editAdvert") // Эта ветка выполняется, если валидации производятся при попытке редактирования параметров объекта недвижимости
            {
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT * FROM propertyFotos WHERE propertyId=?") === FALSE)
                    OR ($stmt->bind_param("s", $this->id) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res1 = $stmt->get_result()) === FALSE)
                    OR (($res1 = $res1->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
                    return $errors;
                }

                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT * FROM tempFotos WHERE fileuploadid=?") === FALSE)
                    OR ($stmt->bind_param("s", $this->fileUploadId) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res2 = $stmt->get_result()) === FALSE)
                    OR (($res2 = $res2->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
                    return $errors;
                }

                if (!is_array($res1) || !is_array($res2) || (count($res1) == 0 && count($res2) == 0)) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений'; // проверка на хотя бы 1 фотку
            }
            if ($this->fileUploadId == "") $errors[] = 'Перезагрузите браузер, пожалуйста: возникла ошибка при формировании формы для загрузки фотографий';

            if ($this->amountOfRooms == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите количество комнат в квартире, доме';
            if ($this->adjacentRooms == "0" && $this->amountOfRooms != "0" && $this->amountOfRooms != "1") $errors[] = 'Укажите: есть ли смежные комнаты в сдаваемом объекте недвижимости';
            if ($this->amountOfAdjacentRooms == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж" && $this->adjacentRooms != "0" && $this->adjacentRooms != "нет" && $this->amountOfRooms != "0" && $this->amountOfRooms != "1" && $this->amountOfRooms != "2") $errors[] = 'Укажите количество смежных комнат';
            if ($this->amountOfAdjacentRooms > $this->amountOfRooms && $this->typeOfObject != "0" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж" && $this->adjacentRooms != "0" && $this->adjacentRooms != "нет" && $this->amountOfRooms != "0" && $this->amountOfRooms != "1" && $this->amountOfRooms != "2") $errors[] = 'Исправьте: количество смежных комнат не может быть больше общего количества комнат';
            if ($this->typeOfBathrooms == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите тип санузла';
            if ($this->typeOfBalcony == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: есть ли балкон, лоджия или эркер в сдаваемом объекте недвижимости';
            if ($this->balconyGlazed == "0" && $this->typeOfBalcony != "0" && $this->typeOfBalcony != "нет" && $this->typeOfBalcony != "эркер" && $this->typeOfBalcony != "2 эркера и более") $errors[] = 'Укажите остекление балкона/лоджии';
            if ($this->roomSpace == "" && $this->typeOfObject != "0" && $this->typeOfObject != "квартира" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите площадь комнаты';
            if ($this->roomSpace != "") {
                if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->roomSpace)) $errors[] = 'Неправильный формат для площади комнаты, используйте только цифры и точку, например: 16.55';
            }
            if ($this->totalArea == "" && $this->typeOfObject != "0" && $this->typeOfObject != "комната") $errors[] = 'Укажите общую площадь';
            if ($this->totalArea != "") {
                if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->totalArea)) $errors[] = 'Неправильный формат для общей площади, используйте только цифры и точку, например: 86.55';
            }
            if ($this->livingSpace == "" && $this->typeOfObject != "0" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж") $errors[] = 'Укажите жилую площадь';
            if ($this->livingSpace != "") {
                if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->livingSpace)) $errors[] = 'Неправильный формат для жилой площади, используйте только цифры и точку, например: 86.55';
            }
            if ($this->kitchenSpace == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите площадь кухни';
            if ($this->kitchenSpace != "") {
                if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->kitchenSpace)) $errors[] = 'Неправильный формат для площади кухни, используйте только цифры и точку, например: 86.55';
            }
            if ($this->floor == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите этаж, на котором расположена квартира, комната';
            if ($this->floor != "") {
                if (!preg_match('/^\d{0,3}$/', $this->floor)) $errors[] = 'Неправильный формат для этажа, на котором расположена квартира, комната: должно быть не более 3 цифр';
            }
            if ($this->totalAmountFloor == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите количество этажей в доме';
            if ($this->totalAmountFloor != "") {
                if (!preg_match('/^\d{0,3}$/', $this->totalAmountFloor)) $errors[] = 'Неправильный формат для количества этажей: должно быть не более 3 цифр';
            }
            if ($this->totalAmountFloor != "" && $this->floor != "" && $this->floor > $this->totalAmountFloor) $errors[] = 'Общее количество этажей в доме не может быть меньше этажа, на котором расположена Ваше недвижимость';
            if ($this->numberOfFloor == "" && $this->typeOfObject != "0" && $this->typeOfObject != "квартира" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж") $errors[] = 'Укажите количество этажей в доме';
            if ($this->numberOfFloor != "") {
                if (!preg_match('/^\d{0,2}$/', $this->numberOfFloor)) $errors[] = 'Неправильный формат для количества этажей: должно быть не более 2 цифр';
            }
            if ($this->concierge == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: есть ли в доме консьерж';
            if ($this->intercom == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие домофона';
            if ($this->parking == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие и тип парковки во дворе';

            if ($this->city != "Екатеринбург") $errors[] = 'Укажите в качестве города местонахождения Екатеринбург';
            if ($this->district == "0") $errors[] = 'Укажите район';
            if ($this->coordX == "" || $this->coordY == "") $errors[] = 'Укажите улицу и номер дома, затем нажмите кнопку "Проверить адрес"';
            if ($this->coordX != "" && $this->coordY != "") {
                if (!preg_match('/^\d{0,3}\.\d{0,10}$/', $this->coordX) || !preg_match('/^\d{0,3}\.\d{0,10}$/', $this->coordY)) $errors[] = 'Убедитесь, что на карте метка указывает на Ваш дом';
            }
            if ($this->address == "") $errors[] = 'Укажите улицу и номер дома';
            if (strlen($this->address) > 60) $errors[] = 'Указан слишком длинный адрес (используйте не более 60 символов)';
            if ($this->apartmentNumber == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите номер квартиры';
            if (strlen($this->apartmentNumber) > 20) $errors[] = 'Указан слишком длинный номер квартиры (используйте не более 20 символов)';

            // Убеждаемся что данный пользователь еще не публиковал объявлений по этому адресу. Не стоит позволять публиковать несколько разных объявлений одному человеку с привязкой к одному и тому же адресу
            if ($typeOfValidation == "newAdvert") {
                $stmt = $this->DBlink->stmt_init();
                if (($stmt->prepare("SELECT * FROM property WHERE (address=? OR (coordX=? AND coordY=?)) AND apartmentNumber=?") === FALSE)
                    OR ($stmt->bind_param("ssss", $this->address, $this->coordX, $this->coordY, $this->apartmentNumber) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->get_result()) === FALSE)
                    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    $errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
                    return $errors;
                } else {
                    if (!is_array($res)) {
                        $errors[] = 'К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.';
                        return $errors;
                    }
                    if (count($res) != 0) {
                        if ($res[0]['apartmentNumber'] != "") $errors[] = 'Вы уже завели ранее объявление по данному адресу с таким же номером квартиры. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
                        if ($res[0]['apartmentNumber'] == "") $errors[] = 'Вы уже завели ранее объявление по данному адресу. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
                    }
                }
            }

            if ($this->subwayStation == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите станцию метро рядом';
            if ($this->distanceToMetroStation == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж" && $this->subwayStation != "0" && $this->subwayStation != "нет") $errors[] = 'Укажите количество минут ходьбы до ближайшей станции метро';
            if ($this->distanceToMetroStation != "") {
                if (!preg_match('/^\d{0,3}$/', $this->distanceToMetroStation)) $errors[] = 'Неправильный формат для количества минут ходьбы до ближайшей станции метро: должно быть не более 3 цифр';
            }
            if ($this->currency == "0") $errors[] = 'Укажите валюту для рассчетов с арендатором(ами)';
            if ($this->costOfRenting == "") $errors[] = 'Укажите плату за аренду в месяц';
            if ($this->costOfRenting != "") {
                if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->costOfRenting)) $errors[] = 'Неправильный формат для платы за аренду, используйте только цифры и точку, например: 25550.50';
            }
            if ($this->utilities == "0") $errors[] = 'Укажите условия оплаты коммунальных услуг';
            if ($this->costInSummer == "" && $this->utilities != "0" && $this->utilities != "нет") $errors[] = 'Укажите примерную стоимость коммунальных услуг летом';
            if ($this->costInSummer != "") {
                if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->costInSummer)) $errors[] = 'Неправильный формат для стоимости коммунальных услуг летом, используйте только цифры и точку, например: 2550.50';
            }
            if ($this->costInWinter == "" && $this->utilities != "0" && $this->utilities != "нет") $errors[] = 'Укажите примерную стоимость коммунальных услуг зимой';
            if ($this->costInWinter != "") {
                if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->costInWinter)) $errors[] = 'Неправильный формат для стоимости коммунальных услуг зимой, используйте только цифры и точку, например: 2550.50';
            }
            if ($this->electricPower == "0") $errors[] = 'Укажите условия оплаты электроэнергии';
            if ($this->bail == "0") $errors[] = 'Укажите наличие залога';
            if ($this->bailCost == "" && $this->bail != "0" && $this->bail != "нет") $errors[] = 'Укажите величину залога';
            if ($this->bailCost != "") {
                if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->bailCost)) $errors[] = 'Неправильный формат для величины залога, используйте только цифры и точку, например: 2550.50';
            }
            if ($this->prepayment == "0") $errors[] = 'Укажите: есть ли предоплата';
            if ($this->compensationMoney == "" || $this->compensationPercent == "") $errors[] = 'Укажите величину единоразовой комиссии собственника. Если Вы не собираетесь брать ее с арендатора, укажите 0';
            if ($this->compensationMoney != "") {
                if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->compensationMoney)) $errors[] = 'Неправильный формат для величины единоразовой комиссии собственника, используйте только цифры и точку, например: 1550.50';
            }
            if ($this->compensationPercent != "") {
                if (!preg_match('/^\d{0,3}\.{0,1}\d{0,2}$/', $this->compensationPercent)) $errors[] = 'Неправильный формат для величины единоразовой комиссии собственника, используйте только цифры и точку, например: 15.75'; else {
                    if ($this->compensationPercent > 30) $errors[] = "Слишком большая единовременная комиссия. При работе с нашим сайтом разрешается устанавливать размер единовременной комиссии собственника не более 30% от месячной платы за аренду недвижимости";
                }
            }
            if ($this->repair == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите текущее состояние ремонта';
            if ($this->furnish == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите текущее состояние отделки';
            if ($this->windows == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите материал окон';
            if ($this->internet == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие интернета';
            if ($this->telephoneLine == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие телефонной линии';
            if ($this->cableTV == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие кабельного телевидения';

            if (count($this->sexOfTenant) == 0 && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите допустимый пол арендатора';
            if (count($this->relations) == 0 && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите допустимые взаимоотношения между арендаторами';
            if ($this->children == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: готовы ли Вы поселить арендаторов с детьми';
            if ($this->animals == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: готовы ли Вы поселить арендаторов с животными';
            if ($this->contactTelephonNumber != "") {
                if (!preg_match('/^[0-9]{10}$/', $this->contactTelephonNumber)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226540018';
            } else {
                $errors[] = 'Укажите контактный номер телефона для арендаторов по этому объявлению';
            }
            if ($this->timeForRingBegin == "0" || $this->timeForRingEnd == "0") $errors[] = 'Укажите время, в которое Вы готовы принимать звонки от арендаторов';
            if ($this->timeForRingBegin + 0 > $this->timeForRingEnd + 0 && $this->timeForRingBegin != "0" && $this->timeForRingEnd != "0") $errors[] = 'Исправьте: время начала приема звонков не может быть больше, чем время окончания приема звонков';
            if ($this->checking == "0") $errors[] = 'Укажите: как часто Вы собираетесь проверять сдаваемую недвижимость';
            if ($this->responsibility == "") $errors[] = 'Укажите: какую ответственность за состояние и ремонт объекта Вы берете на себя, а какую арендатор';

            return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
        }

        // Метод инициализирует параметры объекта в соответствии с переданным ассоциативным массивом
        // На входе - ассоциированный массив, который, скорее всего, был получен из БД вызывающей метод функцией
        // В случае успеха возвращает TRUE, иначе FALSE
        public function buildFromArr($arr = FALSE)
        {

            if ($arr == FALSE) return FALSE;

            // Присваиваем параметры ассоцативного массива соответствующим свойствам нашего объекта
            foreach ($arr as $key => $value) {

                // TODO: реализовать проверки отдельных $key и преобразование (даты, BLOB..)

                if (isset($this->$key)) $this->$key = $value;
            }

            return TRUE;

        }
    }
