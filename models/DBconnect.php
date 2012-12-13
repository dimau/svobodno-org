<?php
/**
 * Статический класс для работы с БД (практически синглтон, содержащий единственный на весь скрипт объект соединения с Базой данных)
 * Правила работы с БД:
 * 1. Только данный класс должен напрямую работать с БД. Служит оберткой над БД. Остальные классы получают и записывают данные в БД с помощью методов DBconnect
 * 2. Цель данного класса как обертки над БД - скрывать структуру БД
 * 3. Данный класс самостоятельно выполняет преобразваония данных из формата хранения в БД в формат, с которым работает проект и наоборот.
 */

class DBconnect
{
	private static $connect; // Cодержит объект соединения с базой данных класса mysqli (единственный на весь скрипт)

	public static function get() {
		if (self::$connect === NULL) { // Если соединение с БД еще не устанавливалось
			self::$connect = self::connectToDB(); // Создаем объект соединения с БД
		}

		return self::$connect; // Возвращаем объект соединения с БД. Либо FALSE, если установить соединение не удалось
	}

	// Метод отрабатывает один раз при вызове DBconnect::get();
	// Метод возвращает объект соединения с БД (mysqli), лиюо FALSE
	private static function connectToDB() {
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

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одной из фотографий. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - временный идентификатор сессии загрузки фотографий ($fileUploadId)
	public static function selectPhotosForFileUploadId($fileUploadId) {

		// Проверка входящих параметров
		if (!isset($fileUploadId) || $fileUploadId == "") return array();

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM tempFotos WHERE fileUploadId = ?") === FALSE)
			OR ($stmt->bind_param("s", $fileUploadId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM tempFotos WHERE fileUploadId=" . $fileUploadId . "'. id логгера: DBconnect::selectPhotosForFileUploadId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одной из фотографий. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - идентификатор пользователя, чьи фотографии нужно получить (точнее данные по его фотографиям)
	public static function selectPhotosForUser($userId) {

		// Проверка входящих параметров
		if (!isset($userId) || !is_int($userId)) return array();

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM userFotos WHERE userId = ?") === FALSE)
			OR ($stmt->bind_param("i", $userId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			// Логируем ошибку
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM userFotos WHERE userId=" . $userId . "'. Местонахождение кода: DBconnect::selectPhotosForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		return $res;
	}

	// Функция возвращает массив ассоциированных массивов с данными о фотографиях объекта недвижимости. В случае отсутствия фотографий, а также в случае получения ошибки возвращает пустой массив.
	// На входе - идентификатор объекта недвижимости, по которому нужно получить фотографии
	public static function selectPhotosForProperty($propertyId) {

		// Проверка входящих параметров
		if (!isset($propertyId) || !is_int($propertyId)) return array();

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM propertyFotos WHERE propertyId = ?") === FALSE)
			OR ($stmt->bind_param("i", $propertyId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM propertyFotos WHERE propertyId = " . $propertyId . "'. Местонахождение кода: DBconnect::selectPhotosForProperty():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одной из заявок. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - идентификатор объекта недвижимости, либо массив идентификаторов объектов недвижимости, по которым нужно найти все заявки на просмотр
	public static function selectRequestsToViewForProperties($propertiesId) {

		// Проверка входящих параметров
		if (!isset($propertiesId)) return array();
		if (is_array($propertiesId) && count($propertiesId) == 0) return array();

		// Если нам на вход дали единичный идентификатор, то приведем его к виду массива
		if (!is_array($propertiesId)) $propertiesId = array($propertiesId);

		// Для надежности преобразование к целому типу членов массива и их проверка
		for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
			$propertiesId[$i] = intval($propertiesId[$i]);
			if ($propertiesId[$i] == 0) return array(); // Если преобразование дало 0, значит один из членов массива не является идентификатором объекта недвижимости - входные данные некорректны
		}

		// Соберем условие для получения данных из БД
		$strWHERE = " (";
		for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
			$strWHERE .= " propertyId = '" . $propertiesId[$i] . "'";
			if ($i < $s - 1) $strWHERE .= " OR";
		}
		$strWHERE .= " )";

		// Получаем данные из БД
		$res = DBconnect::get()->query("SELECT * FROM requestToView WHERE" . $strWHERE . " ORDER BY status DESC");
		if ((DBconnect::get()->errno)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			return array();
		}

		// Вернем результат
		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одной из заявок. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - статус, все заявки, имеющие который, будут находиться в выборке
	public static function selectRequestsToViewForStatus($status) {
		// Проверка входящих параметров
		$allPermittedStatuses = array("Новая", "Ошибка при отправке", "Назначен просмотр", "Отложена", "Объект уже сдан", "Отказ собственника", "Отменена", "Безуспешный просмотр", "Успешный просмотр");
		if (!isset($status) || !in_array($status, $allPermittedStatuses)) return array();

		// TODO: Выбирем сортировку результатов - в зависимости от статуса

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM requestToView WHERE status = ? ORDER BY id DESC") === FALSE)
			OR ($stmt->bind_param("s", $status) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM requestToView WHERE status = '" . $status . "' ORDER BY id DESC'. Местонахождение кода: DBconnect::selectRequestsToViewForStatus():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		// Если получить данные из БД удалось - вернем их
		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одной из заявок. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - id арендатора или массив id арендаторов, все заявки которых необходимо получить
	public static function selectRequestsToViewForTenants($tenantsId) {
		// Проверка входящих параметров
		if (!isset($tenantsId)) return array();
		if (is_array($tenantsId) && count($tenantsId) == 0) return array();

		// Если нам на вход дали единичный идентификатор, то приведем его к виду массива
		if (!is_array($tenantsId)) $tenantsId = array($tenantsId);

		// Для надежности преобразование к целому типу членов массива и их проверка
		for ($i = 0, $s = count($tenantsId); $i < $s; $i++) {
			$tenantsId[$i] = intval($tenantsId[$i]);
			if ($tenantsId[$i] == 0) return array(); // Если преобразование дало 0, значит один из членов массива не является идентификатором пользователя - входные данные некорректны
		}

		// Соберем условие для получения данных из БД
		$strWHERE = " (";
		for ($i = 0, $s = count($tenantsId); $i < $s; $i++) {
			$strWHERE .= " tenantId = '" . $tenantsId[$i] . "'";
			if ($i < $s - 1) $strWHERE .= " OR";
		}
		$strWHERE .= " )";

		// Получаем данные из БД
		$res = DBconnect::get()->query("SELECT * FROM requestToView WHERE" . $strWHERE . " ORDER BY status DESC");
		if ((DBconnect::get()->errno)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			return array();
		}

		// Вернем результат
		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одной из заявок. Если ничего не найдено или произошла ошибка, вернет пустой массив
	public static function selectRequestsFromOwners() {

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM requestFromOwners ORDER BY regDate DESC") === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM requestFromOwners ORDER BY regDate DESC'. Местонахождение кода: DBconnect::selectRequestsFromOwners():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		// Если получить данные из БД удалось - вернем их
		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из уведомлений. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - id уведомления
	public static function selectMessageNewPropertyForId($messageId) {

		// Валидация входящих данных
		if (!isset($messageId) || !is_int($messageId)) return array();

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM messagesNewProperty WHERE id = ?") === FALSE)
			OR ($stmt->bind_param("i", $messageId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM messagesNewProperty WHERE id = " . $messageId . "'. Местонахождение кода: DBconnect::selectMessageNewPropertyForId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		// Преобразование данных для удобной работы с ними
		for ($i = 0, $s = count($res); $i < $s; $i++) {
			$res[$i]['fotoArr'] = unserialize($res[$i]['fotoArr']);
		}

		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из уведомлений. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - id пользователя, чьи уведомления мы хотим получить
	public static function selectMessagesNewPropertyForUser($userId) {

		// Проверка входящих параметров
		if (!isset($userId) || !is_int($userId)) return array();

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM messagesNewProperty WHERE userId = ? ORDER BY isReaded ASC, timeIndex DESC") === FALSE)
			OR ($stmt->bind_param("i", $userId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM messagesNewProperty WHERE userId = " . $userId . " ORDER BY isReaded ASC, timeIndex DESC'. Местонахождение кода: DBconnect::selectMessagesNewPropertyForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		// Преобразование данных для удобной работы с ними
		for ($i = 0, $s = count($res); $i < $s; $i++) {
			$res[$i]['fotoArr'] = unserialize($res[$i]['fotoArr']);
		}

		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из уведомлений. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// Необязательный параметр на входе - максимальное кол-во уведомлений, которые мы хотим получить за одно обращение
	public static function selectMessagesForEmail($limit = 100) {

		// Проверка входящих параметров
		if (isset($limit) && !is_int($limit)) return array();

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM messagesNewProperty WHERE needEmail = 1 LIMIT ?") === FALSE)
			OR ($stmt->bind_param("i", $limit) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM messagesNewProperty WHERE needEmail = 1 LIMIT ".$limit."'. Местонахождение кода: DBconnect::selectMessagesNewPropertyForEmail():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		return $res;
	}

	// Сохраняет данные о фотографии в таблицу временного хранения tempFotos
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function insertPhotoForFileUploadId($paramsArr) {

		// Проверка входящих параметров
		if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

		// Сохраняем информацию о загруженной фотке в БД
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("INSERT INTO tempFotos (id, fileUploadId, folder, filename, extension, filesizeMb, regDate) VALUES (?,?,?,?,?,?,?)") === FALSE)
			OR ($stmt->bind_param("sssssdi", $paramsArr['id'], $paramsArr['fileUploadId'], $paramsArr['folder'], $paramsArr['filename'], $paramsArr['extension'], $paramsArr['filesizeMb'], $paramsArr['regDate']) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($res === 0)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO tempFotos (id, fileUploadId, folder, filename, extension, filesizeMb, regDate) VALUES (" . $paramsArr['id'] . "," . $paramsArr['fileUploadId'] . "," . $paramsArr['folder'] . "," . $paramsArr['filename'] . "," . $paramsArr['extension'] . "," . $paramsArr['filesizeMb'] . "," . $paramsArr['regDate'] . ")'. id логгера: DBconnect::insertPhotoForFileUploadId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Сохраняет данные о новом уведомлении в БД. Если передан второй аргумент - массив идентификаторов арендаторов, то уведомление будет сохранено для каждого из них
	 *
	 * @param $paramsArr параметры уведомления
	 * @param $listOfTargetUsers массив, содержащий идентификаторы пользователей, для каждого из которых нужно сформировать данное уведомление
	 * @return bool TRUE в случае успеха и FALSE в случае неудачи
	 */
	public static function insertMessageNewProperty($paramsArr, $listOfTargetUsers) {

		// Проверка входящих параметров
		if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;
		if (isset($listOfTargetUsers) && (!is_array($listOfTargetUsers) || count($listOfTargetUsers) == 0)) return FALSE;

		// Подготовка данных к записи в БД
		$paramsArr['fotoArr'] = serialize($paramsArr['fotoArr']);

		// Если у нас целый массив арендаторов, для которых нужно сформировать уведомление
		if (isset($listOfTargetUsers)) {

			// Инициализируем переменные, в которую поочередно будем складывать id и статусы оповещения пользователей из списка
			$currentTargetUser = 0;
			$currentNeedEmail = 0;
			$currentNeedSMS = 0;

			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("INSERT INTO messagesNewProperty (userId, timeIndex, messageType, isReaded, fotoArr, targetId, needEmail, needSMS, typeOfObject, address, currency, costOfRenting, utilities, electricPower, amountOfRooms, adjacentRooms, amountOfAdjacentRooms, roomSpace, totalArea, livingSpace, kitchenSpace, totalAmountFloor, numberOfFloor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
				OR ($stmt->bind_param("iisssiiisssssssssssssii", $currentTargetUser, $paramsArr['timeIndex'], $paramsArr['messageType'], $paramsArr['isReaded'], $paramsArr['fotoArr'], $paramsArr['targetId'], $currentNeedEmail, $currentNeedSMS, $paramsArr['typeOfObject'], $paramsArr['address'], $paramsArr['currency'], $paramsArr['costOfRenting'], $paramsArr['utilities'], $paramsArr['electricPower'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['amountOfAdjacentRooms'], $paramsArr['roomSpace'], $paramsArr['totalArea'], $paramsArr['livingSpace'], $paramsArr['kitchenSpace'], $paramsArr['totalAmountFloor'], $paramsArr['numberOfFloor']) === FALSE)
			) {
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO messagesNewProperty (userId, timeIndex, messageType, isReaded, fotoArr, targetId, needEmail, needSMS, typeOfObject, address, currency, costOfRenting, utilities, electricPower, amountOfRooms, adjacentRooms, amountOfAdjacentRooms, roomSpace, totalArea, livingSpace, kitchenSpace, totalAmountFloor, numberOfFloor) VALUES (" . $currentTargetUser . ", " . $paramsArr['timeIndex'] . ", " . $paramsArr['messageType'] . ", " . $paramsArr['isReaded'] . ", " . $paramsArr['fotoArr'] . ", " . $paramsArr['targetId'] . ", " . $paramsArr['needEmail'] . ", " . $paramsArr['needSMS'] . ", " . $paramsArr['typeOfObject'] . ", " . $paramsArr['address'] . ", " . $paramsArr['currency'] . ", " . $paramsArr['costOfRenting'] . ", " . $paramsArr['utilities'] . ", " . $paramsArr['electricPower'] . ", " . $paramsArr['amountOfRooms'] . ", " . $paramsArr['adjacentRooms'] . ", " . $paramsArr['amountOfAdjacentRooms'] . ", " . $paramsArr['roomSpace'] . ", " . $paramsArr['totalArea'] . ", " . $paramsArr['livingSpace'] . ", " . $paramsArr['kitchenSpace'] . ", " . $paramsArr['totalAmountFloor'] . ", " . $paramsArr['numberOfFloor'] . ")'. id логгера: DBconnect::insertMessageNewProperty():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
				return FALSE;
			}

			for ($i = 0, $s = count($listOfTargetUsers); $i < $s; $i++) {

				// Подставляем новый идентификатор пользователя и его статусы оповещения
				$currentTargetUser = $listOfTargetUsers[$i]['userId'];
				$currentNeedEmail = $listOfTargetUsers[$i]['needEmail'];
				$currentNeedSMS = $listOfTargetUsers[$i]['needSMS'];

				// Записываем в БД для него уведомление про новый объект недвижимости
				if (($stmt->execute() === FALSE)
					OR (($res = $stmt->affected_rows) === -1)
					OR ($res === 0)
				) {
					Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO messagesNewProperty (userId, timeIndex, messageType, isReaded, fotoArr, targetId, needEmail, needSMS, typeOfObject, address, currency, costOfRenting, utilities, electricPower, amountOfRooms, adjacentRooms, amountOfAdjacentRooms, roomSpace, totalArea, livingSpace, kitchenSpace, totalAmountFloor, numberOfFloor) VALUES (" . $currentTargetUser . ", " . $paramsArr['timeIndex'] . ", " . $paramsArr['messageType'] . ", " . $paramsArr['isReaded'] . ", " . $paramsArr['fotoArr'] . ", " . $paramsArr['targetId'] . ", " . $currentNeedEmail . ", " . $currentNeedSMS . ", " . $paramsArr['typeOfObject'] . ", " . $paramsArr['address'] . ", " . $paramsArr['currency'] . ", " . $paramsArr['costOfRenting'] . ", " . $paramsArr['utilities'] . ", " . $paramsArr['electricPower'] . ", " . $paramsArr['amountOfRooms'] . ", " . $paramsArr['adjacentRooms'] . ", " . $paramsArr['amountOfAdjacentRooms'] . ", " . $paramsArr['roomSpace'] . ", " . $paramsArr['totalArea'] . ", " . $paramsArr['livingSpace'] . ", " . $paramsArr['kitchenSpace'] . ", " . $paramsArr['totalAmountFloor'] . ", " . $paramsArr['numberOfFloor'] . ")'. id логгера: DBconnect::insertMessageNewProperty():2. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
					// Продолжаем работу по формированию уведомлений, не обращая внимание, что для одного из пользователей выполнить операцию не удалось - ничего страшного
				}
			}
			$stmt->close();

		} else {

			// Сохраняем только 1 уведомление для 1 пользователя
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("INSERT INTO messagesNewProperty (userId, timeIndex, messageType, isReaded, fotoArr, targetId, needEmail, needSMS, typeOfObject, address, currency, costOfRenting, utilities, electricPower, amountOfRooms, adjacentRooms, amountOfAdjacentRooms, roomSpace, totalArea, livingSpace, kitchenSpace, totalAmountFloor, numberOfFloor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
				OR ($stmt->bind_param("iisssiiisssssssssssssii", $paramsArr['userId'], $paramsArr['timeIndex'], $paramsArr['messageType'], $paramsArr['isReaded'], $paramsArr['fotoArr'], $paramsArr['targetId'], $paramsArr['needEmail'], $paramsArr['needSMS'], $paramsArr['typeOfObject'], $paramsArr['address'], $paramsArr['currency'], $paramsArr['costOfRenting'], $paramsArr['utilities'], $paramsArr['electricPower'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['amountOfAdjacentRooms'], $paramsArr['roomSpace'], $paramsArr['totalArea'], $paramsArr['livingSpace'], $paramsArr['kitchenSpace'], $paramsArr['totalAmountFloor'], $paramsArr['numberOfFloor']) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($res === 0)
				OR ($stmt->close() === FALSE)
			) {
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO messagesNewProperty (userId, timeIndex, messageType, isReaded, fotoArr, targetId, needEmail, needSMS, typeOfObject, address, currency, costOfRenting, utilities, electricPower, amountOfRooms, adjacentRooms, amountOfAdjacentRooms, roomSpace, totalArea, livingSpace, kitchenSpace, totalAmountFloor, numberOfFloor) VALUES (" . $paramsArr['userId'] . ", " . $paramsArr['timeIndex'] . ", " . $paramsArr['messageType'] . ", " . $paramsArr['isReaded'] . ", " . $paramsArr['fotoArr'] . ", " . $paramsArr['targetId'] . ", " . $paramsArr['needEmail'] . ", " . $paramsArr['needSMS'] . ", " . $paramsArr['typeOfObject'] . ", " . $paramsArr['address'] . ", " . $paramsArr['currency'] . ", " . $paramsArr['costOfRenting'] . ", " . $paramsArr['utilities'] . ", " . $paramsArr['electricPower'] . ", " . $paramsArr['amountOfRooms'] . ", " . $paramsArr['adjacentRooms'] . ", " . $paramsArr['amountOfAdjacentRooms'] . ", " . $paramsArr['roomSpace'] . ", " . $paramsArr['totalArea'] . ", " . $paramsArr['livingSpace'] . ", " . $paramsArr['kitchenSpace'] . ", " . $paramsArr['totalAmountFloor'] . ", " . $paramsArr['numberOfFloor'] . ")'. id логгера: DBconnect::insertMessageNewProperty():3. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
				return FALSE;
			}
		}

		return TRUE;
	}

	// Сохраняет в БД новое значение одного из статусов пользователя (typeTenant или typeOwner) - TRUE или FALSE
	// Получает $type ("typeTenant" или "typeOwner") и value ("TRUE" или "FALSE")
	public static function updateUserCharacteristicTypeUser($userId, $type, $value) {

		// Валидация входящих данных
		if (!isset($userId) || !is_int($userId) || !isset($type) || !isset($value) || ($type != "typeTenant" && $type != "typeOwner") || ($value != "TRUE" && $value != "FALSE")) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("UPDATE users SET " . $type . " = '" . $value . "' WHERE id = ?") === FALSE)
			OR ($stmt->bind_param("i", $userId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE users SET " . $type . " = '" . $value . "' WHERE id = " . $userId . "'. id логгера: DBconnect::updateUserCharacteristicTypeUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Обновляет параметры уведомления класса "Новое подходящее объявление" в БД
	public static function updateMessageNewProperty($paramsArr) {

		// Проверка входящих параметров
		if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

		// Подготовка данных к записи в БД
		$paramsArr['fotoArr'] = serialize($paramsArr['fotoArr']);

		// Сохраняем информацию о загруженной фотке в БД
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("UPDATE messagesNewProperty SET userId = ?, timeIndex = ?, messageType = ?, isReaded = ?, fotoArr = ?, targetId = ?, needEmail = ?, needSMS = ?, typeOfObject = ?, address = ?, currency = ?, costOfRenting = ?, utilities = ?, electricPower = ?, amountOfRooms = ?, adjacentRooms = ?, amountOfAdjacentRooms = ?, roomSpace = ?, totalArea = ?, livingSpace = ?, kitchenSpace = ?, totalAmountFloor = ?, numberOfFloor = ? WHERE id = ?") === FALSE)
			OR ($stmt->bind_param("iisssiiisssssssssssssiii", $paramsArr['userId'], $paramsArr['timeIndex'], $paramsArr['messageType'], $paramsArr['isReaded'], $paramsArr['fotoArr'], $paramsArr['targetId'], $paramsArr['needEmail'], $paramsArr['needSMS'], $paramsArr['typeOfObject'], $paramsArr['address'], $paramsArr['currency'], $paramsArr['costOfRenting'], $paramsArr['utilities'], $paramsArr['electricPower'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['amountOfAdjacentRooms'], $paramsArr['roomSpace'], $paramsArr['totalArea'], $paramsArr['livingSpace'], $paramsArr['kitchenSpace'], $paramsArr['totalAmountFloor'], $paramsArr['numberOfFloor'], $paramsArr['id']) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($res === 0)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE messagesNewProperty SET userId = " . $paramsArr['userId'] . ", timeIndex = " . $paramsArr['timeIndex'] . ", messageType = " . $paramsArr['messageType'] . ", isReaded = " . $paramsArr['isReaded'] . ", fotoArr = " . $paramsArr['fotoArr'] . ", targetId = " . $paramsArr['targetId'] . ", needEmail = ". $paramsArr['needEmail'].", needSMS = " . $paramsArr['needSMS'] . ", typeOfObject = " . $paramsArr['typeOfObject'] . ", address = " . $paramsArr['address'] . ", currency = " . $paramsArr['currency'] . ", costOfRenting = " . $paramsArr['costOfRenting'] . ", utilities = " . $paramsArr['utilities'] . ", electricPower = " . $paramsArr['electricPower'] . ", amountOfRooms = " . $paramsArr['amountOfRooms'] . ", adjacentRooms = " . $paramsArr['adjacentRooms'] . ", amountOfAdjacentRooms = " . $paramsArr['amountOfAdjacentRooms'] . ", roomSpace = " . $paramsArr['roomSpace'] . ", totalArea = " . $paramsArr['totalArea'] . ", livingSpace = " . $paramsArr['livingSpace'] . ", kitchenSpace = " . $paramsArr['kitchenSpace'] . ", totalAmountFloor = " . $paramsArr['totalAmountFloor'] . ", numberOfFloor = " . $paramsArr['numberOfFloor'] . "'. id логгера: DBconnect::updateMessageNewProperty():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Удаляет все фотографии, загруженные по временному идентификатору сессии загрузки фотографий ($fileUploadId)
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function deletePhotosForFileUploadId($fileUploadId) {

		// Валидация входный данных
		if (!isset($fileUploadId) || $fileUploadId == "") return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM tempFotos WHERE fileUploadId = ?") === FALSE)
			OR ($stmt->bind_param("s", $fileUploadId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'. id логгера: DBconnect::deletePhotosForFileUploadId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Удаляет поисковый запрос конкретного пользователя из БД
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function deleteSearchRequestsForUser($userId) {

		// Валидация входный данных
		if (!isset($userId) || !is_int($userId)) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM searchRequests WHERE userId = ?") === FALSE)
			OR ($stmt->bind_param("i", $userId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM searchRequests WHERE userId = " . $userId . "'. id логгера: DBconnect::deleteSearchRequestsForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Удаляет все заявки на просмотр соответствующего пользователя (с $tenantId) по всем статусам кроме "Успешный просмотр"
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function deleteRequestsToViewForTenant($tenantId) {

		// Валидация входный данных
		if (!isset($tenantId) || !is_int($tenantId)) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM requestToView WHERE tenantId = ? AND status != 'Успешный просмотр'") === FALSE)
			OR ($stmt->bind_param("i", $tenantId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM requestToView WHERE tenantId = " . $tenantId . " AND status != 'Успешный просмотр''. id логгера: DBconnect::deleteRequestsToViewForTenant():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Удаляет заявку от собственника по ее id
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function deleteRequestFromOwnerForId($requestFromOwnerId) {

		// Валидация входных данных
		if (!isset($requestFromOwnerId) || !is_int($requestFromOwnerId)) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM requestFromOwners WHERE id = ?") === FALSE)
			OR ($stmt->bind_param("i", $requestFromOwnerId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM requestFromOwners WHERE id = " . $requestFromOwnerId . "'. id логгера: DBconnect::deleteRequestFromOwnerForId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Удаляет все уведомления о новом подходящем объекте недвижимости из БД, предназначенные для пользователя $userId
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function deleteMessagesNewPropertyForUser($userId) {

		// Валидация входный данных
		if (!isset($userId) || !is_int($userId)) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM messagesNewProperty WHERE userId = ?") === FALSE)
			OR ($stmt->bind_param("i", $userId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM messagesNewProperty WHERE userId = " . $userId . "'. id логгера: DBconnect::deleteMessagesNewPropertyForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Удаляет все уведомления о новом подходящем объекте недвижимости из БД, касающиеся объекта $targetId
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function deleteMessagesNewPropertyForProperty($targetId) {

		// Валидация входных данных
		if (!isset($targetId) || !is_int($targetId)) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM messagesNewProperty WHERE targetId = ?") === FALSE)
			OR ($stmt->bind_param("i", $targetId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM messagesNewProperty WHERE targetId = " . $targetId . "'. id логгера: DBconnect::deleteMessagesNewPropertyForProperty():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Удаляет уведомление о новом подходящем объекте недвижимости из БД по id
	public static function deleteMessageNewPropertyForId($messageId) {
		// Валидация входных данных
		if (!isset($messageId) || !is_int($messageId)) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM messagesNewProperty WHERE id = ?") === FALSE)
			OR ($stmt->bind_param("i", $messageId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM messagesNewProperty WHERE id = " . $messageId . "'. id логгера: DBconnect::deleteMessageNewPropertyForId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	// Возвращает число = кол-ву всех непрочитанных уведомлений пользователя
	public static function countUnreadMessagesForUser($userId) {

		// Валидация входных параметров
		if (!isset($userId) || !is_int($userId)) return 0;

		$res = DBconnect::get()->query("SELECT COUNT(*) FROM messagesNewProperty WHERE userId = '" . $userId . "' AND isReaded = 'не прочитано'");
		if ((DBconnect::get()->errno)
			OR (($res = $res->fetch_row()) === NULL)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT COUNT(*) FROM messagesNewProperty WHERE userId = '" . $userId . "' AND isReaded = 'не прочитано''. id логгера: DBconnect::countUnreadMessagesForUser():1. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID пользователя: не определено");
			return 0;
		}

		return $res[0];
	}

	// Функция возвращает подробные сведения по объектам недвижимости из БД
	// В случае ошибки возвращает FALSE, елси данные получить не удалось, то пустой массив
	// На входе - отсортированный массив id объектов недвижимости
	// $mode - режим работы. "all" - выдать данные по всем объектам (вне зависимости опубликованы они или нет), "published" - выдать данные только по опубликованным объектам
	// На выходе - отсортированный в том же порядке массив ассоциативных массивов, каждый из которых содержит все параметры одного объекта, в том числе его фотографии
	public static function getFullDataAboutProperties($propertiesId, $mode) {
		// Проверка входного массива
		if (!isset($propertiesId) || !is_array($propertiesId)) return FALSE;

		// Сколько всего объектов интересует
		$limit = count($propertiesId);
		// Если 0, возвращаем пустой массив
		if ($limit == 0) return array();

		// Собираем строку WHERE для поискового запроса к БД по полным данным для не более чем 20-ти первых объектов
		$strWHERE = " (";
		for ($i = 0; $i < $limit; $i++) {
			$strWHERE .= " id = '" . $propertiesId[$i] . "'";
			if ($i < $limit - 1) $strWHERE .= " OR";
		}
		$strWHERE .= ")";

		// Если требуется режим получения данных только по опубликованным объектам, то реализуем его
		if ($mode == "published") $strWHERE .= " AND (status = 'опубликовано')";

		// Узнаем анкетные данные о наших объектах
		$res = DBconnect::get()->query("SELECT * FROM property WHERE" . $strWHERE);
		if ((DBconnect::get()->errno)
			OR (($propertyFullArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			$propertyFullArr = array();
		}

		// Упорядочим полученные результаты из БД в том порядке, в котором во входящем массиве $propertiesId были указаны соответствующие id объектов недвижимости
		$tempArr = array();
		for ($i = 0; $i < $limit; $i++) {
			foreach ($propertyFullArr as $value) {
				if ($propertiesId[$i] == $value['id']) {
					$tempArr[] = $value;
					break;
				}
			}
		}
		$propertyFullArr = $tempArr;

		// Получим данные о фотографиях для каждого объекта из $propertyFullArr
		for ($i = 0, $s = count($propertyFullArr); $i < $s; $i++) {
			// Получим данные о фотографиях по id объекта недвижимости
			$propertyFotos = DBconnect::selectPhotosForProperty($propertyFullArr[$i]['id']);
			// Записываем полученный массив массивов с данными о фотографиях в специальный новый параметр массива $propertyFullArr
			$propertyFullArr[$i]['propertyFotos'] = $propertyFotos;
		}

		return $propertyFullArr;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из пользователей. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - идентификатор пользователя, либо массив идентификаторов пользователей, по которым нужно получить данные
	// ВНИМАНИЕ: массивы могут быть расположены не в том же порядке, в каком идентификаторы располагались во входном массиве
	public static function getAllDataAboutCharacteristicUsers($usersId) {

		// Проверка входящих параметров
		if (!isset($usersId)) return array();
		if (is_array($usersId) && count($usersId) == 0) return array();

		// Если нам на вход дали единичный идентификатор, то приведем его к виду массива
		if (!is_array($usersId)) $usersId = array($usersId);

		// Для надежности преобразование к целому типу членов массива и их проверка
		for ($i = 0, $s = count($usersId); $i < $s; $i++) {
			$usersId[$i] = intval($usersId[$i]);
			if ($usersId[$i] == 0) return array(); // Если преобразование дало 0, значит один из членов массива не является идентификатором объекта недвижимости - входные данные некорректны
		}

		// Соберем условие для получения данных из БД
		$strWHERE = " (";
		for ($i = 0, $s = count($usersId); $i < $s; $i++) {
			$strWHERE .= " id = '" . $usersId[$i] . "'";
			if ($i < $s - 1) $strWHERE .= " OR";
		}
		$strWHERE .= " )";

		// Получаем данные из БД
		$res = DBconnect::get()->query("SELECT * FROM users WHERE" . $strWHERE);
		if ((DBconnect::get()->errno)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			return array();
		}

		// Вернем результат
		return $res;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из объектов недвижимости. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - идентификатор объекта недвижимости, либо массив идентификаторов объектов недвижимости, по которым нужно получить данные
	// ВНИМАНИЕ: массивы могут быть расположены не в том же порядке, в каком идентификаторы располагались во входном массиве
	public static function getAllDataAboutCharacteristicProperties($propertiesId) {

		// Проверка входящих параметров
		if (!isset($propertiesId)) return array();
		if (is_array($propertiesId) && count($propertiesId) == 0) return array();

		// Если нам на вход дали единичный идентификатор, то приведем его к виду массива
		if (!is_array($propertiesId)) $propertiesId = array($propertiesId);

		// Для надежности преобразование к целому типу членов массива и их проверка
		for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
			$propertiesId[$i] = intval($propertiesId[$i]);
			if ($propertiesId[$i] == 0) return array(); // Если преобразование дало 0, значит один из членов массива не является идентификатором объекта недвижимости - входные данные некорректны
		}

		// Соберем условие для получения данных из БД
		$strWHERE = " (";
		for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
			$strWHERE .= " id = '" . $propertiesId[$i] . "'";
			if ($i < $s - 1) $strWHERE .= " OR";
		}
		$strWHERE .= " )";

		// Получаем данные из БД
		$res = DBconnect::get()->query("SELECT * FROM property WHERE" . $strWHERE);
		if ((DBconnect::get()->errno)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			return array();
		}

		// Вернем результат
		return $res;
	}

	/*public function selectUsersCharacteristicFull() {
	}
	public function selectUsersFoto() {
	} */

	// Конструктор не используется (но чтобы его нельзя было вызвать снаружи защищен модификатором private), так как он возвращает объект класса DBconnect, а мне в переменной $connect нужен объект класса mysqli
	private function __construct() {
	}
}
