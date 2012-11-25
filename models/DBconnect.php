<?php
    /* Статический класс для работы с БД (практически синглтон, содержащий единственный на весь скрипт объект соединения с Базой данных) */

    class DBconnect
    {
        private static $connect;   // Cодержит объект соединения с базой данных класса mysqli (единственный на весь скрипт)

        public static function get()
        {
            if (self::$connect === NULL) { // Если соединение с БД еще не устанавливалось
                self::$connect = self::connectToDB(); // Создаем объект соединения с БД
            }

            return self::$connect; // Возвращаем объект соединения с БД. Либо FALSE, если установить соединение не удалось
        }

        // Метод отрабатывает один раз при вызове DBconnect::get();
        // Метод возвращает объект соединения с БД (mysqli), лиюо FALSE
        private static function connectToDB()
        {
            // Устанавливаем соединение с базой данных
            $mysqli = new mysqli("localhost", "dimau1_dimau", "udvudv", "dimau1_homes");

            // Проверим - удалось ли установить соединение
            if (mysqli_connect_error()) {
                // TODO: сохранить в лог ошибку подключения к БД: ('Ошибка подключения к базе данных (' . mysqli_connect_errno() . ') ' . mysqli_connect_error())
                // TODO: сделать красивую страницу тех поддержки, на которую перенаправлять пользователя, если с БД связи нет
                return FALSE;
            }

            // Устанавливаем кодировку
            if (!$mysqli->set_charset("utf8")) {
                // TODO: сохранить в лог ошибку изменения кодировки БД
            }

            // Если объект соединения с БД получен - вернем его в качестве результата работы конструктора
            return $mysqli;
        }

        // Функция закрывает соединение с БД
        public static function closeConnectToDB() {

            // Если соединения не было, то и закрывать нечего
            if (self::$connect === FALSE || self::$connect === NULL) return TRUE;

            if (self::$connect->close()) {

                return TRUE;

            } else {

                // TODO: сохранить в лог ошибку закрытия соединения с БД
                return FALSE;

            }

        }

        // Конструктор не используется (но чтобы его нельзя было вызвать снаружи защищен модификатором private), так как он возвращает объект класса DBconnect, а мне в переменной $connect нужен объект класса mysqli
        private function __construct() {}

    }
