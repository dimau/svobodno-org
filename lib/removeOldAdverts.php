<?php
/**
 * Сценарий запускается автоматически каждый день
 * Задача - выявить объявления из чужих баз, которые опубликованы на портале уже более 3-х дней и не имеют ни одной открытой заявки на просмотр (со статусом: Новая, Назначен просмотр, Отложена, Успешный просмотр)
 * Выявленные устаревшие объявления (скорее всего они уже сданы) необходимо также автоматически переносить в архив (типа удалять из рабочей базы объектов недвижимости)
 * При этом объявления, относящиеся к нашим собственникам, висят опубликованным на портале столько, сколько нужно для их сдачи в аренду
 *
 * Порядок действий:
 * 1. Получить из БД все данные по ЧУЖИМ объявлениям, ОПУБЛИКОВАННЫМ РАНЕЕ 3-Х ДНЕЙ
 *
 *
 */

// Подключаем необходимые модели, классы
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/class.phpmailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';



// Инициализируем модель для этого объекта недвижимости
$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB() || !$property->readFotoInformationFromDB()) {
	Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось выполнить считывание данных из БД по объекту:".$propertyId);
	exit();
}

// Получим список пользователей-арендаторов, под чьи поисковые запросы подходит этот объект
$listOfTargetUsers = $property->whichTenantsAppropriate();
if ($listOfTargetUsers == FALSE) {
	Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось получить список id потенциальных арендаторов по объекту:".$propertyId);
	exit();
}

// Формируем уведомления по данному объекту
if (!$property->sendMessagesAboutNewProperty($listOfTargetUsers)) {
	Logger::getLogger(GlobFunc::$loggerName)->log("notificationAboutNewProperty.php: не удалось сформировать уведомления для потенциальных арендаторов по объекту:".$propertyId);
	exit();
}

// Формируем и рассылаем email по тем пользователям, которые подписаны на такую рассылку
$listOfTargetUsersForEmail = array();
foreach ($listOfTargetUsers as $value) {
	if ($value['needEmail'] = 1) $listOfTargetUsersForEmail[] = $value;
}
$property->sendEmailAboutNewProperty($listOfTargetUsersForEmail);


// Получим максимум 100 уведомлений для обработки - обработка уведомлений порционно позволяет снизить негативный эффект в случае повторной обработки (если вдруг запустится второй экземпляр скрипта пока работает первый)
/*$messages = DBconnect::selectMessagesForEmail(100);
$amountMessages = count($messages);

// Если отправлять нечего, то прекращает выполнение скрипта
if ($amountMessages == 0) exit();

// Инициализируем класс для отправки e-mail и указываем постоянные параметры (верные для любых уведомлений)
$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
try {
	$mail->SetFrom('support@svobodno.org', 'Svobodno.org');
	$mail->AddReplyTo('support@svobodno.org', 'support');
	//$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
} catch (phpmailerException $e) {
	echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
	echo $e->getMessage(); //Boring error messages from anything else!
}

// Обрабатываем каждое уведомление индивидуально
foreach ($messages as $message) {

	if ($message['messageType'] == "newProperty") {
		$MsgHTML = View::getHTMLforMessageNewProperty($message);
	}

	// Отправка очередного e-mail
	try {
		$mail->AddAddress('dimau777@gmail.com', 'Ushakov');
		$mail->Subject = 'Новое объявление: ';
		$mail->MsgHTML($MsgHTML);
		$mail->Send();
	} catch (phpmailerException $e) {
		echo $e->errorMessage(); //Pretty error messages from PHPMailer
	} catch (Exception $e) {
		echo $e->getMessage(); //Boring error messages from anything else!
	}
} */

