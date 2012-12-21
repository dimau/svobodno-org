<?php
/**
 * Сценарий должен запускаться автоматически каждый день с помощью cron
 * Задача - выявить объявления из чужих баз, которые опубликованы на портале уже более 3-х дней и не имеют ни одной открытой заявки на просмотр (со статусом: Новая, Назначен просмотр, Отложена, Успешный просмотр)
 * Выявленные устаревшие объявления (скорее всего они уже сданы) необходимо снимать с публикации и переносить в архив (типа удалять из рабочей базы объектов недвижимости)
 * При этом объявления, относящиеся к нашим собственникам, висят опубликованным на портале столько, сколько нужно для их сдачи в аренду
 *
 */

// Подключаем необходимые модели, классы
if (!isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == "") $_SERVER['DOCUMENT_ROOT'] = "/var/www/dimau/data/www/svobodno.org"; // так как cron не инициализирует переменную окружения $_SERVER['DOCUMENT_ROOT'] (а точнее инициализирует ее пустой строкой), приходиться использовать костыль
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/class.phpmailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/DBconnect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/GlobFunc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Property.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) {
	Logger::getLogger(GlobFunc::$loggerName)->log("removeOldAdverts.php:1 Ошибка инициализации соединения с БД:");
	exit();
}

// Если объявление было зарегистрировано ранее $oldestActualTimeStamp, то оно подпадает под угрозу переноса в архив
$oldestActualTimeStamp = time() - (3 * 24 * 60 * 60);

// Получаем полные данные по интересующим нас объявлениям из БД
$stmt = DBconnect::get()->stmt_init();
if (($stmt->prepare("SELECT * FROM property WHERE completeness = '0' AND reg_date < ? AND 0 = (SELECT COUNT(*) FROM requestToView WHERE property.id = requestToView.propertyId AND (status = 'Новая' OR status = 'Назначен просмотр' OR status = 'Отложена' OR status = 'Успешный просмотр') LIMIT 1)") === FALSE)
	OR ($stmt->bind_param("i", $oldestActualTimeStamp) === FALSE)
	OR ($stmt->execute() === FALSE)
	OR (($res = $stmt->get_result()) === FALSE)
	OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
	OR ($stmt->close() === FALSE)
) {
	//TODO: перенести в DBконнект и переделать строку логгирования
	Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM property WHERE completeness = '0' AND reg_date < ". $oldestActualTimeStamp ." AND 0 < (SELECT COUNT(*) FROM requestToView WHERE property.id = requestToView.propertyId AND (status = 'Новая' OR status = 'Назначен просмотр' OR status = 'Отложена' OR status = 'Успешный просмотр') LIMIT 1)'. id логгера: :1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
	//return array();
	exit();
}

// Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
for ($i = 0, $s = count($res); $i < $s; $i++) {
	$res[$i] = DBconnect::conversionPropertyCharacteristicFromDBToView($res[$i]);
}

// Для каждого полученного объявления создаем объект и переносим его в архивную таблицу ("удаляем") как положено
foreach ($res as $propertyArr) {
	$property = new Property($propertyArr);
	$property->unpublishAdvert();
}

/********************************************************************************
 * Оповещаем руководство об успешном выполнении операции очистки
 *******************************************************************************/

$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
$MsgHTML = "Найдено и перенесено в архив ".count($res)." устаревших чужих объявлений";
try {
	$mail->CharSet = "utf-8";
	$mail->SetFrom('support@svobodno.org', 'Svobodno.org');
	$mail->AddReplyTo('support@svobodno.org', 'Svobodno.org');
	$mail->Subject = 'Удаление устаревших объявлений';
	$mail->MsgHTML($MsgHTML);
	$mail->AddAddress("dimau777@gmail.com");
	$mail->Send();
} catch (phpmailerException $e) {
	Logger::getLogger(GlobFunc::$loggerName)->log("removeOldAdverts.php:1 Ошибка при формировании e-mail:".$e->errorMessage()."Текст сообщения:".$MsgHTML); //Pretty error messages from PHPMailer
	return FALSE;
} catch (Exception $e) {
	Logger::getLogger(GlobFunc::$loggerName)->log("removeOldAdverts.php:2 Ошибка при формировании e-mail:".$e->getMessage()."Текст сообщения:".$MsgHTML); //Boring error messages from anything else!
	return FALSE;
}

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();