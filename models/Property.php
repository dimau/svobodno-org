<?php
/**
 * Класс представляем собой полную модель объекта недвижимости (объявления)
 *
 * Схема работы с объектами класса:
 * 1. Инициализация (в качестве параметра конструктору можно передать id объекта). При этом параметры объекта устанавливаются по умолчанию (пустые значения)
 * 2. Записать в параметры объекта нужные данные. С помощью методов write записать в параметры объекта данные из БД или POST
 * 3. Выполнить манипуляции с объектом и его параметрами
 * 4. Записать изменившиеся значения параметров объекта в БД
 */

class Property
{
	private $id = "";
	private $userId = "";
	private $typeOfObject = "0";
	private $dateOfEntry = "";
	private $termOfLease = "0";
	private $dateOfCheckOut = "";
	private $amountOfRooms = "0";
	private $adjacentRooms = "0";
	private $amountOfAdjacentRooms = "0";
	private $typeOfBathrooms = "0";
	private $typeOfBalcony = "0";
	private $balconyGlazed = "0";
	private $roomSpace = "";
	private $totalArea = "";
	private $livingSpace = "";
	private $kitchenSpace = "";
	private $floor = "";
	private $totalAmountFloor = "";
	private $numberOfFloor = "";
	private $concierge = "0";
	private $intercom = "0";
	private $parking = "0";
	private $city = "Екатеринбург";
	private $district = "0";
	private $coordX = "";
	private $coordY = "";
	private $address = "";
	private $apartmentNumber = "";
	private $subwayStation = "0";
	private $distanceToMetroStation = "";
	private $currency = "0";
	private $costOfRenting = "";
	private $realCostOfRenting = "";
	private $utilities = "0";
	private $costInSummer = "";
	private $costInWinter = "";
	private $electricPower = "0";
	private $bail = "0";
	private $bailCost = "";
	private $prepayment = "0";
	private $compensationMoney = "";
	private $compensationPercent = "";
	private $repair = "0";
	private $furnish = "0";
	private $windows = "0";
	private $internet = "0";
	private $telephoneLine = "0";
	private $cableTV = "0";
	private $furnitureInLivingArea = array();
	private $furnitureInLivingAreaExtra = "";
	private $furnitureInKitchen = array();
	private $furnitureInKitchenExtra = "";
	private $appliances = array();
	private $appliancesExtra = "";
	private $sexOfTenant = array();
	private $relations = array();
	private $children = "0";
	private $animals = "0";
	private $contactTelephonNumber = "";
	private $timeForRingBegin = "0";
	private $timeForRingEnd = "0";
	private $checking = "0";
	private $responsibility = "";
	private $comment = "";
	private $last_act = "";
	private $reg_date = "";
	private $status = "";
	private $earliestDate = "";
	private $earliestTimeHours = "";
	private $earliestTimeMinutes = "";
	private $adminComment = "";
	private $completeness = "";

	private $fileUploadId = "";
	private $uploadedFoto = array(); // В переменной будет храниться информация о загруженных фотографиях. Представляет собой массив ассоциированных массивов
	private $primaryFotoId = "";

	private $ownerLogin = ""; // Параметр содержит логин пользователя-собственника (необходим для того, чтобя выездные агенты могли создавать новые объявления и присваивать их ране зарегистрированным собственникам)

