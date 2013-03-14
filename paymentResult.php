<?php
/* На данный URL приходят запросы от сервиса приема оплаты с данными по оплаченным пользователями счетам */

// Подключаем нужные модели и представления
$websiteRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/models/User.php';
require_once $websiteRoot . '/models/UserFull.php';
require_once $websiteRoot . '/models/Payment.php';

// Создаем объект для работы с оплатой
$payment = new Payment();

// Удалось ли подключиться к БД?
if (!isset($payment) || DBconnect::get() == FALSE) {
    Payment::returnRepeatLater();
    Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД или получения объекта класса Payment. id логгера: paymentResult.php:1.");
    exit();
}

// Получим POST параметры оплаты
$payment->readPaymentFromPOST();

// Проверим на формат и полноту поступившие параметры сообщения об оплате
if (!$payment->validateResultParams()) {
    Logger::getLogger(GlobFunc::$loggerName)->log("Данные об оплате, полученные от сервиса приема платежей не соответствуют параметрам заданным в настройках. id логгера: paymentResult.php:2");
    Payment::returnRepeatLater();
    DBconnect::closeConnectToDB();
    exit();
}

// Проверяем - был ли оплачен ранее счет с таким id
// Если оплата с указанным id уже поступала и мы ее зачислили, то сообщаем статус ОК сервису оплаты
if ($payment->isPreviouslyPaid()) {
    Payment::returnSuccessStatus();
    DBconnect::closeConnectToDB();
    exit();
}

// Инициализируем модель пользователя, от которого поступила оплата
$user = new UserFull($payment->getUserId());
if (!$user->readCharacteristicFromDB()) {
    Logger::getLogger(GlobFunc::$loggerName)->log("Не удалось инициализировать пользователя. id логгера: paymentResult.php:3, id пользователя: " . $payment->getUserId());
    Payment::returnRepeatLater();
    DBconnect::closeConnectToDB();
    exit();
}

// Назначаем права пользователю
switch ($payment->getPurchase()) {
    case "reviewFull1d": // Премиум-доступ на 1 сутки
        $value = 1;
        break;
    case "reviewFull10d": // Премиум-доступ на 10 дней
        $value = 10;
        break;
    case "reviewFull30d": // Премиум-доступ на 30 дней
        $value = 30;
        break;
    default:
        Logger::getLogger(GlobFunc::$loggerName)->log("Пользователь оплатил неизвестный тариф. id логгера: paymentResult.php:4, id пользователя: '" . $payment->getUserId() . "' тариф: '" . $payment->getPurchase() . "'");
        Payment::returnRepeatLater();
        DBconnect::closeConnectToDB();
        exit();
}

if (!$user->setUserRights($value)) {
    Logger::getLogger(GlobFunc::$loggerName)->log("Не удалось изменить права пользователя. id логгера: paymentResult.php:5, id пользователя: " . $payment->getUserId() . "' тариф: '" . $payment->getPurchase() . "'");
    Payment::returnRepeatLater();
    DBconnect::closeConnectToDB();
    exit();
}

// Оповещаем пользователя
if (!GlobFunc::sendSMS($user->getTelephon(), "Платный доступ к порталу Svobodno.org активирован")) {
    Logger::getLogger(GlobFunc::$loggerName)->log("Не удалось оповестить пользователя по смс об активации платного доступа. id логгера: paymentResult.php:6, id пользователя: " . $payment->getUserId() . "' тариф: '" . $payment->getPurchase() . "'");
}

// Сохраняем данные по поступившей оплате
$payment->setDateOfPayment(time()); // В качестве времени успешной обработки оплаты устанавливаем текущее
if (!$payment->saveParamsToDB()) {
    Logger::getLogger(GlobFunc::$loggerName)->log("Данные об оплате не сохранены в БД. id логгера: paymentResult.php:7, id пользователя: " . $payment->getUserId() . "' тариф: '" . $payment->getPurchase() . "'");
    Payment::returnSuccessStatus();
    DBconnect::closeConnectToDB();
    exit();
}

// Уведомление сервиса оплаты об успешном приеме и обработке сообщения
Payment::returnSuccessStatus();

// Закрываем соединение с БД
DBconnect::closeConnectToDB();