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

echo "Статус создания таблицы userFotos: " . $rez . "\n";

// Создаем таблицу для хранения информации о ПОИСКОВЫХ ЗАПРОСАХ пользователей
$rez = mysql_query("CREATE TABLE searchRequests (
        userId INT(11) NOT NULL PRIMARY KEY COMMENT 'Идентификатор пользователя, которому принадлежит данный поисковый запрос. Так как я считаю, что каждый пользователь может иметь только 1 поисковый запрос, то данное поле является ключом таблицы',
        typeOfObject VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип объекта, который ищет пользователь',
        amountOfRooms BLOB,
        adjacentRooms VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        floor VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        furniture VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        minCost INT NOT NULL,
        maxCost INT NOT NULL,
        pledge INT NOT NULL,
        prepayment INT COMMENT 'Максимальная предоплата, которую готов внести арендатор, указана в месяцах',
        district BLOB COMMENT 'Список районов, в которых пользователь ищет недвижимость. Представляет собой сериализованный массив',
        withWho VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        linksToFriends TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        children VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        howManyChildren TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        animals VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        howManyAnimals TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        termOfLease VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        additionalDescriptionOfSearch TEXT CHARACTER SET utf8 COLLATE utf8_general_ci
)");

echo "Статус создания таблицы searchRequests: " . $rez . "\n";

// Создаем таблицу для хранения информации об ОБЪЕКТАХ НЕДВИЖИМОСТИ пользователей
$rez = mysql_query("CREATE TABLE property (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор объекта недвижимости или объявления - можно его называть и так, и так',
        userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя (собственника), который указал данное объявление в системе и который сдает данный объект',
        typeOfObject VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        dateOfEntry DATE,
        termOfLease VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        dateOfCheckOut DATE,
        amountOfRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        adjacentRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        amountOfAdjacentRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        typeOfBathrooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        typeOfBalcony VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        balconyGlazed VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        roomSpace FLOAT(2),
        totalArea FLOAT(2),
        livingSpace FLOAT(2),
        kitchenSpace FLOAT(2),
        floor INT,
        totalAmountFloor INT,
        numberOfFloor INT,
        concierge VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        intercom VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        parking VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        city VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        district VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        coordX VARCHAR(30),
        coordY VARCHAR(30),
        address VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci,
        apartmentNumber VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        subwayStation VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        distanceToMetroStation INT,
        currency VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        costOfRenting FLOAT(2),
        utilities VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        costInSummer FLOAT(2),
        costInWinter FLOAT(2),
        electricPower VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        bail VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        bailCost FLOAT(2),
        prepayment VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        compensationMoney FLOAT(2),
        compensationPercent FLOAT(2),
        repair VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        furnish VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        windows VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        internet VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        telephoneLine VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        cableTV VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
        furnitureInLivingArea BLOB,
        furnitureInLivingAreaExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
        furnitureInKitchen BLOB,
        furnitureInKitchenExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
        appliances BLOB,
        appliancesExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
        sexOfTenant BLOB,
        relations BLOB,
        children VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        animals VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        contactTelephonNumber VARCHAR(20),
        timeForRingBegin VARCHAR(20),
        timeForRingEnd VARCHAR(20),
        checking VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        responsibility TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        comment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        last_act INT(11) COMMENT 'Время последнего изменения объявления - будь-то время создания или время последнего редактирования. Используется для сортировки объявлений в разделе Мои объявления личного кабинета',
        reg_date INT(11) COMMENT 'Время создания объявления',
        status VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'не опубликовано' COMMENT 'Статус объявления: опубликовано или неопубликовано. Сразу после создания объявление становится неопубликованным'
)");

echo "Статус создания таблицы property: " . $rez . "\n";

// Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ объектов недвижимости
$rez = mysql_query("CREATE TABLE propertyFotos (
        id VARCHAR(32) NOT NULL PRIMARY KEY,
        filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
        extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
        filesizeMb FLOAT(1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
        propertyId INT(11) COMMENT 'Идентификатор объекта недвижимости (или иначе объявления), к которому относится данная фотография'
)");

echo "Статус создания таблицы propertyFotos: " . $rez . " \n ";

// Создаем таблицу для хранения списка районов каждого города присутствия сервиса
$rez = mysql_query("CREATE TABLE districts (
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название района, которое отображается пользователю',
        city VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Город, в котором расположен данный район'
)");

echo "Статус создания таблицы districts: " . $rez . " \n ";

// Записываем в таблицу с районами инфу о районах
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Автовокзал (южный)', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Академический', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Ботанический', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('ВИЗ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Вокзальный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Втузгородок', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Горный щит', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Елизавет', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('ЖБИ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Завокзальный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Заречный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Изоплит', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Исток', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Калиновский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Кольцово', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Компрессорный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Лечебный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Малый исток', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Нижнеисетский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Парковый', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Пионерский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Птицефабрика', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Рудный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Садовый', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Северка', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Семь ключей', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Сибирский тракт', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Синие камни', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Совхозный', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Сортировка новая', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Сортировка старая', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Уктус', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('УНЦ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Уралмаш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Химмаш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Центр', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Чермет', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Чусовское озеро', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Шабровский', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Шарташ', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Шарташский рынок', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Широкая речка', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Шувакиш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Эльмаш', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('Юго-запад', 'Екатеринбург')");
$rezDistricts[] = mysql_query("INSERT INTO districts (name, city) VALUES ('За городом', 'Екатеринбург')");

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