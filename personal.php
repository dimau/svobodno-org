<?php
    include_once 'lib/connect.php'; //подключаемся к БД
    include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

    /*************************************************************************************
     * Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
     ************************************************************************************/
    $userId = login();
    if (!$userId) {
        header('Location: login.php');
    }

    /*************************************************************************************
     * Получаем информацию о пользователе по его логину из БД сервера
     ************************************************************************************/

    $rezUsers = mysql_query("SELECT * FROM users WHERE id = '" . $userId . "'");
    $rowUsers = mysql_fetch_assoc($rezUsers);

    // Если пользователь пожелал удалить поисковый запрос, то это нужно сделать вместо получения данных из таблицы БД searchrequests
    if (isset($_GET['action']) && $_GET['action'] == 'deleteSearchRequest') {
        $rez = mysql_query("DELETE FROM searchrequests WHERE userId='" . $rowUsers['id'] . "'");
        $rowSearchRequests = FALSE;
        $rez = mysql_query("UPDATE users SET typeTenant='" . "false" . "' WHERE id='" . $rowUsers['id'] . "'");
        $rowUsers['typeTenant'] = "false";
    } else {
        $rezSearchRequests = mysql_query("SELECT * FROM searchrequests WHERE userId = '" . $rowUsers['id'] . "'");
        $rowSearchRequests = mysql_fetch_assoc($rezSearchRequests);
    }

    // Получаем информацию о фотографиях пользователя
    $rezUserFotos = mysql_query("SELECT * FROM userfotos WHERE userId = '" . $rowUsers['id'] . "'");
    $rowUserFotos = mysql_fetch_assoc($rezUserFotos); // TODO: сделать отображение нескольких фоток, если пользователь загрузит не одну

    // Получаем информацию о всех объектах пользователя (возможно он является собственником)
    $rowPropertyArr = array(); // в итоге получаем массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления данного пользователя
    $rezProperty = mysql_query("SELECT * FROM property WHERE userId = '" . $rowUsers['id'] . "' ORDER BY status DESC, last_act DESC");
    for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
        $rowPropertyArr[] = mysql_fetch_assoc($rezProperty);
    }

    // Получаем информацию о фотографиях объектов недвижимости пользователя (возможно он является собственником)
    // На самом деле мы получаем информацию только по 1 первой попавшейся фотке каждого из объектов недвижимости
    $rowPropertyFotosArr = array();
    for ($i = 0; $i < count($rowPropertyArr); $i++) {
        $rezPropertyFotos = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $rowPropertyArr[$i]['id'] . "'");
        $rowTemp = mysql_fetch_assoc($rezPropertyFotos);
        if ($rowTemp != FALSE) $rowPropertyFotosArr[$i] = $rowTemp; else $rowPropertyFotosArr[$i] = array(); // Кажется, текущее решение не позволит перепутать фотографии от разных объявлений
    }

    // Готовим массив со списком районов в городе пользователя
    $allDistrictsInCity = array();
    $rezDistricts = mysql_query("SELECT name FROM districts WHERE city = '" . "Екатеринбург" . "' ORDER BY name ASC");
    for ($i = 0; $i < mysql_num_rows($rezDistricts); $i++) {
        $rowDistricts = mysql_fetch_assoc($rezDistricts);
        $allDistrictsInCity[] = $rowDistricts['name'];
    }

    // Инициализируем переменную корректности - используется при формировании нового Запроса на поиск
    $correct = "null"; // Отражает корректность и полноту личных данных пользователя, необходимую для создания НОВОГО поискового запроса.
    $correctNewSearchRequest = "null"; // Отражает корректность отредактированных пользователем параметров поиска
    $correctNewProfileParameters = "null"; // Корректность личных данных пользователя. Работает, если он пытается изменить личные данные своего профайла. Проверка осуществляется в соответствии со статусом пользователя (арендатор или собственник)

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
    if (isset($rowUsers['birthday'])) $birthday = dateFromDBToView($rowUsers['birthday']); else $birthday = "";
    if (isset($rowUsers['login'])) $login = $rowUsers['login']; else $login = "";
    if (isset($rowUsers['password'])) $password = $rowUsers['password']; else $password = "";
    if (isset($rowUsers['telephon'])) $telephon = $rowUsers['telephon']; else $telephon = "";
    if (isset($rowUsers['email'])) $email = $rowUsers['email']; else $email = "";
    $fileUploadId = generateCode(7);
    if (isset($rowUsers['currentStatusEducation'])) $currentStatusEducation = $rowUsers['currentStatusEducation']; else $currentStatusEducation = "0";
    if (isset($rowUsers['almamater'])) $almamater = $rowUsers['almamater']; else $almamater = "";
    if (isset($rowUsers['speciality'])) $speciality = $rowUsers['speciality']; else $speciality = "";
    if (isset($rowUsers['kurs'])) $kurs = $rowUsers['kurs']; else $kurs = "";
    if (isset($rowUsers['ochnoZaochno'])) $ochnoZaochno = $rowUsers['ochnoZaochno']; else $ochnoZaochno = "0";
    if (isset($rowUsers['yearOfEnd'])) $yearOfEnd = $rowUsers['yearOfEnd']; else $yearOfEnd = "";
    if (isset($rowUsers['statusWork'])) $statusWork = $rowUsers['statusWork']; else $statusWork = "";
    if (isset($rowUsers['placeOfWork'])) $placeOfWork = $rowUsers['placeOfWork']; else $placeOfWork = "";
    if (isset($rowUsers['workPosition'])) $workPosition = $rowUsers['workPosition']; else $workPosition = "";
    if (isset($rowUsers['regionOfBorn'])) $regionOfBorn = $rowUsers['regionOfBorn']; else $regionOfBorn = "";
    if (isset($rowUsers['cityOfBorn'])) $cityOfBorn = $rowUsers['cityOfBorn']; else $cityOfBorn = "";
    if (isset($rowUsers['shortlyAboutMe'])) $shortlyAboutMe = $rowUsers['shortlyAboutMe']; else $shortlyAboutMe = "";
    if (isset($rowUsers['vkontakte'])) $vkontakte = $rowUsers['vkontakte']; else $vkontakte = "";
    if (isset($rowUsers['odnoklassniki'])) $odnoklassniki = $rowUsers['odnoklassniki']; else $odnoklassniki = "";
    if (isset($rowUsers['facebook'])) $facebook = $rowUsers['facebook']; else $facebook = "";
    if (isset($rowUsers['twitter'])) $twitter = $rowUsers['twitter']; else $twitter = "";

    if (isset($rowSearchRequests['typeOfObject'])) $typeOfObject = $rowSearchRequests['typeOfObject']; else $typeOfObject = "0";
    if (isset($rowSearchRequests['amountOfRooms'])) $amountOfRooms = unserialize($rowSearchRequests['amountOfRooms']); else $amountOfRooms = array();
    if (isset($rowSearchRequests['adjacentRooms'])) $adjacentRooms = $rowSearchRequests['adjacentRooms']; else $adjacentRooms = "0";
    if (isset($rowSearchRequests['floor'])) $floor = $rowSearchRequests['floor']; else $floor = "0";
    if (isset($rowSearchRequests['minCost'])) $minCost = $rowSearchRequests['minCost']; else $minCost = "";
    if (isset($rowSearchRequests['maxCost'])) $maxCost = $rowSearchRequests['maxCost']; else $maxCost = "";
    if (isset($rowSearchRequests['pledge'])) $pledge = $rowSearchRequests['pledge']; else $pledge = "";
    if (isset($rowSearchRequests['prepayment'])) $prepayment = $rowSearchRequests['prepayment']; else $prepayment = "0";
    if (isset($rowSearchRequests['district'])) $district = unserialize($rowSearchRequests['district']); else $district = array();
    if (isset($rowSearchRequests['withWho'])) $withWho = $rowSearchRequests['withWho']; else $withWho = "0";
    if (isset($rowSearchRequests['linksToFriends'])) $linksToFriends = $rowSearchRequests['linksToFriends']; else $linksToFriends = "";
    if (isset($rowSearchRequests['children'])) $children = $rowSearchRequests['children']; else $children = "0";
    if (isset($rowSearchRequests['howManyChildren'])) $howManyChildren = $rowSearchRequests['howManyChildren']; else $howManyChildren = "";
    if (isset($rowSearchRequests['animals'])) $animals = $rowSearchRequests['animals']; else $animals = "0";
    if (isset($rowSearchRequests['howManyAnimals'])) $howManyAnimals = $rowSearchRequests['howManyAnimals']; else $howManyAnimals = "";
    if (isset($rowSearchRequests['termOfLease'])) $termOfLease = $rowSearchRequests['termOfLease']; else $termOfLease = "0";
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
        if (isset($_POST['statusWork'])) $statusWork = htmlspecialchars($_POST['statusWork']);
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
            $birthdayDB = dateFromViewToDB($birthday);

            // Сохраняем новые параметры Профиля пользователя в БД
            $rez = mysql_query("UPDATE users SET
            name='" . $name . "',
            secondName='" . $secondName . "',
            surname='" . $surname . "',
            sex='" . $sex . "',
            nationality='" . $nationality . "',
            birthday='" . $birthdayDB . "',
            password='" . $password . "',
            telephon='" . $telephon . "',
            email='" . $email . "',
            currentStatusEducation='" . $currentStatusEducation . "',
            almamater='" . $almamater . "',
            speciality='" . $speciality . "',
            kurs='" . $kurs . "',
            ochnoZaochno='" . $ochnoZaochno . "',
            yearOfEnd='" . $yearOfEnd . "',
            statusWork='" . $statusWork . "',
            placeOfWork='" . $placeOfWork . "',
            workPosition='" . $workPosition . "',
            regionOfBorn='" . $regionOfBorn . "',
            cityOfBorn='" . $cityOfBorn . "',
            shortlyAboutMe='" . $shortlyAboutMe . "',
            vkontakte='" . $vkontakte . "',
            odnoklassniki='" . $odnoklassniki . "',
            facebook='" . $facebook . "',
            twitter='" . $twitter . "'
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
        if (isset($_POST['amountOfRooms']) && is_array($_POST['amountOfRooms'])) $amountOfRooms = $_POST['amountOfRooms']; else $amountOfRooms = array(); // Если пользователь отправил форму submit, и в параметрах нет значения amountOfRooms, значит пользователь не отметил ни один чекбокс из группы, чему соответствует пустой массив
        if (isset($_POST['district']) && is_array($_POST['district'])) $district = $_POST['district']; else $district = array(); // Если пользователь отправил форму submit, и в параметрах нет значения district, значит пользователь не отметил ни один чекбокс из группы, чему соответствует пустой массив
        if (isset($_POST['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
        if (isset($_POST['floor'])) $floor = htmlspecialchars($_POST['floor']);
        if (isset($_POST['minCost'])) $minCost = htmlspecialchars($_POST['minCost']);
        if (isset($_POST['maxCost'])) $maxCost = htmlspecialchars($_POST['maxCost']);
        if (isset($_POST['pledge'])) $pledge = htmlspecialchars($_POST['pledge']);
        if (isset($_POST['prepayment'])) $prepayment = htmlspecialchars($_POST['prepayment']);
        if (isset($_POST['withWho'])) $withWho = htmlspecialchars($_POST['withWho']);
        if (isset($_POST['linksToFriends'])) $linksToFriends = htmlspecialchars($_POST['linksToFriends']);
        if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']);
        if (isset($_POST['howManyChildren'])) $howManyChildren = htmlspecialchars($_POST['howManyChildren']);
        if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']);
        if (isset($_POST['howManyAnimals'])) $howManyAnimals = htmlspecialchars($_POST['howManyAnimals']);
        if (isset($_POST['termOfLease'])) $termOfLease = htmlspecialchars($_POST['termOfLease']);
        if (isset($_POST['additionalDescriptionOfSearch'])) $additionalDescriptionOfSearch = htmlspecialchars($_POST['additionalDescriptionOfSearch']);

        // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = userDataCorrect("validateSearchRequest"); // Параметр validateSearchRequest задает режим проверки "Проверка корректности уже существующих параметров поиска", который активирует только соответствующие ему проверки
        if (count($errors) == 0) $correctNewSearchRequest = "true"; else $correctNewSearchRequest = "false"; // Считаем ошибки, если 0, то можно принять и сохранить новые параметры поиска

        // Если данные верны, сохраним их в БД
        if ($correctNewSearchRequest == "true") {

            $amountOfRoomsSerialized = serialize($amountOfRooms);
            $districtSerialized = serialize($district);

            // Готовим пустой массив с идентификаторами объектов, которыми заинтересовался пользователь. Нужны только, если пользователь сформировал новый поисковый запрос, а не отредактировал уже имеющийся
            $interestingPropertysId = array();
            $interestingPropertysId = serialize($interestingPropertysId);

            if ($typeTenant == "true") {
                $rez = mysql_query("UPDATE searchrequests SET
            typeOfObject='" . $typeOfObject . "',
            amountOfRooms='" . $amountOfRoomsSerialized . "',
            adjacentRooms='" . $adjacentRooms . "',
            floor='" . $floor . "',
            minCost='" . $minCost . "',
            maxCost='" . $maxCost . "',
            pledge='" . $pledge . "',
            prepayment='" . $prepayment . "',
            district='" . $districtSerialized . "',
            withWho='" . $withWho . "',
            linksToFriends='" . $linksToFriends . "',
            children='" . $children . "',
            howManyChildren='" . $howManyChildren . "',
            animals='" . $animals . "',
            howManyAnimals='" . $howManyAnimals . "',
            termOfLease='" . $termOfLease . "',
            additionalDescriptionOfSearch='" . $additionalDescriptionOfSearch . "'
            WHERE userId = '" . $rowUsers['id'] . "'");
            } else {
                $rez = mysql_query("INSERT INTO searchrequests SET
            userId='" . $rowUsers['id'] . "',
            typeOfObject='" . $typeOfObject . "',
            amountOfRooms='" . $amountOfRoomsSerialized . "',
            adjacentRooms='" . $adjacentRooms . "',
            floor='" . $floor . "',
            minCost='" . $minCost . "',
            maxCost='" . $maxCost . "',
            pledge='" . $pledge . "',
            prepayment='" . $prepayment . "',
            district='" . $districtSerialized . "',
            withWho='" . $withWho . "',
            linksToFriends='" . $linksToFriends . "',
            children='" . $children . "',
            howManyChildren='" . $howManyChildren . "',
            animals='" . $animals . "',
            howManyAnimals='" . $howManyAnimals . "',
            termOfLease='" . $termOfLease . "',
            additionalDescriptionOfSearch='" . $additionalDescriptionOfSearch . "',
            interestingPropertysId='" . $interestingPropertysId . "'");
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

    /********************************************************************************
     * МОИ ОБЪЯВЛЕНИЯ. Наполнение шаблона из БД
     *******************************************************************************/

    // Шаблон для блока с описанием объявления для вкладки tabs-3 Мои объявления
    $tmpl_MyAdvert = "
<div class='news advertForPersonalPage {statusEng}'>
    <div class='newsHeader'>
        <span class='advertHeaderAddress'>{typeOfObject} по адресу: {address}{apartmentNumber}</span>
        <div class='advertHeaderStatus'>
            статус: {status}
        </div>
    </div>
    <div class='fotosWrapper'>
        <div class='middleFotoWrapper'>
            <img class='middleFoto' src='{urlFoto}'>
        </div>
    </div>
    <ul class='setOfInstructions'>
        {instructionPublish}
        <li>
            <a href='editadvert.php?propertyId={propertyId}'>редактировать</a>
        </li>
        <li>
            <a href='objdescription.php?propertyId={propertyId}'>подробнее</a>
        </li>
        {instructionDelete}
    </ul>
    <ul class='listDescription'>
        <li>
            <span class='headOfString' style='vertical-align: top;' title='Пользователи, запросившие контакты собственника по этому объявлению'>Возможные арендаторы:</span>{probableTenants}
        </li>
        <li>
            <br>
        </li>
        <li>
            <span class='headOfString'>Плата за аренду:</span> {costOfRenting} {currency} {utilities} {electricPower}
        </li>
        <li>
            <span class='headOfString'>Залог:</span> {bail}
        </li>
        <li>
            <span class='headOfString'>Предоплата:</span> {prepayment}
        </li>
        <li>
            <span class='headOfString'>Единовременная комиссия:</span>
            <span title='Предназначена для компенсации затрат собственника, связанных с поиском арендаторов'> {compensationMoney} {currency} ({compensationPercent}%) собственнику</span>
        </li>
        <li>
            <span class='headOfString'>Срок аренды:</span> {termOfLease}, c {dateOfEntry} {dateOfCheckOut}
        </li>
        <li>
            <span class='headOfString'>Адрес:</span> {address}
        </li>
         <li>
            <span class='headOfString'>Район:</span> {district}
        </li>
        <li>
            <span class='headOfString'>{amountOfRoomsName}</span> {amountOfRooms}{adjacentRooms}
        </li>
        <li>
            <span class='headOfString'>Площадь ({areaNames}):</span> {areaValues} м²
        </li>
        <li>
            <span class='headOfString'>{floorName}</span> {floor}
        </li>
        <li>
            <span class='headOfString'>{furnitureName}</span> {furniture}
        </li>
        <li>
            <span class='headOfString'>{repairName}</span> {repair}
        </li>
        <li>
            <span class='headOfString'>{parkingName}</span> {parking}
        </li>
        <li>
            <span class='headOfString'>Телефон собственника:</span>
            {contactTelephonNumber}, <a href='{urlMan}'>{name} {secondName}</a>, c {timeForRingBegin} до {timeForRingEnd}
        </li>
    </ul>
    <div class='clearBoth'></div>
</div>
";

    // Создаем бриф для каждого объявления пользователя на основе шаблона (для вкладки МОИ ОБЪЯВЛЕНИЯ), и в цикле объединяем их в один HTML блок - $briefOfAdverts.
    // Если объявлений у пользователя несколько, то в переменную, содержащую весь HTML - $briefOfAdverts, записываем каждое из них последовательно
    $briefOfAdverts = "";
    for ($i = 0; $i < count($rowPropertyArr); $i++) {
        // Копируем html-текст шаблона
        $currentAdvert = $tmpl_MyAdvert;

        // Подставляем класс в заголовок html объявления для применения соответствующего css оформления
        $str = "";
        if ($rowPropertyArr[$i]['status'] == "не опубликовано") $str = "unpublished";
        if ($rowPropertyArr[$i]['status'] == "опубликовано") $str = "published";
        $currentAdvert = str_replace("{statusEng}", $str, $currentAdvert);

        // В заголовке блока отображаем тип недвижимости, для красоты первую букву типа сделаем в верхнем регистре
        $str = getFirstCharUpper($rowPropertyArr[$i]['typeOfObject']);
        $currentAdvert = str_replace("{typeOfObject}", $str, $currentAdvert);

        // Адрес и номер квартиры, если он есть
        $str = $rowPropertyArr[$i]['address'];
        $currentAdvert = str_replace("{address}", $str, $currentAdvert);
        if ($rowPropertyArr[$i]['apartmentNumber'] != "") $str = ", № " . $rowPropertyArr[$i]['apartmentNumber']; else $str = "";
        $currentAdvert = str_replace("{apartmentNumber}", $str, $currentAdvert);

        // Статус объявления
        $str = $rowPropertyArr[$i]['status'];
        $currentAdvert = str_replace("{status}", $str, $currentAdvert);

        // Фотографию
        $str = "";
        if (isset($rowPropertyFotosArr[$i]['id']) && isset($rowPropertyFotosArr[$i]['extension'])) $str = "uploaded_files/" . $rowPropertyFotosArr[$i]['id'] . "." . $rowPropertyFotosArr[$i]['extension'];
        $currentAdvert = str_replace("{urlFoto}", $str, $currentAdvert);

        // Корректируем список инструкций, доступных пользователю
        $strInstructionPublish = "";
        $strInstructionDelete = "";
        if ($rowPropertyArr[$i]['status'] == "опубликовано") {
            $strInstructionPublish = "<li><a href='#'>снять с публикации</a></li>";
            $strInstructionDelete = "";
        }
        if ($rowPropertyArr[$i]['status'] == "не опубликовано") {
            $strInstructionPublish = "<li><a href='#'>опубликовать</a></li>";
            $strInstructionDelete = "<li><a href='#'>удалить</a></li>";
        }
        $currentAdvert = str_replace("{instructionPublish}", $strInstructionPublish, $currentAdvert);
        $currentAdvert = str_replace("{instructionDelete}", $strInstructionDelete, $currentAdvert);
        $str = $rowPropertyArr[$i]['id'];
        $currentAdvert = str_replace("{propertyId}", $str, $currentAdvert);

        /******* Список потенциальных арендаторов ******/
        $str = " ";
        // Получаем список id заинтересовавшихся арендаторов
        $visibleUsersId = unserialize($rowPropertyArr[$i]['visibleUsersId']);
        // Получаем имена и отчества заинтересовавшихся арендаторов
        // Составляем условие запроса к БД, указывая интересующие нас id объявлений
        $selectValue = "";
        for ($j = 0; $j < count($visibleUsersId); $j++) {
            $selectValue .= " id = '" . $visibleUsersId[$j] . "'";
            if ($j < count($visibleUsersId) - 1) $selectValue .= " OR";
        }
        // Перебираем полученные строки из таблицы, каждая из которых соответствует 1 потенциальному арендатору
        if ($rez = mysql_query("SELECT id, typeTenant, name, secondName FROM users WHERE " . $selectValue)) {
            for ($j = 0; $j < mysql_num_rows($rez); $j++) {
                if ($row = mysql_fetch_assoc($rez)) {
                    // Формируем из имен и отчеств строку гиперссылок с ссылками на страницы арендаторов
                    if ($row['typeTenant'] == "true") { // Если данный пользователь (арендатор) еще ищет недвижимость
                        $compId = $row['id'] * 5 + 2;
                        $str .= "<a href='man.php?compId=" . $compId . "'>" . $row['name'] . " " . $row['secondName'] . "</a>";
                    } else {
                        $str .= "<span title='Пользователь уже нашел недвижимость'>" . $row['name'] . " " . $row['secondName'] . "</span>";
                    }
                    if ($j < mysql_num_rows($rez) - 1) $str .= ", ";
                }
            }
        }
        // Заливаем полученную строку в шаблон
        if ($str == " ") $str = " -"; // Если нет ни одного потенциального арендатора
        $currentAdvert = str_replace("{probableTenants}", $str, $currentAdvert);

        // Все, что касается СТОИМОСТИ АРЕНДЫ
        $str = $rowPropertyArr[$i]['costOfRenting'];
        $currentAdvert = str_replace("{costOfRenting}", $str, $currentAdvert);
        $str = $rowPropertyArr[$i]['currency'];
        $currentAdvert = str_replace("{currency}", $str, $currentAdvert);
        if ($rowPropertyArr[$i]['utilities'] == "да") $str = "+ коммунальные услуги от " . $rowPropertyArr[$i]['costInSummer'] . " до " . $rowPropertyArr[$i]['costInWinter'] . " " . $rowPropertyArr[$i]['currency']; else $str = "";
        $currentAdvert = str_replace("{utilities}", $str, $currentAdvert);
        if ($rowPropertyArr[$i]['electricPower'] == "да") $str = "+ плата за электричество"; else $str = "";
        $currentAdvert = str_replace("{electricPower}", $str, $currentAdvert);
        if ($rowPropertyArr[$i]['bail'] == "есть") $str = $rowPropertyArr[$i]['bailCost'] . " " . $rowPropertyArr[$i]['currency']; else $str = "нет";
        $currentAdvert = str_replace("{bail}", $str, $currentAdvert);
        $str = $rowPropertyArr[$i]['prepayment'];
        $currentAdvert = str_replace("{prepayment}", $str, $currentAdvert);
        $str = $rowPropertyArr[$i]['compensationMoney'];
        $currentAdvert = str_replace("{compensationMoney}", $str, $currentAdvert);
        $str = $rowPropertyArr[$i]['compensationPercent'];
        $currentAdvert = str_replace("{compensationPercent}", $str, $currentAdvert);

        // Срок аренды
        $str = $rowPropertyArr[$i]['termOfLease'];
        $currentAdvert = str_replace("{termOfLease}", $str, $currentAdvert);
        $str = dateFromDBToView($rowPropertyArr[$i]['dateOfEntry']);
        $currentAdvert = str_replace("{dateOfEntry}", $str, $currentAdvert);
        if ($rowPropertyArr[$i]['bail'] == "есть") $str = $rowPropertyArr[$i]['bailCost'] . " " . $rowPropertyArr[$i]['currency']; else $str = "нет";
        if ($rowPropertyArr[$i]['dateOfCheckOut'] != "0000-00-00") $str = " по " . dateFromDBToView($rowPropertyArr[$i]['dateOfCheckOut']); else $str = "";
        $currentAdvert = str_replace("{dateOfCheckOut}", $str, $currentAdvert);

        // Подставляем название района для данного объекта недвижимости
        $str = $rowPropertyArr[$i]['district'];
        $currentAdvert = str_replace("{district}", $str, $currentAdvert);

        // Комнаты
        if ($rowPropertyArr[$i]['amountOfRooms'] != "0") {
            $str = $rowPropertyArr[$i]['amountOfRooms'];
            $strAmountOfRoomsName = "Количество комнат:";
        } else {
            $str = "";
            $strAmountOfRoomsName = "";
        }
        $currentAdvert = str_replace("{amountOfRooms}", $str, $currentAdvert);
        $currentAdvert = str_replace("{amountOfRoomsName}", $strAmountOfRoomsName, $currentAdvert);
        if ($rowPropertyArr[$i]['adjacentRooms'] == "да") {
            if ($rowPropertyArr[$i]['amountOfAdjacentRooms'] != "0") {
                $str = ", из них смежных: " . $rowPropertyArr[$i]['amountOfAdjacentRooms'];
            } else {
                $str = ", смежные";
            }
        } else {
            $str = "";
        }
        $currentAdvert = str_replace("{adjacentRooms}", $str, $currentAdvert);

        // Площади помещений
        $strAreaNames = "";
        $strAreaValues = "";
        if ($rowPropertyArr[$i]['typeOfObject'] != "квартира" && $rowPropertyArr[$i]['typeOfObject'] != "дом" && $rowPropertyArr[$i]['typeOfObject'] != "таунхаус" && $rowPropertyArr[$i]['typeOfObject'] != "дача" && $rowPropertyArr[$i]['typeOfObject'] != "гараж") {
            $strAreaNames .= "комнаты";
            $strAreaValues .= $rowPropertyArr[$i]['roomSpace'];
        }
        if ($rowPropertyArr[$i]['typeOfObject'] != "комната") {
            $strAreaNames .= "общая";
            $strAreaValues .= $rowPropertyArr[$i]['totalArea'];
        }
        if ($rowPropertyArr[$i]['typeOfObject'] != "комната" && $rowPropertyArr[$i]['typeOfObject'] != "гараж") {
            $strAreaNames .= "/жилая";
            $strAreaValues .= " / " . $rowPropertyArr[$i]['livingSpace'];
        }
        if ($rowPropertyArr[$i]['typeOfObject'] != "дача" && $rowPropertyArr[$i]['typeOfObject'] != "гараж") {
            $strAreaNames .= "/кухни";
            $strAreaValues .= " / " . $rowPropertyArr[$i]['kitchenSpace'];
        }
        $currentAdvert = str_replace("{areaNames}", $strAreaNames, $currentAdvert);
        $currentAdvert = str_replace("{areaValues}", $strAreaValues, $currentAdvert);

        // Этаж
        $strFloorName = "";
        $strFloor = "";
        if ($rowPropertyArr[$i]['floor'] != "0" && $rowPropertyArr[$i]['totalAmountFloor'] != "0") {
            $strFloorName = "Этаж:";
            $strFloor = $rowPropertyArr[$i]['floor'] . " из " . $rowPropertyArr[$i]['totalAmountFloor'];
        }
        if ($rowPropertyArr[$i]['numberOfFloor'] != "0") {
            $strFloorName = "Этажность:";
            $strFloor = $rowPropertyArr[$i]['numberOfFloor'];
        }
        $currentAdvert = str_replace("{floorName}", $strFloorName, $currentAdvert);
        $currentAdvert = str_replace("{floor}", $strFloor, $currentAdvert);

        // Мебель
        $strFurnitureName = "";
        $strFurniture = "";
        if ($rowPropertyArr[$i]['typeOfObject'] != "0" && $rowPropertyArr[$i]['typeOfObject'] != "гараж") {
            $strFurnitureName = "Мебель:";
            if (count(unserialize($rowPropertyArr[$i]['furnitureInLivingArea'])) != 0 || $rowPropertyArr[$i]['furnitureInLivingAreaExtra'] != "") $strFurniture = "есть в жилой зоне";
            if (count(unserialize($rowPropertyArr[$i]['furnitureInKitchen'])) != 0 || $rowPropertyArr[$i]['furnitureInKitchenExtra'] != "") if ($strFurniture == "") $strFurniture = "есть на кухне"; else $strFurniture .= ", есть на кухне";
            if (count(unserialize($rowPropertyArr[$i]['appliances'])) != 0 || $rowPropertyArr[$i]['appliancesExtra'] != "") if ($strFurniture == "") $strFurniture = "есть бытовая техника"; else $strFurniture .= ", есть бытовая техника";
            if ($strFurniture == "") $strFurniture = "нет";
        }
        $currentAdvert = str_replace("{furnitureName}", $strFurnitureName, $currentAdvert);
        $currentAdvert = str_replace("{furniture}", $strFurniture, $currentAdvert);

        // Ремонт
        $strRepairName = "";
        $strRepair = "";
        if ($rowPropertyArr[$i]['repair'] != "0" && $rowPropertyArr[$i]['furnish'] != "0") {
            $strRepairName = "Ремонт:";
            $strRepair = $rowPropertyArr[$i]['repair'] . ", отделка " . $rowPropertyArr[$i]['furnish'];
        }
        $currentAdvert = str_replace("{repairName}", $strRepairName, $currentAdvert);
        $currentAdvert = str_replace("{repair}", $strRepair, $currentAdvert);

        // Парковка
        $strParkingName = "";
        $strParking = "";
        if ($rowPropertyArr[$i]['parking'] != "0") {
            $strParkingName = "Парковка во дворе:";
            $strParking = $rowPropertyArr[$i]['parking'];
        }
        $currentAdvert = str_replace("{parkingName}", $strParkingName, $currentAdvert);
        $currentAdvert = str_replace("{parking}", $strParking, $currentAdvert);

        // Контакты собственника
        $str = $rowPropertyArr[$i]['contactTelephonNumber'];
        $currentAdvert = str_replace("{contactTelephonNumber}", $str, $currentAdvert);
        $str = "man.php?compId=" . ($rowUsers['id'] * 5 + 2); // compId - "вычисленное id пользователя. Равняется id пользователя * 5 + 2. Идентификатор пользователя подвергаем математическим вычислениям с целью скрыть его реальное значение от чужих глаз - для безопасности"
        $currentAdvert = str_replace("{urlMan}", $str, $currentAdvert);
        $str = $rowUsers['name'];
        $currentAdvert = str_replace("{name}", $str, $currentAdvert);
        $str = $rowUsers['secondName'];
        $currentAdvert = str_replace("{secondName}", $str, $currentAdvert);
        $str = $rowPropertyArr[$i]['timeForRingBegin'];
        $currentAdvert = str_replace("{timeForRingBegin}", $str, $currentAdvert);
        $str = $rowPropertyArr[$i]['timeForRingEnd'];
        $currentAdvert = str_replace("{timeForRingEnd}", $str, $currentAdvert);

        // Сформированный блок с описанием объявления добавляем в общую копилку. На вкладке tabs-3 (Мои объявления) полученный HTML всех блоков вставим в страницу.
        $briefOfAdverts .= $currentAdvert; // Добавим html-текст еще одного объявления. Готовим html-текст к добавлению на вкладку tabs-3 в Мои объявления
    }

    /********************************************************************************
     * СООБЩЕНИЯ. Наполнение шаблона из БД
     *******************************************************************************/

    // Шаблоны для блока с сообщениями для вкладки tabs-2 Сообщения
    $tmpl_Mes_NewTenant = "
    <div class='news unread'>
        <div class='newsHeader'>
            Претендент на {typeOfObject} по адресу: {address}{apartmentNumber}
        </div>

        <div class='fotosWrapper'>
            <div class='middleFotoWrapper'>
                <img class='middleFoto' src=''>
            </div>
        </div>

        <ul class='setOfInstructions'>
            <li>
                <a href='#'>подробнее</a>
            </li>
            <li>
                <a href='#'>прочитал</a>
            </li>
        </ul>

        <ul class='listDescription'>
            <li>
                <span class='headOfString'>ФИО:</span>
                Ушаков Дмитрий Владимирович
            </li>
            <li>
                <span class='headOfString'>Возраст:</span>
                25
            </li>
            <li>
                <span class='headOfString'>Срок аренды:</span>
                долгосрочно
            </li>
            <li>
                <span class='headOfString'>С кем жить:</span>
                несемейная пара
            </li>
            <li>
                <span class='headOfString'>Дети:</span>
                нет
            </li>
            <li>
                <span class='headOfString'>Животные:</span>
                нет
            </li>
            <li>
                <span class='headOfString'>Телефон:</span>
                89221431615
            </li>
        </ul>
        <div class='clearBoth'></div>
    </div>



<div class='news advertForPersonalPage {statusEng}'>
    <div class='newsHeader'>
        <span class='advertHeaderAddress'>{typeOfObject} по адресу: {address}{apartmentNumber}</span>
        <div class='advertHeaderStatus'>
            статус: {status}
        </div>
    </div>
    <div class='fotosWrapper'>
        <div class='middleFotoWrapper'>
            <img class='middleFoto' src='{urlFoto}'>
        </div>
    </div>
    <ul class='setOfInstructions'>
        {instructionPublish}
        <li>
            <a href='editadvert.php?propertyId={propertyId}'>редактировать</a>
        </li>
        <li>
            <a href='objdescription.php?propertyId={propertyId}'>подробнее</a>
        </li>
        {instructionDelete}
    </ul>
    <ul class='listDescription'>
        <li>
            <span class='headOfString' style='vertical-align: top;' title='Пользователи, запросившие контакты собственника по этому объявлению'>Возможные арендаторы:</span>{probableTenants}
        </li>
        <li>
            <br>
        </li>
        <li>
            <span class='headOfString'>Плата за аренду:</span> {costOfRenting} {currency} {utilities} {electricPower}
        </li>
        <li>
            <span class='headOfString'>Залог:</span> {bail}
        </li>
        <li>
            <span class='headOfString'>Предоплата:</span> {prepayment}
        </li>
        <li>
            <span class='headOfString'>Единовременная комиссия:</span>
            <span title='Предназначена для компенсации затрат собственника, связанных с поиском арендаторов'> {compensationMoney} {currency} ({compensationPercent}%) собственнику</span>
        </li>
        <li>
            <span class='headOfString'>Срок аренды:</span> {termOfLease}, c {dateOfEntry} {dateOfCheckOut}
        </li>
        <li>
            <span class='headOfString'>Адрес:</span> {address}
        </li>
         <li>
            <span class='headOfString'>Район:</span> {district}
        </li>
        <li>
            <span class='headOfString'>{amountOfRoomsName}</span> {amountOfRooms}{adjacentRooms}
        </li>
        <li>
            <span class='headOfString'>Площадь ({areaNames}):</span> {areaValues} м²
        </li>
        <li>
            <span class='headOfString'>{floorName}</span> {floor}
        </li>
        <li>
            <span class='headOfString'>{furnitureName}</span> {furniture}
        </li>
        <li>
            <span class='headOfString'>{repairName}</span> {repair}
        </li>
        <li>
            <span class='headOfString'>{parkingName}</span> {parking}
        </li>
        <li>
            <span class='headOfString'>Телефон собственника:</span>
            {contactTelephonNumber}, <a href='{urlMan}'>{name} {secondName}</a>, c {timeForRingBegin} до {timeForRingEnd}
        </li>
    </ul>
    <div class='clearBoth'></div>
</div>
";

    /***************************************************************************************************************
     * ИЗБРАННОЕ. Получаем данные по каждому избранному объявлению из БД и ниже наполняем вкладку tabs-5
     **************************************************************************************************************/

    // Получаем массив с идентификаторами избранных объявлений для данного пользователя
    $propertyIdArr = array();
    if (isset($rowUsers['favoritesPropertysId'])) $propertyIdArr = unserialize($rowUsers['favoritesPropertysId']);

    // Собираем строку WHERE для поискового запроса к БД
    $strWHERE = "";
    // Если есть хотя бы 1 идентификатор избранного объявления
    if (count($propertyIdArr) != 0) {
        $strWHERE = " (";
        for ($i = 0; $i < count($propertyIdArr); $i++) {
            $strWHERE .= " id = '" . $propertyIdArr[$i] . "'";
            if ($i < count($propertyIdArr) - 1) $strWHERE .= " OR";
        }
        $strWHERE .= ") AND (status = 'опубликовано')"; //TODO: сделать особое отображение (засеренное) для не опубликованных объявлений, тогда можно будет снять это ограничение на показ пользователю в избранных только еще опубликованных объектов
    }

    // Собираем и выполняем поисковый запрос на получение основных данных (id, координаты) по каждому из избранных объявлений
    $propertyLightArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
    if ($strWHERE != "") { // Если $strWHERE = "", значит у пользователя нет ни одного избранного объявления и выполнять поиск нам не нужно
        $rezProperty = mysql_query("SELECT id, coordX, coordY FROM property WHERE" . $strWHERE . " ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting"); // Сортируем по стоимости аренды и не ограничиваем количество объявлений - все, добавленные в избранные
        // Сортируем по стоимости аренды и ограничиваем количество 100 объявлениями
        if ($rezProperty != FALSE) {
            for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
                $propertyLightArr[] = mysql_fetch_assoc($rezProperty);
            }
        }
    }

    // Собираем строку WHERE для поискового запроса к БД по полным данным для не более чем 20-ти первых объектов
    $strWHERE = "";
    if (count($propertyLightArr) < 20) $limit = count($propertyLightArr); else $limit = 20;
    if ($limit != 0) {
        $strWHERE = " (";
        for ($i = 0; $i < $limit; $i++) {
            $strWHERE .= " id = '" . $propertyLightArr[$i]['id'] . "'";
            if ($i < $limit - 1) $strWHERE .= " OR";
        }
        $strWHERE .= ")";
    }

    // Собираем и выполняем поисковый запрос к БД - получаем подробные сведения по не более чем 20-ти первым в списке объявлениям
    $propertyFullArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
    if ($strWHERE != "") {
        $rezProperty = mysql_query("SELECT * FROM property WHERE" . $strWHERE . " ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting LIMIT 20"); // Сортируем по стоимости аренды и ограничиваем количество 20 объявлениями, чтобы запрос не проходил таблицу до конца, когда выделит нужные нам 20 объектов
        if ($rezProperty != FALSE) {
            for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
                $row = mysql_fetch_assoc($rezProperty);
                if ($row != false) $propertyFullArr[$row['id']] = $row;
            }
        }
    }

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Личный кабинет</title>
    <meta name="description" content="Личный кабинет">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/fileuploader.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        #newAdvertButton {
            margin-bottom: 10px;
        }
    </style>

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- Русификатор виджета календарь -->
    <script src="js/vendor/jquery.ui.datepicker-ru.js"></script>
    <!-- Загрузчик фотографий на AJAX -->
    <script src="js/vendor/fileuploader.js" type="text/javascript"></script>
    <!-- Загружаем библиотеку для работы с картой от Яндекса -->
    <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

</head>

<body>
<div class="page_without_footer">

<!-- Всплывающее поле для отображения списка ошибок, полученных при проверке данных на сервере (PHP)-->
<div id="userMistakesBlock" class="ui-widget">
    <div class="ui-state-highlight ui-corner-all">
        <div>
            <p>
                <span class="icon-mistake ui-icon ui-icon-info"></span>
                <span
                    id="userMistakesText">Для продолжения, пожалуйста, дополните или исправьте следующие данные:</span>
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

<!-- Добавялем невидимый input для того, чтобы передать тип пользователя (собственник/арендатор) - это используется в JS для простановки обязательности полей для заполнения -->
<?php echo "<input type='hidden' class='userType' typeTenant='" . $typeTenant . "' typeOwner='" . $typeOwner . "' correctNewSearchRequest='" . $correctNewSearchRequest . "'>"; ?>

<!-- Добавялем невидимый input для того, чтобы передать идентификатор вкладки, которую нужно открыть через JS -->
<?php
    // При загрузке страницы открываем вкладку № 4 "Поиск", если пользователь создает поисковый запрос и его личные данные для этого достаточны ($correct == "true"), либо если он редактирует поисковый запрос ($correctNewSearchRequest == "true", $correctNewSearchRequest == "false"). В ином случае - открываем вкладку №1.
    if ($correct == "true" || $correctNewSearchRequest == "true" || $correctNewSearchRequest == "false") $tabsId = "tabs-4"; elseif (isset($_GET['tabsId'])) $tabsId = $_GET['tabsId']; else $tabsId = "tabs-1";
    echo "<input type='hidden' class='tabsId' tabsId='" . $tabsId . "'>";
?>

<!-- Сформируем и вставим заголовок страницы -->
<?php
    include("header.php");
?>

<div class="page_main_content">
<div class="headerOfPage">
    Личный кабинет
</div>
<div id="tabs">
<ul>
    <li>
        <a href="#tabs-1">Профиль</a>
    </li>
    <li>
        <a href="#tabs-2">Сообщения (<span class='amountOfNewMessages' id="amountUnreadNews">15</span>)</a>
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
<?php if ($correctNewProfileParameters != "false"): ?>
<!-- Блок с нередактируемыми параметрами Профайла не выдается только в 1 случае: если пользователь корректировал свои параметры, и они не прошли проверку -->
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
                if ($currentStatusEducation == "нет") {
                    echo "нет";
                }
                if ($currentStatusEducation == "сейчас учусь") {
                    if (isset($almamater)) echo $almamater . ", ";
                    if (isset($speciality)) echo $speciality . ", ";
                    if (isset($ochnoZaochno)) echo $ochnoZaochno . ", ";
                    if (isset($kurs)) echo "курс: " . $kurs;
                }
                if ($currentStatusEducation == "закончил") {
                    if (isset($almamater)) echo $almamater . ", ";
                    if (isset($speciality)) echo $speciality . ", ";
                    if (isset($ochnoZaochno)) echo $ochnoZaochno . ", ";
                    if (isset($yearOfEnd)) echo "<span style='white-space: nowrap;'>закончил в " . $yearOfEnd . " году</span>";
                }
                ?>
            </li>
            <li>
                <span class="headOfString">Работа:</span> <?php
                if ($statusWork == "не работаю") {
                    echo "не работаю";
                } else {
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
                <span class="headOfString">Внешность:</span> <?php
                if (isset($nationality) && $nationality != "0") echo "<span style='white-space: nowrap;'>" . $nationality . "</span>";
                ?>
            </li>
            <li>
                <span class="headOfString">Пол:</span> <?php
                if (isset($sex)) echo $sex;
                ?>
            </li>
            <li>
                <span class="headOfString">День рождения:</span> <?php
                if (isset($birthday)) echo $birthday;
                ?>
            </li>
            <li>
                <span class="headOfString">Возраст:</span> <?php
                $date = substr($birthday, 0, 2);
                $month = substr($birthday, 3, 2);
                $year = substr($birthday, 6, 4);
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
                        <option value="мужской" <?php if ($sex == "мужской") echo "selected";?>>мужской</option>
                        <option value="женский" <?php if ($sex == "женский") echo "selected";?>>женский</option>
                    </select>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Внешность: </span>

                <div class="searchItemBody">
                    <select name="nationality" id="nationality" validations='validate[required]'>
                        <option value="0" <?php if ($nationality == "0") echo "selected";?>></option>
                        <option
                            value="славянская" <?php if ($nationality == "славянская") echo "selected";?>>
                            славянская
                        </option>
                        <option
                            value="европейская" <?php if ($nationality == "европейская") echo "selected";?>>
                            европейская
                        </option>
                        <option
                            value="азиатская" <?php if ($nationality == "азиатская") echo "selected";?>>
                            азиатская
                        </option>
                        <option
                            value="кавказская" <?php if ($nationality == "кавказская") echo "selected";?>>
                            кавказская
                        </option>
                        <option
                            value="африканская" <?php if ($nationality == "африканская") echo "selected";?>>
                            африканская
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
                    </div>
                    <span class="searchItemLabel">Логин: </span>

                    <div class="searchItemBody">
                        <?php echo $login;?>
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
                        value="нет" <?php if ($currentStatusEducation == "нет") echo "selected";?>>
                        Нигде не учился
                    </option>
                    <option
                        value="сейчас учусь" <?php if ($currentStatusEducation == "сейчас учусь") echo "selected";?>>
                        Сейчас учусь
                    </option>
                    <option
                        value="закончил" <?php if ($currentStatusEducation == "закончил") echo "selected";?>>
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
                    <option value="очно" <?php if ($ochnoZaochno == "очно") echo "selected";?>>Очно</option>
                    <option value="заочно" <?php if ($ochnoZaochno == "заочно") echo "selected";?>>Заочно</option>
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
            Статус занятости:
        </legend>
        <div>
            <select name="statusWork" id="statusWork">
                <option value="0" <?php if ($statusWork == "0") echo "selected";?>></option>
                <option value="работаю" <?php if ($statusWork == "работаю") echo "selected";?>>работаю</option>
                <option value="не работаю" <?php if ($statusWork == "не работаю") echo "selected";?>>не работаю</option>
            </select>
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
        <a href="personal.php?tabsId=1" style="margin-right: 10px;">Отмена</a>
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
        На этой вкладке располагается информация о важных событиях, случившихся на ресурсе Хани Хом, как например:
        появление
        новых потенциальных арендаторов, заинтересовавшихся Вашим объявлением, или новых объявлений, которые подходят
        под
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
                публикацию на основных интернет-порталах города. Это обеспечит максимальный приток арендаторов, из
                которых
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
    <?php
    echo $briefOfAdverts;
    ?>
</div>

<div id="tabs-4">
<div class="shadowText">
    На этой вкладке Вы можете задать параметры, в соответствии с которыми ресурс Хани Хом будет осуществлять
    автоматический поиск объявлений на портале и будет оповещать Вас о появлении новых объектов по указанному в
    профиле
    e-mail
</div>
<?php if ($typeTenant != "true" && $correct != "true" && $correctNewSearchRequest == "null"): ?>
<!-- Если пользователь еще не сформировал поисковый запрос (а значит не является арендатором) и он либо не нажимал на кнопку формирования запроса, либо нажимал, но не прошел проверку на полноту информации о пользователи, то ему доступна только кнопка формирования нового запроса. В ином случае будет отображаться сам поисковый запрос пользователя, либо форма для его заполнения -->
<form name="createSearchRequest" method="post">
    <button type="submit" name="createSearchRequestButton" id='createSearchRequestButton' class='left-bottom'>
        Запрос на поиск
    </button>
</form>
    <?php endif;?>
<?php if ($typeTenant == "true" && $correctNewSearchRequest != "false"): ?>
<!-- Если пользователь является арендатором и (если он редактировал пар-ры поиска) после редактирования параметров поиска ошибок не обнаружено, то у пользователя уже сформирован корректный поисковый запрос, который мы и показываем на этой вкладке -->
<div id="notEditingSearchParametersBlock" class="objectDescription">
    <div class="setOfInstructions">
        <li><a href="#">редактировать</a></li>
        <li><a href="personal.php?action=deleteSearchRequest&tabsId=4"
               title="Удаляет запрос на поиск - кликните по этой ссылке, когда Вы найдете недвижимость">удалить</a>
        </li>
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
                    <td class="objectDescriptionBody">
            <span>
            <?php
                if (isset($typeOfObject) && $typeOfObject != "0") echo $typeOfObject; else echo "любой";
                ?>
            </span>
                    </td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Количество комнат:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($amountOfRooms) && count($amountOfRooms) != "0") for ($i = 0; $i < count($amountOfRooms); $i++) {
                            echo $amountOfRooms[$i];
                            if ($i < count($amountOfRooms) - 1) echo ", ";
                        } else echo "любое";
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Комнаты смежные:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($adjacentRooms) && $adjacentRooms != "0") echo $adjacentRooms; else echo "любые";
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Этаж:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($floor) && $floor != "0") echo $floor; else echo "любой";
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
                        if (isset($minCost) && $minCost != "0") echo "<span>" . $minCost . "</span> руб."; else echo "любая";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
                    <td class="objectDescriptionBody"><?php
                        if (isset($maxCost) && $maxCost != "0") echo "<span>" . $maxCost . "</span> руб."; else echo "любая";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Залог до:</td>
                    <td class="objectDescriptionBody"><?php
                        if (isset($pledge) && $pledge != "0") echo "<span>" . $pledge . "</span> руб."; else echo "любой";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Максимальная предоплата:</td>
                    <td class="objectDescriptionBody"><?php
                        if (isset($prepayment) && $prepayment != "0") echo "<span>" . $prepayment . "</span>"; else echo "любая";
                        ?></td>
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
                if (isset($district) && count($district) != 0) { // Если район указан пользователем
                    echo "<tr><td>";
                    for ($i = 0; $i < count($district); $i++) { // Выводим названия всех районов, в которых ищет недвижимость пользователь
                        echo $district[$i];
                        if ($i < count($district) - 1) echo ", ";
                    }
                    echo  "</td></tr>";
                } else {
                    echo "<tr><td>" . "любой" . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </fieldset>
    <div class="clearBoth"></div>
    <fieldset class="notEdited">
        <legend>
            Особые параметры поиска
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Как собираетесь проживать:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($withWho) && $withWho != "0") echo $withWho; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
                if ($withWho != "самостоятельно" && $withWho != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Информация о сожителях:</td><td class='objectDescriptionBody''><span>";
                    if (isset($linksToFriends)) echo $linksToFriends;
                    echo "</span></td></tr>";
                }
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Дети:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($children) && $children != "0") echo $children; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
                if ($children != "без детей" && $children != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Количество детей и их возраст:</td><td class='objectDescriptionBody''><span>";
                    if (isset($howManyChildren)) echo $howManyChildren;
                    echo "</span></td></tr>";
                }
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Животные:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($animals) && $animals != "0") echo $animals; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
                if ($animals != "без животных" && $animals != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Количество животных и их вид:</td><td class='objectDescriptionBody''><span>";
                    if (isset($howManyAnimals)) echo $howManyAnimals;
                    echo "</span></td></tr>";
                }
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Срок аренды:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($termOfLease) && $termOfLease != "0") echo $termOfLease; else echo "не указан";
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
</div>
    <?php endif;?>
