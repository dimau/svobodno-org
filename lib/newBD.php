<?php
/**
 * Формирует необходимую для работы сайта базу данных со всей структурой и таблицами
 * При изменении структуры таблиц в этом файле или в БД, не забудь соответствующим образом изменить проверку валидности введенных пользователем данных на JS и на PHP, а также запрос на сохранение данных в БД при регистрации и другие запросы к БД
 */
include_once 'connect.php'; //подключаемся к БД

// Создаем таблицу для хранения информации о ПОЛЬЗОВАТЕЛЯХ
$rez = mysql_query("CREATE TABLE users (
        id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        typeTenant VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Равен строке true, если пользователь в данный момент ищет недвижимость (является потенциальным арендатором), в том числе, обязательно имеет поисковый запрос',
        typeOwner VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Равен строке true, если пользователь указал хотя бы 1 объявление по сдаче в аренду недвижимости (является собственником)(не имеет значение - опубликованное или нет)',
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Имя пользователя',
        secondName VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Отчество пользователя',
        surname VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Фамилия пользователя',
        sex VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Пол пользователя',
        nationality VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        birthday DATE,
        login VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        password VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        telephon VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        emailReg VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Электронный адрес, указанный пользователем при регистрации',
        email VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Текущий электронный адрес пользователя',
        currentStatusEducation VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        almamater VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название учебного заведения, в котором пользователь учится сейчас или последнее из тех, что он закончил',
        speciality VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
        kurs VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci,
        ochnoZaochno VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        yearOfEnd VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Год окончания учебного заведения, указанного выше в форме',
        notWorkCheckbox VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Если в поле что-то записано, значит пользователь сейчас не работает, если поле пустое, значит пользователь указал, что он работает',
        placeOfWork VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
        workPosition VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
        regionOfBorn VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Регион РФ, в котором пользователь родился. Это важно, чтобы, например, он смог найти собственников-земляков и легче получить недвижимость в аренду',
        cityOfBorn VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        shortlyAboutMe TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        vkontakte VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
        odnoklassniki VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
        facebook VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
        twitter VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
        lic VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci,
        user_hash VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Поле хранит id последней сессии пользователя. Это нужно для безопасности: если значение идентификатора сессии, присланное браузером, не совпадает с этим значением, значит его сессия устарела и требует обновления ',
        last_act INT(11) COMMENT 'Время последней активности пользователя в секундах после 1970 года - формат timestamp',
        reg_date INT(11) COMMENT 'Время регистрации пользователя с точностью до секунд в формате timestamp'
)");

echo "Статус создания таблицы users: " . $rez . "\n";

