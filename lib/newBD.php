<?php
/**
 * Формирует необходимую для работы сайта базу данных со всей структурой и таблицами
 * При изменении структуры таблиц в этом файле или в БД, не забудь соответствующим образом изменить проверку валидности введенных пользователем данных на JS и на PHP, а также запрос на сохранение данных в БД при регистрации и другие запросы к БД

 */

include_once 'connect.php'; //подключаемся к БД

// Создаем таблицу для хранения информации о ПОЛЬЗОВАТЕЛЯХ
$rez = mysql_query("CREATE TABLE users (id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, typeTenant BOOLEAN NOT NULL, typeOwner BOOLEAN NOT NULL, name VARCHAR(50) NOT NULL, secondName VARCHAR(50) NOT NULL, surname VARCHAR(50) NOT NULL, sex VARCHAR(20) NOT NULL, nationality VARCHAR(20) NOT NULL,                         birthday DATE NOT NULL,              login VARCHAR(50) NOT NULL, password VARCHAR(32) NOT NULL, telephon INT(10) NOT NULL, emailReg VARCHAR(50) NOT NULL, email VARCHAR(50) NOT NULL, fotoFilesId VARCHAR(255) NOT NULL, currentStatusEducation VARCHAR(20) NOT NULL, almamater VARCHAR(80) NOT NULL, speciality VARCHAR(80) NOT NULL, kurs VARCHAR(20) NOT NULL, ochnoZaochno VARCHAR(20) NOT NULL, yearOfEnd VARCHAR(20) NOT NULL, notWorkCheckbox BOOLEAN NOT NULL, placeOfWork VARCHAR(50) NOT NULL, workPosition VARCHAR(50) NOT NULL, regionOfBorn VARCHAR(50) NOT NULL, cityOfBorn VARCHAR(50) NOT NULL, shortlyAboutMe VARCHAR(255) NOT NULL, vkontakte VARCHAR(80) NOT NULL, odnoklassniki VARCHAR(80) NOT NULL, facebook VARCHAR(80) NOT NULL, twitter VARCHAR(80) NOT NULL, searchRequestId VARCHAR(32) NOT NULL, salt VARCHAR(3) NOT NULL, user_hash VARCHAR(32) NOT NULL, last_act INT(11) NOT NULL, reg_date INT(11) NOT NULL");

// Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ пользователей
$rez = mysql_query("CREATE TABLE userFotos (id VARCHAR(32) NOT NULL PRIMARY KEY, filename VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, filesizeMb FLOAT(1) NOT NULL)");

// Создаем таблицу для временного хранения информации о ЗАГРУЖЕННЫХ при регистрации ФОТОГРАФИЯХ пользователей
$rez = mysql_query("CREATE TABLE tempregfotos (id VARCHAR(32) NOT NULL PRIMARY KEY, fileUploadId VARCHAR(7) NOT NULL, filename VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, filesizeMb FLOAT(1) NOT NULL)");

// Создаем таблицу для хранения информации о ПОИСКОВЫХ ЗАПРОСАХ пользователей
$rez = mysql_query("CREATE TABLE searchRequests (id VARCHAR(32) NOT NULL PRIMARY KEY, typeOfObject VARCHAR(20) NOT NULL, amountOfRooms INT(6) NOT NULL, adjacentRooms VARCHAR(20) NOT NULL), floor VARCHAR(20) NOT NULL, withWithoutFurniture BOOLEAN NOT NULL, minCost INT(8) NOT NULL, maxCost INT(8) NOT NULL, pledge INT(8) NOT NULL, district VARCHAR(255) NOT NULL, withWho VARCHAR(20) NOT NULL, liksToFriends VARCHAR(255) NOT NULL, children VARCHAR(20) NOT NULL, howManyChildren VARCHAR(255) NOT NULL, animals VARCHAR(20) NOT NULL, howManyAnimals VARCHAR(255) NOT NULL, period VARCHAR(80) NOT NULL, additionalDescriptionOfSearch VARCHAR(255) NOT NULL, lic BOOLEAN NOT NULL");
// amountOfRooms храним в виде 001011, где каждый разряд соответствует количеству комнат (1 разряд - 1 комната, 2-ой разряд - 2 комнаты и т.д.), а 0 - не отмечено, 1 - отмечено
// district храним в виде последовательности идентификаторов районов, выбранных пользователем, разделенных знаком ","

echo $rez;

?>