<?php
/**
 * Класс представляем собой полную модель пользователя
 *
 * Схема работы с объектами класса:
 * 1. Инициализация (в качестве параметра конструктору можно передать id пользователя). При этом параметры объекта устанавливаются по умолчанию (пустые значения)
 * 2. Записать в параметры объекта нужные данные. С помощью методов write записать в параметры объекта данные из БД или POST
 * 3. Выполнить манипуляции с объектом и его параметрами
 * 4. Записать изменившиеся значения параметров объекта в БД
 */

class User
{
	public $typeTenant = NULL;
	public $typeOwner = NULL;
	public $name = "";
	public $secondName = "";
	public $surname = "";
	public $sex = "0";
	public $nationality = "0";
	public $birthday = "";
	public $login = "";
	public $password = "";
	public $telephon = "";
	public $email = "";
	public $currentStatusEducation = "0";
	public $almamater = "";
	public $speciality = "";
	public $kurs = "";
	public $ochnoZaochno = "0";
	public $yearOfEnd = "";
	public $statusWork = "0";
	public $placeOfWork = "";
	public $workPosition = "";
	public $regionOfBorn = "";
	public $cityOfBorn = "";
	public $shortlyAboutMe = "";
	public $vkontakte = "";
	public $odnoklassniki = "";
	public $facebook = "";
	public $twitter = "";
	public $lic = "";
	private $id = "";
	private $typeAdmin = NULL;
	private $emailReg = "";
	private $user_hash = "";
	private $last_act = "";
	private $reg_date = "";
	public $favoritesPropertysId = array();

	public $typeOfObject = "0";
	public $amountOfRooms = array();
	public $adjacentRooms = "0";
	public $floor = "0";
	public $minCost = "";
	public $maxCost = "";
	public $pledge = "";
	public $prepayment = "0";
	public $district = array();
	public $withWho = "0";
	public $linksToFriends = "";
	public $children = "0";
	public $howManyChildren = "";
	public $animals = "0";
	public $howManyAnimals = "";
	public $termOfLease = "0";
	public $additionalDescriptionOfSearch = "";
	public $regDate = "";

	public $fileUploadId = "";
	public $uploadedFoto = array(); // В переменной будет храниться информация о загруженных фотографиях. Представляет собой массив ассоциированных массивов
	public $primaryFotoId = "";

	/**
	 * КОНСТРУКТОР
	 *
	 * Конструктор всегда инициализирует параметры объекта пустыми значениями.
	 * Если объект создается под существующего пользователя, то нужно сразу указать id этого пользователя (в параметрах конструктора)
	 * Инициализация объекта параметрами существующего пользователя выделена в отдельные методы (writeCharacteristicFrom.., writeFotoInformationFrom.., writeSearchRequestFrom..), что позволяет убедиться в их успешном выполнении (получении данных из БД или из POST), а также выполнить инициализацию только тех параметров, которые понадобятся в работе с этим объектом (характеристика и/или данные о фотографиях и/или данные о поисковом запросе). Ну и кроме того, это позволяет инициализировать объект параметрами как из БД, так и из POST запроса по выбору.
	 * @param bool $userId - идентификатор существующего (записанного ранее в БД) пользователя - используется при инициализации объекта под заранее известного, ранее заведенного в БД пользователя
	 */
	public function __construct($userId = FALSE) {
		// Инициализируем переменную "сессии" для временного сохранения фотографий
		$this->fileUploadId = GlobFunc::generateCode(7);

		// Если мы собираемся инициализировать данную модель в соответствии с текущим пользователем, запросившим страницу, то запишем его ключевые параметры
		if ($userId != FALSE) {
			$this->id = $userId;
		}

	}

	// ДЕСТРУКТОР
	public function __destruct() {}

	// Метод возвращает id пользователя
	public function getId() {
		return $this->id;
	}

	// Функция сохраняет личные параметры пользователя (текущие значения параметров данного объекта) в БД. Все параметры, кроме поискового запроса (у него отдельная функция)
	// $typeOfUser = "new" - режим сохранения для нового (регистрируемого пользователя)
	// $typeOfUser = "edit" - режим сохранения для редактируемых параметров (для существующего пользователя)
	// Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
	public function saveCharacteristicToDB($typeOfUser = "edit") {
		// Валидация необходимых исходных данных
		if ($typeOfUser != "new" && $typeOfUser != "edit") return FALSE;
		if ($typeOfUser == "edit" && $this->id == "") return FALSE; // Если запись данные в БД требуется не для нового пользователя (не на странице регистрации) и мы не знаем id пользователя, то функция не выполняется
		if ($this->login == "" || $this->password == "") return FALSE; // Логин и пароль - это самые обязательные из всех реквизитов пользователя, без них сохраненные данные о пользователе потеряются в БД

		// Корректируем дату дня рождения для того, чтобы сделать ее пригодной для сохранения в базу данных
		$birthdayDB = GlobFunc::dateFromViewToDB($this->birthday);
		// Получаем текущее время для сохранения в качестве даты регистрации и даты последнего действия
		$tm = time();
		$last_act = $tm;
		$reg_date = $tm; // Сохранится в БД только для нового пользователя $typeOfUser = "new"
		// Сериализуем массив с избранными объявлениями
		$favoritesPropertysId = serialize($this->favoritesPropertysId);
		// Преобразуем из логических в строковые (MySQL почему-то не поддерживает сохранение логических параметров)
		if ($this->typeTenant === TRUE) $typeTenant = "TRUE";
		if ($this->typeTenant === FALSE) $typeTenant = "FALSE";
		if ($this->typeTenant === NULL) $typeTenant = "FALSE";
		if ($this->typeOwner === TRUE) $typeOwner = "TRUE";
		if ($this->typeOwner === FALSE) $typeOwner = "FALSE";
		if ($this->typeOwner === NULL) $typeOwner = "FALSE";

		// Для простоты технической поддержки пользователей пойдем на небольшой риск с точки зрения безопасности и будем хранить пароли пользователей на сервере в БД без соли и шифрования
		/*$salt = mt_rand(100, 999);
		$password = md5(md5($password) . $salt);*/

		// Пишем данные пользователя в БД. При успехе в $res сохраняем TRUE, иначе - FALSE
		// Код для сохранения данных разный: для нового пользователя и при редактировании параметров существующего пользователя
		if ($typeOfUser == "new") {

			// Для нового пользователя всегда тип собственника сбрасываем в FALSE (пока под этим пользователем не появится хотя бы 1 объявление)
			$typeOwner = "FALSE";

			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("INSERT INTO users (typeTenant,typeOwner,name,secondName,surname,sex,nationality,birthday,login,password,telephon,emailReg,email,currentStatusEducation,almamater,speciality,kurs,ochnoZaochno,yearOfEnd,statusWork,placeOfWork,workPosition,regionOfBorn,cityOfBorn,shortlyAboutMe,vkontakte,odnoklassniki,facebook,twitter,lic,last_act,reg_date,favoritesPropertysId) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
				OR ($stmt->bind_param("ssssssssssssssssssssssssssssssiis", $typeTenant, $typeOwner, $this->name, $this->secondName, $this->surname, $this->sex, $this->nationality, $birthdayDB, $this->login, $this->password, $this->telephon, $this->email, $this->email, $this->currentStatusEducation, $this->almamater, $this->speciality, $this->kurs, $this->ochnoZaochno, $this->yearOfEnd, $this->statusWork, $this->placeOfWork, $this->workPosition, $this->regionOfBorn, $this->cityOfBorn, $this->shortlyAboutMe, $this->vkontakte, $this->odnoklassniki, $this->facebook, $this->twitter, $this->lic, $last_act, $reg_date, $favoritesPropertysId) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($res === 0)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)

				return FALSE;
			}

			return TRUE;
		}

		if ($typeOfUser == "edit") {

			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("UPDATE users SET name=?, secondName=?, surname=?, sex=?, nationality=?, birthday=?, password=?, telephon=?, email=?, currentStatusEducation=?, almamater=?, speciality=?, kurs=?, ochnoZaochno=?, yearOfEnd=?, statusWork=?, placeOfWork=?, workPosition=?, regionOfBorn=?, cityOfBorn=?, shortlyAboutMe=?, vkontakte=?, odnoklassniki=?, facebook=?, twitter=?, last_act=? WHERE id=?") === FALSE)
				OR ($stmt->bind_param("sssssssssssssssssssssssssis", $this->name, $this->secondName, $this->surname, $this->sex, $this->nationality, $birthdayDB, $this->password, $this->telephon, $this->email, $this->currentStatusEducation, $this->almamater, $this->speciality, $this->kurs, $this->ochnoZaochno, $this->yearOfEnd, $this->statusWork, $this->placeOfWork, $this->workPosition, $this->regionOfBorn, $this->cityOfBorn, $this->shortlyAboutMe, $this->vkontakte, $this->odnoklassniki, $this->facebook, $this->twitter, $last_act, $this->id) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				return FALSE;
			}

			return TRUE;
		}

