<?php

/**
 * Класс хранит данные и методы для работы с оплатой, поступающей от клиентов
 * Схема работы:
 * 1. На страницах, имеющих кнопку оплаты, формируем каждый раз спец. форму, соответствующую требованиям сервиса оплаты (со скрытыми инпутами, содержащими необходимые данные по оплате)
 * 2. Если пользователь действительно кликнет на эту спец. форму (а точнее на кнопку оплаты, содержащуююся в этой спец. форме), то его перебрасывает на страницу сервиса оплаты
 * 3. После совершения платежа, сервис оплаты сообщает мне на сервер на специальный адрес о статусе платежа. Идентифицировать платеж я могу с помощью моих дополнительных параметров: идентификатора пользователя, признаков оплаты выбранных им услуг. Кроме того, я могу проверить сумму оплаты.
 * 4. Если все в порядке - ключаю данному пользователю соответствующие права доступа на сайте и запоминаю номер счета (чтобы в следующий раз не провести его повторно)
 */

class Payment {

    private static $genuineAccountId = "185864873196"; // Идентификатор моего кошелька в Единой кассе
    private static $secretKey = "Mbdhcy562jmdlpnacu783gbdjsYRnVdb4SV0"; // Уникальный секретный ключ
    private static $paymentSuccessURL = "http://svobodno.org/paymentSuccess.php"; // Адрес страницы, на которую нужно перебрасывать пользователя при успешной оплате
    private static $paymentFailURL = "http://svobodno.org/paymentFail.php"; // Адрес страницы, на которую нужно перебрасывать пользователя при безуспешной оплате

    private $accountId = "";
    private $signature = "";

    private $number = "";
    private $userId = ""; // Id пользователя, который произвел данную оплату
    private $status = "";
    private $cost = "";
    private $purchase = ""; // Тариф доступа к порталу, приобретенный пользователем по данной оплате
    private $dateOfPayment = ""; // Дата и время успешной обработки оплаты в формате таймстамп

    /**
     * КОНСТРУКТОР
     */
    public function __construct() { }

    public function getUserId() {
        return $this->userId;
    }

    public function getPurchase() {
        return $this->purchase;
    }

    // Выставляем дату поступления оплаты
    public function setDateOfPayment($dateOfPayment) {
        $this->dateOfPayment = $dateOfPayment;
    }

    // Инициализирует объект параметрами оплаты, которые пришли в POST запросе от сервиса приема платежей
    public function readPaymentFromPOST() {

        // Идентификатор моей компании в сервисе оплаты
        if (isset($_POST['WMI_MERCHANT_ID'])) $this->accountId = $_POST['WMI_MERCHANT_ID'];
        // Сколько пользователь денег реально перечислил
        if (isset($_POST['WMI_PAYMENT_AMOUNT'])) $this->cost = intval($_POST['WMI_PAYMENT_AMOUNT']);
        // Уникальный идентификатор счета (оплаты)
        if (isset($_POST['WMI_PAYMENT_NO'])) $this->number = $_POST['WMI_PAYMENT_NO'];
        // Статус оплаты (успешно/безуспешно)
        if (isset($_POST['WMI_ORDER_STATE'])) {
            if ($_POST['WMI_ORDER_STATE'] == "Accepted") $this->status = "оплачен"; else $this->status = $_POST['WMI_ORDER_STATE'];
        }
        // Подпись сервера сервиса оплаты для проверки достоверности запроса
        if (isset($_POST['WMI_SIGNATURE'])) $this->signature = $_POST['WMI_SIGNATURE'];
        // Идентификатор пользователя, который произвел оплату
        if (isset($_POST['userId'])) $this->userId = intval($_POST['userId']);
        // Тариф доступа к порталу, приобретенный пользователем по данной оплате
        if (isset($_POST['purchase'])) $this->purchase = $_POST['purchase'];

        return TRUE;
    }

    // Проверяет полученные от сервиса оплаты платежей параметры оплаты на полноту, формат и достоверность
    public function validateResultParams() {

        // Массив для хранения сообщений об ошибках
        $errors = array();

        if ($this->accountId != self::$genuineAccountId) $errors[] = "Не совпадает accountId, указанный в платеже и genuineAccountId, accountId='" . $this->accountId . "'";
        if ($this->status != "оплачен") $errors[] = "Статус счета не = 'Accepted' или 'оплачен', статус счета = '" . $this->status . "'";
        // TODO: добавить проверку подписи, суммы, purchase

        // Возвращаем результат проверки
        if (count($errors) == 0) {
            return TRUE;
        } else {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибки при проверке параметров оплаты. id логгера: Payment.php->validateResultParams():1, " . json_encode($errors));
            return FALSE;
        }
    }

