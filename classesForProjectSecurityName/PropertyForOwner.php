<?php

    class PropertyForOwner extends Property
    {

        public $typeOfObject = "0";
        public $dateOfEntry = "";
        public $termOfLease = "0";
        public $dateOfCheckOut = "";
        public $address = "";
        public $apartmentNumber = "";
        public $currency = "0";
        public $costOfRenting = "";
        public $utilities = "0";
        public $costInSummer = "";
        public $costInWinter = "";
        public $electricPower = "0";
        public $bail = "0";
        public $bailCost = "";
        public $prepayment = "0";
        public $repair = "0";
        public $furnish = "0";
        public $furnitureInLivingArea = array();
        public $furnitureInLivingAreaExtra = "";
        public $furnitureInKitchen = array();
        public $furnitureInKitchenExtra = "";
        public $appliances = array();
        public $appliancesExtra = "";
        public $contactTelephonNumber = "";
        public $timeForRingBegin = "0";
        public $timeForRingEnd = "0";
        public $status = "";
        public $visibleUsersId = array();
        public $id = "";


        //public $userId = "";

        //public $fileUploadId = "";
        //public $uploadedFoto = array(); // В переменной будет храниться информация о загруженных фотографиях. Представляет собой массив ассоциированных массивов
        //public $primaryFotoId = "";

        //private $DBlink = FALSE; // Переменная для хранения объекта соединения с базой данных
        //private $globFunc = FALSE; // Переменная для хранения глобальных функций


        // Метод инициализирует параметры объекта в соответствии с переданным ассоциативным массивом
        // На входе - ассоциированный массив, который, скорее всего, был получен из БД вызывающей метод функцией
        // В случае успеха возвращает TRUE, иначе FALSE
        // КОНСТРУКТОР
        public function __construct($arr) {

            if ($arr == FALSE) return FALSE;

            // Присваиваем параметры ассоцативного массива соответствующим свойствам нашего объекта
            foreach ($arr as $key => $value) {

                // TODO: реализовать проверки отдельных $key и преобразование (даты, BLOB..)

                if (isset($this->$key)) $this->$key = $value;
            }

            return TRUE;

        }
    }
