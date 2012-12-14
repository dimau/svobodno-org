<?php
/* Сценарий периодически пробегает уведомления и формирует+отправляет по тем из них, у которых проставлен признак необходимости отправки e-mail, соответствующие электронные письма */

// Подключаем необходимые модели, классы
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/class.phpmailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/View.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';

// Получаем id объекта недвижимости, рассылку о котором нужно выполнить
if (isset($_POST['propertyId']) && intval($_POST['propertyId']) != 0) {
	$propertyId = intval($_POST['propertyId']);
} else {
	// TODO: Запишем в лог сбой
	exit();
}

// Инициализируем модель для этого объекта недвижимости
$property = new Property($propertyId);
if (!$property->readCharacteristicFromDB() || !$property->writeFotoInformationFromDB()) {
	// TODO: Запишем в лог сбой
	exit();
}

// Получим список пользователей-арендаторов, под чьи поисковые запросы подходит этот объект
$listOfTargetUsers = $property->whichTenantsAppropriate();
if ($listOfTargetUsers == FALSE) {
	// TODO: Запишем в лог сбой
	exit();
}

// Формируем уведомления по данному объекту
$property->sendMessagesAboutNewProperty($listOfTargetUsers);

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