    // Проверяет - оплачен ли был этот счет ранее, если да вернет TRUE, иначе FALSE
    public function isPreviouslyPaid() {
        $res = DBconnect::selectInvoiceForNumber($this->number);
        if (count($res) != 0 && $res['status'] == "оплачен") return TRUE; else return FALSE;
    }

    // Сохраняет параметры объекта (данные о конкретной оплате) в БД
    public function saveParamsToDB() {
        return DBconnect::insertInvoice($this->getPaymentData());
    }

    /**
     * Возвращает ответ серверу сервиса оплаты о невозможности обработки сообщения в текущий момент времени (требуется повторить запрос позже)
     * Сервер временно не доступен (например, не удается связаться с БД)
     */
    public static function returnRepeatLater() {
        echo "WMI_RESULT=RETRY&WMI_DESCRIPTION=Сервер временно недоступен";
        return TRUE;
    }

    // Возвращает ответ сервису оплаты об успешной обработке сообщения и платежа
    public static function returnSuccessStatus() {
        echo "WMI_RESULT=OK&WMI_DESCRIPTION=Order successfully processed";
        return TRUE;
    }

    //TODO: доделать!
    public function generateSignature() {

        // Собираем поля формы в ассоциативный массив
        $fields = array();
        $fields["WMI_MERCHANT_ID"] = "119175088534";
        $fields["WMI_PAYMENT_AMOUNT"] = "100.00";
        $fields["WMI_CURRENCY_ID"] = "643";
        $fields["WMI_PAYMENT_NO"] = "12345-001";
        $fields["WMI_DESCRIPTION"] = "BASE64:" . base64_encode("Payment for order #12345-001 in MYSHOP.com");
        $fields["WMI_EXPIRED_DATE"] = "2019-12-31T23:59:59";
        $fields["WMI_SUCCESS_URL"] = "https://myshop.com/w1/success.php";
        $fields["WMI_FAIL_URL"] = "https://myshop.com/w1/fail.php";
        $fields["MyShopParam1"] = "Value1"; // Дополнительные параметры
        $fields["MyShopParam2"] = "Value2"; // интернет-магазина тоже участвуют
        $fields["MyShopParam3"] = "Value3"; // при формировании подписи!

        //Сортировка значений внутри полей
        foreach ($fields as $name => $val) {
            if (is_array($val)) {
                usort($val, "strcasecmp");
                $fields[$name] = $val;
            }
        }

        // Формирование сообщения, путем объединения значений формы, отсортированных по именам ключей в порядке возрастания.
        uksort($fields, "strcasecmp");
        $fieldValues = "";

        foreach ($fields as $value) {
            if (is_array($value)) foreach ($value as $v) {
                //Конвертация из текущей кодировки (UTF-8)
                //необходима только если кодировка магазина отлична от Windows-1251
                $v = iconv("utf-8", "windows-1251", $v);
                $fieldValues .= $v;
            } else {
                //Конвертация из текущей кодировки (UTF-8)
                //необходима только если кодировка магазина отлична от Windows-1251
                $value = iconv("utf-8", "windows-1251", $value);
                $fieldValues .= $value;
            }
        }

        // Формирование значения параметра WMI_SIGNATURE, путем
        // вычисления отпечатка, сформированного выше сообщения,
        // по алгоритму MD5 и представление его в Base64
        $signature = base64_encode(pack("H*", md5($fieldValues . $key)));

        //Добавление параметра WMI_SIGNATURE в словарь параметров формы
        $fields["WMI_SIGNATURE"] = $signature;

        // Формирование HTML-кода платежной формы
        print "<form action=\"https://merchant.w1.ru/checkout/default.aspx\" method=\"POST\">";

        foreach ($fields as $key => $val) {
            if (is_array($val)) foreach ($val as $value) {
                print "$key: <input type=\"text\" name=\"$key\" value=\"$value\"/><br>";
            } else
                print "$key: <input type=\"text\" name=\"$key\" value=\"$val\"/><br>";
        }

        print "<input type=\"submit\"/></form>";

    }

    // Возвращает ассоциативный массив с данными об оплате
    public function getPaymentData() {

        $result = array();

        $result['number'] = $this->number;
        $result['userId'] = $this->userId;
        $result['status'] = $this->status;
        $result['cost'] = $this->cost;
        $result['purchase'] = $this->purchase;
        $result['dateOfPayment'] = $this->dateOfPayment;

        return $result;
    }

}