// Создаем таблицу для временного хранения информации о ЗАГРУЖЕННЫХ при регистрации ФОТОГРАФИЯХ пользователей
$rez = mysql_query("CREATE TABLE tempFotos (
        id VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Содержит идентификатор фотографии, он же имя файла на сервере (без расширения)',
        fileUploadId VARCHAR(7) NOT NULL COMMENT 'фактически это такой идентификатор сессии заполнения формы регистрации. Позволяет добиться того, чтобы при перезагрузке формы (в случае, например, ошибок и пустых полей, незаполненных пользователем) данные о фотографиях не потерялись',
        filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
        extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
        filesizeMb FLOAT(1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой'
)");

echo "Статус создания таблицы tempFotos: " . $rez . "\n";

// Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ пользователей (только личные)
$rez = mysql_query("CREATE TABLE userFotos (
        id VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Содержит идентификатор фотографии, он же имя файла на сервере (без расширения)',
        filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
        extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
        filesizeMb FLOAT(1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
        userId INT(11) COMMENT 'Идентификатор пользователя, которому соответствует данная фотография'
)");
// userId - содержит идентификатор пользователя, к которому относится фотография

echo "Статус создания таблицы userFotos: " . $rez . "\n";

// Создаем таблицу для хранения информации о ПОИСКОВЫХ ЗАПРОСАХ пользователей
$rez = mysql_query("CREATE TABLE searchRequests (
        userId INT(11) NOT NULL PRIMARY KEY COMMENT 'Идентификатор пользователя, которому принадлежит данный поисковый запрос',
        typeOfObject VARCHAR(20) COMMENT 'Тип объекта, который ищет пользователь',
        amountOfRooms BLOB,
        adjacentRooms VARCHAR(20),
        floor VARCHAR(20),
        furniture VARCHAR(20),
        minCost INT NOT NULL,
        maxCost INT NOT NULL,
        pledge INT NOT NULL,
        district BLOB COMMENT 'Список районов, в которых пользователь ищет недвижимость. Представляет собой сериализованный массив',
        withWho VARCHAR(20),
        linksToFriends TEXT,
        children VARCHAR(20),
        howManyChildren TEXT,
        animals VARCHAR(20),
        howManyAnimals TEXT,
        period VARCHAR(80),
        additionalDescriptionOfSearch TEXT
)");
// в поле userId указывается идентификатор пользователя, к которому привязан данный поисковый запрос. Так как я считаю, что каждый пользователь может иметь только 1 поисковый запрос, то данное поле является ключом таблицы
// amountOfRooms храним в виде 001011, где каждый разряд соответствует количеству комнат (1 разряд - 1 комната, 2-ой разряд - 2 комнаты и т.д.), а 0 - не отмечено, 1 - отмечено
// district храним в виде последовательности идентификаторов районов, выбранных пользователем, разделенных знаком ","

echo "Статус создания таблицы searchRequests: " . $rez . "\n";

// Создаем таблицу для хранения информации об ОБЪЕКТАХ НЕДВИЖИМОСТИ пользователей
$rez = mysql_query("CREATE TABLE property (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор объекта недвижимости или объявления - можно его называть и так, и так',
        userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя (собственника), который указал данное объявление в системе и который сдает данный объект',
        typeOfObject VARCHAR(20),
        dateOfEntry DATE,
        termOfLease VARCHAR(20),
        dateOfCheckOut DATE,
        amountOfRooms VARCHAR(20),
        adjacentRooms VARCHAR(20),
        amountOfAdjacentRooms VARCHAR(20),
        typeOfBathrooms VARCHAR(20),
        typeOfBalcony VARCHAR(20),
        balconyGlazed VARCHAR(20),
        roomSpace FLOAT(2),
        totalArea FLOAT(2),
        livingSpace FLOAT(2),
        kitchenSpace FLOAT(2),
        floor INT,
        totalAmountFloor INT,
        numberOfFloor INT,
        concierge VARCHAR(20),
        intercom VARCHAR(20),
        parking VARCHAR(20),
        city VARCHAR(50),
        district VARCHAR(50),
        coordX VARCHAR(30),
        coordY VARCHAR(30),
        address VARCHAR(60),
        apartmentNumber VARCHAR(20),
        subwayStation VARCHAR(50),
        distanceToMetroStation INT,
        currency VARCHAR(20),
        costOfRenting FLOAT(2),
        utilities VARCHAR(20),
        costInSummer FLOAT(2),
        costInWinter FLOAT(2),
        electricPower VARCHAR(20),
        bail VARCHAR(20),
        bailCost FLOAT(2),
        prepayment VARCHAR(20),
        compensationMoney FLOAT(2),
        compensationPercent FLOAT(2),
        repair VARCHAR(50),
        furnish VARCHAR(50),
        windows VARCHAR(20),
        internet VARCHAR(20),
        telephoneLine VARCHAR(20),
        cableTV VARCHAR(20),
        furnitureInLivingArea BLOB,
        furnitureInLivingAreaExtra VARCHAR(255),
        furnitureInKitchen BLOB,
        furnitureInKitchenExtra VARCHAR(255),
        appliances BLOB,
        appliancesExtra VARCHAR(255),
        sexOfTenant BLOB,
        relations BLOB,
        children VARCHAR(50),
        animals VARCHAR(50),
        contactTelephonNumber VARCHAR(20),
        timeForRingBegin VARCHAR(20),
        timeForRingEnd VARCHAR(20),
        checking VARCHAR(50),
        responsibility TEXT,
        comment TEXT,
        last_act INT(11) COMMENT 'Время последнего изменения объявления - будь-то время создания или время последнего редактирования. Используется для сортировки объявлений в разделе Мои объявления личного кабинета',
        reg_date INT(11) COMMENT 'Время создания объявления',
        status VARCHAR(20) DEFAULT  'не опубликовано' COMMENT 'Статус объявления: опубликовано или неопубликовано. Сразу после создания объявление становится неопубликованным'
)");
// userId - содержит идентификатор пользователя, к которому относится объект недвижимости (собственник)

echo "Статус создания таблицы property: " . $rez . "\n";

// Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ объектов недвижимости
$rez = mysql_query("CREATE TABLE propertyFotos (
        id VARCHAR(32) NOT NULL PRIMARY KEY,
        filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
        extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
        filesizeMb FLOAT(1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
        propertyId INT(11) COMMENT 'Идентификатор объекта недвижимости (или иначе объявления), к которому относится данная фотография'
)");
// propertyId - содержит идентификатор объявления недвижимости, к которому относится фотография

echo "Статус создания таблицы propertyFotos: " . $rez . "\n";

// Создаем таблицу для хранения информации о соотношении названия района его идентификатора и города нахождения
$rez = mysql_query("CREATE TABLE districts (
        keyOfDistricts INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Ключ для района. Данное поле используется для сортировки районов по порядку - первые по алфавиту заведены первыми в эту базу данных',
        id VARCHAR(20) NOT NULL,
        name VARCHAR(50) COMMENT 'Название района, которое отображается пользователю',
        city VARCHAR(50) COMMENT 'Город, в котором расположен данный район'
)");

echo "Статус создания таблицы districts: " . $rez . "\n";

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