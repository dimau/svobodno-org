<?php

class RequestFromOwner {
    private $id = "";
    private $name = "";
    private $telephon = "";
    private $address = "";
    private $commentOwner = "";
    private $userId = ""; // Хранит идентификатор пользователя, если обратившийся пользователь был авторизован
    private $regDate = "";

    /**
     * КОНСТРУКТОР
     *
     * @param userIncoming $userIncoming объект, созданный в качестве модели запросившего страницу пользователя
     */
    public function __construct($userIncoming) {
        // Если пользователь, перешедший на страницу формирования запроса авторизован - воспользуемся его данными (например, для автоматического заполнения части полей)
        if (isset($userIncoming) && $userIncoming->login()) {
            $this->name = $userIncoming->getName() . " " . $userIncoming->getSecondName();
            $this->telephon = $userIncoming->getTelephon();
            $this->userId = $userIncoming->getId();
        }
    }

    public function getName() {
        return $this->name;
    }

    public function getAddress() {
        return $this->address;
    }

    // Сохраняет параметры запроса собственника в БД
    // Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
    public function saveParamsToDB() {
        // Если у запроса на просмотр уже есть id, значит речь идет о редактировании данных, в противном случае - о создании нового запроса в БД
        if ($this->id != "") {

            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("UPDATE requestFromOwners SET name = ?, telephon = ?, address = ?, commentOwner = ?, userId = ?, regDate = ? WHERE id = ?") === FALSE)
                OR ($stmt->bind_param("ssssiii", $this->name, $this->telephon, $this->address, $this->commentOwner, $this->userId, $this->regDate, $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

        } else {

            // Вычислим время регистрации заявки от собственника на сайте
            $this->regDate = time();

            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("INSERT INTO requestFromOwners (name, telephon, address, commentOwner, userId, regDate) VALUES (?,?,?,?,?,?)") === FALSE)
                OR ($stmt->bind_param("ssssii", $this->name, $this->telephon, $this->address, $this->commentOwner, $this->userId, $this->regDate) === FALSE)
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

    // Валидация параметров запроса собственника
    public function requestFromOwnerDataValidate() {
        // Подготовим массив для сохранения сообщений об ошибках
        $errors = array();

        if ($this->name == "") $errors[] = 'Укажите Ваше имя';
        if (mb_strlen($this->name, "utf-8") > 100) $errors[] = 'Слишком длинное имя. Используйте не более 100 символов';

        if ($this->telephon == "") $errors[] = 'Укажите Ваш контактный номер телефона';
        if (mb_strlen($this->telephon, "utf-8") > 20) $errors[] = 'Слишком длинный номер телефона. Используйте не более 20 цифр, например: 9225468392';

        if ($this->address == "") $errors[] = 'Укажите адрес недвижимости';
        if (mb_strlen($this->address, "utf-8") > 60) $errors[] = 'Указан слишком длинный адрес (используйте не более 60 символов)';

        // Возвращаем список ошибок, если все в порядке, то он будет пуст
        return $errors;
    }

    // Возвращает ассоциированный массив с данными о запросе собственника на новое объявление
    public function getRequestFromOwnerData() {
        $result = array();

        $result['id'] = $this->id;
        $result['name'] = $this->name;
        $result['telephon'] = $this->telephon;
        $result['address'] = $this->address;
        $result['commentOwner'] = $this->commentOwner;
        $result['userId'] = $this->userId;
        $result['regDate'] = $this->regDate;

        return $result;
    }
}
