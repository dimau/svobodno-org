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
 * Присваиваем всем переменным значения по умолчанию
 ************************************************************************************/
$typeOfObject = "0";
$dateOfEntry = "";
$termOfLease = "0";
$dateOfCheckOut = "";
$amountOfRooms = "0";
$adjacentRooms = "0";
$amountOfAdjacentRooms = "0";
$typeOfBathrooms = "0";
$typeOfBalcony = "0";
$balconyGlazed = "0";
$roomSpace = "";
$totalArea = "";
$livingSpace = "";
$kitchenSpace = "";
$floor = "";
$totalAmountFloor = "";
$numberOfFloor = "";
$concierge = "0";
$intercom = "0";
$parking = "0";
$city = "Екатеринбург";
$district = "0";
$coordX = "";
$coordY = "";
$address = "";
$apartmentNumber = "";
$subwayStation = "0";
$distanceToMetroStation = "";
$currency = "0";
$costOfRenting = "";
$utilities = "0";
$costInSummer = "";
$costInWinter = "";
$electricPower = "0";
$bail = "0";
$bailCost = "";
$prepayment = "0";
$compensationMoney = "";
$compensationPercent = "";
$repair = "0";
$furnish = "0";
$windows = "0";
$internet = "0";
$telephoneLine = "0";
$cableTV = "0";
$furnitureInLivingArea = array();
$furnitureInLivingAreaExtra = "";
$furnitureInKitchen = array();
$furnitureInKitchenExtra = "";
$appliances = array();
$appliancesExtra = "";
$sexOfTenant = array();
$relations = array();
$children = "0";
$animals = "0";
$contactTelephonNumber = "";
$timeForRingBegin = "0";
$timeForRingEnd = "0";
$checking = "0";
$responsibility = "";
$comment = "";
$fileUploadId = generateCode(7);

