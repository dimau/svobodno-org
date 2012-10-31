<?php

    //TODO: удалить строку!
    //include_once 'lib/function_searchResult.php'; // Подключаем файл с функциями по HTML оформлению результатов поиска

    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные классы
    include_once 'classesForProjectSecurityName/GlobFunc.php';
    include_once 'classesForProjectSecurityName/User.php';
    include_once 'classesForProjectSecurityName/CollectionProperty.php';
    include_once 'classesForProjectSecurityName/Property.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем объект пользователя
    $user = new User($globFunc, $DBlink);

    // Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
    if (!$user->login()) {
        header('Location: login.php');
    }

    /*************************************************************************************
     * Получаем информацию о пользователе из БД сервера
     ************************************************************************************/

    // Анкетные (основные персональные) данные пользователя
    $user->writeCharacteristicFromDB();

    // Данные поискового запроса
    if (isset($_GET['action']) && $_GET['action'] == 'deleteSearchRequest') {
        // Если пользователь пожелал удалить поисковый запрос, то это нужно сделать вместо получения данных из БД
        $user->removeSearchRequest();
    } else {
        // Иначе получим данные из БД по поисковому запросу данного пользователя в параметры объекта $user
        $user->writeSearchRequestFromDB();
    }

    // Информация о фотографиях пользователя
    $user->writeFotoInformationFromDB();

    // Если пользователь - собственник, получим коллекцию его объектов недвижимости
    /*if ($user->isOwner()) {
        $propertyCol = new PropertyCollection($globFunc, $DBlink);
        $propertyCol->buildFromOwnerId($user->getId());
    }*/







    // Получаем информацию о фотографиях объектов недвижимости пользователя (возможно он является собственником)
    // На самом деле мы получаем информацию только по 1 первой попавшейся фотке каждого из объектов недвижимости
    /*$rowPropertyFotosArr = array();
    for ($i = 0; $i < count($rowPropertyArr); $i++) {
        $rezPropertyFotos = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $rowPropertyArr[$i]['id'] . "' AND status = 'основная'");
        $rowTemp = mysql_fetch_assoc($rezPropertyFotos);
        if ($rowTemp != FALSE) $rowPropertyFotosArr[$i] = $rowTemp; else $rowPropertyFotosArr[$i] = array(); // Кажется, текущее решение не позволит перепутать фотографии от разных объявлений
    } */

    // Готовим массив со списком районов в городе пользователя
    $allDistrictsInCity = $globFunc->getAllDistrictsInCity("Екатеринбург");

    // Инициализируем переменные корректности - используется при формировании нового Запроса на поиск
    $correct = NULL; // Отражает корректность и полноту личных данных пользователя, необходимую для создания НОВОГО поискового запроса.
    $correctNewSearchRequest = NULL; // Отражает корректность отредактированных пользователем параметров поиска
    $correctNewProfileParameters = NULL; // Корректность личных данных пользователя. Работает, если он пытается изменить личные данные своего профайла. Проверка осуществляется в соответствии со статусом пользователя (арендатор или собственник)

    /********************************************************************************
     * РЕДАКТИРОВАНИЕ ЛИЧНЫХ ДАННЫХ ПРОФИЛЯ. Если пользователь отправил редактированные параметры своего профиля
     *******************************************************************************/
    if (isset($_POST['saveProfileParameters'])) {

        // Записываем POST параметры в параметры объекта пользователя
        $user->writePOSTparameters();

        // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = $user->userDataCorrect("validateProfileParameters");

        // Установим признак корректности введенных пользователем новых личных параметров
        if (is_array($errors) && count($errors) == 0) {
            $correctNewProfileParameters = TRUE;
        } else {
            $correctNewProfileParameters = FALSE;
        }

        // Если данные верны, сохраним их в БД
        if ($correctNewProfileParameters == TRUE) {
            // Личная информация
            $correctSaveCharacteristicToDB = $user->saveCharacteristicToDB("edit");
            // Сохраним информацию о фотографиях пользователя
            $user->saveFotoInformationToDB();
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
        $errors = userDataCorrect("validateSearchRequest", $DBlink); // Параметр validateSearchRequest задает режим проверки "Проверка корректности уже существующих параметров поиска", который активирует только соответствующие ему проверки
        if (count($errors) == 0) $correctNewSearchRequest = TRUE; else $correctNewSearchRequest = FALSE; // Считаем ошибки, если 0, то можно принять и сохранить новые параметры поиска

        // Если данные верны, сохраним их в БД
        if ($correctNewSearchRequest == TRUE) {

            $amountOfRoomsSerialized = serialize($amountOfRooms);
            $districtSerialized = serialize($district);

            // Готовим пустой массив с идентификаторами объектов, которыми заинтересовался пользователь. Нужны только, если пользователь сформировал новый поисковый запрос, а не отредактировал уже имеющийся
            $interestingPropertysId = array();
            $interestingPropertysId = serialize($interestingPropertysId);

            if ($typeTenant == TRUE) {
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
        $errors = userDataCorrect("createSearchRequest", $DBlink); // Параметр createSearchRequest задает режим проверки "Создание запроса на поиск", который активирует только соответствующие ему проверки
        if (count($errors) == 0) $correct = TRUE; else $correct = FALSE; // Считаем ошибки, если 0, то можно выдать пользователю форму для ввода параметров Запроса поиска
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
    <div class='fotosWrapper fotoNonInteractive'>
        <div class='smallFotoWrapper'>
            <img class='smallFoto gallery' src='{urlFoto1}' href='{hrefFoto1}'>
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
            <span class='headOfString'>Срок аренды:</span> {termOfLease}, c {dateOfEntry} {dateOfCheckOut}
        </li>
        <li>
            <span class='headOfString'>{furnitureName}</span> {furniture}
        </li>
        <li>
            <span class='headOfString'>{repairName}</span> {repair}
        </li>
        <li>
            <span class='headOfString'>Контактный телефон:</span>
            {contactTelephonNumber}, {name} {secondName}, c {timeForRingBegin} до {timeForRingEnd}
        </li>
    </ul>
    <div class='clearBoth'></div>
</div>
";

    /*
    // Создаем бриф для каждого объявления пользователя на основе шаблона (для вкладки МОИ ОБЪЯВЛЕНИЯ), и в цикле объединяем их в один HTML блок - $briefOfAdverts.
    // Если объявлений у пользователя несколько, то в переменную, содержащую весь HTML - $briefOfAdverts, записываем каждое из них последовательно
    $briefOfAdverts = "";
    for ($i = 0; $i < count($rowPropertyArr); $i++) {

        // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне баллуна
        $arrMyAdvertReplace = array();

        // Подставляем класс в заголовок html объявления для применения соответствующего css оформления
        $arrMyAdvertReplace['statusEng'] = "";
        if ($rowPropertyArr[$i]['status'] == "не опубликовано") $arrMyAdvertReplace['statusEng'] = "unpublished";
        if ($rowPropertyArr[$i]['status'] == "опубликовано") $arrMyAdvertReplace['statusEng'] = "published";

        // В заголовке блока отображаем тип недвижимости, для красоты первую букву типа сделаем в верхнем регистре
        $arrMyAdvertReplace['typeOfObject'] = "";
        $arrMyAdvertReplace['typeOfObject'] = getFirstCharUpper($rowPropertyArr[$i]['typeOfObject']);

        // Адрес и номер квартиры, если он есть
        $arrMyAdvertReplace['address'] = "";
        if (isset($rowPropertyArr[$i]['address']))  $arrMyAdvertReplace['address'] = $rowPropertyArr[$i]['address'];
        $arrMyAdvertReplace['apartmentNumber'] = "";
        if (isset($rowPropertyArr[$i]['apartmentNumber']) && $rowPropertyArr[$i]['apartmentNumber'] != "") $arrMyAdvertReplace['apartmentNumber'] = ", № " . $rowPropertyArr[$i]['apartmentNumber'];

        // Статус объявления
        $arrMyAdvertReplace['status'] = "";
        $arrMyAdvertReplace['status'] = $rowPropertyArr[$i]['status'];

        // Фото
        $arrMyAdvertReplace['urlFoto1'] = "";
        $arrMyAdvertReplace['hrefFoto1'] = "";
        $arrayFotoInfAboutOneProperty = array($rowPropertyFotosArr[$i]); // функция getLinksToFotos ожидает получить массив массивов, каждый из которых будет содержать сведения об 1 фотографии
        $linksToFotosArr = getLinksToFotos($arrayFotoInfAboutOneProperty, $rowPropertyArr[$i]['id'], 'small');
        $arrMyAdvertReplace['urlFoto1'] = $linksToFotosArr['urlFoto1'];
        $arrMyAdvertReplace['hrefFoto1'] = $linksToFotosArr['hrefFoto1'];

        // Корректируем список инструкций, доступных пользователю
        $arrMyAdvertReplace['instructionPublish'] = "";
        $arrMyAdvertReplace['propertyId'] = "";
        $arrMyAdvertReplace['instructionDelete'] = "";
        if ($rowPropertyArr[$i]['status'] == "опубликовано") {
            $arrMyAdvertReplace['instructionPublish'] = "<li><a href='#'>снять с публикации</a></li>";
            $arrMyAdvertReplace['instructionDelete'] = "";
        }
        if ($rowPropertyArr[$i]['status'] == "не опубликовано") {
            $arrMyAdvertReplace['instructionPublish'] = "<li><a href='#'>опубликовать</a></li>";
            $arrMyAdvertReplace['instructionDelete'] = "<li><a href='#'>удалить</a></li>";
        }
        $arrMyAdvertReplace['propertyId'] = $rowPropertyArr[$i]['id'];

        /******* Список потенциальных арендаторов ******/
    /*    $arrMyAdvertReplace['probableTenants'] = "";
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
                        $arrMyAdvertReplace['probableTenants'] .= "<a href='man.php?compId=" . $compId . "'>" . $row['name'] . " " . $row['secondName'] . "</a>";
                    } else {
                        $arrMyAdvertReplace['probableTenants'] .= "<span title='Пользователь уже нашел недвижимость'>" . $row['name'] . " " . $row['secondName'] . "</span>";
                    }
                    if ($j < mysql_num_rows($rez) - 1) $arrMyAdvertReplace['probableTenants'] .= ", ";
                }
            }
        }
        // Заливаем полученную строку в шаблон
        if ($arrMyAdvertReplace['probableTenants'] == " ") $arrMyAdvertReplace['probableTenants'] = " -"; // Если нет ни одного потенциального арендатора

        // Все, что касается СТОИМОСТИ АРЕНДЫ
        $arrMyAdvertReplace['costOfRenting'] = "";
        $arrMyAdvertReplace['costOfRenting'] = $rowPropertyArr[$i]['costOfRenting'];
        $arrMyAdvertReplace['currency'] = "";
        $arrMyAdvertReplace['currency'] = $rowPropertyArr[$i]['currency'];
        $arrMyAdvertReplace['utilities'] = "";
        if ($rowPropertyArr[$i]['utilities'] == "да") $arrMyAdvertReplace['utilities'] = "+ коммунальные услуги от " . $rowPropertyArr[$i]['costInSummer'] . " до " . $rowPropertyArr[$i]['costInWinter'] . " " . $rowPropertyArr[$i]['currency'];
        $arrMyAdvertReplace['electricPower'] = "";
        if ($rowPropertyArr[$i]['electricPower'] == "да") $arrMyAdvertReplace['electricPower'] = "+ плата за электричество";
        $arrMyAdvertReplace['bail'] = "";
        if ($rowPropertyArr[$i]['bail'] == "есть") $arrMyAdvertReplace['bail'] = $rowPropertyArr[$i]['bailCost'] . " " . $rowPropertyArr[$i]['currency'];
        if ($rowPropertyArr[$i]['bail'] == "нет") $arrMyAdvertReplace['bail'] = "нет";
        $arrMyAdvertReplace['prepayment'] = "";
        $arrMyAdvertReplace['prepayment'] = $rowPropertyArr[$i]['prepayment'];

        // Срок аренды
        $arrMyAdvertReplace['termOfLease'] = "";
        $arrMyAdvertReplace['dateOfEntry'] = "";
        $arrMyAdvertReplace['dateOfCheckOut'] = "";
        $arrMyAdvertReplace['termOfLease'] = $rowPropertyArr[$i]['termOfLease'];
        $arrMyAdvertReplace['dateOfEntry'] = dateFromDBToView($rowPropertyArr[$i]['dateOfEntry']);
        if ($rowPropertyArr[$i]['dateOfCheckOut'] != "0000-00-00") $arrMyAdvertReplace['dateOfCheckOut'] = " по " . dateFromDBToView($rowPropertyArr[$i]['dateOfCheckOut']);

        // Мебель
        $arrMyAdvertReplace['furnitureName'] = "";
        $arrMyAdvertReplace['furniture'] = "";
        if ($rowPropertyArr[$i]['typeOfObject'] != "0" && $rowPropertyArr[$i]['typeOfObject'] != "гараж") {
            $arrMyAdvertReplace['furnitureName'] = "Мебель:";
            if (count(unserialize($rowPropertyArr[$i]['furnitureInLivingArea'])) != 0 || $rowPropertyArr[$i]['furnitureInLivingAreaExtra'] != "") $arrMyAdvertReplace['furniture'] = "есть в жилой зоне";
            if (count(unserialize($rowPropertyArr[$i]['furnitureInKitchen'])) != 0 || $rowPropertyArr[$i]['furnitureInKitchenExtra'] != "") if ($arrMyAdvertReplace['furniture'] == "") $arrMyAdvertReplace['furniture'] = "есть на кухне"; else $arrMyAdvertReplace['furniture'] .= ", есть на кухне";
            if (count(unserialize($rowPropertyArr[$i]['appliances'])) != 0 || $rowPropertyArr[$i]['appliancesExtra'] != "") if ($arrMyAdvertReplace['furniture'] == "") $arrMyAdvertReplace['furniture'] = "есть бытовая техника"; else $arrMyAdvertReplace['furniture'] .= ", есть бытовая техника";
            if ($arrMyAdvertReplace['furniture'] == "") $arrMyAdvertReplace['furniture'] = "нет";
        }

        // Ремонт
        $arrMyAdvertReplace['repairName'] = "";
        $arrMyAdvertReplace['repair'] = "";
        if ($rowPropertyArr[$i]['repair'] != "0" && $rowPropertyArr[$i]['furnish'] != "0") {
            $arrMyAdvertReplace['repairName'] = "Ремонт:";
            $arrMyAdvertReplace['repair'] = $rowPropertyArr[$i]['repair'] . ", отделка " . $rowPropertyArr[$i]['furnish'];
        }

        // Контакты собственника
        $arrMyAdvertReplace['contactTelephonNumber'] = "";
        $arrMyAdvertReplace['contactTelephonNumber'] = $rowPropertyArr[$i]['contactTelephonNumber'];
        $arrMyAdvertReplace['urlMan'] = "";
        $arrMyAdvertReplace['urlMan'] = "man.php?compId=" . ($rowUsers['id'] * 5 + 2); // compId - "вычисленное id пользователя. Равняется id пользователя * 5 + 2. Идентификатор пользователя подвергаем математическим вычислениям с целью скрыть его реальное значение от чужих глаз - для безопасности"
        $arrMyAdvertReplace['name'] = "";
        $arrMyAdvertReplace['name'] = $rowUsers['name'];
        $arrMyAdvertReplace['secondName'] = "";
        $arrMyAdvertReplace['secondName'] = $rowUsers['secondName'];
        $arrMyAdvertReplace['timeForRingBegin'] = "";
        $arrMyAdvertReplace['timeForRingBegin'] = $rowPropertyArr[$i]['timeForRingBegin'];
        $arrMyAdvertReplace['timeForRingEnd'] = "";
        $arrMyAdvertReplace['timeForRingEnd'] = $rowPropertyArr[$i]['timeForRingEnd'];

        // Производим заполнение шаблона
        // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
        $arrMyAdvertTemplVar = array('{statusEng}', '{typeOfObject}', '{address}', '{apartmentNumber}', '{status}', '{urlFoto1}', '{hrefFoto1}', '{instructionPublish}', '{propertyId}', '{instructionDelete}', '{probableTenants}', '{costOfRenting}', '{currency}', '{utilities}', '{electricPower}', '{bail}', '{prepayment}', '{termOfLease}', '{dateOfEntry}', '{dateOfCheckOut}', '{furnitureName}', '{furniture}', '{repairName}', '{repair}', '{contactTelephonNumber}', '{urlMan}', '{name}', '{secondName}', '{timeForRingBegin}', '{timeForRingEnd}');
        // Копируем html-текст шаблона
        $currentMyAdvert = str_replace($arrMyAdvertTemplVar, $arrMyAdvertReplace, $tmpl_MyAdvert);

        // Сформированный блок с описанием объявления добавляем в общую копилку. На вкладке tabs-3 (Мои объявления) полученный HTML всех блоков вставим в страницу.
        $briefOfAdverts .= $currentMyAdvert; // Добавим html-текст еще одного объявления. Готовим html-текст к добавлению на вкладку tabs-3 в Мои объявления
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
   /* $propertyIdArr = array();
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
    } */

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
    <link rel="stylesheet" href="css/colorbox.css">
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
    <!-- ColorBox - плагин jQuery, позволяющий делать модальное окно для просмотра фотографий -->
    <script src="js/vendor/jquery.colorbox-min.js"></script>
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
<?php echo "<input type='hidden' class='userType' typeTenant='" . $user->isTenant() . "' typeOwner='" . $user->isOwner() . "' correctNewSearchRequest='" . $correctNewSearchRequest . "'>"; ?>

<!-- Добавялем невидимый input для того, чтобы передать идентификатор вкладки, которую нужно открыть через JS -->
<?php
    // При загрузке страницы открываем вкладку № 4 "Поиск", если пользователь создает поисковый запрос и его личные данные для этого достаточны ($correct == "true"), либо если он редактирует поисковый запрос ($correctNewSearchRequest == TRUE, $correctNewSearchRequest == FALSE). В ином случае - открываем вкладку №1.
    if ($correct === TRUE || $correctNewSearchRequest === TRUE || $correctNewSearchRequest == FALSE) {
        $tabsId = "tabs-4";
    } elseif (isset($_GET['tabsId'])) {
        $tabsId = $_GET['tabsId'];
    } else {
        $tabsId = "tabs-1";
    }
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
<?php if ($correctNewProfileParameters !== FALSE): ?>
<!-- Блок с нередактируемыми параметрами Профайла не выдается только в 1 случае: если пользователь корректировал свои параметры, и они не прошли проверку -->
<div id="notEditingProfileParametersBlock">
    <div class="setOfInstructions">
        <a href="#">редактировать</a>
        <br>
    </div>
    <div class="fotosWrapper fotoNonInteractive">
        <div class='middleFotoWrapper'>
        <?php
            if (isset($rowUserFotos['id']) && isset($rowUserFotos['extension'])) {
                echo "<img class='middleFoto' src='" . $rowUserFotos['folder'] . "\\middle\\" . $rowUserFotos['id'] . "." . $rowUserFotos['extension'] . "'>";
            } else {
                // TODO: вставить реквизиты фотки по умолчанию, "нет фото"
                echo "<img class='middleFoto' src=''>";
            }
        ?>
        </div>
    </div>
    <div class="profileInformation">
        <ul class="listDescription">
            <li>
                <span
                    class="FIO"><?php echo $user->surname . " " . $user->name . " " . $user->secondName?></span>
            </li>
            <li>
                <br>
            </li>
            <li>
                <span class="headOfString">Образование:</span> <?php
                if ($user->currentStatusEducation == "0") {
                    echo "";
                }
                if ($user->currentStatusEducation == "нет") {
                    echo "нет";
                }
                if ($user->currentStatusEducation == "сейчас учусь") {
                    if (isset($user->almamater)) echo $user->almamater . ", ";
                    if (isset($user->speciality)) echo $user->speciality . ", ";
                    if (isset($user->ochnoZaochno)) echo $user->ochnoZaochno . ", ";
                    if (isset($user->kurs)) echo "курс: " . $user->kurs;
                }
                if ($user->currentStatusEducation == "закончил") {
                    if (isset($user->almamater)) echo $user->almamater . ", ";
                    if (isset($user->speciality)) echo $user->speciality . ", ";
                    if (isset($user->ochnoZaochno)) echo $user->ochnoZaochno . ", ";
                    if (isset($user->yearOfEnd)) echo "<span style='white-space: nowrap;'>закончил в " . $user->yearOfEnd . " году</span>";
                }
                ?>
            </li>
            <li>
                <span class="headOfString">Работа:</span> <?php
                if ($user->statusWork == "не работаю") {
                    echo "не работаю";
                } else {
                    if (isset($user->placeOfWork) && $user->placeOfWork != "") {
                        echo $user->placeOfWork . ", ";
                    }
                    if (isset($user->workPosition)) {
                        echo $user->workPosition;
                    }
                }
                ?>
            </li>
            <li>
                <span class="headOfString">Внешность:</span> <?php
                if (isset($user->nationality) && $user->nationality != "0") echo "<span style='white-space: nowrap;'>" . $user->nationality . "</span>";
                ?>
            </li>
            <li>
                <span class="headOfString">Пол:</span> <?php
                if (isset($user->sex)) echo $user->sex;
                ?>
            </li>
            <li>
                <span class="headOfString">День рождения:</span> <?php
                if (isset($user->birthday)) echo $user->birthday;
                ?>
            </li>
            <li>
                <span class="headOfString">Возраст:</span> <?php
                $date = substr($user->birthday, 0, 2);
                $month = substr($user->birthday, 3, 2);
                $year = substr($user->birthday, 6, 4);
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
                if (isset($user->email)) echo $user->email;
                ?>
            </li>
            <li>
                <span class="headOfString">Телефон:</span> <?php
                if (isset($user->telephon)) echo $user->telephon;
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
                if (isset($user->cityOfBorn)) echo $user->cityOfBorn;
                ?>
            </li>
            <li>
                <span class="headOfString">Регион:</span> <?php
                if (isset($user->regionOfBorn)) echo $user->regionOfBorn;
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
                if (isset($user->shortlyAboutMe)) echo $user->shortlyAboutMe;
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
                    if (isset($user->vkontakte)) echo "<li><a href='" . $user->vkontakte . "'>" . $user->vkontakte . "</a></li>";
                    ?>
                    <?php
                    if (isset($user->odnoklassniki)) echo "<li><a href='" . $user->odnoklassniki . "'>" . $user->odnoklassniki . "</a></li>";
                    ?>
                    <?php
                    if (isset($user->facebook)) echo "<li><a href='" . $user->facebook . "'>" . $user->facebook . "</a></li>";
                    ?>
                    <?php
                    if (isset($user->twitter)) echo "<li><a href='" . $user->twitter . "'>" . $user->twitter . "</a></li>";
                    ?>
                </ul>
            </li>
        </ul>
    </div>
</div>
    <?php endif; ?>
<form method="post" name="profileParameters" id="editingProfileParametersBlock" class="descriptionFieldsetsWrapper" enctype="multipart/form-data"
      style='<?php if ($correctNewProfileParameters !== FALSE) echo "display: none;"?>'>
    <div class="descriptionFieldsetsWrapper">
        <fieldset class="edited private">
            <legend>
                ФИО
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="itemLabel">
                            Фамилия
                        </td>
                        <td class="itemRequired">
                            *
                        </td>
                        <td class="itemBody">
                            <input name="surname" id="surname" type="text" autofocus <?php echo "value='$user->surname'";?>>
                        </td>
                    </tr>
                    <tr>
                        <td class="itemLabel">
                            Имя
                        </td>
                        <td class="itemRequired">
                            *
                        </td>
                        <td class="itemBody">
                            <input name="name" id="name" type="text" <?php echo "value='$user->name'";?>>
                        </td>
                    </tr>
                    <tr>
                        <td class="itemLabel">
                            Отчество
                        </td>
                        <td class="itemRequired">
                            *
                        </td>
                        <td class="itemBody">
                            <input name="secondName" id="secondName" type="text" <?php echo "value='$user->secondName'";?>>
                        </td>
                    </tr>
                    <tr>
                        <td class="itemLabel">
                            Пол
                        </td>
                        <td class="itemRequired">
                            *
                        </td>
                        <td class="itemBody">
                            <select name="sex" id="sex">
                                <option value="0" <?php if ($user->sex == "0") echo "selected";?>></option>
                                <option value="мужской" <?php if ($user->sex == "мужской") echo "selected";?>>мужской</option>
                                <option value="женский" <?php if ($user->sex == "женский") echo "selected";?>>женский</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="itemLabel">
                            Внешность
                        </td>
                        <td class="itemRequired">
                            *
                        </td>
                        <td class="itemBody">
                            <select name="nationality" id="nationality">
                                <option value="0" <?php if ($user->nationality == "0") echo "selected";?>></option>
                                <option
                                    value="славянская" <?php if ($user->nationality == "славянская") echo "selected";?>>
                                    славянская
                                </option>
                                <option
                                    value="европейская" <?php if ($user->nationality == "европейская") echo "selected";?>>
                                    европейская
                                </option>
                                <option
                                    value="азиатская" <?php if ($user->nationality == "азиатская") echo "selected";?>>
                                    азиатская
                                </option>
                                <option
                                    value="кавказская" <?php if ($user->nationality == "кавказская") echo "selected";?>>
                                    кавказская
                                </option>
                                <option
                                    value="африканская" <?php if ($user->nationality == "африканская") echo "selected";?>>
                                    африканская
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="itemLabel">
                            День рождения
                        </td>
                        <td class="itemRequired">
                            *
                        </td>
                        <td class="itemBody">
                            <input name="birthday" id="birthday" type="text"
                                   placeholder="дд.мм.гггг" <?php echo "value='$user->birthday'";?>>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>

        <div style="display: inline-block; vertical-align: top;">
            <fieldset class="edited private" style="display: block;">
                <legend>
                    Логин и пароль
                </legend>
                <table>
                    <tbody>
                        <tr title="Используйте в качестве логина ваш e-mail или телефон">
                            <td class="itemLabel">
                                Логин:
                            </td>
                            <td class="itemRequired">
                            </td>
                            <td class="itemBody">
                                <?php echo $user->login;?>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemLabel">
                                Пароль
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input type="password" name="password" id="password"
                                       maxlength="50" <?php echo "value='$user->password'";?>>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>

            <fieldset class="edited private" style="display: block;">
                <legend>
                    Контакты
                </legend>
                <table>
                    <tbody>
                        <tr>
                            <td class="itemLabel">
                                Телефон
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input type="text" name="telephon" id="telephon" <?php echo "value='$user->telephon'";?>>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemLabel">
                                E-mail
                            </td>
                            <td class="itemRequired">
                                <?php if ($user->isTenant()) {
                                echo "*";
                            } ?>
                            </td>
                            <td class="itemBody">
                                <input type="text" name="email" id="email" <?php echo "value='$user->email'"; ?>>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>

        <fieldset id='fotoWrapperBlock' class="edited private" style="min-width: 300px;">
            <legend
                <?php if ($user->isTenant()) echo 'title="Рекомендуем загрузить хотя бы 1 фотографию, которая в выгодном свете представит Вас перед собственником"'; ?>>
                Фотографии
            </legend>
            <?php
            echo "<input type='hidden' name='fileUploadId' id='fileUploadId' value='" . $user->fileUploadId . "'>";
            ?>
            <input type='hidden' name='uploadedFoto' id='uploadedFoto' value=''>

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
        <table>
            <tbody>
                <tr>
                    <td class="itemLabel">
                        Текущий статус
                    </td>
                    <td class="itemRequired">
                        <?php if ($user->isTenant()) {
                        echo "*";
                    } ?>
                    </td>
                    <td class="itemBody">
                        <select name="currentStatusEducation" id="currentStatusEducation">
                            <option value="0" <?php if ($user->currentStatusEducation == "0") echo "selected";?>></option>
                            <option
                                value="нет" <?php if ($user->currentStatusEducation == "нет") echo "selected";?>>
                                Нигде не учился
                            </option>
                            <option
                                value="сейчас учусь" <?php if ($user->currentStatusEducation == "сейчас учусь") echo "selected";?>>
                                Сейчас учусь
                            </option>
                            <option
                                value="закончил" <?php if ($user->currentStatusEducation == "закончил") echo "selected";?>>
                                Закончил
                            </option>
                        </select>
                    </td>
                </tr>
                <tr id="almamaterBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет"
                    title="Укажите учебное заведение, в котором учитесь сейчас, либо последнее из тех, что заканчивали">
                    <td class="itemLabel">
                        Учебное заведение
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="almamater" id="almamater"
                               class="ifLearned" <?php echo "value='$user->almamater'";?>>
                    </td>
                </tr>
                <tr id="specialityBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет">
                    <td class="itemLabel">
                        Специальность
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="speciality" id="speciality"
                               class="ifLearned" <?php echo "value='$user->speciality'";?>>
                    </td>
                </tr>
                <tr id="kursBlock"
                    notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_закончил"
                    title="Укажите курс, на котором учитесь">
                    <td class="itemLabel">
                        Курс
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="kurs" id="kurs" class="ifLearned" <?php echo "value='$user->kurs'";?>>
                    </td>
                </tr>
                <tr id="formatEducation"
                    notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_закончил"
                    title="Укажите форму обучения">
                    <td class="itemLabel">
                        Очно / Заочно
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <select name="ochnoZaochno" id="ochnoZaochno" class="ifLearned">
                            <option value="0" <?php if ($user->ochnoZaochno == "0") echo "selected";?>></option>
                            <option value="очно" <?php if ($user->ochnoZaochno == "очно") echo "selected";?>>Очно</option>
                            <option value="заочно" <?php if ($user->ochnoZaochno == "заочно") echo "selected";?>>Заочно
                            </option>
                        </select>
                    </td>
                </tr>
                <tr id="yearOfEndBlock"
                    notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_сейчас учусь"
                    title="Укажите год окончания учебного заведения">
                    <td class="itemLabel">
                        Год окончания
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="yearOfEnd" id="yearOfEnd"
                               class="ifLearned" <?php echo "value='$user->yearOfEnd'";?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <fieldset class="edited private">
        <legend>
            Работа
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="itemLabel">
                        Статус занятости
                    </td>
                    <td class="itemRequired">
                        <?php if ($user->isTenant()) {
                        echo "*";
                    } ?>
                    </td>
                    <td class="itemBody">
                        <select name="statusWork" id="statusWork">
                            <option value="0" <?php if ($user->statusWork == "0") echo "selected";?>></option>
                            <option value="работаю" <?php if ($user->statusWork == "работаю") echo "selected";?>>работаю
                            </option>
                            <option value="не работаю" <?php if ($user->statusWork == "не работаю") echo "selected";?>>не
                                работаю
                            </option>
                        </select>
                    </td>
                </tr>
                <tr notavailability="statusWork_0&statusWork_не работаю">
                    <td class="itemLabel">
                        Место работы
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="placeOfWork" id="placeOfWork"
                               class="ifWorked" <?php echo "value='$user->placeOfWork'";?>>
                    </td>
                </tr>
                <tr notavailability="statusWork_0&statusWork_не работаю">
                    <td class="itemLabel">
                        Должность
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="workPosition" id="workPosition"
                               class="ifWorked" <?php echo "value='$user->workPosition'";?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <fieldset class="edited private">
        <legend>
            Коротко о себе
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="itemLabel">
                        В каком регионе родились
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="regionOfBorn" id="regionOfBorn" <?php echo "value='$user->regionOfBorn'";?>>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Родной город, населенный пункт
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="cityOfBorn" id="cityOfBorn" <?php echo "value='$user->cityOfBorn'";?>>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Коротко о себе и своих интересах:
                    </td>
                    <td class="itemRequired">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <textarea name="shortlyAboutMe" id="shortlyAboutMe"
                                  rows="4"><?php echo $user->shortlyAboutMe;?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <fieldset class="edited private social">
        <legend>
            Страницы в социальных сетях
        </legend>
        <table>
            <tbody>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/vkontakte.jpg">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="vkontakte" id="vkontakte"
                               placeholder="http://vk.com/..." <?php echo "value='$user->vkontakte'";?>>
                    </td>
                </tr>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/odnoklassniki.png">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="odnoklassniki" id="odnoklassniki"
                               placeholder="http://www.odnoklassniki.ru/profile/..." <?php echo "value='$user->odnoklassniki'";?>>
                    </td>
                </tr>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/facebook.jpg">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="facebook" id="facebook"
                               placeholder="https://www.facebook.com/profile.php?..." <?php echo "value='$user->facebook'";?>>
                    </td>
                </tr>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/twitter.png">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="twitter" id="twitter"
                               placeholder="https://twitter.com/..." <?php echo "value='$user->twitter'";?>>
                    </td>
                </tr>
            </tbody>
        </table>
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
    /*echo $briefOfAdverts;*/
    //TODO: поправить как надо
    ?>
</div>

<div id="tabs-4">
<div class="shadowText">
    На этой вкладке Вы можете задать параметры, в соответствии с которыми ресурс Хани Хом будет осуществлять
    автоматический поиск объявлений на портале и будет оповещать Вас о появлении новых объектов по указанному в
    профиле
    e-mail
</div>
<?php if ($user->isTenant() != TRUE && $correct != TRUE && $correctNewSearchRequest == NULL): ?>
<!-- Если пользователь еще не сформировал поисковый запрос (а значит не является арендатором) и он либо не нажимал на кнопку формирования запроса, либо нажимал, но не прошел проверку на полноту информации о пользователи, то ему доступна только кнопка формирования нового запроса. В ином случае будет отображаться сам поисковый запрос пользователя, либо форма для его заполнения -->
<form name="createSearchRequest" method="post">
    <button type="submit" name="createSearchRequestButton" id='createSearchRequestButton' class='left-bottom'>
        Запрос на поиск
    </button>
</form>
    <?php endif;?>
<?php if ($user->isTenant() == TRUE && $correctNewSearchRequest !== FALSE): ?>
<!-- Если пользователь является арендатором и (если он редактировал пар-ры поиска) после редактирования параметров поиска ошибок не обнаружено, то у пользователя уже сформирован корректный поисковый запрос, который мы и показываем на этой вкладке -->
    <!--
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
                //if (isset($user->typeOfObject) && $user->typeOfObject != "0") echo $user->typeOfObject; else echo "любой";
                ?>
            </span>
                    </td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Количество комнат:</td>
                    <td class="objectDescriptionBody"><span><?php
                      /*  if (isset($user->amountOfRooms) && count($user->amountOfRooms) != "0") for ($i = 0; $i < count($user->amountOfRooms); $i++) {
                            echo $amountOfRooms[$i];
                            if ($i < count($amountOfRooms) - 1) echo ", ";
                        } else echo "любое"; */
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Комнаты смежные:</td>
                    <td class="objectDescriptionBody"><span><?php
                       // if (isset($adjacentRooms) && $adjacentRooms != "0") echo $adjacentRooms; else echo "любые";
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Этаж:</td>
                    <td class="objectDescriptionBody"><span><?php
                      //  if (isset($floor) && $floor != "0") echo $floor; else echo "любой";
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
                     //   if (isset($minCost) && $minCost != "0") echo "<span>" . $minCost . "</span> руб."; else echo "любая";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
                    <td class="objectDescriptionBody"><?php
                     //   if (isset($maxCost) && $maxCost != "0") echo "<span>" . $maxCost . "</span> руб."; else echo "любая";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Залог до:</td>
                    <td class="objectDescriptionBody"><?php
                      //  if (isset($pledge) && $pledge != "0") echo "<span>" . $pledge . "</span> руб."; else echo "любой";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Максимальная предоплата:</td>
                    <td class="objectDescriptionBody"><?php
                      //  if (isset($prepayment) && $prepayment != "0") echo "<span>" . $prepayment . "</span>"; else echo "любая";
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
              /*  if (isset($district) && count($district) != 0) { // Если район указан пользователем
                    echo "<tr><td>";
                    for ($i = 0; $i < count($district); $i++) { // Выводим названия всех районов, в которых ищет недвижимость пользователь
                        echo $district[$i];
                        if ($i < count($district) - 1) echo ", ";
                    }
                    echo  "</td></tr>";
                } else {
                    echo "<tr><td>" . "любой" . "</td></tr>";
                } */
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
                     //   if (isset($withWho) && $withWho != "0") echo $withWho; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
              /*  if ($withWho != "самостоятельно" && $withWho != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Информация о сожителях:</td><td class='objectDescriptionBody''><span>";
                    if (isset($linksToFriends)) echo $linksToFriends;
                    echo "</span></td></tr>";
                } */
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Дети:</td>
                    <td class="objectDescriptionBody"><span><?php
                     //   if (isset($children) && $children != "0") echo $children; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
               /* if ($children != "без детей" && $children != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Количество детей и их возраст:</td><td class='objectDescriptionBody''><span>";
                    if (isset($howManyChildren)) echo $howManyChildren;
                    echo "</span></td></tr>";
                } */
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Животные:</td>
                    <td class="objectDescriptionBody"><span><?php
                    //    if (isset($animals) && $animals != "0") echo $animals; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
             /*   if ($animals != "без животных" && $animals != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Количество животных и их вид:</td><td class='objectDescriptionBody''><span>";
                    if (isset($howManyAnimals)) echo $howManyAnimals;
                    echo "</span></td></tr>";
                } */
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Срок аренды:</td>
                    <td class="objectDescriptionBody"><span><?php
                   //     if (isset($termOfLease) && $termOfLease != "0") echo $termOfLease; else echo "не указан";
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
                    <td class="objectDescriptionBody"><span><?php
                    //    if (isset($additionalDescriptionOfSearch)) echo $additionalDescriptionOfSearch;
                        ?></span></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>
    <?php endif;?>
<?php if ($user->isTenant() === TRUE || $correct === TRUE || $correctNewSearchRequest === FALSE): ?>
<!-- Если пользователь является арендатором, то вместе с отображением текущих параметров поискового запроса мы выдаем скрытую форму для их редактирования, также мы выдаем видимую форму для редактирования параметров поиска в случае, если пользователь нажал на кнопку Нового поискового запроса и проверка на корректность его данных Профиля профла успешно, а также в случае если пользователь корректировал данные поискового запроса, но они не прошли проверку -->
<form method="post" name="searchParameters" id="extendedSearchParametersBlock">
    <div id="leftBlockOfSearchParameters" style="display: inline-block;">
        <fieldset class="edited">
            <legend>
                Характеристика объекта
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="itemLabel">
                            Тип
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="typeOfObject" id="typeOfObject">
                                <option value="0" <?php if ($user->typeOfObject == "0") echo "selected";?>></option>
                                <option value="квартира" <?php if ($user->typeOfObject == "квартира") echo "selected";?>>
                                    квартира
                                </option>
                                <option value="комната" <?php if ($user->typeOfObject == "комната") echo "selected";?>>
                                    комната
                                </option>
                                <option value="дом" <?php if ($user->typeOfObject == "дом") echo "selected";?>>дом,
                                    коттедж
                                </option>
                                <option value="таунхаус" <?php if ($user->typeOfObject == "таунхаус") echo "selected";?>>
                                    таунхаус
                                </option>
                                <option value="дача" <?php if ($user->typeOfObject == "дача") echo "selected";?>>дача
                                </option>
                                <option value="гараж" <?php if ($user->typeOfObject == "гараж") echo "selected";?>>гараж
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr notavailability="typeOfObject_гараж">
                        <td class="itemLabel">
                            Количество комнат
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="checkbox" value="1" name="amountOfRooms[]"
                                <?php
                                foreach ($user->amountOfRooms as $value) {
                                    if ($value == "1") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            1
                            <input type="checkbox" value="2"
                                   name="amountOfRooms[]" <?php
                                foreach ($user->amountOfRooms as $value) {
                                    if ($value == "2") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            2
                            <input type="checkbox" value="3"
                                   name="amountOfRooms[]" <?php
                                foreach ($user->amountOfRooms as $value) {
                                    if ($value == "3") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            3
                            <input type="checkbox" value="4"
                                   name="amountOfRooms[]" <?php
                                foreach ($user->amountOfRooms as $value) {
                                    if ($value == "4") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            4
                            <input type="checkbox" value="5"
                                   name="amountOfRooms[]" <?php
                                foreach ($user->amountOfRooms as $value) {
                                    if ($value == "5") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            5
                            <input type="checkbox" value="6"
                                   name="amountOfRooms[]" <?php
                                foreach ($user->amountOfRooms as $value) {
                                    if ($value == "6") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            6...
                        </td>
                    </tr>
                    <tr notavailability="typeOfObject_гараж">
                        <td class="itemLabel">
                            Комнаты смежные
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="adjacentRooms" id="adjacentRooms">
                                <option value="0" <?php if ($user->adjacentRooms == "0") echo "selected";?>></option>
                                <option
                                    value="не имеет значения" <?php if ($user->adjacentRooms == "не имеет значения") echo "selected";?>>
                                    не
                                    имеет значения
                                </option>
                                <option
                                    value="только изолированные" <?php if ($user->adjacentRooms == "только изолированные") echo "selected";?>>
                                    только изолированные
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr notavailability="typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
                        <td class="itemLabel">
                            Этаж
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="floor" id="floor">
                                <option value="0" <?php if ($user->floor == "0") echo "selected";?>></option>
                                <option value="любой" <?php if ($user->floor == "любой") echo "selected";?>>любой</option>
                                <option value="не первый" <?php if ($user->floor == "не первый") echo "selected";?>>не
                                    первый
                                </option>
                                <option
                                    value="не первый и не последний" <?php if ($user->floor == "не первый и не последний") echo "selected";?>>
                                    не первый и не
                                    последний
                                </option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset class="edited cost">
            <legend>
                Стоимость
            </legend>
            <table>
                <tbody>
                    <tr title="В месяц за аренду недвижимости с учетом стоимости коммунальных услуг (если они оплачиваются дополнительно)">
                        <td class="itemLabel">
                            Арендная плата от
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="text" name="minCost" id="minCost"
                                   maxlength="8" <?php echo "value='$user->minCost'";?>>
                            руб.
                        </td>
                    </tr>
                    <tr title="В месяц за аренду недвижимости с учетом стоимости коммунальных услуг (если они оплачиваются дополнительно)">
                        <td class="itemLabel">
                            Арендная плата до
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="text" name="maxCost" id="maxCost"
                                   maxlength="8" <?php echo "value='$user->maxCost'";?>>
                            руб.
                        </td>
                    </tr>
                    <tr title="Какую сумму Вы готовы передать собственнику в качестве возвращаемого гарантийного депозита">
                        <td class="itemLabel">
                            Залог до
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="text" name="pledge" id="pledge"
                                   maxlength="8" <?php echo "value='$user->pledge'";?>>
                            руб.
                        </td>
                    </tr>
                    <tr title="Какую предоплату за проживание Вы готовы внести">
                        <td class="itemLabel">
                            Макс. предоплата
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="prepayment" id="prepayment">
                                <option value="0" <?php if ($user->prepayment == "0") echo "selected";?>></option>
                                <option value="нет" <?php if ($user->prepayment == "нет") echo "selected";?>>нет</option>
                                <option value="1 месяц" <?php if ($user->prepayment == "1 месяц") echo "selected";?>>1
                                    месяц
                                </option>
                                <option value="2 месяца" <?php if ($user->prepayment == "2 месяца") echo "selected";?>>2
                                    месяца
                                </option>
                                <option value="3 месяца" <?php if ($user->prepayment == "3 месяца") echo "selected";?>>3
                                    месяца
                                </option>
                                <option value="4 месяца" <?php if ($user->prepayment == "4 месяца") echo "selected";?>>4
                                    месяца
                                </option>
                                <option value="5 месяцев" <?php if ($user->prepayment == "5 месяцев") echo "selected";?>>5
                                    месяцев
                                </option>
                                <option value="6 месяцев" <?php if ($user->prepayment == "6 месяцев") echo "selected";?>>6
                                    месяцев
                                </option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div id="rightBlockOfSearchParameters">
        <fieldset class="edited">
            <legend>
                Район
            </legend>
            <ul>
                <?php
                if (isset($allDistrictsInCity)) {
                    foreach ($allDistrictsInCity as $value) { // Для каждого идентификатора района и названия формируем чекбокс
                        echo "<li><input type='checkbox' name='district[]' value='" . $value['name'] . "'";
                        foreach ($user->district as $valueDistrict) {
                            if ($valueDistrict == $value['name']) {
                                echo "checked";
                                break;
                            }
                        }
                        echo "> " . $value['name'] . "</li>";
                    }
                }
                ?>
            </ul>
        </fieldset>
    </div>
    <!-- /end.rightBlockOfSearchParameters -->
    <fieldset class="edited private">
        <legend>
            Особые параметры поиска
        </legend>
        <table>
            <tbody>
                <tr notavailability="typeOfObject_гараж">
                    <td class="itemLabel">
                        Как собираетесь проживать
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="withWho" id="withWho">
                            <option value="0" <?php if ($user->withWho == "0") echo "selected";?>></option>
                            <option
                                value="самостоятельно" <?php if ($user->withWho == "самостоятельно") echo "selected";?>>
                                самостоятельно
                            </option>
                            <option value="семья" <?php if ($user->withWho == "семья") echo "selected";?>>семьей
                            </option>
                            <option value="пара" <?php if ($user->withWho == "пара") echo "selected";?>>парой
                            </option>
                            <option value="2 мальчика" <?php if ($user->withWho == "2 мальчика") echo "selected";?>>2
                                мальчика
                            </option>
                            <option value="2 девочки" <?php if ($user->withWho == "2 девочки") echo "selected";?>>2
                                девочки
                            </option>
                            <option value="со знакомыми" <?php if ($user->withWho == "со знакомыми") echo "selected";?>>со
                                знакомыми
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="withWhoDescription" style="display: none;">
                    <td class="itemLabel" colspan="3">
                        Что Вы можете сказать о сожителях:
                    </td>
                </tr>
                <tr class="withWhoDescription" style="display: none;">
                    <td colspan="3">
                        <textarea name="linksToFriends" id="linksToFriends"
                                  rows="3"><?php echo $user->linksToFriends;?></textarea>
                    </td>
                </tr>

                <tr notavailability="typeOfObject_гараж">
                    <td class="itemLabel">
                        Дети
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="children" id="children">
                            <option value="0" <?php if ($user->children == "0") echo "selected";?>></option>
                            <option value="без детей" <?php if ($user->children == "без детей") echo "selected";?>>без
                                детей
                            </option>
                            <option
                                value="с детьми младше 4-х лет" <?php if ($user->children == "с детьми младше 4-х лет") echo "selected";?>>
                                с детьми
                                младше 4-х лет
                            </option>
                            <option
                                value="с детьми старше 4-х лет" <?php if ($user->children == "с детьми старше 4-х лет") echo "selected";?>>
                                с детьми
                                старше 4-х лет
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="childrenDescription" style="display: none;">
                    <td class="itemLabel" colspan="3">
                        Сколько у Вас детей и какого возраста:
                    </td>
                </tr>
                <tr class="childrenDescription" style="display: none;">
                    <td colspan="3">
                        <textarea name="howManyChildren" id="howManyChildren"
                                  rows="3"><?php echo $user->howManyChildren;?></textarea>
                    </td>
                </tr>

                <tr notavailability="typeOfObject_гараж">
                    <td class="itemLabel">
                        Домашние животные
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="animals" id="animals">
                            <option value="0" <?php if ($user->animals == "0") echo "selected";?>></option>
                            <option value="без животных" <?php if ($user->animals == "без животных") echo "selected";?>>
                                без
                                животных
                            </option>
                            <option
                                value="с животным(ми)" <?php if ($user->animals == "с животным(ми)") echo "selected";?>>с
                                животным(ми)
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="animalsDescription" style="display: none;">
                    <td class="itemLabel" colspan="3">
                        Сколько у Вас животных и какого вида:
                    </td>
                </tr>
                <tr class="animalsDescription" style="display: none;">
                    <td colspan="3">
                        <textarea name="howManyAnimals" id="howManyAnimals"
                                  rows="3"><?php echo $user->howManyAnimals;?></textarea>
                    </td>
                </tr>

                <tr>
                    <td class="itemLabel">
                        Срок аренды
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="termOfLease" id="termOfLease">
                            <option value="0" <?php if ($user->termOfLease == "0") echo "selected";?>></option>
                            <option
                                value="длительный срок" <?php if ($user->termOfLease == "длительный срок") echo "selected";?>>
                                длительный срок (от года)
                            </option>
                            <option
                                value="несколько месяцев" <?php if ($user->termOfLease == "несколько месяцев") echo "selected";?>>
                                несколько месяцев (до года)
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="itemLabel">
                        Дополнительные условия поиска:
                    </td>
                    <td class="itemRequired">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <textarea name="additionalDescriptionOfSearch" id="additionalDescriptionOfSearch"
                                  rows="4"><?php echo $user->additionalDescriptionOfSearch;?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
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
    //echo getSearchResultHTML($propertyLightArr, $userId, "favorites");

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
<script>
    // Сервер сохранит в эту переменную данные о загруженных фотографиях в формате JSON
    // Переменная uploadedFoto содержит массив объектов, каждый из которых представляет информацию по 1 фотографии
    var temp = '<?php echo json_encode($user->uploadedFoto);?>'.split("\\").join("\\\\");
    var uploadedFoto = JSON.parse(temp);
</script>
<script src="js/main.js"></script>
<script src="js/personal.js"></script>
<!-- TODO: тест <script src="js/searchResult.js"></script>-->
<!-- end scripts -->

<?php
    // Закрываем соединение с БД
    $globFunc->closeConnectToDB($DBlink);
?>

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
