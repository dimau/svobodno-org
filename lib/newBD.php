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
        prepayment VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Максимальная предоплата, которую готов внести арендатор, указана строкой в месяцах',
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
        typeOfObject VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип объекта: квартира, комната, дом, таунхаус, дача, гараж',
        dateOfEntry DATE COMMENT 'С какого числа арендатор может въезжать в объект',
        termOfLease VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Срок аренды: длительный срок (от года) или несколько месяцев (до года)',
        dateOfCheckOut DATE COMMENT 'Крайний срок выезда арендатора',
        amountOfRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Количество комнат в квартире, доме:',
        adjacentRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие смежных комнат: да или нет',
        amountOfAdjacentRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Количество смежных комнат',
        typeOfBathrooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Санузел: совмещенный, раздельный или количество штук',
        typeOfBalcony VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип балкона, лоджии, эркера и количество',
        balconyGlazed VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Остекление балкона/лоджии',
        roomSpace FLOAT(2) COMMENT 'Площадь комнаты в м2',
        totalArea FLOAT(2) COMMENT 'Площадь общая в м2',
        livingSpace FLOAT(2) COMMENT 'Площадь жилая в м2',
        kitchenSpace FLOAT(2) COMMENT 'Площадь кухни в м2',
        floor INT COMMENT 'Этаж, на котором расположена квартира, комната',
        totalAmountFloor INT COMMENT 'Общее количество этажей в доме, в котором расположена квартира, комната',
        numberOfFloor INT COMMENT 'Этажность дома, дачи, таунхауса',
        concierge VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие в подъезде консьержа',
        intercom VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие в подъезде домофона',
        parking VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип парковки во дворе',
        city VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Город местоположения объекта недвижимости',
        district VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Район местоположения объекта недвижимости - название',
        coordX VARCHAR(30) COMMENT 'Координата x на яндекс карте местоположения объекта недвижимости',
        coordY VARCHAR(30) COMMENT 'Координата y на яндекс карте местоположения объекта недвижимости',
        address VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Человеческое название улицы и номера дома',
        apartmentNumber VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Номер квартиры, если комната в квартире, то с индексом для уникальности',
        subwayStation VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название станции метро рядом',
        distanceToMetroStation INT COMMENT 'Расстояние в минутах ходьбы до ближайшего метро',
        currency VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Валюта для рассчетов',
        costOfRenting FLOAT(2) COMMENT 'Стоимость аренды в месяц в валюте, выбранной собственником',
        realCostOfRenting FLOAT(2) COMMENT 'Стоимость аренды в месяц в рублях (при сохранении в БД стоимость аренды конвертируется в рубли, если она была указана в другой валюте). Это позволяет делать правильные выборки и сортировки из БД.',
        utilities VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Коммунальные услуги оплачиваются арендатором дополнительно: да или нет',
        costInSummer FLOAT(2) COMMENT 'Стоимость комм. услуг летом',
        costInWinter FLOAT(2) COMMENT 'Стоимость комм. услуг зимой',
        electricPower VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Электроэнергия оплачивается дополнительно: да или нет',
        bail VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Залог: есть или нет',
        bailCost FLOAT(2) COMMENT 'Величина залога в валюте для расчетов',
        prepayment VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Предоплата в количестве месяцев - указывается строкой (например, 1 месяц) для простоты отображения и возможности числового сравнения',
        compensationMoney FLOAT(2) COMMENT 'Единоразовая комиссия собственника в валюте для расчетов',
        compensationPercent FLOAT(2) COMMENT 'Единоразовая комиссия собственника в процентах от месячной стоимости аренды',
        repair VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Текущее состояние ремонта',
        furnish VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Текущее состояние отделки',
        windows VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Материал окон',
        internet VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие интернета',
        telephoneLine VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие проводного телефона',
        cableTV VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие кабельного ТВ',
        furnitureInLivingArea BLOB COMMENT 'Список мебели в жилой зоне - из предложенного в сервисе',
        furnitureInLivingAreaExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список мебели (указывается через запятую с пробелом)',
        furnitureInKitchen BLOB COMMENT 'Список мебели на кухне - из предложенного в сервисе',
        furnitureInKitchenExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список мебели (указывается через запятую с пробелом)',
        appliances BLOB COMMENT 'Список быт. техники - из предложенного в сервисе',
        appliancesExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список быт. техники (указывается через запятую с пробелом)',
        sexOfTenant VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Допустимый пол арендатора (если он будет жить один)',
        relations VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Отношения между арендаторами (если можно проживать не только одному, но и с кем-то). Представляет собой строку, состоящую из названий допустимых отношений, соединенных между собой знаком _',
        children VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Возможность заселения арендаторов с детьми',
        animals VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Возможность заселения арендаторов с животными',
        contactTelephonNumber VARCHAR(20) COMMENT 'Контактный телефон собственника именно по аренде данного объявления, который будет болтаться на сайте',
        timeForRingBegin VARCHAR(20) COMMENT 'С какого времени можно звонить собственнику',
        timeForRingEnd VARCHAR(20) COMMENT 'До какого времени можно звонить собственнику',
        checking VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Как часто собственник проверяет недвижимость',
        responsibility TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Распределение ответственности между арендатором и собственником за ремонт и поддержание недвижимости',
        comment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Свободный комментарий собственника',
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

// Создаем таблицу для хранения курсов валют: доллара США и евро к рублю
$rez = mysql_query("CREATE TABLE currencies (
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название валюты',
        value FLOAT(2) COMMENT 'Текущий курс обмена данной валюты на рубли'
)");

echo "Статус создания таблицы currencies: " . $rez . " \n ";

// Записываем в таблицу с валютами текущие курсы
$rezCurrencies[] = mysql_query("INSERT INTO currencies (name, value) VALUES ('дол. США', 31.22)");
$rezCurrencies[] = mysql_query("INSERT INTO currencies (name, value) VALUES ('евро', 40.17)");

echo "Статус записи инфы о валютах: ";
foreach ($rezCurrencies as $value) {
    echo $value;
}

/**
 * Проверяем настройки PHP сервера
 *
 * ini_set ("session.use_trans_sid", true); вроде как PHP сам умеет устанавливать id сессии либо в куки, либо в строку запроса (http://www.phpfaq.ru/sessions)
 */
?>