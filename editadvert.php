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
     * Если в строке не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет
     ************************************************************************************/
    $propertyId = "0";
    if (isset($_GET['propertyId'])) {
        $propertyId = $_GET['propertyId']; // Получаем идентификатор объявления для редактирования из строки запроса
    } else {
        header('Location: personal.php?tabsId=3'); // Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет к списку его объявлений
    }

    /*************************************************************************************
     * Получаем объявление пользователя для редактирования, а также другие данные из БД
     ************************************************************************************/

    // Получаем информацию о нужном объекте недвижимости
    $rezProperty = mysql_query("SELECT * FROM property WHERE id = '" . $propertyId . "'");
    $rowProperty = mysql_fetch_assoc($rezProperty);

    // Получаем информацию о фотографиях объекта недвижимости пользователя
    // Массив $rowPropertyFotosArr представляет собой массив массивов, каждый из которых содержит информацию об одной фотографии объекта недвижимости
    $rezPropertyFotos = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $propertyId . "'");
    for ($i = 0; $i < mysql_num_rows($rezPropertyFotos); $i++) {
        $rowPropertyFotosArr[] = mysql_fetch_assoc($rezPropertyFotos);
    }

    // Инициализируем переменную корректности
    $correct = "null";

    /**************************************************************************************************************
     * Проверяем, что пользователь имеет право редактировать данное объявление - он является собственником данного объекта недвижимости
     **************************************************************************************************************/
    if ($rowProperty['userId'] != $userId) header('Location: personal.php?tabsId=3');

    /**************************************************************************************************************
     * Инициализируем переменные, содержащиеся в описании объекта недвижимости, в зависимости от ситуации
     **************************************************************************************************************/

    // Если данные по пользователю есть в БД, присваиваем их соответствующим переменным, иначе - значения по умолчанию.
    if (isset($rowProperty['typeOfObject'])) $typeOfObject = $rowProperty['typeOfObject']; else $typeOfObject = "0";
    if (isset($rowProperty['dateOfEntry']) && $rowProperty['dateOfEntry'] != "0000-00-00") $dateOfEntry = dateFromDBToView($rowProperty['dateOfEntry']); else $dateOfEntry = "";
    if (isset($rowProperty['termOfLease'])) $termOfLease = $rowProperty['termOfLease']; else $termOfLease = "0";
    if (isset($rowProperty['dateOfCheckOut']) && $rowProperty['dateOfCheckOut'] != "0000-00-00") $dateOfCheckOut = dateFromDBToView($rowProperty['dateOfCheckOut']); else $dateOfCheckOut = "";
    if (isset($rowProperty['amountOfRooms'])) $amountOfRooms = $rowProperty['amountOfRooms']; else $amountOfRooms = "0";
    if (isset($rowProperty['adjacentRooms'])) $adjacentRooms = $rowProperty['adjacentRooms']; else $adjacentRooms = "0";
    if (isset($rowProperty['amountOfAdjacentRooms'])) $amountOfAdjacentRooms = $rowProperty['amountOfAdjacentRooms']; else $amountOfAdjacentRooms = "0";
    if (isset($rowProperty['typeOfBathrooms'])) $typeOfBathrooms = $rowProperty['typeOfBathrooms']; else $typeOfBathrooms = "0";
    if (isset($rowProperty['typeOfBalcony'])) $typeOfBalcony = $rowProperty['typeOfBalcony']; else $typeOfBalcony = "0";
    if (isset($rowProperty['balconyGlazed'])) $balconyGlazed = $rowProperty['balconyGlazed']; else $balconyGlazed = "0";
    if (isset($rowProperty['roomSpace'])) $roomSpace = $rowProperty['roomSpace']; else $roomSpace = "";
    if (isset($rowProperty['totalArea'])) $totalArea = $rowProperty['totalArea']; else $totalArea = "";
    if (isset($rowProperty['livingSpace'])) $livingSpace = $rowProperty['livingSpace']; else $livingSpace = "";
    if (isset($rowProperty['kitchenSpace'])) $kitchenSpace = $rowProperty['kitchenSpace']; else $kitchenSpace = "";
    if (isset($rowProperty['floor'])) $floor = $rowProperty['floor']; else $floor = "";
    if (isset($rowProperty['totalAmountFloor'])) $totalAmountFloor = $rowProperty['totalAmountFloor']; else $totalAmountFloor = "";
    if (isset($rowProperty['numberOfFloor'])) $numberOfFloor = $rowProperty['numberOfFloor']; else $numberOfFloor = "";
    if (isset($rowProperty['concierge'])) $concierge = $rowProperty['concierge']; else $concierge = "0";
    if (isset($rowProperty['intercom'])) $intercom = $rowProperty['intercom']; else $intercom = "0";
    if (isset($rowProperty['parking'])) $parking = $rowProperty['parking']; else $parking = "0";
    if (isset($rowProperty['city'])) $city = $rowProperty['city']; else $city = "Екатеринбург";
    if (isset($rowProperty['district'])) $district = $rowProperty['district']; else $district = "0";
    if (isset($rowProperty['coordX'])) $coordX = $rowProperty['coordX']; else $coordX = "";
    if (isset($rowProperty['coordY'])) $coordY = $rowProperty['coordY']; else $coordY = "";
    if (isset($rowProperty['address'])) $address = $rowProperty['address']; else $address = "";
    if (isset($rowProperty['apartmentNumber'])) $apartmentNumber = $rowProperty['apartmentNumber']; else $apartmentNumber = "";
    if (isset($rowProperty['subwayStation'])) $subwayStation = $rowProperty['subwayStation']; else $subwayStation = "0";
    if (isset($rowProperty['distanceToMetroStation'])) $distanceToMetroStation = $rowProperty['distanceToMetroStation']; else $distanceToMetroStation = "";
    if (isset($rowProperty['currency'])) $currency = $rowProperty['currency']; else $currency = "0";
    if (isset($rowProperty['costOfRenting'])) $costOfRenting = $rowProperty['costOfRenting']; else $costOfRenting = "";
    if (isset($rowProperty['utilities'])) $utilities = $rowProperty['utilities']; else $utilities = "0";
    if (isset($rowProperty['costInSummer'])) $costInSummer = $rowProperty['costInSummer']; else $costInSummer = "";
    if (isset($rowProperty['costInWinter'])) $costInWinter = $rowProperty['costInWinter']; else $costInWinter = "";
    if (isset($rowProperty['electricPower'])) $electricPower = $rowProperty['electricPower']; else $electricPower = "0";
    if (isset($rowProperty['bail'])) $bail = $rowProperty['bail']; else $bail = "0";
    if (isset($rowProperty['bailCost'])) $bailCost = $rowProperty['bailCost']; else $bailCost = "";
    if (isset($rowProperty['prepayment'])) $prepayment = $rowProperty['prepayment']; else $prepayment = "0";
    if (isset($rowProperty['compensationMoney'])) $compensationMoney = $rowProperty['compensationMoney']; else $compensationMoney = "";
    if (isset($rowProperty['compensationPercent'])) $compensationPercent = $rowProperty['compensationPercent']; else $compensationPercent = "";
    if (isset($rowProperty['repair'])) $repair = $rowProperty['repair']; else $repair = "0";
    if (isset($rowProperty['furnish'])) $furnish = $rowProperty['furnish']; else $furnish = "0";
    if (isset($rowProperty['windows'])) $windows = $rowProperty['windows']; else $windows = "0";
    if (isset($rowProperty['internet'])) $internet = $rowProperty['internet']; else $internet = "0";
    if (isset($rowProperty['telephoneLine'])) $telephoneLine = $rowProperty['telephoneLine']; else $telephoneLine = "0";
    if (isset($rowProperty['cableTV'])) $cableTV = $rowProperty['cableTV']; else $cableTV = "0";
    if (isset($rowProperty['furnitureInLivingArea'])) $furnitureInLivingArea = unserialize($rowProperty['furnitureInLivingArea']); else $furnitureInLivingArea = array();
    if (isset($rowProperty['furnitureInLivingAreaExtra'])) $furnitureInLivingAreaExtra = $rowProperty['furnitureInLivingAreaExtra']; else $furnitureInLivingAreaExtra = "";
    if (isset($rowProperty['furnitureInKitchen'])) $furnitureInKitchen = unserialize($rowProperty['furnitureInKitchen']); else $furnitureInKitchen = array();
    if (isset($rowProperty['furnitureInKitchenExtra'])) $furnitureInKitchenExtra = $rowProperty['furnitureInKitchenExtra']; else $furnitureInKitchenExtra = "";
    if (isset($rowProperty['appliances'])) $appliances = unserialize($rowProperty['appliances']); else $appliances = array();
    if (isset($rowProperty['appliancesExtra'])) $appliancesExtra = $rowProperty['appliancesExtra']; else $appliancesExtra = "";
    if (isset($rowProperty['sexOfTenant'])) $sexOfTenant = explode("_", $rowProperty['sexOfTenant']); else $sexOfTenant = array();
    if (isset($rowProperty['relations'])) $relations = explode("_", $rowProperty['relations']); else $relations = array();
    if (isset($rowProperty['children'])) $children = $rowProperty['children']; else $children = "0";
    if (isset($rowProperty['animals'])) $animals = $rowProperty['animals']; else $animals = "0";
    if (isset($rowProperty['contactTelephonNumber'])) $contactTelephonNumber = $rowProperty['contactTelephonNumber']; else $contactTelephonNumber = "";
    if (isset($rowProperty['timeForRingBegin'])) $timeForRingBegin = $rowProperty['timeForRingBegin']; else $timeForRingBegin = "0";
    if (isset($rowProperty['timeForRingEnd'])) $timeForRingEnd = $rowProperty['timeForRingEnd']; else $timeForRingEnd = "0";
    if (isset($rowProperty['checking'])) $checking = $rowProperty['checking']; else $checking = "0";
    if (isset($rowProperty['responsibility'])) $responsibility = $rowProperty['responsibility']; else $responsibility = "";
    if (isset($rowProperty['comment'])) $comment = $rowProperty['comment']; else $comment = "";
    $fileUploadId = generateCode(7);

    /*************************************************************************************
     * Если пользователь заполнил и отослал форму - проверяем ее
     ************************************************************************************/

    // Если была нажата кнопка Сохранить, проверим данные на корректность и, если данные введены и введены правильно, добавим запись с новым объектом недвижмости в БД
    if (isset($_POST['saveAdvertButton'])) {
        // Формируем набор переменных для сохранения в базу данных, либо для возвращения вместе с формой при их некорректности
        if (isset($_POST['typeOfObject'])) $typeOfObject = htmlspecialchars($_POST['typeOfObject']);
        if (isset($_POST['dateOfEntry'])) $dateOfEntry = htmlspecialchars($_POST['dateOfEntry']);
        if (isset($_POST['termOfLease'])) $termOfLease = htmlspecialchars($_POST['termOfLease']);
        if (isset($_POST['dateOfCheckOut'])) $dateOfCheckOut = htmlspecialchars($_POST['dateOfCheckOut']);
        if (isset($_POST['amountOfRooms'])) $amountOfRooms = htmlspecialchars($_POST['amountOfRooms']);
        if (isset($_POST['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
        if (isset($_POST['amountOfAdjacentRooms'])) $amountOfAdjacentRooms = htmlspecialchars($_POST['amountOfAdjacentRooms']);
        if (isset($_POST['typeOfBathrooms'])) $typeOfBathrooms = htmlspecialchars($_POST['typeOfBathrooms']);
        if (isset($_POST['typeOfBalcony'])) $typeOfBalcony = htmlspecialchars($_POST['typeOfBalcony']);
        if (isset($_POST['balconyGlazed'])) $balconyGlazed = htmlspecialchars($_POST['balconyGlazed']);
        if (isset($_POST['roomSpace'])) $roomSpace = htmlspecialchars($_POST['roomSpace']);
        if (isset($_POST['totalArea'])) $totalArea = htmlspecialchars($_POST['totalArea']);
        if (isset($_POST['livingSpace'])) $livingSpace = htmlspecialchars($_POST['livingSpace']);
        if (isset($_POST['kitchenSpace'])) $kitchenSpace = htmlspecialchars($_POST['kitchenSpace']);
        if (isset($_POST['floor'])) $floor = htmlspecialchars($_POST['floor']);
        if (isset($_POST['totalAmountFloor'])) $totalAmountFloor = htmlspecialchars($_POST['totalAmountFloor']);
        if (isset($_POST['numberOfFloor'])) $numberOfFloor = htmlspecialchars($_POST['numberOfFloor']);
        if (isset($_POST['concierge'])) $concierge = htmlspecialchars($_POST['concierge']);
        if (isset($_POST['intercom'])) $intercom = htmlspecialchars($_POST['intercom']);
        if (isset($_POST['parking'])) $parking = htmlspecialchars($_POST['parking']);
        if (isset($_POST['district'])) $district = htmlspecialchars($_POST['district']);
        if (isset($_POST['coordX'])) $coordX = htmlspecialchars($_POST['coordX']);
        if (isset($_POST['coordY'])) $coordY = htmlspecialchars($_POST['coordY']);
        if (isset($_POST['address'])) $address = htmlspecialchars($_POST['address']);
        if (isset($_POST['apartmentNumber'])) $apartmentNumber = htmlspecialchars($_POST['apartmentNumber']);
        if (isset($_POST['subwayStation'])) $subwayStation = htmlspecialchars($_POST['subwayStation']);
        if (isset($_POST['distanceToMetroStation'])) $distanceToMetroStation = htmlspecialchars($_POST['distanceToMetroStation']);
        if (isset($_POST['currency'])) $currency = htmlspecialchars($_POST['currency']);
        if (isset($_POST['costOfRenting'])) $costOfRenting = htmlspecialchars($_POST['costOfRenting']);
        if (isset($_POST['utilities'])) $utilities = htmlspecialchars($_POST['utilities']);
        if (isset($_POST['costInSummer'])) $costInSummer = htmlspecialchars($_POST['costInSummer']);
        if (isset($_POST['costInWinter'])) $costInWinter = htmlspecialchars($_POST['costInWinter']);
        if (isset($_POST['electricPower'])) $electricPower = htmlspecialchars($_POST['electricPower']);
        if (isset($_POST['bail'])) $bail = htmlspecialchars($_POST['bail']);
        if (isset($_POST['bailCost'])) $bailCost = htmlspecialchars($_POST['bailCost']);
        if (isset($_POST['prepayment'])) $prepayment = htmlspecialchars($_POST['prepayment']);
        if (isset($_POST['compensationMoney'])) $compensationMoney = htmlspecialchars($_POST['compensationMoney']);
        if (isset($_POST['compensationPercent'])) $compensationPercent = htmlspecialchars($_POST['compensationPercent']);
        if (isset($_POST['repair'])) $repair = htmlspecialchars($_POST['repair']);
        if (isset($_POST['furnish'])) $furnish = htmlspecialchars($_POST['furnish']);
        if (isset($_POST['windows'])) $windows = htmlspecialchars($_POST['windows']);
        if (isset($_POST['internet'])) $internet = htmlspecialchars($_POST['internet']);
        if (isset($_POST['telephoneLine'])) $telephoneLine = htmlspecialchars($_POST['telephoneLine']);
        if (isset($_POST['cableTV'])) $cableTV = htmlspecialchars($_POST['cableTV']);
        if (isset($_POST['furnitureInLivingArea'])) $furnitureInLivingArea = $_POST['furnitureInLivingArea']; else $furnitureInLivingArea = array(); // Если пользователь отправил форму и не отметил ни одного предмета мебели, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
        if (isset($_POST['furnitureInLivingAreaExtra'])) $furnitureInLivingAreaExtra = htmlspecialchars($_POST['furnitureInLivingAreaExtra']);
        if (isset($_POST['furnitureInKitchen'])) $furnitureInKitchen = $_POST['furnitureInKitchen']; else $furnitureInKitchen = array(); // Если пользователь отправил форму и не отметил ни одного предмета мебели, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
        if (isset($_POST['furnitureInKitchenExtra'])) $furnitureInKitchenExtra = htmlspecialchars($_POST['furnitureInKitchenExtra']);
        if (isset($_POST['appliances'])) $appliances = $_POST['appliances']; else $appliances = array(); // Если пользователь отправил форму и не отметил ни одного предмета бытовой техники, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
        if (isset($_POST['appliancesExtra'])) $appliancesExtra = htmlspecialchars($_POST['appliancesExtra']);
        if (isset($_POST['sexOfTenant'])) $sexOfTenant = $_POST['sexOfTenant']; else $sexOfTenant = array(); // Если пользователь отправил форму и не отметил ни одного пола, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
        if (isset($_POST['relations'])) $relations = $_POST['relations']; else $relations = array(); // Если пользователь отправил форму и не отметил ни одной формы отношений между арендаторами, то обязательно нужно явно присвоить этой переменной пустой массив, иначе изменение не вступит в силу, а возьмется старое значение из БД
        if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']);
        if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']);
        if (isset($_POST['contactTelephonNumber'])) $contactTelephonNumber = htmlspecialchars($_POST['contactTelephonNumber']);
        if (isset($_POST['timeForRingBegin'])) $timeForRingBegin = htmlspecialchars($_POST['timeForRingBegin']);
        if (isset($_POST['timeForRingEnd'])) $timeForRingEnd = htmlspecialchars($_POST['timeForRingEnd']);
        if (isset($_POST['checking'])) $checking = htmlspecialchars($_POST['checking']);
        if (isset($_POST['responsibility'])) $responsibility = htmlspecialchars($_POST['responsibility']);
        if (isset($_POST['comment'])) $comment = htmlspecialchars($_POST['comment']);
        $fileUploadId = $_POST['fileUploadId'];

        // Проверяем корректность данных объявления. Функции isAdvertCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = isAdvertCorrect("editAdvert");
        if (is_array($errors) && count($errors) == 0) $correct = TRUE; else $correct = FALSE; // Считаем ошибки, если 0, то можно будет записать данные в БД

        // Если данные, указанные пользователем, корректны, запишем объявление в базу данных
        if ($correct) {
            // Корректируем даты для того, чтобы сделать их пригодными для сохранения в базу данных
            $dateOfEntryForDB = dateFromViewToDB($dateOfEntry);
            $dateOfCheckOutForDB = dateFromViewToDB($dateOfCheckOut);

            // Для хранения массивов в БД, их необходимо сериализовать
            $furnitureInLivingAreaSerialized = serialize($furnitureInLivingArea);
            $furnitureInKitchenSerialized = serialize($furnitureInKitchen);
            $appliancesSerialized = serialize($appliances);
            $sexOfTenantImploded = implode("_", $sexOfTenant);
            $relationsImploded = implode("_", $relations);

            // Проверяем в какой валюте сохраняется стоимость аренды, формируем переменную realCostOfRenting
            if ($currency == 'руб.') $realCostOfRenting = $costOfRenting;
            if ($currency != 'руб.') {
                $rezCurrency = mysql_query("SELECT value FROM currencies WHERE name = '" . $currency . "'");
                $rowCurrency = mysql_fetch_assoc($rezCurrency);
                if ($rowCurrency != FALSE) $realCostOfRenting = $costOfRenting * $rowCurrency['value']; else $realCostOfRenting = 0;
            }

            $tm = time();
            $last_act = $tm; // время последнего редактирования объявления
            $reg_date = $tm; // время регистрации ("рождения") объявления

            if (mysql_query("UPDATE property SET
                            userId='" . $userId . "',
                            typeOfObject='" . $typeOfObject . "',
                            dateOfEntry='" . $dateOfEntryForDB . "',
                            termOfLease='" . $termOfLease . "',
                            dateOfCheckOut='" . $dateOfCheckOutForDB . "',
                            amountOfRooms='" . $amountOfRooms . "',
                            adjacentRooms='" . $adjacentRooms . "',
                            amountOfAdjacentRooms='" . $amountOfAdjacentRooms . "',
                            typeOfBathrooms='" . $typeOfBathrooms . "',
                            typeOfBalcony='" . $typeOfBalcony . "',
                            balconyGlazed='" . $balconyGlazed . "',
                            roomSpace='" . $roomSpace . "',
                            totalArea='" . $totalArea . "',
                            livingSpace='" . $livingSpace . "',
                            kitchenSpace='" . $kitchenSpace . "',
                            floor='" . $floor . "',
                            totalAmountFloor='" . $totalAmountFloor . "',
                            numberOfFloor='" . $numberOfFloor . "',
                            concierge='" . $concierge . "',
                            intercom='" . $intercom . "',
                            parking='" . $parking . "',
                            city='" . $city . "',
                            district='" . $district . "',
                            coordX='" . $coordX . "',
                            coordY='" . $coordY . "',
                            address='" . $address . "',
                            apartmentNumber='" . $apartmentNumber . "',
                            subwayStation='" . $subwayStation . "',
                            distanceToMetroStation='" . $distanceToMetroStation . "',
                            currency='" . $currency . "',
                            costOfRenting='" . $costOfRenting . "',
                            realCostOfRenting='" . $realCostOfRenting . "',
                            utilities='" . $utilities . "',
                            costInSummer='" . $costInSummer . "',
                            costInWinter='" . $costInWinter . "',
                            electricPower='" . $electricPower . "',
                            bail='" . $bail . "',
                            bailCost='" . $bailCost . "',
                            prepayment='" . $prepayment . "',
                            compensationMoney='" . $compensationMoney . "',
                            compensationPercent='" . $compensationPercent . "',
                            repair='" . $repair . "',
                            furnish='" . $furnish . "',
                            windows='" . $windows . "',
                            internet='" . $internet . "',
                            telephoneLine='" . $telephoneLine . "',
                            cableTV='" . $cableTV . "',
                            furnitureInLivingArea='" . $furnitureInLivingAreaSerialized . "',
                            furnitureInLivingAreaExtra='" . $furnitureInLivingAreaExtra . "',
                            furnitureInKitchen='" . $furnitureInKitchenSerialized . "',
                            furnitureInKitchenExtra='" . $furnitureInKitchenExtra . "',
                            appliances='" . $appliancesSerialized . "',
                            appliancesExtra='" . $appliancesExtra . "',
                            sexOfTenant='" . $sexOfTenantImploded . "',
                            relations='" . $relationsImploded . "',
                            children='" . $children . "',
                            animals='" . $animals . "',
                            contactTelephonNumber='" . $contactTelephonNumber . "',
                            timeForRingBegin='" . $timeForRingBegin . "',
                            timeForRingEnd='" . $timeForRingEnd . "',
                            checking='" . $checking . "',
                            responsibility='" . $responsibility . "',
                            comment='" . $comment . "',
                            last_act='" . $last_act . "',
                            reg_date='" . $reg_date . "'
                            WHERE id = '" . $propertyId . "'")
            ) {
                /******* Переносим информацию о фотографиях объекта недвижимости в таблицу для постоянного хранения *******/
                // Получим информацию о всех фотках, соответствующих текущему fileUploadId
                $rezTempFotos = mysql_query("SELECT id, filename, extension, filesizeMb FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");
                for ($i = 0; $i < mysql_num_rows($rezTempFotos); $i++) {
                    $rowTempFotos = mysql_fetch_assoc($rezTempFotos);
                    mysql_query("INSERT INTO propertyFotos (id, filename, extension, filesizeMb, propertyId) VALUES ('" . $rowTempFotos['id'] . "','" . $rowTempFotos['filename'] . "','" . $rowTempFotos['extension'] . "','" . $rowTempFotos['filesizeMb'] . "','" . $propertyId . "')"); // Переносим информацию о фотографиях на постоянное хранение
                }
                // Удаляем записи о фотках в таблице для временного хранения данных
                mysql_query("DELETE FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");

                // Пересылаем пользователя в личный кабинет на вкладку Мои объявления
                header('Location: personal.php?tabsId=3');
            } else {
                $correct = FALSE;
                $errors[] = 'Не прошел запрос к БД. К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку';
                // Сохранении данных в БД не прошло - объявление не сохранено
            }
        }
    }

    // В будущем необходимо будет проверять личные данные пользователя на полноту для его работы в качестве собственника, если у него typeOwner != "true"
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Редактирование объявления</title>
    <meta name="description" content="Редактирование объявления">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/fileuploader.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Стили для создания нового Объявления*/

        .objectDescriptionItem .objectDescriptionItemLabel {
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

        .objectDescriptionBody ul {
            list-style: none;
            padding: 0;
            margin: 0;
            line-height: 1.6em;
        }

        .advertDescriptionEdit {
            border: none;
            border-radius: 5px;
            padding: 0.2em;
            background-color: #ffffff;
        }

        .advertDescriptionChapterHeader {
            background-color: #6A9D02;
            font-size: 1.2em;
            padding-left: 15px;
            border-radius: 5px;
            color: white;
            text-align: left;
            line-height: 1.8em;
        }

        .tableForMap td {
            padding: 0;
        }

        .bottomButton {
            margin: 10px 10px 10px 10px;
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

<!-- Сформируем и вставим заголовок страницы -->
<?php
    include("header.php");
?>

<div class="page_main_content">

<div class="headerOfPage">
    Редактирование объявления.
    <?php
    if ($apartmentNumber != "") $apartmentNumberInHeader = ", № " . $apartmentNumber; else $apartmentNumberInHeader = "";
    echo getFirstCharUpper($typeOfObject) . " по адресу: " . $address . $apartmentNumberInHeader;
    ?>
</div>

<form method="post" name="newAdvert" class="advertDescriptionEdit">
<div class="advertDescriptionChapter" id="typeAndPeriodChapter">
    <div class="advertDescriptionChapterHeader">
        Тип и сроки
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Тип объекта:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="typeOfObject" id="typeOfObject" <?php echo "value='$typeOfObject'"; ?>>
            <!-- Значение поля необходимо сохранить, так как JS в зависимости от него будет делать некоторые элементы недоступными для редактирования -->
            <?php
            echo $typeOfObject;
            ?>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            С какого числа можно въезжать:
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfEntry" type="text" id="datepicker1" size="15"
                   placeholder="дд.мм.гггг" <?php echo "value='$dateOfEntry'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            На какой срок сдается:
        </div>
        <div class="objectDescriptionBody">
            <select name="termOfLease" id="termOfLease">
                <option value="0" <?php if ($termOfLease == "0") echo "selected";?>></option>
                <option value="длительный срок" <?php if ($termOfLease == "длительный срок") echo "selected";?>>
                    длительный срок (от года)
                </option>
                <option value="несколько месяцев" <?php if ($termOfLease == "несколько месяцев") echo "selected";?>>
                    несколько месяцев (до года)
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="termOfLease_0&termOfLease_длительный срок">
        <div class="objectDescriptionItemLabel">
            Крайний срок выезда арендатора(ов):
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfCheckOut" type="text" id="datepicker2" size="15"
                   placeholder="дд.мм.гггг" <?php echo "value='$dateOfCheckOut'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Фотографии:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="fileUploadId" id="fileUploadId" <?php echo "value='$fileUploadId'"; ?>>
            <?php
            // Получаем информацию о всех загруженных фото и формируем для каждого свой input type hidden для передачи данных в обработчик яваскрипта
            if ($rez = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $propertyId . "'")) // ищем уже загруженные пользователем фотки
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
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="roomsAndFacilitiesChapter">
    <div class="advertDescriptionChapterHeader">
        Комнаты и помещения
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Количество комнат в квартире, доме:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfRooms" id="amountOfRooms">
                <option value="0" <?php if ($amountOfRooms == "0") echo "selected";?>></option>
                <option value="1" <?php if ($amountOfRooms == "1") echo "selected";?>>1</option>
                <option value="2" <?php if ($amountOfRooms == "2") echo "selected";?>>2</option>
                <option value="3" <?php if ($amountOfRooms == "3") echo "selected";?>>3</option>
                <option value="4" <?php if ($amountOfRooms == "4") echo "selected";?>>4</option>
                <option value="5" <?php if ($amountOfRooms == "5") echo "selected";?>>5</option>
                <option value="6" <?php if ($amountOfRooms == "6") echo "selected";?>>6 или более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="amountOfRooms_0&amountOfRooms_1">
        <div class="objectDescriptionItemLabel">
            Комнаты смежные:
        </div>
        <div class="objectDescriptionBody">
            <select name="adjacentRooms" id="adjacentRooms">
                <option value="0" <?php if ($adjacentRooms == "0") echo "selected";?>></option>
                <option value="да" <?php if ($adjacentRooms == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($adjacentRooms == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_комната&typeOfObject_гараж&adjacentRooms_0&adjacentRooms_нет&amountOfRooms_0&amountOfRooms_1&amountOfRooms_2">
        <div class="objectDescriptionItemLabel">
            Количество смежных комнат:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfAdjacentRooms">
                <option value="0" <?php if ($amountOfAdjacentRooms == "0") echo "selected";?>></option>
                <option value="2" <?php if ($amountOfAdjacentRooms == "2") echo "selected";?>>2</option>
                <option value="3" <?php if ($amountOfAdjacentRooms == "3") echo "selected";?>>3</option>
                <option value="4" <?php if ($amountOfAdjacentRooms == "4") echo "selected";?>>4</option>
                <option value="5" <?php if ($amountOfAdjacentRooms == "5") echo "selected";?>>5</option>
                <option value="6" <?php if ($amountOfAdjacentRooms == "6") echo "selected";?>>6 или более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Санузел:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBathrooms">
                <option value="0" <?php if ($typeOfBathrooms == "0") echo "selected";?>></option>
                <option value="раздельный" <?php if ($typeOfBathrooms == "раздельный") echo "selected";?>>раздельный
                </option>
                <option value="совмещенный" <?php if ($typeOfBathrooms == "совмещенный") echo "selected";?>>
                    совмещенный
                </option>
                <option value="2 шт." <?php if ($typeOfBathrooms == "2 шт.") echo "selected";?>>2</option>
                <option value="3 шт." <?php if ($typeOfBathrooms == "3 шт.") echo "selected";?>>3</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Балкон/лоджия:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBalcony" id="typeOfBalcony">
                <option value="0" <?php if ($typeOfBalcony == "0") echo "selected";?>></option>
                <option value="нет" <?php if ($typeOfBalcony == "нет") echo "selected";?>>нет</option>
                <option value="балкон" <?php if ($typeOfBalcony == "балкон") echo "selected";?>>балкон</option>
                <option value="лоджия" <?php if ($typeOfBalcony == "лоджия") echo "selected";?>>лоджия</option>
                <option value="эркер" <?php if ($typeOfBalcony == "эркер") echo "selected";?>>эркер</option>
                <option value="балкон и лоджия" <?php if ($typeOfBalcony == "балкон и лоджия") echo "selected";?>>балкон
                    и лоджия
                </option>
                <option value="балкон и эркер" <?php if ($typeOfBalcony == "балкон и эркер") echo "selected";?>>балкон и
                    эркер
                </option>
                <option value="2 балкона и более" <?php if ($typeOfBalcony == "2 балкона и более") echo "selected";?>>2
                    балкона и более
                </option>
                <option value="2 лоджии и более" <?php if ($typeOfBalcony == "2 лоджии и более") echo "selected";?>>2
                    лоджии и более
                </option>
                <option value="2 эркера и более" <?php if ($typeOfBalcony == "2 эркера и более") echo "selected";?>>2
                    эркера и более
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfBalcony_0&typeOfBalcony_нет&typeOfBalcony_эркер&typeOfBalcony_2 эркера и более">
        <div class="objectDescriptionItemLabel">
            Остекление балкона/лоджии:
        </div>
        <div class="objectDescriptionBody">
            <select name="balconyGlazed">
                <option value="0" <?php if ($balconyGlazed == "0") echo "selected";?>></option>
                <option value="да" <?php if ($balconyGlazed == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($balconyGlazed == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_квартира&typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Площадь комнаты:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="roomSpace" <?php echo "value='$roomSpace'";?>>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_комната">
        <div class="objectDescriptionItemLabel">
            Площадь общая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="totalArea" <?php echo "value='$totalArea'";?>>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_комната&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Площадь жилая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="livingSpace" <?php echo "value='$livingSpace'";?>>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Площадь кухни:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="kitchenSpace" <?php echo "value='$kitchenSpace'";?>>
            м²
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="floorAndPorchChapter">
    <div class="advertDescriptionChapterHeader">
        Этаж и подъезд
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Этаж:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="floor" <?php echo "value='$floor'";?>>
            <?php echo $floor;?>
            из
            <input type="hidden" name="totalAmountFloor" <?php echo "value='$totalAmountFloor'";?>>
            <?php echo $totalAmountFloor;?>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_квартира&typeOfObject_комната&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Этажность дома:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="numberOfFloor" <?php echo "value='$numberOfFloor'";?>>
            <?php echo $numberOfFloor;?>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Консьерж:
        </div>
        <div class="objectDescriptionBody">
            <select name="concierge">
                <option value="0" <?php if ($concierge == "0") echo "selected";?>></option>
                <option value="есть" <?php if ($concierge == "есть") echo "selected";?>>есть</option>
                <option value="нет" <?php if ($concierge == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Домофон:
        </div>
        <div class="objectDescriptionBody">
            <select name="intercom">
                <option value="0" <?php if ($intercom == "0") echo "selected";?>></option>
                <option value="есть" <?php if ($intercom == "есть") echo "selected";?>>есть</option>
                <option value="нет" <?php if ($intercom == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Парковка во дворе:
        </div>
        <div class="objectDescriptionBody">
            <select name="parking">
                <option value="0" <?php if ($parking == "0") echo "selected";?>></option>
                <option value="охраняемая" <?php if ($parking == "охраняемая") echo "selected";?>>охраняемая</option>
                <option value="неохраняемая" <?php if ($parking == "неохраняемая") echo "selected";?>>неохраняемая
                </option>
                <option value="подземная" <?php if ($parking == "подземная") echo "selected";?>>подземная</option>
                <option value="отсутствует" <?php if ($parking == "отсутствует") echo "selected";?>>отсутствует</option>
            </select>
        </div>
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
            <span>Екатеринбург</span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Район:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="district" <?php echo "value='$district'";?>">
            <!-- Значение поля необходимо сохранить, так как JS в зависимости от него будет делать некоторые элементы недоступными для редактирования -->
            <?php
            if (isset($district)) echo $district;
            ?>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel" style="line-height: 2.3em;">
            Улица и номер дома:
        </div>
        <div class="objectDescriptionBody" style="min-width: 470px">
            <input type="hidden" name="coordX" id="coordX" <?php echo "value='$coordX'";?>>
            <input type="hidden" name="coordY" id="coordY" <?php echo "value='$coordY'";?>>
            <table class="tableForMap">
                <tbody>
                    <tr>
                        <td>
                            <input type="hidden" name="address" id="addressTextBox" <?php echo "value='$address'";?>">
                            <!-- Значение поля необходимо сохранить, так как JS в зависимости от него будет делать некоторые элементы недоступными для редактирования -->
                            <?php echo $address; ?>
                        </td>
                        <td>
                            <button id="checkAddressButton" style='margin-left: 0.7em;'>Проверить адрес</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'><!-- Карта Яндекса -->
                            <div id="mapForNewAdvert" style="width: 100%; height: 400px; margin-top: 15px;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_дом&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Номер квартиры:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="apartmentNumber" <?php echo "value='$apartmentNumber'";?>>
            <!-- Значение поля необходимо сохранить, так как JS в зависимости от него будет делать некоторые элементы недоступными для редактирования -->
            <?php if ($apartmentNumber != "") echo $apartmentNumber; ?>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Станция метро рядом:
        </div>
        <div class="objectDescriptionBody">
            <select name="subwayStation" id="subwayStation">
                <option value="0" <?php if ($subwayStation == "0") echo "selected";?>></option>
                <option value="нет" <?php if ($subwayStation == "нет") echo "selected";?>>Нет</option>
                <option
                    value="Проспект Космонавтов" <?php if ($subwayStation == "Проспект Космонавтов") echo "selected";?>>
                    Проспект Космонавтов
                </option>
                <option value="Уралмаш" <?php if ($subwayStation == "Уралмаш") echo "selected";?>>Уралмаш</option>
                <option value="Машиностроителей" <?php if ($subwayStation == "Машиностроителей") echo "selected";?>>
                    Машиностроителей
                </option>
                <option value="Уральская" <?php if ($subwayStation == "Уральская") echo "selected";?>>Уральская</option>
                <option value="Динамо" <?php if ($subwayStation == "Динамо") echo "selected";?>>Динамо</option>
                <option value="Площадь 1905 г." <?php if ($subwayStation == "Площадь 1905 г.") echo "selected";?>>
                    Площадь 1905 г.
                </option>
                <option value="Геологическая" <?php if ($subwayStation == "Геологическая") echo "selected";?>>
                    Геологическая
                </option>
                <option value="Чкаловская" <?php if ($subwayStation == "Чкаловская") echo "selected";?>>Чкаловская
                </option>
                <option value="Ботаническая" <?php if ($subwayStation == "Ботаническая") echo "selected";?>>
                    Ботаническая
                </option>
            </select>
            <span notavailability="subwayStation_0&subwayStation_нет">
            <input type="text" name="distanceToMetroStation" size="7" <?php echo "value='$distanceToMetroStation'";?>>
            мин. ходьбы
            </span>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="costChapter">
    <div class="advertDescriptionChapterHeader">
        Стоимость, условия оплаты
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Валюта для расчетов:
        </div>
        <div class="objectDescriptionBody">
            <select name="currency" id="currency">
                <option value="0" <?php if ($currency == "0") echo "selected";?>></option>
                <option value="руб." <?php if ($currency == "руб.") echo "selected";?>>рубль</option>
                <option value="дол. США" <?php if ($currency == "дол. США") echo "selected";?>>доллар США</option>
                <option value="евро" <?php if ($currency == "евро") echo "selected";?>>евро</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Плата за аренду:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="costOfRenting" id="costOfRenting" size="7" <?php echo "value='$costOfRenting'";?>>
            <span class="currency"></span> в месяц
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Коммунальные услуги оплачиваются арендатором дополнительно:
        </div>
        <div class="objectDescriptionBody">
            <select name="utilities" id="utilities">
                <option value="0" <?php if ($utilities == "0") echo "selected";?>></option>
                <option value="да" <?php if ($utilities == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($utilities == "нет") echo "selected";?>>нет</option>
            </select>
            <span notavailability="utilities_0&utilities_нет">
            Летом
            <input type="text" name="costInSummer" size="7" <?php echo "value='$costInSummer'";?>>
            <span class="currency"></span>
            Зимой
            <input type="text" name="costInWinter" size="7" <?php echo "value='$costInWinter'";?>>
            <span class="currency"></span>
            </span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Электроэнергия оплачивается дополнительно:
        </div>
        <div class="objectDescriptionBody">
            <select name="electricPower">
                <option value="0" <?php if ($electricPower == "0") echo "selected";?>></option>
                <option value="да" <?php if ($electricPower == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($electricPower == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Залог:
        </div>
        <div class="objectDescriptionBody">
            <select name="bail" id="bail">
                <option value="0" <?php if ($bail == "0") echo "selected";?>></option>
                <option value="есть" <?php if ($bail == "есть") echo "selected";?>>есть</option>
                <option value="нет" <?php if ($bail == "нет") echo "selected";?>>нет</option>
            </select>
            <span notavailability="bail_0&bail_нет">
            <input type="text" name="bailCost" size="7" <?php echo "value='$bailCost'";?>>
            <span class="currency"></span>
            </span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Предоплата:
        </div>
        <div class="objectDescriptionBody">
            <select name="prepayment">
                <option value="0" <?php if ($prepayment == "0") echo "selected";?>></option>
                <option value="нет" <?php if ($prepayment == "нет") echo "selected";?>>нет</option>
                <option value="1 месяц" <?php if ($prepayment == "1 месяц") echo "selected";?>>1 месяц</option>
                <option value="2 месяца" <?php if ($prepayment == "2 месяца") echo "selected";?>>2 месяца</option>
                <option value="3 месяца" <?php if ($prepayment == "3 месяца") echo "selected";?>>3 месяца</option>
                <option value="4 месяца" <?php if ($prepayment == "4 месяца") echo "selected";?>>4 месяца</option>
                <option value="5 месяцев" <?php if ($prepayment == "5 месяцев") echo "selected";?>>5 месяцев</option>
                <option value="6 месяцев" <?php if ($prepayment == "6 месяцев") echo "selected";?>>6 месяцев</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         title="Предназначена для компенсации затрат собственника на публикацию объявления и поиск арендатора">
        <div class="objectDescriptionItemLabel">
            Единоразовая комиссия собственника:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="compensationMoney"
                   id="compensationMoney" <?php echo "value='$compensationMoney'";?>>
            <span class="currency"></span> или <input type="text" size="7" name="compensationPercent"
                                                      id="compensationPercent" <?php echo "value='$compensationPercent'";?>>
            % от стоимости аренды
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="currentStatus">
    <div class="advertDescriptionChapterHeader">
        Текущее состояние
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Ремонт:
        </div>
        <div class="objectDescriptionBody">
            <select name="repair">
                <option value="0" <?php if ($repair == "0") echo "selected";?>></option>
                <option
                    value="не выполнялся (новый дом)" <?php if ($repair == "не выполнялся (новый дом)") echo "selected";?>>
                    не выполнялся (новый дом)
                </option>
                <option value="сделан только что" <?php if ($repair == "сделан только что") echo "selected";?>>сделан
                    только что
                </option>
                <option value="меньше 1 года назад" <?php if ($repair == "меньше 1 года назад") echo "selected";?>>
                    меньше 1 года назад
                </option>
                <option value="больше года назад" <?php if ($repair == "больше года назад") echo "selected";?>>больше
                    года назад
                </option>
                <option value="выполнялся давно" <?php if ($repair == "выполнялся давно") echo "selected";?>>выполнялся
                    давно
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Отделка:
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <select name="furnish">
                <option value="0" <?php if ($furnish == "0") echo "selected";?>></option>
                <option value="евростандарт" <?php if ($furnish == "евростандарт") echo "selected";?>>евростандарт
                </option>
                <option
                    value="свежая (новые обои, побелка потолков)" <?php if ($furnish == "свежая (новые обои, побелка потолков)") echo "selected";?>>
                    свежая (новые обои, побелка потолков)
                </option>
                <option value="бабушкин вариант" <?php if ($furnish == "бабушкин вариант") echo "selected";?>>бабушкин
                    вариант
                </option>
                <option value="требует обновления" <?php if ($furnish == "требует обновления") echo "selected";?>>
                    требует обновления
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Окна:
        </div>
        <div class="objectDescriptionBody">
            <select name="windows">
                <option value="0" <?php if ($windows == "0") echo "selected";?>></option>
                <option value="деревянные" <?php if ($windows == "деревянные") echo "selected";?>>деревянные</option>
                <option value="пластиковые" <?php if ($windows == "пластиковые") echo "selected";?>>пластиковые</option>
                <option value="иное" <?php if ($windows == "иное") echo "selected";?>>иное</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="communication">
    <div class="advertDescriptionChapterHeader">
        Связь
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Интернет:
        </div>
        <div class="objectDescriptionBody">
            <select name="internet">
                <option value="0" <?php if ($internet == "0") echo "selected";?>></option>
                <option value="проведен" <?php if ($internet == "проведен") echo "selected";?>>проведен</option>
                <option value="не проведен" <?php if ($internet == "не проведен") echo "selected";?>>не проведен
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Телефон:
        </div>
        <div class="objectDescriptionBody">
            <select name="telephoneLine">
                <option value="0" <?php if ($telephoneLine == "0") echo "selected";?>></option>
                <option value="проведен" <?php if ($telephoneLine == "проведен") echo "selected";?>>проведен</option>
                <option value="не проведен" <?php if ($telephoneLine == "не проведен") echo "selected";?>>не проведен
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Кабельное ТВ:
        </div>
        <div class="objectDescriptionBody">
            <select name="cableTV">
                <option value="0" <?php if ($cableTV == "0") echo "selected";?>></option>
                <option value="проведено" <?php if ($cableTV == "проведено") echo "selected";?>>проведено</option>
                <option value="не проведено" <?php if ($cableTV == "не проведено") echo "selected";?>>не проведено
                </option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="furniture">
    <div class="advertDescriptionChapterHeader">
        Мебель и бытовая техника
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Мебель в жилой зоне:
        </div>
        <div class="objectDescriptionBody">
            <ul>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]" value="диван раскладной"
                        <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "диван раскладной") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> диван раскладной
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="диван нераскладной" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "диван нераскладной") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> диван нераскладной
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кровать одноместная" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "кровать одноместная") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кровать одноместная
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кровать двухместная" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "кровать двухместная") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кровать двухместная
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кровать детская" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "кровать детская") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кровать детская
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол письменный" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "стол письменный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол письменный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол компьютерный" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "стол компьютерный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол компьютерный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол журнальный" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "стол журнальный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол журнальный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол раскладной" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "стол раскладной") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол раскладной
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кресло раскладное" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "кресло раскладное") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кресло раскладное
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кресло нераскладное" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "кресло нераскладное") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кресло нераскладное
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стулья и табуретки" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "стулья и табуретки") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стулья и табуретки
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стенка" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "стенка") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стенка
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="шкаф для одежды" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "шкаф для одежды") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкаф для одежды
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="шкаф-купе" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "шкаф-купе") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкаф-купе
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="комод" <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "комод") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> комод
                </li>
                <li>
                    <input type="text" name="furnitureInLivingAreaExtra" maxlength="254"
                           title='Перечислите через запятую те предметы мебели в жилой зоне, что предоставляются вместе с арендуемой недвижимостью и не были указаны в списке выше. Например: "трюмо, тумбочка под телевизор"' <?php echo "value='$furnitureInLivingAreaExtra'";?>>
                </li>
            </ul>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Мебель на кухне:
        </div>
        <div class="objectDescriptionBody">
            <ul>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="стол обеденный" <?php foreach ($furnitureInKitchen as $value) {
                        if ($value == "стол обеденный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол обеденный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="стулья, табуретки" <?php foreach ($furnitureInKitchen as $value) {
                        if ($value == "стулья, табуретки") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стулья, табуретки
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="диван" <?php foreach ($furnitureInKitchen as $value) {
                        if ($value == "диван") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> диван
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="кухонный гарнитур" <?php foreach ($furnitureInKitchen as $value) {
                        if ($value == "кухонный гарнитур") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кухонный гарнитур
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="шкафчики навесные" <?php foreach ($furnitureInKitchen as $value) {
                        if ($value == "шкафчики навесные") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкафчики навесные
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="шкафчики напольные" <?php foreach ($furnitureInKitchen as $value) {
                        if ($value == "шкафчики напольные") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкафчики напольные
                </li>
                <li>
                    <input type="text" name="furnitureInKitchenExtra" maxlength="254"
                           title='Перечислите через запятую те предметы мебели на кухне, что предоставляются вместе с арендуемой недвижимостью и не были указаны в списке выше. Например: "трюмо, тумбочка под телевизор"' <?php echo "value='$furnitureInKitchenExtra'";?>>
                </li>
            </ul>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Бытовая техника:
        </div>
        <div class="objectDescriptionBody">
            <ul>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="холодильник" <?php foreach ($appliances as $value) {
                        if ($value == "холодильник") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> холодильник
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="микроволновая печь" <?php foreach ($appliances as $value) {
                        if ($value == "микроволновая печь") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> микроволновая печь
                </li>
                <li>
                    <input type="checkbox" name="appliances[]" value="телевизор" <?php foreach ($appliances as $value) {
                        if ($value == "телевизор") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> телевизор
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="стиральная машина (автомат)" <?php foreach ($appliances as $value) {
                        if ($value == "стиральная машина (автомат)") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стиральная машина (автомат)
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="стиральная машина (не автомат)" <?php foreach ($appliances as $value) {
                        if ($value == "стиральная машина (не автомат)") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стиральная машина (не автомат)
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="нагреватель воды" <?php foreach ($appliances as $value) {
                        if ($value == "нагреватель воды") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> нагреватель воды
                </li>
                <li>
                    <input type="checkbox" name="appliances[]" value="пылесос" <?php foreach ($appliances as $value) {
                        if ($value == "пылесос") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> пылесос
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="кондиционер" <?php foreach ($appliances as $value) {
                        if ($value == "кондиционер") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кондиционер
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="охранная сигнализация" <?php foreach ($appliances as $value) {
                        if ($value == "охранная сигнализация") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> охранная сигнализация
                </li>
                <li>
                    <input type="text" name="appliancesExtra" maxlength="254"
                           title='Перечислите через запятую ту бытовую технику, что предоставляется вместе с арендуемой недвижимостью и не была указана в списке выше. Например: "кухонный комбайн, компьютер"' <?php echo "value='$appliancesExtra'";?>>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="requirementsForTenant">
    <div class="advertDescriptionChapterHeader">
        Требования к арендатору
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Пол:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="sexOfTenant[]" value="мужчина" <?php foreach ($sexOfTenant as $value) {
                if ($value == "мужчина") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            мужчина
            <br>
            <input type="checkbox" name="sexOfTenant[]" value="женщина" <?php foreach ($sexOfTenant as $value) {
                if ($value == "женщина") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            женщина
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Отношения между арендаторами:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="relations[]" value="один человек" <?php foreach ($relations as $value) {
                if ($value == "один человек") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            один человек
            <br>
            <input type="checkbox" name="relations[]" value="семья" <?php foreach ($relations as $value) {
                if ($value == "семья") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            семья
            <br>
            <input type="checkbox" name="relations[]" value="пара" <?php foreach ($relations as $value) {
                if ($value == "пара") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            пара
            <br>
            <input type="checkbox" name="relations[]" value="2 мальчика" <?php foreach ($relations as $value) {
                if ($value == "2 мальчика") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            2 мальчика
            <br>
            <input type="checkbox" name="relations[]" value="2 девочки" <?php foreach ($relations as $value) {
                if ($value == "2 девочки") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            2 девочки
            <br>
            <input type="checkbox" name="relations[]" value="группа людей" <?php foreach ($relations as $value) {
                if ($value == "группа людей") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            группа людей
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Дети:
        </div>
        <div class="objectDescriptionBody">
            <select name="children">
                <option value="0" <?php if ($children == "0") echo "selected";?>></option>
                <option value="не имеет значения" <?php if ($children == "не имеет значения") echo "selected";?>>не
                    имеет значения
                </option>
                <option
                    value="с детьми старше 4-х лет" <?php if ($children == "с детьми старше 4-х лет") echo "selected";?>>
                    с детьми старше 4-х лет
                </option>
                <option value="только без детей" <?php if ($children == "только без детей") echo "selected";?>>только
                    без детей
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Животные:
        </div>
        <div class="objectDescriptionBody">
            <select name="animals">
                <option value="0" <?php if ($animals == "0") echo "selected";?>></option>
                <option value="не имеет значения" <?php if ($animals == "не имеет значения") echo "selected";?>>не имеет
                    значения
                </option>
                <option value="только без животных" <?php if ($animals == "только без животных") echo "selected";?>>
                    только без животных
                </option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="specialConditions">
    <div class="advertDescriptionChapterHeader">
        Особые условия
    </div>
    <div class="objectDescriptionItem" title="Этот номер будет доступен для заинтересовавшихся арендаторов">
        <div class="objectDescriptionItemLabel">
            Контактный номер телефона:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="contactTelephonNumber" size="15" <?php echo "value='$contactTelephonNumber'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel" style="line-height: 1.8em;">
            Время для звонков:
        </div>
        <div class="objectDescriptionBody">
            с
            <select name="timeForRingBegin">
                <option value="0" <?php if ($timeForRingBegin == "0") echo "selected";?>></option>
                <option value="6:00" <?php if ($timeForRingBegin == "6:00") echo "selected";?>>6:00</option>
                <option value="7:00" <?php if ($timeForRingBegin == "7:00") echo "selected";?>>7:00</option>
                <option value="8:00" <?php if ($timeForRingBegin == "8:00") echo "selected";?>>8:00</option>
                <option value="9:00" <?php if ($timeForRingBegin == "9:00") echo "selected";?>>9:00</option>
                <option value="10:00" <?php if ($timeForRingBegin == "10:00") echo "selected";?>>10:00</option>
                <option value="11:00" <?php if ($timeForRingBegin == "11:00") echo "selected";?>>11:00</option>
                <option value="12:00" <?php if ($timeForRingBegin == "12:00") echo "selected";?>>12:00</option>
                <option value="13:00" <?php if ($timeForRingBegin == "13:00") echo "selected";?>>13:00</option>
                <option value="14:00" <?php if ($timeForRingBegin == "14:00") echo "selected";?>>14:00</option>
                <option value="15:00" <?php if ($timeForRingBegin == "15:00") echo "selected";?>>15:00</option>
                <option value="16:00" <?php if ($timeForRingBegin == "16:00") echo "selected";?>>16:00</option>
                <option value="17:00" <?php if ($timeForRingBegin == "17:00") echo "selected";?>>17:00</option>
                <option value="18:00" <?php if ($timeForRingBegin == "18:00") echo "selected";?>>18:00</option>
                <option value="19:00" <?php if ($timeForRingBegin == "19:00") echo "selected";?>>19:00</option>
                <option value="20:00" <?php if ($timeForRingBegin == "20:00") echo "selected";?>>20:00</option>
                <option value="21:00" <?php if ($timeForRingBegin == "21:00") echo "selected";?>>21:00</option>
                <option value="22:00" <?php if ($timeForRingBegin == "22:00") echo "selected";?>>22:00</option>
                <option value="23:00" <?php if ($timeForRingBegin == "23:00") echo "selected";?>>23:00</option>
                <option value="24:00" <?php if ($timeForRingBegin == "24:00") echo "selected";?>>24:00</option>
            </select>
            до
            <select name="timeForRingEnd">
                <option value="0" <?php if ($timeForRingEnd == "0") echo "selected";?>></option>
                <option value="6:00" <?php if ($timeForRingEnd == "6:00") echo "selected";?>>6:00</option>
                <option value="7:00" <?php if ($timeForRingEnd == "7:00") echo "selected";?>>7:00</option>
                <option value="8:00" <?php if ($timeForRingEnd == "8:00") echo "selected";?>>8:00</option>
                <option value="9:00" <?php if ($timeForRingEnd == "9:00") echo "selected";?>>9:00</option>
                <option value="10:00" <?php if ($timeForRingEnd == "10:00") echo "selected";?>>10:00</option>
                <option value="11:00" <?php if ($timeForRingEnd == "11:00") echo "selected";?>>11:00</option>
                <option value="12:00" <?php if ($timeForRingEnd == "12:00") echo "selected";?>>12:00</option>
                <option value="13:00" <?php if ($timeForRingEnd == "13:00") echo "selected";?>>13:00</option>
                <option value="14:00" <?php if ($timeForRingEnd == "14:00") echo "selected";?>>14:00</option>
                <option value="15:00" <?php if ($timeForRingEnd == "15:00") echo "selected";?>>15:00</option>
                <option value="16:00" <?php if ($timeForRingEnd == "16:00") echo "selected";?>>16:00</option>
                <option value="17:00" <?php if ($timeForRingEnd == "17:00") echo "selected";?>>17:00</option>
                <option value="18:00" <?php if ($timeForRingEnd == "18:00") echo "selected";?>>18:00</option>
                <option value="19:00" <?php if ($timeForRingEnd == "19:00") echo "selected";?>>19:00</option>
                <option value="20:00" <?php if ($timeForRingEnd == "20:00") echo "selected";?>>20:00</option>
                <option value="21:00" <?php if ($timeForRingEnd == "21:00") echo "selected";?>>21:00</option>
                <option value="22:00" <?php if ($timeForRingEnd == "22:00") echo "selected";?>>22:00</option>
                <option value="23:00" <?php if ($timeForRingEnd == "23:00") echo "selected";?>>23:00</option>
                <option value="24:00" <?php if ($timeForRingEnd == "24:00") echo "selected";?>>24:00</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Как часто собственник проверяет недвижимость:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <select name="checking">
                <option value="0"  <?php if ($checking == "0") echo "selected";?>></option>
                <option
                    value="Никогда (проживает в другом городе)" <?php if ($checking == "Никогда (проживает в другом городе)") echo "selected";?>>
                    Никогда (проживает в другом городе)
                </option>
                <option
                    value="1 раз в месяц (при получении оплаты)" <?php if ($checking == "1 раз в месяц (при получении оплаты)") echo "selected";?>>
                    1 раз в месяц (при получении оплаты)
                </option>
                <option
                    value="Периодически (чаще 1 раза в месяц)" <?php if ($checking == "Периодически (чаще 1 раза в месяц)") echo "selected";?>>
                    Периодически (чаще 1 раза в месяц)
                </option>
                <option
                    value="Постоянно (проживает на этой же площади)" <?php if ($checking == "Постоянно (проживает на этой же площади)") echo "selected";?>>
                    Постоянно (проживает на этой же площади)
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         title="Какую ответственность за состояние и ремонт объекта берет на себя собственник">
        <div class="objectDescriptionItemLabel">
            Ответственность за состояние и ремонт недвижимости:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="responsibility" maxlength="2000" rows="7" cols="43"><?php echo $responsibility;?></textarea>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Дополнительный комментарий:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="comment" maxlength="2000" rows="7" cols="43"><?php echo $comment;?></textarea>
        </div>
    </div>
</div>

<div class="bottomButton">
    <a href="personal.php?tabsId=3" style="margin-right: 10px;">Отмена</a>
    <button type="submit" name="saveAdvertButton" id="saveAdvertButton" class="button">
        Сохранить
    </button>
</div>
<div class="clearBoth"></div>

</form>

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
<script src="js/newOrEditAdvert.js"></script>
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
