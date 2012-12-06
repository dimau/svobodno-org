<?php
/* Класс представляем собой полную модель объекта недвижимости (объявления) */

class Property
{
	public $typeOfObject = "0";
	public $dateOfEntry = "";
	public $termOfLease = "0";
	public $dateOfCheckOut = "";
	public $amountOfRooms = "0";
	public $adjacentRooms = "0";
	public $amountOfAdjacentRooms = "0";
	public $typeOfBathrooms = "0";
	public $typeOfBalcony = "0";
	public $balconyGlazed = "0";
	public $roomSpace = "";
	public $totalArea = "";
	public $livingSpace = "";
	public $kitchenSpace = "";
	public $floor = "";
	public $totalAmountFloor = "";
	public $numberOfFloor = "";
	public $concierge = "0";
	public $intercom = "0";
	public $parking = "0";
	public $city = "Екатеринбург";
	public $district = "0";
	public $coordX = "";
	public $coordY = "";
	public $address = "";
	public $apartmentNumber = "";
	public $subwayStation = "0";
	public $distanceToMetroStation = "";
	public $currency = "0";
	public $costOfRenting = "";
	public $utilities = "0";
	public $costInSummer = "";
	public $costInWinter = "";
	public $electricPower = "0";
	public $bail = "0";
	public $bailCost = "";
	public $prepayment = "0";
	public $compensationMoney = "";
	public $compensationPercent = "";
	public $repair = "0";
	public $furnish = "0";
	public $windows = "0";
	public $internet = "0";
	public $telephoneLine = "0";
	public $cableTV = "0";
	public $furnitureInLivingArea = array();
	public $furnitureInLivingAreaExtra = "";
	public $furnitureInKitchen = array();
	public $furnitureInKitchenExtra = "";
	public $appliances = array();
	public $appliancesExtra = "";
	public $sexOfTenant = array();
	public $relations = array();
	public $children = "0";
	public $animals = "0";
	public $contactTelephonNumber = "";
	public $timeForRingBegin = "0";
	public $timeForRingEnd = "0";
	public $checking = "0";
	public $responsibility = "";
	public $comment = "";
	public $earliestDate = "";
	public $earliestTimeHours = "";
	public $earliestTimeMinutes = "";
	public $adminComment = "";
	public $completeness = "";

	public $realCostOfRenting = "";
	public $last_act = "";
	public $reg_date = "";
	public $status = "";
	public $id = "";
	public $userId = "";

	public $fileUploadId = "";
	public $uploadedFoto = array(); // В переменной будет храниться информация о загруженных фотографиях. Представляет собой массив ассоциированных массивов
	public $primaryFotoId = "";

	public $ownerLogin = ""; // Параметр содержит логин пользователя-собственника (необходим для того, чтобя выездные агенты могли создавать новые объявления и присваивать их ране зарегистрированным собственникам)

	// КОНСТРУКТОР
	public function __construct($propertyId = FALSE) {
		// Инициализируем переменную "сессии" для временного сохранения фотографий
		$this->fileUploadId = GlobFunc::generateCode(7);

		// Если конструктору передан идентификатор объекта недвижимости, запишем его в параметры объекта. Это позволит, например, инициализировать объект данными из БД
		if ($propertyId != FALSE) $this->id = $propertyId;
	}

	// ДЕСТРУКТОР
	public function __destruct() {
	}

	// Устанавливает признак полноты для объекта
	// $levelCompleteness = "0" объявление из чужой базы - мнимум требований к полноте
	// $levelCompleteness = "1" объявление от собственника, который является нашим клиентом - максимальные требования к полноте
	public function setCompleteness($levelCompleteness) {
		// Проверка входных данных на адекватность
		if ($levelCompleteness != "1" && $levelCompleteness != "0") return FALSE;
		$this->completeness = $levelCompleteness;
		return TRUE;
	}

