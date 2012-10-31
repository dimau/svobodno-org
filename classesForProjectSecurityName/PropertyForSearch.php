<?php

    class PropertyForSearch extends Property
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

            //TODO: для некоторых объявлений нужно генерить $fileUploadId


        }

        // ДЕСТРУКТОР
        public function __destruct()
        {

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
