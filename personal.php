<?php
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

/*************************************************************************************
 * Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
 ************************************************************************************/
if (!login()) {
    header('Location: login.php');
}

/*************************************************************************************
 * Получаем информацию о пользователе по его логину из БД сервера
 ************************************************************************************/

$login = $_COOKIE['login']; // TODO: что будет, если куки подчищены?

$rezUsers = mysql_query("SELECT * FROM users WHERE login = '" . $login . "'");
$rowUsers = mysql_fetch_assoc($rezUsers);

// Если пользователь пожелал удалить поисковый запрос, то это нужно сделать вместо получения данных из таблицы БД searchrequests
if (isset($_GET['action']) && $_GET['action'] == 'deleteSearchRequest') {
    $rez = mysql_query("DELETE FROM searchrequests WHERE userId='" . $rowUsers['id'] . "'");
    $rowSearchRequests = false;
    $rez = mysql_query("UPDATE users SET typeTenant='" . "false" . "' WHERE id='" . $rowUsers['id'] . "'");
    $rowUsers['typeTenant'] = "false";
} else {
    $rezSearchRequests = mysql_query("SELECT * FROM searchrequests WHERE userId = '" . $rowUsers['id'] . "'");
    $rowSearchRequests = mysql_fetch_assoc($rezSearchRequests);
}

$rezUserFotos = mysql_query("SELECT * FROM userfotos WHERE userId = '" . $rowUsers['id'] . "'");
$rowUserFotos = mysql_fetch_assoc($rezUserFotos); // TODO: сделать отображение нескольких фоток, если пользователь загрузит не одну

// Готовим массив со списком районов в городе пользователя: нужно только для вкладки Поиск Личного кабинета
$rezDistricts = mysql_query("SELECT * FROM districts WHERE city = '" . "Екатеринбург" . "'");
for ($i = 0; $i < mysql_num_rows($rezDistricts); $i++) {
    $rowDistricts = mysql_fetch_assoc($rezDistricts);
    $allDistrictsInCity[$rowDistricts['id']] = $rowDistricts['name'];
}

// Инициализируем переменную корректности - используется при формировании нового Запроса на поиск
$correct = "null";
$correctNewSearchRequest = "null";
$correctNewProfileParameters = "null";

/**************************************************************************************************************
 * Инициализируем переменные поискового запроса, а также данные Профиля пользователя в зависимости от ситуации
 **************************************************************************************************************/

// Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - значения по умолчанию.
if (isset($rowUsers['typeTenant'])) $typeTenant = $rowUsers['typeTenant']; else $typeTenant = "true";
if (isset($rowUsers['typeOwner'])) $typeOwner = $rowUsers['typeOwner']; else $typeOwner = "true";
if (isset($rowUsers['name'])) $name = $rowUsers['name']; else $name = "";
if (isset($rowUsers['secondName'])) $secondName = $rowUsers['secondName']; else $secondName = "";
if (isset($rowUsers['surname'])) $surname = $rowUsers['surname']; else $surname = "";
if (isset($rowUsers['sex'])) $sex = $rowUsers['sex']; else $sex = "0";
if (isset($rowUsers['nationality'])) $nationality = $rowUsers['nationality']; else $nationality = "0";
if (isset($rowUsers['birthday'])) $birthday = birthdayFromDBToView($rowUsers['birthday']); else $birthday = "";
if (isset($rowUsers['login'])) $login = $rowUsers['login']; else $login = "";
if (isset($rowUsers['password'])) $password = $rowUsers['password']; else $password = ""; // Реальный пароль пользователя в БД не хранится, но так как пароль проверяется только на наличие (не = 0 длину), то достаточно того, что хранится в БД в поле password
if (isset($rowUsers['telephon'])) $telephon = $rowUsers['telephon']; else $telephon = "";
if (isset($rowUsers['email'])) $email = $rowUsers['email']; else $email = "";
$fileUploadId = generateCode(7);
if (isset($rowUsers['currentStatusEducation'])) $currentStatusEducation = $rowUsers['currentStatusEducation']; else $currentStatusEducation = "0";
if (isset($rowUsers['almamater'])) $almamater = $rowUsers['almamater']; else $almamater = "";
if (isset($rowUsers['speciality'])) $speciality = $rowUsers['speciality']; else $speciality = "";
if (isset($rowUsers['kurs'])) $kurs = $rowUsers['kurs']; else $kurs = "";
if (isset($rowUsers['ochnoZaochno'])) $ochnoZaochno = $rowUsers['ochnoZaochno']; else $ochnoZaochno = "0";
if (isset($rowUsers['yearOfEnd'])) $yearOfEnd = $rowUsers['yearOfEnd']; else $yearOfEnd = "";
if (isset($rowUsers['notWorkCheckbox'])) $notWorkCheckbox = $rowUsers['notWorkCheckbox']; else $notWorkCheckbox = "";
if (isset($rowUsers['placeOfWork'])) $placeOfWork = $rowUsers['placeOfWork']; else $placeOfWork = "";
if (isset($rowUsers['workPosition'])) $workPosition = $rowUsers['workPosition']; else $workPosition = "";
if (isset($rowUsers['regionOfBorn'])) $regionOfBorn = $rowUsers['regionOfBorn']; else $regionOfBorn = "";
if (isset($rowUsers['cityOfBorn'])) $cityOfBorn = $rowUsers['cityOfBorn']; else $cityOfBorn = "";
if (isset($rowUsers['shortlyAboutMe'])) $shortlyAboutMe = $rowUsers['shortlyAboutMe']; else $shortlyAboutMe = "";
if (isset($rowUsers['vkontakte'])) $vkontakte = $rowUsers['vkontakte']; else $vkontakte = "";
if (isset($rowUsers['odnoklassniki'])) $odnoklassniki = $rowUsers['odnoklassniki']; else $odnoklassniki = "";
if (isset($rowUsers['facebook'])) $facebook = $rowUsers['facebook']; else $facebook = "";
if (isset($rowUsers['twitter'])) $twitter = $rowUsers['twitter']; else $twitter = "";

if (isset($rowSearchRequests['typeOfObject'])) $typeOfObject = $rowSearchRequests['typeOfObject']; else $typeOfObject = "flat";
// Инициализируем переменные для отображения количества комнат
$amountOfRooms1 = ""; $amountOfRooms2 = ""; $amountOfRooms3 = ""; $amountOfRooms4 = ""; $amountOfRooms5 = ""; $amountOfRooms6 = "";
if (isset($rowSearchRequests['amountOfRooms']))
{
    $amountOfRooms = unserialize($rowSearchRequests['amountOfRooms']);
    foreach ($amountOfRooms as $value) {
        if ($value == "1") $amountOfRooms1 = "1";
        if ($value == "2") $amountOfRooms2 = "2";
        if ($value == "3") $amountOfRooms3 = "3";
        if ($value == "4") $amountOfRooms4 = "4";
        if ($value == "5") $amountOfRooms5 = "5";
        if ($value == "6") $amountOfRooms6 = "6";
    }
}
else {
    $amountOfRooms = array("1", "2", "3", "4", "5", "6");
}
if (isset($rowSearchRequests['adjacentRooms'])) $adjacentRooms = $rowSearchRequests['adjacentRooms']; else $adjacentRooms = "yes";
if (isset($rowSearchRequests['floor'])) $floor = $rowSearchRequests['floor']; else $floor = "any";
if (isset($rowSearchRequests['furniture'])) $furniture = $rowSearchRequests['furniture']; else $furniture = "any";
if (isset($rowSearchRequests['minCost'])) $minCost = $rowSearchRequests['minCost']; else $minCost = "";
if (isset($rowSearchRequests['maxCost'])) $maxCost = $rowSearchRequests['maxCost']; else $maxCost = "";
if (isset($rowSearchRequests['pledge'])) $pledge = $rowSearchRequests['pledge']; else $pledge = "";
// Инициализируем переменные для отображения районов
$district1 = ""; $district2 = ""; $district3 = ""; $district4 = ""; $district5 = ""; $district6 = ""; $district7 = ""; $district8 = ""; $district9 = ""; $district10 = ""; $district11 = ""; $district12 = ""; $district13 = ""; $district14 = ""; $district15 = ""; $district16 = ""; $district17 = ""; $district18 = ""; $district19 = ""; $district20 = ""; $district21 = ""; $district22 = ""; $district23 = ""; $district24 = ""; $district25 = ""; $district26 = ""; $district27 = ""; $district28 = ""; $district29 = ""; $district30 = ""; $district31 = ""; $district32 = ""; $district33 = ""; $district34 = ""; $district35 = ""; $district36 = ""; $district37 = ""; $district38 = ""; $district39 = ""; $district40 = ""; $district41 = ""; $district42 = ""; $district43 = ""; $district44 = ""; $district45 = ""; $district46 = "";
if (isset($rowSearchRequests['district']))
{
    $district = unserialize($rowSearchRequests['district']);
    foreach ($district as $value) {
        if ($value == "1") $district1 = "1";
        if ($value == "2") $district2 = "2";
        if ($value == "3") $district3 = "3";
        if ($value == "4") $district4 = "4";
        if ($value == "5") $district5 = "5";
        if ($value == "6") $district6 = "6";
        if ($value == "7") $district7 = "7";
        if ($value == "8") $district8 = "8";
        if ($value == "9") $district9 = "9";
        if ($value == "10") $district10 = "10";
        if ($value == "11") $district11 = "11";
        if ($value == "12") $district12 = "12";
        if ($value == "13") $district13 = "13";
        if ($value == "14") $district14 = "14";
        if ($value == "15") $district15 = "15";
        if ($value == "16") $district16 = "16";
        if ($value == "17") $district17 = "17";
        if ($value == "18") $district18 = "18";
        if ($value == "19") $district19 = "19";
        if ($value == "20") $district20 = "20";
        if ($value == "21") $district21 = "21";
        if ($value == "22") $district22 = "22";
        if ($value == "23") $district23 = "23";
        if ($value == "24") $district24 = "24";
        if ($value == "25") $district25 = "25";
        if ($value == "26") $district26 = "26";
        if ($value == "27") $district27 = "27";
        if ($value == "28") $district28 = "28";
        if ($value == "29") $district29 = "29";
        if ($value == "30") $district30 = "30";
        if ($value == "31") $district31 = "31";
        if ($value == "32") $district32 = "32";
        if ($value == "33") $district33 = "33";
        if ($value == "34") $district34 = "34";
        if ($value == "35") $district35 = "35";
        if ($value == "36") $district36 = "36";
        if ($value == "37") $district37 = "37";
        if ($value == "38") $district38 = "38";
        if ($value == "39") $district39 = "39";
        if ($value == "40") $district40 = "40";
        if ($value == "41") $district41 = "41";
        if ($value == "42") $district42 = "42";
        if ($value == "43") $district43 = "43";
        if ($value == "44") $district44 = "44";
        if ($value == "45") $district45 = "45";
        if ($value == "46") $district46 = "46";
    }
}
else {
    $district = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46");
}
if (isset($rowSearchRequests['withWho'])) $withWho = $rowSearchRequests['withWho']; else $withWho = "alone";
if (isset($rowSearchRequests['linksToFriends'])) $linksToFriends = $rowSearchRequests['linksToFriends']; else $linksToFriends = "";
if (isset($rowSearchRequests['children'])) $children = $rowSearchRequests['children']; else $children = "without";
if (isset($rowSearchRequests['howManyChildren'])) $howManyChildren = $rowSearchRequests['howManyChildren']; else $howManyChildren = "";
if (isset($rowSearchRequests['animals'])) $animals = $rowSearchRequests['animals']; else $animals = "without";
if (isset($rowSearchRequests['howManyAnimals'])) $howManyAnimals = $rowSearchRequests['howManyAnimals']; else $howManyAnimals = "";
if (isset($rowSearchRequests['period'])) $period = $rowSearchRequests['period']; else $period = "";
if (isset($rowSearchRequests['additionalDescriptionOfSearch'])) $additionalDescriptionOfSearch = $rowSearchRequests['additionalDescriptionOfSearch']; else $additionalDescriptionOfSearch = "";

/********************************************************************************
 * РЕДАКТИРОВАНИЕ ЛИЧНЫХ ДАННЫХ ПРОФИЛЯ. Если пользователь отправил редактированные параметры своего профиля
 *******************************************************************************/
