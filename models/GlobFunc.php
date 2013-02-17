<?php
/* Статический класс, содержащий набор статических методов, часто используемых в самых разных местах серверных скриптов */

class GlobFunc {
    public static $loggerName = "test"; // Название логера (а также и название файла, в который сохраняется лог)
    // ВАЖНО: если изменяешь название логгера ($loggerName), то необходимо создать файл с ровно таким же именем и расширением .log в каталоге logs (корень проекта)

    // КОНСТРУКТОР
    public function __construct() {
    }

    // ДЕСТРУКТОР
    public function __destruct() { }

    // Функция для скрытия реального id пользователя при передаче в GET параметрах
    public static function idToCompId($id) {
        return $id * 5 + 2;
    }

    // Возвращает реальный id пользователя из compId
    public static function compIdToId($compId) {
        return ($compId - 2) / 5;
    }

    //Функция для генерации случайной строки
    public static function generateCode($length = 6) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";

        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }

        return $code;
    }

    // Преобразовывает дату из формата, пригодного для хранения в БД в формат, пригодный для отображения
    public static function dateFromDBToView($dateFromDB) {
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
    public static function dateFromViewToDB($dateFromView) {
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
    public static function getFirstCharUpper($str) {
        $enc = 'utf-8';
        return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc) . mb_substr($str, 1, mb_strlen($str, $enc), $enc);
    }

    // Функция вычисляет возраст по дате рождения. Пример: echo calculate_age('27.01.2012');
    public static function calculate_age($birthday) {
        if (!isset($birthday) || $birthday == "" || $birthday == "00.00.0000") return "";

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

    // Отправляет e-mail на захардкоденные служебные адреса
    public static function sendEmailToOperator($subject, $msgHTML) {

        // Список служебных адресов для рассылки
        $emails = array("support@svobodno.org");

        // Подключаем класс для отправки e-mail, если он ранее еще не был подключен
        if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "") $websiteRoot = $_SERVER['DOCUMENT_ROOT']; else $websiteRoot = "/var/www/dimau/data/www/svobodno.org"; // так как cron не инициализирует переменную окружения $_SERVER['DOCUMENT_ROOT'] (а точнее инициализирует ее пустой строкой), приходиться использовать костыль
        require_once $websiteRoot . '/lib/class.phpmailer.php';

        // Готовим и отправляем e-mail
        $mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
        try {
            $mail->CharSet = "utf-8";
            $mail->SetFrom('support@svobodno.org', 'Svobodno.org Служебная');
            $mail->AddReplyTo('support@svobodno.org', 'Svobodno.org');
            $mail->Subject = $subject;
            $mail->MsgHTML($msgHTML);
            $mail->ClearAddresses();
            foreach ($emails as $email) {
                $mail->AddAddress($email);
            }
            $mail->Send();
        } catch (phpmailerException $e) {
            Logger::getLogger(GlobFunc::$loggerName)->log("GlobFunc::sendEmailToOperator:1 Ошибка при формировании e-mail: " . $e->errorMessage() . " Текст сообщения: " . $msgHTML); //Pretty error messages from PHPMailer
            return FALSE;
        } catch (Exception $e) {
            Logger::getLogger(GlobFunc::$loggerName)->log("GlobFunc::sendEmailToOperator:2 Ошибка при формировании e-mail: " . $e->getMessage() . " Текст сообщения: " . $msgHTML); //Boring error messages from anything else!
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Отправляет смс на указанный номер с указанным содержанием
     * @param string $phoneNumber номер телефона, на который нужно отправить смс, без 8-ки, например: 9221431615
     * @param string $msgText текст смс сообщения
     * @return bool TRUE в случае успешной отправки и FALSE в противном случае
     */
    public static function sendSMS($phoneNumber, $msgText) {

        // Валидация входных параметров
        if (!isset($phoneNumber) || !isset($msgText)) return FALSE;

        // Инициализация библиотеки curl.
        if (!($ch = curl_init())) {
            Logger::getLogger(GlobFunc::$loggerName)->log("GlobFunc::sendSMS():1 Не удалось инициализировать библиотеку curl");
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HEADER, false); // При значении true CURL включает в вывод результата заголовки, которые нам не нужны (мы их на сервере не обрабатываем).
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // При значении = true полученный код страницы возвращается как результат выполнения curl_exec.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Максимальное время ожидания ответа от сервера в секундах
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); // Установим значение поля User-agent для маскировки под обычного пользователя

        // Заменим пробелы на плюсы для передачи текста в GET запросе на сервис рассылки смс (смсАэро)
        $msgText = str_replace(" ", "+", $msgText);

        // Готовим параметры для отправки смс
        $user = "dimau777@gmail.com";
        $password = md5("udvudvudv5H");
        $from = "Svobodno";
        $to = "7" . $phoneNumber;
        $url = "http://gate.smsaero.ru/send/?user=" . $user . "&password=" . $password . "&to=" . $to . "&from=" . $from . "&text=" . $msgText;

        // Выполнение запроса
        curl_setopt($ch, CURLOPT_URL, $url); // Устанавливаем URL запроса
        $data = curl_exec($ch);

        // Проверка на успех отправки смс. Смс считается успешно отправленной, если получен ответ вида: "123456=accepted"
        if ($data == FALSE || strpos($data, "accepted") === FALSE) {
            Logger::getLogger(GlobFunc::$loggerName)->log("GlobFunc::sendSMS():2 Не удалось отправить смс, используя адрес: " . $url . " Ответ сервера: " . $data);
            curl_close($ch);
            return FALSE;
        }

        // Особождение ресурса
        curl_close($ch);

        // Задача успешно выполнена
        return TRUE;
    }

    // Вспомогательная функция для AJAX запросов - отказ в доступе
    public static function accessDenied() {
        header('Content-Type: text/xml; charset=UTF-8');
        echo "<xml><span status='denied'></span></xml>";
        exit();
    }

}