// Готовим массив со списком районов в городе пользователя
$rezDistricts = mysql_query("SELECT * FROM districts WHERE city = '" . "Екатеринбург" . "'");
for ($i = 0; $i < mysql_num_rows($rezDistricts); $i++) {
    $rowDistricts = mysql_fetch_assoc($rezDistricts);
    $allDistrictsInCity[$rowDistricts['id']] = $rowDistricts['name'];
}

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
    if (isset($_POST['furnitureInLivingArea'])) $furnitureInLivingArea = $_POST['furnitureInLivingArea'];
    if (isset($_POST['furnitureInLivingAreaExtra'])) $furnitureInLivingAreaExtra = htmlspecialchars($_POST['furnitureInLivingAreaExtra']);
    if (isset($_POST['furnitureInKitchen'])) $furnitureInKitchen = $_POST['furnitureInKitchen'];
    if (isset($_POST['furnitureInKitchenExtra'])) $furnitureInKitchenExtra = htmlspecialchars($_POST['furnitureInKitchenExtra']);
    if (isset($_POST['appliances'])) $appliances = $_POST['appliances'];
    if (isset($_POST['appliancesExtra'])) $appliancesExtra = htmlspecialchars($_POST['appliancesExtra']);
    if (isset($_POST['sexOfTenant'])) $sexOfTenant = $_POST['sexOfTenant'];
    if (isset($_POST['relations'])) $relations = $_POST['relations'];
    if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']);
    if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']);
    if (isset($_POST['contactTelephonNumber'])) $contactTelephonNumber = htmlspecialchars($_POST['contactTelephonNumber']);
    if (isset($_POST['timeForRingBegin'])) $timeForRingBegin = htmlspecialchars($_POST['timeForRingBegin']);
    if (isset($_POST['timeForRingEnd'])) $timeForRingEnd = htmlspecialchars($_POST['timeForRingEnd']);
    if (isset($_POST['checking'])) $checking = htmlspecialchars($_POST['checking']);
    if (isset($_POST['responsibility'])) $responsibility = htmlspecialchars($_POST['responsibility']);
    if (isset($_POST['comment'])) $comment = htmlspecialchars($_POST['comment']);
    $fileUploadId = $_POST['fileUploadId'];

    // Проверяем корректность данных нового объявления. Функции isAdvertCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
    $errors = isAdvertCorrect("newAdvert");
    if (count($errors) == 0) $correct = true; else $correct = false; // Считаем ошибки, если 0, то можно будет записать данные в БД

    // Если данные, указанные пользователем, корректны, запишем объявление в базу данных
    if ($correct) {
        // Корректируем даты для того, чтобы сделать их пригодными для сохранения в базу данных
        $dateOfEntryForDB = dateFromViewToDB($dateOfEntry);
        $dateOfCheckOutForDB = dateFromViewToDB($dateOfCheckOut);

        // Для хранения массивов в БД, их необходимо сериализовать
        $furnitureInLivingAreaSerialized = serialize($furnitureInLivingArea);
        $furnitureInKitchenSerialized = serialize($furnitureInKitchen);
        $appliancesSerialized = serialize($appliances);
        $sexOfTenantSerialized = serialize($sexOfTenant);
        $relationsSerialized = serialize($relations);

        $tm = time();
        $last_act = $tm; // время последнего редактирования объявления
        $reg_date = $tm; // время регистрации ("рождения") объявления

        if (mysql_query("INSERT INTO property SET
                            userId='" . $userId ."',
                            typeOfObject='" . $typeOfObject ."',
                            dateOfEntry='" . $dateOfEntryForDB ."',
                            termOfLease='" . $termOfLease ."',
                            dateOfCheckOut='" . $dateOfCheckOutForDB ."',
                            amountOfRooms='" . $amountOfRooms ."',
                            adjacentRooms='" . $adjacentRooms ."',
                            amountOfAdjacentRooms='" . $amountOfAdjacentRooms ."',
                            typeOfBathrooms='" . $typeOfBathrooms ."',
                            typeOfBalcony='" . $typeOfBalcony ."',
                            balconyGlazed='" . $balconyGlazed ."',
                            roomSpace='" . $roomSpace ."',
                            totalArea='" . $totalArea ."',
                            livingSpace='" . $livingSpace ."',
                            kitchenSpace='" . $kitchenSpace ."',
                            floor='" . $floor ."',
                            totalAmountFloor='" . $totalAmountFloor ."',
                            numberOfFloor='" . $numberOfFloor ."',
                            concierge='" . $concierge ."',
                            intercom='" . $intercom ."',
                            parking='" . $parking ."',
                            city='" . $city ."',
                            district='" . $district ."',
                            coordX='" . $coordX ."',
                            coordY='" . $coordY ."',
                            address='" . $address ."',
                            apartmentNumber='" . $apartmentNumber ."',
                            subwayStation='" . $subwayStation ."',
                            distanceToMetroStation='" . $distanceToMetroStation ."',
                            currency='" . $currency ."',
                            costOfRenting='" . $costOfRenting ."',
                            utilities='" . $utilities ."',
                            costInSummer='" . $costInSummer ."',
                            costInWinter='" . $costInWinter ."',
                            electricPower='" . $electricPower ."',
                            bail='" . $bail ."',
                            bailCost='" . $bailCost ."',
                            prepayment='" . $prepayment ."',
                            compensationMoney='" . $compensationMoney ."',
                            compensationPercent='" . $compensationPercent ."',
                            repair='" . $repair ."',
                            furnish='" . $furnish ."',
                            windows='" . $windows ."',
                            internet='" . $internet ."',
                            telephoneLine='" . $telephoneLine ."',
                            cableTV='" . $cableTV ."',
                            furnitureInLivingArea='" . $furnitureInLivingAreaSerialized ."',
                            furnitureInLivingAreaExtra='" . $furnitureInLivingAreaExtra ."',
                            furnitureInKitchen='" . $furnitureInKitchenSerialized ."',
                            furnitureInKitchenExtra='" . $furnitureInKitchenExtra ."',
                            appliances='" . $appliancesSerialized ."',
                            appliancesExtra='" . $appliancesExtra ."',
                            sexOfTenant='" . $sexOfTenantSerialized ."',
                            relations='" . $relationsSerialized ."',
                            children='" . $children ."',
                            animals='" . $animals ."',
                            contactTelephonNumber='" . $contactTelephonNumber ."',
                            timeForRingBegin='" . $timeForRingBegin ."',
                            timeForRingEnd='" . $timeForRingEnd ."',
                            checking='" . $checking ."',
                            responsibility='" . $responsibility ."',
                            comment='" . $comment ."',
                            last_act='" . $last_act ."',
                            reg_date='" . $reg_date ."'"))
        {
            /******* Переносим информацию о фотографиях объекта недвижимости в таблицу для постоянного хранения *******/
            // Узнаем id объявления - необходимо при сохранении информации о фотке в постоянную базу
            $rezId = mysql_query("SELECT id FROM property WHERE address='".$address."' AND coordX='".$coordX."' AND coordY='".$coordY."' AND apartmentNumber='".$apartmentNumber."'");
            $rowId = mysql_fetch_assoc($rezId);
            // Получим информацию о всех фотках, соответствующих текущему fileUploadId
            $rezTempFotos = mysql_query("SELECT id, filename, extension, filesizeMb FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");
            for ($i = 0; $i < mysql_num_rows($rezTempFotos); $i++) {
                $rowTempFotos = mysql_fetch_assoc($rezTempFotos);
                mysql_query("INSERT INTO propertyFotos (id, filename, extension, filesizeMb, propertyId) VALUES ('" . $rowTempFotos['id'] . "','" . $rowTempFotos['filename'] . "','" . $rowTempFotos['extension'] . "','" . $rowTempFotos['filesizeMb'] . "','" . $rowId['id'] . "')"); // Переносим информацию о фотографиях на постоянное хранение
            }
            // Удаляем записи о фотках в таблице для временного хранения данных
            mysql_query("DELETE FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");

            // Пересылаем пользователя в личный кабинет на вкладку Мои объявления
            header('Location: personal.php?tabsId=3');
        }
        else {
            $correct = false;
            $errors[] = 'Не прошел запрос к БД. К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку';
            // Сохранении данных в БД не прошло - объявление не сохранено
        }
    }
}

// В будущем необходимо будет проверять личные данные пользователя на полноту для его работы в качестве собственника, если у него typeOwner != "true"
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Новое объявление</title>
    <meta name="description" content="Новое объявление">

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
    Новое объявление
</div>

<form method="post" name="newAdvert" class="advertDescriptionEdit">
<div class="advertDescriptionChapter" id="mainParametersChapter">
    <div class="advertDescriptionChapterHeader">
        Описание объекта
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Тип объекта:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfObject" id="typeOfObject">
                <option value="0" <?php if ($typeOfObject == "0") echo "selected";?>></option>
                <option value="flat" <?php if ($typeOfObject == "flat") echo "selected";?>>квартира</option>
                <option value="room" <?php if ($typeOfObject == "room") echo "selected";?>>комната</option>
                <option value="house" <?php if ($typeOfObject == "house") echo "selected";?>>дом, коттедж</option>
                <option value="townhouse" <?php if ($typeOfObject == "townhouse") echo "selected";?>>таунхаус</option>
                <option value="dacha" <?php if ($typeOfObject == "dacha") echo "selected";?>>дача</option>
                <option value="garage" <?php if ($typeOfObject == "garage") echo "selected";?>>гараж</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            С какого числа можно въезжать:
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfEntry" type="text" id="datepicker1" size="15" placeholder="дд.мм.гггг" <?php echo "value='$dateOfEntry'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            На какой срок сдается:
        </div>
        <div class="objectDescriptionBody">
            <select name="termOfLease" id="termOfLease">
                <option value="0" <?php if ($termOfLease == "0") echo "selected";?>></option>
                <option value="long" <?php if ($termOfLease == "long") echo "selected";?>>длительный срок (от года)</option>
                <option value="little" <?php if ($termOfLease == "little") echo "selected";?>>несколько месяцев (до года)</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="termOfLease_0 termOfLease_long">
        <div class="objectDescriptionItemLabel">
            Крайний срок выезда арендатора(ов):
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfCheckOut" type="text" id="datepicker2" size="15" placeholder="дд.мм.гггг" <?php echo "value='$dateOfCheckOut'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
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
                <option value="6" <?php if ($amountOfRooms == "6") echo "selected";?>>6 и более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="amountOfRooms_0 amountOfRooms_1">
        <div class="objectDescriptionItemLabel">
            Комнаты смежные:
        </div>
        <div class="objectDescriptionBody">
            <select name="adjacentRooms" id="adjacentRooms">
                <option value="0" <?php if ($adjacentRooms == "0") echo "selected";?>></option>
                <option value="yes" <?php if ($adjacentRooms == "yes") echo "selected";?>>да</option>
                <option value="no" <?php if ($adjacentRooms == "no") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_room typeOfObject_garage adjacentRooms_0 adjacentRooms_no amountOfRooms_0 amountOfRooms_1 amountOfRooms_2">
        <div class="objectDescriptionItemLabel">
            Количество смежных комнат в квартире, доме:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfAdjacentRooms">
                <option value="0" <?php if ($amountOfAdjacentRooms == "0") echo "selected";?>></option>
                <option value="2" <?php if ($amountOfAdjacentRooms == "2") echo "selected";?>>2</option>
                <option value="3" <?php if ($amountOfAdjacentRooms == "3") echo "selected";?>>3</option>
                <option value="4" <?php if ($amountOfAdjacentRooms == "4") echo "selected";?>>4</option>
                <option value="5" <?php if ($amountOfAdjacentRooms == "5") echo "selected";?>>5</option>
                <option value="6" <?php if ($amountOfAdjacentRooms == "6") echo "selected";?>>6 и более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Санузел:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBathrooms">
                <option value="0" <?php if ($typeOfBathrooms == "0") echo "selected";?>></option>
                <option value="separate" <?php if ($typeOfBathrooms == "separate") echo "selected";?>>раздельный</option>
                <option value="combined" <?php if ($typeOfBathrooms == "combined") echo "selected";?>>совмещенный</option>
                <option value="2" <?php if ($typeOfBathrooms == "2") echo "selected";?>>2</option>
                <option value="3" <?php if ($typeOfBathrooms == "3") echo "selected";?>>3</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Балкон/лоджия:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBalcony" id="typeOfBalcony">
                <option value="0" <?php if ($typeOfBalcony == "0") echo "selected";?>></option>
                <option value="not" <?php if ($typeOfBalcony == "not") echo "selected";?>>нет</option>
                <option value="balcony" <?php if ($typeOfBalcony == "balcony") echo "selected";?>>балкон</option>
                <option value="loggia" <?php if ($typeOfBalcony == "loggia") echo "selected";?>>лоджия</option>
                <option value="oriel" <?php if ($typeOfBalcony == "oriel") echo "selected";?>>эркер</option>
                <option value="balconyAndLoggia" <?php if ($typeOfBalcony == "balconyAndLoggia") echo "selected";?>>балкон и лоджия</option>
                <option value="balconyAndOriel" <?php if ($typeOfBalcony == "balconyAndOriel") echo "selected";?>>балкон и эркер</option>
                <option value="2balcony" <?php if ($typeOfBalcony == "2balcony") echo "selected";?>>2 балкона и более</option>
                <option value="2loggia" <?php if ($typeOfBalcony == "2loggia") echo "selected";?>>2 лоджии и более</option>
                <option value="2oriel" <?php if ($typeOfBalcony == "2oriel") echo "selected";?>>2 эркера и более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfBalcony_0 typeOfBalcony_not typeOfBalcony_oriel typeOfBalcony_2oriel">
        <div class="objectDescriptionItemLabel">
            Остекление балкона/лоджии:
        </div>
        <div class="objectDescriptionBody">
            <select name="balconyGlazed">
                <option value="0" <?php if ($balconyGlazed == "0") echo "selected";?>></option>
                <option value="yes" <?php if ($balconyGlazed == "yes") echo "selected";?>>да</option>
                <option value="no" <?php if ($balconyGlazed == "no") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_flat typeOfObject_house typeOfObject_townhouse typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Площадь комнаты:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="roomSpace" <?php echo "value='$roomSpace'";?>>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_room">
        <div class="objectDescriptionItemLabel">
            Площадь общая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="totalArea" <?php echo "value='$totalArea'";?>>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_room typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Площадь жилая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="livingSpace" <?php echo "value='$livingSpace'";?>>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Площадь кухни:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="kitchenSpace" <?php echo "value='$kitchenSpace'";?>>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_house typeOfObject_townhouse typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Этаж:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="3" name="floor" <?php echo "value='$floor'";?>>
            из
            <input type="text" size="3" name="totalAmountFloor" <?php echo "value='$totalAmountFloor'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_flat typeOfObject_room typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Этажность дома:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="3" name="numberOfFloor" <?php echo "value='$numberOfFloor'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_house typeOfObject_townhouse typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Консьерж:
        </div>
        <div class="objectDescriptionBody">
            <select name="concierge">
                <option value="0" <?php if ($concierge == "0") echo "selected";?>></option>
                <option value="yes" <?php if ($concierge == "yes") echo "selected";?>>есть</option>
                <option value="no" <?php if ($concierge == "no") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Домофон:
        </div>
        <div class="objectDescriptionBody">
            <select name="intercom">
                <option value="0" <?php if ($intercom == "0") echo "selected";?>></option>
                <option value="yes" <?php if ($intercom == "yes") echo "selected";?>>есть</option>
                <option value="no" <?php if ($intercom == "no") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Парковка во дворе:
        </div>
        <div class="objectDescriptionBody">
            <select name="parking">
                <option value="0" <?php if ($parking == "0") echo "selected";?>></option>
                <option value="guarded" <?php if ($parking == "guarded") echo "selected";?>>охраняемая</option>
                <option value="unguarded" <?php if ($parking == "unguarded") echo "selected";?>>неохраняемая</option>
                <option value="underground" <?php if ($parking == "underground") echo "selected";?>>подземная</option>
                <option value="no" <?php if ($parking == "no") echo "selected";?>>отсутствует</option>
            </select>
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
            <select name="district">
                <option value="0"></option>
            <?php
            if (isset($allDistrictsInCity)) {
                foreach ($allDistrictsInCity as $key => $value) { // Для каждого идентификатора района и названия формируем пункт селекта
                    echo "<option value='" . $key . "'";
                    if ($key == $district) echo "selected";
                    echo ">" . $value . "</option>";
                }
            }
            ?>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel" style="line-height: 2em;">
            Улица и номер дома:
        </div>
        <div class="objectDescriptionBody" style="min-width: 470px">
            <input type="hidden" name="coordX" id="coordX" <?php echo "value='$coordX'";?>>
            <input type="hidden" name="coordY" id="coordY" <?php echo "value='$coordY'";?>>
            <table class="tableForMap">
                <tbody>
                <tr>
                    <td>
                        <input type="text" name="address" id="addressTextBox" size="30" <?php echo "value='$address'";?>>
                        <button id="checkAddressButton">Проверить адрес</button>
                    </td>
                </tr>
                <tr>
                    <td><!-- Карта Яндекса -->
                        <div id="mapForNewAdvert" style="width: 100%; height: 400px; margin-top: 15px;"></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_house typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Номер квартиры:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="apartmentNumber" size="7" maxlength="20" <?php echo "value='$apartmentNumber'";?>>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_dacha typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Станция метро рядом:
        </div>
        <div class="objectDescriptionBody">
            <select name="subwayStation" id="subwayStation">
                <option value="0" <?php if ($subwayStation == "0") echo "selected";?>></option>
                <option value="not" <?php if ($subwayStation == "not") echo "selected";?>>Нет</option>
                <option value="ProspectCosmonauts" <?php if ($subwayStation == "ProspectCosmonauts") echo "selected";?>>Проспект Космонавтов</option>
                <option value="Uralmash" <?php if ($subwayStation == "Uralmash") echo "selected";?>>Уралмаш</option>
                <option value="Mashinostroiteley" <?php if ($subwayStation == "Mashinostroiteley") echo "selected";?>>Машиностроителей</option>
                <option value="Uralskaya" <?php if ($subwayStation == "Uralskaya") echo "selected";?>>Уральская</option>
                <option value="Dinamo" <?php if ($subwayStation == "Dinamo") echo "selected";?>>Динамо</option>
                <option value="Ploshchad1905g" <?php if ($subwayStation == "Ploshchad1905g") echo "selected";?>>Площадь 1905 г.</option>
                <option value="Geologicheskaya" <?php if ($subwayStation == "Geologicheskaya") echo "selected";?>>Геологическая</option>
                <option value="Chkalovskaya" <?php if ($subwayStation == "Chkalovskaya") echo "selected";?>>Чкаловская</option>
                <option value="Botanicheskaya" <?php if ($subwayStation == "Botanicheskaya") echo "selected";?>>Ботаническая</option>
            </select>
            <span notavailability="subwayStation_0 subwayStation_not">
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
                <option value="rubl" <?php if ($currency == "rubl") echo "selected";?>>рубль</option>
                <option value="dollar" <?php if ($currency == "dollar") echo "selected";?>>доллар США</option>
                <option value="euro" <?php if ($currency == "euro") echo "selected";?>>евро</option>
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
                <option value="yes" <?php if ($utilities == "yes") echo "selected";?>>да</option>
                <option value="no" <?php if ($utilities == "no") echo "selected";?>>нет</option>
            </select>
            <span notavailability="utilities_0 utilities_no">
            Летом
            <input type="text" name="costInSummer" size="7" <?php echo "value='$costInSummer'";?>>
            <span class="currency"></span> Зимой
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
                <option value="yes" <?php if ($electricPower == "yes") echo "selected";?>>да</option>
                <option value="no" <?php if ($electricPower == "no") echo "selected";?>>нет</option>
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
                <option value="yes" <?php if ($bail == "yes") echo "selected";?>>есть</option>
                <option value="no" <?php if ($bail == "no") echo "selected";?>>нет</option>
            </select>
            <span notavailability="bail_0 bail_no">
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
                <option value="no" <?php if ($prepayment == "no") echo "selected";?>>нет</option>
                <option value="1" <?php if ($prepayment == "1") echo "selected";?>>1 месяц</option>
                <option value="2" <?php if ($prepayment == "2") echo "selected";?>>2 месяца</option>
                <option value="3" <?php if ($prepayment == "3") echo "selected";?>>3 месяца</option>
                <option value="4" <?php if ($prepayment == "4") echo "selected";?>>4 месяца</option>
                <option value="5" <?php if ($prepayment == "5") echo "selected";?>>5 месяцев</option>
                <option value="6" <?php if ($prepayment == "6") echo "selected";?>>6 месяцев</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" title="Предназначена для компенсации затрат собственника на публикацию объявления и поиск арендатора">
        <div class="objectDescriptionItemLabel">
            Единоразовая комиссия собственника:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="compensationMoney" id="compensationMoney" <?php echo "value='$compensationMoney'";?>>
            <span class="currency"></span> или <input type="text" size="7" name="compensationPercent" id="compensationPercent" <?php echo "value='$compensationPercent'";?>> % от стоимости аренды
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="currentStatus">
    <div class="advertDescriptionChapterHeader">
        Текущее состояние
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Ремонт:
        </div>
        <div class="objectDescriptionBody">
            <select name="repair">
                <option value="0" <?php if ($repair == "0") echo "selected";?>></option>
                <option value="no" <?php if ($repair == "no") echo "selected";?>>не выполнялся (новый дом)</option>
                <option value="just" <?php if ($repair == "just") echo "selected";?>>сделан только что</option>
                <option value="less1year" <?php if ($repair == "less1year") echo "selected";?>>меньше 1 года назад</option>
                <option value="over1year" <?php if ($repair == "over1year") echo "selected";?>>больше года назад</option>
                <option value="long" <?php if ($repair == "long") echo "selected";?>>выполнялся давно</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Отделка:
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <select name="furnish">
                <option value="0" <?php if ($furnish == "0") echo "selected";?>></option>
                <option value="euro" <?php if ($furnish == "euro") echo "selected";?>>евростандарт</option>
                <option value="fresh" <?php if ($furnish == "fresh") echo "selected";?>>свежая (новые обои, побелка потолков)</option>
                <option value="grandma" <?php if ($furnish == "grandma") echo "selected";?>>бабушкин вариант</option>
                <option value="needsUpdated" <?php if ($furnish == "needsUpdated") echo "selected";?>>требует обновления</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Окна:
        </div>
        <div class="objectDescriptionBody">
            <select name="windows">
                <option value="0" <?php if ($windows == "0") echo "selected";?>></option>
                <option value="wooden" <?php if ($windows == "wooden") echo "selected";?>>деревянные</option>
                <option value="plastic" <?php if ($windows == "plastic") echo "selected";?>>пластиковые</option>
                <option value="otherwise" <?php if ($windows == "otherwise") echo "selected";?>>иное</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="communication">
    <div class="advertDescriptionChapterHeader">
        Связь
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Интернет:
        </div>
        <div class="objectDescriptionBody">
            <select name="internet">
                <option value="0" <?php if ($internet == "0") echo "selected";?>></option>
                <option value="nono" <?php if ($internet == "nono") echo "selected";?>>не проведен, нельзя провести</option>
                <option value="no" <?php if ($internet == "no") echo "selected";?>>не проведен, можно провести</option>
                <option value="yes" <?php if ($internet == "yes") echo "selected";?>>проведен, можно использовать</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Телефон:
        </div>
        <div class="objectDescriptionBody">
            <select name="telephoneLine">
                <option value="0" <?php if ($telephoneLine == "0") echo "selected";?>></option>
                <option value="nono" <?php if ($telephoneLine == "nono") echo "selected";?>>не проведен, нельзя провести</option>
                <option value="no" <?php if ($telephoneLine == "no") echo "selected";?>>не проведен, можно провести</option>
                <option value="yes" <?php if ($telephoneLine == "yes") echo "selected";?>>проведен, можно использовать</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Кабельное ТВ:
        </div>
        <div class="objectDescriptionBody">
            <select name="cableTV">
                <option value="0" <?php if ($cableTV == "0") echo "selected";?>></option>
                <option value="nono" <?php if ($cableTV == "nono") echo "selected";?>>не проведено, нельзя провести</option>
                <option value="no" <?php if ($cableTV == "no") echo "selected";?>>не проведено, можно провести</option>
                <option value="yes" <?php if ($cableTV == "yes") echo "selected";?>>проведено, можно использовать</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="furniture">
<div class="advertDescriptionChapterHeader">
    Мебель и бытовая техника
</div>
<div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
    <div class="objectDescriptionItemLabel">
        Мебель в жилой зоне:
    </div>
    <div class="objectDescriptionBody">
        <ul>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="sofa"
                <?php foreach ($furnitureInLivingArea as $value) {
                        if ($value == "sofa") {echo "checked"; break;}
                      }
                ?>> диван раскладной
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="sofaNeraskladnoy" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "sofaNeraskladnoy") {echo "checked"; break;}
            }
                ?>> диван нераскладной
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="singleBed" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "singleBed") {echo "checked"; break;}
            }
                ?>> кровать одноместная
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="doubleBed" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "doubleBed") {echo "checked"; break;}
            }
                ?>> кровать двухместная
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="babyBed" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "babyBed") {echo "checked"; break;}
            }
                ?>> кровать детская
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="writingDesk" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "writingDesk") {echo "checked"; break;}
            }
                ?>> стол письменный
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="computerTable" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "computerTable") {echo "checked"; break;}
            }
                ?>> стол компьютерный
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="coffeeTable" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "coffeeTable") {echo "checked"; break;}
            }
                ?>> стол журнальный
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="foldingTable" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "foldingTable") {echo "checked"; break;}
            }
                ?>> стол раскладной
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="foldingChair" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "foldingChair") {echo "checked"; break;}
            }
                ?>> кресло раскладное
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="nonFoldingChair" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "nonFoldingChair") {echo "checked"; break;}
            }
                ?>> кресло нераскладное
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="chairsAndStools" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "chairsAndStools") {echo "checked"; break;}
            }
                ?>> стулья и табуретки
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="wall" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "wall") {echo "checked"; break;}
            }
                ?>> стенка
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="wardrobe" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "wardrobe") {echo "checked"; break;}
            }
                ?>> шкаф для одежды
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="shkafKupe" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "shkafKupe") {echo "checked"; break;}
            }
                ?>> шкаф-купе
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="komod" <?php foreach ($furnitureInLivingArea as $value) {
                if ($value == "komod") {echo "checked"; break;}
            }
                ?>> комод
        </li>
        <li>
            <input type="text" name="furnitureInLivingAreaExtra" maxlength="254" <?php echo "value='$furnitureInLivingAreaExtra'";?>>
        </li>
            </ul>
    </div>
