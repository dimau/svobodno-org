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
	/**
	 * Cодержит объект соединения с базой данных класса mysqli (единственный на весь скрипт)
	 * @var mysqli
	 */
	private static $connect;

	/**
	 * Возвращает объект mysqli для прямого обращения к БД
	 *
	 * @return mysqli|bool возвращает объект класса mysqli для прямой работы с БД в случае успеха (удалось получить соединение с БД) и FALSE в ином случае
	 */
	public static function get() {
		if (self::$connect === NULL) { // Если соединение с БД еще не устанавливалось
			self::$connect = self::connectToDB(); // Создаем объект соединения с БД
		}

		return self::$connect; // Возвращаем объект соединения с БД. Либо FALSE, если установить соединение не удалось
	}

	/**
	 * Метод возвращает объект соединения с БД (mysqli), либо FALSE
	 * Метод отрабатывает один раз при вызове DBconnect::get();
	 *
	 * @return mysqli|bool
	 */
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

    /**
     * Возвращает ассоциированный массив, который содержит полные данные (характеристику) по пользователю системы. Если ничего не найдено или произошла ошибка, вернет пустой массив
     *
     * @param int $userId - идентификатор пользователя, по которому нужно получить данные
     * @return array - ассоциированный массив, содержащий все параметры характеристики пользователя
     */
    public static function selectUserCharacteristic($userId) {

        // Проверка входящих параметров
        if (!isset($userId) || !is_int($userId)) return array();

        // Получим из БД данные ($res) по искомому пользователю
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("SELECT * FROM users WHERE id = ? LIMIT 1") === FALSE)
            OR ($stmt->bind_param("i", $userId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->get_result()) === FALSE)
            OR (($res = $res->fetch_assoc()) === NULL)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM users WHERE id = ".$userId." LIMIT 1'. id логгера: DBconnect::selectUserCharacteristic():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return array();
        }

        // Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
        $res = DBconnect::conversionUserCharacteristicFromDBToView($res);

        // Вернем результат
        return $res;
    }

    /**
     * Возвращает ассоциированный массив, который содержит полные данные (характеристику) по пользователю системы с известным логином. Если ничего не найдено или произошла ошибка, вернет пустой массив
     *
     * @param string $login - логин пользователя, по которому нужно получить данные
     * @return array - ассоциированный массив, содержащий все параметры характеристики пользователя
     */
    public static function selectUserCharacteristicForLogin($login) {

        // Проверка входящих параметров
        if (!isset($login) || !is_string($login)) return array();

        // Получим из БД данные ($res) по искомому пользователю
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("SELECT * FROM users WHERE login = ? LIMIT 1") === FALSE)
            OR ($stmt->bind_param("s", $login) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->get_result()) === FALSE)
            OR (($res = $res->fetch_assoc()) === NULL)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM users WHERE login = ".$login." LIMIT 1'. id логгера: DBconnect::selectUserCharacteristicForLogin():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return array();
        }

        // Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
        $res = DBconnect::conversionUserCharacteristicFromDBToView($res);

        // Вернем результат
        return $res;
    }

    /**
     * Возвращает ассоциированный массив, который содержит полные данные (характеристику) по пользователю системы с известным хэшем. Если ничего не найдено или произошла ошибка, вернет пустой массив
     *
     * @param string $user_hash - хэш пользователя, по которому нужно получить данные
     * @return array - ассоциированный массив, содержащий все параметры характеристики пользователя
     */
    public static function selectUserCharacteristicForHash($user_hash) {

        // Проверка входящих параметров
        if (!isset($user_hash) || !is_string($user_hash)) return array();

        // Получим из БД данные ($res) по искомому пользователю
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("SELECT * FROM users WHERE user_hash = ? LIMIT 1") === FALSE)
            OR ($stmt->bind_param("s", $user_hash) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->get_result()) === FALSE)
            OR (($res = $res->fetch_assoc()) === NULL)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM users WHERE user_hash = ".$user_hash." LIMIT 1'. id логгера: DBconnect::selectUserCharacteristicForHash():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return array();
        }

        // Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
        $res = DBconnect::conversionUserCharacteristicFromDBToView($res);

        // Вернем результат
        return $res;
    }

    /**
     * Возвращает ассоциированный массив, который содержит полные данные по объекту недвижимости. Если ничего не найдено или произошла ошибка, вернет пустой массив
     *
     * @param int $propertyId - идентификатор объекта недвижимости, по которому нужно получить данные
     * @return array - ассоциированный массив, содержащий все параметры характеристики объекта недвижимости
     */
    public static function selectPropertyCharacteristic($propertyId) {

        // Проверка входящих параметров
        if (!isset($propertyId) || !is_int($propertyId)) return array();

        // Получим из БД данные ($res) по искомому объекту недвижимости
        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("SELECT * FROM property WHERE id = ? LIMIT 1") === FALSE)
            OR ($stmt->bind_param("i", $propertyId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->get_result()) === FALSE)
            OR (($res = $res->fetch_assoc()) === NULL)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM property WHERE id = ".$propertyId."'. id логгера: DBconnect::selectPropertyCharacteristic():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return array();
        }

        // Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
        $res = DBconnect::conversionPropertyCharacteristicFromDBToView($res);

        // Вернем результат
        return $res;
    }

	/**
	 * Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из объектов недвижимости. Если ничего не найдено или произошла ошибка, вернет пустой массив
	 * ВНИМАНИЕ: массивы могут быть расположены не в том же порядке, в каком идентификаторы располагались во входном массиве
	 *
	 * @param int|array $propertiesId - идентификатор объекта недвижимости, либо массив идентификаторов объектов недвижимости, по которым нужно получить данные
	 * @return array
	 */
	public static function selectCharacteristicForProperties($propertiesId) {

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
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM property WHERE".$strWHERE."'. id логгера: DBconnect::selectCharacteristicForProperties():1. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID пользователя: не определено");
			return array();
		}

		// Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
		for ($i = 0, $s = count($res); $i < $s; $i++) {
			$res[$i] = DBconnect::conversionPropertyCharacteristicFromDBToView($res[$i]);
		}

		// Вернем результат
		return $res;
	}

    // Функция возвращает подробные сведения по объектам недвижимости из БД
    // В случае ошибки возвращает FALSE, елси данные получить не удалось, то пустой массив
    // На входе - отсортированный массив id объектов недвижимости
    // $mode - режим работы. "all" - выдать данные по всем объектам (вне зависимости опубликованы они или нет), "published" - выдать данные только по опубликованным объектам
    // На выходе - отсортированный в том же порядке массив ассоциативных массивов, каждый из которых содержит все параметры одного объекта, в том числе его фотографии
    // TODO: переделать функцию или избавиться от нее или переназвать
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

        // Подготовим данные объектов недвижимости к обработке в php
        for ($i = 0, $s = count($propertyFullArr); $i < $s; $i++) {
            $propertyFullArr[$i] = DBconnect::conversionPropertyCharacteristicFromDBToView($propertyFullArr[$i]);
        }

        // Получим данные о фотографиях для каждого объекта из $propertyFullArr
        for ($i = 0, $s = count($propertyFullArr); $i < $s; $i++) {
            // Получим данные о фотографиях по id объекта недвижимости
            $propertyFotos = DBconnect::selectPhotosForProperty(intval($propertyFullArr[$i]['id']));
            // Записываем полученный массив массивов с данными о фотографиях в специальный новый параметр массива $propertyFullArr
            $propertyFullArr[$i]['propertyFotos'] = $propertyFotos;
        }

        return $propertyFullArr;
    }

	/**
	 * Функция возвращает массив массивов с названиями районов в городе $city
	 *
	 * @param string $city город, чей список районов мы хотим получить
	 * @return array массив ассоциированных массивов, содержащих только один ключ-значение = названию района города
	 */
	public static function selectDistrictsForCity($city = "Екатеринбург")
	{
		// Проверка входных параметров
		if ($city != "Екатеринбург") return array();

		// Получим из БД данные ($res) по пользователю с логином = $login
		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT name FROM districts WHERE city=? ORDER BY name ASC") === FALSE)
			OR ($stmt->bind_param("s", $city) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT name FROM districts WHERE city = ".$city." ORDER BY name ASC'. id логгера: DBconnect::selectDistrictsForCity():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		return $res;
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

	/**
	 * Возвращает поисковый запрос пользователя
	 *
	 * @param int $userId идентификатор пользователя, чей поисковый запрос мы хотим получить
	 * @return array массив ассоциативных массивов, содержащих параметры поисковых запросов (теоретически всегда должен быть только 1 ассоциативный массив внутри этого возвращаемого). Если ничего не найдено, то вернет пустой массив
	 */
	public static function selectSearchRequestForUser($userId) {

		// Проверка входящих параметров
		if (!isset($userId) || !is_int($userId)) return array();

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT * FROM searchRequests WHERE userId = ?") === FALSE)
			OR ($stmt->bind_param("i", $userId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM searchRequests WHERE userId = " . $userId . "'. Местонахождение кода: DBconnect::selectSearchRequestForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return array();
		}

		// Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
		for ($i = 0, $s = count($res); $i < $s; $i++) {
            $res[$i] = DBconnect::conversionSearchRequestFromDBToView($res[$i]);
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

		// Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
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

		// Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
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

	/**
	 * Возвращает массив ассоциированных массивов из таблицы архивных объявлений, каждый из которых содержит данные по одному из объектов недвижимости. Если ничего не найдено или произошла ошибка, вернет пустой массив
	 * ВНИМАНИЕ: массивы могут быть расположены не в том же порядке, в каком идентификаторы располагались во входном массиве
	 *
	 * @param int|array $propertiesId - идентификатор объекта недвижимости, либо массив идентификаторов объектов недвижимости, по которым нужно получить данные
	 * @return array
	 */
	public static function selectPropertyCharacteristicFromArchive($propertiesId) {

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
		$res = DBconnect::get()->query("SELECT * FROM archiveAdverts WHERE" . $strWHERE);
		if ((DBconnect::get()->errno)
			OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM archiveAdverts WHERE".$strWHERE."'. id логгера: DBconnect::selectPropertyCharacteristicFromArchive():1. Выдаваемая ошибка: " . DBconnect::get()->errno . " " . DBconnect::get()->error . ". ID пользователя: не определено");
			return array();
		}

		// Преобразование данных из формата хранения в БД в формат, с которым работают php скрипты
		for ($i = 0, $s = count($res); $i < $s; $i++) {
			$res[$i] = DBconnect::conversionPropertyCharacteristicFromDBToView($res[$i]);
		}

		// Вернем результат
		return $res;
	}

    /**
     * Сохраняет данные об объекте недвижимости в БД
     *
     * @param array $paramsArr ассоциативный массив параметров пользователя
     * @return bool возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
    public static function insertUserCharacteristic($paramsArr) {

		// Проверка входящих параметров
		if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

		// Подготовка данных к записи в БД
		$paramsArr = DBconnect::conversionUserCharacteristicFromViewToDB($paramsArr);

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("INSERT INTO users SET typeTenant=?, typeOwner=?, name=?, secondName=?, surname=?, sex=?, nationality=?, birthday=?, login=?, password=?, telephon=?, emailReg=?, email=?, currentStatusEducation=?, almamater=?, speciality=?, kurs=?, ochnoZaochno=?, yearOfEnd=?, statusWork=?, placeOfWork=?, workPosition=?, regionOfBorn=?, cityOfBorn=?, shortlyAboutMe=?, vkontakte=?, odnoklassniki=?, facebook=?, twitter=?, lic=?, last_act=?, reg_date=?, favoritePropertiesId=?") === FALSE)
			OR ($stmt->bind_param("ssssssssssssssssssssssssssssssiis", $paramsArr['typeTenant'], $paramsArr['typeOwner'], $paramsArr['name'], $paramsArr['secondName'], $paramsArr['surname'], $paramsArr['sex'], $paramsArr['nationality'], $paramsArr['birthday'], $paramsArr['login'], $paramsArr['password'], $paramsArr['telephon'], $paramsArr['emailReg'], $paramsArr['email'], $paramsArr['currentStatusEducation'], $paramsArr['almamater'], $paramsArr['speciality'], $paramsArr['kurs'], $paramsArr['ochnoZaochno'], $paramsArr['yearOfEnd'], $paramsArr['statusWork'], $paramsArr['placeOfWork'], $paramsArr['workPosition'], $paramsArr['regionOfBorn'], $paramsArr['cityOfBorn'], $paramsArr['shortlyAboutMe'], $paramsArr['vkontakte'], $paramsArr['odnoklassniki'], $paramsArr['facebook'], $paramsArr['twitter'], $paramsArr['lic'], $paramsArr['last_act'], $paramsArr['reg_date'], $paramsArr['favoritePropertiesId']) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($res === 0)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO users SET typeTenant=".$paramsArr['typeTenant'].", typeOwner=".$paramsArr['typeOwner'].", name=".$paramsArr['name'].", secondName=".$paramsArr['secondName'].", surname=".$paramsArr['surname'].", sex=".$paramsArr['sex'].", nationality=".$paramsArr['nationality'].", birthday=".$paramsArr['birthday'].", login=".$paramsArr['login'].", password=".$paramsArr['password'].", telephon=".$paramsArr['telephon'].", emailReg=".$paramsArr['emailReg'].", email=".$paramsArr['email'].", currentStatusEducation=".$paramsArr['currentStatusEducation'].", almamater=".$paramsArr['almamater'].", speciality=".$paramsArr['speciality'].", kurs=".$paramsArr['kurs'].", ochnoZaochno=".$paramsArr['ochnoZaochno'].", yearOfEnd=".$paramsArr['yearOfEnd'].", statusWork=".$paramsArr['statusWork'].", placeOfWork=".$paramsArr['placeOfWork'].", workPosition=".$paramsArr['workPosition'].", regionOfBorn=".$paramsArr['regionOfBorn'].", cityOfBorn=".$paramsArr['cityOfBorn'].", shortlyAboutMe=".$paramsArr['shortlyAboutMe'].", vkontakte=".$paramsArr['vkontakte'].", odnoklassniki=".$paramsArr['odnoklassniki'].", facebook=".$paramsArr['facebook'].", twitter=".$paramsArr['twitter'].", lic=".$paramsArr['lic'].", last_act=".$paramsArr['last_act'].", reg_date=".$paramsArr['reg_date'].", favoritePropertiesId=".$paramsArr['favoritePropertiesId']."'. id логгера: DBconnect::insertUserCharacteristic():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

    /**
     * Сохраняет данные о новом объекте недвижимости в БД
     *
     * @param array $paramsArr ассоциативный массив параметров объекта недвижимости
     * @return bool возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
    public static function insertPropertyCharacteristic($paramsArr) {

        // Проверка входящих параметров
        if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

        // Подготовка данных к записи в БД
        $paramsArr = DBconnect::conversionPropertyCharacteristicFromViewToDB($paramsArr);

        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("INSERT INTO property SET userId=?, typeOfObject=?, dateOfEntry=?, termOfLease=?, dateOfCheckOut=?, amountOfRooms=?, adjacentRooms=?, amountOfAdjacentRooms=?, typeOfBathrooms=?, typeOfBalcony=?, balconyGlazed=?, roomSpace=?, totalArea=?, livingSpace=?, kitchenSpace=?, floor=?, totalAmountFloor=?, numberOfFloor=?, concierge=?, intercom=?, parking=?, city=?, district=?, coordX=?, coordY=?, address=?, apartmentNumber=?, subwayStation=?, distanceToMetroStation=?, currency=?, costOfRenting=?, realCostOfRenting=?, utilities=?, costInSummer=?, costInWinter=?, electricPower=?, bail=?, bailCost=?, prepayment=?, compensationMoney=?, compensationPercent=?, repair=?, furnish=?, windows=?, internet=?, telephoneLine=?, cableTV=?, furnitureInLivingArea=?, furnitureInLivingAreaExtra=?, furnitureInKitchen=?, furnitureInKitchenExtra=?, appliances=?, appliancesExtra=?, sexOfTenant=?, relations=?, children=?, animals=?, contactTelephonNumber=?, timeForRingBegin=?, timeForRingEnd=?, checking=?, responsibility=?, comment=?, last_act=?, reg_date=?, status=?, earliestDate=?, earliestTimeHours=?, earliestTimeMinutes=?, adminComment=?, completeness=?") === FALSE)
            OR ($stmt->bind_param("sssssssssssddddiiissssssssssisddsddssdsddssssssssssssssssssssssiissssss", $paramsArr['userId'], $paramsArr['typeOfObject'], $paramsArr['dateOfEntry'], $paramsArr['termOfLease'], $paramsArr['dateOfCheckOut'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['amountOfAdjacentRooms'], $paramsArr['typeOfBathrooms'], $paramsArr['typeOfBalcony'], $paramsArr['balconyGlazed'], $paramsArr['roomSpace'], $paramsArr['totalArea'], $paramsArr['livingSpace'], $paramsArr['kitchenSpace'], $paramsArr['floor'], $paramsArr['totalAmountFloor'], $paramsArr['numberOfFloor'], $paramsArr['concierge'], $paramsArr['intercom'], $paramsArr['parking'], $paramsArr['city'], $paramsArr['district'], $paramsArr['coordX'], $paramsArr['coordY'], $paramsArr['address'], $paramsArr['apartmentNumber'], $paramsArr['subwayStation'], $paramsArr['distanceToMetroStation'], $paramsArr['currency'], $paramsArr['costOfRenting'], $paramsArr['realCostOfRenting'], $paramsArr['utilities'], $paramsArr['costInSummer'], $paramsArr['costInWinter'], $paramsArr['electricPower'], $paramsArr['bail'], $paramsArr['bailCost'], $paramsArr['prepayment'], $paramsArr['compensationMoney'], $paramsArr['compensationPercent'], $paramsArr['repair'], $paramsArr['furnish'], $paramsArr['windows'], $paramsArr['internet'], $paramsArr['telephoneLine'], $paramsArr['cableTV'], $paramsArr['furnitureInLivingArea'], $paramsArr['furnitureInLivingAreaExtra'], $paramsArr['furnitureInKitchen'], $paramsArr['furnitureInKitchenExtra'], $paramsArr['appliances'], $paramsArr['appliancesExtra'], $paramsArr['sexOfTenant'], $paramsArr['relations'], $paramsArr['children'], $paramsArr['animals'], $paramsArr['contactTelephonNumber'], $paramsArr['timeForRingBegin'], $paramsArr['timeForRingEnd'], $paramsArr['checking'], $paramsArr['responsibility'], $paramsArr['comment'], $paramsArr['last_act'], $paramsArr['reg_date'], $paramsArr['status'], $paramsArr['earliestDate'], $paramsArr['earliestTimeHours'], $paramsArr['earliestTimeMinutes'], $paramsArr['adminComment'], $paramsArr['completeness']) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($res === 0)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO property SET userId=".$paramsArr['userId'].", typeOfObject=".$paramsArr['typeOfObject'].", dateOfEntry=".$paramsArr['dateOfEntry'].", termOfLease=".$paramsArr['termOfLease'].", dateOfCheckOut=".$paramsArr['dateOfCheckOut'].", amountOfRooms=".$paramsArr['amountOfRooms'].", adjacentRooms=".$paramsArr['adjacentRooms'].", amountOfAdjacentRooms=".$paramsArr['amountOfAdjacentRooms'].", typeOfBathrooms=".$paramsArr['typeOfBathrooms'].", typeOfBalcony=".$paramsArr['typeOfBalcony'].", balconyGlazed=".$paramsArr['balconyGlazed'].", roomSpace=".$paramsArr['roomSpace'].", totalArea=".$paramsArr['totalArea'].", livingSpace=".$paramsArr['livingSpace'].", kitchenSpace=".$paramsArr['kitchenSpace'].", floor=".$paramsArr['floor'].", totalAmountFloor=".$paramsArr['totalAmountFloor'].", numberOfFloor=".$paramsArr['numberOfFloor'].", concierge=".$paramsArr['concierge'].", intercom=".$paramsArr['intercom'].", parking=".$paramsArr['parking'].", city=".$paramsArr['city'].", district=".$paramsArr['district'].", coordX=".$paramsArr['coordX'].", coordY=".$paramsArr['coordY'].", address=".$paramsArr['address'].", apartmentNumber=".$paramsArr['apartmentNumber'].", subwayStation=".$paramsArr['subwayStation'].", distanceToMetroStation=".$paramsArr['distanceToMetroStation'].", currency=".$paramsArr['currency'].", costOfRenting=".$paramsArr['costOfRenting'].", realCostOfRenting=".$paramsArr['realCostOfRenting'].", utilities=".$paramsArr['utilities'].", costInSummer=".$paramsArr['costInSummer'].", costInWinter=".$paramsArr['costInWinter'].", electricPower=".$paramsArr['electricPower'].", bail=".$paramsArr['bail'].", bailCost=".$paramsArr['bailCost'].", prepayment=".$paramsArr['prepayment'].", compensationMoney=".$paramsArr['compensationMoney'].", compensationPercent=".$paramsArr['compensationPercent'].", repair=".$paramsArr['repair'].", furnish=".$paramsArr['furnish'].", windows=".$paramsArr['windows'].", internet=".$paramsArr['internet'].", telephoneLine=".$paramsArr['telephoneLine'].", cableTV=".$paramsArr['cableTV'].", furnitureInLivingArea=".$paramsArr['furnitureInLivingArea'].", furnitureInLivingAreaExtra=".$paramsArr['furnitureInLivingAreaExtra'].", furnitureInKitchen=".$paramsArr['furnitureInKitchen'].", furnitureInKitchenExtra=".$paramsArr['furnitureInKitchenExtra'].", appliances=".$paramsArr['appliances'].", appliancesExtra=".$paramsArr['appliancesExtra'].", sexOfTenant=".$paramsArr['sexOfTenant'].", relations=".$paramsArr['relations'].", children=".$paramsArr['children'].", animals=".$paramsArr['animals'].", contactTelephonNumber=".$paramsArr['contactTelephonNumber'].", timeForRingBegin=".$paramsArr['timeForRingBegin'].", timeForRingEnd=".$paramsArr['timeForRingEnd'].", checking=".$paramsArr['checking'].", responsibility=".$paramsArr['responsibility'].", comment=".$paramsArr['comment'].", last_act=".$paramsArr['last_act'].", reg_date=".$paramsArr['reg_date'].", status=".$paramsArr['status'].", earliestDate=".$paramsArr['earliestDate'].", earliestTimeHours=".$paramsArr['earliestTimeHours'].", earliestTimeMinutes=".$paramsArr['earliestTimeMinutes'].", adminComment=".$paramsArr['adminComment'].", completeness=".$paramsArr['completeness']."'. id логгера: DBconnect::insertPropertyCharacteristic():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return FALSE;
        }

        return TRUE;
    }

	// Сохраняет данные о чужом объявлении в архивную таблицу
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function insertPropertyCharacteristicToArchive($paramsArr) {

		// Проверка входящих параметров
		if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

		// Подготовка данных к записи в БД
		$paramsArr = DBconnect::conversionPropertyCharacteristicFromViewToDB($paramsArr);

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("INSERT INTO archiveAdverts SET id=?, userId=?, typeOfObject=?, dateOfEntry=?, termOfLease=?, dateOfCheckOut=?, amountOfRooms=?, adjacentRooms=?, amountOfAdjacentRooms=?, typeOfBathrooms=?, typeOfBalcony=?, balconyGlazed=?, roomSpace=?, totalArea=?, livingSpace=?, kitchenSpace=?, floor=?, totalAmountFloor=?, numberOfFloor=?, concierge=?, intercom=?, parking=?, city=?, district=?, coordX=?, coordY=?, address=?, apartmentNumber=?, subwayStation=?, distanceToMetroStation=?, currency=?, costOfRenting=?, realCostOfRenting=?, utilities=?, costInSummer=?, costInWinter=?, electricPower=?, bail=?, bailCost=?, prepayment=?, compensationMoney=?, compensationPercent=?, repair=?, furnish=?, windows=?, internet=?, telephoneLine=?, cableTV=?, furnitureInLivingArea=?, furnitureInLivingAreaExtra=?, furnitureInKitchen=?, furnitureInKitchenExtra=?, appliances=?, appliancesExtra=?, sexOfTenant=?, relations=?, children=?, animals=?, contactTelephonNumber=?, timeForRingBegin=?, timeForRingEnd=?, checking=?, responsibility=?, comment=?, last_act=?, reg_date=?, status=?, earliestDate=?, earliestTimeHours=?, earliestTimeMinutes=?, adminComment=?, completeness=?") === FALSE)
			OR ($stmt->bind_param("isssssssssssddddiiissssssssssisddsddssdsddssssssssssssssssssssssiissssss", $paramsArr['id'], $paramsArr['userId'], $paramsArr['typeOfObject'], $paramsArr['dateOfEntry'], $paramsArr['termOfLease'], $paramsArr['dateOfCheckOut'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['amountOfAdjacentRooms'], $paramsArr['typeOfBathrooms'], $paramsArr['typeOfBalcony'], $paramsArr['balconyGlazed'], $paramsArr['roomSpace'], $paramsArr['totalArea'], $paramsArr['livingSpace'], $paramsArr['kitchenSpace'], $paramsArr['floor'], $paramsArr['totalAmountFloor'], $paramsArr['numberOfFloor'], $paramsArr['concierge'], $paramsArr['intercom'], $paramsArr['parking'], $paramsArr['city'], $paramsArr['district'], $paramsArr['coordX'], $paramsArr['coordY'], $paramsArr['address'], $paramsArr['apartmentNumber'], $paramsArr['subwayStation'], $paramsArr['distanceToMetroStation'], $paramsArr['currency'], $paramsArr['costOfRenting'], $paramsArr['realCostOfRenting'], $paramsArr['utilities'], $paramsArr['costInSummer'], $paramsArr['costInWinter'], $paramsArr['electricPower'], $paramsArr['bail'], $paramsArr['bailCost'], $paramsArr['prepayment'], $paramsArr['compensationMoney'], $paramsArr['compensationPercent'], $paramsArr['repair'], $paramsArr['furnish'], $paramsArr['windows'], $paramsArr['internet'], $paramsArr['telephoneLine'], $paramsArr['cableTV'], $paramsArr['furnitureInLivingArea'], $paramsArr['furnitureInLivingAreaExtra'], $paramsArr['furnitureInKitchen'], $paramsArr['furnitureInKitchenExtra'], $paramsArr['appliances'], $paramsArr['appliancesExtra'], $paramsArr['sexOfTenant'], $paramsArr['relations'], $paramsArr['children'], $paramsArr['animals'], $paramsArr['contactTelephonNumber'], $paramsArr['timeForRingBegin'], $paramsArr['timeForRingEnd'], $paramsArr['checking'], $paramsArr['responsibility'], $paramsArr['comment'], $paramsArr['last_act'], $paramsArr['reg_date'], $paramsArr['status'], $paramsArr['earliestDate'], $paramsArr['earliestTimeHours'], $paramsArr['earliestTimeMinutes'], $paramsArr['adminComment'], $paramsArr['completeness']) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($res === 0)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO archiveAdverts SET id = ".$paramsArr['id'].", userId=".$paramsArr['userId'].", typeOfObject=".$paramsArr['typeOfObject'].", dateOfEntry=".$paramsArr['dateOfEntry'].", termOfLease=".$paramsArr['termOfLease'].", dateOfCheckOut=".$paramsArr['dateOfCheckOut'].", amountOfRooms=".$paramsArr['amountOfRooms'].", adjacentRooms=".$paramsArr['adjacentRooms'].", amountOfAdjacentRooms=".$paramsArr['amountOfAdjacentRooms'].", typeOfBathrooms=".$paramsArr['typeOfBathrooms'].", typeOfBalcony=".$paramsArr['typeOfBalcony'].", balconyGlazed=".$paramsArr['balconyGlazed'].", roomSpace=".$paramsArr['roomSpace'].", totalArea=".$paramsArr['totalArea'].", livingSpace=".$paramsArr['livingSpace'].", kitchenSpace=".$paramsArr['kitchenSpace'].", floor=".$paramsArr['floor'].", totalAmountFloor=".$paramsArr['totalAmountFloor'].", numberOfFloor=".$paramsArr['numberOfFloor'].", concierge=".$paramsArr['concierge'].", intercom=".$paramsArr['intercom'].", parking=".$paramsArr['parking'].", city=".$paramsArr['city'].", district=".$paramsArr['district'].", coordX=".$paramsArr['coordX'].", coordY=".$paramsArr['coordY'].", address=".$paramsArr['address'].", apartmentNumber=".$paramsArr['apartmentNumber'].", subwayStation=".$paramsArr['subwayStation'].", distanceToMetroStation=".$paramsArr['distanceToMetroStation'].", currency=".$paramsArr['currency'].", costOfRenting=".$paramsArr['costOfRenting'].", realCostOfRenting=".$paramsArr['realCostOfRenting'].", utilities=".$paramsArr['utilities'].", costInSummer=".$paramsArr['costInSummer'].", costInWinter=".$paramsArr['costInWinter'].", electricPower=".$paramsArr['electricPower'].", bail=".$paramsArr['bail'].", bailCost=".$paramsArr['bailCost'].", prepayment=".$paramsArr['prepayment'].", compensationMoney=".$paramsArr['compensationMoney'].", compensationPercent=".$paramsArr['compensationPercent'].", repair=".$paramsArr['repair'].", furnish=".$paramsArr['furnish'].", windows=".$paramsArr['windows'].", internet=".$paramsArr['internet'].", telephoneLine=".$paramsArr['telephoneLine'].", cableTV=".$paramsArr['cableTV'].", furnitureInLivingArea=".$paramsArr['furnitureInLivingArea'].", furnitureInLivingAreaExtra=".$paramsArr['furnitureInLivingAreaExtra'].", furnitureInKitchen=".$paramsArr['furnitureInKitchen'].", furnitureInKitchenExtra=".$paramsArr['furnitureInKitchenExtra'].", appliances=".$paramsArr['appliances'].", appliancesExtra=".$paramsArr['appliancesExtra'].", sexOfTenant=".$paramsArr['sexOfTenant'].", relations=".$paramsArr['relations'].", children=".$paramsArr['children'].", animals=".$paramsArr['animals'].", contactTelephonNumber=".$paramsArr['contactTelephonNumber'].", timeForRingBegin=".$paramsArr['timeForRingBegin'].", timeForRingEnd=".$paramsArr['timeForRingEnd'].", checking=".$paramsArr['checking'].", responsibility=".$paramsArr['responsibility'].", comment=".$paramsArr['comment'].", last_act=".$paramsArr['last_act'].", reg_date=".$paramsArr['reg_date'].", status=".$paramsArr['status'].", earliestDate=".$paramsArr['earliestDate'].", earliestTimeHours=".$paramsArr['earliestTimeHours'].", earliestTimeMinutes=".$paramsArr['earliestTimeMinutes'].", adminComment=".$paramsArr['adminComment'].", completeness=".$paramsArr['completeness']."'. id логгера: DBconnect::insertPropertyCharacteristicToArchive():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
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
	 * Сохраняет новый поисковый запрос
	 *
	 * @param array $paramsArr ассоциативный массив параметров поискового запроса
	 * @return bool TRUE в случае успеха и FALSE в случае неудачи
	 */
	public static function insertSearchRequestForUser($paramsArr) {

		// Проверка входящих параметров
		if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

		// Подготовка данных к записи в БД
		$paramsArr['amountOfRooms'] = serialize($paramsArr['amountOfRooms']);
		$paramsArr['district'] = serialize($paramsArr['district']);

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("INSERT INTO searchRequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, minCost, maxCost, pledge, prepayment, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, termOfLease, additionalDescriptionOfSearch, regDate, needEmail, needSMS) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)") === FALSE)
			OR ($stmt->bind_param("issssiiissssssssssiii", $paramsArr['userId'], $paramsArr['typeOfObject'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['floor'], $paramsArr['minCost'], $paramsArr['maxCost'], $paramsArr['pledge'], $paramsArr['prepayment'], $paramsArr['district'], $paramsArr['withWho'], $paramsArr['linksToFriends'], $paramsArr['children'], $paramsArr['howManyChildren'], $paramsArr['animals'], $paramsArr['howManyAnimals'], $paramsArr['termOfLease'], $paramsArr['additionalDescriptionOfSearch'], $paramsArr['regDate'], $paramsArr['needEmail'], $paramsArr['needSMS']) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($res === 0)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'INSERT INTO searchRequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, minCost, maxCost, pledge, prepayment, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, termOfLease, additionalDescriptionOfSearch, regDate, needEmail, needSMS) VALUES (".$paramsArr['userId'].",".$paramsArr['typeOfObject'].",".$paramsArr['amountOfRooms'].",".$paramsArr['adjacentRooms'].",".$paramsArr['floor'].",".$paramsArr['minCost'].",".$paramsArr['maxCost'].",".$paramsArr['pledge'].",".$paramsArr['prepayment'].",".$paramsArr['district'].",".$paramsArr['withWho'].",".$paramsArr['linksToFriends'].",".$paramsArr['children'].",".$paramsArr['howManyChildren'].",".$paramsArr['animals'].",".$paramsArr['howManyAnimals'].",".$paramsArr['termOfLease'].",".$paramsArr['additionalDescriptionOfSearch'].",".$paramsArr['regDate'].",".$paramsArr['needEmail'].",".$paramsArr['needSMS'].")'. id логгера: DBconnect::insertSearchRequestForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Сохраняет данные о новом уведомлении в БД. Если передан второй аргумент - массив идентификаторов арендаторов, то уведомление будет сохранено для каждого из них
	 *
	 * @param array $paramsArr параметры уведомления
	 * @param array $listOfTargetUsers массив ассоциативных массивов, содержащих идентификаторы пользователей и их параметры рассылки (email и sms), для каждого из которых нужно сформировать данное уведомление
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

    /**
     * Изменяет данные о пользователе в БД
     *
     * @param array $paramsArr ассоциативный массив параметров пользователя
     * @return bool возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
    public static function updateUserCharacteristic($paramsArr) {

        // Проверка входящих параметров
        if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

        // Подготовка данных к записи в БД
        $paramsArr = DBconnect::conversionUserCharacteristicFromViewToDB($paramsArr);

        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE users SET typeTenant=?, typeOwner=?, name=?, secondName=?, surname=?, sex=?, nationality=?, birthday=?, login=?, password=?, telephon=?, emailReg=?, email=?, currentStatusEducation=?, almamater=?, speciality=?, kurs=?, ochnoZaochno=?, yearOfEnd=?, statusWork=?, placeOfWork=?, workPosition=?, regionOfBorn=?, cityOfBorn=?, shortlyAboutMe=?, vkontakte=?, odnoklassniki=?, facebook=?, twitter=?, lic=?, last_act=?, reg_date=?, favoritePropertiesId=? WHERE id=?") === FALSE)
            OR ($stmt->bind_param("ssssssssssssssssssssssssssssssiisi", $paramsArr['typeTenant'], $paramsArr['typeOwner'], $paramsArr['name'], $paramsArr['secondName'], $paramsArr['surname'], $paramsArr['sex'], $paramsArr['nationality'], $paramsArr['birthday'], $paramsArr['login'], $paramsArr['password'], $paramsArr['telephon'], $paramsArr['emailReg'], $paramsArr['email'], $paramsArr['currentStatusEducation'], $paramsArr['almamater'], $paramsArr['speciality'], $paramsArr['kurs'], $paramsArr['ochnoZaochno'], $paramsArr['yearOfEnd'], $paramsArr['statusWork'], $paramsArr['placeOfWork'], $paramsArr['workPosition'], $paramsArr['regionOfBorn'], $paramsArr['cityOfBorn'], $paramsArr['shortlyAboutMe'], $paramsArr['vkontakte'], $paramsArr['odnoklassniki'], $paramsArr['facebook'], $paramsArr['twitter'], $paramsArr['lic'], $paramsArr['last_act'], $paramsArr['reg_date'], $paramsArr['favoritePropertiesId'], $paramsArr['id']) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($res === 0)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE users SET typeTenant=".$paramsArr['typeTenant'].", typeOwner=".$paramsArr['typeOwner'].", name=".$paramsArr['name'].", secondName=".$paramsArr['secondName'].", surname=".$paramsArr['surname'].", sex=".$paramsArr['sex'].", nationality=".$paramsArr['nationality'].", birthday=".$paramsArr['birthday'].", login=".$paramsArr['login'].", password=".$paramsArr['password'].", telephon=".$paramsArr['telephon'].", emailReg=".$paramsArr['emailReg'].", email=".$paramsArr['email'].", currentStatusEducation=".$paramsArr['currentStatusEducation'].", almamater=".$paramsArr['almamater'].", speciality=".$paramsArr['speciality'].", kurs=".$paramsArr['kurs'].", ochnoZaochno=".$paramsArr['ochnoZaochno'].", yearOfEnd=".$paramsArr['yearOfEnd'].", statusWork=".$paramsArr['statusWork'].", placeOfWork=".$paramsArr['placeOfWork'].", workPosition=".$paramsArr['workPosition'].", regionOfBorn=".$paramsArr['regionOfBorn'].", cityOfBorn=".$paramsArr['cityOfBorn'].", shortlyAboutMe=".$paramsArr['shortlyAboutMe'].", vkontakte=".$paramsArr['vkontakte'].", odnoklassniki=".$paramsArr['odnoklassniki'].", facebook=".$paramsArr['facebook'].", twitter=".$paramsArr['twitter'].", lic=".$paramsArr['lic'].", last_act=".$paramsArr['last_act'].", reg_date=".$paramsArr['reg_date'].", favoritePropertiesId=".$paramsArr['favoritePropertiesId']." WHERE id=".$paramsArr['id']."'. id логгера: DBconnect::updateUserCharacteristic():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Сохраняет в БД новое значение одного из статусов пользователя (typeTenant или typeOwner) - TRUE или FALSE
     *
     * @param $userId идентификатор пользователя, чей статус меняем
     * @param $type какой именно статус (тип) меняем: "typeTenant" или "typeOwner"
     * @param $value на какое значение меняем: "TRUE" или "FALSE"
     * @return bool возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
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

    // Изменяет данные об объекте недвижимости в БД
    // Возвращает TRUE в случае успеха и FALSE в случае неудачи
    public static function updatePropertyCharacteristic($paramsArr) {

        // Проверка входящих параметров
        if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

        // Подготовка данных к записи в БД
        $paramsArr = DBconnect::conversionPropertyCharacteristicFromViewToDB($paramsArr);

        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE property SET userId=?, typeOfObject=?, dateOfEntry=?, termOfLease=?, dateOfCheckOut=?, amountOfRooms=?, adjacentRooms=?, amountOfAdjacentRooms=?, typeOfBathrooms=?, typeOfBalcony=?, balconyGlazed=?, roomSpace=?, totalArea=?, livingSpace=?, kitchenSpace=?, floor=?, totalAmountFloor=?, numberOfFloor=?, concierge=?, intercom=?, parking=?, city=?, district=?, coordX=?, coordY=?, address=?, apartmentNumber=?, subwayStation=?, distanceToMetroStation=?, currency=?, costOfRenting=?, realCostOfRenting=?, utilities=?, costInSummer=?, costInWinter=?, electricPower=?, bail=?, bailCost=?, prepayment=?, compensationMoney=?, compensationPercent=?, repair=?, furnish=?, windows=?, internet=?, telephoneLine=?, cableTV=?, furnitureInLivingArea=?, furnitureInLivingAreaExtra=?, furnitureInKitchen=?, furnitureInKitchenExtra=?, appliances=?, appliancesExtra=?, sexOfTenant=?, relations=?, children=?, animals=?, contactTelephonNumber=?, timeForRingBegin=?, timeForRingEnd=?, checking=?, responsibility=?, comment=?, last_act=?, reg_date=?, status=?, earliestDate=?, earliestTimeHours=?, earliestTimeMinutes=?, adminComment=?, completeness=? WHERE id=?") === FALSE)
            OR ($stmt->bind_param("sssssssssssddddiiissssssssssisddsddssdsddssssssssssssssssssssssiissssssi", $paramsArr['userId'], $paramsArr['typeOfObject'], $paramsArr['dateOfEntry'], $paramsArr['termOfLease'], $paramsArr['dateOfCheckOut'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['amountOfAdjacentRooms'], $paramsArr['typeOfBathrooms'], $paramsArr['typeOfBalcony'], $paramsArr['balconyGlazed'], $paramsArr['roomSpace'], $paramsArr['totalArea'], $paramsArr['livingSpace'], $paramsArr['kitchenSpace'], $paramsArr['floor'], $paramsArr['totalAmountFloor'], $paramsArr['numberOfFloor'], $paramsArr['concierge'], $paramsArr['intercom'], $paramsArr['parking'], $paramsArr['city'], $paramsArr['district'], $paramsArr['coordX'], $paramsArr['coordY'], $paramsArr['address'], $paramsArr['apartmentNumber'], $paramsArr['subwayStation'], $paramsArr['distanceToMetroStation'], $paramsArr['currency'], $paramsArr['costOfRenting'], $paramsArr['realCostOfRenting'], $paramsArr['utilities'], $paramsArr['costInSummer'], $paramsArr['costInWinter'], $paramsArr['electricPower'], $paramsArr['bail'], $paramsArr['bailCost'], $paramsArr['prepayment'], $paramsArr['compensationMoney'], $paramsArr['compensationPercent'], $paramsArr['repair'], $paramsArr['furnish'], $paramsArr['windows'], $paramsArr['internet'], $paramsArr['telephoneLine'], $paramsArr['cableTV'], $paramsArr['furnitureInLivingArea'], $paramsArr['furnitureInLivingAreaExtra'], $paramsArr['furnitureInKitchen'], $paramsArr['furnitureInKitchenExtra'], $paramsArr['appliances'], $paramsArr['appliancesExtra'], $paramsArr['sexOfTenant'], $paramsArr['relations'], $paramsArr['children'], $paramsArr['animals'], $paramsArr['contactTelephonNumber'], $paramsArr['timeForRingBegin'], $paramsArr['timeForRingEnd'], $paramsArr['checking'], $paramsArr['responsibility'], $paramsArr['comment'], $paramsArr['last_act'], $paramsArr['reg_date'], $paramsArr['status'], $paramsArr['earliestDate'], $paramsArr['earliestTimeHours'], $paramsArr['earliestTimeMinutes'], $paramsArr['adminComment'], $paramsArr['completeness'], $paramsArr['id']) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($res === 0)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE property SET userId=".$paramsArr['userId'].", typeOfObject=".$paramsArr['typeOfObject'].", dateOfEntry=".$paramsArr['dateOfEntry'].", termOfLease=".$paramsArr['termOfLease'].", dateOfCheckOut=".$paramsArr['dateOfCheckOut'].", amountOfRooms=".$paramsArr['amountOfRooms'].", adjacentRooms=".$paramsArr['adjacentRooms'].", amountOfAdjacentRooms=".$paramsArr['amountOfAdjacentRooms'].", typeOfBathrooms=".$paramsArr['typeOfBathrooms'].", typeOfBalcony=".$paramsArr['typeOfBalcony'].", balconyGlazed=".$paramsArr['balconyGlazed'].", roomSpace=".$paramsArr['roomSpace'].", totalArea=".$paramsArr['totalArea'].", livingSpace=".$paramsArr['livingSpace'].", kitchenSpace=".$paramsArr['kitchenSpace'].", floor=".$paramsArr['floor'].", totalAmountFloor=".$paramsArr['totalAmountFloor'].", numberOfFloor=".$paramsArr['numberOfFloor'].", concierge=".$paramsArr['concierge'].", intercom=".$paramsArr['intercom'].", parking=".$paramsArr['parking'].", city=".$paramsArr['city'].", district=".$paramsArr['district'].", coordX=".$paramsArr['coordX'].", coordY=".$paramsArr['coordY'].", address=".$paramsArr['address'].", apartmentNumber=".$paramsArr['apartmentNumber'].", subwayStation=".$paramsArr['subwayStation'].", distanceToMetroStation=".$paramsArr['distanceToMetroStation'].", currency=".$paramsArr['currency'].", costOfRenting=".$paramsArr['costOfRenting'].", realCostOfRenting=".$paramsArr['realCostOfRenting'].", utilities=".$paramsArr['utilities'].", costInSummer=".$paramsArr['costInSummer'].", costInWinter=".$paramsArr['costInWinter'].", electricPower=".$paramsArr['electricPower'].", bail=".$paramsArr['bail'].", bailCost=".$paramsArr['bailCost'].", prepayment=".$paramsArr['prepayment'].", compensationMoney=".$paramsArr['compensationMoney'].", compensationPercent=".$paramsArr['compensationPercent'].", repair=".$paramsArr['repair'].", furnish=".$paramsArr['furnish'].", windows=".$paramsArr['windows'].", internet=".$paramsArr['internet'].", telephoneLine=".$paramsArr['telephoneLine'].", cableTV=".$paramsArr['cableTV'].", furnitureInLivingArea=".$paramsArr['furnitureInLivingArea'].", furnitureInLivingAreaExtra=".$paramsArr['furnitureInLivingAreaExtra'].", furnitureInKitchen=".$paramsArr['furnitureInKitchen'].", furnitureInKitchenExtra=".$paramsArr['furnitureInKitchenExtra'].", appliances=".$paramsArr['appliances'].", appliancesExtra=".$paramsArr['appliancesExtra'].", sexOfTenant=".$paramsArr['sexOfTenant'].", relations=".$paramsArr['relations'].", children=".$paramsArr['children'].", animals=".$paramsArr['animals'].", contactTelephonNumber=".$paramsArr['contactTelephonNumber'].", timeForRingBegin=".$paramsArr['timeForRingBegin'].", timeForRingEnd=".$paramsArr['timeForRingEnd'].", checking=".$paramsArr['checking'].", responsibility=".$paramsArr['responsibility'].", comment=".$paramsArr['comment'].", last_act=".$paramsArr['last_act'].", reg_date=".$paramsArr['reg_date'].", status=".$paramsArr['status'].", earliestDate=".$paramsArr['earliestDate'].", earliestTimeHours=".$paramsArr['earliestTimeHours'].", earliestTimeMinutes=".$paramsArr['earliestTimeMinutes'].", adminComment=".$paramsArr['adminComment'].", completeness=".$paramsArr['completeness']." WHERE id=".$paramsArr['id']."'. id логгера: DBconnect::updatePropertyCharacteristic():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Обновляет параметры поискового запроса в БД
     *
     * @param array $paramsArr ассоциативный массив параметров поискового запроса
     * @return bool TRUE в случае успеха и FALSE в случае неудачи
     */
    public static function updateSearchRequestForUser($paramsArr) {

        // Проверка входящих параметров
        if (!isset($paramsArr) || !is_array($paramsArr)) return FALSE;

        // Подготовка данных к записи в БД
        $paramsArr['amountOfRooms'] = serialize($paramsArr['amountOfRooms']);
        $paramsArr['district'] = serialize($paramsArr['district']);

        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE searchRequests SET userId=?, typeOfObject=?, amountOfRooms=?, adjacentRooms=?, floor=?, minCost=?, maxCost=?, pledge=?, prepayment=?, district=?, withWho=?, linksToFriends=?, children=?, howManyChildren=?, animals=?, howManyAnimals=?, termOfLease=?, additionalDescriptionOfSearch=?, regDate=?, needEmail=?, needSMS=? WHERE userId=?") === FALSE)
            OR ($stmt->bind_param("issssiiissssssssssiiii", $paramsArr['userId'], $paramsArr['typeOfObject'], $paramsArr['amountOfRooms'], $paramsArr['adjacentRooms'], $paramsArr['floor'], $paramsArr['minCost'], $paramsArr['maxCost'], $paramsArr['pledge'], $paramsArr['prepayment'], $paramsArr['district'], $paramsArr['withWho'], $paramsArr['linksToFriends'], $paramsArr['children'], $paramsArr['howManyChildren'], $paramsArr['animals'], $paramsArr['howManyAnimals'], $paramsArr['termOfLease'], $paramsArr['additionalDescriptionOfSearch'], $paramsArr['regDate'], $paramsArr['needEmail'], $paramsArr['needSMS'], $paramsArr['userId']) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($res === 0)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE searchRequests SET userId=".$paramsArr['userId'].", typeOfObject=".$paramsArr['typeOfObject'].", amountOfRooms=".$paramsArr['amountOfRooms'].", adjacentRooms=".$paramsArr['adjacentRooms'].", floor=".$paramsArr['floor'].", minCost=".$paramsArr['minCost'].", maxCost=".$paramsArr['maxCost'].", pledge=".$paramsArr['pledge'].", prepayment=".$paramsArr['prepayment'].", district=".$paramsArr['district'].", withWho=".$paramsArr['withWho'].", linksToFriends=".$paramsArr['linksToFriends'].", children=".$paramsArr['children'].", howManyChildren=".$paramsArr['howManyChildren'].", animals=".$paramsArr['animals'].", howManyAnimals=".$paramsArr['howManyAnimals'].", termOfLease=".$paramsArr['termOfLease'].", additionalDescriptionOfSearch=".$paramsArr['additionalDescriptionOfSearch'].", regDate=".$paramsArr['regDate'].", needEmail=".$paramsArr['needEmail'].", needSMS=".$paramsArr['needSMS']." WHERE userId=".$paramsArr['userId']."'. id логгера: DBconnect::updateSearchRequestForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Изменяет целевой объект недвижимости у запросов на просмотр
     * Используется при объединении 2-х объявлений в одно
     *
     * @param $propertyIdOld исходный id объекта недвижимости
     * @param $propertyIdNew новый id объекта недвижимости
     * @return bool возвращает TRUE в случае успеха и FALSE в случае неудачи
     */
    public static function updateRequestToViewForPropertyId($propertyIdOld, $propertyIdNew) {

        // Валидация входящих данных
        if (!isset($propertyIdOld) || !is_int($propertyIdOld) || !isset($propertyIdNew) || !is_int($propertyIdNew)) return FALSE;

        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("UPDATE requestToView SET propertyId = ? WHERE propertyId = ?") === FALSE)
            OR ($stmt->bind_param("ii", $propertyIdNew, $propertyIdOld) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'UPDATE requestToView SET propertyId = ".$propertyIdNew." WHERE propertyId = ".$propertyIdOld."'. id логгера: DBconnect::updateRequestToViewForPropertyId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
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

	// Удаляет описание (характеристику) объекта недвижимости
	// Возвращает TRUE в случае успеха и FALSE в случае неудачи
	public static function deletePropertyCharacteristicForId($propertyId) {

		// Валидация входный данных
		if (!isset($propertyId) || !is_int($propertyId)) return FALSE;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("DELETE FROM property WHERE id = ?") === FALSE)
			OR ($stmt->bind_param("i", $propertyId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->affected_rows) === -1)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM property WHERE id = " . $propertyId . "'. id логгера: DBconnect::deletePropertyCharacteristicForId():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
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

    // Удаляет все фотографии объекта недвижимости
    // Возвращает TRUE в случае успеха и FALSE в случае неудачи
    public static function deletePhotosForProperty($propertyId) {

        // Валидация входный данных
        if (!isset($propertyId) || $propertyId == "") return FALSE;

        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("DELETE FROM propertyFotos WHERE propertyId = ?") === FALSE)
            OR ($stmt->bind_param("i", $propertyId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM propertyFotos WHERE propertyId = " . $propertyId . "'. id логгера: DBconnect::deletePhotosForProperty():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
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

    // Удаляет описание (характеристику) объекта недвижимости из архивной таблицы
    // Возвращает TRUE в случае успеха и FALSE в случае неудачи
    public static function deletePropertyFromArchive($propertyId) {

        // Валидация входный данных
        if (!isset($propertyId) || !is_int($propertyId)) return FALSE;

        $stmt = DBconnect::get()->stmt_init();
        if (($stmt->prepare("DELETE FROM archiveAdverts WHERE id = ?") === FALSE)
            OR ($stmt->bind_param("i", $propertyId) === FALSE)
            OR ($stmt->execute() === FALSE)
            OR (($res = $stmt->affected_rows) === -1)
            OR ($stmt->close() === FALSE)
        ) {
            Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'DELETE FROM archiveAdverts WHERE id = " . $propertyId . "'. id логгера: DBconnect::deletePropertyFromArchive():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
            return FALSE;
        }

        return TRUE;
    }

	// Возвращает число = кол-ву всех непрочитанных уведомлений пользователя
	public static function countUnreadMessagesForUser($userId) {

		// Валидация входных параметров
		if (!isset($userId) || !is_int($userId)) return 0;

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT COUNT(*) FROM messagesNewProperty WHERE userId = ? AND isReaded = 'не прочитано'") === FALSE)
			OR ($stmt->bind_param("i", $userId) === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_row()) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT COUNT(*) FROM messagesNewProperty WHERE userId = '" . $userId . "' AND isReaded = 'не прочитано''. id логгера: DBconnect::countUnreadMessagesForUser():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return 0;
		}

		return $res[0];
	}

	// Возвращает количество всех объявлений в БД со статусом "опубликовано"
	public static function countAllPublishedProperties() {

		$stmt = DBconnect::get()->stmt_init();
		if (($stmt->prepare("SELECT COUNT(*) FROM property WHERE status = 'опубликовано'") === FALSE)
			OR ($stmt->execute() === FALSE)
			OR (($res = $stmt->get_result()) === FALSE)
			OR (($res = $res->fetch_row()) === FALSE)
			OR ($stmt->close() === FALSE)
		) {
			Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT COUNT(*) FROM property WHERE status = 'опубликовано''. id логгера: DBconnect::countAllPublishedProperties():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
			return "";
		}

		return $res[0];
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из пользователей. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - идентификатор пользователя, либо массив идентификаторов пользователей, по которым нужно получить данные
	// ВНИМАНИЕ: массивы могут быть расположены не в том же порядке, в каком идентификаторы располагались во входном массиве
	// TODO: переделать, переназвать
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

    /**
     * Преобразование данных о пользователе из формата хранения в БД в формат, с которым работают php скрипты
     *
     * @param array $user ассоциативный массив с параметрами пользователя
     * @return array исходный массив с преобразованными параметрами. Если на входе мусор, возвращает пустой массив
     */
    public static function conversionUserCharacteristicFromDBToView($user) {

        if (!isset($user) || !is_array($user)) return array();

        if (isset($user['typeTenant'])) {
            if ($user['typeTenant'] == "TRUE") {
                $user['typeTenant'] = TRUE;
            } elseif ($user['typeTenant'] == "FALSE") {
                $user['typeTenant'] = FALSE;
            } else {
                $user['typeTenant'] = FALSE;
            }
        }

        if (isset($user['typeOwner'])) {
            if ($user['typeOwner'] == "TRUE") {
                $user['typeOwner'] = TRUE;
            } elseif ($user['typeOwner'] == "FALSE") {
                $user['typeOwner'] = FALSE;
            } else {
                $user['typeOwner'] = FALSE;
            }
        }

        if (isset($user['birthday'])) $user['birthday'] = GlobFunc::dateFromDBToView($user['birthday']);
        if (isset($user['favoritePropertiesId'])) $user['favoritePropertiesId'] = unserialize($user['favoritePropertiesId']);

        return $user;
    }

	/**
	 * Преобразование данных об объекте недвижимости из формата хранения в БД в формат, с которым работают php скрипты
	 *
	 * @param array $property ассоциативный массив с параметрами объекта недвижимости
	 * @return array исходный массив с преобразованными параметрами. Если на входе мусор, возвращает пустой массив
	 */
	public static function conversionPropertyCharacteristicFromDBToView($property) {

		if (!isset($property) || !is_array($property)) return array();

		$property['dateOfEntry'] = GlobFunc::dateFromDBToView($property['dateOfEntry']);
		$property['dateOfCheckOut'] = GlobFunc::dateFromDBToView($property['dateOfCheckOut']);
		if ($property['roomSpace'] == 0) $property['roomSpace'] = "";
		if ($property['totalArea'] == 0) $property['totalArea'] = "";
		if ($property['livingSpace'] == 0) $property['livingSpace'] = "";
		if ($property['kitchenSpace'] == 0) $property['kitchenSpace'] = "";
		if ($property['floor'] == 0) $property['floor'] = "";
		if ($property['totalAmountFloor'] == 0) $property['totalAmountFloor'] = "";
		if ($property['numberOfFloor'] == 0) $property['numberOfFloor'] = "";
		if ($property['distanceToMetroStation'] == 0) $property['distanceToMetroStation'] = "";
		if ($property['costOfRenting'] == 0) $property['costOfRenting'] = "";
		if ($property['costInSummer'] == 0) $property['costInSummer'] = "";
		if ($property['costInWinter'] == 0) $property['costInWinter'] = "";
		if ($property['bailCost'] == 0) $property['bailCost'] = "";
		if ($property['compensationMoney'] == 0) $property['compensationMoney'] = "";
		if ($property['compensationPercent'] == 0) $property['compensationPercent'] = "";
		$property['furnitureInLivingArea'] = unserialize($property['furnitureInLivingArea']);
		$property['furnitureInKitchen'] = unserialize($property['furnitureInKitchen']);
		$property['appliances'] = unserialize($property['appliances']);
		$property['sexOfTenant'] = unserialize($property['sexOfTenant']);
		$property['relations'] = unserialize($property['relations']);
		$property['earliestDate'] = GlobFunc::dateFromDBToView($property['earliestDate']);

		return $property;
	}

    /**
     * Преобразование данных о поисковом запросе пользователя из формата хранения в БД в формат, с которым работают php скрипты
     *
     * @param array $searchRequest ассоциативный массив с параметрами поискового запроса
     * @return array исходный массив с преобразованными параметрами. Если на входе мусор, возвращает пустой массив
     */
    public static function conversionSearchRequestFromDBToView($searchRequest) {

        if (!isset($searchRequest) || !is_array($searchRequest)) return array();

        $searchRequest['amountOfRooms'] = unserialize($searchRequest['amountOfRooms']);
        $searchRequest['district'] = unserialize($searchRequest['district']);
        if ($searchRequest['minCost'] == 0) $searchRequest['minCost'] = "";
        if ($searchRequest['maxCost'] == 0) $searchRequest['maxCost'] = "";
        if ($searchRequest['pledge'] == 0) $searchRequest['pledge'] = "";

        return $searchRequest;
    }

    /**
     * Преобразование данных о пользователе из формата, с которым работают php скрипты в формат хранения в БД
     *
     * @param array $user ассоциативный массив с параметрами характеристики польхователя
     * @return array исходный массив с преобразованными параметрами. Если на входе мусор, возвращает пустой массив
     */
    public static function conversionUserCharacteristicFromViewToDB($user) {

        if (!isset($user) || !is_array($user)) return array();

        if ($user['typeTenant'] === TRUE) {
            $typeTenant = "TRUE";
        } elseif ($user['typeTenant'] === FALSE) {
            $typeTenant = "FALSE";
        } else {
            $typeTenant = "FALSE";
        }

        if ($user['typeOwner'] === TRUE) {
            $typeOwner = "TRUE";
        } elseif ($user['typeOwner'] === FALSE) {
            $typeOwner = "FALSE";
        } else {
            $typeOwner = "FALSE";
        }

        $user['birthday'] = GlobFunc::dateFromViewToDB($user['birthday']);
        $user['favoritePropertiesId'] = serialize($user['favoritePropertiesId']);

        return $user;
    }

	/**
	 * Преобразование данных об объекте недвижимости из формата, с которым работают php скрипты в формат хранения в БД
	 *
	 * @param array $property ассоциативный массив с параметрами объекта недвижимости
	 * @return array исходный массив с преобразованными параметрами. Если на входе мусор, возвращает пустой массив
	 */
	public static function conversionPropertyCharacteristicFromViewToDB($property) {

		if (!isset($property) || !is_array($property)) return array();

		$property['dateOfEntry'] = GlobFunc::dateFromViewToDB($property['dateOfEntry']);
		$property['dateOfCheckOut'] = GlobFunc::dateFromViewToDB($property['dateOfCheckOut']);
		if ($property['roomSpace'] == "") $property['roomSpace'] = 0.00;
		if ($property['totalArea'] == "") $property['totalArea'] = 0.00;
		if ($property['livingSpace'] == "") $property['livingSpace'] = 0.00;
		if ($property['kitchenSpace'] == "") $property['kitchenSpace'] = 0.00;
		if ($property['floor'] == "") $property['floor'] = 0;
		if ($property['totalAmountFloor'] == "") $property['totalAmountFloor'] = 0;
		if ($property['numberOfFloor'] == "") $property['numberOfFloor'] = 0;
		if ($property['distanceToMetroStation'] == "") $property['distanceToMetroStation'] = 0;
		if ($property['costOfRenting'] == "") $property['costOfRenting'] = 0;
		if ($property['costInSummer'] == "") $property['costInSummer'] = 0;
		if ($property['costInWinter'] == "") $property['costInWinter'] = 0;
		if ($property['bailCost'] == "") $property['bailCost'] = 0;
		if ($property['compensationMoney'] == "") $property['compensationMoney'] = 0.00;
		if ($property['compensationPercent'] == "") $property['compensationPercent'] = 0.00;
		$property['furnitureInLivingArea'] = serialize($property['furnitureInLivingArea']);
		$property['furnitureInKitchen'] = serialize($property['furnitureInKitchen']);
		$property['appliances'] = serialize($property['appliances']);
		$property['sexOfTenant'] = serialize($property['sexOfTenant']);
		$property['relations'] = serialize($property['relations']);
		$property['earliestDate'] = GlobFunc::dateFromViewToDB($property['earliestDate']);

		return $property;
	}

	// Конструктор не используется (но чтобы его нельзя было вызвать снаружи защищен модификатором private), так как он возвращает объект класса DBconnect, а мне в переменной $connect нужен объект класса mysqli
	private function __construct() {
	}
}