if (isset($_POST['saveProfileParameters'])) {
    // Формируем набор переменных для сохранения в базу данных, либо для возвращения вместе с формой при их некорректности
    if (isset($_POST['name'])) $name = htmlspecialchars($_POST['name']);
    if (isset($_POST['secondName'])) $secondName = htmlspecialchars($_POST['secondName']);
    if (isset($_POST['surname'])) $surname = htmlspecialchars($_POST['surname']);
    if (isset($_POST['sex'])) $sex = htmlspecialchars($_POST['sex']);
    if (isset($_POST['nationality'])) $nationality = htmlspecialchars($_POST['nationality']);
    if (isset($_POST['birthday'])) $birthday = htmlspecialchars($_POST['birthday']);
    if (isset($_POST['login'])) { $oldLogin = $login; $login = htmlspecialchars($_POST['login']); }
    if (isset($_POST['password'])) $password = htmlspecialchars($_POST['password']);
    if (isset($_POST['telephon'])) $telephon = htmlspecialchars($_POST['telephon']);
    if (isset($_POST['email'])) $email = htmlspecialchars($_POST['email']);
    $fileUploadId = $_POST['fileUploadId'];

    if (isset($_POST['currentStatusEducation'])) $currentStatusEducation = htmlspecialchars($_POST['currentStatusEducation']);
    if (isset($_POST['almamater'])) $almamater = htmlspecialchars($_POST['almamater']);
    if (isset($_POST['speciality'])) $speciality = htmlspecialchars($_POST['speciality']);
    if (isset($_POST['kurs'])) $kurs = htmlspecialchars($_POST['kurs']);
    if (isset($_POST['ochnoZaochno'])) $ochnoZaochno = htmlspecialchars($_POST['ochnoZaochno']);
    if (isset($_POST['yearOfEnd'])) $yearOfEnd = htmlspecialchars($_POST['yearOfEnd']);
    if (isset($_POST['notWorkCheckbox'])) $notWorkCheckbox = htmlspecialchars($_POST['notWorkCheckbox']);
    if (isset($_POST['placeOfWork'])) $placeOfWork = htmlspecialchars($_POST['placeOfWork']);
    if (isset($_POST['workPosition'])) $workPosition = htmlspecialchars($_POST['workPosition']);
    if (isset($_POST['regionOfBorn'])) $regionOfBorn = htmlspecialchars($_POST['regionOfBorn']);
    if (isset($_POST['cityOfBorn'])) $cityOfBorn = htmlspecialchars($_POST['cityOfBorn']);
    if (isset($_POST['shortlyAboutMe'])) $shortlyAboutMe = htmlspecialchars($_POST['shortlyAboutMe']);

    if (isset($_POST['vkontakte'])) $vkontakte = htmlspecialchars($_POST['vkontakte']);
    if (isset($_POST['odnoklassniki'])) $odnoklassniki = htmlspecialchars($_POST['odnoklassniki']);
    if (isset($_POST['facebook'])) $facebook = htmlspecialchars($_POST['facebook']);
    if (isset($_POST['twitter'])) $twitter = htmlspecialchars($_POST['twitter']);

    // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
    $errors = userDataCorrect("validateProfileParameters");
    if (count($errors) == 0) $correctNewProfileParameters = "true"; else $correctNewProfileParameters = "false"; // Считаем ошибки, если 0, то можно сохранит новые параметры в БД

    // Если данные верны, сохраним их в БД
    if ($correctNewProfileParameters == "true") {

        // Корректируем дату дня рождения для того, чтобы сделать ее пригодной для сохранения в базу данных
        $birthdayDB = birthdayFromViewToDB($birthday);
        $salt = mt_rand(100, 999);
        $password = md5(md5($password) . $salt);

        // Сохраняем новые параметры Профиля пользователя в БД
            $rez = mysql_query("UPDATE users SET
            name='" . $name ."',
            secondName='" . $secondName ."',
            surname='" . $surname ."',
            sex='" . $sex ."',
            nationality='" . $nationality ."',
            birthday='" . $birthdayDB ."',


            login='" . $login ."',


            password='" . $typeOfObject ."',


            telephon='" . $telephon ."',
            email='" . $email ."',
            currentStatusEducation='" . $currentStatusEducation ."',
            almamater='" . $almamater ."',
            speciality='" . $speciality ."',
            kurs='" . $kurs ."',
            ochnoZaochno='" . $ochnoZaochno ."',
            yearOfEnd='" . $yearOfEnd ."',
            notWorkCheckbox='" . $notWorkCheckbox ."',
            placeOfWork='" . $placeOfWork ."',
            workPosition='" . $workPosition ."',
            regionOfBorn='" . $regionOfBorn ."',
            cityOfBorn='" . $cityOfBorn ."',
            shortlyAboutMe='" . $shortlyAboutMe ."',
            vkontakte='" . $vkontakte ."',
            odnoklassniki='" . $odnoklassniki ."',
            facebook='" . $facebook ."',
            twitter='" . $twitter ."'
            WHERE id = '" . $rowUsers['id'] . "'");

        /******* Переносим информацию о фотографиях пользователя в таблицу для постоянного хранения *******/
        // Получим информацию о всех фотках, соответствующих текущему fileUploadId
        $rezTempFotos = mysql_query("SELECT id, filename, extension, filesizeMb FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");
        for ($i = 0; $i < mysql_num_rows($rezTempFotos); $i++) {
            $rowTempFotos = mysql_fetch_assoc($rezTempFotos);
            mysql_query("INSERT INTO userFotos (id, filename, extension, filesizeMb, userId) VALUES ('" . $rowTempFotos['id'] . "','" . $rowTempFotos['filename'] . "','" . $rowTempFotos['extension'] . "','" . $rowTempFotos['filesizeMb'] . "','" . $rowUsers['id'] . "')"); // Переносим информацию о фотографиях на постоянное хранение
        }
        // Удаляем записи о фотках в таблице для временного хранения данных
        mysql_query("DELETE FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");
    }
}

/********************************************************************************
 * РЕДАКТИРОВАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь отправил редактированные параметры поискового запроса
 *******************************************************************************/

// Так как пользователь ввел новые парметры поискового запроса - их нужно воспроизвести в форму - это необходимо, чтобы в случае ошибки пользователю не пришлось все данные перебивать заново
if (isset($_POST['saveSearchParametersButton'])) {
    // Формируем набор переменных для сохранения в базу данных, либо для возвращения вместе с формой при их некорректности
    if (isset($_POST['typeOfObject'])) $typeOfObject = htmlspecialchars($_POST['typeOfObject']);
    if (isset($_POST['amountOfRooms']) && is_array($_POST['amountOfRooms'])) // Проверяем, передан ли массив значений
    {
        $amountOfRooms = $_POST['amountOfRooms']; // Будем использовать переменную при записи данных в таблицу в виде массива
        foreach ($_POST['amountOfRooms'] as $value) {
            if ($value == "1") $amountOfRooms1 = "1";
            if ($value == "2") $amountOfRooms2 = "2";
            if ($value == "3") $amountOfRooms3 = "3";
            if ($value == "4") $amountOfRooms4 = "4";
            if ($value == "5") $amountOfRooms5 = "5";
            if ($value == "6") $amountOfRooms6 = "6";
        }
    }
    else {
        $amountOfRooms = array("1", "2", "3", "4", "5", "6");
    }
    if (isset($_POST['district']) && is_array($_POST['district'])) // Проверяем, передан ли массив значений
    {
        $district = $_POST['district']; // Будем использовать переменную при записи данных в таблицу в виде массива
        foreach ($_POST['district'] as $value) {
            if ($value == "1") $district1 = "1";
            if ($value == "2") $district2 = "2";
            if ($value == "3") $district3 = "3";
            if ($value == "4") $district4 = "4";
            if ($value == "5") $district5 = "5";
            if ($value == "6") $district6 = "6";
            if ($value == "7") $district7 = "7";
            if ($value == "8") $district8 = "8";
            if ($value == "9") $district9 = "9";
            if ($value == "10") $district10 = "10";
            if ($value == "11") $district11 = "11";
            if ($value == "12") $district12 = "12";
            if ($value == "13") $district13 = "13";
            if ($value == "14") $district14 = "14";
            if ($value == "15") $district15 = "15";
            if ($value == "16") $district16 = "16";
            if ($value == "17") $district17 = "17";
            if ($value == "18") $district18 = "18";
            if ($value == "19") $district19 = "19";
            if ($value == "20") $district20 = "20";
            if ($value == "21") $district21 = "21";
            if ($value == "22") $district22 = "22";
            if ($value == "23") $district23 = "23";
            if ($value == "24") $district24 = "24";
            if ($value == "25") $district25 = "25";
            if ($value == "26") $district26 = "26";
            if ($value == "27") $district27 = "27";
            if ($value == "28") $district28 = "28";
            if ($value == "29") $district29 = "29";
            if ($value == "30") $district30 = "30";
            if ($value == "31") $district31 = "31";
            if ($value == "32") $district32 = "32";
            if ($value == "33") $district33 = "33";
            if ($value == "34") $district34 = "34";
            if ($value == "35") $district35 = "35";
            if ($value == "36") $district36 = "36";
            if ($value == "37") $district37 = "37";
            if ($value == "38") $district38 = "38";
            if ($value == "39") $district39 = "39";
            if ($value == "40") $district40 = "40";
            if ($value == "41") $district41 = "41";
            if ($value == "42") $district42 = "42";
            if ($value == "43") $district43 = "43";
            if ($value == "44") $district44 = "44";
            if ($value == "45") $district45 = "45";
            if ($value == "46") $district46 = "46";
        }
    }
    else {
        $district = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46");
    }
    if (isset($_POST['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
    if (isset($_POST['floor'])) $floor = htmlspecialchars($_POST['floor']);
    if (isset($_POST['furniture'])) $furniture = htmlspecialchars($_POST['furniture']);
    if (isset($_POST['minCost']) && $_POST['minCost'] != "") $minCost = htmlspecialchars($_POST['minCost']); else $minCost = "0";
    if (isset($_POST['maxCost']) && $_POST['maxCost'] != "") $maxCost = htmlspecialchars($_POST['maxCost']); else $maxCost = "99999999";
    if (isset($_POST['pledge']) && $_POST['pledge'] != "") $pledge = htmlspecialchars($_POST['pledge']); else $pledge = "99999999";
    if (isset($_POST['withWho'])) $withWho = htmlspecialchars($_POST['withWho']);
    if (isset($_POST['linksToFriends'])) $linksToFriends = htmlspecialchars($_POST['linksToFriends']);
    if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']);
    if (isset($_POST['howManyChildren'])) $howManyChildren = htmlspecialchars($_POST['howManyChildren']);
    if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']);
    if (isset($_POST['howManyAnimals'])) $howManyAnimals = htmlspecialchars($_POST['howManyAnimals']);
    if (isset($_POST['period'])) $period = htmlspecialchars($_POST['period']);
    if (isset($_POST['additionalDescriptionOfSearch'])) $additionalDescriptionOfSearch = htmlspecialchars($_POST['additionalDescriptionOfSearch']);

    // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
    $errors = userDataCorrect("validateSearchRequest"); // Параметр createSearchRequest задает режим проверки "Создание запроса на поиск", который активирует только соответствующие ему проверки
    if (count($errors) == 0) $correctNewSearchRequest = "true"; else $correctNewSearchRequest = "false"; // Считаем ошибки, если 0, то можно выдать пользователю форму для ввода параметров Запроса поиска

    // Если данные верны, сохраним их в БД
    if ($correctNewSearchRequest == "true") {
        $amountOfRoomsSerialized = serialize($amountOfRooms);
        $districtSerialized = serialize($district);

        if ($typeTenant == "true") {
            $rez = mysql_query("UPDATE searchrequests SET
            typeOfObject='" . $typeOfObject ."',
            amountOfRooms='" . $amountOfRoomsSerialized ."',
            adjacentRooms='" . $adjacentRooms ."',
            floor='" . $floor ."',
            furniture='" . $furniture ."',
            minCost='" . $minCost ."',
            maxCost='" . $maxCost ."',
            pledge='" . $pledge ."',
            district='" . $districtSerialized ."',
            withWho='" . $withWho ."',
            linksToFriends='" . $linksToFriends ."',
            children='" . $children ."',
            howManyChildren='" . $howManyChildren ."',
            animals='" . $animals ."',
            howManyAnimals='" . $howManyAnimals ."',
            period='" . $period ."',
            additionalDescriptionOfSearch='" . $additionalDescriptionOfSearch ."'
            WHERE userId = '" . $rowUsers['id'] . "'");
        } else {
            $rez = mysql_query("INSERT INTO searchrequests SET
            userId='" . $rowUsers['id'] ."',
            typeOfObject='" . $typeOfObject ."',
            amountOfRooms='" . $amountOfRoomsSerialized ."',
            adjacentRooms='" . $adjacentRooms ."',
            floor='" . $floor ."',
            furniture='" . $furniture ."',
            minCost='" . $minCost ."',
            maxCost='" . $maxCost ."',
            pledge='" . $pledge ."',
            district='" . $districtSerialized ."',
            withWho='" . $withWho ."',
            linksToFriends='" . $linksToFriends ."',
            children='" . $children ."',
            howManyChildren='" . $howManyChildren ."',
            animals='" . $animals ."',
            howManyAnimals='" . $howManyAnimals ."',
            period='" . $period ."',
            additionalDescriptionOfSearch='" . $additionalDescriptionOfSearch ."'");
        }

        $rez = mysql_query("UPDATE users SET typeTenant='true' WHERE login = '" . $login . "'");
        $typeTenant = "true";
    }
}

/********************************************************************************
 * ЗАПРОС НА СОЗДАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь нажал на кнопку Формирования поискового запроса
 *******************************************************************************/

// Проверяем: захотел ли пользователь добавить поисковый запрос. На этом месте мы можем быть уверены, что пользователь является только собственником, но не является пока арендатором, лишь собирается им стать (для чего он и хочет сформировать поисковый запрос)
if (isset($_POST['createSearchRequestButton'])) {
    // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
    $errors = userDataCorrect("createSearchRequest"); // Параметр createSearchRequest задает режим проверки "Создание запроса на поиск", который активирует только соответствующие ему проверки
    if (count($errors) == 0) $correct = "true"; else $correct = "false"; // Считаем ошибки, если 0, то можно выдать пользователю форму для ввода параметров Запроса поиска
}

?>
<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!-- Consider specifying the language of your content by adding the `lang` attribute to <html> -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Личный кабинет</title>
    <meta name="description" content="Личный кабинет пользователя">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/fileuploader.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Стили для создания нового Объявления*/
        .actionChangeStatusAdvert {
            float: right;
            margin-top: -0.1em;
            margin-right: 4px;
            font-weight: normal;
            font-size: 0.8em;
        }

        #addressForm {
            clear: both;
        }

        .inputItem, .objectDescriptionItem {
            width: 100%;
            margin-top: 7px;
            margin-bottom: 7px;
        }

        .inputItem .label, .objectDescriptionItem .objectDescriptionItemLabel {
            min-width: 150px;
            width: 49%;
            text-align: right;
            display: inline-block;
            vertical-align: top;
        }

        .objectDescriptionItem .objectDescriptionBody {
            display: inline-block;
            width: 49%;
        }

        .objectDescriptionBody.furniture {
            min-width: 300px;
        }

        table td {
            vertical-align: middle;
            padding: 5px;
        }

        #newAdvertButton {
            margin-bottom: 10px;
        }

        .advertHeader {
            border: 1px solid #AAAAAA;
            padding: 5px;
            background-color: #B5B5B5;
            font-size: 1.2em;
            border-radius: 5px 5px 0 0;
        }

        .advertHeaderStatus {
            color: red;
            display: inline-block;
        }

        .advertDescriptionEdit {
            border: 1px solid #AAAAAA;
            border-radius: 0 0 5px 5px;
            padding: 10px;
        }

        .advertDescriptionChapterHeader {
            background-color: #8e4a15;
            font-size: 1.2em;
            padding-left: 5px;
            border-radius: 5px;
            color: white;
        }
    </style>
</head>

<body>
<div class="page_without_footer">

<!-- Всплывающее поле для отображения списка ошибок, полученных при проверке данных на сервере (PHP)-->
<div id="userMistakesBlock" class="ui-widget">
    <div class="ui-state-highlight ui-corner-all">
        <div>
            <p>
                <span class="icon-mistake ui-icon ui-icon-info"></span>
                <span id="userMistakesText">Для продолжения, пожалуйста, дополните или исправьте следующие данные:</span>
            </p>
            <ol><?php
                if (isset($errors) && count($errors) != 0) {
                    foreach ($errors as $value) {
                        echo "<li>$value</li>";
                    }
                }
                ?></ol>
        </div>
    </div>
</div>

<!-- Сформируем и вставим заголовок страницы -->
<?php
include("header.php");
?>

<div class="page_main_content">
<div class="wrapperOfTabs">
<div class="headerOfPage">
    Личный кабинет
</div>
<div id="tabs">
<ul>
    <li>
        <a href="#tabs-1">Профиль</a>
    </li>
    <li>
        <a href="#tabs-2">Новости (<span id="amountUnreadNews">12</span>)</a>
    </li>
    <li>
        <a href="#tabs-3">Мои объявления</a>
    </li>
    <li>
        <a href="#tabs-4">Поиск</a>
    </li>
    <li>
        <a href="#tabs-5">Избранное</a>
    </li>
</ul>
<div id="tabs-1">
<?php if ($correctNewProfileParameters != "false"): ?> <!-- Блок с нередактируемыми параметрами Профайла не выдается только в 1 случае: если пользователь корректировал свои параметры, и они не прошли проверку -->
<div id="notEditingProfileParametersBlock">
    <div class="setOfInstructions">
        <a href="#">редактировать</a>
        <br>
    </div>
    <div class="fotosWrapper">
        <?php
        if (isset($rowUserFotos['id']) && isset($rowUserFotos['extension'])) echo "<div class='bigFotoWrapper'><img class='bigFoto' src='uploaded_files/" . $rowUserFotos['id'] . "." . $rowUserFotos['extension'] . "'></div>";
        ?>
    </div>
    <div class="profileInformation">
        <ul class="listDescription">
            <li>
                <span
                    class="FIO"><?php echo $surname . " " . $name . " " . $secondName?></span>
            </li>
            <li>
                <br>
            </li>
            <li>
                <span class="headOfString">Образование:</span> <?php
                if ($currentStatusEducation == "0") {
                    echo "";
                }
                if ($currentStatusEducation == "withoutEducation") {
                    echo "нет";
                }
                if ($currentStatusEducation == "learningNow") {
                    if (isset($almamater)) echo $almamater . ", ";
                    if (isset($speciality)) echo $speciality . ", ";
                    if (isset($ochnoZaochno)) {
                        if ($ochnoZaochno == "ochno") echo "очно, "; else echo "заочно, ";
                    }
                    if (isset($kurs)) echo $kurs;
                }
                if ($currentStatusEducation == "finishedEducation") {
                    if (isset($almamater)) echo $almamater . ", ";
                    if (isset($speciality)) echo $speciality . ", ";
                    if (isset($ochnoZaochno)) {
                        if ($ochnoZaochno == "ochno") echo "очно, "; else echo "заочно, ";
                    }
                    if (isset($yearOfEnd)) echo "закончил в " . $yearOfEnd . " году";
                }
                ?>
            </li>
            <li>
                <span class="headOfString">Работа:</span> <?php
                if ($notWorkCheckbox == "isNotWorking") {
                    echo "не работаю";
                }
                else {
                    if (isset($placeOfWork) && $placeOfWork != "") {
                        echo $placeOfWork . ", ";
                    }
                    if (isset($workPosition)) {
                        echo $workPosition;
                    }
                }
                ?>
            </li>
            <li>
                <span class="headOfString">Национальность:</span> <?php
                if ($nationality == "russian") {
                    echo "русский";
                }
                if ($nationality == "west") {
                    echo "европеец, американец";
                }
                if ($nationality == "east") {
                    echo "СНГ, восточная нац-сть";
                }
                ?>
            </li>
            <li>
                <span class="headOfString">Пол:</span> <?php
                if ($sex == "man") {
                    echo "мужской";
                }
                if ($sex == "woman") {
                    echo "женский";
                }
                ?>
            </li>
            <li>
                <span class="headOfString">День рождения:</span> <?php
                if (isset($birthday)) echo $birthday;
                ?>
            </li>
            <li>
                <span class="headOfString">Возраст:</span> <?php
                $date = substr($birthday, 8, 2);
                $month = substr($birthday, 5, 2);
                $year = substr($birthday, 0, 4);
                $birthdayForAge = mktime(0, 0, 0, $month, $date, $year);
                $currentDate = time();
                echo date_interval_format(date_diff(new DateTime("@{$currentDate}"), new DateTime("@{$birthdayForAge}")), '%y');
                ?>
            </li>
            <li>
                <br>
            </li>
            <li>
                <span style="font-weight: bold;">Контакты:</span>
            </li>
            <li>
                <span class="headOfString">E-mail:</span> <?php
                if (isset($email)) echo $email;
                ?>
            </li>
            <li>
                <span class="headOfString">Телефон:</span> <?php
                if (isset($telephon)) echo $telephon;
                ?>
            </li>
            <li>
                <br>
            </li>
            <li>
                <span style="font-weight: bold;">Малая Родина:</span>
            </li>
            <li>
                <span class="headOfString">Город (населенный пункт):</span> <?php
                if (isset($cityOfBorn)) echo $cityOfBorn;
                ?>
            </li>
            <li>
                <span class="headOfString">Регион:</span> <?php
                if (isset($regionOfBorn)) echo $regionOfBorn;
                ?>
            </li>
            <li>
                <br>
            </li>
            <li>
                <span style="font-weight: bold;">Коротко о себе и своих интересах:</span>
            </li>
            <li>
                <?php
                if (isset($shortlyAboutMe)) echo $shortlyAboutMe;
                ?>
            </li>
            <li>
                <br>
            </li>
            <li>
                <span style="font-weight: bold;">Страницы в социальных сетях:</span>
            </li>
            <li>
                <ul class="linksToAccounts">
                    <?php
                    if (isset($vkontakte)) echo "<li><a href='" . $vkontakte . "'>" . $vkontakte . "</a></li>";
                    ?>
                    <?php
                    if (isset($odnoklassniki)) echo "<li><a href='" . $odnoklassniki . "'>" . $odnoklassniki . "</a></li>";
                    ?>
                    <?php
                    if (isset($facebook)) echo "<li><a href='" . $facebook . "'>" . $facebook . "</a></li>";
                    ?>
                    <?php
                    if (isset($twitter)) echo "<li><a href='" . $twitter . "'>" . $twitter . "</a></li>";
                    ?>
                </ul>
            </li>
        </ul>
    </div>
</div>
<?php endif; ?>
<form method="post" name="profileParameters" id="editingProfileParametersBlock" class="descriptionFieldsetsWrapper"
      style='<?php if ($correctNewProfileParameters != "false") echo "display: none;"?>'>
<div class="descriptionFieldsetsWrapper">
    <fieldset class="edited private">
        <legend>
            ФИО
        </legend>
        <div class="searchItem">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Имя: </span>

            <div class="searchItemBody">
                <input name="name" type="text" size="38" autofocus
                       validations="validate[required]" <?php echo "value='$name'";?>>
            </div>
        </div>
        <div class="searchItem">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Отчество: </span>

            <div class="searchItemBody">
                <input name="secondName" type="text" size="33"
                       validations="validate[required]" <?php echo "value='$secondName'";?>>
            </div>
        </div>
        <div class="searchItem">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Фамилия: </span>

            <div class="searchItemBody">
                <input name="surname" type="text" size="33"
                       validations="validate[required]" <?php echo "value='$surname'";?>>
            </div>
        </div>
        <div class="searchItem">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Пол: </span>

            <div class="searchItemBody">
                <select name="sex" validations="validate[required]">
                    <option value="0" <?php if ($sex == "0") echo "selected";?>></option>
                    <option value="man" <?php if ($sex == "man") echo "selected";?>>мужской</option>
                    <option value="woman" <?php if ($sex == "woman") echo "selected";?>>женский</option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Национальность: </span>

            <div class="searchItemBody">
                <select name="nationality" validations="validate[required]">
                    <option value="0" <?php if ($nationality == "0") echo "selected";?>></option>
                    <option value="russian" <?php if ($nationality == "russian") echo "selected";?>>русский</option>
                    <option value="west" <?php if ($nationality == "west") echo "selected";?>>европеец, американец
                    </option>
                    <option value="east" <?php if ($nationality == "east") echo "selected";?>>СНГ, восточная нац-сть
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">День рождения: </span>

            <div class="searchItemBody">
                <input name="birthday" type="text" id="datepicker" size="15"
                       placeholder="дд.мм.гггг" <?php echo "value='$birthday'";?>>
            </div>
        </div>
    </fieldset>

    <div style="display: inline-block; vertical-align: top;">
        <fieldset class="edited private" style="display: block;">
            <legend>
                Логин и пароль
            </legend>
            <div class="searchItem" title="Используйте в качестве логина ваш e-mail или телефон">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Логин: </span>

                <div class="searchItemBody">
                    <input type="text" size="30" maxlength="50" name="login" placeholder="e-mail или номер телефона"
                           validations="validate[required]" <?php echo "value='$login'";?>>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Пароль: </span>

                <div class="searchItemBody">
                    <input type="password" size="29" maxlength="50"
                           name="password" validations="validate[required]" <?php echo "value='$password'";?>>
                </div>
            </div>
        </fieldset>

        <fieldset class="edited private" style="display: block;">
            <legend>
                Контакты
            </legend>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Телефон: </span>

                <div class="searchItemBody">
                    <input name="telephon" type="text" size="27"
                           validations="validate[required,custom[telephone]]" <?php echo "value='$telephon'";?>>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    <?php if ($typeTenant == "true") {
                    echo "*";
                } ?>
                </div>
                <span class="searchItemLabel">e-mail: </span>

                <div class="searchItemBody">
                    <input name="email" type="text" size="30" <?php if ($typeTenant == "true") {
                        echo "validations='validate[required,custom[email]]'";
                    } echo "value='$email'"; ?>>
                </div>
            </div>
        </fieldset>
    </div>

    <fieldset class="edited private" style="min-width: 300px;">
        <legend title="Для успешной регистрации должна быть загружена хотя бы 1 фотография">
            <div class="required">
                <?php if ($typeTenant == "true") {
                echo "*";
            } ?>
            </div>
            Фотографии
        </legend>
        <input type="hidden" name="fileUploadId" id="fileUploadId" <?php echo "value='$fileUploadId'";?>>
        <?php
        // Получаем информацию о всех загруженных фото и формируем для каждого свой input type hidden для передачи данных в обработчик яваскрипта
        if ($rez = mysql_query("SELECT * FROM userFotos WHERE userId = '" . $rowUsers['id'] . "'")) // ищем уже загруженные пользователем фотки
        {
            $numUploadedFiles = mysql_num_rows($rez);
            for ($i = 0; $i < $numUploadedFiles; $i++) {
                $row = mysql_fetch_assoc($rez);
                echo "<input type='hidden' class='uploadedFoto' filename='" . $row['filename'] . "' filesizeMb='" . $row['filesizeMb'] . "'>";
            }
        }
        if ($rez = mysql_query("SELECT * FROM tempFotos WHERE fileuploadid = '" . $fileUploadId . "'")) // ищем уже загруженные пользователем фотки
        {
            $numUploadedFiles = mysql_num_rows($rez);
            for ($i = 0; $i < $numUploadedFiles; $i++) {
                $row = mysql_fetch_assoc($rez);
                echo "<input type='hidden' class='uploadedFoto' filename='" . $row['filename'] . "' filesizeMb='" . $row['filesizeMb'] . "'>";
            }
        }
        ?>
        <div id="file-uploader">
            <noscript>
                <p>Пожалуйста, активируйте JavaScript для загрузки файлов</p>
                <!-- or put a simple form for upload here -->
            </noscript>
        </div>
    </fieldset>

</div>
<!-- /end.descriptionFieldsetsWrapper -->
<fieldset class="edited private">
    <legend>
        Образование
    </legend>
    <div class="searchItem">
        <div class="required">
            <?php if ($typeTenant == "true") {
            echo "*";
        } ?>
        </div>
        <span class="searchItemLabel">Текущий статус: </span>

        <div class="searchItemBody">
            <select name="currentStatusEducation" id="currentStatusEducation" <?php if ($typeTenant == "true") {
                echo "validations='validate[required]'";
            } ?>>
                <option value="0" <?php if ($currentStatusEducation == "0") echo "selected";?>></option>
                <option
                    value="withoutEducation" <?php if ($currentStatusEducation == "withoutEducation") echo "selected";?>>
                    Нигде не учился
                </option>
                <option value="learningNow" <?php if ($currentStatusEducation == "learningNow") echo "selected";?>>
                    Сейчас учусь
                </option>
                <option
                    value="finishedEducation" <?php if ($currentStatusEducation == "finishedEducation") echo "selected";?>>
                    Закончил
                </option>
            </select>
        </div>
    </div>
    <div id="almamater" class="searchItem ifLearned"
         title="Укажите учебное заведение, в котором учитесь сейчас, либо последнее из тех, что заканчивали">
        <div class="required">
        </div>
        <span class="searchItemLabel">Учебное заведение: </span>

        <div class="searchItemBody">
            <input name="almamater" class="ifLearned" type="text" size="50" <?php echo "value='$almamater'";?>>
        </div>
    </div>
    <div id="speciality" class="searchItem ifLearned">
        <div class="required">
        </div>
        <span class="searchItemLabel">Специальность: </span>

        <div class="searchItemBody">
            <input name="speciality" class="ifLearned" type="text" size="55" <?php echo "value='$speciality'";?>>
        </div>
    </div>
    <div id="kurs" class="searchItem ifLearned" title="Укажите курс, на котором учитесь">
        <div class="required">
        </div>
        <span class="searchItemLabel">Курс: </span>

        <div class="searchItemBody">
            <input name="kurs" class="ifLearned" type="text" size="19" <?php echo "value='$kurs'";?>>
        </div>
    </div>
    <div id="formatEducation" class="searchItem ifLearned" title="Укажите форму обучения">
        <div class="required">
        </div>
        <span class="searchItemLabel">Очно / Заочно: </span>

        <div class="searchItemBody">
            <select name="ochnoZaochno" class="ifLearned">
                <option value="0" <?php if ($ochnoZaochno == "0") echo "selected";?>></option>
                <option value="ochno" <?php if ($ochnoZaochno == "ochno") echo "selected";?>>Очно</option>
                <option value="zaochno" <?php if ($ochnoZaochno == "zaochno") echo "selected";?>>Заочно</option>
            </select>
        </div>
    </div>
    <div id="yearOfEnd" class="searchItem ifLearned" title="Укажите год окончания учебного заведения">
        <div class="required">
        </div>
        <span class="searchItemLabel">Год окончания: </span>

        <div class="searchItemBody">
            <input name="yearOfEnd" class="ifLearned" type="text" size="9" <?php echo "value='$yearOfEnd'";?>>
        </div>
    </div>
</fieldset>

<fieldset class="edited private">
    <legend>
        Работа
    </legend>
    <div>
        <input type="checkbox" name="notWorkCheckbox" value="isNotWorking"
               id="notWorkCheckbox" <?php if ($notWorkCheckbox == "isNotWorking") echo "checked";?>>
        Я не работаю
    </div>
    <div class="searchItem ifWorked">
        <div class="required">
        </div>
        <span class="searchItemLabel">Место работы: </span>

        <div class="searchItemBody">
            <input name="placeOfWork" class="ifWorked" type="text" size="30" <?php echo "value='$placeOfWork'";?>>
        </div>
    </div>
    <div class="searchItem ifWorked">
        <div class="required">
        </div>
        <span class="searchItemLabel">Должность: </span>

        <div class="searchItemBody">
            <input name="workPosition" class="ifWorked" type="text" size="33" <?php echo "value='$workPosition'";?>>
        </div>
    </div>
</fieldset>

<fieldset class="edited private">
    <legend>
        Коротко о себе
    </legend>
    <div class="searchItem">
        <div class="required">
            <?php if ($typeTenant == "true") {
            echo "*";
        } ?>
        </div>
        <span class="searchItemLabel">В каком регионе родились: </span>

        <div class="searchItemBody">
            <input name="regionOfBorn" type="text" size="42" <?php if ($typeTenant == "true") {
                echo "validations='validate[required]'";
            } echo "value='$regionOfBorn'";?>>
        </div>
    </div>
    <div class="searchItem">
        <div class="required">
            <?php if ($typeTenant == "true") {
            echo "*";
        } ?>
        </div>
        <span class="searchItemLabel">Родной город, населенный пункт: </span>

        <div class="searchItemBody">
            <input name="cityOfBorn" type="text" size="36" <?php if ($typeTenant == "true") {
                echo "validations='validate[required]'";
            } echo "value='$cityOfBorn'";?>>
        </div>
    </div>
    <div class="searchItem">
        <div class="required"></div>
        <span class="searchItemLabel">Коротко о себе и своих интересах: </span>
    </div>
    <div class="searchItem">
        <div class="required"></div>
        <textarea name="shortlyAboutMe" cols="71" rows="4"><?php echo $shortlyAboutMe;?></textarea>
    </div>
</fieldset>
<fieldset class="edited private">
    <legend>
        Страницы в социальных сетях
    </legend>
    <div class="searchItem"
         title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
        <div class="required"></div>
        <img src="img/vkontakte.jpg">

        <div class="searchItemBody">
            <input type="text" name="vkontakte" size="62"
                   placeholder="http://vk.com/..." <?php echo "value='$vkontakte'";?>>
        </div>
    </div>
    <div class="searchItem"
         title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
        <div class="required"></div>
        <img src="img/odnoklassniki.png">

        <div class="searchItemBody">
            <input type="text" name="odnoklassniki" size="68"
                   placeholder="http://www.odnoklassniki.ru/profile/..." <?php echo "value='$odnoklassniki'";?>>
        </div>
    </div>
    <div class="searchItem"
         title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
        <div class="required"></div>
        <img src="img/facebook.jpg">

        <div class="searchItemBody">
            <input type="text" name="facebook" size="71"
                   placeholder="https://www.facebook.com/profile.php?..." <?php echo "value='$facebook'";?>>
        </div>
    </div>
    <div class="searchItem"
         title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
        <div class="required"></div>
        <img src="img/twitter.png">

        <div class="searchItemBody">
            <input type="text" name="twitter" size="62"
                   placeholder="https://twitter.com/..." <?php echo "value='$twitter'";?>>
        </div>
    </div>
</fieldset>
<div class="clearBoth"></div>
<div class="bottomButton">
    <a href="personal.php" style="margin-right: 10px;">Отмена</a>
    <button type="submit" name="saveProfileParameters" id="saveProfileParameters" class="button">
        Сохранить
    </button>
</div>
<div class="clearBoth"></div>
</form>
<!-- /end.descriptionFieldsetsWrapper -->
<div class="clearBoth"></div>
</div>
<!-- /end.tabs-1 -->
<div id="tabs-2">
<div class="shadowText">
    На этой вкладке располагается информация о важных событиях, случившихся на ресурсе Хани Хом, как например: появление
    новых потенциальных арендаторов, заинтересовавшихся Вашим объявлением, или новых объявлений, которые подходят под
    Ваш запрос
</div>
<div class="news unread">
    <div class="newsHeader">
        Претендент на квартиру по адресу: улица Сибирский тракт 50 летия 107, кв 70.
        <div class="actionReaded">
            <a href="#">прочитал</a>
        </div>
        <div class="clearBoth"></div>
    </div>

    <div class="fotosWrapper">
        <div class="middleFotoWrapper">
            <img class="middleFoto" src="">
        </div>
    </div>
    <ul class="setOfInstructions">
        <li>
            <a href="#">подробнее</a>
        </li>
    </ul>
    <ul class="listDescription">
        <li>
            <span class="headOfString">ФИО:</span>
            Ушаков Дмитрий Владимирович
        </li>
        <li>
            <span class="headOfString">Возраст:</span>
            25
        </li>
        <li>
            <span class="headOfString">Срок аренды:</span>
            долгосрочно
        </li>
        <li>
            <span class="headOfString">С кем жить:</span>
            несемейная пара
        </li>
        <li>
            <span class="headOfString">Дети:</span>
            нет
        </li>
        <li>
            <span class="headOfString">Животные:</span>
            нет
        </li>
        <li>
            <span class="headOfString">Телефон:</span>
            89221431615
        </li>
    </ul>
    <div class="clearBoth"></div>
</div>
<div class="news unread">
    <div class="newsHeader">
        Изменение статуса объявления
        <div class="actionReaded">
            <a href="#">прочитал</a>
        </div>
        <div class="clearBoth"></div>
    </div>
    <div class="fotosWrapper">
        <div class="middleFotoWrapper">
            <img class="middleFoto" src="">
        </div>
    </div>
    <ul class="setOfInstructions">
        <li>
            <a href="#">подробнее</a>
        </li>
    </ul>
    <ul class="listDescription">
        <li>
            <span class="headOfString">Адрес объекта:</span>
            улица Шаумяна 107, кв 70
        </li>
        <li>
            <span class="headOfString">Статус изменен на:</span>
            <span style="color: green">объявление опубликовано</span>
        </li>
        <li>
            <span class="headOfString">Дата:</span>
            25.09.2012
        </li>
        <li>
            <span class="headOfString">Комментарий к статусу:</span>
            объявление опубликовано на ресурсе Хани Хом, а также поставлено в очередь на автоматическую ежедневную
            публикацию на основных интернет-порталах города. Это обеспечит максимальный приток арендаторов, из которых
            Вы сможете выбрать наиболее ответственных и надежных
        </li>
    </ul>
    <div class="clearBoth"></div>
</div>
<div class="news">
    <div class="newsHeader">
        Претендент на квартиру по адресу: улица Сибирский тракт 50 летия 107, кв 70.
    </div>
    <div class="fotosWrapper">
        <div class="middleFotoWrapper">
            <img class="middleFoto" src="">
        </div>
    </div>
    <ul class="setOfInstructions">
        <li>
            <a href="#">подробнее</a>
        </li>
    </ul>
    <ul class="listDescription">
        <li>
            <span class="headOfString">ФИО:</span>
            Ушаков Дмитрий Владимирович
        </li>
        <li>
            <span class="headOfString">Возраст:</span>
            25
        </li>
        <li>
            <span class="headOfString">Срок аренды:</span>
            долгосрочно
        </li>
        <li>
            <span class="headOfString">С кем жить:</span>
            несемейная пара
        </li>
        <li>
            <span class="headOfString">Дети:</span>
            нет
        </li>
        <li>
            <span class="headOfString">Животные:</span>
            нет
        </li>
        <li>
            <span class="headOfString">Телефон:</span>
            89221431615
        </li>
    </ul>
    <div class="clearBoth"></div>
</div>
<div class="news">
    <div class="newsHeader">
        Новое предложение по Вашему поиску
    </div>
    <div class="fotosWrapper">
        <div class="middleFotoWrapper">
            <img class="middleFoto" src="">
        </div>
    </div>
    <ul class="setOfInstructions">
        <li>
            <a href="#">подробнее</a>
        </li>
        <li>
            <a href="#">посмотреть на карте</a>
        </li>
    </ul>
    <ul class="listDescription">
        <li>
            <span class="headOfString">Тип:</span> Квартира
        </li>
        <li>
            <span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
        </li>
        <li>
            <span class="headOfString">Единовременная комиссия:</span>
            <a href="#"> 3000 руб. (40%) собственнику</a>
        </li>
        <li>
            <span class="headOfString">Адрес:</span>
            улица Посадская 51
        </li>
        <li>
            <span class="headOfString">Количество комнат:</span>
            2, смежные
        </li>
        <li>
            <span class="headOfString">Площадь (жилая/общая):</span>
            22.4/34 м²
        </li>
        <li>
            <span class="headOfString">Этаж:</span>
            3 из 10
        </li>
        <li>
            <span class="headOfString">Срок сдачи:</span>
            долгосрочно
        </li>
        <li>
            <span class="headOfString">Мебель:</span>
            есть
        </li>
        <li>
            <span class="headOfString">Район:</span>
            Центр
        </li>
        <li>
            <span class="headOfString">Телефон собственника:</span>
            <a href="#">показать</a>
        </li>
    </ul>
    <div class="clearBoth"></div>
</div>
</div>
<div id="tabs-3">
<button id="newAdvertButton">
    Новое объявление
</button>
<div id="modalWindowNewAdvert">
<form name="advert0" class="advertDescriptionEdit">
<div class="advertDescriptionChapter">
    <div class="advertDescriptionChapterHeader">
        Описание объекта
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Тип объекта:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfObject">
                <option value="0" selected></option>
                <option value="flat">квартира</option>
                <option value="room">комната</option>
                <option value="house">дом, коттедж</option>
                <option value="townhouse">таунхаус</option>
                <option value="dacha">дача</option>
                <option value="garage">гараж</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            С какого числа можно въезжать:
        </div>
        <div class="objectDescriptionBody">
            Выбор даты
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            На какой срок сдается:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="termOfLease" value="">
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Количество комнат в квартире, доме:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfRooms">
                <option value="0" selected></option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6 и более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Комнаты смежные:
        </div>
        <div class="objectDescriptionBody">
            <select name="adjacentRooms">
                <option value="0" selected></option>
                <option value="1">нет</option>
                <option value="2">да</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Санузел:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBathrooms">
                <option value="0" selected></option>
                <option value="1">раздельный</option>
                <option value="2">совмещенный</option>
                <option value="3">2</option>
                <option value="4">3</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Балкон/лоджия:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBalcony">
                <option value="0" selected></option>
                <option value="1">нет</option>
                <option value="2">балкон</option>
                <option value="3">лоджия</option>
                <option value="4">балкон и лоджия</option>
                <option value="5">балкон и эркер</option>
                <option value="6">2 балкона и более</option>
                <option value="7">2 лоджии и более</option>
                <option value="8">2 эркера и более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Остекление балкона/лоджии:
        </div>
        <div class="objectDescriptionBody">
            <select name="glazed">
                <option value="0" selected></option>
                <option value="1">нет</option>
                <option value="2">да</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Общая площадь:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="totalАrea" value="">
            м²
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Жилая площадь:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="livingSpace" value="">
            м²
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Этаж:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="3" name="floor" value="">
            из
            <input type="text" size="3" name="totalAmountFloor" value="">
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Консьерж:
        </div>
        <div class="objectDescriptionBody">
            <select name="concierge">
                <option value="0" selected></option>
                <option value="1">есть</option>
                <option value="2">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Домофон:
        </div>
        <div class="objectDescriptionBody">
            <select name="intercom">
                <option value="0" selected></option>
                <option value="1">есть и работает</option>
                <option value="2">есть, но не работает</option>
                <option value="3">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Парковка:
        </div>
        <div class="objectDescriptionBody">
            <select name="parking">
                <option value="0" selected></option>
                <option value="1">стихийная</option>
                <option value="2">охраняемая</option>
                <option value="3">неохраняемая</option>
                <option value="4">подземная</option>
                <option value="5">отсутствует</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Фотографии (вид из окна, двор и дом, каждая из комнат, ванна, туалет, кухня)
        </div>
        <div class="objectDescriptionBody"></div>
    </div>
</div>

<div class="advertDescriptionChapter" id="addressChapter">
    <div class="advertDescriptionChapterHeader">
        Местоположение
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Город:
        </div>
        <div class="objectDescriptionBody">
            <span> Екатеринбург</span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Район:
        </div>
        <div class="objectDescriptionBody">
            <select name="district">
                <option value="0" selected></option>
                <option value="1">Автовокзал (южный)</option>
                <option value="2">Академический</option>
                <option value="3">Ботанический</option>
                <option value="4">ВИЗ</option>
                <option value="5">Вокзальный</option>
                <option value="6">Втузгородок</option>
                <option value="7">Горный щит</option>
                <option value="8">Елизавет</option>
                <option value="9">ЖБИ</option>
                <option value="10">Завокзальный</option>
                <option value="11">Заречный</option>
                <option value="12">Изоплит</option>
                <option value="13">Исток</option>
                <option value="14">Калиновский</option>
                <option value="15">Кольцово</option>
                <option value="16">Компрессорный</option>
                <option value="17">Лечебный</option>
                <option value="18">Медный</option>
                <option value="19">Нижнеисетский</option>
                <option value="20">Парковый</option>
                <option value="21">Пионерский</option>
                <option value="22">Птицефабрика</option>
                <option value="23">Семь ключей</option>
                <option value="24">Сибирский тракт</option>
                <option value="25">Синие камни</option>
                <option value="26">Совхозный</option>
                <option value="27">Сортировка новая</option>
                <option value="28">Сортировка старая</option>
                <option value="29">Уктус</option>
                <option value="30">УНЦ</option>
                <option value="31">Уралмаш</option>
                <option value="32">Химмаш</option>
                <option value="33">Центр</option>
                <option value="34">Чермет</option>
                <option value="35">Шарташ</option>
                <option value="36">Широкая речка</option>
                <option value="37">Эльмаш</option>
                <option value="38">Юго-запад</option>
                <option value="39">За городом</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Улица и номер дома:
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <table>
                <tbody>
                <tr>
                    <td>
                        <input type="text" name="address" id="addressTextBox" size="30" value="">
                        <input type="button" value="Проверить адрес" id="checkAddressButton">
                    </td>
                </tr>
                <tr>
                    <td><!-- Карта Яндекса -->
                        <div id="mapForNewAdvert" style="width: 400px; height: 400px; margin-top: 8px;"></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Номер квартиры:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="apartment number" size="7" value="">
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Станция метро рядом:
        </div>
        <div class="objectDescriptionBody">
            <select name="subwayStation">
                <option value="0" selected></option>
                <option value="10">Нет</option>
                <option value="1">Проспект Космонавтов</option>
                <option value="2">Уралмаш</option>
                <option value="3">Машиностроителей</option>
                <option value="4">Уральская</option>
                <option value="5">Динамо</option>
                <option value="6">Площадь 1905 г.</option>
                <option value="7">Геологическая</option>
                <option value="8">Чкаловская</option>
                <option value="9">Ботаническая</option>
            </select>
            <input type="text" name="distanceToMetroStation" size="4" value="">
            мин.ходьбы
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="costChapter">
    <div class="advertDescriptionChapterHeader">
        Стоимость, условия оплаты
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Плата за аренду:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="costOfRenting" size="7" value="">
            руб. в месяц
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Коммунальные услуги оплачиваются арендатором дополнительно:
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <select name="utilities">
                <option value="0" selected></option>
                <option value="1">да</option>
                <option value="2">нет</option>
            </select>
            Летом
            <input type="text" name="costInSummer" size="7" value="">
            руб. Зимой
            <input type="text" name="costInWinter" size="7" value="">
            руб.
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Электроэнергия оплачивается дополнительно:
        </div>
        <div class="objectDescriptionBody">
            <select name="electricPower">
                <option value="0" selected></option>
                <option value="1">да</option>
                <option value="2">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Залог:
        </div>
        <div class="objectDescriptionBody">
            <select name="bail">
                <option value="0" selected></option>
                <option value="1">есть</option>
                <option value="2">нет</option>
            </select>
            <input type="text" name="bailCost" size="7" value="">
            руб.
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="currentStatus">
    <div class="advertDescriptionChapterHeader">
        Текущее состояние
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Ремонт:
        </div>
        <div class="objectDescriptionBody">
            <select name="repair">
                <option value="0" selected></option>
                <option value="1">не выполнялся (новый дом)</option>
                <option value="2">больше года назад</option>
                <option value="3">меньше 1 года назад</option>
                <option value="4">сделан только что</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Отделка (жилых помещений):
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <select name="furnish">
                <option value="0" selected></option>
                <option value="1">евростандарт</option>
                <option value="2">косметическая (новые обои, побелка потолков)</option>
                <option value="3">иное</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Окна:
        </div>
        <div class="objectDescriptionBody">
            <select name="windows">
                <option value="0" selected></option>
                <option value="1">деревянные</option>
                <option value="2">стеклопакет</option>
                <option value="3">иное</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Санузел, отделка:
        </div>
        <div class="objectDescriptionBody">
            <select name="wс">
                <option value="0" selected></option>
                <option value="1">кафель</option>
                <option value="2">обои</option>
                <option value="3">побелка</option>
                <option value="4">иное</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Половое покрытие в комнатах:
        </div>
        <div class="objectDescriptionBody">
            <select name="flooring">
                <option value="0" selected></option>
                <option value="1">дерево</option>
                <option value="2">паркет</option>
                <option value="3">ламинат</option>
                <option value="4">ковер</option>
                <option value="5">бетон</option>
                <option value="6">линолеум</option>
                <option value="7">иное</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="communication">
    <div class="advertDescriptionChapterHeader">
        Связь
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Интернет:
        </div>
        <div class="objectDescriptionBody">
            <select name="internet">
                <option value="0" selected></option>
                <option value="1">не проведен, нельзя провести</option>
                <option value="2">не проведен, можно провести</option>
                <option value="3">проведен, можно использовать</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Телефон:
        </div>
        <div class="objectDescriptionBody">
            <select name="telephoneLine">
                <option value="0" selected></option>
                <option value="1">не проведен, нельзя провести</option>
                <option value="2">не проведен, можно провести</option>
                <option value="3">проведен, можно использовать</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Кабельное ТВ:
        </div>
        <div class="objectDescriptionBody">
            <select name="cableTV">
                <option value="0" selected></option>
                <option value="1">не проведен, нельзя провести</option>
                <option value="2">не проведен, можно провести</option>
                <option value="3">проведен, можно использовать</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="furniture">
<div class="advertDescriptionChapterHeader">
    Мебель и бытовая техника
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        Наличие мебели и бытовой техники:
    </div>
    <div class="objectDescriptionBody" style="min-width: 330px">
        <select name="furnitureYesNo">
            <option value="0" selected></option>
            <option value="1">Сдается с мебелью и бытовой техникой</option>
            <option value="2">Сдается без мебели и бытовой техники</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="sofa">
        Диван:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="sofaAmount" size="3" value="">
        состояние
        <select name="sofaCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="chairBed">
        Кресло-кровать:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="chairBedAmount" size="3" value="">
        состояние
        <select name="chairBedCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="chair">
        Кресло:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="chairAmount" size="3" value="">
        состояние
        <select name="chairCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="stool">
        Стул:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="stoolAmount" size="3" value="">
        состояние
        <select name="stoolCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="tabouret">
        Табурет:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="tabouretAmount" size="3" value="">
        состояние
        <select name="tabouretCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="deskTable">
        Стол письменный, компьютерный:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="deskTableAmount" size="3" value="">
        состояние
        <select name="deskTableCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="coffeeTable">
        Стол журнальный:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="coffeeTableAmount" size="3" value="">
        состояние
        <select name="coffeeTableCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="setOfCabinets">
        Стенка:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="setOfCabinetsAmount" size="3" value="">
        состояние
        <select name="setOfCabinetsCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="cabinet">
        Шкаф для одежды:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="cabinetAmount" size="3" value="">
        состояние
        <select name="cabinetCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>

<br>

<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="diningTable">
        Стол обеденный:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="diningTableAmount" size="3" value="">
        состояние
        <select name="diningTableCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="kitchenSet">
        Кухонный гарнитур:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="kitchenSetAmount" size="3" value="">
        состояние
        <select name="kitchenSetCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="cupboards">
        Шкаф для посуды:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="cupboardsAmount" size="3" value="">
        состояние
        <select name="cupboardsCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>

<br>

<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="televisionSet">
        Телевизор:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="televisionSetAmount" size="3" value="">
        состояние
        <select name="televisionSetCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="refrigerator">
        Холодильник:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="refrigeratorAmount" size="3" value="">
        состояние
        <select name="refrigeratorCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="washingMachine">
        Стиральная машина:
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="washingMachineAmount" size="3" value="">
        состояние
        <select name="washingMachineCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
<div class="objectDescriptionItem">
    <div class="objectDescriptionItemLabel">
        <input type="checkbox" name="stove">
        Плита (газовая, электрическая):
    </div>
    <div class="objectDescriptionBody furniture">
        количество
        <input type="text" name="stoveAmount" size="3" value="">
        состояние
        <select name="stoveCurrentStatus">
            <option value="0" selected></option>
            <option value="1">новый</option>
            <option value="2">старый</option>
        </select>
    </div>
</div>
</div>

<div class="advertDescriptionChapter" id="requirementsForTenant">
    <div class="advertDescriptionChapterHeader">
        Требования к арендатору
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Пол:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="sexOfTenant" value="man">
            мужчина
            <br>
            <input type="checkbox" name="sexOfTenant" value="woman">
            женщина
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Отношения между арендаторами:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="relations" value="family">
            семейная пара
            <br>
            <input type="checkbox" name="relations" value="notFamily">
            несемейная пара
            <br>
            <input type="checkbox" name="relations" value="alone">
            один человек
            <br>
            <input type="checkbox" name="relations" value="group">
            группа людей
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Национальность:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="nationality" value="russian">
            русским
            <br>
            <input type="checkbox" name="nationality" value="european">
            европейцам, американцам
            <br>
            <input type="checkbox" name="nationality" value="east">
            СНГ, восточным национальностям
            <br>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Дети:
        </div>
        <div class="objectDescriptionBody">
            <select name="children">
                <option value="0" selected></option>
                <option value="1">не имеет значения</option>
                <option value="2">с детьми старше 4-х лет</option>
                <option value="3">только без детей</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Животные:
        </div>
        <div class="objectDescriptionBody">
            <select name="animals">
                <option value="0" selected></option>
                <option value="1">не имеет значения</option>
                <option value="2">только без животных</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="specialConditions">
    <div class="advertDescriptionChapterHeader">
        Особые условия
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Как часто собственник проверяет сдаваемую недвижимость:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <select name="checking">
                <option value="0" selected></option>
                <option value="1">Никогда (проживает в другом городе)</option>
                <option value="2">1 раз в месяц (при получении оплаты)</option>
                <option value="3">Периодически (чаще 1 раза в месяц)</option>
                <option value="4">Постоянно (проживает в этой же квартире)</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Какую ответственность за состояние и ремонт объекта берет на себя собственник:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="responsibility" maxlength="1000" rows="7" cols="43"></textarea>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Важные моменты, касающиеся сдаваемого объекта, которые не были указаны в форме выше:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="comment" maxlength="1000" rows="7" cols="43"></textarea>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="submitAdvertButton">
    <div class="advertDescriptionChapterHeader"></div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel"></div>
        <div class="objectDescriptionBody">
            <button class="saveAdvertButton">
                Сохранить
            </button>
            <!-- При нажатии - форма проверяется на заполненность и выдаются красные предупреждения, отправляется ан сервер и сохраняется как есть, перематывается вверх плавно к заголовку объявления - выдается модальное окно с результатом -->
        </div>
    </div>
</div>
</form>
</div>
<div class="news advertForPersonalPage unpublished">
    <div class="newsHeader">
        <span class="advertHeaderAddress">Квартира по улице Кирова 15, №3</span>

        <div class="advertHeaderStatus">
            статус: не опубликовано
        </div>
    </div>
    <div class="fotosWrapper">
        <div class="middleFotoWrapper">
            <img class="middleFoto" src="">
        </div>
    </div>
    <ul class="setOfInstructions">
        <li>
            <a href="#">удалить</a>
        </li>
        <li>
            <a href="#">редактировать</a>
        </li>
        <li>
            <a href="#">подробнее</a>
        </li>
        <li>
            <a href="#">опубликовать</a>
        </li>
    </ul>
    <ul class="listDescription">
        <li>
            <span class="headOfString" style="vertical-align: top;">Заинтересовавшиеся арендаторы:</span><a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;"
                                                                                  href="man.php">Алексей Мухмаев</a>, <a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;"
                                                                                  href="man.php">Алексей Мухмаев</a>, <a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;"
                                                                                  href="man.php">Алексей Мухмаев</a>, <a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>
        </li>
        <li>
            <span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
        </li>
        <li>
            <span class="headOfString">Единовременная комиссия:</span>
            <a href="#"> 3000 руб. (40%) собственнику</a>
        </li>
        <li>
            <span class="headOfString">Адрес:</span>
            улица Посадская 51
        </li>
        <li>
            <span class="headOfString">Количество комнат:</span>
            2, смежные
        </li>
        <li>
            <span class="headOfString">Площадь (жилая/общая):</span>
            22.4/34 м²
        </li>
        <li>
            <span class="headOfString">Этаж:</span>
            3 из 10
        </li>
        <li>
            <span class="headOfString">Срок сдачи:</span>
            долгосрочно
        </li>
        <li>
            <span class="headOfString">Мебель:</span>
            есть
        </li>
        <li>
            <span class="headOfString">Район:</span>
            Центр
        </li>
        <li>
            <span class="headOfString">Телефон собственника:</span>
            89221431615, <a href="#">Алексей Иванович</a>
        </li>
    </ul>
    <div class="clearBoth"></div>
</div>
<div class="news advertForPersonalPage published">
    <div class="newsHeader">
        <span class="advertHeaderAddress">Квартира по улице Кирова 15, №3</span>

        <div class="advertHeaderStatus">
            статус: опубликовано
        </div>
    </div>
    <div class="fotosWrapper">
        <div class="middleFotoWrapper">
            <img class="middleFoto" src="">
        </div>
    </div>
    <ul class="setOfInstructions">
        <li>
            <a href="#">редактировать</a>
        </li>
        <li>
            <a href="#">подробнее</a>
        </li>
        <li>
            <a href="#">снять с публикации</a>
        </li>
    </ul>
    <ul class="listDescription">
        <li>
            <span class="headOfString" style="vertical-align: top;">Заинтересовавшиеся арендаторы:</span><a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;"
                                                                                  href="man.php">Алексей Мухмаев</a>, <a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;"
                                                                                  href="man.php">Алексей Мухмаев</a>, <a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;"
                                                                                  href="man.php">Алексей Мухмаев</a>, <a
            style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>
        </li>
        <li>
            <span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
        </li>
        <li>
            <span class="headOfString">Единовременная комиссия:</span>
            <a href="#"> 3000 руб. (40%) собственнику</a>
        </li>
        <li>
            <span class="headOfString">Адрес:</span>
            улица Посадская 51
        </li>
        <li>
            <span class="headOfString">Количество комнат:</span>
            2, смежные
        </li>
        <li>
            <span class="headOfString">Площадь (жилая/общая):</span>
            22.4/34 м²
        </li>
        <li>
            <span class="headOfString">Этаж:</span>
            3 из 10
        </li>
        <li>
            <span class="headOfString">Срок сдачи:</span>
            долгосрочно
        </li>
        <li>
            <span class="headOfString">Мебель:</span>
            есть
        </li>
        <li>
            <span class="headOfString">Район:</span>
            Центр
        </li>
        <li>
            <span class="headOfString">Телефон собственника:</span>
            89221431615, <a href="#">Алексей Иванович</a>
        </li>
    </ul>
    <div class="clearBoth"></div>
</div>
</div>
<div id="tabs-4">
<div class="shadowText">
    На этой вкладке Вы можете задать параметры, в соответствии с которыми ресурс Хани Хом будет осуществлять
    автоматический поиск объявлений на портале и будет оповещать Вас о появлении новых объектов по указанному в профиле
    e-mail
</div>
<?php if ($typeTenant != "true" && $correct != "true" && $correctNewSearchRequest == "null"): ?> <!-- Если пользователь еще не сформировал поисковый запрос (а значит не является арендатором) и он либо не нажимал на кнопку формирования запроса, либо нажимал, но не прошел проверку на полноту информации о пользователи, то ему доступна только кнопка формирования нового запроса. В ином случае будет отображаться сам поисковый запрос пользователя, либо форма для его заполнения -->
<form name="createSearchRequest" method="post">
    <button type="submit" name="createSearchRequestButton" id='createSearchRequestButton' class='left-bottom'>
        Запрос на поиск
    </button>
</form>
    <?php endif;?>
<?php if ($typeTenant == "true"): ?> <!-- Если пользователь является арендатором, то у него уже сформирован поисковый запрос, который мы и показываем на этой вкладке -->
<div id="notEditingSearchParametersBlock" class="objectDescription">
<div class="setOfInstructions">
    <li><a href="#">редактировать</a></li>
    <li><a href="personal.php?action=deleteSearchRequest" title="Удаляет запрос на поиск - кликните по этой ссылке, когда Вы найдете недвижимость">удалить</a></li>
    <br>
</div>
<fieldset class="notEdited">
    <legend>
        Характеристика объекта
    </legend>
    <table>
        <tbody>
        <tr>
            <td class="objectDescriptionItemLabel">Тип:</td>
            <td class="objectDescriptionBody"><span>
                                                    <?php
                if ($typeOfObject == "flat") {
                    echo "квартира";
                }
                if ($typeOfObject == "room") {
                    echo "комната";
                }
                if ($typeOfObject == "house") {
                    echo "дом, коттедж";
                }
                if ($typeOfObject == "townhouse") {
                    echo "таунхаус";
                }
                if ($typeOfObject == "dacha") {
                    echo "дача";
                }
                if ($typeOfObject == "garage") {
                    echo "гараж";
                }
                ?></span></td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">Количество комнат:</td>
            <td class="objectDescriptionBody"><span><?php
                for ($i = 0; $i < count($amountOfRooms); $i++) {
                    echo $amountOfRooms[$i];
                    if ($i < count($amountOfRooms) - 1) echo ", ";
                }
                ?></span></td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">Комнаты смежные:</td>
            <td class="objectDescriptionBody"><span><?php
                if ($adjacentRooms == "yes") {
                    echo "не имеет значения";
                }
                if ($adjacentRooms == "no") {
                    echo "только изолированные";
                }
                ?></span></td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">Этаж:</td>
            <td class="objectDescriptionBody"><span><?php
                if ($floor == "any") {
                    echo "любой";
                }
                if ($floor == "not1") {
                    echo "не первый";
                }
                if ($floor == "not1notLasted") {
                    echo "не первый и не последний";
                }
                ?></span></td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">Мебель:</td>
            <td class="objectDescriptionBody"><span><?php
                if ($furniture == "any") {
                    echo "не имеет значения";
                }
                if ($furniture == "with") {
                    echo "с мебелью и быт. техникой";
                }
                if ($furniture == "without") {
                    echo "без мебели";
                }
                ?></span></td>
        </tr>
        </tbody>
    </table>
</fieldset>
<fieldset class="notEdited">
    <legend>
        Стоимость
    </legend>
    <table>
        <tbody>
        <tr>
            <td class="objectDescriptionItemLabel">Арендная плата в месяц от:</td>
            <td class="objectDescriptionBody"><?php
                echo "<span>" . $minCost . "</span> руб.";
                ?></td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
            <td class="objectDescriptionBody"><?php
                echo "<span>" . $maxCost . "</span> руб.";
                ?></td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">Залог до:</td>
            <td class="objectDescriptionBody"><?php
                echo "<span>" . $pledge . "</span> руб.";
                ?></td>
        </tr>
        </tbody>
    </table>
</fieldset>
<fieldset class="notEdited" id="additionalSearchDescription">
    <legend>
        Особые параметры поиска
    </legend>
    <table>
        <tbody>
        <tr>
            <td class="objectDescriptionItemLabel" id="firstTableColumnSpecial">Как собираетесь проживать:</td>
            <td class="objectDescriptionBody"><span><?php
                if ($withWho == "alone") {
                    echo "один";
                }
                if ($withWho == "couple") {
                    echo "семейная пара";
                }
                if ($withWho == "nonFamilyPair") {
                    echo "несемейная пара";
                }
                if ($withWho == "withFriends") {
                    echo "со знакомыми";
                }
                ?></span></td>
        </tr>
            <?php
            if ($withWho != "alone") {
                echo "<tr><td class='objectDescriptionItemLabel'>Ссылки на страницы сожителей:</td><td class='objectDescriptionBody''><span>";
                if (isset($linksToFriends)) echo $linksToFriends;
                echo "</span></td></tr>";
            }
            ?>
        <tr>
            <td class="objectDescriptionItemLabel">Дети:</td>
            <td class="objectDescriptionBody"><span><?php
                if ($children == "without") {
                    echo "без детей";
                }
                if ($children == "childrenUnder4") {
                    echo "с детьми младше 4-х лет";
                }
                if ($children == "childrenOlder4") {
                    echo "с детьми старше 4-х лет";
                }
                ?></span></td>
        </tr>
            <?php
            if ($children != "without") {
                echo "<tr><td class='objectDescriptionItemLabel'>Количество детей и их возраст:</td><td class='objectDescriptionBody''><span>";
                if (isset($howManyChildren)) echo $howManyChildren;
                echo "</span></td></tr>";
            }
            ?>
        <tr>
            <td class="objectDescriptionItemLabel">Животные:</td>
            <td class="objectDescriptionBody"><span><?php
                if ($animals == "without") {
                    echo "без животных";
                }
                if ($animals == "with") {
                    echo "с животным(ми)";
                }
                ?></span></td>
        </tr>
            <?php
            if ($animals != "without") {
                echo "<tr><td class='objectDescriptionItemLabel'>Количество животных и их вид:</td><td class='objectDescriptionBody''><span>";
                if (isset($howManyAnimals)) echo $howManyAnimals;
                echo "</span></td></tr>";
            }
            ?>
        <tr>
            <td class="objectDescriptionItemLabel">Ориентировочный срок аренды:</td>
            <td class="objectDescriptionBody"><span><?php
                if (isset($period)) echo $period;
                ?></span></td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
            <td class="objectDescriptionBody"><span><?php
                if (isset($additionalDescriptionOfSearch)) echo $additionalDescriptionOfSearch;
                ?></span></td>
        </tr>
        </tbody>
    </table>
</fieldset>
<fieldset class="notEdited">
    <legend>
        Район
    </legend>
    <table>
        <tbody>
            <?php
            if (isset($district) && isset($allDistrictsInCity)) { // Если район указан пользователем
                foreach ($district as $value) { // Для каждого идентификатора района подбираем название из таблицы
                    echo "<tr><td class='objectDescriptionItemLabel'>" . $allDistrictsInCity[$value] . "</td></tr>";
                }
            }
            ?>
        </tbody>
    </table>
</fieldset>
</div>
    <?php endif;?>
<?php if ($typeTenant == "true" || $correct == "true" || $correctNewSearchRequest == "false"): ?> <!-- Если пользователь является арендатором, то вместе с отображением текущих параметров поискового запроса мы выдаем скрытую форму для их редактирования, также мы выдаем видимую форму для редактирования параметров поиска в случае, если пользователь нажал на кнопку Нового поискового запроса и проверка на корректность его данных Профиля профла успешно, а также в случае если пользователь корректировал данные поискового запроса, но они не прошли проверку -->
<form method="post" name="searchParameters" id="extendedSearchParametersBlock" style='<?php if ($typeTenant == "true") echo "display: none;"?>'> <!-- Блок редактируемых параметров поиска невидим в случае если пользователь уже является арендатором (у него есть поисковый запрос, данные которого и отображаются в нередактируемом виде (блок id="notEditingSearchParametersBlock")) -->
<div id="leftBlockOfSearchParameters" style="display: inline-block;">
    <fieldset class="edited">
        <legend>
            Характеристика объекта
        </legend>
        <div class="searchItem">
            <span class="searchItemLabel"> Тип: </span>

            <div class="searchItemBody">
                <select name="typeOfObject">
                    <option value="flat" <?php if ($typeOfObject == "flat") echo "selected";?>>квартира</option>
                    <option value="room" <?php if ($typeOfObject == "room") echo "selected";?>>комната</option>
                    <option value="house" <?php if ($typeOfObject == "house") echo "selected";?>>дом, коттедж</option>
                    <option value="townhouse" <?php if ($typeOfObject == "townhouse") echo "selected";?>>таунхаус
                    </option>
                    <option value="dacha" <?php if ($typeOfObject == "dacha") echo "selected";?>>дача</option>
                    <option value="garage" <?php if ($typeOfObject == "garage") echo "selected";?>>гараж</option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Количество комнат: </span>

            <div class="searchItemBody">
                <input type="checkbox" value="1"
                       name="amountOfRooms[]" <?php if ($amountOfRooms1 == "1") echo "checked";?>>
                1
                <input type="checkbox" value="2"
                       name="amountOfRooms[]" <?php if ($amountOfRooms2 == "2") echo "checked";?>>
                2
                <input type="checkbox" value="3"
                       name="amountOfRooms[]" <?php if ($amountOfRooms3 == "3") echo "checked";?>>
                3
                <input type="checkbox" value="4"
                       name="amountOfRooms[]" <?php if ($amountOfRooms4 == "4") echo "checked";?>>
                4
                <input type="checkbox" value="5"
                       name="amountOfRooms[]" <?php if ($amountOfRooms5 == "5") echo "checked";?>>
                5
                <input type="checkbox" value="6"
                       name="amountOfRooms[]" <?php if ($amountOfRooms6 == "6") echo "checked";?>>
                6...
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Комнаты смежные: </span>

            <div class="searchItemBody">
                <select name="adjacentRooms">
                    <option value="yes" <?php if ($adjacentRooms == "yes") echo "selected";?>>не имеет значения</option>
                    <option value="no" <?php if ($adjacentRooms == "no") echo "selected";?>>только изолированные
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Этаж: </span>

            <div class="searchItemBody">
                <select name="floor">
                    <option value="any" <?php if ($floor == "any") echo "selected";?>>любой</option>
                    <option value="not1" <?php if ($floor == "not1") echo "selected";?>>не первый</option>
                    <option value="not1notLasted" <?php if ($floor == "not1notLasted") echo "selected";?>>не первый и не
                        последний
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Мебель: </span>

            <div class="searchItemBody">
                <select name="furniture">
                    <option value="any" <?php if ($furniture == "any") echo "selected";?>>не имеет значения</option>
                    <option value="with" <?php if ($furniture == "with") echo "selected";?>>с мебелью и быт. техникой</option>
                    <option value="without" <?php if ($furniture == "without") echo "selected";?>>без мебели</option>
                </select>
            </div>
        </div>
    </fieldset>
    <fieldset class="edited">
        <legend>
            Стоимость
        </legend>
        <div class="searchItem">
            <div class="searchItemLabel">
                Арендная плата (в месяц с учетом к.у.)
            </div>
            <div class="searchItemBody">
                от
                <input type="text" name="minCost" size="10" maxlength="8" <?php echo "value='$minCost'";?>>
                руб., до
                <input type="text" name="maxCost" size="10" maxlength="8" <?php echo "value='$maxCost'";?>>
                руб.
            </div>
        </div>
        <div class="searchItem"
             title="Какую сумму Вы готовы передать собственнику в качестве возвращаемого гарантийного депозита, а также предоплаты за проживание, кроме арендной платы за первый месяц">
            <span class="searchItemLabel"> Залог </span>

            <div class="searchItemBody">
                до
                <input type="text" name="pledge" size="10" maxlength="8" <?php echo "value='$pledge'";?>>
                руб.
            </div>
        </div>
    </fieldset>
</div>
<div id="rightBlockOfSearchParameters">
<fieldset>
<legend>
    Район
</legend>
<div class="searchItem">
<div class="searchItemBody">
<ul>
<li>
    <input type="checkbox" name="district[]"
           value="1" <?php if ($district1 == "1") echo "checked";?>>
    Автовокзал (южный)
</li>
<li>
    <input type="checkbox" name="district[]"
           value="2" <?php if ($district2 == "2") echo "checked";?>>
    Академический
</li>
<li>
    <input type="checkbox" name="district[]"
           value="3" <?php if ($district3 == "3") echo "checked";?>>
    Ботанический
</li>
<li>
    <input type="checkbox" name="district[]"
           value="4" <?php if ($district4 == "4") echo "checked";?>>
    ВИЗ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="5" <?php if ($district5 == "5") echo "checked";?>>
    Вокзальный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="6" <?php if ($district6 == "6") echo "checked";?>>
    Втузгородок
</li>
<li>
    <input type="checkbox" name="district[]"
           value="7" <?php if ($district7 == "7") echo "checked";?>>
    Горный щит
</li>
<li>
    <input type="checkbox" name="district[]"
           value="8" <?php if ($district8 == "8") echo "checked";?>>
    Елизавет
</li>
<li>
    <input type="checkbox" name="district[]"
           value="9" <?php if ($district9 == "9") echo "checked";?>>
    ЖБИ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="10" <?php if ($district10 == "10") echo "checked";?>>
    Завокзальный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="11" <?php if ($district11 == "11") echo "checked";?>>
    Заречный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="12" <?php if ($district12 == "12") echo "checked";?>>
    Изоплит
</li>
<li>
    <input type="checkbox" name="district[]"
           value="13" <?php if ($district13 == "13") echo "checked";?>>
    Исток
</li>
<li>
    <input type="checkbox" name="district[]"
           value="14" <?php if ($district14 == "14") echo "checked";?>>
    Калиновский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="15" <?php if ($district15 == "15") echo "checked";?>>
    Кольцово
</li>
<li>
    <input type="checkbox" name="district[]"
           value="16" <?php if ($district16 == "16") echo "checked";?>>
    Компрессорный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="17" <?php if ($district17 == "17") echo "checked";?>>
    Лечебный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="18" <?php if ($district18 == "18") echo "checked";?>>
    Малый исток
</li>
<li>
    <input type="checkbox" name="district[]"
           value="19" <?php if ($district19 == "19") echo "checked";?>>
    Нижнеисетский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="20" <?php if ($district20 == "20") echo "checked";?>>
    Парковый
</li>
<li>
    <input type="checkbox" name="district[]"
           value="21" <?php if ($district21 == "21") echo "checked";?>>
    Пионерский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="22" <?php if ($district22 == "22") echo "checked";?>>
    Птицефабрика
</li>
<li>
    <input type="checkbox" name="district[]"
           value="23" <?php if ($district23 == "23") echo "checked";?>>
    Рудный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="24" <?php if ($district24 == "24") echo "checked";?>>
    Садовый
</li>
<li>
    <input type="checkbox" name="district[]"
           value="25" <?php if ($district25 == "25") echo "checked";?>>
    Северка
</li>
<li>
    <input type="checkbox" name="district[]"
           value="26" <?php if ($district26 == "26") echo "checked";?>>
    Семь ключей
</li>
<li>
    <input type="checkbox" name="district[]"
           value="27" <?php if ($district27 == "27") echo "checked";?>>
    Сибирский тракт
</li>
<li>
    <input type="checkbox" name="district[]"
           value="28" <?php if ($district28 == "28") echo "checked";?>>
    Синие камни
</li>
<li>
    <input type="checkbox" name="district[]"
           value="29" <?php if ($district29 == "29") echo "checked";?>>
    Совхозный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="30" <?php if ($district30 == "30") echo "checked";?>>
    Сортировка новая
</li>
<li>
    <input type="checkbox" name="district[]"
           value="31" <?php if ($district31 == "31") echo "checked";?>>
    Сортировка старая
</li>
<li>
    <input type="checkbox" name="district[]"
           value="32" <?php if ($district32 == "32") echo "checked";?>>
    Уктус
</li>
<li>
    <input type="checkbox" name="district[]"
           value="33" <?php if ($district33 == "33") echo "checked";?>>
    УНЦ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="34" <?php if ($district34 == "34") echo "checked";?>>
    Уралмаш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="35" <?php if ($district35 == "35") echo "checked";?>>
    Химмаш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="36" <?php if ($district36 == "36") echo "checked";?>>
    Центр
</li>
<li>
    <input type="checkbox" name="district[]"
           value="37" <?php if ($district37 == "37") echo "checked";?>>
    Чермет
</li>
<li>
    <input type="checkbox" name="district[]"
           value="38" <?php if ($district38 == "38") echo "checked";?>>
    Чусовское озеро
</li>
<li>
    <input type="checkbox" name="district[]"
           value="39" <?php if ($district39 == "39") echo "checked";?>>
    Шабровский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="40" <?php if ($district40 == "40") echo "checked";?>>
    Шарташ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="41" <?php if ($district41 == "41") echo "checked";?>>
    Шарташский рынок
</li>
<li>
    <input type="checkbox" name="district[]"
           value="42" <?php if ($district42 == "42") echo "checked";?>>
    Широкая речка
</li>
<li>
    <input type="checkbox" name="district[]"
           value="43" <?php if ($district43 == "43") echo "checked";?>>
    Шувакиш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="44" <?php if ($district44 == "44") echo "checked";?>>
    Эльмаш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="45" <?php if ($district45 == "45") echo "checked";?>>
    Юго-запад
</li>
<li>
    <input type="checkbox" name="district[]"
           value="46" <?php if ($district46 == "46") echo "checked";?>>
    За городом
</li>
</ul>
</div>
</div>
</fieldset>
</div>
<!-- /end.rightBlockOfSearchParameters -->
<fieldset class="edited private">
    <legend>
        Особые параметры поиска
    </legend>
    <div class="searchItem">
        <span class="searchItemLabel">Как собираетесь проживать: </span>

        <div class="searchItemBody">
            <select name="withWho" id="withWho">
                <option value="alone" <?php if ($withWho == "alone") echo "selected";?>>один</option>
                <option value="couple" <?php if ($withWho == "couple") echo "selected";?>>семейная пара</option>
                <option value="nonFamilyPair" <?php if ($withWho == "nonFamilyPair") echo "selected";?>>несемейная
                    пара
                </option>
                <option value="withFriends" <?php if ($withWho == "withFriends") echo "selected";?>>со знакомыми
                </option>
            </select>
        </div>
    </div>
    <div class="searchItem" id="withWhoDescription" style="display: none;">
        <div class="searchItemLabel">
            Ссылки на страницы сожителей:
        </div>
        <div class="searchItemBody">
            <textarea name="linksToFriends" cols="40" rows="3"><?php echo $linksToFriends;?></textarea>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Дети: </span>

        <div class="searchItemBody">
            <select name="children" id="children">
                <option value="without" <?php if ($children == "without") echo "selected";?>>без детей</option>
                <option value="childrenUnder4" <?php if ($children == "childrenUnder4") echo "selected";?>>с детьми
                    младше 4-х лет
                </option>
                <option value="childrenOlder4" <?php if ($children == "childrenOlder4") echo "selected";?>>с детьми
                    старше 4-х лет
                </option>
            </select>
        </div>
    </div>
    <div class="searchItem" id="childrenDescription" style="display: none;">
        <div class="searchItemLabel">
            Сколько у Вас детей и какого возраста:
        </div>
        <div class="searchItemBody">
            <textarea name="howManyChildren" cols="40" rows="3"><?php echo $howManyChildren;?></textarea>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Животные: </span>

        <div class="searchItemBody">
            <select name="animals" id="animals">
                <option value="without" <?php if ($animals == "without") echo "selected";?>>без животных</option>
                <option value="with" <?php if ($animals == "with") echo "selected";?>>с животным(ми)</option>
            </select>
        </div>
    </div>
    <div class="searchItem" id="animalsDescription" style="display: none;">
        <div class="searchItemLabel">
            Сколько у Вас животных и какого вида:
        </div>
        <div class="searchItemBody">
            <textarea name="howManyAnimals" cols="40" rows="3"><?php echo $howManyAnimals;?></textarea>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Ориентировочный срок аренды:</span>

        <div class="searchItemBody">
            <input type="text" name="period" size="18" maxlength="80"
                   validations="validate[required]" <?php echo "value='$period'";?>>
        </div>
    </div>
    <div class="searchItem">
        <div class="searchItemLabel">
            Дополнительные условия поиска:
        </div>
        <div class="searchItemBody">
            <textarea name="additionalDescriptionOfSearch" cols="50"
                      rows="4"><?php echo $additionalDescriptionOfSearch;?></textarea>
        </div>
    </div>
</fieldset>

<div class="clearBoth"></div>
<div class="bottomButton">
<a href="personal.php" style="margin-right: 10px;">Отмена</a>
<button type="submit" name="saveSearchParametersButton" id="saveSearchParametersButton" class="button">
    Сохранить
</button>
</div>

<div class="clearBoth"></div>
</form>
<!-- /end.extendedSearchParametersBlock -->
    <?php endif;?>
</div>
<!-- /end.tabs-4 -->
<div id="tabs-5">
<div class="shadowText">
    На этой вкладке расположены все объявления, добавленные Вами в избранные
</div>
<div class="choiceViewSearchResult">
    <span id="expandList"><a href="#">Список</a>&nbsp;&nbsp;&nbsp;</span><span id="listPlusMap"><a href="#">Список +
    карта</a>&nbsp;&nbsp;&nbsp;</span><span id="expandMap"><a href="#">Карта</a></span>
</div>
<div id="resultOnSearchPage" style="height: 100%;">

<!-- Информация об объектах, подходящих условиям поиска -->
<table class="listOfRealtyObjects" id="shortListOfRealtyObjects">
<tbody>
<tr class="realtyObject" coordX="56.836396" coordY="60.588662"
    balloonContentBody='<div class="headOfBalloon">ул. Ленина 13</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
    <td>
        <div class="numberOfRealtyObject">
            1
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Ленина 13
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 15000</td>
</tr>
<tr class="realtyObject" coordX="56.819927" coordY="60.539264"
    balloonContentBody='<div class="headOfBalloon">ул. Репина 105</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
    <td>
        <div class="numberOfRealtyObject">
            2
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Репина 105
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 35000</td>
</tr>
<tr class="realtyObject" coordX="56.817405" coordY="60.558452"
    balloonContentBody='<div class="headOfBalloon">ул. Шаумяна 107</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
    <td>
        <div class="numberOfRealtyObject">
            3
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Шаумяна 107
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 150000</td>
</tr>
<tr class="realtyObject" coordX="56.825483" coordY="60.57357"
    balloonContentBody='<div class="headOfBalloon">ул. Гурзуфская 38</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
    <td>
        <div class="numberOfRealtyObject">
            123
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Гурзуфская 38
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 6000</td>
</tr>
<tr class="realtyObject" coordX="56.820769" coordY="60.560742"
    balloonContentBody='<div class="headOfBalloon">ул. Серафимы Дерябиной 17</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
    <td>
        <div class="numberOfRealtyObject">
            1254
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Серафимы Дерябиной 17
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 2000</td>
</tr>
<tr class="realtyObject" coordX="56.820769" coordY="60.560742"
    balloonContentBody='<div class="headOfBalloon">ул. Серафимы Дерябиной 17</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
    <td>
        <div class="numberOfRealtyObject">
            12
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Серафимы Дерябиной 17
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 350000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="numberOfRealtyObject">
            15
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>улица Сибирский тракт 50 летия 107
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 15000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="numberOfRealtyObject">
            15
        </div>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Сумасранка 4
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 35000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Серафимы Дерябиной 154
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 150000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Белореченская 24
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 6000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Маврода 2012
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 2000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Пискуна 1
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 350000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>улица Сибирский тракт 50 летия 107
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 15000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Сумасранка 4
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 35000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Серафимы Дерябиной 154
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 150000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Белореченская 24
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 6000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Маврода 2012
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 2000</td>
</tr>
<tr class="realtyObject">
    <td>
        <div class="blockOfIcon">
            <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
        </div>
    </td>
    <td>
        <div class="fotosWrapper resultSearchFoto">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
    </td>
    <td>ул. Пискуна 1
        <div class="linkToDescriptionBlock">
            <a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
        </div>
    </td>
    <td> 350000</td>
</tr>
</tbody>
</table>

<!-- Область показа карты -->
<div id="map"></div>

<div class="clearBoth"></div>

<!-- Первоначально скрытый раздел с подробным списком объявлений-->
<div id="fullParametersListOfRealtyObjects" style="display: none;">
    <table class="listOfRealtyObjects" style="width: 100%; float:none;">
        <thead>
        <tr class="listOfRealtyObjectsHeader">
            <th class="top left"></th>
            <th> Фото</th>
            <th> Адрес</th>
            <th> Район</th>
            <th> Комнат</th>
            <th> Площадь</th>
            <th> Этаж</th>
            <th class="top right"> Цена, руб.</th>
        </tr>
        </thead>
        <tbody>
        <tr class="realtyObject" linkToDescription="descriptionOfObject.html">
            <td>
                <div class="numberOfRealtyObject">
                    15
                </div>
                <div class="blockOfIcon">
                    <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
                </div>
            </td>
            <td>
                <div class="fotosWrapper resultSearchFoto">
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                </div>
            </td>
            <td>ул. Серафимы Дерябиной 17</td>
            <td> ВИЗ</td>
            <td> 2</td>
            <td> 22.4/34</td>
            <td> 2/13</td>
            <td> 15000</td>
        </tr>
        <tr class="realtyObject" linkToDescription="descriptionOfObject.html">
            <td>
                <div class="numberOfRealtyObject">
                    15
                </div>
                <div class="blockOfIcon">
                    <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
                </div>
            </td>
            <td>
                <div class="fotosWrapper resultSearchFoto">
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                </div>
            </td>
            <td>ул. Гурзуфская 38</td>
            <td> ВИЗ</td>
            <td> 2</td>
            <td> 22.4/34</td>
            <td> 2/13</td>
            <td> 15000</td>
        </tr>
        <tr class="realtyObject" linkToDescription="descriptionOfObject.html">
            <td>
                <div class="numberOfRealtyObject">
                    15
                </div>
                <div class="blockOfIcon">
                    <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
                </div>
            </td>
            <td>
                <div class="fotosWrapper resultSearchFoto">
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                </div>
            </td>
            <td>ул. Шаумяна 107</td>
            <td> ВИЗ</td>
            <td> 2</td>
            <td> 22.4/34</td>
            <td> 2/13</td>
            <td> 15000</td>
        </tr>
        <tr class="realtyObject" linkToDescription="descriptionOfObject.html">
            <td>
                <div class="numberOfRealtyObject">
                    15
                </div>
                <div class="blockOfIcon">
                    <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
                </div>
            </td>
            <td>
                <div class="fotosWrapper resultSearchFoto">
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                </div>
            </td>
            <td>ул. Репина 105</td>
            <td> ВИЗ</td>
            <td> 2</td>
            <td> 22.4/34</td>
            <td> 2/13</td>
            <td> 15000</td>
        </tr>
        <tr class="realtyObject" linkToDescription="descriptionOfObject.html">
            <td>
                <div class="blockOfIcon">
                    <a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
                </div>
            </td>
            <td>
                <div class="fotosWrapper resultSearchFoto">
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                    <div class="middleFotoWrapper">
                        <img class="middleFoto" src="">
                    </div>
                </div>
            </td>
            <td>ул. Ленина 13</td>
            <td> ВИЗ</td>
            <td> 2</td>
            <td> 22.4/34</td>
            <td> 2/13</td>
            <td> 15000</td>
        </tr>
        </tbody>
    </table>
</div>
</div>
<!-- /end.resultOnSearchPage -->
</div>
</div>
</div>

</div>
<!-- /end.page_main_content -->
<!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
<div class="page-buffer"></div>
</div>
<!-- /end.page_without_footer -->
<div class="footer">
    2012 «Хани Хом», вопросы и пожелания по работе портала можно передавать по телефону 8-922-143-16-15
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->

<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

<!-- jQuery UI с моей темой оформления -->
<script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>

<!-- Русификатор виджета календарь -->
<script src="js/vendor/jquery.ui.datepicker-ru.js"></script>

<!-- Загрузчик фотографий на AJAX -->
<script src="js/vendor/fileuploader.js" type="text/javascript"></script>

<!-- Загружаем библиотеку для работы с картой от Яндекса -->
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

<!-- scripts concatenated and minified via build script -->
<script src="js/main.js"></script>
<script src="js/personal.js"></script>

<!-- end scripts -->

<!-- Asynchronous Google Analytics snippet. Change UA-XXXXX-X to be your site's ID.
        mathiasbynens.be/notes/async-analytics-snippet -->
<!-- <script>
        var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
        </script> -->
</body>
</html>
