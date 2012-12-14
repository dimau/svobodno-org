<?php
    /* Статический класс, содержащий набор статических методов, часто используемых в самых разных местах серверных скриптов */

    class GlobFunc
    {
        public static $loggerName = "test"; // Название логера (а также и название файла, в который сохраняется лог)
        // ВАЖНО: если изменяешь название логгера ($loggerName), то необходимо создать файл с ровно таким же именем и расширением .log в каталоге logs (корень проекта)

        // КОНСТРУКТОР
        public function __construct()
        {
        }

        // ДЕСТРУКТОР
        public function __destruct()
        {}

		// Функция для скрытия реального id пользователя при передаче в GET параметрах
		public static function idToCompId($id) {
			return $id * 5 + 2;
		}

		// Возвращает реальный id пользователя из compId
		public static function compIdToId($compId) {
			return ($compId - 2) / 5;
		}

        //Функция для генерации случайной строки
        public static function generateCode($length = 6)
        {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
            $code = "";

            $clen = strlen($chars) - 1;
            while (strlen($code) < $length) {
                $code .= $chars[mt_rand(0, $clen)];
            }

            return $code;
        }

        // Преобразовывает дату из формата, пригодного для хранения в БД в формат, пригодный для отображения
        public static function dateFromDBToView($dateFromDB)
        {
			if (!isset($dateFromDB) || $dateFromDB == "" || $dateFromDB == "0000-00-00") return "";

            $date = substr($dateFromDB, 8, 2);
            $month = substr($dateFromDB, 5, 2);
            $year = substr($dateFromDB, 0, 4);

			// Валидация чисел
			if ($date < "01" || $date > "31" || $month < "01" || $month > "12" || $year < "1800" || $year > "2100") return "";

			// Если все хорошо - возвращаем нормальную дату для сохранения в БД
            return $date . "." . $month . "." . $year;
        }

        // Преобразовывает дату из формата, пригодного для отображения в формат, пригодный для хранения в БД
        public static function dateFromViewToDB($dateFromView)
        {
			if (!isset($dateFromView) || $dateFromView == "") return "0000-00-00";

            $date = substr($dateFromView, 0, 2);
            $month = substr($dateFromView, 3, 2);
            $year = substr($dateFromView, 6, 4);

			// Валидация чисел
			if ($date < "01" || $date > "31" || $month < "01" || $month > "12" || $year < "1800" || $year > "2100") return "0000-00-00";

			// Если все хорошо - возвращаем нормальную дату для сохранения в БД
            return $year . "." . $month . "." . $date;
        }

		// Преобразовывает дату из формата таймстамп, пригодного для хранения в БД в формат, пригодный для отображения
		public static function timestampFromDBToView($timestamp) {
			return date('d-m-Y G:i', $timestamp);
		}

        // Функция делает первый символ строки в верхнем регистре
        public static function getFirstCharUpper($str)
        {
            $enc = 'utf-8';
            return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc) . mb_substr($str, 1, mb_strlen($str, $enc), $enc);
        }

        // Функция вычисляет возраст по дате рождения. Пример: echo calculate_age('27.01.2012');
        public static function calculate_age($birthday)
        {
			if (!isset($birthday) || $birthday == "" || $birthday = "00.00.0000") return "";

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

		// Вспомогательная функция для AJAX запросов - отказ в доступе
		public static function accessDenied() {
			header('Content-Type: text/xml; charset=UTF-8');
			echo "<xml><span status='denied'></span></xml>";
			exit();
		}

    }