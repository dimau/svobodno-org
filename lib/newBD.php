<?php
/**
 * Формирует необходимую для работы сайта базу данных со всей структурой и таблицами
 * При изменении структуры таблиц в этом файле или в БД, не забудь соответствующим образом изменить проверку валидности введенных пользователем данных на JS и на PHP, а также запрос на сохранение данных в БД при регистрации и другие запросы к БД

 */

include_once 'connect.php'; //подключаемся к БД

// Создаем таблицу для хранения информации о ПОЛЬЗОВАТЕЛЯХ
$rez = mysql_query("CREATE TABLE users (id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, typeTenant VARCHAR(5) NOT NULL COMMENT 'Равен строке true, если пользователь в данный момент ищет недвижимость (является потенциальным арендатором), в том числе, обязательно имеет поисковый запрос', typeOwner VARCHAR(5) NOT NULL, name VARCHAR(50) NOT NULL, secondName VARCHAR(50) NOT NULL, surname VARCHAR(50) NOT NULL, sex VARCHAR(20) NOT NULL, nationality VARCHAR(20) NOT NULL, birthday DATE NOT NULL, login VARCHAR(50) NOT NULL, password VARCHAR(32) NOT NULL, telephon VARCHAR(20) NOT NULL, emailReg VARCHAR(50), email VARCHAR(50), currentStatusEducation VARCHAR(20), almamater VARCHAR(100), speciality VARCHAR(100), kurs VARCHAR(30), ochnoZaochno VARCHAR(20), yearOfEnd VARCHAR(20), notWorkCheckbox VARCHAR(20), placeOfWork VARCHAR(100), workPosition VARCHAR(100), regionOfBorn VARCHAR(50), cityOfBorn VARCHAR(50), shortlyAboutMe TEXT, vkontakte VARCHAR(100), odnoklassniki VARCHAR(100), facebook VARCHAR(100), twitter VARCHAR(100), lic VARCHAR(5) NOT NULL, salt VARCHAR(3) NOT NULL, user_hash VARCHAR(32), last_act INT(11) NOT NULL, reg_date INT(11) NOT NULL)");

echo "Статус создания таблицы users: " . $rez . "\n";

// Создаем таблицу для временного хранения информации о ЗАГРУЖЕННЫХ при регистрации ФОТОГРАФИЯХ пользователей
$rez = mysql_query("CREATE TABLE tempFotos (id VARCHAR(32) NOT NULL PRIMARY KEY, fileUploadId VARCHAR(7) NOT NULL, filename VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, filesizeMb FLOAT(1) NOT NULL)");

echo "Статус создания таблицы tempFotos: " . $rez . "\n";

// Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ пользователей (только личные)
$rez = mysql_query("CREATE TABLE userFotos (id VARCHAR(32) NOT NULL PRIMARY KEY, filename VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, filesizeMb FLOAT(1) NOT NULL, userId INT(11) NOT NULL)");
// userId - содержит идентификатор пользователя, к которому относится фотография

echo "Статус создания таблицы userFotos: " . $rez . "\n";

// Создаем таблицу для хранения информации о ПОИСКОВЫХ ЗАПРОСАХ пользователей
$rez = mysql_query("CREATE TABLE searchRequests (userId INT(11) NOT NULL PRIMARY KEY, typeOfObject VARCHAR(20), amountOfRooms BLOB, adjacentRooms VARCHAR(20), floor VARCHAR(20), furniture VARCHAR(20), minCost INT NOT NULL, maxCost INT NOT NULL, pledge INT NOT NULL, district BLOB, withWho VARCHAR(20), linksToFriends TEXT, children VARCHAR(20), howManyChildren TEXT, animals VARCHAR(20), howManyAnimals TEXT, period VARCHAR(80), additionalDescriptionOfSearch TEXT)");
// в поле userId указывается идентификатор пользователя, к которому привязан данный поисковый запрос. Так как я считаю, что каждый пользователь может иметь только 1 поисковый запрос, то данное поле является ключом таблицы
// amountOfRooms храним в виде 001011, где каждый разряд соответствует количеству комнат (1 разряд - 1 комната, 2-ой разряд - 2 комнаты и т.д.), а 0 - не отмечено, 1 - отмечено
// district храним в виде последовательности идентификаторов районов, выбранных пользователем, разделенных знаком ","

echo "Статус создания таблицы searchRequests: " . $rez . "\n";

// Создаем таблицу для хранения информации о соотношении названия района его идентификатора и города нахождения
$rez = mysql_query("CREATE TABLE districts (id VARCHAR(20) NOT NULL PRIMARY KEY, name VARCHAR(50), city VARCHAR(50))");
echo "Статус создания таблицы districts: " . $rez . "\n";
// Записываем в таблицу с районами инфу о районах
// Записываем в таблицу с районами инфу о районах
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('1', 'Автовокзал (южный)', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('2', 'Академический', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('3', 'Ботанический', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('4', 'ВИЗ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('5', 'Вокзальный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('6', 'Втузгородок', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('7', 'Горный щит', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('8', 'Елизавет', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('9', 'ЖБИ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('10', 'Завокзальный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('11', 'Заречный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('12', 'Изоплит', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('13', 'Исток', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('14', 'Калиновский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('15', 'Кольцово', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('16', 'Компрессорный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('17', 'Лечебный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('18', 'Малый исток', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('19', 'Нижнеисетский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('20', 'Парковый', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('21', 'Пионерский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('22', 'Птицефабрика', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('23', 'Рудный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('24', 'Садовый', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('25', 'Северка', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('26', 'Семь ключей', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('27', 'Сибирский тракт', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('28', 'Синие камни', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('29', 'Совхозный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('30', 'Сортировка новая', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('31', 'Сортировка старая', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('32', 'Уктус', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('33', 'УНЦ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('34', 'Уралмаш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('35', 'Химмаш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('36', 'Центр', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('37', 'Чермет', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('38', 'Чусовское озеро', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('39', 'Шабровский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('40', 'Шарташ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('41', 'Шарташский рынок', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('42', 'Широкая речка', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('43', 'Шувакиш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('44', 'Эльмаш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('45', 'Юго-запад', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (id, name, city) VALUES ('46', 'За городом', 'Екатеринбург')");
echo "Статус записи инфы о районах в таблицу districts: ";
foreach ($rezDistricts as $value) {
    echo $value;
}


/**
 * Проверяем настройки PHP сервера
 *
 * ini_set ("session.use_trans_sid", true); вроде как PHP сам умеет устанавливать id сессии либо в куки, либо в строку запроса (http://www.phpfaq.ru/sessions)
 */
?>