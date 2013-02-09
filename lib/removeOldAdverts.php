<?php
/**
 * Сценарий должен запускаться автоматически каждый день с помощью cron
 * Задача - выявить объявления из чужих баз, которые опубликованы на портале уже более 3-х дней 7-ми дней или 14-ти дней (в зависимости от их стоимости) и не имеют ни одной открытой заявки на просмотр (со статусом: Новая, Назначен просмотр, Отложена, Успешный просмотр)
 * Выявленные устаревшие объявления (скорее всего они уже сданы) необходимо снимать с публикации и переносить в архив (типа удалять из рабочей базы объектов недвижимости)
 * При этом объявления, относящиеся к нашим собственникам, висят опубликованным на портале столько, сколько нужно для их сдачи в аренду
 */

// Подключаем необходимые модели, классы
if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "") $websiteRoot = $_SERVER['DOCUMENT_ROOT']; else $websiteRoot = "/var/www/dimau/data/www/svobodno.org"; // так как cron не инициализирует переменную окружения $_SERVER['DOCUMENT_ROOT'] (а точнее инициализирует ее пустой строкой), приходиться использовать костыль
require_once $websiteRoot . '/lib/class.phpmailer.php';
require_once $websiteRoot . '/models/DBconnect.php';
require_once $websiteRoot . '/models/GlobFunc.php';
require_once $websiteRoot . '/models/Logger.php';
require_once $websiteRoot . '/models/Property.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) {
    Logger::getLogger(GlobFunc::$loggerName)->log("removeOldAdverts.php:1 Ошибка инициализации соединения с БД:");
    exit();
}

// Считаем время жизни для разных категорий объявлений
$cheap = time() - (3 * 24 * 60 * 60); // 3 дня для объявлений дешевле 29 000 рублей
$medium = time() - (7 * 24 * 60 * 60); // 7 дней для объявлений от 29 000 - 49 000 рублей
$expensive = time() - (14 * 24 * 60 * 60); // 14 дней для объявлений от 49 000 рублей и выше

// Получаем полные данные по интересующим нас объявлениям из БД
$stmt = DBconnect::get()->stmt_init();
if (($stmt->prepare("SELECT * FROM property WHERE completeness = '0' AND ((realCostOfRenting <= 29000 AND reg_date < ?) OR (realCostOfRenting > 29000 AND realCostOfRenting <= 49000 AND reg_date < ?) OR (realCostOfRenting > 49000 AND reg_date < ?))") === FALSE)
    OR ($stmt->bind_param("iii", $cheap, $medium, $expensive) === FALSE)
    OR ($stmt->execute() === FALSE)
    OR (($res = $stmt->get_result()) === FALSE)
    OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
    OR ($stmt->close() === FALSE)
) {
    //TODO: перенести в DBконнект и переделать строку логгирования
    Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM property WHERE completeness = '0' AND ((realCostOfRenting <= 29000 AND reg_date < " . $cheap . ") OR (realCostOfRenting > 29000 AND realCostOfRenting <= 49000 AND reg_date < " . $medium . ") OR (realCostOfRenting > 49000 AND reg_date < " . $expensive . "))'. id логгера: removeOldAdverts.php:2. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
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

$subject = 'Удаление устаревших объявлений';
$msgHTML = "Найдено и перенесено в архив " . count($res) . " устаревших чужих объявлений";

GlobFunc::sendEmailToOperator($subject, $msgHTML);

/********************************************************************************
 * Закрываем соединение с БД
 *******************************************************************************/

DBconnect::closeConnectToDB();