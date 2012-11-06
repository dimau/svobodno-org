<?php

    /* Объект данного класса служит для создания и управления коллекциями объектов недвижимости (класса Property)
     *
     */

    class CollectionProperty
    {
        public $allProperties = array();
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

            // Сбрасываем массив управляемых объектов для того, чтобы заполнить его новыми значениями
            $this->allProperties = array();

            // Создаем объекты, соответствующие недвижимости данного пользователя
            for ($i = 0; $i < count($res); $i++) {
                $property = new Property($this->globFunc, $this->DBlink);
                if ($property->buildFromArr($res[$i])) {
                    $this->allProperties[] = $property;
                }
            }

            // В итоге имеем массив $allProperties, состоящий из объектов класса Property
            // Возвращаем количество успешно созданных объектов
            return count($this->allProperties);

        }

    }

