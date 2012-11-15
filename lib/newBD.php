<?php
    /**
     * Формирует необходимую для работы сайта базу данных со всей структурой и таблицами
     * При изменении структуры таблиц в этом файле или в БД, не забудь соответствующим образом изменить проверку валидности введенных пользователем данных на JS и на PHP, а также запрос на сохранение данных в БД при регистрации и другие запросы к БД
     */

    // Подключаем нужные модели и представления
    include '../models/GlobFunc.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Функция возвращает "1", если операция над БД была выполнена успешно и FALSE с расшифровкой ошибки, если выполнить ее не удалось
    // $typeRes = "1" - выдача результата по отдельной операции с базой данных, крезультат по каждой из которых выводится в отдельную строку
    // $typeRes = "2" - выдача результата по набору однотипных операций с БД - в одну строку!
    function returnResultMySql($rez)
    {
        global $DBlink;

        if ($rez == FALSE) {
            echo " <span style='color: red;'>FALSE(".$DBlink->errno." ".$DBlink->error.")</span> ";
        } else {
            echo 1;
        }
        echo "<br>";
    }

    // Текущая версия PHP на сервере
    //echo 'Current PHP version: '.phpversion().'<br>';
    // Вывести подробную информацию о PHP
    //echo phpinfo()."<br><br>";

    /***************************************************************************
     * Чистим базу данных перед закачкой исходных данных
     ***************************************************************************/

    $DBlink->query("DROP TABLE IF EXISTS
    users,
    tempFotos,
    userFotos,
    searchRequests,
    property,
    propertyFotos,
    messages,
    requestToView,
    requestFromOwners,
    districts,
    currencies
    ");

    echo "Статус удаления старых таблиц: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения информации о ПОЛЬЗОВАТЕЛЯХ
     ***************************************************************************/
    $DBlink->query("CREATE TABLE users (
        id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        typeTenant VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Равен строке true, если пользователь в данный момент ищет недвижимость (является потенциальным арендатором), в том числе, обязательно имеет поисковый запрос',
        typeOwner VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Равен строке true, если пользователь указал хотя бы 1 объявление по сдаче в аренду недвижимости (является собственником)(не имеет значение - опубликованное или нет)',
        typeAdmin VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Содержит строку, указывающую какие привилегии администратора имеет данный пользователь. Для каждой привилегии может быть установлено состояние: 0 (выключена) или 1 (включена), состояние NULL характеризует обычного пользователя (не админа). 1-ый признак - есть ли право создавать новые объекты под существующими пользователями, 2-ой признак - есть ли право создавать новые объявления (без проверки полноты реквизитов) для объектов из чужих баз по недвижимости',
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Имя пользователя',
        secondName VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Отчество пользователя',
        surname VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Фамилия пользователя',
        sex VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Пол пользователя',
        nationality VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Внешность пользователя',
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
        statusWork VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Работает или не работает пользователь',
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
        reg_date INT(11) COMMENT 'Время регистрации пользователя с точностью до секунд в формате timestamp',
        favoritesPropertysId BLOB COMMENT 'Список id объектов недвижимости, которые данный пользователь добавил в избранные'
)");

    echo "Статус создания таблицы users: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для временного хранения информации о ЗАГРУЖЕННЫХ при регистрации ФОТОГРАФИЯХ пользователей
     ***************************************************************************/

    $DBlink->query("CREATE TABLE tempFotos (
        id VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Содержит идентификатор фотографии, он же имя файла на сервере (без расширения)',
        fileUploadId VARCHAR(7) NOT NULL COMMENT 'фактически это такой идентификатор сессии заполнения формы регистрации. Позволяет добиться того, чтобы при перезагрузке формы (в случае, например, ошибок и пустых полей, незаполненных пользователем) данные о фотографиях не потерялись',
        folder VARCHAR(255) NOT NULL COMMENT 'Адрес каталога (кроме каталога, указывающего на размер фотографии), в котором расположен файл фотографии. Например: ../uploaded_files/3/ ',
        filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
        extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
        filesizeMb FLOAT(1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой'
)");

    echo "Статус создания таблицы tempFotos: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ пользователей (только личные)
     ***************************************************************************/

    $DBlink->query("CREATE TABLE userFotos (
        id VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Содержит идентификатор фотографии, он же имя файла на сервере (без расширения)',
        folder VARCHAR(255) NOT NULL COMMENT 'Адрес каталога (кроме каталога, указывающего на размер фотографии), в котором расположен файл фотографии. Например: ../uploaded_files/3/ ',
        filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
        extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
        filesizeMb FLOAT(1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
        userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя, которому соответствует данная фотография',
        status VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'У основной личной фотографии пользователя статус = основная, у остальных - пустой'
)");

    echo "Статус создания таблицы userFotos: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения информации о ПОИСКОВЫХ ЗАПРОСАХ пользователей
     ***************************************************************************/

    $DBlink->query("CREATE TABLE searchRequests (
        userId INT(11) NOT NULL PRIMARY KEY COMMENT 'Идентификатор пользователя, которому принадлежит данный поисковый запрос. Так как я считаю, что каждый пользователь может иметь только 1 поисковый запрос, то данное поле является ключом таблицы',
        typeOfObject VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип объекта, который ищет пользователь',
        amountOfRooms BLOB,
        adjacentRooms VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        floor VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
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
        additionalDescriptionOfSearch TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        interestingPropertysId BLOB COMMENT 'Список id объектов недвижимости, которыми заинтересовался пользователь в ходе поиска жилья. К просмотру анкет собственников данных объектов недвижимости пользователь имеет доступ в качестве потенциального арендатора'
)");

    echo "Статус создания таблицы searchRequests: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения информации об ОБЪЕКТАХ НЕДВИЖИМОСТИ пользователей
     ***************************************************************************/

    $DBlink->query("CREATE TABLE property (
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
        sexOfTenant VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Допустимый пол арендатора (если он будет жить один). Представляет собой строку, состоящую из названий допустимых полов, соединенных между собой знаком _ (пример: мужчина, женщина, мужчина_женщина)',
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
        status VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'не опубликовано' COMMENT 'Статус объявления: опубликовано или неопубликовано. Сразу после создания объявление становится неопубликованным',
        visibleUsersId BLOB COMMENT 'Список id пользователей, которые заинтересовались данным объектом недвижимости при его текущей публикации. После того, как объявление снято с публикации, данный список сохраняется лишь в течение некоторого срока (что-то около 10 дней), после чего его восстановить уже нельзя',
        schemeOfWork VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'классический, улучшенный или оптимальный'
)");

    echo "Статус создания таблицы property: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ объектов недвижимости
     ***************************************************************************/

    $DBlink->query("CREATE TABLE propertyFotos (
        id VARCHAR(32) NOT NULL PRIMARY KEY,
        folder VARCHAR(255) NOT NULL COMMENT 'Адрес каталога (кроме каталога, указывающего на размер фотографии), в котором расположен файл фотографии. Например: ../uploaded_files/3/ ',
        filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
        extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
        filesizeMb FLOAT(1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
        propertyId INT(11) NOT NULL COMMENT 'Идентификатор объекта недвижимости (или иначе объявления), к которому относится данная фотография',
        status VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'У основной фотографии объекта недвижимости статус = основная, у остальных - пустой'
)");

    echo "Статус создания таблицы propertyFotos: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения информации о СООБЩЕНИЯХ пользователей
     ***************************************************************************/

    $DBlink->query("CREATE TABLE messages (
        id VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Идентификатор сообщения (новости)',
        userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя, к которому относится данное сообщение',
        typeMessage VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип сообщения: о новом кандидате в арендаторы, о новом объекте недвижимости для арендатора, о назначении времени просмотра объекта для собственника, о назначении времени просмотра объекта для арендатора, об изменении времени просмотра объекта для собственника, об изменении времени просмотра объекта для арендатора',
        targetId INT(11) NOT NULL COMMENT 'Идентификатор пользователя (если речь идет о сообщении о новом кандидате в арендаторы), идентификатор объекта (если речь идет о новом объекте недвижимости для арендатора), идентификатор заявки на просмотр (если речь идет о новой заявке)',
        additionalPropertyId INT(11) COMMENT 'Идентификатор объекта недвижимости, к которому данный арендатор проявил интерес. Заполняется только для сообщений типа О новом объекте недвижимости для арендатора',
        status VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'не прочитано' COMMENT 'Статус сообщения: прочитано, непрочитано, удалено. Сразу после создания сообщение становится не прочитанным'
    )");

    echo "Статус создания таблицы messages: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения информации о заявках на просмотр
     ***************************************************************************/

    $DBlink->query("CREATE TABLE requestToView (
       id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор запроса на просмотр недвижимости',
       tenantId INT(11) NOT NULL COMMENT 'Идентификатор пользователя (арендатора), который отправил запрос на просмотр объекта недвижимости',
       propertyId INT(11) NOT NULL COMMENT 'Идентификатор объекта недвижимости, который желает посмотреть арендатор',
       tenantTime VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дата и время, которые указал арендатор в качестве желаемых (удобных) по этому запросу',
       tenantComment VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Комментарий арендатора к запросу на просмотр',
       ownerStatus VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Ответ собственника на запрос показа его недвижимости данному претенденту: confirmed - подтверждает, время показа указано в полях finalDate и т.д.; failure - отказ собственника от показа объекта данному претенденту; inProgress - ответ от собственника еще не получен',
       finalDate DATE COMMENT 'Хранит дату показа, согласованную с собственником и арендатором',
       finalTimeHours VARCHAR(2) COMMENT 'Хранит время (часы) показа, согласованные с собственником и арендатором',
       finalTimeMinutes VARCHAR(2) COMMENT 'Хранит время (минуты) показа, согласованные с собственником и арендатором'
    )");

    echo "Статус создания таблицы requestToView: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения информации о заявках на сдачу в аренду недвижимости от собственников
     ***************************************************************************/

    $DBlink->query("CREATE TABLE requestFromOwners (
       id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор запроса на сдачу в аренду недвижимости',
       name VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Имя собственника - как к нему обращаться',
       telephon VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
       address VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Человеческое назва',
       commentOwner TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Комментарий собственника к запросу',
       userId INT(11) COMMENT 'Идентификатор обратившегося пользователя. Не пуст, если с новым объявлением к нам обращается уже авторизованный пользователь'
    )");

    echo "Статус создания таблицы requestFromOwners: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения списка районов каждого города присутствия сервиса
     ***************************************************************************/

    $DBlink->query("CREATE TABLE districts (
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название района, которое отображается пользователю',
        city VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Город, в котором расположен данный район'
)");

    echo "Статус создания таблицы districts: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Записываем в таблицу с районами инфу о районах
     ***************************************************************************/

    $DBlink->query("INSERT INTO districts (name, city) VALUES
    ('Автовокзал (южный)', 'Екатеринбург'),
    ('Академический', 'Екатеринбург'),
    ('Ботанический', 'Екатеринбург'),
    ('ВИЗ', 'Екатеринбург'),
    ('Вокзальный', 'Екатеринбург'),
    ('Втузгородок', 'Екатеринбург'),
    ('Горный щит', 'Екатеринбург'),
    ('Елизавет', 'Екатеринбург'),
    ('ЖБИ', 'Екатеринбург'),
    ('Завокзальный', 'Екатеринбург'),
    ('Заречный', 'Екатеринбург'),
    ('Изоплит', 'Екатеринбург'),
    ('Исток', 'Екатеринбург'),
    ('Калиновский', 'Екатеринбург'),
    ('Кольцово', 'Екатеринбург'),
    ('Компрессорный', 'Екатеринбург'),
    ('Лечебный', 'Екатеринбург'),
    ('Малый исток', 'Екатеринбург'),
    ('Нижнеисетский', 'Екатеринбург'),
    ('Парковый', 'Екатеринбург'),
    ('Пионерский', 'Екатеринбург'),
    ('Птицефабрика', 'Екатеринбург'),
    ('Рудный', 'Екатеринбург'),
    ('Садовый', 'Екатеринбург'),
    ('Северка', 'Екатеринбург'),
    ('Семь ключей', 'Екатеринбург'),
    ('Сибирский тракт', 'Екатеринбург'),
    ('Синие камни', 'Екатеринбург'),
    ('Совхозный', 'Екатеринбург'),
    ('Сортировка новая', 'Екатеринбург'),
    ('Сортировка старая', 'Екатеринбург'),
    ('Уктус', 'Екатеринбург'),
    ('УНЦ', 'Екатеринбург'),
    ('Уралмаш', 'Екатеринбург'),
    ('Химмаш', 'Екатеринбург'),
    ('Центр', 'Екатеринбург'),
    ('Чермет', 'Екатеринбург'),
    ('Чусовское озеро', 'Екатеринбург'),
    ('Шабровский', 'Екатеринбург'),
    ('Шарташ', 'Екатеринбург'),
    ('Шарташский рынок', 'Екатеринбург'),
    ('Широкая речка', 'Екатеринбург'),
    ('Шувакиш', 'Екатеринбург'),
    ('Эльмаш', 'Екатеринбург'),
    ('Юго-запад', 'Екатеринбург'),
    ('За городом', 'Екатеринбург')
");

    echo "Статус записи инфы о районах в таблицу districts: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Создаем таблицу для хранения курсов валют: доллара США и евро к рублю
     ***************************************************************************/

    $DBlink->query("CREATE TABLE currencies (
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название валюты',
        value FLOAT(2) COMMENT 'Текущий курс обмена данной валюты на рубли'
)");

    echo "Статус создания таблицы currencies: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /****************************************************************************
     * Записываем в таблицу с валютами текущие курсы
     ***************************************************************************/

    $DBlink->query("INSERT INTO currencies (name, value) VALUES
    ('дол. США', 31.22),
    ('евро', 40.17)
    ");

    echo "Статус записи инфы о валютах: ";
    if ($DBlink->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

    /**
     * Проверяем настройки PHP сервера
     * В файле php.ini нужно установить ограничения на максимальный размер загружаемых файлов:
     * post_max_size = 100M
     * upload_max_filesize = 25M
     * memory_limit = 256M
     *
     * ini_set ("session.use_trans_sid", true); вроде как PHP сам умеет устанавливать id сессии либо в куки, либо в строку запроса (http://www.phpfaq.ru/sessions)
     */

    // Закрываем соединение с БД
    $globFunc->closeConnectToDB($DBlink);