</div>
<div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
    <div class="objectDescriptionItemLabel">
        Мебель на кухне:
    </div>
    <div class="objectDescriptionBody">
        <ul>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="diningTable" <?php foreach ($furnitureInKitchen as $value) {
                if ($value == "diningTable") {echo "checked"; break;}
            }
                ?>> стол обеденный
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="chairsAndStools" <?php foreach ($furnitureInKitchen as $value) {
                if ($value == "chairsAndStools") {echo "checked"; break;}
            }
                ?>> стулья, табуретки
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="sofa" <?php foreach ($furnitureInKitchen as $value) {
                if ($value == "sofa") {echo "checked"; break;}
            }
                ?>> диван
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="kitchenSet" <?php foreach ($furnitureInKitchen as $value) {
                if ($value == "kitchenSet") {echo "checked"; break;}
            }
                ?>> кухонный гарнитур
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="mountedCabinets" <?php foreach ($furnitureInKitchen as $value) {
                if ($value == "mountedCabinets") {echo "checked"; break;}
            }
                ?>> шкафчики навесные
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="lockersFloor" <?php foreach ($furnitureInKitchen as $value) {
                if ($value == "lockersFloor") {echo "checked"; break;}
            }
                ?>> шкафчики напольные
        </li>
        <li>
            <input type="text" name="furnitureInKitchenExtra" maxlength="254" <?php echo "value='$furnitureInKitchenExtra'";?>>
        </li>
            </ul>
    </div>