	/**
	 * КОНСТРУКТОР
	 *
	 * Конструктор всегда инициализирует параметры объекта пустыми значениями.
	 * Если объект создается под существующее объявление, то нужно сразу указать id этого объекта недвижимости (в параметрах конструктора), либо передать ассоциированный массив, содержащий значения параметров создаваемого объекта
	 * Инициализация объекта параметрами существующего объявления выделена в отдельные методы (writeCharacteristicFrom.., writeFotoInformationFrom..), что позволяет убедиться в их успешном выполнении (получении данных из БД или из POST), а также выполнить инициализацию только тех параметров, которые понадобятся в работе с этим объектом (характеристика и/или данные о фотографиях). Ну и кроме того, это позволяет инициализировать объект параметрами как из БД, так и из POST запроса по выбору.
	 *
	 * @param int|array $params либо идентификтатор конкретного объекта недвижимости, либо ассоциативный массив параметров объекта недвижимости
	 */
	public function __construct($params) {
		// Инициализируем переменную "сессии" для временного сохранения фотографий
		$this->fileUploadId = GlobFunc::generateCode(7);

		// Если конструктору передан идентификатор объекта недвижимости, запишем его в параметры объекта. Это позволит, например, инициализировать объект данными из БД
		if (isset($params) && is_int($params)) $this->id = $params;

		// Если конструктору передан массив, то инициализируем параметры объекта значениями этого массива с соответствующими ассоциативными ключами
		if (isset($params) && is_array($params)) {
			$this->initialization($params);
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getUserId() {
		return $this->userId;
	}

    public function getAddress() {
        return $this->address;
    }

	public function getStatus() {
		return $this->status;
	}

	public function getCompleteness() {
		return $this->completeness;
	}

    /**
     * Устанавливает признак полноты для объекта
     * ВАЖНО: сохранения в БД функция не выполняет, устанавливает признак лишь для текущего объекта
     *
     * @param $levelCompleteness
     * $levelCompleteness = "0" объявление из чужой базы - мнимум требований к полноте
     * $levelCompleteness = "1" объявление от собственника, который является нашим клиентом - максимальные требования к полноте
     * @return bool возвращает TRUE в случае успешной установки признака у нашего объекта, FALSE в противном случае
     */
    public function setCompleteness($levelCompleteness) {
        // Проверка входных данных на адекватность
        if ($levelCompleteness != "1" && $levelCompleteness != "0") return FALSE;
        $this->completeness = $levelCompleteness;
        return TRUE;
    }

	/**
	 * Метод для инициализации параметров объекта конкретными значениями
	 *
	 * @param array $params ассоциативный массив, содержащий значения параметров для инициализации
     * @return bool TRUE в случае успешного перебора и FALSE в противном случае.
	 */
	private function initialization($params) {

        // Валидация исходных данных
        if (!isset($params) || !is_array($params)) return FALSE;

        // Перебираем полученный ассоциативный массив и присваиваем значения его параметров параметрам объекта
		foreach ($params as $key => $value) {
			if (isset($this->$key)) $this->$key = $value;
		}

        return TRUE;
	}

	// Функция сохраняет текущие параметры объекта недвижимости в БД
	// $typeOfProperty = "new" - режим сохранения для нового объекта недвижимости
	// $typeOfProperty = "edit" - режим сохранения для редактируемых параметров объекта недвижимости
	// Кроме того, при успешной работе изменяет статус typeOwner пользователя (с id = userId) на TRUE
	// Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
	public function saveCharacteristicToDB($typeOfProperty) {

		// Валидация необходимых исходных данных
		if ($typeOfProperty != "new" && $typeOfProperty != "edit") return FALSE; // Если объявление не является ни новым, ни существующим - видимо какая-то ошибка была допущена при передаче параметров методу
		if ($typeOfProperty == "new" && $this->ownerLogin == "") return FALSE;
		if ($typeOfProperty == "edit" && $this->userId == "") return FALSE;

		// Вычислим по логину id пользователя-собственника данного объекта недвижимости, если создается новое объявление выездным специалистом со своего аккаунта
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

			$this->userId = $res[0]['id'];
		}

		// Если не указан id пользователя собственника, то дальнейшие действия не имеют смысла
		if ($this->userId == "") return FALSE;

		// Меняем время последней операции над характеристикой объекта недвижимости
		$this->last_act = time();

		// Проверяем в какой валюте сохраняется стоимость аренды, формируем переменную realCostOfRenting
		if ($this->currency == 'руб.') {
            $this->realCostOfRenting = $this->costOfRenting;
        } else {
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

            $this->realCostOfRenting = $this->costOfRenting * $res[0]['value'];
        }

		// Пишем данные объекта недвижимости в БД.
		// Код для сохранения данных разный: для нового объявления и при редактировании параметров существующего объявления
		if ($typeOfProperty == "new") {

			// Довычисляем недостающие для нового объекта параметры
			$this->reg_date = time(); // Время регистрации ("рождения") объявления
			if ($this->status != "опубликовано" && $this->status != "не опубликовано") $this->status = "опубликовано"; // На всякий случай: если выездной специалист не указал статус при формировании характеристики объекта
			if ($this->completeness != "0" && $this->completeness != "1") $this->completeness = "1"; // На всякий случай: возможно, будет полезно для нового объявления

			// Непосредственное сохранение характеристики объекта в БД
			if (!DBconnect::insertPropertyCharacteristic($this->getCharacteristicData())) return FALSE;
			// Узнаем id объекта недвижимости - необходимо при сохранении информации о фотках в постоянную базу
			$this->getIdUseAddress();
			// Изменим статус пользователя (typeOwner), так как он теперь точно стал собственником
			DBconnect::updateUserCharacteristicTypeUser($this->userId, "typeOwner", "TRUE");

		} elseif ($typeOfProperty == "edit") {

            // Непосредственное сохранение характеристики объекта в БД
            if (!DBconnect::updatePropertyCharacteristic($this->getCharacteristicData())) return FALSE;
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
				// Пометим все члены массива признаком их получения из таблицы propertyFotos
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
		// на INSERT новых строк в propertyFotos
		// на DELETE более ненужных фоток из propertyFotos
		$strINSERT = "";
		$strDELETE = "";
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {

			if ($allFotos[$i]['fromTable'] == "tempFotos" && $allFotos[$i]['forRemove'] == FALSE) {
				if ($strINSERT != "") $strINSERT .= ",";
				$strINSERT .= "('" . $allFotos[$i]['id'] . "','" . $allFotos[$i]['folder'] . "','" . $allFotos[$i]['filename'] . "','" . $allFotos[$i]['extension'] . "','" . $allFotos[$i]['filesizeMb'] . "','" . $this->id . "','" . $allFotos[$i]['status'] . "','" . $allFotos[$i]['regDate'] . "')";
			}

			if ($allFotos[$i]['forRemove'] == TRUE) {
				if ($strDELETE != "") $strDELETE .= " OR";
				$strDELETE .= " id = '" . $allFotos[$i]['id'] . "'";
			}

		}

		// Выполним сформированные запросы
		// INSERT
		if ($strINSERT != "") {
			DBconnect::get()->query("INSERT INTO propertyFotos (id, folder, filename, extension, filesizeMb, propertyId, status, regDate) VALUES " . $strINSERT);
			if ((DBconnect::get()->errno)
				OR (($res = DBconnect::get()->affected_rows) === -1)
				OR ($res === 0)
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO propertyFotos (id, folder, filename, extension, filesizeMb, propertyId, status, regDate) VALUES " . $strINSERT . "' id логгера: Property.php->saveFotoInformationToDB():8. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID объекта недвижимости: " . $this->id);
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
		$this->readFotoInformationFromDB();

		return TRUE;
	}

	// Метод читает данные объекта недвижимости из БД и записывает их в параметры данного объекта
	public function readCharacteristicFromDB() {

		// Если идентификатор объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
		if ($this->id == "") return FALSE;

		// Получим из БД данные ($res) по объекту недвижимости с идентификатором = $this->id
		$res = DBconnect::selectPropertyCharacteristic($this->id);

		// Если мы получили пустой массив, значит данные в БД по этому объекту не найдены
		if (!is_array($res) || count($res) == 0) return FALSE;

		// Передаем данные для инициализации параметров объекта
		if (!$this->initialization($res)) return FALSE;

		return TRUE;
	}

	// Метод читает данные объекта недвижимости из архивной таблицы БД и записывает их в параметры данного объекта
	public function readCharacteristicFromArchive() {

		// Если идентификатор объекта недвижимости неизвестен, то дальнейшие действия не имеют смысла
		if ($this->id == "") return FALSE;

		// Получим из БД данные ($res) по объекту недвижимости с идентификатором = $this->id
		$res = DBconnect::selectPropertyCharacteristicFromArchive($this->id);

		// Если получено меньше или больше одной строки (одного объекта недвижимости) из БД, то сообщаем об ошибке
		if (!is_array($res) || count($res) != 1) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Property->readCharacteristicFromArchive():1 Ошибка: по id объекта недвижимости == ".$this->id." получено ".count($res)." результатов выборки данных из таблицы archiveAdverts БД. Должна быть только 1 строка. ID пользователя: не определено");
			return FALSE;
		}

		// Передаем данные для инициализации параметров объекта
		$this->initialization($res[0]);

		return TRUE;
	}

	// Метод читает данные о фотографиях из БД и записывает их в параметры объекта недвижимости
	// Для корректной работы в параметрах объекта должен быть указан id объекта недвижимости ($this->id)
	public function readFotoInformationFromDB() {

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

		$result['id'] = $this->id;
		$result['userId'] = $this->userId;
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
		$result['realCostOfRenting'] = $this->realCostOfRenting;
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
		$result['last_act'] = $this->last_act;
		$result['reg_date'] = $this->reg_date;
		$result['status'] = $this->status;
		$result['earliestDate'] = $this->earliestDate;
		$result['earliestTimeHours'] = $this->earliestTimeHours;
		$result['earliestTimeMinutes'] = $this->earliestTimeMinutes;
		$result['adminComment'] = $this->adminComment;
		$result['completeness'] = $this->completeness;
		$result['ownerLogin'] = $this->ownerLogin;

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

	// $typeOfValidation = newAdvert - режим первичной (для нового объявления) проверки указанных пользователем параметров объекта недвижимости
	// $typeOfValidation = editAdvert - режим вторичной (при редактировании уже существующего объявления) проверки указанных пользователем параметров объекта недвижимости
	// $typeOfValidation = newAlienAdvert - режим проверки параметров нового объявления из чужой базы по минимуму - так как о чужих объектах обычно мало информации.
	// $typeOfValidation = editAlienAdvert - режим проверки параметров ранее созданного и записанного в БД объявления из чужой базы. По минимуму - так как о чужих объектах обычно мало информации.
	public function validate($typeOfValidation) {
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
	private function getIdUseAddress() {
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

	// Метод с минимальной задержкой Запускает механизм оповещения о Новом объекте недвижимости (уведомления + email + sms).
	// Сам механизм оповещения выполняется в отдельном скрипте, что позволяет данному методу не дожидаться его окончания
	public function notifyUsersAboutNewProperty() {

		$parts = parse_url("http://svobodno.org/lib/notificationAboutNewProperty.php");
		//TODO: test
		//$parts = parse_url("http://localhost/lib/notificationAboutNewProperty.php");
		$params = array("propertyId" => $this->id);

		if (!$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80))
		{
			Logger::getLogger(GlobFunc::$loggerName)->log("Property->notifyUsersAboutNewProperty():1 не удалось запустить скрипт оповещения о новом объекте недвижимости для:".$this->id." ".$this->address);
			return FALSE;
		}

		$data = http_build_query($params, '', '&');

		fwrite($fp, "POST " . (!empty($parts['path']) ? $parts['path'] : '/') . " HTTP/1.1\r\n");
		fwrite($fp, "Host: " . $parts['host'] . "\r\n");
		fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
		fwrite($fp, "Content-Length: " . strlen($data) . "\r\n");
		fwrite($fp, "Connection: Close\r\n\r\n");
		fwrite($fp, $data);
		fclose($fp);

		return TRUE;
	}

	// Метод определяет каким пользователям подходит данный объект по их параметрам поиска
	// Возвращает массив ассоциативных массивов c параметрами этих пользователей: (id, needEmail, needSMS)
	public function whichTenantsAppropriate() {

		//Составляем поисковый запрос, который выявит список id пользователей-арендаторов, под чьи searchRequests подходит наше объявление (именно их и нужно оповестить)

		// Инициализируем массив, в который будем собирать условия поиска.
		$searchLimits = array();

		// Ограничение на тип объекта
		$searchLimits['typeOfObject'] = "";
		if (isset($this->typeOfObject) && $this->typeOfObject != "0") {
			$searchLimits['typeOfObject'] = " (searchRequests.typeOfObject = '0' || searchRequests.typeOfObject = '" . $this->typeOfObject . "')";
		}

		// Ограничение на количество комнат
		$searchLimits['amountOfRooms'] = "";
		if (isset($this->amountOfRooms) && $this->amountOfRooms != '0') {
			$searchLimits['amountOfRooms'] = " (searchRequests.amountOfRooms = 'a:0:{}' OR searchRequests.amountOfRooms LIKE '%" . $this->amountOfRooms . "%')";
		}

		// Ограничение на смежность комнат
		$searchLimits['adjacentRooms'] = "";
		if ($this->adjacentRooms == "0") $searchLimits['adjacentRooms'] = "";
		if ($this->adjacentRooms == "да") $searchLimits['adjacentRooms'] = " (searchRequests.adjacentRooms = 'не имеет значения' || searchRequests.adjacentRooms = '0')";
		if ($this->adjacentRooms == "нет") $searchLimits['adjacentRooms'] = "";

		// Ограничение на этаж
		$searchLimits['floor'] = "";
		if (isset($this->floor) && isset($this->totalAmountFloor) && $this->floor != 0 && $this->totalAmountFloor != 0 && $this->floor != "" && $this->totalAmountFloor != "") {
			if ($this->floor == 1) $searchLimits['floor'] = " (searchRequests.floor = '0' OR searchRequests.floor = 'любой')";
			if ($this->floor != 1 && $this->floor == $this->totalAmountFloor) $searchLimits['floor'] = " (searchRequests.floor = '0' OR searchRequests.floor = 'любой' OR searchRequests.floor = 'не первый')";
			if ($this->floor != 1 && $this->floor != $this->totalAmountFloor) $searchLimits['floor'] = "";
		}

		// Ограничение на минимальную сумму арендной платы
		$searchLimits['minCost'] = "";
		if (isset($this->realCostOfRenting) && $this->realCostOfRenting != "" && $this->realCostOfRenting != 0) {
			$searchLimits['minCost'] = " (searchRequests.minCost <= " . $this->realCostOfRenting . ")";
		}

		// Ограничение на максимальную сумму арендной платы
		$searchLimits['maxCost'] = "";
		if (isset($this->realCostOfRenting) && $this->realCostOfRenting != "" && $this->realCostOfRenting != 0) {
			$searchLimits['maxCost'] = " (searchRequests.maxCost >= " . $this->realCostOfRenting . ")";
		}

		// Ограничение на максимальный залог
		// отношение realCostOfRenting / costOfRenting позволяет вычислить курс валюты, либо получить 1, если стоимость аренды указана собственником в рублях
		$searchLimits['pledge'] = "";
		if (isset($this->bailCost) && isset($this->realCostOfRenting) && isset($this->costOfRenting) && $this->bailCost != "" && $this->realCostOfRenting != "" && $this->costOfRenting != "" && $this->bailCost != 0 && $this->realCostOfRenting != 0 && $this->costOfRenting != 0) {
			$searchLimits['pledge'] = " (searchRequests.pledge >= " . $this->bailCost * $this->realCostOfRenting / $this->costOfRenting . ")";
		}

		// Ограничение на максимальную предоплату
		$searchLimits['prepayment'] = "";
		if (isset($this->prepayment) && $this->prepayment != '0') {
			$searchLimits['prepayment'] = " (searchRequests.prepayment + 0 >= '" . $this->prepayment . "')";
		}

		// Ограничение на район
		$searchLimits['district'] = "";
		if (isset($this->district) && $this->district != '0') {
			$searchLimits['district'] = " (searchRequests.district = 'a:0:{}' OR searchRequests.district LIKE '%" . $this->district . "%')";
		}

		// Ограничение на формат проживания (с кем)
		$searchLimits['withWho'] = "";
		if (isset($this->relations) && is_array($this->relations) && count($this->relations) != 0) {
			$searchLimits['withWho'] = " (";
			for ($i = 0, $s = count($this->relations); $i < $s; $i++) {
				$searchLimits['withWho'] .= " searchRequests.withWho LIKE '%" . $this->relations[$i] . "%'";
				if ($i < count($this->relations) - 1) $searchLimits['withWho'] .= " OR";
			}
			$searchLimits['withWho'] .= " )";
		}
		//TODO: если есть ограничение на пол и возможно проживание 1 человека, то сделать еще проверку на пол этого арендатора

		// Ограничение на проживание с детьми
		$searchLimits['children'] = "";
		if (isset($this->children) && $this->children != "0") {
			if ($this->children == "не имеет значения") $searchLimits['children'] = "";
			if ($this->children == "с детьми старше 4-х лет") $searchLimits['children'] = " (searchRequests.children = '0' OR searchRequests.children = 'без детей' OR searchRequests.children = 'с детьми старше 4-х лет')";
			if ($this->children == "только без детей") $searchLimits['children'] = " (searchRequests.children = '0' OR searchRequests.children = 'без детей')";
		}

		// Ограничение на проживание с животными
		$searchLimits['animals'] = "";
		if (isset($this->animals) && $this->animals != "0") {
			if ($this->animals == "не имеет значения") $searchLimits['animals'] = "";
			if ($this->animals == "только без животных") $searchLimits['animals'] = " (searchRequests.animals = '0' OR searchRequests.animals = 'без животных')";
		}

		// Ограничение на длительность аренды
		$searchLimits['termOfLease'] = "";
		if (isset($this->termOfLease) && $this->termOfLease != "0") {
			if ($this->termOfLease == "длительный срок") $searchLimits['termOfLease'] = " (searchRequests.termOfLease = '0' OR searchRequests.termOfLease = 'длительный срок')";
			if ($this->termOfLease == "несколько месяцев") $searchLimits['termOfLease'] = " (searchRequests.termOfLease = '0' OR searchRequests.termOfLease = 'несколько месяцев')";
		}

		// Собираем строку WHERE для поискового запроса к БД
		$strWHERE = "";
		foreach ($searchLimits as $value) {
			if ($value == "") continue;
			if ($strWHERE != "") $strWHERE .= " AND" . $value; else $strWHERE .= $value;
		}

		// Получаем идентификаторы и параметры рассылки всех пользователей-арендаторов, чьим поисковым запросам соответствует данный объект недвижимости
		$res = DBconnect::get()->query("SELECT searchRequests.userId AS userId, searchRequests.needEmail AS needEmail, searchRequests.needSMS AS needSMS, users.name AS name, users.email AS email, users.telephon AS telephon FROM searchRequests, users WHERE" . $strWHERE." AND searchRequests.userId = users.id");
		//$res = DBconnect::get()->query("SELECT searchRequests.userId, searchRequests.needEmail, searchRequests.needSMS, users.name, users.email, users.telephon FROM searchRequests, users WHERE" . $strWHERE." AND searchRequests.userId = users.id");
		if ((DBconnect::get()->errno)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT searchRequests.userId AS userId, searchRequests.needEmail AS needEmail, searchRequests.needSMS AS needSMS, user.name AS name, user.email AS email, user.telephon AS telephon FROM searchRequests, user WHERE" . $strWHERE." AND searchRequests.userId = user.id'. id логгера: DBconnect::whichTenantsAppropriate():1. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID пользователя: не определено");
			return FALSE;
		} else {
			for ($i = 0, $s = count($res); $i < $s; $i++ ) {
				$res[$i]['userId'] = intval($res[$i]['userId']);
				$res[$i]['needEmail'] = intval($res[$i]['needEmail']);
				$res[$i]['needSMS'] = intval($res[$i]['needSMS']);
			}
		}

		return $res;
	}

	// Метод, который позволяет оповестить пользователей-арендаторов о появлении нового объекта недвижимости, который соответствует их параметрам поиска.
	// Создает и сохраняет в БД уведомления
	// ВАЖНО: перед использованием метода убедиться, что параметры объекта Property инициализированы теми значениями, которые нужны, в противном случае, пользователи могут получить уведомления, не соответствующие действительности
	public function sendMessagesAboutNewProperty($listOfTargetUsers) {

		// Для выполнения функция у объекта недвижимости обязательно должен быть id (то есть данные о нем уже сохранены в БД)
		if ($this->id == "") return FALSE;

		// Подготовим параметры уведомления для сохранения в БД
		$tm = time(); // Время регистрации уведомлений
		$messageType = "newProperty"; // Задаем тип уведомления
		$isReaded = "не прочитано"; // Первоначально все уведомления попадают в БД непрочитанными

		return DBconnect::insertMessageNewProperty(array("timeIndex" => $tm, "messageType" => $messageType, "isReaded" => $isReaded, "fotoArr" => $this->uploadedFoto, "targetId" => $this->id, "typeOfObject" => $this->typeOfObject, "address" => $this->address, "currency" => $this->currency, "costOfRenting" => $this->costOfRenting, "utilities" => $this->utilities, "electricPower" => $this->electricPower, "amountOfRooms" => $this->amountOfRooms, "adjacentRooms" => $this->adjacentRooms, "amountOfAdjacentRooms" => $this->amountOfAdjacentRooms, "roomSpace" => $this->roomSpace, "totalArea" => $this->totalArea, "livingSpace" => $this->livingSpace, "kitchenSpace" => $this->kitchenSpace, "totalAmountFloor" => $this->totalAmountFloor, "numberOfFloor" => $this->numberOfFloor), $listOfTargetUsers);
	}

	public function sendEmailAboutNewProperty($listOfTargetUsersForEmail) {

		// Проверка входных данных
		if (!isset($listOfTargetUsersForEmail) || !is_array($listOfTargetUsersForEmail) || count($listOfTargetUsersForEmail) == 0) return FALSE;

		// Инициализируем класс для отправки e-mail и указываем постоянные параметры (верные для любых уведомлений)
		$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch

		// Вычислим HTML для электронного письма
		$MsgHTML = View::getHTMLforEmailAboutNewProperty($this->getCharacteristicData());

		// Инициализируем общие параметры всех email по данному оьъявлению
		try {
			$mail->CharSet = "utf-8";
			$mail->SetFrom('support@svobodno.org', 'Svobodno.org');
			$mail->AddReplyTo('support@svobodno.org', 'Svobodno.org');
			$mail->Subject = 'Новое объявление: '.$this->address;
		} catch (phpmailerException $e) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Property->sendEmailAboutNewProperty():1 Ошибка при формировании e-mail:".$e->errorMessage()."Текст сообщения:".$MsgHTML); //Pretty error messages from PHPMailer
			return FALSE;
		} catch (Exception $e) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Property->sendEmailAboutNewProperty():2 Ошибка при формировании e-mail:".$e->getMessage()."Текст сообщения:".$MsgHTML); //Boring error messages from anything else!
			return FALSE;
		}

		// Отправляем электронное письмо каждому пользователю индивидуально
		foreach ($listOfTargetUsersForEmail as $tenant) {
			// Подставим имя клиента - получателя email
			$MsgHTML = str_replace("{name}", $tenant['name'], $MsgHTML);
			try {
				$mail->MsgHTML($MsgHTML);
				$mail->AddAddress($tenant['email'], $tenant['name']);
				$mail->Send();
				//$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
			} catch (phpmailerException $e) {
				Logger::getLogger(GlobFunc::$loggerName)->log("Property->sendEmailAboutNewProperty():3 Ошибка при формировании e-mail:".$e->errorMessage()."Текст сообщения:".$MsgHTML); //Pretty error messages from PHPMailer
			} catch (Exception $e) {
				Logger::getLogger(GlobFunc::$loggerName)->log("Property->sendEmailAboutNewProperty():4 Ошибка при формировании e-mail:".$e->getMessage()."Текст сообщения:".$MsgHTML); //Boring error messages from anything else!
			}
		}

		return TRUE;
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

		// Если переданы пустые значения, то нужно сбросить дату/время ближайшего просмотра (это нормальный рабочий вариант входящих значений)
		// Если переданы не пустые значения - хорошень их провалидируем
		if ($earliestDate != "" || $earliestTimeHours != "" || $earliestTimeMinutes != "") {

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
		}

		// Изменим параметры даты и времени ближайшего просмотра
		$this->earliestDate = $earliestDate;
		$this->earliestTimeHours = $earliestTimeHours;
		$this->earliestTimeMinutes = $earliestTimeMinutes;

		return TRUE;
	}

	/**
	 * Снимает с публикации объявление (для чужих объявлений - переносит в архивную базу)
	 * 	1. Изменить статус объекта на "не опубликовано" в БД
	 * 	2. Очистить дату и время ближайшего показа в БД
	 * 	3. Очистить дату въезда и выезда у объекта
	 * 	4. Удалить все уведомления (типа Новый подходящий объект)
	 * 	Важно, что, если по данному удаленному объекту есть заявки на просмотр со статусом (Новая, Назначен просмотр, Отложена), то оператор увидит информацию о таких удаленных объявлениях и их заявках в админке (по ссылке "Недозакрытые объявления") - оператору необходимо вручную изменить статус у таких заявок, предварительно созвонившись с арендаторами и предупредив их, что объект уже не сдается
	 *	Если по объекту недвижимости назначен просмотр, то его невозможно снять с публикации - сначала нужно скинуть дату просмотра
	 *
	 * @return array - массив строк, каждая из которых представляет сведения по 1 ошибке, не позволившей снять объявление с публикации. В случае успеха - возвращаемый массив пуст
	 */
	public function unpublishAdvert() {

		// Валидация начальных данных
		if ($this->id == "" || $this->status == "" || $this->completeness == "") return array("Не удалось снять с публикации объявление: недостаточно данных. Попробуйте повторить немного позже или свяжитесь с нами: 8-922-160-95-14");

		// Если по объекту назначен ближайший просмотр, то его нельзя снять с публикации. Если просмотр прошел, то объявление должно быть прежде обработано оператором, а затем снято с публикации, если же просмотр еще не прошел, то прежде чем снимать объявление с публикации необходимо предупредить об отмене всех участников просмотра
		if ($this->earliestDate != "") return array("Не удалось снять с публикации объявление: по нему назначен просмотр. Вы можете отменить просмотр, связавшись с нами по телефону 8-922-160-95-14");

		// Меняем параметры объекта
		$this->status = "не опубликовано";
		$this->earliestDate = "";
		$this->earliestTimeHours = "";
		$this->earliestTimeMinutes = "";
		$this->dateOfEntry = "";
		$this->dateOfCheckOut = "";

		// Сохраняем изменения в БД
		if (!$this->saveCharacteristicToDB("edit")) return array("Не удалось снять с публикации объявление: ошибка доступа к базе. Попробуйте повторить немного позже или свяжитесь с нами по телефону 8-922-160-95-14");

		// Удалим уведомления типа "Новый подходящий объект", касающиеся этого объекта
		DBconnect::deleteMessagesNewPropertyForProperty($this->id);

		// Если это объявление из чужой базы, то его необходимо перенести в архивную таблицу
		if ($this->completeness == "0") {
			if (!DBconnect::insertPropertyCharacteristicToArchive($this->getCharacteristicData()) || !DBconnect::deletePropertyCharacteristicForId($this->id)) {
				return array("Не удалось снять с публикации объявление: ошибка доступа к базе. Попробуйте повторить немного позже или свяжитесь с нами по телефону 8-922-160-95-14");
			}
		}

		return array();
	}

	/**
	 * Делает объявление опубликованным
	 *  1. Проверить сведения на достаточность для публикации
	 * 	2. Изменить статус объекта на "опубликовано" в БД
	 *  3. Оповестить арендаторов о появлении подходящего объекта
	 *  Размер комиссии оставить тем-же?? Пока не меняет размер комиссии
	 *  Важно, что при публикации объявления пересчитывается его реальная стоимость аренды
	 *
	 * @return array - массив строк, каждая из которых представляет сведения по 1 ошибке, не позволившей выполнить публикацию. В случае успеха - возвращаемый массив пуст
	 */
	public function publishAdvert() {

		// Валидация начальных данных
		if ($this->id == "" || $this->status == "" || $this->completeness == "") return array("Не удалось опубликовать объявление: недостаточно данных. Попробуйте повторить немного позже или обратитесь к нам: 8-922-160-95-14");

		// Проверяем корректность данных объявления. Функции validate() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
		// Если мы имеем дело с редактированием чужого объявления администратором, то проверки данных происходят по упрощенному способу
		if ($this->completeness == "0") {
			$errors = $this->validate("editAlienAdvert");
		} else {
			$errors = $this->validate("editAdvert");
		}
		if (count($errors) != 0) return $errors;

		// Меняем параметры объекта
		$this->status = "опубликовано";

		// Сохраняем изменения в БД. При этом пересчитывается реальная стоимость аренды (если она была указана валюте и даже в рублях)
		if (!$this->saveCharacteristicToDB("edit")) return array("Не удалось опубликовать объявление: ошибка при записи данных в базу. Попробуйте повторить немного позже или обратитесь к нам: 8-922-160-95-14");

		// Оповестим арендаторов о появлении нового объекта недвижимости
		$this->notifyUsersAboutNewProperty();

		return array();
	}
}
