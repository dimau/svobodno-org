<?php
/* Класс представляет собой модель Запроса на просмотр */

class RequestToView {
    private $id = "";
    private $tenantId = "";
    private $propertyId = "";
    private $tenantTime = "";
    private $tenantComment = "";
    private $status = "";

    /**
     * КОНСТРУКТОР
     *
     * @param string $tenantId - идентификатор арендатора, который отправил заявку
     * @param string $propertyId - идентификатор объекта недвжимости, который хочет посмотреть арендатор
     * @param string $requestId - идентификатор заявки на просмотр, если она ранее уже была создана и id известен
     */
    public function __construct($tenantId = "", $propertyId = "", $requestId = "") {
        // Сохраняем id текущего пользователя (который выступает в качестве претендента на объект)
        if ($tenantId != "") {
            $this->tenantId = $tenantId;
        }

        // Сохраняем id объекта недвижимости
        if ($propertyId != "") {
            $this->propertyId = $propertyId;
        }

        // Сохраняем id запроса на просмотр
        if ($requestId != "") {
            $this->id = $requestId;
        }

        // Если по данному пользователю и объекту уже заводился запрос на просмотр - запишем его данные в параметры объекта
        $this->writeParamsFromDB();
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setTenantComment($tenantComment) {
        $this->tenantComment = $tenantComment;
    }

    public function setTenantTime($tenantTime) {
        $this->tenantTime = $tenantTime;
    }

    // Сохраняет параметры запроса на показ в БД
    // Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
    public function saveParamsToDB() {
        // Если идентификатор пользователя или объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
        if ($this->tenantId == "" || $this->propertyId == "") return FALSE;

        // Если у запроса на просмотр уже есть id, значит речь идет о редактировании данных, в противном случае - о создании нового запроса в БД
        if ($this->id != "") {

            // Непосредственное сохранение данных о поисковом запросе
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("UPDATE requestToView SET tenantId = ?, propertyId = ?, tenantTime = ?, tenantComment = ?, status = ? WHERE id=?") === FALSE)
                OR ($stmt->bind_param("iisssi", $this->tenantId, $this->propertyId, $this->tenantTime, $this->tenantComment, $this->status, $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

        } else {

            // При сохранении нового запроса на просмотр, необходимо в БД записать его первоначальный статус
            $statusForDB = "Новая";

            // Непосредственное сохранение данных о поисковом запросе
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("INSERT INTO requestToView (tenantId, propertyId, tenantTime, tenantComment, status) VALUES (?,?,?,?,?)") === FALSE)
                OR ($stmt->bind_param("iisss", $this->tenantId, $this->propertyId, $this->tenantTime, $this->tenantComment, $statusForDB) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($res === 0)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            // Если все прошло успешно, обновим соответствующих параметр статуса у текущего нашего объекта (модели)
            $this->status = $statusForDB;

        }

        return TRUE;
    }

    // Записывает в параметры объекта данные из БД (таблица requestToView) по данному объекту и данному претенденту
    public function writeParamsFromDB() {
        // Если идентификатор пользователя или объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
        if (($this->tenantId == "" || $this->propertyId == "") && $this->id == "") return FALSE;

        // Получим из БД данные ($res) по запросу на просмотр, который нас интересуют (если он сохранен в БД)
        if ($this->id != "") {
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("SELECT * FROM requestToView WHERE id=?") === FALSE)
                OR ($stmt->bind_param("s", $this->id) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }
        }

        // Получим из БД данные ($res) по запросу на просмотр, который нас интересуют (если он сохранен в БД)
        if ($this->tenantId != "" && $this->propertyId != "" && $this->id == "") {
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
        if (isset($oneRequestToViewDataArr['status'])) $this->status = $oneRequestToViewDataArr['status'];

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
        $result['status'] = $this->status;

        return $result;
    }

}
