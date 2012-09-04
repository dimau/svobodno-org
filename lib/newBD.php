<?php
/**
 * Формирует необходимую для работы сайта базу данных со всей структурой и таблицами
 * При изменении структуры таблиц в этом файле или в БД, не забудь соответствующим образом изменить проверку валидности введенных пользователем данных на JS и на PHP, а также запрос на сохранение данных в БД при регистрации и другие запросы к БД

 */

include_once 'connect.php'; //подключаемся к БД

// Создаем таблицу для хранения информации о ПОЛЬЗОВАТЕЛЯХ
$rez = mysql_query("CREATE TABLE users (id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, typeTenant VARCHAR(5) NOT NULL, typeOwner VARCHAR(5) NOT NULL, name VARCHAR(50) NOT NULL, secondName VARCHAR(50) NOT NULL, surname VARCHAR(50) NOT NULL, sex VARCHAR(20) NOT NULL, nationality VARCHAR(20) NOT NULL, birthday DATE NOT NULL, login VARCHAR(50) NOT NULL, password VARCHAR(32) NOT NULL, telephon VARCHAR(20) NOT NULL, emailReg VARCHAR(50), email VARCHAR(50), currentStatusEducation VARCHAR(20), almamater VARCHAR(100), speciality VARCHAR(100), kurs VARCHAR(30), ochnoZaochno VARCHAR(20), yearOfEnd VARCHAR(20), notWorkCheckbox VARCHAR(20), placeOfWork VARCHAR(100), workPosition VARCHAR(100), regionOfBorn VARCHAR(50), cityOfBorn VARCHAR(50), shortlyAboutMe TEXT, vkontakte VARCHAR(100), odnoklassniki VARCHAR(100), facebook VARCHAR(100), twitter VARCHAR(100), lic VARCHAR(5) NOT NULL, salt VARCHAR(3) NOT NULL, user_hash VARCHAR(32), last_act INT(11) NOT NULL, reg_date INT(11) NOT NULL)");

echo "Статус создания таблицы users: " . $rez;

// Создаем таблицу для временного хранения информации о ЗАГРУЖЕННЫХ при регистрации ФОТОГРАФИЯХ пользователей
$rez = mysql_query("CREATE TABLE tempFotos (id VARCHAR(32) NOT NULL PRIMARY KEY, fileUploadId VARCHAR(7) NOT NULL, filename VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, filesizeMb FLOAT(1) NOT NULL)");

echo "Статус создания таблицы tempFotos: " . $rez;

// Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ пользователей (только личные)
$rez = mysql_query("CREATE TABLE userFotos (id VARCHAR(32) NOT NULL PRIMARY KEY, filename VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, filesizeMb FLOAT(1) NOT NULL, userId INT(11) NOT NULL)");
// userId - содержит идентификатор пользователя, к которому относится фотография

echo "Статус создания таблицы userFotos: " . $rez;

// Создаем таблицу для хранения информации о ПОИСКОВЫХ ЗАПРОСАХ пользователей
$rez = mysql_query("CREATE TABLE searchRequests (userId INT(11) NOT NULL PRIMARY KEY, typeOfObject VARCHAR(20), amountOfRooms BLOB, adjacentRooms VARCHAR(20), floor VARCHAR(20), withWithoutFurniture VARCHAR(20), minCost INT NOT NULL, maxCost INT NOT NULL, pledge INT NOT NULL, district BLOB, withWho VARCHAR(20), linksToFriends TEXT, children VARCHAR(20), howManyChildren TEXT, animals VARCHAR(20), howManyAnimals TEXT, period VARCHAR(80), additionalDescriptionOfSearch TEXT)");
// в поле userId указывается идентификатор пользователя, к которому привязан данный поисковый запрос. Так как я считаю, что каждый пользователь может иметь только 1 поисковый запрос, то данное поле является ключом таблицы
// amountOfRooms храним в виде 001011, где каждый разряд соответствует количеству комнат (1 разряд - 1 комната, 2-ой разряд - 2 комнаты и т.д.), а 0 - не отмечено, 1 - отмечено
// district храним в виде последовательности идентификаторов районов, выбранных пользователем, разделенных знаком ","

echo "Статус создания таблицы searchRequests: " . $rez;

/**
 * Проверяем настройки PHP сервера
 *
 * ini_set ("session.use_trans_sid", true); вроде как PHP сам умеет устанавливать id сессии либо в куки, либо в строку запроса (http://www.phpfaq.ru/sessions)
 */
?>