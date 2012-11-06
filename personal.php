<?php

    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include 'models/GlobFunc.php';
    include 'models/Logger.php';
    include 'models/IncomingUser.php';
    include 'views/View.php';
    include 'models/User.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser($globFunc, $DBlink);

    // Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
    if (!$incomingUser->login()) {
        header('Location: login.php');
    }

    /*************************************************************************************
     * Получаем информацию о пользователе из БД сервера
     ************************************************************************************/

    // Инициализируем полную модель пользователя
    $user = new User($globFunc, $DBlink, $incomingUser);

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

    // Информация о фотографиях пользователя. Метод вызывается во всех случаях, кроме того, когда пользователь отредактировал свои личные параметры и нажал на кнопку "Сохранить"
    if (!isset($_POST['saveProfileParameters'])) $user->writeFotoInformationFromDB();

    //TODO: переработать!
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
    $errors = array();
    $correctNewSearchRequest = NULL; // Отражает корректность и полноту личных данных пользователя, необходимую для создания НОВОГО поискового запроса.
    $correctEditSearchRequest = NULL; // Отражает корректность отредактированных пользователем параметров поиска
    $correctEditProfileParameters = NULL; // Корректность личных данных пользователя. Работает, если он пытается изменить личные данные своего профайла. Проверка осуществляется в соответствии со статусом пользователя (арендатор или собственник)

    /********************************************************************************
     * РЕДАКТИРОВАНИЕ ЛИЧНЫХ ДАННЫХ ПРОФИЛЯ. Если пользователь отправил редактированные параметры своего профиля
     *******************************************************************************/

    if (isset($_POST['saveProfileParameters'])) {

        // Записываем POST параметры в параметры объекта пользователя
        $user->writeCharacteristicFromPOST();
        $user->writeFotoInformationFromPOST();

        // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = $user->userDataCorrect("validateProfileParameters");

        // Установим признак корректности введенных пользователем новых личных параметров
        if (is_array($errors) && count($errors) == 0) {
            $correctEditProfileParameters = TRUE;
        } else {
            $correctEditProfileParameters = FALSE;
        }

        // Если данные верны, сохраним их в БД
        if ($correctEditProfileParameters == TRUE) {

            // Личная информация
            $correctSaveCharacteristicToDB = $user->saveCharacteristicToDB("edit");

            if ($correctSaveCharacteristicToDB) {
                // Сохраним информацию о фотографиях пользователя
                $user->saveFotoInformationToDB();
            } else {
                $errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и нажмите кнопку Сохранить';
                // Сохранении данных в БД не прошло - данные пользователя не сохранены
            }

        }
    }

    /********************************************************************************
     * РЕДАКТИРОВАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь отправил редактированные параметры поискового запроса
     *******************************************************************************/

    if (isset($_POST['saveSearchParametersButton'])) {

        // Записываем POST параметры в параметры объекта пользователя
        $user->writeSearchRequestFromPOST();

        // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = $user->userDataCorrect("validateSearchRequest"); // Параметр validateSearchRequest задает режим проверки "Проверка корректности уже существующих параметров поиска", который активирует только соответствующие ему проверки
        if (count($errors) == 0) $correctEditSearchRequest = TRUE; else $correctEditSearchRequest = FALSE; // Считаем ошибки, если 0, то можно принять и сохранить новые параметры поиска

        // Если данные верны, сохраним их в БД
        // Кроме сохранение данных поискового запроса метод перезапишет статус пользователя (typeTenant), так как он теперь точно стал арендатором
        if ($correctEditSearchRequest == TRUE) {
            $user->saveSearchRequestToDB("edit");
        }
    }

    /********************************************************************************
     * ЗАПРОС НА СОЗДАНИЕ УСЛОВИЙ ПОИСКА. Если пользователь нажал на кнопку Формирования нового поискового запроса
     *******************************************************************************/

    if (isset($_POST['createSearchRequestButton'])) {

        // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = $user->userDataCorrect("createSearchRequest"); // Параметр createSearchRequest задает режим проверки "Создание запроса на поиск", который активирует только соответствующие ему проверки
        if (count($errors) == 0) $correctNewSearchRequest = TRUE; else $correctNewSearchRequest = FALSE; // Считаем ошибки, если 0, то можно выдать пользователю форму для ввода параметров Запроса поиска

    }

    /********************************************************************************
     * ФОРМИРОВАНИЕ ПРЕДСТАВЛЕНИЯ (View)
     *******************************************************************************/

    $view = new View();
    $view->generate("templ_personal.php", array('userCharacteristic' => $user->getCharacteristicData(),
                                                'userFotoInformation' => $user->getFotoInformationData(),
                                                'userSearchRequest' => $user->getSearchRequestData(),
                                                'errors' => $errors,
                                                'correctNewSearchRequest' => $correctNewSearchRequest,
                                                'correctEditSearchRequest' => $correctEditSearchRequest,
                                                'correctEditProfileParameters' => $correctEditProfileParameters,
                                                'allDistrictsInCity' => $allDistrictsInCity,
                                                'isLoggedIn' => $incomingUser->login()));

    /********************************************************************************
     * Закрываем соединение с БД
     *******************************************************************************/

    $globFunc->closeConnectToDB($DBlink);

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