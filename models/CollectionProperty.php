<?php

    /* Объект данного класса служит для получения из БД и обработки данных сразу по множеству объектов недвижимости (это позволяет сократить кодичество запросов к БД, которое бы потребовалось при работе с объектами класса Property)
     * Пример использования - получение и обработка данных по всем объектам, собственником которых является пользователель с таким-то id (для Личного кабинета)
     */

    class CollectionProperty
    {
        public $allPropertiesCharacteristic = array(); // Массив содержит массивы данных по каждому конкретному объекту недвижимости из коллекции
        public $allPropertiesFotoInformation = array(); // 2-х мерный массив. Каждый член данного массива представляет фотографии по отдельному объекту недвижимости (позиции соответствуют позициям объектов в массиве $allPropertiesCharacteristic). Каждый член этого массива представляет собой массив массивов, каждый член которого содержит информацию по конкретной фотографии соответствующего объекта недвижимости
        public $allPropertiesTenantPretenders = array(); // 2-х мерный массив. Каждый член данного массива представляет данные потенциальных арендаторов по отдельному объекту недвижимости (позиции соответствуют позициям объектов в массиве $allPropertiesCharacteristic). Каждый член этого массива представляет собой массив массивов, каждый член которого содержит информацию по конкретному претенденту на аренду соответствующего объекта недвижимости

        private $DBlink = FALSE; // Переменная для хранения объекта соединения с базой данных
        private $globFunc = FALSE; // Переменная для хранения глобальных функций

        // КОНСТРУКТОР
        public function __construct($globFunc = FALSE, $DBlink = FALSE) {

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
        public function __destruct() {

        }

        // Метод создает коллекцию объектов по id собственника
        // Возвращает количество созданных объектов, либо FALSE в случае ошибки
        public function buildFromOwnerId($userId = FALSE) {

            // Проверка: передан ли id собственника
            if ($userId == FALSE) return FALSE;

            // Получаем данные по всем объектам недвижимости пользователя (в качестве собственника)
            $stmt = $this->DBlink->stmt_init();
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
            for ($i = 0; $i < count($this->allPropertiesCharacteristic); $i++) {

                // Получим из БД данные о фотографиях ($res) по объекту недвижимости
                $stmt = $this->DBlink->stmt_init();
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

                // Получаем список id заинтересовавшихся арендаторов
                $visibleUsersId = unserialize($this->allPropertiesCharacteristic[$i]['visibleUsersId']);
               // Получаем имена и отчества заинтересовавшихся арендаторов
               // Составляем условие запроса к БД, указывая интересующие нас id объявлений
               $selectValue = "";
               for ($j = 0; $j < count($visibleUsersId); $j++) {
                   $selectValue .= " id = '" . $visibleUsersId[$j] . "'";
                   if ($j < count($visibleUsersId) - 1) $selectValue .= " OR";
               }

                // Получим из БД данные о заинтересовавшихся арендаторах ($res) по объекту недвижимости
                $res = $this->DBlink->query("SELECT id, typeTenant, name, secondName FROM users WHERE ".$selectValue);
                if (($this->DBlink->errno)
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
            $stmt = $this->DBlink->stmt_init();
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
            for ($i = 0; $i < count($this->allPropertiesCharacteristic); $i++) {

                if ($this->allPropertiesCharacteristic[$i]['id'] == $propertyId) {
                    $this->allPropertiesCharacteristic[$i]['status'] = $status;
                    break;
                }
            }

            return TRUE;
        }

    }

