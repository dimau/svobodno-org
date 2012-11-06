<?php

    class SearchRequest
    {
        public $userId = "";
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
        public $interestingPropertysId = array();

        // КОНСТРУКТОР
        public function __construct()
        {

        }

        // ДЕСТРУКТОР
        public function __destruct()
        {

        }

        // Перезаписать параметры объекта данными поискового запроса пользователя с id, указанным в $this->userId
        public function writeParamsFromDB() {
            public $userId = "";
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
            public $interestingPropertysId = array();
        }

        public function writeParamsFastFromPOST() {
            if (isset($_GET['typeOfObjectFast'])) $this->typeOfObject = htmlspecialchars($_GET['typeOfObjectFast']);
            if (isset($_GET['districtFast']) && $_GET['districtFast'] != "0") $this->district = array($_GET['districtFast']);
            if (isset($_GET['districtFast']) && $_GET['districtFast'] == "0") $this->district = array();
            if (isset($_GET['minCostFast']) && preg_match("/^\d{0,8}$/", $_GET['minCostFast'])) $this->minCost = htmlspecialchars($_GET['minCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
            if (isset($_GET['maxCostFast']) && preg_match("/^\d{0,8}$/", $_GET['maxCostFast'])) $this->maxCost = htmlspecialchars($_GET['maxCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
        }

        public function writeParamsExtendedFromPOST() {
            if (isset($_GET['typeOfObject'])) $this->typeOfObject = htmlspecialchars($_GET['typeOfObject']);
            if (isset($_GET['amountOfRooms']) && is_array($_GET['amountOfRooms'])) $this->amountOfRooms = $_GET['amountOfRooms'];
            if (isset($_GET['adjacentRooms'])) $this->adjacentRooms = htmlspecialchars($_GET['adjacentRooms']);
            if (isset($_GET['floor'])) $this->floor = htmlspecialchars($_GET['floor']);
            if (isset($_GET['minCost']) && preg_match("/^\d{0,8}$/", $_GET['minCost'])) $this->minCost = htmlspecialchars($_GET['minCost']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
            if (isset($_GET['maxCost']) && preg_match("/^\d{0,8}$/", $_GET['maxCost'])) $this->maxCost = htmlspecialchars($_GET['maxCost']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
            if (isset($_GET['pledge']) && preg_match("/^\d{0,8}$/", $_GET['pledge'])) $this->pledge = htmlspecialchars($_GET['pledge']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
            if (isset($_GET['prepayment'])) $this->prepayment = htmlspecialchars($_GET['prepayment']);
            if (isset($_GET['district']) && is_array($_GET['district'])) $this->district = $_GET['district'];
            if (isset($_GET['withWho'])) $this->withWho = htmlspecialchars($_GET['withWho']);
            if (isset($_GET['children'])) $this->children = htmlspecialchars($_GET['children']);
            if (isset($_GET['animals'])) $this->animals = htmlspecialchars($_GET['animals']);
            if (isset($_GET['termOfLease'])) $this->termOfLease = htmlspecialchars($_GET['termOfLease']);
        }
    }
