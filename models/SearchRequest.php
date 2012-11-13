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
        }

        // ДЕСТРУКТОР
        public function __destruct()
        {

        }

        // Перезаписать параметры объекта данными поискового запроса пользователя с id, указанным в $this->userId
        public function writeParamsFromDB() {

            // Если идентификатор пользователя неизвестен, то дальнейшие действия не имеют смысла
            if ($this->userId == "") return FALSE;

            // Получим из БД данные ($res) по поисковому запросу пользователя с идентификатором = $this->id
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("SELECT * FROM searchrequests WHERE userId=?") === FALSE)
                OR ($stmt->bind_param("s", $this->userId) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Если получено меньше или больше одной строки (одного поискового запроса) из БД, то сообщаем о невозможности записи параметров поискового запроса из БД
            if (!is_array($res) || count($res) != 1) {
                // TODO: Сохранить в лог ошибку получения данных пользователя из БД
                return FALSE;
            }

            // Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
            $oneSearchRequestDataArr = $res[0];

            // Если данные по поисковому запросу есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию.
            if (isset($oneSearchRequestDataArr['userId'])) $this->userId = $oneSearchRequestDataArr['userId'];
            if (isset($oneSearchRequestDataArr['typeOfObject'])) $this->typeOfObject = $oneSearchRequestDataArr['typeOfObject'];
            if (isset($oneSearchRequestDataArr['amountOfRooms'])) $this->amountOfRooms = unserialize($oneSearchRequestDataArr['amountOfRooms']);
            if (isset($oneSearchRequestDataArr['adjacentRooms'])) $this->adjacentRooms = $oneSearchRequestDataArr['adjacentRooms'];
            if (isset($oneSearchRequestDataArr['floor'])) $this->floor = $oneSearchRequestDataArr['floor'];
            if (isset($oneSearchRequestDataArr['minCost'])) $this->minCost = $oneSearchRequestDataArr['minCost'];
            if (isset($oneSearchRequestDataArr['maxCost'])) $this->maxCost = $oneSearchRequestDataArr['maxCost'];
            if (isset($oneSearchRequestDataArr['pledge'])) $this->pledge = $oneSearchRequestDataArr['pledge'];
            if (isset($oneSearchRequestDataArr['prepayment'])) $this->prepayment = $oneSearchRequestDataArr['prepayment'];
            if (isset($oneSearchRequestDataArr['district'])) $this->district = unserialize($oneSearchRequestDataArr['district']);
            if (isset($oneSearchRequestDataArr['withWho'])) $this->withWho = $oneSearchRequestDataArr['withWho'];
            if (isset($oneSearchRequestDataArr['linksToFriends'])) $this->linksToFriends = $oneSearchRequestDataArr['linksToFriends'];
            if (isset($oneSearchRequestDataArr['children'])) $this->children = $oneSearchRequestDataArr['children'];
            if (isset($oneSearchRequestDataArr['howManyChildren'])) $this->howManyChildren = $oneSearchRequestDataArr['howManyChildren'];
            if (isset($oneSearchRequestDataArr['animals'])) $this->animals = $oneSearchRequestDataArr['animals'];
            if (isset($oneSearchRequestDataArr['howManyAnimals'])) $this->howManyAnimals = $oneSearchRequestDataArr['howManyAnimals'];
            if (isset($oneSearchRequestDataArr['termOfLease'])) $this->termOfLease = $oneSearchRequestDataArr['termOfLease'];
            if (isset($oneSearchRequestDataArr['additionalDescriptionOfSearch'])) $this->additionalDescriptionOfSearch = $oneSearchRequestDataArr['additionalDescriptionOfSearch'];
            if (isset($oneSearchRequestDataArr['interestingPropertysId'])) $this->interestingPropertysId = unserialize($oneSearchRequestDataArr['interestingPropertysId']);

            return TRUE;
        }

        // Инициализировать параметры поискового запроса данными из POST запроса пользователя (форма быстрого поиска)
        public function writeParamsFastFromPOST() {
            if (isset($_GET['typeOfObjectFast'])) $this->typeOfObject = htmlspecialchars($_GET['typeOfObjectFast']);
            if (isset($_GET['districtFast']) && $_GET['districtFast'] != "0") $this->district = array($_GET['districtFast']);
            if (isset($_GET['districtFast']) && $_GET['districtFast'] == "0") $this->district = array();
            if (isset($_GET['minCostFast']) && preg_match("/^\d{0,8}$/", $_GET['minCostFast'])) $this->minCost = htmlspecialchars($_GET['minCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
            if (isset($_GET['maxCostFast']) && preg_match("/^\d{0,8}$/", $_GET['maxCostFast'])) $this->maxCost = htmlspecialchars($_GET['maxCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
        }

        // Инициализировать параметры поискового запроса данными из POST запроса пользователя (форма поиска с подробными параметрами)
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

        // Возвращает массив с краткими данными (id, coordX, coordY) об объектах недвижимости, соответствующих параметрам поискового запроса
        public function getArrResultSQLrequest() {

            // Инициализируем массив, в который будем собирать условия поиска.
            $searchLimits = array();

            // Ограничение на тип объекта
            $searchLimits['typeOfObject'] = "";
            if ($this->typeOfObject == "0") $searchLimits['typeOfObject'] = "";
            if ($this->typeOfObject == "квартира" || $this->typeOfObject == "комната" || $this->typeOfObject == "дом" || $this->typeOfObject == "таунхаус" || $this->typeOfObject == "дача" || $this->typeOfObject == "гараж") {
                $searchLimits['typeOfObject'] = " (typeOfObject = '" . $this->typeOfObject . "')"; // Думаю, что с точки зрения безопасности (чтобы нельзя было подсунуть в запрос левые SQL подобные строки), нужно перечислять все доступные варианты
            }

            // Ограничение на количество комнат
            $searchLimits['amountOfRooms'] = "";
            if (count($this->amountOfRooms) != "0") {
                $searchLimits['amountOfRooms'] = " (";
                for ($i = 0; $i < count($this->amountOfRooms); $i++) {
                    $searchLimits['amountOfRooms'] .= " amountOfRooms = '" . $this->amountOfRooms[$i] . "'";
                    if ($i < count($this->amountOfRooms) - 1) $searchLimits['amountOfRooms'] .= " OR";
                }
                $searchLimits['amountOfRooms'] .= " )";
            }

            // Ограничение на смежность комнат
            $searchLimits['adjacentRooms'] = "";
            if ($this->adjacentRooms == "0") $searchLimits['adjacentRooms'] = "";
            if ($this->adjacentRooms == "не имеет значения") $searchLimits['adjacentRooms'] = "";
            if ($this->adjacentRooms == "только изолированные") $searchLimits['adjacentRooms'] = " (adjacentRooms != 'да')";

            // Ограничение на этаж
            $searchLimits['floor'] = "";
            if ($this->floor == "0") $searchLimits['floor'] = "";
            if ($this->floor == "любой") $searchLimits['floor'] = " (floor != 0)";
            if ($this->floor == "не первый") $searchLimits['floor'] = " (floor != 0 AND floor != 1)";
            if ($this->floor == "не первый и не последний") $searchLimits['floor'] = " (floor != 0 AND floor != 1 AND floor != totalAmountFloor)";

            // Ограничение на минимальную сумму арендной платы
            $searchLimits['minCost'] = "";
            if ($this->minCost == "") $searchLimits['minCost'] = "";
            if ($this->minCost != "") $searchLimits['minCost'] = " (realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting >= " . $this->minCost . ")";

            // Ограничение на максимальную сумму арендной платы
            $searchLimits['maxCost'] = "";
            if ($this->maxCost == "") $searchLimits['maxCost'] = "";
            if ($this->maxCost != "") $searchLimits['maxCost'] = " (realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting <= " . $this->maxCost . ")";

            // Ограничение на максимальный залог
            $searchLimits['pledge'] = "";
            if ($this->pledge == "") $searchLimits['pledge'] = "";
            if ($this->pledge != "") $searchLimits['pledge'] = " (bailCost * realCostOfRenting / costOfRenting <= " . $this->pledge . ")"; // отношение realCostOfRenting / costOfRenting позволяет вычислить курс валюты, либо получить 1, если стоимость аренды указана собственником в рублях

            // Ограничение на предоплату
            $searchLimits['prepayment'] = "";
            if ($this->prepayment == "0") $searchLimits['prepayment'] = "";
            if ($this->prepayment != "0") $searchLimits['prepayment'] = " (prepayment + 0 <= '" . $this->prepayment . "')";

            // Ограничение на район
            $searchLimits['district'] = "";
            if (count($this->district) == 0) $searchLimits['district'] = "";
            if (count($this->district) != 0) {
                $searchLimits['district'] = " (";
                for ($i = 0; $i < count($this->district); $i++) {
                    $searchLimits['district'] .= " district = '" . $this->district[$i] . "'";
                    if ($i < count($this->district) - 1) $searchLimits['district'] .= " OR";
                }
                $searchLimits['district'] .= " )";
            }

            // Ограничение на формат проживания (с кем собираетесь проживать)
            $searchLimits['withWho'] = "";
            if ($this->withWho == "0") $searchLimits['withWho'] = "";
            if ($this->withWho == "самостоятельно") $searchLimits['withWho'] = "(relations LIKE '%один человек%' OR relations = '')";
            if ($this->withWho == "семья") $searchLimits['withWho'] = "(relations LIKE '%семья%' OR relations = '')";
            if ($this->withWho == "пара") $searchLimits['withWho'] = "(relations LIKE '%пара%' OR relations = '')";
            if ($this->withWho == "2 мальчика") $searchLimits['withWho'] = "(relations LIKE '%2 мальчика%' OR relations = '')";
            if ($this->withWho == "2 девочки") $searchLimits['withWho'] = "(relations LIKE '%2 девочки%' OR relations = '')";
            if ($this->withWho == "со знакомыми") $searchLimits['withWho'] = "(relations LIKE '%группа людей%' OR relations = '')";

            // Ограничение на проживание с детьми
            $searchLimits['children'] = "";
            if ($this->children == "0" || $this->children == "без детей") $searchLimits['children'] = "";
            if ($this->children == "с детьми старше 4-х лет") $searchLimits['children'] = " (children != 'только без детей')";
            if ($this->children == "с детьми младше 4-х лет") $searchLimits['children'] = " (children != 'только без детей' AND children != 'с детьми старше 4-х лет')";

            // Ограничение на проживание с животными
            $searchLimits['animals'] = "";
            if ($this->animals == "0" || $this->animals == "без животных") $searchLimits['animals'] = "";
            if ($this->animals == "с животным(ми)") $searchLimits['animals'] = " (animals != 'только без животных')";

            // Ограничение на длительность аренды
            $searchLimits['termOfLease'] = "";
            if ($this->termOfLease == "0") $searchLimits['termOfLease'] = "";
            if ($this->termOfLease == "длительный срок") $searchLimits['termOfLease'] = " (termOfLease = 'длительный срок')";
            if ($this->termOfLease == "несколько месяцев") $searchLimits['termOfLease'] = " (termOfLease = 'несколько месяцев')";

            // Показываем только опубликованные объявления
            $searchLimits['status'] = " (status = 'опубликовано')";

            // Собираем строку WHERE для поискового запроса к БД
            $strWHERE = "";
            foreach ($searchLimits as $value) {
                if ($value == "") continue;
                if ($strWHERE != "") $strWHERE .= " AND" . $value; else $strWHERE .= $value;
            }

            // Получаем данные из БД - ВСЕ объекты недвижимости, соответствующие поисковому запросу
            // Сортируем по стоимости аренды и не ограничиваем количество объявлений - все, подходящие под условия пользователя
            // В итоге получим массив ($propertyLightArr), каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
            $res = $this->DBlink->query("SELECT id, coordX, coordY FROM property WHERE".$strWHERE." ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting");
            if (($this->DBlink->errno)
                OR (($propertyLightArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
            ) {
                // Логируем ошибку
                //TODO: сделать логирование ошибки
                return array();
            }

            return $propertyLightArr;
        }

        // Возвращает ассоциированный массив с данными о поисковом запросе (для использования в представлении)
        public function getSearchRequestData() {

            $result = array();

            $result['typeOfObject'] = $this->typeOfObject;
            $result['amountOfRooms'] = $this->amountOfRooms;
            $result['adjacentRooms'] = $this->adjacentRooms;
            $result['floor'] = $this->floor;
            $result['minCost'] = $this->minCost;
            $result['maxCost'] = $this->maxCost;
            $result['pledge'] = $this->pledge;
            $result['prepayment'] = $this->prepayment;
            $result['district'] = $this->district;
            $result['withWho'] = $this->withWho;
            $result['linksToFriends'] = $this->linksToFriends;
            $result['children'] = $this->children;
            $result['howManyChildren'] = $this->howManyChildren;
            $result['animals'] = $this->animals;
            $result['howManyAnimals'] = $this->howManyAnimals;
            $result['termOfLease'] = $this->termOfLease;
            $result['additionalDescriptionOfSearch'] = $this->additionalDescriptionOfSearch;
            $result['interestingPropertysId'] = $this->interestingPropertysId;

            return $result;

        }
    }