<?php if ($typeTenant == "true" || $correct == "true" || $correctNewSearchRequest == "false"): ?>
<!-- Если пользователь является арендатором, то вместе с отображением текущих параметров поискового запроса мы выдаем скрытую форму для их редактирования, также мы выдаем видимую форму для редактирования параметров поиска в случае, если пользователь нажал на кнопку Нового поискового запроса и проверка на корректность его данных Профиля профла успешно, а также в случае если пользователь корректировал данные поискового запроса, но они не прошли проверку -->
<form method="post" name="searchParameters" id="extendedSearchParametersBlock">
    <div id="leftBlockOfSearchParameters" style="display: inline-block;">
        <fieldset class="edited">
            <legend>
                Характеристика объекта
            </legend>
            <div class="searchItem">
                <span class="searchItemLabel"> Тип: </span>

                <div class="searchItemBody">
                    <select name="typeOfObject" id="typeOfObject">
                        <option value="0" <?php if ($typeOfObject == "0") echo "selected";?>></option>
                        <option value="квартира" <?php if ($typeOfObject == "квартира") echo "selected";?>>
                            квартира
                        </option>
                        <option value="комната" <?php if ($typeOfObject == "комната") echo "selected";?>>комната
                        </option>
                        <option value="дом" <?php if ($typeOfObject == "дом") echo "selected";?>>дом, коттедж
                        </option>
                        <option value="таунхаус" <?php if ($typeOfObject == "таунхаус") echo "selected";?>>
                            таунхаус
                        </option>
                        <option value="дача" <?php if ($typeOfObject == "дача") echo "selected";?>>дача</option>
                        <option value="гараж" <?php if ($typeOfObject == "гараж") echo "selected";?>>гараж</option>
                    </select>
                </div>
            </div>
            <div class="searchItem" notavailability="typeOfObject_гараж">
                <span class="searchItemLabel"> Количество комнат: </span>

                <div class="searchItemBody">
                    <input type="checkbox" value="1" name="amountOfRooms[]"
                        <?php
                        foreach ($amountOfRooms as $value) {
                            if ($value == "1") {
                                echo "checked";
                                break;
                            }
                        }
                        ?>>
                    1
                    <input type="checkbox" value="2"
                           name="amountOfRooms[]" <?php
                        foreach ($amountOfRooms as $value) {
                            if ($value == "2") {
                                echo "checked";
                                break;
                            }
                        }
                        ?>>
                    2
                    <input type="checkbox" value="3"
                           name="amountOfRooms[]" <?php
                        foreach ($amountOfRooms as $value) {
                            if ($value == "3") {
                                echo "checked";
                                break;
                            }
                        }
                        ?>>
                    3
                    <input type="checkbox" value="4"
                           name="amountOfRooms[]" <?php
                        foreach ($amountOfRooms as $value) {
                            if ($value == "4") {
                                echo "checked";
                                break;
                            }
                        }
                        ?>>
                    4
                    <input type="checkbox" value="5"
                           name="amountOfRooms[]" <?php
                        foreach ($amountOfRooms as $value) {
                            if ($value == "5") {
                                echo "checked";
                                break;
                            }
                        }
                        ?>>
                    5
                    <input type="checkbox" value="6"
                           name="amountOfRooms[]" <?php
                        foreach ($amountOfRooms as $value) {
                            if ($value == "6") {
                                echo "checked";
                                break;
                            }
                        }
                        ?>>
                    6...
                </div>
            </div>
            <div class="searchItem" notavailability="typeOfObject_гараж">
                <span class="searchItemLabel"> Комнаты смежные: </span>

                <div class="searchItemBody">
                    <select name="adjacentRooms">
                        <option value="0" <?php if ($adjacentRooms == "0") echo "selected";?>></option>
                        <option
                            value="не имеет значения" <?php if ($adjacentRooms == "не имеет значения") echo "selected";?>>
                            не
                            имеет значения
                        </option>
                        <option
                            value="только изолированные" <?php if ($adjacentRooms == "только изолированные") echo "selected";?>>
                            только изолированные
                        </option>
                    </select>
                </div>
            </div>
            <div class="searchItem"
                 notavailability="typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
                <span class="searchItemLabel"> Этаж: </span>

                <div class="searchItemBody">
                    <select name="floor">
                        <option value="0" <?php if ($floor == "0") echo "selected";?>></option>
                        <option value="любой" <?php if ($floor == "любой") echo "selected";?>>любой</option>
                        <option value="не первый" <?php if ($floor == "не первый") echo "selected";?>>не первый
                        </option>
                        <option
                            value="не первый и не последний" <?php if ($floor == "не первый и не последний") echo "selected";?>>
                            не первый и не
                            последний
                        </option>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset class="edited">
            <legend>
                Стоимость
            </legend>
            <div class="searchItem">
                <div class="searchItemLabel"
                     title="Укажите, сколько Вы готовы платить в месяц за аренду недвижимости с учетом стоимости коммунальных услуг (если они оплачиваются дополнительно)">
                    Арендная плата (в месяц с учетом ком. усл.)
                </div>
                <div class="searchItemBody">
                    от
                    <input type="text" name="minCost" id="minCost" size="10"
                           maxlength="8" <?php echo "value='$minCost'";?>>
                    руб., до
                    <input type="text" name="maxCost" id="maxCost" size="10"
                           maxlength="8" <?php echo "value='$maxCost'";?>>
                    руб.
                </div>
            </div>
            <div class="searchItem"
                 title="Какую сумму Вы готовы передать собственнику в качестве возвращаемого гарантийного депозита">
                <span class="searchItemLabel"> Залог </span>

                <div class="searchItemBody">
                    до
                    <input type="text" name="pledge" size="10" maxlength="8" <?php echo "value='$pledge'";?>>
                    руб.
                </div>
            </div>
            <div class="searchItem"
                 title="Какую предоплату за проживание Вы готовы внести">
                <span class="searchItemLabel"> Максимальная предоплата: </span>

                <div class="searchItemBody">
                    <select name="prepayment">
                        <option value="0" <?php if ($prepayment == "0") echo "selected";?>></option>
                        <option value="нет" <?php if ($prepayment == "нет") echo "selected";?>>нет</option>
                        <option value="1 месяц" <?php if ($prepayment == "1 месяц") echo "selected";?>>1 месяц
                        </option>
                        <option value="2 месяца" <?php if ($prepayment == "2 месяца") echo "selected";?>>2 месяца
                        </option>
                        <option value="3 месяца" <?php if ($prepayment == "3 месяца") echo "selected";?>>3 месяца
                        </option>
                        <option value="4 месяца" <?php if ($prepayment == "4 месяца") echo "selected";?>>4 месяца
                        </option>
                        <option value="5 месяцев" <?php if ($prepayment == "5 месяцев") echo "selected";?>>5
                            месяцев
                        </option>
                        <option value="6 месяцев" <?php if ($prepayment == "6 месяцев") echo "selected";?>>6
                            месяцев
                        </option>
                    </select>
                </div>
            </div>
        </fieldset>
    </div>
    <div id="rightBlockOfSearchParameters">
        <fieldset class="edited">
            <legend>
                Район
            </legend>
            <div class="searchItem">
                <div class="searchItemBody">
                    <ul>
                        <?php
                        if (isset($allDistrictsInCity)) {
                            foreach ($allDistrictsInCity as $value) { // Для каждого идентификатора района и названия формируем чекбокс
                                echo "<li><input type='checkbox' name='district[]' value='" . $value . "'";
                                foreach ($district as $valueDistrict) {
                                    if ($valueDistrict == $value) {
                                        echo "checked";
                                        break;
                                    }
                                }
                                echo "> " . $value . "</li>";
                            }
                        }
                        ?>
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
        <div class="searchItem" notavailability="typeOfObject_гараж">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Как собираетесь проживать: </span>

            <div class="searchItemBody">
                <select name="withWho" id="withWho">
                    <option value="0" <?php if ($withWho == "0") echo "selected";?>></option>
                    <option value="самостоятельно" <?php if ($withWho == "самостоятельно") echo "selected";?>>
                        самостоятельно
                    </option>
                    <option value="семья" <?php if ($withWho == "семья") echo "selected";?>>семьей
                    </option>
                    <option value="пара" <?php if ($withWho == "пара") echo "selected";?>>парой
                    </option>
                    <option value="2 мальчика" <?php if ($withWho == "2 мальчика") echo "selected";?>>2 мальчика
                    </option>
                    <option value="2 девочки" <?php if ($withWho == "2 девочки") echo "selected";?>>2 девочки
                    </option>
                    <option value="со знакомыми" <?php if ($withWho == "со знакомыми") echo "selected";?>>со
                        знакомыми
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem" id="withWhoDescription" style="display: none;">
            <div class="searchItemLabel">
                Информация о сожителях:
            </div>
            <div class="searchItemBody">
                <textarea name="linksToFriends" cols="40" rows="3"><?php echo $linksToFriends;?></textarea>
            </div>
        </div>
        <div class="searchItem" notavailability="typeOfObject_гараж">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Дети: </span>

            <div class="searchItemBody">
                <select name="children" id="children">
                    <option value="0" <?php if ($children == "0") echo "selected";?>></option>
                    <option value="без детей" <?php if ($children == "без детей") echo "selected";?>>без детей
                    </option>
                    <option
                        value="с детьми младше 4-х лет" <?php if ($children == "с детьми младше 4-х лет") echo "selected";?>>
                        с детьми
                        младше 4-х лет
                    </option>
                    <option
                        value="с детьми старше 4-х лет" <?php if ($children == "с детьми старше 4-х лет") echo "selected";?>>
                        с детьми
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
        <div class="searchItem" notavailability="typeOfObject_гараж">
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Животные: </span>

            <div class="searchItemBody">
                <select name="animals" id="animals">
                    <option value="0" <?php if ($animals == "0") echo "selected";?>></option>
                    <option value="без животных" <?php if ($animals == "без животных") echo "selected";?>>без
                        животных
                    </option>
                    <option value="с животным(ми)" <?php if ($animals == "с животным(ми)") echo "selected";?>>с
                        животным(ми)
                    </option>
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
            <div class="required">
                *
            </div>
            <span class="searchItemLabel">Срок аренды:</span>

            <div class="searchItemBody">
                <select name="termOfLease" id="termOfLease">
                    <option value="0" <?php if ($termOfLease == "0") echo "selected";?>></option>
                    <option value="длительный срок" <?php if ($termOfLease == "длительный срок") echo "selected";?>>
                        длительный срок (от года)
                    </option>
                    <option
                        value="несколько месяцев" <?php if ($termOfLease == "несколько месяцев") echo "selected";?>>
                        несколько месяцев (до года)
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <div class="required"></div>
        <span class="searchItemLabel">
            Дополнительные условия поиска:
        </span>
        </div>
        <div class="searchItem">
            <div class="required"></div>
            <div class="searchItemBody">
                <textarea name="additionalDescriptionOfSearch" cols="50"
                          rows="4"><?php echo $additionalDescriptionOfSearch;?></textarea>
            </div>
        </div>
    </fieldset>

    <div class="clearBoth"></div>
    <div class="bottomButton">
        <a href="personal.php?tabsId=4" style="margin-right: 10px;">Отмена</a>
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

    <?php
    // Для целей ускорения загрузки перенес блок php кода сюда - это позволит браузеру грузить нужные библиотеки в то время, как сервер будет готовить представление для таблиц с данными об объектах недвижимости

    /***************************************************************************************************************
     * Оформляем полученные объявления в красивый HTML для размещения на странице
     **************************************************************************************************************/
    echo getSearchResultHTML($propertyLightArr, $propertyFullArr, $userId);

    ?>

</div>

</div><!-- /end.tabs -->

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
<script src="js/main.js"></script>
<script src="js/personal.js"></script>
<script src="js/searchResult.js"></script>
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
