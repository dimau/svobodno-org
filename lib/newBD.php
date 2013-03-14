<?php
/**
 * Формирует необходимую для работы сайта базу данных со всей структурой и таблицами
 * При изменении структуры таблиц в этом файле или в БД, не забудь соответствующим образом изменить проверку валидности введенных пользователем данных на JS и на PHP, а также запрос на сохранение данных в БД при регистрации и другие запросы к БД
 */

// Подключаем нужные модели и представления
$websiteRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $websiteRoot . '/models/DBconnect.php';

// Удалось ли подключиться к БД?
if (DBconnect::get() == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

// Функция возвращает "1", если операция над БД была выполнена успешно и FALSE с расшифровкой ошибки, если выполнить ее не удалось
// $typeRes = "1" - выдача результата по отдельной операции с базой данных, крезультат по каждой из которых выводится в отдельную строку
// $typeRes = "2" - выдача результата по набору однотипных операций с БД - в одну строку!
function returnResultMySql($rez) {
    if ($rez == FALSE) {
        echo " <span style='color: red;'>FALSE(" . DBconnect::get()->errno . " " . DBconnect::get()->error . ")</span> ";
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

DBconnect::get()->query("DROP TABLE IF EXISTS
users,
property,
tempFotos,
userFotos,
propertyFotos,
searchRequests,
requestsForOwnerContacts,
requestFromOwners,
messagesNewProperty,
messagesNewTenant,
districts,
currencies,
archiveAdverts,
e1,
66ru,
avito,
slando,
lastSuccessfulHandledAdvertsId,
knownPhoneNumbers,
invoices,
duplicatePhoneNumbers
");

echo "Удаление старых таблиц: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ХАРАКТЕРИСТИКИ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ
 *
 * Данная таблица с течением времени только увеличивается - мы накапливаем базу собственников и арендаторов
 * Алгоритмов по очистке таблицы не предусмотрено
 ***************************************************************************/
DBconnect::get()->query("CREATE TABLE users (
        id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Имя пользователя',
        secondName VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Отчество пользователя',
        surname VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Фамилия пользователя',
        sex VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Пол пользователя',
        nationality VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Внешность пользователя',
        birthday DATE,
        login VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        password VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
        telephon VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
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
        favoritePropertiesId TEXT COMMENT 'Список id объектов недвижимости, которые данный пользователь добавил в избранные',
        reg_date INT(11) COMMENT 'Время регистрации пользователя с точностью до секунд в формате timestamp',
        emailReg VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Электронный адрес, указанный пользователем при регистрации',
        lic VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci,
        user_hash VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Поле хранит id последней сессии пользователя. Это нужно для безопасности: если значение идентификатора сессии, присланное браузером, не совпадает с этим значением, значит его сессия устарела и требует обновления ',
        last_act INT(11) COMMENT 'Время последней активности пользователя в секундах после 1970 года - формат timestamp',
        typeTenant VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Равен строке true, если пользователь в данный момент ищет недвижимость (является потенциальным арендатором), в том числе, обязательно имеет поисковый запрос',
        typeOwner VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Равен строке true, если пользователь указал хотя бы 1 объявление по сдаче в аренду недвижимости (является собственником)(не имеет значение - опубликованное или нет)',
        typeAdmin VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Содержит строку, указывающую какие привилегии администратора имеет данный пользователь. Для каждой привилегии может быть установлено состояние: 0 (выключена) или 1 (включена), состояние NULL характеризует обычного пользователя (не админа). 1-ый признак - есть ли право создавать новые объекты под существующими пользователями, 2-ой признак - есть ли право создавать новые объявления (без проверки полноты реквизитов) для объектов из чужих баз по недвижимости, 3-ий признак - есть ли право на поиск пользователей и входа в их Личные кабинеты под аккаунтом админа',
        reviewFull INT(11) COMMENT 'Время действия прав пользователя на доступ к полной информации по объявлениям (время окончания премиум доступа) с точностью до секунд в формате timestamp (до какого момента времени)'
)");

echo "users: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ОБЪЕКТЫ НЕДВИЖИМОСТИ (ОБЪЯВЛЕНИЯ)
 *
 * Содержит информацию как по нашим объектам недвижимости (анкеты которых заполнил специалист компании),
 * так и по чужим объявлениям (полученным из чужих баз собственников)
 * Свои объявления с течением времени только накапливаются (не удаляются и не переносятся)
 * TODO: Чужие объявления периодически переносятся в архивную таблицу с такой же структурой
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE property (
        id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор объекта недвижимости или объявления - можно его называть и так, и так',
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
        roomSpace DEC(7, 2) COMMENT 'Площадь комнаты в м2',
        totalArea DEC(7, 2) COMMENT 'Площадь общая в м2',
        livingSpace DEC(7, 2) COMMENT 'Площадь жилая в м2',
        kitchenSpace DEC(7, 2) COMMENT 'Площадь кухни в м2',
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
        costOfRenting DEC(8) COMMENT 'Стоимость аренды в месяц в валюте, выбранной собственником',
        realCostOfRenting DEC(10, 2) COMMENT 'Стоимость аренды в месяц в рублях (при сохранении в БД стоимость аренды конвертируется в рубли, если она была указана в другой валюте). Это позволяет делать правильные выборки и сортировки из БД.',
        utilities VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Коммунальные услуги оплачиваются арендатором дополнительно: да или нет',
        costInSummer DEC(8) COMMENT 'Стоимость комм. услуг летом',
        costInWinter DEC(8) COMMENT 'Стоимость комм. услуг зимой',
        electricPower VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Электроэнергия оплачивается дополнительно: да или нет',
        bail VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Залог: есть или нет',
        bailCost DEC(8) COMMENT 'Величина залога в валюте для расчетов',
        prepayment VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Предоплата в количестве месяцев - указывается строкой (например, 1 месяц) для простоты отображения и возможности числового сравнения',
        compensationMoney DEC(10, 2) COMMENT 'Единоразовая комиссия в валюте для расчетов',
        compensationPercent DEC(6, 2) COMMENT 'Единоразовая комиссия в процентах от месячной стоимости аренды',
        repair VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Текущее состояние ремонта',
        furnish VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Текущее состояние отделки',
        windows VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Материал окон',
        internet VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие интернета',
        telephoneLine VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие проводного телефона',
        cableTV VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие кабельного ТВ',
        furnitureInLivingArea TEXT COMMENT 'Список мебели в жилой зоне - из предложенного в сервисе',
        furnitureInLivingAreaExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список мебели (указывается через запятую с пробелом)',
        furnitureInKitchen TEXT COMMENT 'Список мебели на кухне - из предложенного в сервисе',
        furnitureInKitchenExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список мебели (указывается через запятую с пробелом)',
        appliances TEXT COMMENT 'Список быт. техники - из предложенного в сервисе',
        appliancesExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список быт. техники (указывается через запятую с пробелом)',
        sexOfTenant TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Допустимый пол арендатора (если он будет жить один). Представляет собой строку - сериализованный массив. По такой строке работает SQL запрос с оператором LIKE',
        relations TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Отношения между арендаторами (если можно проживать не только одному, но и с кем-то). Представляет собой строку - сериализованный массив. По такой строке работает SQL запрос с оператором LIKE',
        children VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Возможность заселения арендаторов с детьми',
        animals VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Возможность заселения арендаторов с животными',
        contactTelephonNumber VARCHAR(20) COMMENT 'Контактный телефон собственника именно по аренде данного объявления, который будет болтаться на сайте',
        timeForRingBegin VARCHAR(20) COMMENT 'С какого времени можно звонить собственнику',
        timeForRingEnd VARCHAR(20) COMMENT 'До какого времени можно звонить собственнику',
        checking VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Как часто собственник проверяет недвижимость',
        comment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Свободный комментарий собственника',
        last_act INT(11) COMMENT 'Время последнего изменения объявления - будь-то время создания или время последнего редактирования. Используется для сортировки объявлений в разделе Мои объявления личного кабинета',
        reg_date INT(11) COMMENT 'Время создания объявления',
        status VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'опубликовано' COMMENT 'Статус объявления: опубликовано или не опубликовано. Сразу после создания объявление становится неопубликованным',
        adminComment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Комментарий сотрудников компании - админов',
		completeness VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Признак полноты данных об объекте (значения: 1/0). Если объявление получено из чужой базы, то его полнота устанавливается в 0 (то есть никаких особых требований к полноте не предъявляется). Если с данным объектом проведена полная работа и получены полные и достоверные данные, то его полнота устанавливается в 1 (при редактировании данных мы требуем соблюдения их полноты)',
		sourceOfAdvert TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Ссылка на страницу с описанием объявления в источнике (для чужих объявлений - e1.ru, 66.ru и т.д.)',
        hasPhotos INT(1) COMMENT 'Флаг хранит признак наличия или отсутствия фотографий в исходном объявлении: 1 - фотографии есть в исходном объявлении, 0 - фотографий в исходном объявлении нет'
)");

echo "property: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ВРЕМЕННОЕ ХРАНЕНИЕ ФОТОГРАФИЙ
 *
 * Создаем таблицу для временного хранения информации о загруженных фотографиях (при регистрации пользователей и при заведении новых объявлений)
 * TODO: необходимо в будущем реализовать периодическую чистку базы и удаление соответствующих файлов
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE tempFotos (
id VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Содержит идентификатор фотографии, он же имя файла на сервере (без расширения)',
fileUploadId VARCHAR(7) NOT NULL COMMENT 'фактически это такой идентификатор сессии заполнения формы регистрации. Позволяет добиться того, чтобы при перезагрузке формы (в случае, например, ошибок и пустых полей, незаполненных пользователем) данные о фотографиях не потерялись',
folder VARCHAR(255) NOT NULL COMMENT 'Адрес каталога (кроме каталога, указывающего на размер фотографии), в котором расположен файл фотографии. Например: ../uploaded_files/3/ ',
filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
filesizeMb DEC(5, 1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
regDate INT(11) COMMENT 'Дата и время сохранения фотографии на сервере'
)");

echo "tempFotos: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ФОТОГРАФИИ ПОЛЬЗОВАТЕЛЕЙ
 *
 * Данная таблица с течением времени только увеличивается вместе с накоплением базы собственников и арендаторов
 * Алгоритмов по очистке таблицы не предусмотрено
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE userFotos (
id VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Содержит идентификатор фотографии, он же имя файла на сервере (без расширения)',
folder VARCHAR(255) NOT NULL COMMENT 'Адрес каталога (кроме каталога, указывающего на размер фотографии), в котором расположен файл фотографии. Например: ../uploaded_files/3/ ',
filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
filesizeMb DEC(5, 1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя, которому соответствует данная фотография',
status VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'У основной личной фотографии пользователя статус = основная, у остальных - пустой',
regDate INT(11) COMMENT 'Дата и время сохранения фотографии на сервере'
)");

echo "userFotos: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ФОТОГРАФИИ ОБЪЕКТОВ НЕДВИЖИМОСТИ
 *
 * Данная таблица с течением времени увеличивается вместе с накоплением базы своих объявлений
 * TODO: При переносе объявления чужой базы также осуществляется перенос информации о фотографиях этого объявления в архивную таблицу
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE propertyFotos (
id VARCHAR(32) NOT NULL PRIMARY KEY,
folder VARCHAR(255) NOT NULL COMMENT 'Адрес каталога (кроме каталога, указывающего на размер фотографии), в котором расположен файл фотографии. Например: ../uploaded_files/3/ ',
filename VARCHAR(255) NOT NULL COMMENT 'Человеческое имя файла, с которым он был загружен с машины пользователя',
extension VARCHAR(5) NOT NULL COMMENT 'Расширение у файла фотографии',
filesizeMb DEC(5, 1) NOT NULL COMMENT 'Размер фотографии в Мб с точностью до 1 цифры после запятой',
propertyId INT(11) NOT NULL COMMENT 'Идентификатор объекта недвижимости (или иначе объявления), к которому относится данная фотография',
status VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'У основной фотографии объекта недвижимости статус = основная, у остальных - пустой',
regDate INT(11) COMMENT 'Дата и время сохранения фотографии на сервере'
)");

echo "propertyFotos: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ПОИСКОВЫЕ ЗАПРОСЫ
 *
 * Для каждого пользователя может быть заведен только 1 поисковый запрос.
 * По окончанию поиска пользователь может удалить поисковый запрос.
 * Соответствующая запись будет удалена из таблицы
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE searchRequests (
		id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор поискового запроса',
        userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя, которому принадлежит данный поисковый запрос. Так как я считаю, что каждый пользователь может иметь только 1 поисковый запрос, то данное поле является ключом таблицы',
        typeOfObject VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип объекта, который ищет пользователь',
        amountOfRooms TEXT,
        adjacentRooms VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        floor VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        minCost INT NOT NULL,
        maxCost INT NOT NULL,
        pledge INT NOT NULL,
        prepayment VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Максимальная предоплата, которую готов внести арендатор, указана строкой в месяцах',
        district TEXT COMMENT 'Список районов, в которых пользователь ищет недвижимость. Представляет собой сериализованный массив',
        withWho VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        linksToFriends TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        children VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        howManyChildren TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        animals VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        howManyAnimals TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        termOfLease VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
        additionalDescriptionOfSearch TEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
        regDate INT(11) COMMENT 'Дата и время создания поискового запроса',
        needEmail INT(1) DEFAULT 0 COMMENT 'Требуется ли оповещать пользователя по email о появлении новых подходящих под его запрос объектов недвижимости: 0 = не требуется, 1 = требуется',
        needSMS INT(1) DEFAULT 0 COMMENT 'Требуется ли оповещать пользователя по смс о появлении новых подходящих под его запрос объектов недвижимости: 0 = не требуется, 1 = требуется'
)");

echo "searchRequests: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ЗАЯВКИ НА ПРОСМОТР КОНТАКТОВ СОБСТВЕННИКОВ
 *
 * TODO: Заявки на просмотр, относящиеся к тому или иному арендатору, удаляются вместе с его поисковым запросом по окончанию поиска
 * TODO: В будущем добавить поле isDeletedTenant и isDeletedOwner в которые записывает true, когда соответственно арендатор удалит свой поисковый запрос, и собственник снимет с публикации объявление. Это позволит показывать заявку только арендатору или только собственнику раздельно. Когда и поисоквый запрос, в рамках которого была создана заявка удален и объект на который она была создана снят с публикации, то такая заявка также должна быть удалена сборщиком мусора в БД.
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE requestsForOwnerContacts (
       id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор запроса на просмотр контактов собственника',
       tenantId INT(11) NOT NULL COMMENT 'Идентификатор пользователя (арендатора), который отправил запрос на просмотр контактов собственника',
       propertyId INT(11) NOT NULL COMMENT 'Идентификатор объекта недвижимости, контактами собственника которого интересовался арендатор'
)");

echo "requestsForOwnerContacts: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ЗАЯВКИ ОТ СОБСТВЕННИКОВ
 *
 * Таблица хранит данные по необработанным заявкам от собственников - для них нужно формировать объявления
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE requestFromOwners (
       id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор запроса на сдачу в аренду недвижимости',
       name VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Имя собственника - как к нему обращаться',
       telephon VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
       address VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Человеческое назва',
       commentOwner TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Комментарий собственника к запросу',
       userId INT(11) COMMENT 'Идентификатор обратившегося пользователя. Не пуст, если с новым объявлением к нам обращается уже авторизованный пользователь',
       regDate INT(11) COMMENT 'Дата и время подачи заявки собственником'
    )");

echo "requestFromOwners: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * УВЕДОМЛЕНИЯ ТИПА "НОВЫЙ ПОДХОДЯЩИЙ ОБЪЕКТ НЕДВИЖИМОСТИ"
 *
 * Для повышения эффекивности (сокращения обращений к БД) содержит в себе всю нужную информацию по соответствующему объекту недвижимости
 * TODO: Уведомления удаляются при удалении соответствующих поисковых запросов, а также при снятии с публикации соответствующих объявлений
 ***************************************************************************/

// Уведомления для пользователей-арендаторов о появлении нового объекта недвижимости (объявления), которое удовлетворяет условиям поиска пользователя-арендатора
DBconnect::get()->query("CREATE TABLE messagesNewProperty (
        id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор уведомления',
        userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя, к которому относится данное уведомление',
        timeIndex INT(11) NOT NULL COMMENT 'Время формирования уведомления - используется для сортировки новостей по времени появления',
        messageType VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'newProperty' COMMENT 'Тип уведомления. В данной таблице хранятся новости типа newProperty',
        isReaded VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'не прочитано' COMMENT 'Статус уведомления: прочитано, не прочитано. Сразу после создания уведомления становится непрочитанным',
        fotoArr TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Массив массивов (по структуре совпадающий с uploadedFoto - это нужно, чтобы на основе этих данных могла работать функция getHTMLfotosWrapper), который включает в себя информацию только об 1 фотографии - основной',
        targetId INT(11) NOT NULL COMMENT 'Идентификатор объекта недвижимости, которому посвящена новость',
        needEmail INT(1) DEFAULT 0 COMMENT 'Требуется ли отправка уведомления пользователю по email: 0 = не требуется (или уже была осуществлена), 1 = требуется',
        needSMS INT(1) DEFAULT 0 COMMENT 'Требуется ли отправка уведомления пользователю по смс: 0 = не требуется (или уже была осуществлена), 1 = требуется',
        typeOfObject VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тип объекта: квартира, комната, дом, таунхаус, дача, гараж',
        address VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Человеческое название улицы и номера дома',
        currency VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Валюта для рассчетов',
        costOfRenting DEC(8) COMMENT 'Стоимость аренды в месяц в валюте, выбранной собственником',
        utilities VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Коммунальные услуги оплачиваются арендатором дополнительно: да или нет',
        electricPower VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Электроэнергия оплачивается дополнительно: да или нет',
        amountOfRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Количество комнат в квартире, доме:',
        adjacentRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие смежных комнат: да или нет',
        amountOfAdjacentRooms VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Количество смежных комнат',
        roomSpace DEC(7, 2) COMMENT 'Площадь комнаты в м2',
        totalArea DEC(7, 2) COMMENT 'Площадь общая в м2',
        livingSpace DEC(7, 2) COMMENT 'Площадь жилая в м2',
        kitchenSpace DEC(7, 2) COMMENT 'Площадь кухни в м2',
        totalAmountFloor INT COMMENT 'Общее количество этажей в доме, в котором расположена квартира, комната',
        numberOfFloor INT COMMENT 'Этажность дома, дачи, таунхауса'
    )");

echo "messagesNewProperty: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * УВЕДОМЛЕНИЯ ТИПА "НОВЫЙ ПРЕТЕНДЕНТ НА НЕДВИЖИМОСТЬ"
 *
 * Для повышения эффекивности (сокращения обращений к БД) содержит в себе всю нужную информацию по соответствующему объекту недвижимости и претенденту
 * TODO: Уведомления должны будут удаляться после снятия с публикации объявления
 ***************************************************************************/

// Уведомления для пользователей-собственников о появлении нового претендента на аренду, отправившего заявку на просмотр недвижимости пользователя-собственника
DBconnect::get()->query("CREATE TABLE messagesNewTenant (
        id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор уведомления',
        userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя, к которому относится данное уведомление',
        timeIndex INT(11) NOT NULL COMMENT 'Время формирования уведомления - используется для сортировки новостей по времени появления',
        messageType VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'newTenant' COMMENT 'Тип уведомления. В данной таблице хранятся новости типа newTenant',
        isReaded VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'не прочитано' COMMENT 'Статус уведомления: прочитано, не прочитано. Сразу после создания уведомления становится непрочитанным',
        fotoArr TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Массив массивов (по структуре совпадающий с uploadedFoto - это нужно, чтобы на основе этих данных могла работать функция getHTMLfotosWrapper), который включает в себя информацию только об 1 фотографии - основной',
        targetId INT(11) NOT NULL COMMENT 'Идентификатор потенциального арендатора, которому посвящена новость',
        address VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Человеческое название улицы и номера дома',
        apartmentNumber VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Номер квартиры, если комната в квартире, то с индексом для уникальности',
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Имя потенциального арендатора',
        secondName VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Отчество потенциального арендатора',
        surname VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Фамилия потенциального арендатора',
        birthday DATE COMMENT 'День рождения потенциального арендатора - для вычисления возраста',
        withWho VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Как (с кем) собирается снимать недвижимость арендатор',
        children VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Арендатор собирается проживать с детьми',
        animals VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Арендатор собирается проживать с домашними животными'
    )");

echo "messagesNewTenant: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * СПИСОК РАЙОНОВ ВСЕХ ГОРОДОВ ПРИСУТСТВИЯ
 *
 * Таблица, содержащая константы
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE districts (
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название района, которое отображается пользователю',
        city VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Город, в котором расположен данный район'
	)");

echo "districts: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

// Записываем в таблицу с районами инфу о районах
DBconnect::get()->query("INSERT INTO districts (name, city) VALUES
    ('Автовокзал', 'Екатеринбург'),
    ('Академический', 'Екатеринбург'),
    ('Ботанический', 'Екатеринбург'),
    ('ВИЗ', 'Екатеринбург'),
    ('Вокзальный', 'Екатеринбург'),
    ('Втузгородок', 'Екатеринбург'),
    ('Вторчермет', 'Екатеринбург'),
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

echo "Запись инфы о районах в таблицу districts: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * ТЕКУЩИЕ КУРСЫ ВАЛЮТ
 *
 * Создаем таблицу для хранения курсов валют: доллара США и евро к рублю
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE currencies (
        name VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Название валюты',
        value DEC(7, 2) COMMENT 'Текущий курс обмена данной валюты на рубли'
)");

echo "currencies: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

// Записываем в таблицу с валютами текущие курсы
DBconnect::get()->query("INSERT INTO currencies (name, value) VALUES
    ('дол. США', 31.22),
    ('евро', 40.17)
    ");

echo "Запись инфы о валютах: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * АРХИВ ОБЪЕКТОВ НЕДВИЖИМОСТИ (ОБЪЯВЛЕНИЙ)
 *
 * Содержит информацию только по чужим объявлениям (полученным из чужих баз собственников), перенесенным в архив из-за потери актуальности
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE archiveAdverts (
        id INT(11) NOT NULL PRIMARY KEY COMMENT 'Идентификатор объекта недвижимости или объявления - можно его называть и так, и так',
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
        roomSpace DEC(7, 2) COMMENT 'Площадь комнаты в м2',
        totalArea DEC(7, 2) COMMENT 'Площадь общая в м2',
        livingSpace DEC(7, 2) COMMENT 'Площадь жилая в м2',
        kitchenSpace DEC(7, 2) COMMENT 'Площадь кухни в м2',
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
        costOfRenting DEC(8) COMMENT 'Стоимость аренды в месяц в валюте, выбранной собственником',
        realCostOfRenting DEC(10, 2) COMMENT 'Стоимость аренды в месяц в рублях (при сохранении в БД стоимость аренды конвертируется в рубли, если она была указана в другой валюте). Это позволяет делать правильные выборки и сортировки из БД.',
        utilities VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Коммунальные услуги оплачиваются арендатором дополнительно: да или нет',
        costInSummer DEC(8) COMMENT 'Стоимость комм. услуг летом',
        costInWinter DEC(8) COMMENT 'Стоимость комм. услуг зимой',
        electricPower VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Электроэнергия оплачивается дополнительно: да или нет',
        bail VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Залог: есть или нет',
        bailCost DEC(8) COMMENT 'Величина залога в валюте для расчетов',
        prepayment VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Предоплата в количестве месяцев - указывается строкой (например, 1 месяц) для простоты отображения и возможности числового сравнения',
        compensationMoney DEC(10, 2) COMMENT 'Единоразовая комиссия в валюте для расчетов',
        compensationPercent DEC(6, 2) COMMENT 'Единоразовая комиссия в процентах от месячной стоимости аренды',
        repair VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Текущее состояние ремонта',
        furnish VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Текущее состояние отделки',
        windows VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Материал окон',
        internet VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие интернета',
        telephoneLine VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие проводного телефона',
        cableTV VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Наличие кабельного ТВ',
        furnitureInLivingArea TEXT COMMENT 'Список мебели в жилой зоне - из предложенного в сервисе',
        furnitureInLivingAreaExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список мебели (указывается через запятую с пробелом)',
        furnitureInKitchen TEXT COMMENT 'Список мебели на кухне - из предложенного в сервисе',
        furnitureInKitchenExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список мебели (указывается через запятую с пробелом)',
        appliances TEXT COMMENT 'Список быт. техники - из предложенного в сервисе',
        appliancesExtra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Дополнительный пользовательский список быт. техники (указывается через запятую с пробелом)',
        sexOfTenant TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Допустимый пол арендатора (если он будет жить один). Представляет собой строку - сериализованный массив. По такой строке работает SQL запрос с оператором LIKE',
        relations TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Отношения между арендаторами (если можно проживать не только одному, но и с кем-то). Представляет собой строку - сериализованный массив. По такой строке работает SQL запрос с оператором LIKE',
        children VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Возможность заселения арендаторов с детьми',
        animals VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Возможность заселения арендаторов с животными',
        contactTelephonNumber VARCHAR(20) COMMENT 'Контактный телефон собственника именно по аренде данного объявления, который будет болтаться на сайте',
        timeForRingBegin VARCHAR(20) COMMENT 'С какого времени можно звонить собственнику',
        timeForRingEnd VARCHAR(20) COMMENT 'До какого времени можно звонить собственнику',
        checking VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Как часто собственник проверяет недвижимость',
        comment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Свободный комментарий собственника',
        last_act INT(11) COMMENT 'Время последнего изменения объявления - будь-то время создания или время последнего редактирования. Используется для сортировки объявлений в разделе Мои объявления личного кабинета',
        reg_date INT(11) COMMENT 'Время создания объявления',
        status VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'опубликовано' COMMENT 'Статус объявления: опубликовано или не опубликовано. Сразу после создания объявление становится неопубликованным',
        adminComment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Комментарий сотрудников компании - админов',
		completeness VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Признак полноты данных об объекте (значения: 1/0). Если объявление получено из чужой базы, то его полнота устанавливается в 0 (то есть никаких особых требований к полноте не предъявляется). Если с данным объектом проведена полная работа и получены полные и достоверные данные, то его полнота устанавливается в 1 (при редактировании данных мы требуем соблюдения их полноты)',
		sourceOfAdvert TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Ссылка на страницу с описанием объявления в источнике (для чужих объявлений - e1.ru, 66.ru и т.д.)',
		hasPhotos INT(1) COMMENT 'Флаг хранит признак наличия или отсутствия фотографий в исходном объявлении: 1 - фотографии есть в исходном объявлении, 0 - фотографий в исходном объявлении нет'
)");

echo "archiveAdverts: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * СПИСОК ИДЕНТИФИКАТОРОВ ОБРАБОТАННЫХ ОБЪЯВЛЕНИЙ
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE e1 (
  id VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'id идентификатор объявления на сайте e1',
  date DATE COMMENT 'Дата публикации объявления'
)");

echo "e1: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

DBconnect::get()->query("CREATE TABLE 66ru (
  id VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'id идентификатор объявления на сайте 66.ru',
  date DATE COMMENT 'Дата публикации объявления'
)");

echo "66ru: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

DBconnect::get()->query("CREATE TABLE avito (
  id VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'id идентификатор объявления на сайте avito.ru',
  date DATE COMMENT 'Дата публикации объявления'
)");

echo "avito: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

DBconnect::get()->query("CREATE TABLE slando (
  id VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'id идентификатор объявления на сайте slando.ru',
  date DATE COMMENT 'Дата публикации объявления'
)");

echo "slando: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * СПИСОК ID ПОСЛЕДНИХ УСПЕШНО ОБРАБОТАННЫХ ОБЪЯВЛЕНИЙ
 * Если парсер при разборе страницы со списком объявлений встречает объявление из одним из этих id, он заканчивает работу
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE lastSuccessfulHandledAdvertsId (
  id VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'идентификатор объявления',
  mode VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'режим работы парсера, при котором было достигнуто данное объявление',
  indexNumber INT(1) COMMENT 'Место идентификатора: 1, 2, 3'
)");

echo "lastSuccessfulHandledAdvertsId: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

// Записываем в таблицу первоначальные параметры
DBconnect::get()->query("INSERT INTO lastSuccessfulHandledAdvertsId (id, mode, indexNumber) VALUES
    ('0', 'e1Kv1k', 0),
    ('0', 'e1Kv1k', 1),
    ('0', 'e1Kv1k', 2),
    ('0', 'e1Kv2k', 0),
    ('0', 'e1Kv2k', 1),
    ('0', 'e1Kv2k', 2),
    ('0', 'e1Kv3k', 0),
    ('0', 'e1Kv3k', 1),
    ('0', 'e1Kv3k', 2),
    ('0', 'e1Kv4k', 0),
    ('0', 'e1Kv4k', 1),
    ('0', 'e1Kv4k', 2),
    ('0', 'e1Kv5k', 0),
    ('0', 'e1Kv5k', 1),
    ('0', 'e1Kv5k', 2),
    ('0', 'e1Kom', 0),
    ('0', 'e1Kom', 1),
    ('0', 'e1Kom', 2),
    ('0', '66ruKv', 0),
    ('0', '66ruKv', 1),
    ('0', '66ruKv', 2),
    ('0', '66ruKom', 0),
    ('0', '66ruKom', 1),
    ('0', '66ruKom', 2),
    ('0', 'avitoKvEkat', 0),
    ('0', 'avitoKvEkat', 1),
    ('0', 'avitoKvEkat', 2),
    ('0', 'avitoKomEkat', 0),
    ('0', 'avitoKomEkat', 1),
    ('0', 'avitoKomEkat', 2),
    ('0', 'slandoKvEkat', 0),
    ('0', 'slandoKvEkat', 1),
    ('0', 'slandoKvEkat', 2),
    ('0', 'slandoKomEkat', 0),
    ('0', 'slandoKomEkat', 1),
    ('0', 'slandoKomEkat', 2)
    ");

echo "Запись первоначальной инфы об успешно обработанных объявлениях: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * НОМЕРА ТЕЛЕФОНОВ СОБСТВЕННИКОВ И АГЕНТОВ
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE knownPhoneNumbers (
  phoneNumber VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL PRIMARY KEY COMMENT 'Телефонный номер',
  status VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Статус телефонного номера: не определен, агент, собственник, арендатор (статус используется, если обладатель номера ищет человека на подселение к себе). Если парсер не обнаружил признаков агентства, то первоначально такому номеру присваивается статус - Не определен. Данный статус может изменить оператор',
  dateOfLastPublication INT(11) COMMENT 'Дата последней публикации объявления с указанием данного номера телефона в качестве контактного, в формате timestamp. Это позволит отслеживать устаревшие данные по номерам телефонов - если телефон долго не используется, то, возможно, стоит пересмотреть его статус'
)");

echo "knownPhoneNumbers: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * СЧЕТА ЗА ДОСТУП К СЕРВИСУ
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE invoices (
  number VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL PRIMARY KEY COMMENT 'Идентификатор счета, выставленного клиенту',
  userId INT(11) NOT NULL COMMENT 'Идентификатор пользователя, для которого выставлен счет',
  status VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'текущий статус счета: выставлен, оплачен, ошибка',
  cost INT(6) COMMENT 'Сумма счета, которую должен оплатить клиент',
  purchase VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Тариф доступа к порталу, приобретенный пользователем по данной оплате: reviewRooms14d, reviewFlats14d, reviewFull10d',
  dateOfPayment INT(11) COMMENT 'Дата и время успешной обработки оплаты в формате timestamp'
)");

echo "invoices: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * СЛУЖЕБНАЯ ТАБЛИЦА СО СПИСКОМ ПОВТОРЯЮЩИХСЯ ТЕЛЕФОНОВ НЕ АГЕНТОВ
 ***************************************************************************/

DBconnect::get()->query("CREATE TABLE duplicatePhoneNumbers (
  phoneNumber VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL PRIMARY KEY COMMENT 'Телефонный номер'
)");

echo "duplicatePhoneNumbers: ";
if (DBconnect::get()->errno) returnResultMySql(FALSE); else returnResultMySql(TRUE);

/****************************************************************************
 * Проверяем настройки PHP сервера
 *
 * В файле php.ini нужно установить ограничения на максимальный размер загружаемых файлов:
 * post_max_size = 100M     // Максимальный размер в МБ для POST запроса пользователя
 * upload_max_filesize = 25M // Максимальный размер файла, закачивать который разрешает php для пользователя
 * memory_limit = 256M    // Максимальный размер оперативной памяти, которую сервер может выделить для выполнения 1 сценария PHP
 * display_errors = Off        // Запрещает PHP отображать предупреждения и ошибки на экране
 * все права для всех пользователей на каталог uploaded_files и logs(чтобы можно было записывать новые фотографии и удалять старые, а также писать логи)
 * date.timezone = Asia/Yekaterinburg // Необходимо установить временную зону по умолчанию
 ***************************************************************************/

// Закрываем соединение с БД
DBconnect::closeConnectToDB();