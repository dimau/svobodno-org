<?php

    class RequestFromOwner
    {
        public $id = "";
        public $name = "";
        public $telephon = "";
        public $address = "";
        public $commentOwner = "";
        private $userId = ""; // Хранит идентификатор пользователя, если обратившийся пользователь был авторизован

        // КОНСТРУКТОР
        public function __construct($incomingUser = FALSE)
        {
            // Если пользователь, перешедший на страницу формирования запроса авторизован - воспользуемся его данными (например, для автоматического заполнения части полей)
            if ($incomingUser != FALSE) {
                $this->name = $incomingUser->name." ".$incomingUser->secondName;
                $this->telephon = $incomingUser->telephon;
                $this->userId = $incomingUser->getId();
            }
        }

        // ДЕСТРУКТОР
        public function __destruct()
        {}

        // Сохраняет параметры запроса собственника в БД
        // Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
        public function saveParamsToDB()
        {
            // Если у запроса на просмотр уже есть id, значит речь идет о редактировании данных, в противном случае - о создании нового запроса в БД
            if ($this->id != "") {

                // Непосредственное сохранение данных о поисковом запросе
                $stmt = DBconnect::get()->stmt_init();
                if (($stmt->prepare("UPDATE requestFromOwners SET name = ?, telephon = ?, address = ?, commentOwner = ? WHERE id = ?") === FALSE)
                    OR ($stmt->bind_param("ssssi", $this->name, $this->telephon, $this->address, $this->commentOwner, $this->id) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->affected_rows) === -1)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

            } else {

                // Непосредственное сохранение данных о поисковом запросе
                $stmt = DBconnect::get()->stmt_init();
                if (($stmt->prepare("INSERT INTO requestFromOwners (name, telephon, address, commentOwner, userId) VALUES (?,?,?,?,?)") === FALSE)
                    OR ($stmt->bind_param("sssss", $this->name, $this->telephon, $this->address, $this->commentOwner, $this->userId) === FALSE)
                    OR ($stmt->execute() === FALSE)
                    OR (($res = $stmt->affected_rows) === -1)
                    OR ($res === 0)
                    OR ($stmt->close() === FALSE)
                ) {
                    // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                    return FALSE;
                }

            }

            return TRUE;
        }

        // Инициализировать параметры запроса данными из POST запроса пользователя
        public function writeParamsFromPOST() {
            if (isset($_POST['name'])) $this->name = htmlspecialchars($_POST['name'], ENT_QUOTES);
            if (isset($_POST['telephon'])) $this->telephon = htmlspecialchars($_POST['telephon'], ENT_QUOTES);
            if (isset($_POST['address'])) $this->address = htmlspecialchars($_POST['address'], ENT_QUOTES);
            if (isset($_POST['commentOwner'])) $this->commentOwner = htmlspecialchars($_POST['commentOwner'], ENT_QUOTES);
        }

        // Возвращает ассоциированный массив с данными о запросе собственника на новое объявление
        public function getRequestFromOwnerData() {
            $result = array();

            $result['id'] = $this->id;
            $result['name'] = $this->name;
            $result['telephon'] = $this->telephon;
            $result['address'] = $this->address;
            $result['commentOwner'] = $this->commentOwner;

            return $result;
        }
    }