	// Функция сохраняет текущие параметры объекта недвижимости в БД
	// $typeOfProperty = "new" - режим сохранения для нового объекта недвижимости
	// $typeOfProperty = "edit" - режим сохранения для редактируемых параметров объекта недвижимости
	// Кроме того, при успешной работе изменяет статус typeOwner пользователя (с id = userId) на TRUE
	// Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
	public function saveCharacteristicToDB($typeOfProperty = "edit") {
		// Валидация необходимых исходных данных
		if ($typeOfProperty != "new" && $typeOfProperty != "edit") return FALSE; // Если объявление не является ни новым, ни существующим - видимо какая-то ошибка была допущена при передаче параметров методу
		if ($typeOfProperty == "new" && $this->ownerLogin == "") return FALSE;
		if ($typeOfProperty == "edit" && $this->userId == "") return FALSE;

		// Вычислим id пользователя-собственника данного объекта недвижимости (если создается не новое объявление, а идет редактирование ранее созданного)
		if ($typeOfProperty == "edit") {
			$userId = $this->userId;
		}

		// Вычислим id пользователя-собственника данного объекта недвижимости (если создается новое объявление выездным специалистом со своего аккаунта)
		if ($typeOfProperty == "new") {

			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("SELECT id FROM users WHERE login=?") === FALSE)
				OR ($stmt->bind_param("s", $this->ownerLogin) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->get_result()) === FALSE)
				OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				return FALSE;
			}

			if (!is_array($res) || count($res) != 1) {
				return FALSE;
			}

			$userId = $res[0]['id'];
		}

		// Если не указан id пользователя собственника, то дальнейшие действия не имеют смысла
		if ($userId == "") return FALSE;

		// Корректируем даты для того, чтобы сделать их пригодными для сохранения в базу данных
		$dateOfEntryForDB = GlobFunc::dateFromViewToDB($this->dateOfEntry);
		$dateOfCheckOutForDB = GlobFunc::dateFromViewToDB($this->dateOfCheckOut);
		$earliestDateForDB = GlobFunc::dateFromViewToDB($this->earliestDate);

		// Для хранения массивов в БД, их необходимо сериализовать
		$furnitureInLivingAreaSerialized = serialize($this->furnitureInLivingArea);
		$furnitureInKitchenSerialized = serialize($this->furnitureInKitchen);
		$appliancesSerialized = serialize($this->appliances);
		$sexOfTenantSerialized = serialize($this->sexOfTenant);
		$relationsSerialized = serialize($this->relations);

		// Довычисляем значения переменных, которые понадобятся при сохранении новой записи в БД или при ее модификации
		$tm = time();
		$last_act = $tm; // время последнего редактирования объявления
		$reg_date = $tm; // время регистрации ("рождения") объявления
		if ($this->status != "опубликовано" && $this->status != "не опубликовано") { // На всякий случай (возможно, будет полезно для нового объявления) будем проверять заполненность поля со статусом
			$this->status = "опубликовано";
		}
		if ($this->completeness != "0" && $this->completeness != "1") { // На всякий случай (возможно, будет полезно для нового объявления) будем проверять заполненность поля с признаком полноты данных об объекте
			$this->completeness = "1";
		}

		// Проверяем в какой валюте сохраняется стоимость аренды, формируем переменную realCostOfRenting
		if ($this->currency == 'руб.') $realCostOfRenting = $this->costOfRenting;
		if ($this->currency != 'руб.') {
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("SELECT value FROM currencies WHERE name=?") === FALSE)
				OR ($stmt->bind_param("s", $this->currency) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->get_result()) === FALSE)
				OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				return FALSE;
			}

			$realCostOfRenting = $this->costOfRenting * $res[0]['value'];
		}

		// Пишем данные объекта недвижимости в БД.
		// Код для сохранения данных разный: для нового объявления и при редактировании параметров существующего объявления
		if ($typeOfProperty == "new") {
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("INSERT INTO property SET userId=?, typeOfObject=?, dateOfEntry=?, termOfLease=?, dateOfCheckOut=?, amountOfRooms=?, adjacentRooms=?, amountOfAdjacentRooms=?, typeOfBathrooms=?, typeOfBalcony=?, balconyGlazed=?, roomSpace=?, totalArea=?, livingSpace=?, kitchenSpace=?, floor=?, totalAmountFloor=?, numberOfFloor=?, concierge=?, intercom=?, parking=?, city=?, district=?, coordX=?, coordY=?, address=?, apartmentNumber=?, subwayStation=?, distanceToMetroStation=?, currency=?, costOfRenting=?, realCostOfRenting=?, utilities=?, costInSummer=?, costInWinter=?, electricPower=?, bail=?, bailCost=?, prepayment=?, compensationMoney=?, compensationPercent=?, repair=?, furnish=?, windows=?, internet=?, telephoneLine=?, cableTV=?, furnitureInLivingArea=?, furnitureInLivingAreaExtra=?, furnitureInKitchen=?, furnitureInKitchenExtra=?, appliances=?, appliancesExtra=?, sexOfTenant=?, relations=?, children=?, animals=?, contactTelephonNumber=?, timeForRingBegin=?, timeForRingEnd=?, checking=?, responsibility=?, comment=?, last_act=?, reg_date=?, status=?, earliestDate=?, earliestTimeHours=?, earliestTimeMinutes=?, adminComment=?, completeness=?") === FALSE)
				OR ($stmt->bind_param("sssssssssssddddiiissssssssssisddsddssdsddssssssssssssssssssssssiissssss", $userId, $this->typeOfObject, $dateOfEntryForDB, $this->termOfLease, $dateOfCheckOutForDB, $this->amountOfRooms, $this->adjacentRooms, $this->amountOfAdjacentRooms, $this->typeOfBathrooms, $this->typeOfBalcony, $this->balconyGlazed, $this->roomSpace, $this->totalArea, $this->livingSpace, $this->kitchenSpace, $this->floor, $this->totalAmountFloor, $this->numberOfFloor, $this->concierge, $this->intercom, $this->parking, $this->city, $this->district, $this->coordX, $this->coordY, $this->address, $this->apartmentNumber, $this->subwayStation, $this->distanceToMetroStation, $this->currency, $this->costOfRenting, $realCostOfRenting, $this->utilities, $this->costInSummer, $this->costInWinter, $this->electricPower, $this->bail, $this->bailCost, $this->prepayment, $this->compensationMoney, $this->compensationPercent, $this->repair, $this->furnish, $this->windows, $this->internet, $this->telephoneLine, $this->cableTV, $furnitureInLivingAreaSerialized, $this->furnitureInLivingAreaExtra, $furnitureInKitchenSerialized, $this->furnitureInKitchenExtra, $appliancesSerialized, $this->appliancesExtra, $sexOfTenantSerialized, $relationsSerialized, $this->children, $this->animals, $this->contactTelephonNumber, $this->timeForRingBegin, $this->timeForRingEnd, $this->checking, $this->responsibility, $this->comment, $last_act, $reg_date, $this->status, $earliestDateForDB, $this->earliestTimeHours, $this->earliestTimeMinutes, $this->adminComment, $this->completeness) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($res === 0)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				return FALSE;
			}
		}

		if ($typeOfProperty == "edit") {
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("UPDATE property SET userId=?, typeOfObject=?, dateOfEntry=?, termOfLease=?, dateOfCheckOut=?, amountOfRooms=?, adjacentRooms=?, amountOfAdjacentRooms=?, typeOfBathrooms=?, typeOfBalcony=?, balconyGlazed=?, roomSpace=?, totalArea=?, livingSpace=?, kitchenSpace=?, floor=?, totalAmountFloor=?, numberOfFloor=?, concierge=?, intercom=?, parking=?, city=?, district=?, coordX=?, coordY=?, address=?, apartmentNumber=?, subwayStation=?, distanceToMetroStation=?, currency=?, costOfRenting=?, realCostOfRenting=?, utilities=?, costInSummer=?, costInWinter=?, electricPower=?, bail=?, bailCost=?, prepayment=?, compensationMoney=?, compensationPercent=?, repair=?, furnish=?, windows=?, internet=?, telephoneLine=?, cableTV=?, furnitureInLivingArea=?, furnitureInLivingAreaExtra=?, furnitureInKitchen=?, furnitureInKitchenExtra=?, appliances=?, appliancesExtra=?, sexOfTenant=?, relations=?, children=?, animals=?, contactTelephonNumber=?, timeForRingBegin=?, timeForRingEnd=?, checking=?, responsibility=?, comment=?, last_act=?, reg_date=?, status=?, earliestDate=?, earliestTimeHours=?, earliestTimeMinutes=?, adminComment=?, completeness=? WHERE id=?") === FALSE)
				OR ($stmt->bind_param("sssssssssssddddiiissssssssssisddsddssdsddssssssssssssssssssssssiisssssss", $userId, $this->typeOfObject, $dateOfEntryForDB, $this->termOfLease, $dateOfCheckOutForDB, $this->amountOfRooms, $this->adjacentRooms, $this->amountOfAdjacentRooms, $this->typeOfBathrooms, $this->typeOfBalcony, $this->balconyGlazed, $this->roomSpace, $this->totalArea, $this->livingSpace, $this->kitchenSpace, $this->floor, $this->totalAmountFloor, $this->numberOfFloor, $this->concierge, $this->intercom, $this->parking, $this->city, $this->district, $this->coordX, $this->coordY, $this->address, $this->apartmentNumber, $this->subwayStation, $this->distanceToMetroStation, $this->currency, $this->costOfRenting, $realCostOfRenting, $this->utilities, $this->costInSummer, $this->costInWinter, $this->electricPower, $this->bail, $this->bailCost, $this->prepayment, $this->compensationMoney, $this->compensationPercent, $this->repair, $this->furnish, $this->windows, $this->internet, $this->telephoneLine, $this->cableTV, $furnitureInLivingAreaSerialized, $this->furnitureInLivingAreaExtra, $furnitureInKitchenSerialized, $this->furnitureInKitchenExtra, $appliancesSerialized, $this->appliancesExtra, $sexOfTenantSerialized, $relationsSerialized, $this->children, $this->animals, $this->contactTelephonNumber, $this->timeForRingBegin, $this->timeForRingEnd, $this->checking, $this->responsibility, $this->comment, $last_act, $this->reg_date, $this->status, $earliestDateForDB, $this->earliestTimeHours, $this->earliestTimeMinutes, $this->adminComment, $this->completeness, $this->id) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				return FALSE;
			}
		}

		// Если объявление новое - изменим статус пользователя (typeOwner), так как он теперь точно стал собственником
		if ($typeOfProperty == "new") {
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("UPDATE users SET typeOwner='TRUE' WHERE id=?") === FALSE)
				OR ($stmt->bind_param("s", $userId) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			}
		}

		return TRUE;
	}

	// Функция сохраняет актуальные данные о фотографиях пользователя в БД. Если какие-то из ранее загруженных фотографий были удалены пользователем (помечены в браузере на удаление), то функция удаляет их с сервера и из БД
	// Возвращает TRUE в случае успешного изменения ключевых данных в БД, FALSE в противном случае
	public function saveFotoInformationToDB() {

		// ВАЖНО:
		// Функция считает, что если объект имеет id, то он уже был зарегистрирован и требуется отредактировать его фотографии
		// Если же объект не имеет id, то функция считает его Новым объектом недвижимости (а значит у него нет сохраненных фоток в propertyFotos)
		//
		// Схема работы функции:
		// 1. Проверить наличие массива данных о фотографиях ($this->uploadedFoto), а также id объекта недвижимости
		// 2. Собираем инфу по всем фотографиям объекта недвижимости из БД tempFotos (по $this->fileUploadId) и propertyFotos (по id)
		// 3. Добавляем в полученные из БД данные актуальную инфу по статусам (основная/неосновная) и помечаем те фотки, которые нужно удалить
		// 4. Перебираем массив и удаляем ненужные фотки с жесткого диска
		// 5. Редактируем данные по нужным фоткам (UPDATE для propertyFotos)
		// 6. Добавляем данные по нужным фоткам (INSERT для propertyFotos)
		// 7. Удаляем ненужные фотки (DELETE для propertyFotos и для tempFotos)

		// На всякий случай, проверим на массив
		if (!isset($this->uploadedFoto) || !is_array($this->uploadedFoto)) {
			// Логируем ошибку
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка: в параметре uploadedFoto находится не массив. id логгера: Property.php->saveFotoInformationToDB():1. ID объекта недвижимости: " . $this->id);
			return FALSE;
		}

		// Для выполнения функция у объекта недвижимости обязательно должен быть id (то есть основные данные о нем уже занесены в БД)
		if ($this->id == "") {
			// Логируем ошибку
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка: обращение к методу записи параметров объекта в БД при отсутствующем id объекта недвижимости. id логгера: Property.php->saveFotoInformationToDB():2. ID объекта недвижимости: " . $this->id);
			return FALSE;
		}

		// Получаем данные по всем фоткам с нашим $this->fileUploadId
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM tempFotos WHERE fileUploadId=?") === FALSE)
			OR ($stmt->bind_param("s", $this->fileUploadId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($allFotos = $stmt->get_result()) === FALSE)
			OR (($allFotos = $allFotos->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			$allFotos = array();
			// Логируем ошибку
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM tempFotos WHERE fileUploadId=" . $this->fileUploadId . "'. id логгера: Property.php->saveFotoInformationToDB():3. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID объекта недвижимости: " . $this->id);
		} else {
			// Пометим все члены массива признаком их получения из таблицы tempFotos
			for ($i = 0, $s = count($allFotos); $i < $s; $i++) {
				$allFotos[$i]['fromTable'] = "tempFotos";
			}
		}

		// Получаем данные по всем фоткам объекта недвижимости (с идентификатором $this->id)
		// Но только для существующих объектов (имеющих id)
		if ($this->id != "") {

			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("SELECT * FROM propertyFotos WHERE propertyId=?") === FALSE)
				OR ($stmt->bind_param("s", $this->id) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->get_result()) === FALSE)
				OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
				OR ($stmt->close() === FALSE)
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM propertyFotos WHERE propertyId=" . $this->id . "'. id логгера: Property.php->saveFotoInformationToDB():4. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID объекта недвижимости: " . $this->id);
			} else {
				// Пометим все члены массива признаком их получения из таблицы userFotos
				for ($i = 0, $s = count($res); $i < $s; $i++) {
					$res[$i]['fromTable'] = "propertyFotos";
				}
				$allFotos = array_merge($allFotos, $res);
			}
		}

		// Перебираем все имеющиеся фотографии пользователя и актуализируем их параметры
		$primaryFotoExists = 0; // Инициализируем переменную, по которой после прохода по всем фотографиям, полученным в форме, сможем сказать была ли указана пользователем основная фотка (число - сколько фоток со статусом основная мы получили с клиента) или нет (0)
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {

			// Для сокращения количества запросов на UPDATE будем отмечать особым признаком те фотографии, по которым требуется выполнения этого запроса к БД
			$allFotos[$i]['updated'] = FALSE;

			// На заметку: в массиве $uploadedFoto также содержится (а точнее может содержаться) актуальная информация по всем статусам фотографий, но легче получить id основной фотки из формы, а не из этого массива
			if ($allFotos[$i]['id'] == $this->primaryFotoId) {
				// Проверяем - нужно ли для данной фотографии проводить UPDATE
				if ($allFotos[$i]['fromTable'] == "propertyFotos" && $allFotos[$i]['status'] != 'основная') {
					$allFotos[$i]['updated'] = TRUE;
				}
				$allFotos[$i]['status'] = 'основная';
				// Признак наличия основной фотографии
				$primaryFotoExists++;
			} else {
				if ($allFotos[$i]['fromTable'] == "propertyFotos" && $allFotos[$i]['status'] != '') {
					$allFotos[$i]['updated'] = TRUE;
				}
				$allFotos[$i]['status'] = '';
			}

			// Отмечаем фотографии на удаление
			$allFotos[$i]['forRemove'] = TRUE;
			foreach ($this->uploadedFoto as $value) {
				if ($allFotos[$i]['id'] == $value['id']) {
					$allFotos[$i]['forRemove'] = FALSE;
					break;
				}
			}

		}

		// Если пользователь не указал основное фото, то укажем первую попавшуюся фотографию (не помеченную на удаление) в качестве основной
		if ($primaryFotoExists == 0) {
			for ($i = 0, $s = count($allFotos); $i < $s; $i++) {
				// Если файл помечен на удаление, то ему статус основной не присваиваем
				if ($allFotos[$i]['forRemove'] == TRUE) continue;

				// Проверяем - нужно ли для данной фотографии проводить UPDATE
				if ($allFotos[$i]['fromTable'] == "propertyFotos" && $allFotos[$i]['status'] != 'основная') {
					$allFotos[$i]['updated'] = TRUE;
				}
				$allFotos[$i]['status'] = 'основная';

				// Как только нашли одну фотку, которая не подлежит удалению и присвоили ей статус основной, так выходим из перебора
				break;
			}
		}

		// Удаляем файлы фотографий (помеченных признаком удаления) с сервера
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {
			if ($allFotos[$i]['forRemove'] == FALSE) continue;
			if ((unlink($allFotos[$i]['folder'] . '/small/' . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension']) === FALSE)
				OR unlink($allFotos[$i]['folder'] . '/middle/' . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension'])
				OR unlink($allFotos[$i]['folder'] . '/big/' . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension'])
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка при удалении фотографий. id логгера: Property.php->saveFotoInformationToDB():5. Адреса фотографий (small, middle, big), которые не удалось удалить: " . $allFotos[$i]['folder'] . '/small/' . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension'] . " ID объекта недвижимости: " . $this->id);
			}
		}

		// Выполним запросы на UPDATE данных в propertyFotos
		$stmt = DBconnect::get()->stmt_init();
		if ($stmt->prepare("UPDATE propertyFotos SET status=? WHERE id=?") === FALSE) {
			// Логируем ошибку
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос prepare: 'UPDATE propertyFotos SET status=? WHERE id=?' id логгера: Property.php->saveFotoInformationToDB():6. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID объекта недвижимости: " . $this->id);
		}
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {
			if ($allFotos[$i]['fromTable'] == "propertyFotos" && $allFotos[$i]['updated'] == TRUE && $allFotos[$i]['forRemove'] == FALSE) {
				if (($stmt->bind_param("ss", $allFotos[$i]['status'], $allFotos[$i]['id']) === FALSE)
					OR ($stmt->execute() === FALSE)
					OR (($res = $stmt->affected_rows) === -1)
				) {
					// Логируем ошибку
					Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE propertyFotos SET status=" . $allFotos[$i]['status'] . " WHERE id=" . $allFotos[$i]['id'] . "' id логгера: Property.php->saveFotoInformationToDB():7. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID объекта недвижимости: " . $this->id);
				}
			}
		}
		$stmt->close();

		// Для уменьшения запросов к БД соберем 2 общих запроса на изменение сразу всех нужных строк
		// Соберем условия WHERE для SQL запросов к БД:
		// на INSERT новых строк в userFotos
		// на DELETE более ненужных фоток из userFotos
		$strINSERT = "";
		$strDELETE = "";
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {

			if ($allFotos[$i]['fromTable'] == "tempFotos" && $allFotos[$i]['forRemove'] == FALSE) {
				if ($strINSERT != "") $strINSERT .= ",";
				$strINSERT .= "('" . $allFotos[$i]['id'] . "','" . $allFotos[$i]['folder'] . "','" . $allFotos[$i]['filename'] . "','" . $allFotos[$i]['extension'] . "','" . $allFotos[$i]['filesizeMb'] . "','" . $this->id . "','" . $allFotos[$i]['status'] . "')";
			}

			if ($allFotos[$i]['forRemove'] == TRUE) {
				if ($strDELETE != "") $strDELETE .= " OR";
				$strDELETE .= " id = '" . $allFotos[$i]['id'] . "'";
			}

		}

		// Выполним сформированные запросы
		// INSERT
		if ($strINSERT != "") {
			DBconnect::get()->query("INSERT INTO propertyFotos (id, folder, filename, extension, filesizeMb, propertyId, status) VALUES " . $strINSERT);
			if ((DBconnect::get()->errno)
				OR (($res = DBconnect::get()->affected_rows) === -1)
				OR ($res === 0)
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO propertyFotos (id, folder, filename, extension, filesizeMb, propertyId, status) VALUES " . $strINSERT . "' id логгера: Property.php->saveFotoInformationToDB():8. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID объекта недвижимости: " . $this->id);
			}
		}
		// DELETE
		if ($strDELETE != "") {
			DBconnect::get()->query("DELETE FROM propertyFotos WHERE " . $strDELETE);
			if ((DBconnect::get()->errno)
				OR (($res = DBconnect::get()->affected_rows) === -1)
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM propertyFotos WHERE " . $strDELETE . "' id логгера: Property.php->saveFotoInformationToDB():9. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID объекта недвижимости: " . $this->id);
			}
		}

		// Удаляем инфу о всех фотках с fileUploadId из tempFotos
		// TODO: Не очень безопасно (используется полученный с клиента fileUploadId)
		if ($this->fileUploadId != "") {
			DBconnect::get()->query("DELETE FROM tempFotos WHERE fileUploadId = '" . $this->fileUploadId . "'");
			if ((DBconnect::get()->errno)
				OR (DBconnect::get()->affected_rows === -1)
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM tempFotos WHERE fileUploadId = '" . $this->fileUploadId . "' id логгера: Property.php->saveFotoInformationToDB():10. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID объекта недвижимости: " . $this->id);
			}
		}

		// Приведем в соответствие с данными из БД наш массив с фотографиями $this->uploadedFotos
		$this->writeFotoInformationFromDB();

		return TRUE;
	}

	// Метод читает данные объекта недвижимости из БД и записывает их в параметры данного объекта
	public function writeCharacteristicFromDB() {
		// Если идентификатор объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
		if ($this->id == "") return FALSE;

		// Получим из БД данные ($res) по объекту недвижимости с идентификатором = $this->id
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM property WHERE id=?") === FALSE)
			OR ($stmt->bind_param("s", $this->id) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			return FALSE;
		}

		// Если получено меньше или больше одной строки (одного объекта недвижимости) из БД, то сообщаем об ошибке
		if (!is_array($res) || count($res) != 1) {
			// TODO: Сохранить в лог ошибку получения данных пользователя из БД
			return FALSE;
		}

		// Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
		$onePropertyDataArr = $res[0];

		// Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию.
		if (isset($onePropertyDataArr['typeOfObject'])) $this->typeOfObject = $onePropertyDataArr['typeOfObject'];
		if (isset($onePropertyDataArr['dateOfEntry']) && $onePropertyDataArr['dateOfEntry'] != "0000-00-00") $this->dateOfEntry = GlobFunc::dateFromDBToView($onePropertyDataArr['dateOfEntry']);
		if (isset($onePropertyDataArr['termOfLease'])) $this->termOfLease = $onePropertyDataArr['termOfLease'];
		if (isset($onePropertyDataArr['dateOfCheckOut']) && $onePropertyDataArr['dateOfCheckOut'] != "0000-00-00") $this->dateOfCheckOut = GlobFunc::dateFromDBToView($onePropertyDataArr['dateOfCheckOut']);
		if (isset($onePropertyDataArr['amountOfRooms'])) $this->amountOfRooms = $onePropertyDataArr['amountOfRooms'];
		if (isset($onePropertyDataArr['adjacentRooms'])) $this->adjacentRooms = $onePropertyDataArr['adjacentRooms'];
		if (isset($onePropertyDataArr['amountOfAdjacentRooms'])) $this->amountOfAdjacentRooms = $onePropertyDataArr['amountOfAdjacentRooms'];
		if (isset($onePropertyDataArr['typeOfBathrooms'])) $this->typeOfBathrooms = $onePropertyDataArr['typeOfBathrooms'];
		if (isset($onePropertyDataArr['typeOfBalcony'])) $this->typeOfBalcony = $onePropertyDataArr['typeOfBalcony'];
		if (isset($onePropertyDataArr['balconyGlazed'])) $this->balconyGlazed = $onePropertyDataArr['balconyGlazed'];
		if (isset($onePropertyDataArr['roomSpace'])) $this->roomSpace = $onePropertyDataArr['roomSpace'];
		if (isset($onePropertyDataArr['totalArea'])) $this->totalArea = $onePropertyDataArr['totalArea'];
		if (isset($onePropertyDataArr['livingSpace'])) $this->livingSpace = $onePropertyDataArr['livingSpace'];
		if (isset($onePropertyDataArr['kitchenSpace'])) $this->kitchenSpace = $onePropertyDataArr['kitchenSpace'];
		if (isset($onePropertyDataArr['floor'])) $this->floor = $onePropertyDataArr['floor'];
		if (isset($onePropertyDataArr['totalAmountFloor'])) $this->totalAmountFloor = $onePropertyDataArr['totalAmountFloor'];
		if (isset($onePropertyDataArr['numberOfFloor'])) $this->numberOfFloor = $onePropertyDataArr['numberOfFloor'];
		if (isset($onePropertyDataArr['concierge'])) $this->concierge = $onePropertyDataArr['concierge'];
		if (isset($onePropertyDataArr['intercom'])) $this->intercom = $onePropertyDataArr['intercom'];
		if (isset($onePropertyDataArr['parking'])) $this->parking = $onePropertyDataArr['parking'];
		if (isset($onePropertyDataArr['city'])) $this->city = $onePropertyDataArr['city'];
		if (isset($onePropertyDataArr['district'])) $this->district = $onePropertyDataArr['district'];
		if (isset($onePropertyDataArr['coordX'])) $this->coordX = $onePropertyDataArr['coordX'];
		if (isset($onePropertyDataArr['coordY'])) $this->coordY = $onePropertyDataArr['coordY'];
		if (isset($onePropertyDataArr['address'])) $this->address = $onePropertyDataArr['address'];
		if (isset($onePropertyDataArr['apartmentNumber'])) $this->apartmentNumber = $onePropertyDataArr['apartmentNumber'];
		if (isset($onePropertyDataArr['subwayStation'])) $this->subwayStation = $onePropertyDataArr['subwayStation'];
		if (isset($onePropertyDataArr['distanceToMetroStation'])) $this->distanceToMetroStation = $onePropertyDataArr['distanceToMetroStation'];
		if (isset($onePropertyDataArr['currency'])) $this->currency = $onePropertyDataArr['currency'];
		if (isset($onePropertyDataArr['costOfRenting'])) $this->costOfRenting = $onePropertyDataArr['costOfRenting'];
		if (isset($onePropertyDataArr['utilities'])) $this->utilities = $onePropertyDataArr['utilities'];
		if (isset($onePropertyDataArr['costInSummer'])) $this->costInSummer = $onePropertyDataArr['costInSummer'];
		if (isset($onePropertyDataArr['costInWinter'])) $this->costInWinter = $onePropertyDataArr['costInWinter'];
		if (isset($onePropertyDataArr['electricPower'])) $this->electricPower = $onePropertyDataArr['electricPower'];
		if (isset($onePropertyDataArr['bail'])) $this->bail = $onePropertyDataArr['bail'];
		if (isset($onePropertyDataArr['bailCost'])) $this->bailCost = $onePropertyDataArr['bailCost'];
		if (isset($onePropertyDataArr['prepayment'])) $this->prepayment = $onePropertyDataArr['prepayment'];
		if (isset($onePropertyDataArr['compensationMoney'])) $this->compensationMoney = $onePropertyDataArr['compensationMoney'];
		if (isset($onePropertyDataArr['compensationPercent'])) $this->compensationPercent = $onePropertyDataArr['compensationPercent'];
		if (isset($onePropertyDataArr['repair'])) $this->repair = $onePropertyDataArr['repair'];
		if (isset($onePropertyDataArr['furnish'])) $this->furnish = $onePropertyDataArr['furnish'];
		if (isset($onePropertyDataArr['windows'])) $this->windows = $onePropertyDataArr['windows'];
		if (isset($onePropertyDataArr['internet'])) $this->internet = $onePropertyDataArr['internet'];
		if (isset($onePropertyDataArr['telephoneLine'])) $this->telephoneLine = $onePropertyDataArr['telephoneLine'];
		if (isset($onePropertyDataArr['cableTV'])) $this->cableTV = $onePropertyDataArr['cableTV'];
		if (isset($onePropertyDataArr['furnitureInLivingArea'])) $this->furnitureInLivingArea = unserialize($onePropertyDataArr['furnitureInLivingArea']);
		if (isset($onePropertyDataArr['furnitureInLivingAreaExtra'])) $this->furnitureInLivingAreaExtra = $onePropertyDataArr['furnitureInLivingAreaExtra'];
		if (isset($onePropertyDataArr['furnitureInKitchen'])) $this->furnitureInKitchen = unserialize($onePropertyDataArr['furnitureInKitchen']);
		if (isset($onePropertyDataArr['furnitureInKitchenExtra'])) $this->furnitureInKitchenExtra = $onePropertyDataArr['furnitureInKitchenExtra'];
		if (isset($onePropertyDataArr['appliances'])) $this->appliances = unserialize($onePropertyDataArr['appliances']);
		if (isset($onePropertyDataArr['appliancesExtra'])) $this->appliancesExtra = $onePropertyDataArr['appliancesExtra'];
		if (isset($onePropertyDataArr['sexOfTenant'])) $this->sexOfTenant = unserialize($onePropertyDataArr['sexOfTenant']);
		if (isset($onePropertyDataArr['relations'])) $this->relations = unserialize($onePropertyDataArr['relations']);
		if (isset($onePropertyDataArr['children'])) $this->children = $onePropertyDataArr['children'];
		if (isset($onePropertyDataArr['animals'])) $this->animals = $onePropertyDataArr['animals'];
		if (isset($onePropertyDataArr['contactTelephonNumber'])) $this->contactTelephonNumber = $onePropertyDataArr['contactTelephonNumber'];
		if (isset($onePropertyDataArr['timeForRingBegin'])) $this->timeForRingBegin = $onePropertyDataArr['timeForRingBegin'];
		if (isset($onePropertyDataArr['timeForRingEnd'])) $this->timeForRingEnd = $onePropertyDataArr['timeForRingEnd'];
		if (isset($onePropertyDataArr['checking'])) $this->checking = $onePropertyDataArr['checking'];
		if (isset($onePropertyDataArr['responsibility'])) $this->responsibility = $onePropertyDataArr['responsibility'];
		if (isset($onePropertyDataArr['comment'])) $this->comment = $onePropertyDataArr['comment'];
		if (isset($onePropertyDataArr['earliestDate']) && $onePropertyDataArr['earliestDate'] != "0000-00-00") $this->earliestDate = GlobFunc::dateFromDBToView($onePropertyDataArr['earliestDate']);
		if (isset($onePropertyDataArr['earliestTimeHours'])) $this->earliestTimeHours = $onePropertyDataArr['earliestTimeHours'];
		if (isset($onePropertyDataArr['earliestTimeMinutes'])) $this->earliestTimeMinutes = $onePropertyDataArr['earliestTimeMinutes'];
		if (isset($onePropertyDataArr['adminComment'])) $this->adminComment = $onePropertyDataArr['adminComment'];
		if (isset($onePropertyDataArr['completeness'])) $this->completeness = $onePropertyDataArr['completeness'];

		if (isset($onePropertyDataArr['realCostOfRenting'])) $this->realCostOfRenting = $onePropertyDataArr['realCostOfRenting'];
		if (isset($onePropertyDataArr['last_act'])) $this->last_act = $onePropertyDataArr['last_act'];
		if (isset($onePropertyDataArr['reg_date'])) $this->reg_date = $onePropertyDataArr['reg_date'];
		if (isset($onePropertyDataArr['status'])) $this->status = $onePropertyDataArr['status'];
		if (isset($onePropertyDataArr['id'])) $this->id = $onePropertyDataArr['id'];
		if (isset($onePropertyDataArr['userId'])) $this->userId = $onePropertyDataArr['userId'];

		return TRUE;
	}

	// Метод читает данные о фотографиях из БД и записывает их в параметры объекта недвижимости
	// Для корректной работы в параметрах объекта должен быть указан id объекта недвижимости ($this->id)
	public function writeFotoInformationFromDB() {

		// Если идентификатор объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
		if ($this->id == "") return FALSE;

		// Получим из БД данные ($res) по объекту недвижимости с идентификатором = $this->id
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM propertyFotos WHERE propertyId=?") === FALSE)
			OR ($stmt->bind_param("s", $this->id) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			return FALSE;
		}

		// Сохраняем в параметры объекта массив массивов, каждый из которых содержит данные по 1 фотографии
		$this->uploadedFoto = $res;

		// Сохраняем идентификатор основной фотографии пользователя в параметры объекта
		foreach ($res as $value) {
			if ($value['status'] == 'основная') {
				$this->primaryFotoId = $value['id'];
				break;
			}
		}

		return TRUE;

	}

	// Записать в качестве параметров объекта недвижимости значения, полученные через POST запрос
	// $mode = "new" режим выбора POST параметров для создания пользователем-собственником (или моим обычным сотрудником) нового объявления
	// $mode = "edit" режим выбора POST параметров для редактирования пользователем-собственником ранее созданного объявления (отличается от режима "new" тем, что не принимает (игнорирует) через POST ряд параметров объекта, запрещенных для редактирования пользователем)
	public function writeCharacteristicFromPOST($mode = "edit") {
		if (isset($_POST['ownerLogin']) && $mode == "new") $this->ownerLogin = htmlspecialchars($_POST['ownerLogin'], ENT_QUOTES);
		if (isset($_POST['status']) && $mode == "new") $this->status = htmlspecialchars($_POST['status'], ENT_QUOTES);
		if (isset($_POST['typeOfObject']) && $mode == "new") $this->typeOfObject = htmlspecialchars($_POST['typeOfObject'], ENT_QUOTES);
		if (isset($_POST['dateOfEntry'])) $this->dateOfEntry = htmlspecialchars($_POST['dateOfEntry'], ENT_QUOTES);
		if (isset($_POST['termOfLease'])) $this->termOfLease = htmlspecialchars($_POST['termOfLease'], ENT_QUOTES);
		if (isset($_POST['dateOfCheckOut'])) $this->dateOfCheckOut = htmlspecialchars($_POST['dateOfCheckOut'], ENT_QUOTES);
		if (isset($_POST['amountOfRooms'])) $this->amountOfRooms = htmlspecialchars($_POST['amountOfRooms'], ENT_QUOTES);
		if (isset($_POST['adjacentRooms'])) $this->adjacentRooms = htmlspecialchars($_POST['adjacentRooms'], ENT_QUOTES);
		if (isset($_POST['amountOfAdjacentRooms'])) $this->amountOfAdjacentRooms = htmlspecialchars($_POST['amountOfAdjacentRooms'], ENT_QUOTES);
		if (isset($_POST['typeOfBathrooms'])) $this->typeOfBathrooms = htmlspecialchars($_POST['typeOfBathrooms'], ENT_QUOTES);
		if (isset($_POST['typeOfBalcony'])) $this->typeOfBalcony = htmlspecialchars($_POST['typeOfBalcony'], ENT_QUOTES);
		if (isset($_POST['balconyGlazed'])) $this->balconyGlazed = htmlspecialchars($_POST['balconyGlazed'], ENT_QUOTES);
		if (isset($_POST['roomSpace'])) $this->roomSpace = htmlspecialchars($_POST['roomSpace'], ENT_QUOTES);
		if (isset($_POST['totalArea'])) $this->totalArea = htmlspecialchars($_POST['totalArea'], ENT_QUOTES);
		if (isset($_POST['livingSpace'])) $this->livingSpace = htmlspecialchars($_POST['livingSpace'], ENT_QUOTES);
		if (isset($_POST['kitchenSpace'])) $this->kitchenSpace = htmlspecialchars($_POST['kitchenSpace'], ENT_QUOTES);
		if (isset($_POST['floor']) && $mode == "new") $this->floor = htmlspecialchars($_POST['floor'], ENT_QUOTES);
		if (isset($_POST['totalAmountFloor']) && $mode == "new") $this->totalAmountFloor = htmlspecialchars($_POST['totalAmountFloor'], ENT_QUOTES);
		if (isset($_POST['numberOfFloor']) && $mode == "new") $this->numberOfFloor = htmlspecialchars($_POST['numberOfFloor'], ENT_QUOTES);
		if (isset($_POST['concierge'])) $this->concierge = htmlspecialchars($_POST['concierge'], ENT_QUOTES);
		if (isset($_POST['intercom'])) $this->intercom = htmlspecialchars($_POST['intercom'], ENT_QUOTES);
		if (isset($_POST['parking'])) $this->parking = htmlspecialchars($_POST['parking'], ENT_QUOTES);
		if (isset($_POST['district']) && $mode == "new") $this->district = htmlspecialchars($_POST['district'], ENT_QUOTES);
		if (isset($_POST['coordX']) && $mode == "new") $this->coordX = htmlspecialchars($_POST['coordX'], ENT_QUOTES);
		if (isset($_POST['coordY']) && $mode == "new") $this->coordY = htmlspecialchars($_POST['coordY'], ENT_QUOTES);
		if (isset($_POST['address']) && $mode == "new") $this->address = htmlspecialchars($_POST['address'], ENT_QUOTES);
		if (isset($_POST['apartmentNumber']) && $mode == "new") $this->apartmentNumber = htmlspecialchars($_POST['apartmentNumber'], ENT_QUOTES);
		if (isset($_POST['subwayStation'])) $this->subwayStation = htmlspecialchars($_POST['subwayStation'], ENT_QUOTES);
		if (isset($_POST['distanceToMetroStation'])) $this->distanceToMetroStation = htmlspecialchars($_POST['distanceToMetroStation'], ENT_QUOTES);
		if (isset($_POST['currency'])) $this->currency = htmlspecialchars($_POST['currency'], ENT_QUOTES);
		if (isset($_POST['costOfRenting'])) $this->costOfRenting = htmlspecialchars($_POST['costOfRenting'], ENT_QUOTES);
		if (isset($_POST['utilities'])) $this->utilities = htmlspecialchars($_POST['utilities'], ENT_QUOTES);
		if (isset($_POST['costInSummer'])) $this->costInSummer = htmlspecialchars($_POST['costInSummer'], ENT_QUOTES);
		if (isset($_POST['costInWinter'])) $this->costInWinter = htmlspecialchars($_POST['costInWinter'], ENT_QUOTES);
		if (isset($_POST['electricPower'])) $this->electricPower = htmlspecialchars($_POST['electricPower'], ENT_QUOTES);
		if (isset($_POST['bail'])) $this->bail = htmlspecialchars($_POST['bail'], ENT_QUOTES);
		if (isset($_POST['bailCost'])) $this->bailCost = htmlspecialchars($_POST['bailCost'], ENT_QUOTES);
		if (isset($_POST['prepayment'])) $this->prepayment = htmlspecialchars($_POST['prepayment'], ENT_QUOTES);
		if (isset($_POST['compensationMoney']) && $mode == "new") $this->compensationMoney = htmlspecialchars($_POST['compensationMoney'], ENT_QUOTES);
		if (isset($_POST['compensationPercent']) && $mode == "new") $this->compensationPercent = htmlspecialchars($_POST['compensationPercent'], ENT_QUOTES);
		if (isset($_POST['repair'])) $this->repair = htmlspecialchars($_POST['repair'], ENT_QUOTES);
		if (isset($_POST['furnish'])) $this->furnish = htmlspecialchars($_POST['furnish'], ENT_QUOTES);
		if (isset($_POST['windows'])) $this->windows = htmlspecialchars($_POST['windows'], ENT_QUOTES);
		if (isset($_POST['internet'])) $this->internet = htmlspecialchars($_POST['internet'], ENT_QUOTES);
		if (isset($_POST['telephoneLine'])) $this->telephoneLine = htmlspecialchars($_POST['telephoneLine'], ENT_QUOTES);
		if (isset($_POST['cableTV'])) $this->cableTV = htmlspecialchars($_POST['cableTV'], ENT_QUOTES);
		if (isset($_POST['furnitureInLivingArea']) && is_array($_POST['furnitureInLivingArea'])) {
			$this->furnitureInLivingArea = array();
			foreach ($_POST['furnitureInLivingArea'] as $value) $this->furnitureInLivingArea[] = htmlspecialchars($value, ENT_QUOTES);
		} else $this->furnitureInLivingArea = array(); // Если пользователь отправил форму и не отметил ни одного предмета мебели, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
		if (isset($_POST['furnitureInLivingAreaExtra'])) $this->furnitureInLivingAreaExtra = htmlspecialchars($_POST['furnitureInLivingAreaExtra'], ENT_QUOTES);
		if (isset($_POST['furnitureInKitchen']) && is_array($_POST['furnitureInKitchen'])) {
			$this->furnitureInKitchen = array();
			foreach ($_POST['furnitureInKitchen'] as $value) $this->furnitureInKitchen[] = htmlspecialchars($value, ENT_QUOTES);
		} else $this->furnitureInKitchen = array(); // Если пользователь отправил форму и не отметил ни одного предмета мебели, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
		if (isset($_POST['furnitureInKitchenExtra'])) $this->furnitureInKitchenExtra = htmlspecialchars($_POST['furnitureInKitchenExtra'], ENT_QUOTES);
		if (isset($_POST['appliances']) && is_array($_POST['appliances'])) {
			$this->appliances = array();
			foreach ($_POST['appliances'] as $value) $this->appliances[] = htmlspecialchars($value, ENT_QUOTES);
		} else $this->appliances = array(); // Если пользователь отправил форму и не отметил ни одного предмета бытовой техники, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
		if (isset($_POST['appliancesExtra'])) $this->appliancesExtra = htmlspecialchars($_POST['appliancesExtra'], ENT_QUOTES);
		if (isset($_POST['sexOfTenant']) && is_array($_POST['sexOfTenant'])) {
			$this->sexOfTenant = array();
			foreach ($_POST['sexOfTenant'] as $value) $this->sexOfTenant[] = htmlspecialchars($value, ENT_QUOTES);
		} else $this->sexOfTenant = array(); // Если пользователь отправил форму и не отметил ни одного допустимого пола для одиночного арендатора, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
		if (isset($_POST['relations']) && is_array($_POST['relations'])) {
			$this->relations = array();
			foreach ($_POST['relations'] as $value) $this->relations[] = htmlspecialchars($value, ENT_QUOTES);
		} else $this->relations = array(); // Если пользователь отправил форму и не отметил ни одного допустимого вида отношений между арендаторами, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
		if (isset($_POST['children'])) $this->children = htmlspecialchars($_POST['children'], ENT_QUOTES);
		if (isset($_POST['animals'])) $this->animals = htmlspecialchars($_POST['animals'], ENT_QUOTES);
		if (isset($_POST['contactTelephonNumber'])) $this->contactTelephonNumber = htmlspecialchars($_POST['contactTelephonNumber'], ENT_QUOTES);
		if (isset($_POST['timeForRingBegin'])) $this->timeForRingBegin = htmlspecialchars($_POST['timeForRingBegin'], ENT_QUOTES);
		if (isset($_POST['timeForRingEnd'])) $this->timeForRingEnd = htmlspecialchars($_POST['timeForRingEnd'], ENT_QUOTES);
		if (isset($_POST['checking'])) $this->checking = htmlspecialchars($_POST['checking'], ENT_QUOTES);
		if (isset($_POST['responsibility'])) $this->responsibility = htmlspecialchars($_POST['responsibility'], ENT_QUOTES);
		if (isset($_POST['comment'])) $this->comment = htmlspecialchars($_POST['comment'], ENT_QUOTES);
		if (isset($_POST['earliestDate'])) $this->earliestDate = htmlspecialchars($_POST['earliestDate'], ENT_QUOTES);
		if (isset($_POST['earliestTimeHours'])) $this->earliestTimeHours = htmlspecialchars($_POST['earliestTimeHours'], ENT_QUOTES);
		if (isset($_POST['earliestTimeMinutes'])) $this->earliestTimeMinutes = htmlspecialchars($_POST['earliestTimeMinutes'], ENT_QUOTES);
		if (isset($_POST['adminComment'])) $this->adminComment = htmlspecialchars($_POST['adminComment'], ENT_QUOTES);
	}

	// Записать в качестве данных о фотографиях соответствующую информацию из POST запроса
	public function writeFotoInformationFromPOST() {
		//TODO: убедиться, что если на клиенте удалить все фотки, то при перезагрузке они снова не появятся (из-за того, что $uploadedFoto не придет в POST параметрах и останется предыдущая версия - которая не будет перезатерта)

		if (isset($_POST['fileUploadId'])) $this->fileUploadId = htmlspecialchars($_POST['fileUploadId'], ENT_QUOTES);
		if (isset($_POST['uploadedFoto'])) $this->uploadedFoto = json_decode($_POST['uploadedFoto'], TRUE); // Массив объектов со сведениями о загруженных фотографиях сериализуется в JSON формат на клиенте и передается как содержимое атрибута value одного единственного INPUT hidden
		if (isset($_POST['primaryFotoRadioButton'])) $this->primaryFotoId = htmlspecialchars($_POST['primaryFotoRadioButton'], ENT_QUOTES);
	}

	// Получить ассоциированный массив с данными анкеты объекта недвижимости (для использования в представлении)
	public function getCharacteristicData() {
		$result = array();

		$result['ownerLogin'] = $this->ownerLogin;
		$result['typeOfObject'] = $this->typeOfObject;
		$result['dateOfEntry'] = $this->dateOfEntry;
		$result['termOfLease'] = $this->termOfLease;
		$result['dateOfCheckOut'] = $this->dateOfCheckOut;
		$result['amountOfRooms'] = $this->amountOfRooms;
		$result['adjacentRooms'] = $this->adjacentRooms;
		$result['amountOfAdjacentRooms'] = $this->amountOfAdjacentRooms;
		$result['typeOfBathrooms'] = $this->typeOfBathrooms;
		$result['typeOfBalcony'] = $this->typeOfBalcony;
		$result['balconyGlazed'] = $this->balconyGlazed;
		$result['roomSpace'] = $this->roomSpace;
		$result['totalArea'] = $this->totalArea;
		$result['livingSpace'] = $this->livingSpace;
		$result['kitchenSpace'] = $this->kitchenSpace;
		$result['floor'] = $this->floor;
		$result['totalAmountFloor'] = $this->totalAmountFloor;
		$result['numberOfFloor'] = $this->numberOfFloor;
		$result['concierge'] = $this->concierge;
		$result['intercom'] = $this->intercom;
		$result['parking'] = $this->parking;
		$result['city'] = $this->city;
		$result['district'] = $this->district;
		$result['coordX'] = $this->coordX;
		$result['coordY'] = $this->coordY;
		$result['address'] = $this->address;
		$result['apartmentNumber'] = $this->apartmentNumber;
		$result['subwayStation'] = $this->subwayStation;
		$result['distanceToMetroStation'] = $this->distanceToMetroStation;
		$result['currency'] = $this->currency;
		$result['costOfRenting'] = $this->costOfRenting;
		$result['utilities'] = $this->utilities;
		$result['costInSummer'] = $this->costInSummer;
		$result['costInWinter'] = $this->costInWinter;
		$result['electricPower'] = $this->electricPower;
		$result['bail'] = $this->bail;
		$result['bailCost'] = $this->bailCost;
		$result['prepayment'] = $this->prepayment;
		$result['compensationMoney'] = $this->compensationMoney;
		$result['compensationPercent'] = $this->compensationPercent;
		$result['repair'] = $this->repair;
		$result['furnish'] = $this->furnish;
		$result['windows'] = $this->windows;
		$result['internet'] = $this->internet;
		$result['telephoneLine'] = $this->telephoneLine;
		$result['cableTV'] = $this->cableTV;
		$result['furnitureInLivingArea'] = $this->furnitureInLivingArea;
		$result['furnitureInLivingAreaExtra'] = $this->furnitureInLivingAreaExtra;
		$result['furnitureInKitchen'] = $this->furnitureInKitchen;
		$result['furnitureInKitchenExtra'] = $this->furnitureInKitchenExtra;
		$result['appliances'] = $this->appliances;
		$result['appliancesExtra'] = $this->appliancesExtra;
		$result['sexOfTenant'] = $this->sexOfTenant;
		$result['relations'] = $this->relations;
		$result['children'] = $this->children;
		$result['animals'] = $this->animals;
		$result['contactTelephonNumber'] = $this->contactTelephonNumber;
		$result['timeForRingBegin'] = $this->timeForRingBegin;
		$result['timeForRingEnd'] = $this->timeForRingEnd;
		$result['checking'] = $this->checking;
		$result['responsibility'] = $this->responsibility;
		$result['comment'] = $this->comment;
		$result['earliestDate'] = $this->earliestDate;
		$result['earliestTimeHours'] = $this->earliestTimeHours;
		$result['earliestTimeMinutes'] = $this->earliestTimeMinutes;
		$result['adminComment'] = $this->adminComment;
		$result['completeness'] = $this->completeness;

		$result['realCostOfRenting'] = $this->realCostOfRenting;
		$result['last_act'] = $this->last_act;
		$result['reg_date'] = $this->reg_date;
		$result['status'] = $this->status;
		$result['id'] = $this->id;
		$result['userId'] = $this->userId;

		return $result;
	}

	// Получить ассоциированный массив с данными о фотографиях объекта недвижимости (для использования в представлении)
	public function getFotoInformationData() {

		$result = array();

		$result['fileUploadId'] = $this->fileUploadId;
		$result['uploadedFoto'] = $this->uploadedFoto;
		$result['primaryFotoId'] = $this->primaryFotoId;

		return $result;

	}

	// $typeOfValidation = newAdvert - режим первичной (для нового объявления) проверки указанных пользователем параметров объекта недвижимости
	// $typeOfValidation = editAdvert - режим вторичной (при редактировании уже существующего объявления) проверки указанных пользователем параметров объекта недвижимости
	// $typeOfValidation = newAlienAdvert - режим проверки параметров нового объявления из чужой базы по минимуму - так как о чужих объектах обычно мало информации.
	// $typeOfValidation = editAlienAdvert - режим проверки параметров ранее созданного и записанного в БД объявления из чужой базы. По минимуму - так как о чужих объектах обычно мало информации.
	public function propertyDataValidate($typeOfValidation) {
		// Подготовим массив для сохранения сообщений об ошибках
		$errors = array();

		// Проверяем переменные
		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "newAlienAdvert") {
			if ($this->ownerLogin == "") $errors[] = 'Укажите логин пользователя-собственника';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert" || $typeOfValidation == "newAlienAdvert" || $typeOfValidation == "editAlienAdvert") {
			if ($this->typeOfObject == "0") $errors[] = 'Укажите тип объекта';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->dateOfEntry == "") $errors[] = 'Укажите с какого числа арендатору можно въезжать в вашу недвижимость';
		}
		if ($this->dateOfEntry != "") {
			if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $this->dateOfEntry)) $errors[] = 'Неправильный формат даты въезда для арендатора, должен быть: дд.мм.гггг';
			if (substr($this->dateOfEntry, 0, 2) < "01" || substr($this->dateOfEntry, 0, 2) > "31") $errors[] = 'Проверьте число даты въезда (допустимо от 01 до 31)';
			if (substr($this->dateOfEntry, 3, 2) < "01" || substr($this->dateOfEntry, 3, 2) > "12") $errors[] = 'Проверьте месяц даты въезда (допустимо от 01 до 12)';
			if (substr($this->dateOfEntry, 6, 4) < "1000" || substr($this->dateOfEntry, 6, 4) > "9999") $errors[] = 'Проверьте год даты въезда (допустимо от 1000 до 9999)';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->termOfLease == "0") $errors[] = 'Укажите на какой срок сдается недвижимость';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->dateOfCheckOut == "" && $this->termOfLease != "0" && $this->termOfLease != "длительный срок") $errors[] = 'Укажите крайний срок выезда для арендатора(ов)';
		}
		if ($this->dateOfCheckOut != "") {
			if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $this->dateOfCheckOut)) $errors[] = 'Неправильный формат крайней даты выезда для арендатора, должен быть: дд.мм.гггг';
			if (substr($this->dateOfCheckOut, 0, 2) < "01" || substr($this->dateOfCheckOut, 0, 2) > "31") $errors[] = 'Проверьте число даты выезда (допустимо от 01 до 31)';
			if (substr($this->dateOfCheckOut, 3, 2) < "01" || substr($this->dateOfCheckOut, 3, 2) > "12") $errors[] = 'Проверьте месяц даты выезда (допустимо от 01 до 12)';
			if (substr($this->dateOfCheckOut, 6, 4) < "1000" || substr($this->dateOfCheckOut, 6, 4) > "9999") $errors[] = 'Проверьте год даты выезда (допустимо от 1000 до 9999)';
		}

		// Проверяем наличие хотя бы 1 фотографии объекта недвижимости
		if ($typeOfValidation == "newAdvert" && $this->fileUploadId != "") {
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("SELECT * FROM tempFotos WHERE fileuploadid=?") === FALSE)
				OR ($stmt->bind_param("s", $this->fileUploadId) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->get_result()) === FALSE)
				OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				$errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
				return $errors;
			} else {
				if (!is_array($res) || count($res) == 0) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений';
			}
		}
		if ($typeOfValidation == "editAdvert") // Эта ветка выполняется, если валидации производятся при попытке редактирования параметров объекта недвижимости
		{
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("SELECT * FROM propertyFotos WHERE propertyId=?") === FALSE)
				OR ($stmt->bind_param("s", $this->id) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res1 = $stmt->get_result()) === FALSE)
				OR (($res1 = $res1->fetch_all(MYSQLI_ASSOC)) === FALSE)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				$errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
				return $errors;
			}

			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("SELECT * FROM tempFotos WHERE fileuploadid=?") === FALSE)
				OR ($stmt->bind_param("s", $this->fileUploadId) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res2 = $stmt->get_result()) === FALSE)
				OR (($res2 = $res2->fetch_all(MYSQLI_ASSOC)) === FALSE)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				$errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
				return $errors;
			}

			if (!is_array($res1) || !is_array($res2) || (count($res1) == 0 && count($res2) == 0)) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений'; // проверка на хотя бы 1 фотку
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert" || $typeOfValidation == "newAlienAdvert" || $typeOfValidation == "editAlienAdvert") {
			if ($this->fileUploadId == "") $errors[] = 'Перезагрузите браузер, пожалуйста: возникла ошибка при формировании формы для загрузки фотографий';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->amountOfRooms == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите количество комнат в квартире, доме';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->adjacentRooms == "0" && $this->amountOfRooms != "0" && $this->amountOfRooms != "1") $errors[] = 'Укажите: есть ли смежные комнаты в сдаваемом объекте недвижимости';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->amountOfAdjacentRooms == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж" && $this->adjacentRooms != "0" && $this->adjacentRooms != "нет" && $this->amountOfRooms != "0" && $this->amountOfRooms != "1" && $this->amountOfRooms != "2") $errors[] = 'Укажите количество смежных комнат';
			if ($this->amountOfAdjacentRooms > $this->amountOfRooms && $this->typeOfObject != "0" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж" && $this->adjacentRooms != "0" && $this->adjacentRooms != "нет" && $this->amountOfRooms != "0" && $this->amountOfRooms != "1" && $this->amountOfRooms != "2") $errors[] = 'Исправьте: количество смежных комнат не может быть больше общего количества комнат';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->typeOfBathrooms == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите тип санузла';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->typeOfBalcony == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: есть ли балкон, лоджия или эркер в сдаваемом объекте недвижимости';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->balconyGlazed == "0" && $this->typeOfBalcony != "0" && $this->typeOfBalcony != "нет" && $this->typeOfBalcony != "эркер" && $this->typeOfBalcony != "2 эркера и более") $errors[] = 'Укажите остекление балкона/лоджии';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->roomSpace == "" && $this->typeOfObject != "0" && $this->typeOfObject != "квартира" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите площадь комнаты';
		}
		if ($this->roomSpace != "") {
			if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->roomSpace)) $errors[] = 'Неправильный формат для площади комнаты, используйте только цифры и точку, например: 16.55';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->totalArea == "" && $this->typeOfObject != "0" && $this->typeOfObject != "комната") $errors[] = 'Укажите общую площадь';
		}
		if ($this->totalArea != "") {
			if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->totalArea)) $errors[] = 'Неправильный формат для общей площади, используйте только цифры и точку, например: 86.55';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->livingSpace == "" && $this->typeOfObject != "0" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж") $errors[] = 'Укажите жилую площадь';
		}
		if ($this->livingSpace != "") {
			if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->livingSpace)) $errors[] = 'Неправильный формат для жилой площади, используйте только цифры и точку, например: 86.55';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->kitchenSpace == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите площадь кухни';
		}
		if ($this->kitchenSpace != "") {
			if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $this->kitchenSpace)) $errors[] = 'Неправильный формат для площади кухни, используйте только цифры и точку, например: 86.55';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->floor == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите этаж, на котором расположена квартира, комната';
		}
		if ($this->floor != "") {
			if (!preg_match('/^\d{0,3}$/', $this->floor)) $errors[] = 'Неправильный формат для этажа, на котором расположена квартира, комната: должно быть не более 3 цифр';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->totalAmountFloor == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите количество этажей в доме';
		}
		if ($this->totalAmountFloor != "") {
			if (!preg_match('/^\d{0,3}$/', $this->totalAmountFloor)) $errors[] = 'Неправильный формат для количества этажей: должно быть не более 3 цифр';
		}
		if ($this->totalAmountFloor != "" && $this->floor != "" && $this->floor > $this->totalAmountFloor) $errors[] = 'Общее количество этажей в доме не может быть меньше этажа, на котором расположена Ваше недвижимость';

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->numberOfFloor == "" && $this->typeOfObject != "0" && $this->typeOfObject != "квартира" && $this->typeOfObject != "комната" && $this->typeOfObject != "гараж") $errors[] = 'Укажите количество этажей в доме';
		}
		if ($this->numberOfFloor != "") {
			if (!preg_match('/^\d{0,2}$/', $this->numberOfFloor)) $errors[] = 'Неправильный формат для количества этажей: должно быть не более 2 цифр';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->concierge == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "таунхаус" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: есть ли в доме консьерж';
			if ($this->intercom == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие домофона';
			if ($this->parking == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие и тип парковки во дворе';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert" || $typeOfValidation == "newAlienAdvert" || $typeOfValidation == "editAlienAdvert") {
			if ($this->city != "Екатеринбург") $errors[] = 'Укажите в качестве города местонахождения Екатеринбург';
			if ($this->district == "0") $errors[] = 'Укажите район';
			if ($this->coordX == "" || $this->coordY == "" || $this->address == "") $errors[] = 'Укажите улицу и номер дома, затем нажмите кнопку "Проверить адрес"';
			if ($this->coordX != "" && $this->coordY != "") {
				if (!preg_match('/^\d{0,3}\.\d{0,10}$/', $this->coordX) || !preg_match('/^\d{0,3}\.\d{0,10}$/', $this->coordY)) $errors[] = 'Убедитесь, что на карте метка указывает на Ваш дом';
			}
			if (strlen($this->address) > 60) $errors[] = 'Указан слишком длинный адрес (используйте не более 60 символов)';
			if ($this->apartmentNumber == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дом" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите номер квартиры';
			if (strlen($this->apartmentNumber) > 20) $errors[] = 'Указан слишком длинный номер квартиры (используйте не более 20 символов)';
		}
		// Убеждаемся что данный пользователь еще не публиковал объявлений по этому адресу. Не стоит позволять публиковать несколько разных объявлений одному человеку с привязкой к одному и тому же адресу
		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "newAlienAdvert") {
			if ($this->address != "" && $this->coordX != "" && $this->coordY != "") {
				$stmt = DBconnect::get()->stmt_init();
				if (($stmt->prepare("SELECT * FROM property WHERE (address=? OR (coordX=? AND coordY=?)) AND apartmentNumber=?") === FALSE)
					OR ($stmt->bind_param("ssss", $this->address, $this->coordX, $this->coordY, $this->apartmentNumber) === FALSE)
					OR ($stmt->execute() === FALSE)
					OR (($res = $stmt->get_result()) === FALSE)
					OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
					OR ($stmt->close() === FALSE)
				) {
					// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
					$errors[] = "К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.";
					return $errors;
				} else {
					if (!is_array($res)) {
						$errors[] = 'К сожалению, произошла ошибка при работе с базой данных (, попробуйте еще раз через некоторое время.';
						return $errors;
					}
					if (count($res) != 0) {
						if ($res[0]['apartmentNumber'] != "") $errors[] = 'Вы уже завели ранее объявление по данному адресу с таким же номером квартиры. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
						if ($res[0]['apartmentNumber'] == "") $errors[] = 'Вы уже завели ранее объявление по данному адресу. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
					}
				}
			}
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->subwayStation == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж") $errors[] = 'Укажите станцию метро рядом';
			if ($this->distanceToMetroStation == "" && $this->typeOfObject != "0" && $this->typeOfObject != "дача" && $this->typeOfObject != "гараж" && $this->subwayStation != "0" && $this->subwayStation != "нет") $errors[] = 'Укажите количество минут ходьбы до ближайшей станции метро';
		}
		if ($this->distanceToMetroStation != "") {
			if (!preg_match('/^\d{0,3}$/', $this->distanceToMetroStation)) $errors[] = 'Неправильный формат для количества минут ходьбы до ближайшей станции метро: должно быть не более 3 цифр';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert" || $typeOfValidation == "newAlienAdvert" || $typeOfValidation == "editAlienAdvert") {
			if ($this->currency == "0") $errors[] = 'Укажите валюту для рассчетов с арендатором(ами)';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert" || $typeOfValidation == "newAlienAdvert" || $typeOfValidation == "editAlienAdvert") {
			if ($this->costOfRenting == "") $errors[] = 'Укажите плату за аренду в месяц';
		}
		if ($this->costOfRenting != "") {
			if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->costOfRenting)) $errors[] = 'Неправильный формат для платы за аренду, используйте только цифры и точку, например: 25550.50';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->utilities == "0") $errors[] = 'Укажите условия оплаты коммунальных услуг';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->costInSummer == "" && $this->utilities != "0" && $this->utilities != "нет") $errors[] = 'Укажите примерную стоимость коммунальных услуг летом';
			if ($this->costInWinter == "" && $this->utilities != "0" && $this->utilities != "нет") $errors[] = 'Укажите примерную стоимость коммунальных услуг зимой';
		}
		if ($this->costInSummer != "") {
			if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->costInSummer)) $errors[] = 'Неправильный формат для стоимости коммунальных услуг летом, используйте только цифры и точку, например: 2550.50';
		}
		if ($this->costInWinter != "") {
			if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->costInWinter)) $errors[] = 'Неправильный формат для стоимости коммунальных услуг зимой, используйте только цифры и точку, например: 2550.50';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->electricPower == "0") $errors[] = 'Укажите условия оплаты электроэнергии';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->bail == "0") $errors[] = 'Укажите наличие залога';
			if ($this->bailCost == "" && $this->bail != "0" && $this->bail != "нет") $errors[] = 'Укажите величину залога';
		}
		if ($this->bailCost != "") {
			if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->bailCost)) $errors[] = 'Неправильный формат для величины залога, используйте только цифры и точку, например: 2550.50';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->prepayment == "0") $errors[] = 'Укажите: есть ли предоплата';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert" || $typeOfValidation == "newAlienAdvert" || $typeOfValidation == "editAlienAdvert") {
			if ($this->compensationMoney == "" || $this->compensationPercent == "") $errors[] = 'Укажите величину единоразовой комиссии';
		}
		if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $this->compensationMoney)) {
			$errors[] = 'Неправильный формат для величины единоразовой комиссии, используйте только цифры и точку, например: 1550.50';
		}
		if (!preg_match('/^\d{0,3}\.{0,1}\d{0,2}$/', $this->compensationPercent)) {
			$errors[] = 'Неправильный формат для величины единоразовой комиссии, используйте только цифры и точку, например: 15.75';
		} else {
			if ($this->compensationPercent > 30) $errors[] = "Слишком большая единовременная комиссия. При работе с нашим сайтом разрешается устанавливать размер единовременной комиссии не более 30% от месячной платы за аренду недвижимости";
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->repair == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите текущее состояние ремонта';
			if ($this->furnish == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите текущее состояние отделки';
			if ($this->windows == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите материал окон';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->internet == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие интернета';
			if ($this->telephoneLine == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие телефонной линии';
			if ($this->cableTV == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите наличие кабельного телевидения';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if (count($this->sexOfTenant) == 0 && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите допустимый пол арендатора';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if (count($this->relations) == 0 && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите допустимые взаимоотношения между арендаторами';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->children == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: готовы ли Вы поселить арендаторов с детьми';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->animals == "0" && $this->typeOfObject != "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите: готовы ли Вы поселить арендаторов с животными';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert" || $typeOfValidation == "newAlienAdvert" || $typeOfValidation == "editAlienAdvert") {
			if ($this->contactTelephonNumber != "") {
				if (!preg_match('/^[0-9]{10}$/', $this->contactTelephonNumber)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226540018';
			} else {
				$errors[] = 'Укажите контактный номер телефона собственника по этому объявлению';
			}
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->timeForRingBegin == "0" || $this->timeForRingEnd == "0") $errors[] = 'Укажите время, в которое Вы готовы принимать звонки от арендаторов';
			if ($this->timeForRingBegin + 0 > $this->timeForRingEnd + 0 && $this->timeForRingBegin != "0" && $this->timeForRingEnd != "0") $errors[] = 'Исправьте: время начала приема звонков не может быть больше, чем время окончания приема звонков';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->checking == "0") $errors[] = 'Укажите: где собирается проживать собственник';
		}

		if ($typeOfValidation == "newAdvert" || $typeOfValidation == "editAdvert") {
			if ($this->responsibility == "") $errors[] = 'Укажите: какую ответственность за состояние и ремонт объекта Вы берете на себя, а какую арендатор';
		}

		return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
	}

	// Используется при регистрации нового объекта недвижимости - позволяет получить идентификатор, используя адрес (для дальнейшего сохранения фотографий объекта).
	// Полученный идентификатор также указывается в параметрах данного объекта
	public function getIdUseAddress() {
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT id FROM property WHERE address=? AND coordX=? AND coordY=? AND apartmentNumber=?") === FALSE)
			OR ($stmt->bind_param("ssss", $this->address, $this->coordX, $this->coordY, $this->apartmentNumber) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR (count($res) === 0)
			OR ($stmt->close() === FALSE)
		) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			return FALSE;
		}

		$this->id = $res[0]['id'];
		return $this->id;
	}

	// Метод, который позволяет оповестить пользователей-арендаторов о появлении нового объекта недвижимости, который соответствует их параметрам поиска.
	// Создает и сохраняет в БД уведомления
	// ВАЖНО: перед использованием метода убедиться, что параметры объекта Property инициализированы теми значениями, которые нужны, в противном случае, пользователи могут получить уведомления, не соответствующие действительности
	public function sendMessagesAboutNewProperty() {

		// Для выполнения функция у объекта недвижимости обязательно должен быть id (то есть данные о нем уже сохранены в БД)
		if ($this->id == "") {
			// TODO: Логируем ошибку использования метода
			return FALSE;
		}

		/******
		 * Составляем поисковый запрос, который выявит список id пользователей-арендаторов, под чьи searchRequests подходит наше объявление (именно их и нужно оповестить)
		 ******/

		// Инициализируем массив, в который будем собирать условия поиска.
		$searchLimits = array();

		// Ограничение на тип объекта
		$searchLimits['typeOfObject'] = "";
		if (isset($this->typeOfObject) && $this->typeOfObject != "0") {
			$searchLimits['typeOfObject'] = " (typeOfObject = '0' || typeOfObject = '" . $this->typeOfObject . "')";
		}

		// Ограничение на количество комнат
		$searchLimits['amountOfRooms'] = "";
		if (isset($this->amountOfRooms) && $this->amountOfRooms != '0') {
			$searchLimits['amountOfRooms'] = " (amountOfRooms = 'a:0:{}' OR amountOfRooms LIKE '%" . $this->amountOfRooms . "%')";
		}

		// Ограничение на смежность комнат
		$searchLimits['adjacentRooms'] = "";
		if ($this->adjacentRooms == "0") $searchLimits['adjacentRooms'] = "";
		if ($this->adjacentRooms == "да") $searchLimits['adjacentRooms'] = " (adjacentRooms = 'не имеет значения' || adjacentRooms = '0')";
		if ($this->adjacentRooms == "нет") $searchLimits['adjacentRooms'] = "";

		// Ограничение на этаж
		$searchLimits['floor'] = "";
		if (isset($this->floor) && isset($this->totalAmountFloor) && $this->floor != 0 && $this->totalAmountFloor != 0 && $this->floor != "" && $this->totalAmountFloor != "") {
			if ($this->floor == 1) $searchLimits['floor'] = " (floor = '0' OR floor = 'любой')";
			if ($this->floor != 1 && $this->floor == $this->totalAmountFloor) $searchLimits['floor'] = " (floor = '0' OR floor = 'любой' OR floor = 'не первый')";
			if ($this->floor != 1 && $this->floor != $this->totalAmountFloor) $searchLimits['floor'] = "";
		}

		// Ограничение на минимальную сумму арендной платы
		$searchLimits['minCost'] = "";
		if (isset($this->realCostOfRenting) && $this->realCostOfRenting != "" && $this->realCostOfRenting != 0) {
			$searchLimits['minCost'] = " (minCost >= " . $this->realCostOfRenting . ")";
		}

		// Ограничение на максимальную сумму арендной платы
		$searchLimits['maxCost'] = "";
		if (isset($this->realCostOfRenting) && $this->realCostOfRenting != "" && $this->realCostOfRenting != 0) {
			$searchLimits['maxCost'] = " (maxCost <= " . $this->realCostOfRenting . ")";
		}

		// Ограничение на максимальный залог
		// отношение realCostOfRenting / costOfRenting позволяет вычислить курс валюты, либо получить 1, если стоимость аренды указана собственником в рублях
		$searchLimits['pledge'] = "";
		if (isset($this->bailCost) && isset($this->realCostOfRenting) && isset($this->costOfRenting) && $this->bailCost != "" && $this->realCostOfRenting != "" && $this->costOfRenting != "" && $this->bailCost != 0 && $this->realCostOfRenting != 0 && $this->costOfRenting != 0) {
			$searchLimits['pledge'] = " (pledge >= " . $this->bailCost * $this->realCostOfRenting / $this->costOfRenting . ")";
		}

		// Ограничение на максимальную предоплату
		$searchLimits['prepayment'] = "";
		if (isset($this->prepayment) && $this->prepayment != '0') {
			$searchLimits['prepayment'] = " (prepayment + 0 >= '" . $this->prepayment . "')";
		}

		// Ограничение на район
		$searchLimits['district'] = "";
		if (isset($this->district) && $this->district != '0') {
			$searchLimits['district'] = " (district = 'a:0:{}' OR district LIKE '%" . $this->district . "%')";
		}

		// Ограничение на формат проживания (с кем)
		$searchLimits['withWho'] = "";
		if (isset($this->relations) && is_array($this->relations) && count($this->relations) != 0) {
			$searchLimits['withWho'] = " (";
			for ($i = 0, $s = count($this->relations); $i < $s; $i++) {
				$searchLimits['withWho'] .= " withWho LIKE '%" . $this->relations[$i] . "%'";
				if ($i < count($this->relations) - 1) $searchLimits['withWho'] .= " OR";
			}
			$searchLimits['withWho'] .= " )";
		}
		//TODO: если есть ограничение на пол и возможно проживание 1 человека, то сделать еще проверку на пол этого арендатора

		// Ограничение на проживание с детьми
		$searchLimits['children'] = "";
		if (isset($this->children) && $this->children != "0") {
			if ($this->children == "не имеет значения") $searchLimits['children'] = "";
			if ($this->children == "с детьми старше 4-х лет") $searchLimits['children'] = " (children = '0' OR children = 'без детей' OR children = 'с детьми старше 4-х лет')";
			if ($this->children == "только без детей") $searchLimits['children'] = " (children = '0' OR children = 'без детей')";
		}

		// Ограничение на проживание с животными
		$searchLimits['animals'] = "";
		if (isset($this->animals) && $this->animals != "0") {
			if ($this->animals == "не имеет значения") $searchLimits['animals'] = "";
			if ($this->animals == "только без животных") $searchLimits['animals'] = " (animals = '0' OR animals = 'без животных')";
		}

		// Ограничение на длительность аренды
		$searchLimits['termOfLease'] = "";
		if (isset($this->termOfLease) && $this->termOfLease != "0") {
			if ($this->termOfLease == "длительный срок") $searchLimits['termOfLease'] = " (termOfLease = '0' OR termOfLease = 'длительный срок')";
			if ($this->termOfLease == "несколько месяцев") $searchLimits['termOfLease'] = " (termOfLease = '0' OR termOfLease = 'несколько месяцев')";
		}

		// Собираем строку WHERE для поискового запроса к БД
		$strWHERE = "";
		foreach ($searchLimits as $value) {
			if ($value == "") continue;
			if ($strWHERE != "") $strWHERE .= " AND" . $value; else $strWHERE .= $value;
		}

		// Получаем идентификаторы всех пользователей-арендаторов, чьим поисковым запросам соответствует данный объект недвижимости
		$targetUsers = array();
		$res = DBconnect::get()->query("SELECT userId FROM searchRequests WHERE" . $strWHERE);
		if ((DBconnect::get()->errno)
			OR (($targetUsers = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			return FALSE;
		}

		// Сохраняем в БД информацию об уведомлении для кажжого такого пользователя-арендатора
		$currentTargetUser = "0"; // Инициализируем переменную, в которую поочередно будем складывать id пользователей-арендаторов
		$tm = time(); // Получаем текущее время
		$messageType = "newProperty"; // Задаем тип уведомления
		$isReaded = "не прочитано";
		$fotoArrSerialized = serialize($this->uploadedFoto);

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("INSERT INTO messagesNewProperty (userId, timeIndex, messageType, isReaded, fotoArr, targetId, typeOfObject, address, currency, costOfRenting, utilities, electricPower, amountOfRooms, adjacentRooms, amountOfAdjacentRooms, roomSpace, totalArea, livingSpace, kitchenSpace, totalAmountFloor, numberOfFloor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
			OR ($stmt->bind_param("iisssisssssssssssssii", $currentTargetUser, $tm, $messageType, $isReaded, $fotoArrSerialized, $this->id, $this->typeOfObject, $this->address, $this->currency, $this->costOfRenting, $this->utilities, $this->electricPower, $this->amountOfRooms, $this->adjacentRooms, $this->amountOfAdjacentRooms, $this->roomSpace, $this->totalArea, $this->livingSpace, $this->kitchenSpace, $this->totalAmountFloor, $this->numberOfFloor) === FALSE)
		) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			return FALSE;
		}

		for ($i = 0, $s = count($targetUsers); $i < $s; $i++) {

			// Подставляем новый идентификатор пользователя-арендатора
			$currentTargetUser = $targetUsers[$i]['userId'];

			// Записываем в БД для него уведомление про новый объект недвижимости
			if (($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
			) {
				//TODO: Логируем ошибку
			}

		}

		$stmt->close();

		return TRUE;
	}

	// Возвращает массив, содержащий список недвижимости в жилой зоне: включая как чекбокс элементы ($furnitureInLivingArea), так и элементы списка extra ($furnitureInLivingAreaExtra)
	public function getFurnitureInLivingAreaAll() {
		// Мебель в жилой зоне, отмеченная галочками
		$furnitureInLivingArea = $this->furnitureInLivingArea;
		// Скидываем в массив всю мебель, которая была добавлена вручную
		$furnitureInLivingArea = array_merge($furnitureInLivingArea, explode(', ', $this->furnitureInLivingAreaExtra));
		// Дополнительная проверка на пустоту нужна, так как пустая строчка после explode воспринимается как один из членов массива
		$furnitureInLivingArea = array_filter($furnitureInLivingArea, function ($el) {
			return !empty($el);
		});

		return $furnitureInLivingArea;
	}

	// Возвращает массив, содержащий список недвижимости в жилой зоне: включая как чекбокс элементы ($furnitureInKitchen), так и элементы списка extra ($furnitureInKitchenExtra)
	public function getFurnitureInKitchenAll() {
		// Мебель на кухне, отмеченная галочками
		$furnitureInKitchen = $this->furnitureInKitchen;
		// Скидываем в массив всю мебель, которая была добавлена вручную
		$furnitureInKitchen = array_merge($furnitureInKitchen, explode(', ', $this->furnitureInKitchenExtra));
		// Дополнительная проверка на пустоту нужна, так как пустая строчка после explode воспринимается как один из членов массива
		$furnitureInKitchen = array_filter($furnitureInKitchen, function ($el) {
			return !empty($el);
		});

		return $furnitureInKitchen;
	}

	// Возвращает массив, содержащий список недвижимости в жилой зоне: включая как чекбокс элементы ($appliances), так и элементы списка extra ($appliancesExtra)
	public function getAppliancesAll() {
		// Бытовая техника, отмеченная галочками
		$appliances = $this->appliances;
		// Скидываем в массив всю бытовую технику, которая была добавлена вручную
		$appliances = array_merge($appliances, explode(', ', $this->appliancesExtra));
		// Дополнительная проверка на пустоту нужна, так как пустая строчка после explode воспринимается как один из членов массива
		$appliances = array_filter($appliances, function ($el) {
			return !empty($el);
		});

		return $appliances;
	}

	/**
	 * Устанавливает или меняет ближайшую дату просмотра у данного объекта.
	 * Но метод не сохраняет данные в БД - для этого нужно вызвать $this->saveCharacteristicToDB
	 *
	 * @param $earliestDate - новая дата просмотра в формате: 27.01.1987
	 * @param $earliestTimeHours - новый час просмотра в 24-х часовом формате
	 * @param $earliestTimeMinutes - новые минуты просмотра (от 0 до 59)
	 * @return bool - возвращает TRUE в случае успеха и FALSE в случае, если дата просмотра в БД не была изменена по каким-либо причинам
	 */
	public function changeEarliestDate($earliestDate, $earliestTimeHours, $earliestTimeMinutes) {

		// Валидация наличия входящих данных
		if (!isset($earliestDate) OR
			!isset($earliestTimeHours) OR
			!isset($earliestTimeMinutes))
		{ return FALSE;}

		// Преобразование входящих данных
		$earliestDate = GlobFunc::dateFromDBToView(GlobFunc::dateFromViewToDB($earliestDate)); // Такое преобразование позволяет убедиться в том, что дата по формату соответствует всем критериям

		// Валидация достоверности входящих данных
		if ($earliestDate == "0000-00-00" OR
			$earliestDate == "" OR
			$earliestTimeHours < "00" OR
			$earliestTimeHours > "23" OR
			$earliestTimeMinutes < "00" OR
			$earliestTimeMinutes > "59")
		{return FALSE;}

		// Изменим параметры даты и времени ближайшего просмотра
		$this->earliestDate = $earliestDate;
		$this->earliestTimeHours = $earliestTimeHours;
		$this->earliestTimeMinutes = $earliestTimeMinutes;

		return TRUE;
	}

}
