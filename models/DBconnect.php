<?php
/* Статический класс для работы с БД (практически синглтон, содержащий единственный на весь скрипт объект соединения с Базой данных) */

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
			$propertyFotos = DBconnect::getPropertyFotosDataArr($propertyFullArr[$i]['id']);
			// Записываем полученный массив массивов с данными о фотографиях в специальный новый параметр массива $propertyFullArr
			$propertyFullArr[$i]['propertyFotos'] = $propertyFotos;
		}

		return $propertyFullArr;
	}

	// Функция возвращает массив ассоциированных массивов с данными о фотографиях объекта недвижимости
	// На входе - идентификатор объекта недвижимости, по которому нужно получить фотографии
	public static function getPropertyFotosDataArr($propertyId) {
		// Проверка входящих параметров
		if (!isset($propertyId) || intval($propertyId) == 0) return FALSE;

		// На всякий случай преобразуем propertyId в гарантированно целое число
		$propertyId = intval($propertyId);

		$res = DBconnect::get()->query("SELECT * FROM propertyFotos WHERE propertyId = '" . $propertyId . "'");
		if ((DBconnect::get()->errno)
			OR (($propertyFotosArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
		) {
			// Логируем ошибку
			//TODO: сделать логирование ошибки
			$propertyFotosArr = array();
		}

		return $propertyFotosArr;
	}

	// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одной из заявок. Если ничего не найдено или произошла ошибка, вернет пустой массив
	// На входе - идентификатор объекта недвижимости, либо массив идентификаторов объектов недвижимости, по которым нужно найти все заявки на просмотр
	public static function getAllRequestToViewForProperties($propertiesId) {

		// Проверка входящих параметров
		if (!isset($propertiesId)) return array();
		if (is_array($propertiesId) && count($propertiesId) == 0) return array();

		// Если нам на вход дали единичный идентификатор, то приведем его к виду массива
		if (!is_array($propertiesId)) $propertiesId = array($propertiesId);

		// Для надежности преобразование к целому типу членов массива и их проверка
		for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
			$propertiesId[$i] = intval($propertiesId[$i]);
			if ($propertiesId[$i] == 0) return array();	// Если преобразование дало 0, значит один из членов массива не является идентификатором объекта недвижимости - входные данные некорректны
		}

		// Соберем условие для получения данных из БД
		$strWHERE = " (";
		for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
			$strWHERE .= " propertyId = '" . $propertiesId[$i] . "'";
			if ($i < $s - 1) $strWHERE .= " OR";
		}
		$strWHERE .= " )";

		// Получаем данные из БД
		$res = DBconnect::get()->query("SELECT * FROM requestToView WHERE".$strWHERE." ORDER BY status DESC");
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
			if ($usersId[$i] == 0) return array();	// Если преобразование дало 0, значит один из членов массива не является идентификатором объекта недвижимости - входные данные некорректны
		}

		// Соберем условие для получения данных из БД
		$strWHERE = " (";
		for ($i = 0, $s = count($usersId); $i < $s; $i++) {
			$strWHERE .= " id = '" . $usersId[$i] . "'";
			if ($i < $s - 1) $strWHERE .= " OR";
		}
		$strWHERE .= " )";

		// Получаем данные из БД
		$res = DBconnect::get()->query("SELECT * FROM users WHERE".$strWHERE);
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
			if ($propertiesId[$i] == 0) return array();	// Если преобразование дало 0, значит один из членов массива не является идентификатором объекта недвижимости - входные данные некорректны
		}

		// Соберем условие для получения данных из БД
		$strWHERE = " (";
		for ($i = 0, $s = count($propertiesId); $i < $s; $i++) {
			$strWHERE .= " id = '" . $propertiesId[$i] . "'";
			if ($i < $s - 1) $strWHERE .= " OR";
		}
		$strWHERE .= " )";

		// Получаем данные из БД
		$res = DBconnect::get()->query("SELECT * FROM property WHERE".$strWHERE);
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


	// Конструктор не используется (но чтобы его нельзя было вызвать снаружи защищен модификатором private), так как он возвращает объект класса DBconnect, а мне в переменной $connect нужен объект класса mysqli
	private function __construct() {
	}

}