</div>
<div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
    <div class="objectDescriptionItemLabel">
        Бытовая техника:
    </div>
    <div class="objectDescriptionBody">
        <ul>
        <li>
            <input type="checkbox" name="appliances[]" value="refrigerator" <?php foreach ($appliances as $value) {
                if ($value == "refrigerator") {echo "checked"; break;}
            }
                ?>> холодильник
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="microwave" <?php foreach ($appliances as $value) {
                if ($value == "microwave") {echo "checked"; break;}
            }
                ?>> микроволновая печь
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="televisor" <?php foreach ($appliances as $value) {
                if ($value == "televisor") {echo "checked"; break;}
            }
                ?>> телевизор
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="washingMachineAutomatic" <?php foreach ($appliances as $value) {
                if ($value == "washingMachineAutomatic") {echo "checked"; break;}
            }
                ?>> стиральная машина (автомат)
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="washingMachineNonAutomatic" <?php foreach ($appliances as $value) {
                if ($value == "washingMachineNonAutomatic") {echo "checked"; break;}
            }
                ?>> стиральная машина (не автомат)
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="waterHeater" <?php foreach ($appliances as $value) {
                if ($value == "waterHeater") {echo "checked"; break;}
            }
                ?>> нагреватель воды
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="vacuumCleaner" <?php foreach ($appliances as $value) {
                if ($value == "vacuumCleaner") {echo "checked"; break;}
            }
                ?>> пылесос
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="airConditioning" <?php foreach ($appliances as $value) {
                if ($value == "airConditioning") {echo "checked"; break;}
            }
                ?>> кондиционер
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="alarm" <?php foreach ($appliances as $value) {
                if ($value == "alarm") {echo "checked"; break;}
            }
                ?>> охранная сигнализация
        </li>
        <li>
            <input type="text" name="appliancesExtra" maxlength="254" <?php echo "value='$appliancesExtra'";?>>
        </li>
            </ul>
    </div>
