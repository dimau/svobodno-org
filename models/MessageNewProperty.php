<?php
/* Класс представляет собой модель для уведомления о новом подходящем объекте недвижимости */

class MessageNewProperty {
    private $id = "";
    private $userId = "";
    private $timeIndex = "";
    private $messageType = "newProperty";
    private $isReaded = "";
    private $fotoArr = array();
    private $targetId = "";
    private $needEmail = 0;
    private $needSMS = 0;
    private $typeOfObject = "0";
    private $address = "";
    private $currency = "0";
    private $costOfRenting = "";
    private $utilities = "0";
    private $electricPower = "0";
    private $amountOfRooms = "0";
    private $adjacentRooms = "0";
    private $amountOfAdjacentRooms = "0";
    private $roomSpace = "";
    private $totalArea = "";
    private $livingSpace = "";
    private $kitchenSpace = "";
    private $totalAmountFloor = "";
    private $numberOfFloor = "";

    /**
     * КОНСТРУКТОР
     *
     * Если в качестве параметра конструктора не указан id существующего уведомления, то конструктор инициализирует параметры объекта пустыми значениями.
     * Если объект создается под существующее уведомление, то нужно сразу указать id этого уведомления (в параметрах конструктора)
     * @param $messageId - идентификатор существующего (записанного ранее в БД) уведомления класса "Новый подходящий объект недвижимости"
     */
    public function __construct($messageId) {
        // Если конструктору передан идентификатор существующего уведомления, то считаем его параметры из БД
        if (isset($messageId) && is_int($messageId)) {
            $this->id = $messageId;
            $this->writeParamsFromDB();
        }
    }

    // ДЕСТРУКТОР
    public function __destruct() {
    }

    // Сохраняет параметры уведомления в БД
    // Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
    public function saveParamsToDB() {

        // Если идентификатор пользователя или объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
        if ($this->userId == "" || $this->targetId == "") return FALSE;

        // Если у уведомления уже есть id, значит речь идет о редактировании данных, в противном случае - о создании нового уведомления в БД
        if ($this->id != "") {
            if (!DBconnect::updateMessageNewProperty($this->getParams())) return FALSE;
        } else {
            if (!DBconnect::insertMessageNewProperty($this->getParams())) return FALSE;
        }

        return TRUE;
    }

    // Записывает в параметры данные из БД об уведомлении
    public function writeParamsFromDB() {

        // Если идентификатор уведомления неизвестен, то дальнейшие действия не имеют смысла
        if ($this->id == "") return FALSE;

        // Получим из БД данные ($res) по уведомлению, которое нас интересуют (если оно сохранено в БД)
        $res = DBconnect::selectMessageNewPropertyForId($this->id);

        // Если получено меньше или больше одной строки из БД, то сообщаем об ошибке
        if (count($res) != 1) {
            // TODO: Сохранить в лог ошибку получения данных пользователя из БД
            return FALSE;
        }

        // Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
        $one = $res[0];

        // Если данные есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию.
        if (isset($one['id'])) $this->id = $one['id'];
        if (isset($one['userId'])) $this->userId = $one['userId'];
        if (isset($one['timeIndex'])) $this->timeIndex = $one['timeIndex'];
        if (isset($one['messageType'])) $this->messageType = $one['messageType'];
        if (isset($one['isReaded'])) $this->isReaded = $one['isReaded'];
        if (isset($one['fotoArr'])) $this->fotoArr = $one['fotoArr'];
        if (isset($one['targetId'])) $this->targetId = $one['targetId'];
        if (isset($one['needEmail'])) $this->needEmail = $one['needEmail'];
        if (isset($one['needSMS'])) $this->needSMS = $one['needSMS'];
        if (isset($one['typeOfObject'])) $this->typeOfObject = $one['typeOfObject'];
        if (isset($one['address'])) $this->address = $one['address'];
        if (isset($one['currency'])) $this->currency = $one['currency'];
        if (isset($one['costOfRenting'])) $this->costOfRenting = $one['costOfRenting'];
        if (isset($one['utilities'])) $this->utilities = $one['utilities'];
        if (isset($one['electricPower'])) $this->electricPower = $one['electricPower'];
        if (isset($one['amountOfRooms'])) $this->amountOfRooms = $one['amountOfRooms'];
        if (isset($one['adjacentRooms'])) $this->adjacentRooms = $one['adjacentRooms'];
        if (isset($one['amountOfAdjacentRooms'])) $this->amountOfAdjacentRooms = $one['amountOfAdjacentRooms'];
        if (isset($one['roomSpace'])) $this->roomSpace = $one['roomSpace'];
        if (isset($one['totalArea'])) $this->totalArea = $one['totalArea'];
        if (isset($one['livingSpace'])) $this->livingSpace = $one['livingSpace'];
        if (isset($one['kitchenSpace'])) $this->kitchenSpace = $one['kitchenSpace'];
        if (isset($one['totalAmountFloor'])) $this->totalAmountFloor = $one['totalAmountFloor'];
        if (isset($one['numberOfFloor'])) $this->numberOfFloor = $one['numberOfFloor'];

        return TRUE;
    }

    // Возвращает TRUE, если данное уведомление относится к пользователю $userId и FALSE в противном случае
    public function referToUser($userId) {

        // Валидация входных данных
        if (!isset($userId) || !is_int($userId)) return FALSE;

        return ($this->userId == $userId);
    }

    // Удаляет данное уведомление из БД
    // Возвращает TRUE в случае успеха и FALSE в противном случае
    public function remove() {
        return DBconnect::deleteMessageNewPropertyForId($this->id);
    }

    // Делает уведомление прочитанным
    // Возвращает TRUE
    public function changeIsReadedTrue() {
        $this->isReaded = "прочитано";
        return TRUE;
    }

    public function getParams() {
        $result = array();

        $result['id'] = $this->id;
        $result['userId'] = $this->userId;
        $result['timeIndex'] = $this->timeIndex;
        $result['messageType'] = $this->messageType;
        $result['isReaded'] = $this->isReaded;
        $result['fotoArr'] = $this->fotoArr;
        $result['targetId'] = $this->targetId;
        $result['needEmail'] = $this->needEmail;
        $result['needSMS'] = $this->needSMS;
        $result['typeOfObject'] = $this->typeOfObject;
        $result['address'] = $this->address;
        $result['currency'] = $this->currency;
        $result['costOfRenting'] = $this->costOfRenting;
        $result['utilities'] = $this->utilities;
        $result['electricPower'] = $this->electricPower;
        $result['amountOfRooms'] = $this->amountOfRooms;
        $result['adjacentRooms'] = $this->adjacentRooms;
        $result['amountOfAdjacentRooms'] = $this->amountOfAdjacentRooms;
        $result['roomSpace'] = $this->roomSpace;
        $result['totalArea'] = $this->totalArea;
        $result['livingSpace'] = $this->livingSpace;
        $result['kitchenSpace'] = $this->kitchenSpace;
        $result['totalAmountFloor'] = $this->totalAmountFloor;
        $result['numberOfFloor'] = $this->numberOfFloor;

        return $result;
    }
}
