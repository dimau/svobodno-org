<?php

    class GlobFunc
    {

        private $DBlink = FALSE; // Переменная для хранения объекта соединения с базой данных
        public $loggerName = "test"; // Название логера (а также и название файла, в который сохраняется лог)
        // ВАЖНО: если изменяешь название логгера ($loggerName), то необходимо создать файл с ровно таким же именем и расширением .log в каталоге logs (корень проекта)

        // КОНСТРУКТОР
        public function __construct()
        {

        }

        // ДЕСТРУКТОР
        public function __destruct()
        {

        }

        // Функция устанавливает соединение с БД и возвращает объект соединение в случае успеха. Если установить соединение не удалось - возвращает FALSE
        public function connectToDB() {
            // Устанавливаем соединение с базой данных и сохраняем его в объект $mysqli
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

            // Если объект соединения с БД получен - сделаем его доступным для всех методов класса
            $this->DBlink = $mysqli;

            // Возвращаем объект - соединение с БД
            return $mysqli;

        }

        // Функция закрывает соединение с БД
        public function closeConnectToDB($DBlink = FALSE) {
            if ($DBlink == FALSE) return FALSE;

            if ($DBlink->close()) {

                return TRUE;

            } else {

                // TODO: сохранить в лог ошибку закрытия соединения с БД
                return FALSE;

            }

        }

        //Функция для генерации случайной строки
        public function generateCode($length = 6)
        {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
            $code = "";

            $clen = strlen($chars) - 1;
            while (strlen($code) < $length) {
                $code .= $chars[mt_rand(0, $clen)];
            }

            return $code;
        }

        // Функция возвращает массив массивов с названиями районов в городе $city
        public function getAllDistrictsInCity($city) {

            // Получим из БД данные ($res) по пользователю с логином = $login
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("SELECT name FROM districts WHERE city=? ORDER BY name ASC") === FALSE)
                OR ($stmt->bind_param("s", $city) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->get_result()) === FALSE)
                OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                OR ($stmt->close() === FALSE)
            ) {
                $res = array();
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
            }

            return $res;
        }

        // Преобразовывает дату из формата, пригодного для хранения в БД в формат, пригодный для отображения
        public function dateFromDBToView($dateFromDB)
        {
            $date = substr($dateFromDB, 8, 2);
            $month = substr($dateFromDB, 5, 2);
            $year = substr($dateFromDB, 0, 4);
            return $date . "." . $month . "." . $year;
        }

        // Преобразовывает дату из формата, пригодного для отображения в формат, пригодный для хранения в БД
        public function dateFromViewToDB($dateFromView)
        {
            $date = substr($dateFromView, 0, 2);
            $month = substr($dateFromView, 3, 2);
            $year = substr($dateFromView, 6, 4);
            return $year . "." . $month . "." . $date;
        }

        // Функция делает первый символ строки в верхнем регистре
        function getFirstCharUpper($str) {
            $enc = 'utf-8';
            return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc).mb_substr($str, 1, mb_strlen($str, $enc), $enc);
        }

        // Функция вычисляет возраст по дате рождения. Пример: echo calculate_age('27.01.2012');
        function calculate_age($birthday) {

            // Дата рождения
            $dateOfBorn = substr($birthday, 0, 2);
            // Месяц рождения
            $monthOfBorn = substr($birthday, 3, 2);
            // Год рождения
            $yearOfBorn = substr($birthday, 6, 4);

            // Вычислим разницу с текущим годом
            $age = date('Y') - $yearOfBorn;

            // Если день рождения еще не прошел в этом году, то уменьшим возраст на 1
            if (date('m') < $monthOfBorn || (date('m') == $monthOfBorn && date('d') < $dateOfBorn)) {
                $age--;
            }

            return $age;
        }

    }