</div>
</div>

<div class="advertDescriptionChapter" id="requirementsForTenant">
    <div class="advertDescriptionChapterHeader">
        Требования к арендатору
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Пол:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="sexOfTenant[]" value="man" <?php foreach ($sexOfTenant as $value) {
                if ($value == "man") {echo "checked"; break;}
            }
                ?>>
            мужчина
            <br>
            <input type="checkbox" name="sexOfTenant[]" value="woman" <?php foreach ($sexOfTenant as $value) {
                if ($value == "woman") {echo "checked"; break;}
            }
                ?>>
            женщина
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Отношения между арендаторами:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="relations[]" value="family" <?php foreach ($relations as $value) {
                if ($value == "family") {echo "checked"; break;}
            }
                ?>>
            семейная пара
            <br>
            <input type="checkbox" name="relations[]" value="notFamily" <?php foreach ($relations as $value) {
                if ($value == "notFamily") {echo "checked"; break;}
            }
                ?>>
            несемейная пара
            <br>
            <input type="checkbox" name="relations[]" value="alone" <?php foreach ($relations as $value) {
                if ($value == "alone") {echo "checked"; break;}
            }
                ?>>
            один человек
            <br>
            <input type="checkbox" name="relations[]" value="group" <?php foreach ($relations as $value) {
                if ($value == "group") {echo "checked"; break;}
            }
                ?>>
            группа людей
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Дети:
        </div>
        <div class="objectDescriptionBody">
            <select name="children">
                <option value="0" <?php if ($children == "0") echo "selected";?>></option>
                <option value="any" <?php if ($children == "any") echo "selected";?>>не имеет значения</option>
                <option value="older4" <?php if ($children == "older4") echo "selected";?>>с детьми старше 4-х лет</option>
                <option value="without" <?php if ($children == "without") echo "selected";?>>только без детей</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0 typeOfObject_garage">
        <div class="objectDescriptionItemLabel">
            Животные:
        </div>
        <div class="objectDescriptionBody">
            <select name="animals">
                <option value="0" <?php if ($animals == "0") echo "selected";?>></option>
                <option value="any" <?php if ($animals == "any") echo "selected";?>>не имеет значения</option>
                <option value="without" <?php if ($animals == "without") echo "selected";?>>только без животных</option>
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
                <option value="6" <?php if ($timeForRingBegin == "6") echo "selected";?>>6:00</option>
                <option value="7" <?php if ($timeForRingBegin == "7") echo "selected";?>>7:00</option>
                <option value="8" <?php if ($timeForRingBegin == "8") echo "selected";?>>8:00</option>
                <option value="9" <?php if ($timeForRingBegin == "9") echo "selected";?>>9:00</option>
                <option value="10" <?php if ($timeForRingBegin == "10") echo "selected";?>>10:00</option>
                <option value="11" <?php if ($timeForRingBegin == "11") echo "selected";?>>11:00</option>
                <option value="12" <?php if ($timeForRingBegin == "12") echo "selected";?>>12:00</option>
                <option value="13" <?php if ($timeForRingBegin == "13") echo "selected";?>>13:00</option>
                <option value="14" <?php if ($timeForRingBegin == "14") echo "selected";?>>14:00</option>
                <option value="15" <?php if ($timeForRingBegin == "15") echo "selected";?>>15:00</option>
                <option value="16" <?php if ($timeForRingBegin == "16") echo "selected";?>>16:00</option>
                <option value="17" <?php if ($timeForRingBegin == "17") echo "selected";?>>17:00</option>
                <option value="18" <?php if ($timeForRingBegin == "18") echo "selected";?>>18:00</option>
                <option value="19" <?php if ($timeForRingBegin == "19") echo "selected";?>>19:00</option>
                <option value="20" <?php if ($timeForRingBegin == "20") echo "selected";?>>20:00</option>
                <option value="21" <?php if ($timeForRingBegin == "21") echo "selected";?>>21:00</option>
                <option value="22" <?php if ($timeForRingBegin == "22") echo "selected";?>>22:00</option>
                <option value="23" <?php if ($timeForRingBegin == "23") echo "selected";?>>23:00</option>
                <option value="24" <?php if ($timeForRingBegin == "24") echo "selected";?>>24:00</option>
            </select>
            до
            <select name="timeForRingEnd">
                <option value="0" <?php if ($timeForRingEnd == "0") echo "selected";?>></option>
                <option value="6" <?php if ($timeForRingEnd == "6") echo "selected";?>>6:00</option>
                <option value="7" <?php if ($timeForRingEnd == "7") echo "selected";?>>7:00</option>
                <option value="8" <?php if ($timeForRingEnd == "8") echo "selected";?>>8:00</option>
                <option value="9" <?php if ($timeForRingEnd == "9") echo "selected";?>>9:00</option>
                <option value="10" <?php if ($timeForRingEnd == "10") echo "selected";?>>10:00</option>
                <option value="11" <?php if ($timeForRingEnd == "11") echo "selected";?>>11:00</option>
                <option value="12" <?php if ($timeForRingEnd == "12") echo "selected";?>>12:00</option>
                <option value="13" <?php if ($timeForRingEnd == "13") echo "selected";?>>13:00</option>
                <option value="14" <?php if ($timeForRingEnd == "14") echo "selected";?>>14:00</option>
                <option value="15" <?php if ($timeForRingEnd == "15") echo "selected";?>>15:00</option>
                <option value="16" <?php if ($timeForRingEnd == "16") echo "selected";?>>16:00</option>
                <option value="17" <?php if ($timeForRingEnd == "17") echo "selected";?>>17:00</option>
                <option value="18" <?php if ($timeForRingEnd == "18") echo "selected";?>>18:00</option>
                <option value="19" <?php if ($timeForRingEnd == "19") echo "selected";?>>19:00</option>
                <option value="20" <?php if ($timeForRingEnd == "20") echo "selected";?>>20:00</option>
                <option value="21" <?php if ($timeForRingEnd == "21") echo "selected";?>>21:00</option>
                <option value="22" <?php if ($timeForRingEnd == "22") echo "selected";?>>22:00</option>
                <option value="23" <?php if ($timeForRingEnd == "23") echo "selected";?>>23:00</option>
                <option value="24" <?php if ($timeForRingEnd == "24") echo "selected";?>>24:00</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Как часто собственник проверяет сдаваемую недвижимость:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <select name="checking">
                <option value="0"  <?php if ($checking == "0") echo "selected";?>></option>
                <option value="never" <?php if ($checking == "never") echo "selected";?>>Никогда (проживает в другом городе)</option>
                <option value="1" <?php if ($checking == "1") echo "selected";?>>1 раз в месяц (при получении оплаты)</option>
                <option value="more1" <?php if ($checking == "more1") echo "selected";?>>Периодически (чаще 1 раза в месяц)</option>
                <option value="constantly" <?php if ($checking == "constantly") echo "selected";?>>Постоянно (проживает в этой же квартире)</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" title="Какую ответственность за состояние и ремонт объекта берет на себя собственник">
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
<script src="js/newadvert.js"></script>

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
