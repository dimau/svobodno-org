<?php

/* Объект данного класса служит для получения из БД и обработки данных сразу по множеству объектов недвижимости (это позволяет сократить кодичество запросов к БД, которое бы потребовалось при работе с объектами класса Property)
* Пример использования - получение и обработка данных по всем объектам, собственником которых является пользователель с таким-то id (для Личного кабинета)
*/

class CollectionProperty {
    private $allPropertiesCharacteristic = array(); // Массив содержит массивы данных по каждому конкретному объекту недвижимости из коллекции
    private $allPropertiesFotoInformation = array(); // 2-х мерный массив. Каждый член данного массива представляет фотографии по отдельному объекту недвижимости (позиции соответствуют позициям объектов в массиве $allPropertiesCharacteristic). Каждый член этого массива представляет собой массив массивов, каждый член которого содержит информацию по конкретной фотографии соответствующего объекта недвижимости
    private $allPropertiesTenantPretenders = array(); // 2-х мерный массив. Каждый член данного массива представляет данные потенциальных арендаторов по отдельному объекту недвижимости (позиции соответствуют позициям объектов в массиве $allPropertiesCharacteristic). Каждый член этого массива представляет собой массив массивов, каждый член которого содержит информацию по конкретному претенденту на аренду соответствующего объекта недвижимости

    // КОНСТРУКТОР
    public function __construct() { }

    // ДЕСТРУКТОР
    public function __destruct() { }

    // Метод создает коллекцию объектов по id собственника
    // Возвращает количество созданных объектов, либо FALSE в случае ошибки
    public function buildFromOwnerId($userId = FALSE) {

        // Проверка: передан ли id собственника
        if ($userId == FALSE) return FALSE;

        // Получаем данные по всем объектам недвижимости пользователя (в качестве собственника)
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("SELECT * FROM property WHERE userId=? ORDER BY status DESC, last_act DESC") === FALSE)
            OR ($stmt->bind_param("s", $userId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->get_result()) === FALSE)
            OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
            OR ($stmt->close() === FALSE)
        ) {
            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            return FALSE;
        }

        // Записываем результат в переменную объекта
        $this->allPropertiesCharacteristic = $res;

        // Получаем для каждого объекта недвижимости из коллекции данные по его фотографиям
        for ($i = 0, $s = count($this->allPropertiesCharacteristic); $i < $s; $i++) {

            // Получим из БД данные о фотографиях ($res) по объекту недвижимости
            $stmt = DBconnect::get()->stmt_init();
            if (($stmt->prepare("SELECT * FROM propertyFotos WHERE propertyId=?") === FALSE)
                OR ($stmt->bind_param("s", $this->allPropertiesCharacteristic[$i]['id']) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                $this->allPropertiesFotoInformation[$i] = array();
            } else {

                // Записываем результат в переменную объекта
                $this->allPropertiesFotoInformation[$i] = $res;
            }

            // Получаем список id заинтересовавшихся арендаторов (кроме id заинтересовавшегося арендатора в массив попадает вся информация по каждому запросу на контакты собственника)
            $allRequestsForOwnerContactsForProperty = DBconnect::selectRequestsForOwnerContactsForProperties($this->allPropertiesCharacteristic[$i]['id']);
            // Получаем имена и отчества заинтересовавшихся арендаторов
            // Составляем условие запроса к БД, указывая интересующие нас id объявлений
            $selectValue = "";
            for ($j = 0, $s1 = count($allRequestsForOwnerContactsForProperty); $j < $s1; $j++) {
                $selectValue .= " id = '" . $allRequestsForOwnerContactsForProperty[$j]['tenantId'] . "'";
                if ($j < $s1 - 1) $selectValue .= " OR";
            }

            // Получим из БД данные о заинтересовавшихся арендаторах ($res) по объекту недвижимости
            $res = DBconnect::get()->query("SELECT id, typeTenant, name, secondName FROM users WHERE " . $selectValue);
            if ((DBconnect::get()->errno)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
            ) {
                // Логируем ошибку
                //TODO: сделать логирование ошибки
                $this->allPropertiesTenantPretenders[$i] = array();
            } else {
                $this->allPropertiesTenantPretenders[$i] = $res;
            }

        }

        // Возвращаем количество успешно созданных объектов
        return count($this->allPropertiesCharacteristic);
    }

    // Возвращем allPropertiesCharacteristic
    public function getAllPropertiesCharacteristic() {
        return $this->allPropertiesCharacteristic;
    }

    // Возвращает allPropertiesFotoInformation
    public function getAllPropertiesFotoInformation() {
        return $this->allPropertiesFotoInformation;
    }

    // Возвращает allPropertiesTenantPretenders
    public function getAllPropertiesTenantPretenders() {
        return $this->allPropertiesTenantPretenders;
    }

    // Возвращает TRUE, если объект с id = $propertyId есть в коллекции и FALSE в противном случае
    public function hasPropertyId($propertyId = 0) {

        foreach ($this->allPropertiesCharacteristic as $value) {

            if ($value['id'] == $propertyId) return TRUE;
        }

        return FALSE;
    }

    // Функция присваивает статус опубликовано объекту с id = $propertyId
    // Если все сделать удалось, возвращает TRUE, иначе FALSE
    public function setPublicationStatus($newStatus, $propertyId) {
        /**
         * Алгоритм работы следующий
         *
         * Если статус объекта меняется на "опубликован", то:
         *
         *
         * Если статус объекта меняется на "не опубликован", то:
         *     1. Изменить статус объекта на "не опубликован" в БД
         *     2. Очистить дату и время ближайшего показа в БД
         *     3. Если есть заявки на его просмотр со статусом (Новая, Назначен просмотр, Отложена), то сообщить о них оператору - необходимо вручную изменить статус, предварительно созвонившись с арендаторами
         *     4. Удалить все уведомления (типа Новый подходящий объект)
         *     5. Очистить дату въезда и выезда у объекта
         *
         */

        // Проверяем: имеет ли данный пользователь право на выполнение изменения статуса объявления
        if (!$this->hasPropertyId($propertyId)) return FALSE;

        // Какой статус присвоим объявлению
        if ($newStatus == "опубликовано") {
            $status = "опубликовано";
        } elseif ($newStatus == "не опубликовано") {
            $status = "не опубликовано";
        } else {
            return FALSE;
        }

        // Внесем изменения в БД
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE property SET status = ? WHERE id = ?") === FALSE)
            OR ($stmt->bind_param("ss", $status, $propertyId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            return FALSE;
        }

        // Внесем изменения в текущие параметры модели
        for ($i = 0, $s = count($this->allPropertiesCharacteristic); $i < $s; $i++) {

            if ($this->allPropertiesCharacteristic[$i]['id'] == $propertyId) {
                $this->allPropertiesCharacteristic[$i]['status'] = $status;
                break;
            }
        }

        return TRUE;
    }

}

