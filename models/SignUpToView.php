<?php

    class SignUpToView
    {
        private $id = "";
        public $tenantId = "";
        public $propertyId = "";
        public $tenantTime = "";
        public $tenantComment = "";
        public $ownerStatus = "";
        public $finalDate = "";
        public $finalTimeHours = "";
        public $finalTimeMinutes = "";

        /**
         * КОНСТРУКТОР
         *
         * @param IncomingUser $incomingUser
         */
        public function __construct($tenantId = "", $propertyId = "")
        {
            // Сохраняем id текущего пользователя (который выступает в качестве претендента на объект)
            if ($tenantId != "") {
                $this->tenantId = $tenantId;
            }

            // Сохраняем id объекта недвижимости
            if ($propertyId != "") {
                $this->propertyId = $propertyId;
            }

            // Если по данному пользователю и объекту уже заводился запрос на просмотр - запишем его данные в параметры объекта
            $this->writeParamsFromDB();
        }

        // ДЕСТРУКТОР
        public function __destruct()
        {

        }

        // Сохраняет параметры запроса на показ в БД
        // Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
        public function saveParamsToDB()
        {
            // Если идентификатор пользователя или объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
            if ($this->tenantId == "" || $this->propertyId == "") return FALSE;

            // Подготовим параметры к записи в БД
            $finalDateDB = GlobFunc::dateFromViewToDB($this->finalDate);

            // Если у запроса на просмотр уже есть id, значит речь идет о редактировании данных, в противном случае - о создании нового запроса в БД
            if ($this->id != "") {

                // Непосредственное сохранение данных о поисковом запросе
                $stmt = DBconnect::get()->stmt_init();
                if (($stmt->prepare("UPDATE requestToView SET tenantId = ?, propertyId = ?, tenantTime = ?, tenantComment = ?, ownerStatus = ?, finalDate = ?, finalTimeHours = ?, finalTimeMinutes = ? WHERE id=?") === FALSE)
                    OR ($stmt->bind_param("iissssssi", $this->tenantId, $this->propertyId, $this->tenantTime, $this->tenantComment, $this->ownerStatus, $finalDateDB, $this->finalTimeHours, $this->finalTimeMinutes, $this->id) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->affected_rows) === -1)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

            } else {

                // При сохранении нового запроса на просмотр, необходимо в БД записать его первоначальный статус
                $statusForDB = "inProgress";

                // Непосредственное сохранение данных о поисковом запросе
                $stmt = DBconnect::get()->stmt_init();
                if (($stmt->prepare("INSERT INTO requestToView (tenantId, propertyId, tenantTime, tenantComment, ownerStatus, finalDate, finalTimeHours, finalTimeMinutes) VALUES (?,?,?,?,?,?,?,?)") === FALSE)
                    OR ($stmt->bind_param("iissssss", $this->tenantId, $this->propertyId, $this->tenantTime, $this->tenantComment, $statusForDB, $finalDateDB, $this->finalTimeHours, $this->finalTimeMinutes) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->affected_rows) === -1)
                    OR ($res === 0)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

                // Если все прошло успешно, обновим соответствующих параметр статуса у текущего нашего объекта (модели)
                $this->ownerStatus = $statusForDB;

            }

            return TRUE;
        }

        // Записывает в параметры объекта данные из БД (таблица requestToView) по данному объекту и данному претенденту
        public function writeParamsFromDB()
        {
            // Если идентификатор пользователя или объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
            if ($this->tenantId == "" || $this->propertyId == "") return FALSE;

            // Получим из БД данные ($res) по запросу на просмотр, который нас интересуют (если он сохранен в БД)
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("SELECT * FROM requestToView WHERE tenantId=? AND propertyId=?") === FALSE)
                OR ($stmt->bind_param("ss", $this->tenantId, $this->propertyId) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Если получено меньше или больше одной строки из БД, то сообщаем об ошибке
            if (!is_array($res) || count($res) != 1) {
                // TODO: Сохранить в лог ошибку получения данных пользователя из БД
                return FALSE;
            }

            // Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
            $oneRequestToViewDataArr = $res[0];

            // Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию.
            if (isset($oneRequestToViewDataArr['id'])) $this->id = $oneRequestToViewDataArr['id'];
            if (isset($oneRequestToViewDataArr['tenantId'])) $this->tenantId = $oneRequestToViewDataArr['tenantId'];
            if (isset($oneRequestToViewDataArr['propertyId'])) $this->propertyId = $oneRequestToViewDataArr['propertyId'];
            if (isset($oneRequestToViewDataArr['tenantTime'])) $this->tenantTime = $oneRequestToViewDataArr['tenantTime'];
            if (isset($oneRequestToViewDataArr['tenantComment'])) $this->tenantComment = $oneRequestToViewDataArr['tenantComment'];
            if (isset($oneRequestToViewDataArr['ownerStatus'])) $this->ownerStatus = $oneRequestToViewDataArr['ownerStatus'];
            if (isset($oneRequestToViewDataArr['finalDate'])) $this->finalDate = GlobFunc::dateFromDBToView($oneRequestToViewDataArr['finalDate']);
            if (isset($oneRequestToViewDataArr['finalTimeHours'])) $this->finalTimeHours = $oneRequestToViewDataArr['finalTimeHours'];
            if (isset($oneRequestToViewDataArr['finalTimeMinutes'])) $this->finalTimeMinutes = $oneRequestToViewDataArr['finalTimeMinutes'];

            return TRUE;
        }

        // Записать в качестве параметров запроса на просмотр значения, полученные через POST
        public function writeParamsFromPOST() {
            if (isset($_POST['convenientTime'])) $this->tenantTime = htmlspecialchars($_POST['convenientTime'], ENT_QUOTES);
            if (isset($_POST['comment'])) $this->tenantComment = htmlspecialchars($_POST['comment'], ENT_QUOTES);
        }

        // Валидация формы запроса на показ недвижимости
        public function isParamsCorrect() {
            // Подготовим массив для сохранения сообщений об ошибках
            $errors = array();

            if ($this->tenantTime == "") $errors[] = 'Укажите хотя бы 1 вариант удобного времени для просмотра недвижимости';

            return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
        }

        // Возвращает ассоциированный массив с параметрами Запроса на просмотр
        public function getParams() {

            $result = array();

            $result['id'] = $this->id;
            $result['tenantId'] = $this->tenantId;
            $result['propertyId'] = $this->propertyId;
            $result['tenantTime'] = $this->tenantTime;
            $result['tenantComment'] = $this->tenantComment;
            $result['ownerStatus'] = $this->ownerStatus;
            $result['finalDate'] = $this->finalDate;
            $result['finalTimeHours'] = $this->finalTimeHours;
            $result['finalTimeMinutes'] = $this->finalTimeMinutes;

            return $result;
        }

    }