		// Теоретичеки до этой строчки функция не должна доходить. Если это произошло, значит что-то не так
		return FALSE;
	}

	// Функция сохраняет актуальные данные о фотографиях пользователя в БД. Если какие-то из ранее загруженных фотографий были удалены пользователем (помечены в браузере на удаление), то функция удаляет их с сервера и из БД
	// TODO: функция пока ничего не возвращает, а может нужно добавить TRUE или FALSE?
	public function saveFotoInformationToDB() {

		// ВАЖНО:
		// Функция считает, что если пользователь имеет id, то он уже был зарегистрирован и требуется отредактировать его фотографии
		// Если же пользователь не имеет id, то функция считает его Новым пользователем (а значит у него нет сохраненных фоток в userFotos)
		//
		// Схема работы функции:
		// 1. Проверить наличие массива данных о фотографиях ($this->uploadedFoto), а также id пользователя
		// 2. Собираем инфу по всем фотографиям пользователя из БД tempFotos (по $this->fileUploadId) и userFotos (по id пользователя)
		// 3. Добавляем в полученные из БД данные актуалную инфу по статусом (основная/неосновная) и помечаем те фотки, которые нужно удалить
		// 4. Перебираем массив и удаляем ненужные фотки с жесткого диска
		// 5. Редактируем данные по нужным фоткам (UPDATE для userFotos)
		// 6. Добавляем данные по нужным фоткам (INSERT для userFotos)
		// 7. Удаляем ненужные фотки (DELETE для userFotos и для tempFotos)

		// На всякий случай, проверим на массив
		if (!is_array($this->uploadedFoto)) return FALSE;

		// Для выполнения функция у пользователя обязательно должен быть id
		if ($this->id == "") return FALSE;

		// Получаем данные по всем фоткам с нашим $this->fileUploadId
		$allFotos = DBconnect::selectPhotosForFileUploadId($this->fileUploadId);
		// Пометим все члены массива признаком их получения из таблицы tempFotos
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {
			$allFotos[$i]['fromTable'] = "tempFotos";
		}

		// Получаем данные по всем фоткам пользователя (с идентификатором $this->id)
		// Но только для существующего - авторизованного пользователя (не для нового)
		if ($this->id != "") {
			$res = DBconnect::selectPhotosForUser($this->id);
			// Пометим все члены массива признаком их получения из таблицы userFotos
			for ($i = 0, $s = count($res); $i < $s; $i++) {
				$res[$i]['fromTable'] = "userFotos";
			}
			$allFotos = array_merge($allFotos, $res);
		}

		// Перебираем все имеющиеся фотографии пользователя и актуализируем их параметры
		$primaryFotoExists = 0; // Инициализируем переменную, по которой после прохода по всем фотографиям, полученным в форме, сможем сказать была ли указана пользователем основная фотка (число - сколько фоток со статусом основная мы получили с клиента) или нет (0)
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {

			// Для сокращения количества запросов на UPDATE будем отмечать особым признаком те фотографии, по которым требуется выполнения этого запроса к БД
			$allFotos[$i]['updated'] = FALSE;

			// На заметку: в массиве $uploadedFoto также содержится (а точнее может содержаться) актуальная информация по всем статусам фотографий, но легче получить id основной фотки из формы, а не из этого массива
			if ($allFotos[$i]['id'] == $this->primaryFotoId) {
				// Проверяем - нужно ли для данной фотографии проводить UPDATE
				if ($allFotos[$i]['fromTable'] == "userFotos" && $allFotos[$i]['status'] != 'основная') {
					$allFotos[$i]['updated'] = TRUE;
				}
				$allFotos[$i]['status'] = 'основная';
				// Признак наличия основной фотографии
				$primaryFotoExists++;
			} else {
				if ($allFotos[$i]['fromTable'] == "userFotos" && $allFotos[$i]['status'] != '') {
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
				if ($allFotos[$i]['fromTable'] == "userFotos" && $allFotos[$i]['status'] != 'основная') {
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
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка удаления файлов фотографий пользователя. Адрес: " . $allFotos[$i]['folder'] . "/big/" . $allFotos[$i]['id'] . "." . $allFotos[$i]['extension'] . " Местонахождение кода: User->saveFotoInformationToDB(). ID пользователя: " . $this->id);
			}
		}

		// Выполним запросы на UPDATE данных в userFotos
		$stmt = DBconnect::get()->stmt_init();
		if ($stmt->prepare("UPDATE userFotos SET status=? WHERE id=?") === FALSE) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
		}
		for ($i = 0, $s = count($allFotos); $i < $s; $i++) {
			if ($allFotos[$i]['fromTable'] == "userFotos" && $allFotos[$i]['updated'] == TRUE && $allFotos[$i]['forRemove'] == FALSE) {
				if (($stmt->bind_param("ss", $allFotos[$i]['status'], $allFotos[$i]['id']) === FALSE)
					OR ($stmt->execute() === FALSE)
					OR (($res = $stmt->affected_rows) === -1)
				) {
					// Логируем ошибку
					Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE userFotos SET status=" . $allFotos[$i]['status'] . " WHERE id=" . $allFotos[$i]['id'] . "'. Местонахождение кода: User->saveFotoInformationToDB(). Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: " . $this->id);
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
			DBconnect::get()->query("INSERT INTO userFotos (id, folder, filename, extension, filesizeMb, userId, status, regDate) VALUES " . $strINSERT);
			if ((DBconnect::get()->errno)
				OR (($res = DBconnect::get()->affected_rows) === -1)
				OR ($res === 0)
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO userFotos (id, folder, filename, extension, filesizeMb, userId, status, regDate) VALUES " . $strINSERT . "'. Местонахождение кода: User->saveFotoInformationToDB(). Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: " . $this->id);
			}
		}
		// DELETE
		if ($strDELETE != "") {
			DBconnect::get()->query("DELETE FROM userFotos WHERE " . $strDELETE);
			if ((DBconnect::get()->errno)
				OR (($res = DBconnect::get()->affected_rows) === -1)
			) {
				// Логируем ошибку
				Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM userFotos WHERE " . $strDELETE . "'. Местонахождение кода: User->saveFotoInformationToDB(). Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: " . $this->id);
			}
		}

		// Удаляем инфу о всех фотках с fileUploadId из tempFotos
		DBconnect::deletePhotosForFileUploadId($this->fileUploadId);

		// Приведем в соответствие с данными из БД наш массив с фотографиями $this->uploadedFotos
		if (!$this->writeFotoInformationFromDB()) return FALSE;

		return TRUE;
	}

	// Функция для сохранения параметров поискового запроса пользователя
	// $typeOfUser = "new" - режим сохранения для нового (регистрируемого пользователя)
	// $typeOfUser = "edit" - режим сохранения для существующего пользователя
	// Режимы нужны из-за того, что typeTenant в случае нового пользователя означает намерение стать арендатором (но у него еще нет в БД поискового запроса), а в случае существующего пользователя typeTenant означает наличие в БД поискового запроса (если = TRUE), либо его отсутствие (если = FALSE)
	// Возвращает TRUE, если данные успешно сохранены и FALSE в противном случае
	public function saveSearchRequestToDB($typeOfUser = "edit") {

		if ($this->id == "") return FALSE;

		// Преобразование формата инфы об искомом кол-ве комнат и районах, так как MySQL не умеет хранить массивы
		$amountOfRoomsSerialized = serialize($this->amountOfRooms);
		$districtSerialized = serialize($this->district);
		$regDate = time();

		if ($this->typeTenant === TRUE && $typeOfUser == "edit") {

			// Непосредственное сохранение данных о поисковом запросе
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("UPDATE searchRequests SET userId=?, typeOfObject=?, amountOfRooms=?, adjacentRooms=?, floor=?, minCost=?, maxCost=?, pledge=?, prepayment=?, district=?, withWho=?, linksToFriends=?, children=?, howManyChildren=?, animals=?, howManyAnimals=?, termOfLease=?, additionalDescriptionOfSearch=?, regDate=? WHERE userId=?") === FALSE)
				OR ($stmt->bind_param("sssssiiissssssssssis", $this->id, $this->typeOfObject, $amountOfRoomsSerialized, $this->adjacentRooms, $this->floor, $this->minCost, $this->maxCost, $this->pledge, $this->prepayment, $districtSerialized, $this->withWho, $this->linksToFriends, $this->children, $this->howManyChildren, $this->animals, $this->howManyAnimals, $this->termOfLease, $this->additionalDescriptionOfSearch, $this->regDate, $this->id) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				return FALSE;
			}

		} else {

			// Непосредственное сохранение данных о поисковом запросе
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("INSERT INTO searchRequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, minCost, maxCost, pledge, prepayment, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, termOfLease, additionalDescriptionOfSearch, regDate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
				OR ($stmt->bind_param("sssssiiissssssssssi", $this->id, $this->typeOfObject, $amountOfRoomsSerialized, $this->adjacentRooms, $this->floor, $this->minCost, $this->maxCost, $this->pledge, $this->prepayment, $districtSerialized, $this->withWho, $this->linksToFriends, $this->children, $this->howManyChildren, $this->animals, $this->howManyAnimals, $this->termOfLease, $this->additionalDescriptionOfSearch, $regDate) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($res = $stmt->affected_rows) === -1)
				OR ($res === 0)
				OR ($stmt->close() === FALSE)
			) {
				// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
				return FALSE;
			}

			// Обновляем статус пользователя - теперь он арендатор
			if (!DBconnect::updateUserCharacteristicTypeUser($this->id, "typeTenant", "TRUE")) return FALSE;

			$this->typeTenant = TRUE;
			$this->regDate = $regDate;
		}

		return TRUE;
	}

	// Метод читает личные данные пользователя из БД и записывает их в параметры данного объекта
	public function writeCharacteristicFromDB() {

		// Если идентификатор пользователя неизвестен, то дальнейшие действия не имеют смысла
		if ($this->id == "") return FALSE;

		// Получим из БД данные ($res) по пользователю с идентификатором = $this->id
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM users WHERE id=?") === FALSE)
			OR ($stmt->bind_param("s", $this->id) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			return FALSE;
		}

		// Если получено меньше или больше одной строки (одного пользователя) из БД, то сообщаем об ошибке
		if (!is_array($res) || count($res) != 1) {
			// TODO: Сохранить в лог ошибку получения данных пользователя из БД
			return FALSE;
		}

		// Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
		$oneUserDataArr = $res[0];

		// Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию.
		if (isset($oneUserDataArr['id'])) $this->id = $oneUserDataArr['id'];

		if (isset($oneUserDataArr['typeTenant'])) {
			if ($oneUserDataArr['typeTenant'] == "TRUE") $this->typeTenant = TRUE;
			if ($oneUserDataArr['typeTenant'] == "FALSE") $this->typeTenant = FALSE;
		}
		if (isset($oneUserDataArr['typeOwner'])) {
			if ($oneUserDataArr['typeOwner'] == "TRUE") $this->typeOwner = TRUE;
			if ($oneUserDataArr['typeOwner'] == "FALSE") $this->typeOwner = FALSE;
		}
		if (isset($oneUserDataArr['typeAdmin'])) $this->typeAdmin = $oneUserDataArr['typeAdmin'];
		if (isset($oneUserDataArr['name'])) $this->name = $oneUserDataArr['name'];
		if (isset($oneUserDataArr['secondName'])) $this->secondName = $oneUserDataArr['secondName'];
		if (isset($oneUserDataArr['surname'])) $this->surname = $oneUserDataArr['surname'];
		if (isset($oneUserDataArr['sex'])) $this->sex = $oneUserDataArr['sex'];
		if (isset($oneUserDataArr['nationality'])) $this->nationality = $oneUserDataArr['nationality'];
		if (isset($oneUserDataArr['birthday'])) $this->birthday = GlobFunc::dateFromDBToView($oneUserDataArr['birthday']);
		if (isset($oneUserDataArr['login'])) $this->login = $oneUserDataArr['login'];
		if (isset($oneUserDataArr['password'])) $this->password = $oneUserDataArr['password'];
		if (isset($oneUserDataArr['telephon'])) $this->telephon = $oneUserDataArr['telephon'];
		if (isset($oneUserDataArr['emailReg'])) $this->emailReg = $oneUserDataArr['emailReg'];
		if (isset($oneUserDataArr['email'])) $this->email = $oneUserDataArr['email'];

		if (isset($oneUserDataArr['currentStatusEducation'])) $this->currentStatusEducation = $oneUserDataArr['currentStatusEducation'];
		if (isset($oneUserDataArr['almamater'])) $this->almamater = $oneUserDataArr['almamater'];
		if (isset($oneUserDataArr['speciality'])) $this->speciality = $oneUserDataArr['speciality'];
		if (isset($oneUserDataArr['kurs'])) $this->kurs = $oneUserDataArr['kurs'];
		if (isset($oneUserDataArr['ochnoZaochno'])) $this->ochnoZaochno = $oneUserDataArr['ochnoZaochno'];
		if (isset($oneUserDataArr['yearOfEnd'])) $this->yearOfEnd = $oneUserDataArr['yearOfEnd'];
		if (isset($oneUserDataArr['statusWork'])) $this->statusWork = $oneUserDataArr['statusWork'];
		if (isset($oneUserDataArr['placeOfWork'])) $this->placeOfWork = $oneUserDataArr['placeOfWork'];
		if (isset($oneUserDataArr['workPosition'])) $this->workPosition = $oneUserDataArr['workPosition'];
		if (isset($oneUserDataArr['regionOfBorn'])) $this->regionOfBorn = $oneUserDataArr['regionOfBorn'];
		if (isset($oneUserDataArr['cityOfBorn'])) $this->cityOfBorn = $oneUserDataArr['cityOfBorn'];
		if (isset($oneUserDataArr['shortlyAboutMe'])) $this->shortlyAboutMe = $oneUserDataArr['shortlyAboutMe'];
		if (isset($oneUserDataArr['vkontakte'])) $this->vkontakte = $oneUserDataArr['vkontakte'];
		if (isset($oneUserDataArr['odnoklassniki'])) $this->odnoklassniki = $oneUserDataArr['odnoklassniki'];
		if (isset($oneUserDataArr['facebook'])) $this->facebook = $oneUserDataArr['facebook'];
		if (isset($oneUserDataArr['twitter'])) $this->twitter = $oneUserDataArr['twitter'];

		if (isset($oneUserDataArr['lic'])) $this->lic = $oneUserDataArr['lic'];
		if (isset($oneUserDataArr['user_hash'])) $this->user_hash = $oneUserDataArr['user_hash'];
		if (isset($oneUserDataArr['last_act'])) $this->last_act = $oneUserDataArr['last_act'];
		if (isset($oneUserDataArr['reg_date'])) $this->reg_date = $oneUserDataArr['reg_date'];
		if (isset($oneUserDataArr['favoritesPropertysId'])) $this->favoritesPropertysId = unserialize($oneUserDataArr['favoritesPropertysId']);

		return TRUE;
	}

	// Метод читает данные о фотографиях из БД и записывает их в параметры пользователя
	public function writeFotoInformationFromDB() {

		// Если идентификатор пользователя неизвестен, то дальнейшие действия не имеют смысла
		if ($this->id == "") return FALSE;

		// Получим из БД данные ($res) по пользователю с идентификатором = $this->id
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM userFotos WHERE userId=?") === FALSE)
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

	// Метод читает данные о поисковом запросе из БД и записывает их в параметры пользователя
	public function writeSearchRequestFromDB() {

		// Если идентификатор пользователя неизвестен или пользователь не является арендатором, то дальнейшие действия не имеют смысла
		if ($this->id == "" || $this->typeTenant === FALSE) return FALSE;

		// Получим из БД данные ($res) по пользователю с идентификатором = $this->id
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM searchRequests WHERE userId=?") === FALSE)
			OR ($stmt->bind_param("s", $this->id) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			return FALSE;
		}

		// Если получено меньше или больше одной строки (а на одного пользователя строго приходится 1 поисковый запрос) из БД, то либо произошла шибка, либо (что значительно более вероятно) у данного пользователя нет поискового запроса
		if (!is_array($res) || count($res) != 1) {
			if ($this->typeTenant === TRUE && count($res) == 0) {
				// TODO: Сохранить в лог ошибку - пользователь является арендатором, но не имеет поискового запроса!
			}
			if (count($res) > 1) {
				// TODO: Сохранить в лог ошибку - у пользователя заведено более 1 поискового запроса!
			}

			// В любом случае, при таком раскладе записать данные поискового запроса в параметры пользователя не представляется возможным
			return FALSE;
		}

		if ($this->typeTenant !== TRUE && count($res) != 0) {
			// TODO: Сохранить в лог ошибку - пользователь НЕ является арендатором, но имеет поисковый запрос!
		}

		// Для красоты (чтобы избавить от индекса ноль при обращении к переменным) переприсвоим значение $res[0] специальной переменной
		$oneUserDataArr = $res[0];

		// Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - у них останутся значения по умолчанию
		if (isset($oneUserDataArr['typeOfObject'])) $this->typeOfObject = $oneUserDataArr['typeOfObject'];
		if (isset($oneUserDataArr['amountOfRooms'])) $this->amountOfRooms = unserialize($oneUserDataArr['amountOfRooms']);
		if (isset($oneUserDataArr['adjacentRooms'])) $this->adjacentRooms = $oneUserDataArr['adjacentRooms'];
		if (isset($oneUserDataArr['floor'])) $this->floor = $oneUserDataArr['floor'];
		if (isset($oneUserDataArr['minCost'])) $this->minCost = $oneUserDataArr['minCost'];
		if (isset($oneUserDataArr['maxCost'])) $this->maxCost = $oneUserDataArr['maxCost'];
		if (isset($oneUserDataArr['pledge'])) $this->pledge = $oneUserDataArr['pledge'];
		if (isset($oneUserDataArr['prepayment'])) $this->prepayment = $oneUserDataArr['prepayment'];
		if (isset($oneUserDataArr['district'])) $this->district = unserialize($oneUserDataArr['district']);
		if (isset($oneUserDataArr['withWho'])) $this->withWho = $oneUserDataArr['withWho'];
		if (isset($oneUserDataArr['linksToFriends'])) $this->linksToFriends = $oneUserDataArr['linksToFriends'];
		if (isset($oneUserDataArr['children'])) $this->children = $oneUserDataArr['children'];
		if (isset($oneUserDataArr['howManyChildren'])) $this->howManyChildren = $oneUserDataArr['howManyChildren'];
		if (isset($oneUserDataArr['animals'])) $this->animals = $oneUserDataArr['animals'];
		if (isset($oneUserDataArr['howManyAnimals'])) $this->howManyAnimals = $oneUserDataArr['howManyAnimals'];
		if (isset($oneUserDataArr['termOfLease'])) $this->termOfLease = $oneUserDataArr['termOfLease'];
		if (isset($oneUserDataArr['additionalDescriptionOfSearch'])) $this->additionalDescriptionOfSearch = $oneUserDataArr['additionalDescriptionOfSearch'];
		if (isset($oneUserDataArr['regDate'])) $this->regDate = $oneUserDataArr['regDate'];

		return TRUE;

	}

	/**
	 * Метод удаляет параметры поискового запроса пользователя из БД, сбрасывает соответствующие настройки объекта на "по-умолчанию", а также меняет статус isTenant на FALSE.
	 * 	1. Проверяем, что у данного пользователя нет заявок на просмотр со статусом "Назначен просмотр", в противном случае, не позволяем удалять поисковый запрос
	 *  2. Удаляем параметры поискового запроса пользователя из БД
	 * 	3. Сбрасывает статус арендатора на FALSE (значение typeTenant в таблице users)
	 * 	4. Удаляет все уведомления типа "Новый подходящий объект недвижимости"
	 * 	5. Удаляет все заявки на просмотр, кроме имеющих статус "Успешный просмотр", чтобы мы знали кто какую недвижимость снимает через нас
	 * 	6. Изменяем параметры текущего объекта
	 * При этом нетронутым остается список избранных объектов пользователя - он ему пригодиться в следующий раз
	 *
	 * @return bool возвращает TRUE если все удачно и FALSE, если выполнить операцию не удалось
	 */
	public function removeSearchRequest() {

		// Проверка на наличие id пользователя
		if ($this->id == "") return FALSE;

		// Убеждаемся, что у арендатора нет заявок на просмотр со статусом "Назначен просмотр"
		$allRequestsToView = DBconnect::selectRequestsToViewForTenants($this->id);
		foreach ($allRequestsToView as $value) {
			if ($value['status'] == "Назначен просмотр") return FALSE;
		}

		// Удалим данные поискового запроса по данному пользователю из БД
		if (!DBconnect::deleteSearchRequestsForUser($this->id)) return FALSE;

		// Обновляем статус данного пользователю (он больше не арендатор)
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("UPDATE users SET typeTenant='FALSE' WHERE id=?") === FALSE)
			OR ($stmt->bind_param("s", $this->id) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
			return FALSE;
		}

		// Удалим все уведомления типа "Новый подходящий объект недвижимости" у данного арендатора
		DBconnect::deleteMessagesNewPropertyForUser($this->id);

		// Удалим все заявки на просмотр данного пользователя, кроме имеющих статус "Успешный просмотр"
		DBconnect::deleteRequestsToViewForTenant($this->id);

		// Внесем соответствующие изменения в статус объекта пользователя
		$this->typeTenant = "FALSE";

		// Скинем на дефолтные параметры поискового запроса данного пользователя
		$this->typeOfObject = "0";
		$this->amountOfRooms = array();
		$this->adjacentRooms = "0";
		$this->floor = "0";
		$this->minCost = "";
		$this->maxCost = "";
		$this->pledge = "";
		$this->prepayment = "0";
		$this->district = array();
		$this->withWho = "0";
		$this->linksToFriends = "";
		$this->children = "0";
		$this->howManyChildren = "";
		$this->animals = "0";
		$this->howManyAnimals = "";
		$this->termOfLease = "0";
		$this->additionalDescriptionOfSearch = "";
		$this->regDate = "";

		return TRUE;
	}

	// Записать в качестве параметров user-а значения, полученные через POST запрос
	public function writeCharacteristicFromPOST() {
		//TODO: не проверять и не менять $_POST['login'], если происходит редактирование существующего пользователя (теоретически можно через POST параметр заслать новый логин и метод его поменяет для ранее зарегистрированного пользователя)

		if (isset($_POST['name'])) $this->name = htmlspecialchars($_POST['name'], ENT_QUOTES);
		if (isset($_POST['secondName'])) $this->secondName = htmlspecialchars($_POST['secondName'], ENT_QUOTES);
		if (isset($_POST['surname'])) $this->surname = htmlspecialchars($_POST['surname'], ENT_QUOTES);
		if (isset($_POST['sex'])) $this->sex = htmlspecialchars($_POST['sex'], ENT_QUOTES);
		if (isset($_POST['nationality'])) $this->nationality = htmlspecialchars($_POST['nationality'], ENT_QUOTES);
		if (isset($_POST['birthday'])) $this->birthday = htmlspecialchars($_POST['birthday'], ENT_QUOTES);
		if (isset($_POST['login'])) $this->login = htmlspecialchars($_POST['login'], ENT_QUOTES);
		if (isset($_POST['password'])) $this->password = htmlspecialchars($_POST['password'], ENT_QUOTES);
		if (isset($_POST['telephon'])) $this->telephon = htmlspecialchars($_POST['telephon'], ENT_QUOTES);
		if (isset($_POST['email'])) $this->email = htmlspecialchars($_POST['email'], ENT_QUOTES);

		if (isset($_POST['currentStatusEducation'])) $this->currentStatusEducation = htmlspecialchars($_POST['currentStatusEducation'], ENT_QUOTES);
		if (isset($_POST['almamater'])) $this->almamater = htmlspecialchars($_POST['almamater'], ENT_QUOTES);
		if (isset($_POST['speciality'])) $this->speciality = htmlspecialchars($_POST['speciality'], ENT_QUOTES);
		if (isset($_POST['kurs'])) $this->kurs = htmlspecialchars($_POST['kurs'], ENT_QUOTES);
		if (isset($_POST['ochnoZaochno'])) $this->ochnoZaochno = htmlspecialchars($_POST['ochnoZaochno'], ENT_QUOTES);
		if (isset($_POST['yearOfEnd'])) $this->yearOfEnd = htmlspecialchars($_POST['yearOfEnd'], ENT_QUOTES);
		if (isset($_POST['statusWork'])) $this->statusWork = htmlspecialchars($_POST['statusWork'], ENT_QUOTES);
		if (isset($_POST['placeOfWork'])) $this->placeOfWork = htmlspecialchars($_POST['placeOfWork'], ENT_QUOTES);
		if (isset($_POST['workPosition'])) $this->workPosition = htmlspecialchars($_POST['workPosition'], ENT_QUOTES);
		if (isset($_POST['regionOfBorn'])) $this->regionOfBorn = htmlspecialchars($_POST['regionOfBorn'], ENT_QUOTES);
		if (isset($_POST['cityOfBorn'])) $this->cityOfBorn = htmlspecialchars($_POST['cityOfBorn'], ENT_QUOTES);
		if (isset($_POST['shortlyAboutMe'])) $this->shortlyAboutMe = htmlspecialchars($_POST['shortlyAboutMe'], ENT_QUOTES);

		if (isset($_POST['vkontakte'])) $this->vkontakte = htmlspecialchars($_POST['vkontakte'], ENT_QUOTES);
		if (isset($_POST['odnoklassniki'])) $this->odnoklassniki = htmlspecialchars($_POST['odnoklassniki'], ENT_QUOTES);
		if (isset($_POST['facebook'])) $this->facebook = htmlspecialchars($_POST['facebook'], ENT_QUOTES);
		if (isset($_POST['twitter'])) $this->twitter = htmlspecialchars($_POST['twitter'], ENT_QUOTES);

		if (isset($_POST['lic'])) $this->lic = htmlspecialchars($_POST['lic'], ENT_QUOTES);

	}

	// Записать в качестве данных о фотографиях соответствующую информацию из POST запроса
	public function writeFotoInformationFromPOST() {
		//TODO: убедиться, что если на клиенте удалить все фотки, то при перезагрузке они снова не появятся (из-за того, что $uploadedFoto не придет в POST параметрах и останется предыдущая версия - которая не будет перезатерта)

		if (isset($_POST['fileUploadId'])) $this->fileUploadId = htmlspecialchars($_POST['fileUploadId'], ENT_QUOTES);
		if (isset($_POST['uploadedFoto'])) $this->uploadedFoto = json_decode($_POST['uploadedFoto'], TRUE); // Массив объектов со сведениями о загруженных фотографиях сериализуется в JSON формат на клиенте и передается как содержимое атрибута value одного единственного INPUT hidden
		if (isset($_POST['primaryFotoRadioButton'])) $this->primaryFotoId = htmlspecialchars($_POST['primaryFotoRadioButton'], ENT_QUOTES);

	}

	// Записать в качестве параметров user-а значения, полученные через POST запрос
	public function writeSearchRequestFromPOST() {

		if (isset($_POST['typeOfObject'])) $this->typeOfObject = htmlspecialchars($_POST['typeOfObject'], ENT_QUOTES);
		if (isset($_POST['amountOfRooms']) && is_array($_POST['amountOfRooms'])) {
			$this->amountOfRooms = array();
			foreach ($_POST['amountOfRooms'] as $value) $this->amountOfRooms[] = htmlspecialchars($value, ENT_QUOTES);
		} else $this->amountOfRooms = array(); // Если пользователь отправил форму submit, и в параметрах нет значения amountOfRooms, значит пользователь не отметил ни один чекбокс из группы, чему соответствует пустой массив
		if (isset($_POST['district']) && is_array($_POST['district'])) {
			$this->district = array();
			foreach ($_POST['district'] as $value) $this->district[] = htmlspecialchars($value, ENT_QUOTES);
		} else $this->district = array(); // Если пользователь отправил форму submit, и в параметрах нет значения district, значит пользователь не отметил ни один чекбокс из группы, чему соответствует пустой массив
		if (isset($_POST['adjacentRooms'])) $this->adjacentRooms = htmlspecialchars($_POST['adjacentRooms'], ENT_QUOTES);
		if (isset($_POST['floor'])) $this->floor = htmlspecialchars($_POST['floor'], ENT_QUOTES);
		if (isset($_POST['minCost'])) $this->minCost = htmlspecialchars($_POST['minCost'], ENT_QUOTES);
		if (isset($_POST['maxCost'])) $this->maxCost = htmlspecialchars($_POST['maxCost'], ENT_QUOTES);
		if (isset($_POST['pledge'])) $this->pledge = htmlspecialchars($_POST['pledge'], ENT_QUOTES);
		if (isset($_POST['prepayment'])) $this->prepayment = htmlspecialchars($_POST['prepayment'], ENT_QUOTES);
		if (isset($_POST['withWho'])) $this->withWho = htmlspecialchars($_POST['withWho'], ENT_QUOTES);
		if (isset($_POST['linksToFriends'])) $this->linksToFriends = htmlspecialchars($_POST['linksToFriends'], ENT_QUOTES);
		if (isset($_POST['children'])) $this->children = htmlspecialchars($_POST['children'], ENT_QUOTES);
		if (isset($_POST['howManyChildren'])) $this->howManyChildren = htmlspecialchars($_POST['howManyChildren'], ENT_QUOTES);
		if (isset($_POST['animals'])) $this->animals = htmlspecialchars($_POST['animals'], ENT_QUOTES);
		if (isset($_POST['howManyAnimals'])) $this->howManyAnimals = htmlspecialchars($_POST['howManyAnimals'], ENT_QUOTES);
		if (isset($_POST['termOfLease'])) $this->termOfLease = htmlspecialchars($_POST['termOfLease'], ENT_QUOTES);
		if (isset($_POST['additionalDescriptionOfSearch'])) $this->additionalDescriptionOfSearch = htmlspecialchars($_POST['additionalDescriptionOfSearch'], ENT_QUOTES);
	}

	// Получить ассоциированный массив с данными Анкеты (Характеристики) пользователя (для использования в представлении)
	public function getCharacteristicData() {

		$result = array();

		$result['name'] = $this->name;
		$result['secondName'] = $this->secondName;
		$result['surname'] = $this->surname;
		$result['sex'] = $this->sex;
		$result['nationality'] = $this->nationality;
		$result['birthday'] = $this->birthday;
		$result['login'] = $this->login;
		$result['password'] = $this->password;
		$result['telephon'] = $this->telephon;
		$result['email'] = $this->email;
		$result['currentStatusEducation'] = $this->currentStatusEducation;
		$result['almamater'] = $this->almamater;
		$result['speciality'] = $this->speciality;
		$result['kurs'] = $this->kurs;
		$result['ochnoZaochno'] = $this->ochnoZaochno;
		$result['yearOfEnd'] = $this->yearOfEnd;
		$result['statusWork'] = $this->statusWork;
		$result['placeOfWork'] = $this->placeOfWork;
		$result['workPosition'] = $this->workPosition;
		$result['regionOfBorn'] = $this->regionOfBorn;
		$result['cityOfBorn'] = $this->cityOfBorn;
		$result['shortlyAboutMe'] = $this->shortlyAboutMe;
		$result['vkontakte'] = $this->vkontakte;
		$result['odnoklassniki'] = $this->odnoklassniki;
		$result['facebook'] = $this->facebook;
		$result['twitter'] = $this->twitter;
		$result['lic'] = $this->lic;
		$result['id'] = $this->id;
		$result['typeTenant'] = $this->typeTenant;
		$result['typeOwner'] = $this->typeOwner;
		$result['typeAdmin'] = $this->typeAdmin;
		$result['emailReg'] = $this->emailReg;
		$result['user_hash'] = $this->user_hash;
		$result['last_act'] = $this->last_act;
		$result['reg_date'] = $this->reg_date;
		$result['favoritesPropertysId'] = $this->favoritesPropertysId;
		$result['regDate'] = $this->regDate;

		return $result;
	}

	// Получить ассоциированный массив с данными о фотографиях пользователя (для использования в представлении)
	public function getFotoInformationData() {

		$result = array();

		$result['fileUploadId'] = $this->fileUploadId;
		$result['uploadedFoto'] = $this->uploadedFoto;
		$result['primaryFotoId'] = $this->primaryFotoId;

		return $result;
	}

	// Получить ассоциированный массив с данными о поисковом запросе пользователя (для использования в представлении)
	public function getSearchRequestData() {

		$result = array();

		$result['typeOfObject'] = $this->typeOfObject;
		$result['amountOfRooms'] = $this->amountOfRooms;
		$result['adjacentRooms'] = $this->adjacentRooms;
		$result['floor'] = $this->floor;
		$result['minCost'] = $this->minCost;
		$result['maxCost'] = $this->maxCost;
		$result['pledge'] = $this->pledge;
		$result['prepayment'] = $this->prepayment;
		$result['district'] = $this->district;
		$result['withWho'] = $this->withWho;
		$result['linksToFriends'] = $this->linksToFriends;
		$result['children'] = $this->children;
		$result['howManyChildren'] = $this->howManyChildren;
		$result['animals'] = $this->animals;
		$result['howManyAnimals'] = $this->howManyAnimals;
		$result['termOfLease'] = $this->termOfLease;
		$result['additionalDescriptionOfSearch'] = $this->additionalDescriptionOfSearch;

		return $result;
	}

	// Проверка корректности параметров пользователя
	// $typeOfValidation = registration - режим проверки при поступлении данных на регистрацию пользователя (включает в себя проверки параметров профиля и поискового запроса как для арендатора, так и для собственника)
	// $typeOfValidation = createSearchRequest - режим проверки при потуплении команды на создание поискового запроса (нет проверки данных поисковой формы, проверка параметров профиля как у арендатора)
	// $typeOfValidation = validateSearchRequest - режим проверки указанных пользователем параметров поиска в совокупности с данными Профиля (причем вне зависимости от того, является ли пользователь арендатором, проверка осуществляется как будто бы является, так как он желает стать арендатором, формируя поисковый запрос)
	// $typeOfValidation = validateProfileParameters - режим проверки отредактированных пользователем данных Профиля (учитывается, является ли пользователь арендатором, или собственником)
	// $typeOfValidation = newAlienOwner - режим проверки параметров нового пользователя по минимуму - так как о чужих собственниках обычно мало информации: только проверки на логин и пароль + проверки на формат (чтобы в БД удалось сохранить без ошибок)
	public function userDataCorrect($typeOfValidation) {
		// Подготовим массив для сохранения сообщений об ошибках
		$errors = array();

		// Является ли данный пользователь арендатором или регистрируется в качестве арендатора
		$typeTenant = $this->typeTenant;

		// Проверки для блока "Личные данные"
		if ($typeOfValidation == "registration" || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest" || $typeOfValidation == "validateProfileParameters") {
			if ($this->name == "") $errors[] = 'Укажите имя';
		}
		if (strlen($this->name) > 50) $errors[] = 'Слишком длинное имя. Можно указать не более 50-ти символов';

		if ($typeOfValidation == "registration" || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest" || $typeOfValidation == "validateProfileParameters") {
			if ($this->secondName == "") $errors[] = 'Укажите отчество';
		}
		if (strlen($this->secondName) > 50) $errors[] = 'Слишком длинное отчество. Можно указать не более 50-ти символов';

		if ($typeOfValidation == "registration" || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest" || $typeOfValidation == "validateProfileParameters") {
			if ($this->surname == "") $errors[] = 'Укажите фамилию';
		}
		if (strlen($this->surname) > 50) $errors[] = 'Слишком длинная фамилия. Можно указать не более 50-ти символов';

		if ($typeOfValidation == "registration" || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest" || $typeOfValidation == "validateProfileParameters") {
			if ($this->sex == "0") $errors[] = 'Укажите пол';
		}

		if ($typeOfValidation == "registration" || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest" || $typeOfValidation == "validateProfileParameters") {
			if ($this->nationality == "0") $errors[] = 'Укажите внешность';
		}

		if ($typeOfValidation == "registration" || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest" || $typeOfValidation == "validateProfileParameters") {
			if ($this->birthday == "") $errors[] = 'Укажите дату рождения';
		}
		if ($this->birthday != "") {
			if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $this->birthday)) $errors[] = 'Неправильный формат даты рождения, должен быть: дд.мм.гггг'; else {
				if (substr($this->birthday, 0, 2) < "01" || substr($this->birthday, 0, 2) > "31") $errors[] = 'Проверьте дату Дня рождения (допустимо от 01 до 31)';
				if (substr($this->birthday, 3, 2) < "01" || substr($this->birthday, 3, 2) > "12") $errors[] = 'Проверьте месяц Дня рождения (допустимо от 01 до 12)';
				if (substr($this->birthday, 6, 4) < "1800" || substr($this->birthday, 6, 4) > "2100") $errors[] = 'Проверьте год Дня рождения (допустимо от 1800 до 2100)';
			}
		}

		if ($this->login == "") $errors[] = 'Укажите логин';
		if (strlen($this->login) > 50) $errors[] = "Слишком длинный логин. Можно указать не более 50-ти символов";
		// Проверяем логин на занятость. Это нужно делать только при регистрации, так как в дальнейшем логин пользователя невозможно изменить
		if ($typeOfValidation == "registration" || $typeOfValidation == "newAlienOwner") {
			if ($this->login != "" && strlen($this->login) <= 50) {
				$stmt = DBconnect::get()->stmt_init();
				if (($stmt->prepare("SELECT id FROM users WHERE login=?") === FALSE)
					OR ($stmt->bind_param("s", $this->login) === FALSE)
					OR ($stmt->execute() === FALSE)
					OR (($res = $stmt->get_result()) === FALSE)
					OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
					OR ($stmt->close() === FALSE)
				) {
					// TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
					$error[] = "Не удалось проверить логин на занятость: ошибка обращения к базе данных (. Попробуйте зайти к нам немного позже.";
				} else {
					if (count($res) != 0) $errors[] = 'Пользователь с таким логином уже существует, укажите другой логин';
				}
			}
		}

		if ($this->password == "") $errors[] = 'Укажите пароль';

		if ($typeOfValidation == "registration" || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest" || $typeOfValidation == "validateProfileParameters") {
			if ($this->telephon == "") $errors[] = 'Укажите контактный (мобильный) телефон';
		}

		if ($this->telephon != "") {
			if (!preg_match('/^[0-9]{10}$/', $this->telephon)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019';
		}

		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "createSearchRequest") || ($typeOfValidation == "validateSearchRequest") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE)) {
			if ($this->email == "") $errors[] = 'Укажите e-mail';
		}
		if ($this->email != "" && !preg_match("/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/", $this->email)) $errors[] = 'Укажите, пожалуйста, Ваш настоящий e-mail (указанный Вами e-mail не прошел проверку формата)';

		// Проверки для блока "Образование"
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->currentStatusEducation == "0") $errors[] = 'Укажите Ваше образование (текущий статус)';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->almamater == "" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил")) $errors[] = 'Укажите учебное заведение';
		}
		if (isset($this->almamater) && strlen($this->almamater) > 100) $errors[] = 'Слишком длинное название учебного заведения (используйте не более 100 символов)';

		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->speciality == "" && ($this->currentStatusEducation == "сейчас учусь" || $this->currentStatusEducation == "закончил")) $errors[] = 'Укажите специальность';
		}
		if (isset($this->speciality) && strlen($this->speciality) > 100) $errors[] = 'Слишком длинное название специальности (используйте не более 100 символов)';

		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->kurs == "" && $this->currentStatusEducation == "сейчас учусь") $errors[] = 'Укажите курс обучения';
		}
		if (isset($this->kurs) && strlen($this->kurs) > 30) $errors[] = 'Курс. Указана слишком длинная строка (используйте не более 30 символов)';

		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->ochnoZaochno == "0" && $this->currentStatusEducation == "сейчас учусь") $errors[] = 'Укажите форму обучения (очная, заочная)';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->yearOfEnd == "" && $this->currentStatusEducation == "закончил") $errors[] = 'Укажите год окончания учебного заведения';
		}
		if ($this->yearOfEnd != "" && !preg_match("/^[12]{1}[0-9]{3}$/", $this->yearOfEnd)) $errors[] = 'Укажите год окончания учебного заведения в формате: "гггг". Например: 2007';

		// Проверки для блока "Работа"
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->statusWork == "0") $errors[] = 'Укажите статус занятости';
		}

		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->placeOfWork == "" && $this->statusWork == "работаю") $errors[] = 'Укажите Ваше место работы (название организации)';
		}
		if (isset($this->placeOfWork) && strlen($this->placeOfWork) > 100) $errors[] = 'Слишком длинное наименование места работы (используйте не более 100 символов)';

		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || ($typeOfValidation == "validateProfileParameters" && $typeTenant == TRUE) || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") {
			if ($this->workPosition == "" && $this->statusWork == "работаю") $errors[] = 'Укажите Вашу должность';
		}
		if (isset($this->workPosition) && strlen($this->workPosition) > 100) $errors[] = 'Слишком длинное название должности (используйте не более 100 символов)';

		// Проверки для блока "Коротко о себе"
		if (isset($this->regionOfBorn) && strlen($this->regionOfBorn) > 50) $errors[] = 'Слишком длинное наименование региона, в котором Вы родились (используйте не более 50 символов)';
		if (isset($this->cityOfBorn) && strlen($this->cityOfBorn) > 50) $errors[] = 'Слишком длинное наименование города, в котором Вы родились (используйте не более 50 символов)';

		// Проверки для блока "Социальные сети"
		if (strlen($this->vkontakte) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу Вконтакте (используйте не более 100 символов)';
		if (strlen($this->vkontakte) > 0 && !preg_match("/vk\.com/", $this->vkontakte)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу Вконтакте, либо оставьте поле пустым (ссылка должна содержать строчку "vk.com")';
		if (strlen($this->odnoklassniki) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Одноклассниках (используйте не более 100 символов)';
		if (strlen($this->odnoklassniki) > 0 && !preg_match("/www\.odnoklassniki\.ru\/profile\//", $this->odnoklassniki)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Одноклассниках, либо оставьте поле пустым (ссылка должна содержать строчку "www.odnoklassniki.ru/profile/")';
		if (strlen($this->facebook) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу на Facebook (используйте не более 100 символов)';
		if (strlen($this->facebook) > 0 && !preg_match("/www\.facebook\.com\/profile\.php/", $this->facebook)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу на Facebook, либо оставьте поле пустым (ссылка должна содержать строчку с "www.facebook.com/profile.php")';
		if (strlen($this->twitter) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Twitter (используйте не более 100 символов)';
		if (strlen($this->twitter) > 0 && !preg_match("/twitter\.com/", $this->twitter)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Twitter, либо оставьте поле пустым (ссылка должна содержать строчку "twitter.com")';

		// Проверки для блока "Параметры поиска"
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if (!preg_match("/^\d{0,8}$/", $this->minCost)) $errors[] = 'Неправильный формат числа в поле минимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if (!preg_match("/^\d{0,8}$/", $this->maxCost)) $errors[] = 'Неправильный формат числа в поле максимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if (!preg_match("/^\d{0,8}$/", $this->pledge)) $errors[] = 'Неправильный формат числа в поле максимальной величины залога (проверьте: только числа, не более 8 символов)';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if ($this->minCost > $this->maxCost) $errors[] = 'Минимальная стоимость аренды не может быть больше, чем максимальная. Исправьте поля, в которых указаны Ваши требования к диапазону стоимости аренды';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if ($this->withWho == "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите, как Вы собираетесь проживать в арендуемой недвижимости (с кем)';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if ($this->children == "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с детьми или без них';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if ($this->animals == "0" && $this->typeOfObject != "гараж") $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с животными или без них';
		}
		if (($typeOfValidation == "registration" && $typeTenant == TRUE) || $typeOfValidation == "validateSearchRequest") {
			if ($this->termOfLease == "0") $errors[] = 'Укажите предполагаемый срок аренды';
		}

		// Проверка согласия пользователя с лицензией
		if ($typeOfValidation == "registration") {
			if ($this->lic != "yes") $errors[] = 'Регистрация возможна только при согласии с условиями лицензионного соглашения';
		}

		return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
	}

	// Используется при регистрации нового пользователя - позволяет получить идентификатор, используя логин.
	// Полученный идентификатор также указывается в параметрах данного объекта
	public function getIdUseLogin() {

		if ($this->login == "") return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT id FROM users WHERE login=?") === FALSE)
			OR ($stmt->bind_param("s", $this->login) === FALSE)
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

	// Если пользователь еще только регистрируется, то необходимо установить его статус в зависимости от строки запроса, по которой была запрошена страница регистрации. В этом строке могут быть указана доп. параметры - в качестве арендатора или в качестве собственника регистрируется пользователь
	public function setTypeTenantOwnerFromGET() {

		if (isset($_GET['typeTenant'])) {
			$this->typeTenant = TRUE;
		} else {
			$this->typeTenant = FALSE;
		}
		if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {
			$this->typeTenant = TRUE;
		}


		if (isset($_GET['typeOwner'])) {
			$this->typeOwner = TRUE;
		} else {
			$this->typeOwner = FALSE;
		}
		if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {
			$this->typeOwner = TRUE;
		}

	}

	// Получить все уведомления пользователя (в виде массива массивов)
	// Уведомления сортируются следующим образом: наверху все непрочитанные, внизу прочитанные, каждая из категорий сортируется по времени появления: появившиеся позже сверху
	public function getAllMessagesSorted() {

		// Инициализируем массив, который вернем по окончанию выполнения метода
		$messagesNewProperty = array();

		// Получим уведомления по новым объектам недвижимости, соответствующим запросу, если наш пользователь - арендатор
		if ($this->id != "" && $this->typeTenant === TRUE) {
			$stmt = DBconnect::get()->stmt_init();
			if (($stmt->prepare("SELECT * FROM messagesNewProperty WHERE userId = ? ORDER BY isReaded DESC, timeIndex DESC") === FALSE)
				OR ($stmt->bind_param("s", $this->id) === FALSE)
				OR ($stmt->execute() === FALSE)
				OR (($messagesNewProperty = $stmt->get_result()) === FALSE)
				OR (($messagesNewProperty = $messagesNewProperty->fetch_all(MYSQLI_ASSOC)) === FALSE)
				OR ($stmt->close() === FALSE)
			) {
				$messagesNewProperty = array();
				// TODO: Логируем ошибку
			}
		}

		// Преобразования над данными - чтобы обеспечить удобство работы на клиенте с ними
		for ($i = 0, $s = count($messagesNewProperty); $i < $s; $i++) {
			$messagesNewProperty[$i]['fotoArr'] = unserialize($messagesNewProperty[$i]['fotoArr']);
		}

		//TODO: реализовать получение новостей и из других таблиц

		//TODO: когда будет несколько таблиц, Сортируем результат по статусу прочитанности и по времени появления

		return $messagesNewProperty;
	}

}