<?php
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

/*************************************************************************************
 * Если пользователь авторизован - получим его id
 ************************************************************************************/

$userId = login();

/*************************************************************************************
 * Присваиваем поисковым переменным значения по умолчанию
 ************************************************************************************/

$typeOfObject = "0";
$amountOfRooms = array();
$adjacentRooms = "0";
$floor = "0";
$furniture = "0";
$minCost = "";
$maxCost = "";
$pledge = "";
$prepayment = "12";
$district = array();
$withWho = "0";
$children = "0";
$animals = "0";
$termOfLease = "0";

// Готовим массив со списком районов в городе пользователя
$allDistrictsInCity = array();
$rezDistricts = mysql_query("SELECT name FROM districts WHERE city = '" . "Екатеринбург" . "' ORDER BY name ASC");
for ($i = 0; $i < mysql_num_rows($rezDistricts); $i++) {
    $rowDistricts = mysql_fetch_assoc($rezDistricts);
    $allDistrictsInCity[] = $rowDistricts['name'];
}

/***************************************************************************************************************
 * Если пользователь нажал на кнопку Поиск
 **************************************************************************************************************/

if (isset($_POST['fastSearchButton'])) {
    if (isset($_POST['typeOfObjectFast'])) $typeOfObject = htmlspecialchars($_POST['typeOfObjectFast']);
    if (isset($_POST['districtFast']) && $_POST['districtFast'] != "0") $district = array($_POST['districtFast']);
    if (isset($_POST['minCostFast']) && preg_match("/^\d{0,8}$/", $_POST['minCostFast'])) $minCost = htmlspecialchars($_POST['minCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
    if (isset($_POST['maxCostFast']) && preg_match("/^\d{0,8}$/", $_POST['maxCostFast'])) $maxCost = htmlspecialchars($_POST['maxCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
}

if (isset($_POST['extendedSearchButton'])) {
    if (isset($_POST['typeOfObject'])) $typeOfObject = htmlspecialchars($_POST['typeOfObject']);
    if (isset($_POST['amountOfRooms']) && is_array($_POST['amountOfRooms'])) $amountOfRooms = $_POST['amountOfRooms'];
    if (isset($_POST['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
    if (isset($_POST['floor'])) $floor = htmlspecialchars($_POST['floor']);
    if (isset($_POST['furniture'])) $furniture = htmlspecialchars($_POST['furniture']);
    if (isset($_POST['minCost']) && preg_match("/^\d{0,8}$/", $_POST['minCost'])) $minCost = htmlspecialchars($_POST['minCost']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
    if (isset($_POST['maxCost']) && preg_match("/^\d{0,8}$/", $_POST['maxCost'])) $maxCost = htmlspecialchars($_POST['maxCost']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
    if (isset($_POST['pledge']) && preg_match("/^\d{0,8}$/", $_POST['pledge'])) $pledge = htmlspecialchars($_POST['pledge']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
    if (isset($_POST['prepayment'])) $prepayment = htmlspecialchars($_POST['prepayment']);
    if (isset($_POST['district']) && is_array($_POST['district'])) $district = $_POST['district'];
    if (isset($_POST['withWho'])) $withWho = htmlspecialchars($_POST['withWho']);
    if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']);
    if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']);
    if (isset($_POST['termOfLease'])) $termOfLease = htmlspecialchars($_POST['termOfLease']);
}

/***************************************************************************************************************
 * Если пользователь залогинен и указал в личном кабинете параметры поиска
 **************************************************************************************************************/

if (!isset($_POST['fastSearchButton']) && !isset($_POST['extendedSearchButton']) && $userId != false) {
    // Получаем данные поискового запроса данного пользователя из БД, если они конечно там есть
    $rezSearchRequests = mysql_query("SELECT * FROM searchrequests WHERE userId = '" . $userId . "'");
    $rowSearchRequests = mysql_fetch_assoc($rezSearchRequests);

    // Выполняем только если есть в БД у данного пользователя поисковый запрос
    if ($rowSearchRequests != false) {
        if (isset($rowSearchRequests['typeOfObject'])) $typeOfObject = htmlspecialchars($rowSearchRequests['typeOfObject']);
        if (isset($rowSearchRequests['amountOfRooms'])) $amountOfRooms = unserialize($rowSearchRequests['amountOfRooms']);
        if (isset($rowSearchRequests['adjacentRooms'])) $adjacentRooms = htmlspecialchars($rowSearchRequests['adjacentRooms']);
        if (isset($rowSearchRequests['floor'])) $floor = htmlspecialchars($rowSearchRequests['floor']);
        if (isset($rowSearchRequests['furniture'])) $furniture = htmlspecialchars($rowSearchRequests['furniture']);
        if (isset($rowSearchRequests['minCost'])) $minCost = htmlspecialchars($rowSearchRequests['minCost']);
        if (isset($rowSearchRequests['maxCost'])) $maxCost = htmlspecialchars($rowSearchRequests['maxCost']);
        if (isset($rowSearchRequests['pledge'])) $pledge = htmlspecialchars($rowSearchRequests['pledge']);
        if (isset($rowSearchRequests['prepayment'])) $prepayment = htmlspecialchars($rowSearchRequests['prepayment']);
        if (isset($rowSearchRequests['district'])) $district = unserialize($rowSearchRequests['district']);
        if (isset($rowSearchRequests['withWho'])) $withWho = htmlspecialchars($rowSearchRequests['withWho']);
        if (isset($rowSearchRequests['children'])) $children = htmlspecialchars($rowSearchRequests['children']);
        if (isset($rowSearchRequests['animals'])) $animals = htmlspecialchars($rowSearchRequests['animals']);
        if (isset($rowSearchRequests['termOfLease'])) $termOfLease = htmlspecialchars($rowSearchRequests['termOfLease']);
    }
}

/***************************************************************************************************************
 * Составляем поисковый запрос и получаем данные по соответствующим объектам недвижимости из БД
 **************************************************************************************************************/

// Инициализируем массив, в который будем собирать условия поиска.
$str = array();

// Ограничение на тип объекта
$str['typeOfObject'] = "";
if ($typeOfObject == "0") $str['typeOfObject'] = "";
if ($typeOfObject == "квартира" || $typeOfObject == "комната" || $typeOfObject == "дом" || $typeOfObject == "таунхаус" || $typeOfObject == "дача" || $typeOfObject == "гараж") {
    $str['typeOfObject'] = " (typeOfObject = '" . $typeOfObject . "')"; // Думаю, что с точки зрения безопасности (чтобы нельзя было подсунуть в запрос левые SQL подобные строки), нужно перечислять все доступные варианты
}

// Ограничение на количество комнат
$str['amountOfRooms'] = "";
if (count($amountOfRooms) != "0") {
    $str['amountOfRooms'] = " (";
    for ($i = 0; $i < count($amountOfRooms); $i++) {
        $str['amountOfRooms'] .= " amountOfRooms = '" . $amountOfRooms[$i] . "'";
        if ($i < count($amountOfRooms) - 1) $str['amountOfRooms'] .= " OR";
    }
    $str['amountOfRooms'] .= " )";
}

// Ограничение на смежность комнат
$str['adjacentRooms'] = "";
if ($adjacentRooms == "0") $str['adjacentRooms'] = "";
if ($adjacentRooms == "не имеет значения") $str['adjacentRooms'] = "";
if ($adjacentRooms == "только изолированные") $str['adjacentRooms'] = " (adjacentRooms != 'да')";

// Ограничение на этаж
$str['floor'] = "";
if ($floor == "0") $str['floor'] = "";
if ($floor == "любой") $str['floor'] = "";
if ($floor == "не первый") $str['floor'] = " (floor != '1')";
if ($floor == "не первый и не последний") $str['floor'] = " (floor != '1' AND floor != totalAmountFloor)";

// Ограничение на мебель
//TODO: доделать
/*Работает только после извлечения и получения данных, так как в БД данные содержатся в BLOB нечитаемом формате!!!!!!!!!!!!!!!!!!!!!
$strFurniture = "";
if ($furniture == "0") $strFurniture = "";
if ($furniture == "не имеет значения") $strFurniture = "";
if ($furniture == "с мебелью и быт. техникой") $strFurniture = ;
if ($furniture == "без мебели") $strFurniture = ;
*/

// Ограничение на минимальную сумму арендной платы
$str['minCost'] = "";
if ($minCost == "") $str['minCost'] = "";
if ($minCost != "") $str['minCost'] = " (costOfRenting >= " . $minCost . ")";

// Ограничение на максимальную сумму арендной платы
$str['maxCost'] = "";
if ($maxCost == "") $str['maxCost'] = "";
if ($maxCost != "") $str['maxCost'] = " (costOfRenting <= " . $maxCost . ")";

// Ограничение на максимальный залог
$str['pledge'] = "";
if ($pledge == "") $str['pledge'] = "";
if ($pledge != "") $str['pledge'] = " (bailCost <= " . $pledge . ")";

// Ограничение на предоплату
//TODO: доделать
/*Нужно менять формат указания предоплаты при формировании объявлений, чтобы их можно было численно сравнивать!!!!!!!!!!!!!!!!!!!!!
prepayment
prepayment
prepayment
prepayment
*/

// Ограничение на район
$str['district'] = "";
if (count($district) == 0) $str['district'] = "";
if (count($district) != 0) {
    $str['district'] = " (";
    for ($i = 0; $i < count($district); $i++) {
        $str['district'] .= " district = '" . $district[$i] . "'";
        if ($i < count($district) - 1) $str['district'] .= " OR";
    }
    $str['district'] .= " )";
}

// Ограничение на формат проживания (с кем собираетесь проживать)
//TODO: доделать
/*Работает только после извлечения и получения данных, так как в БД данные содержатся в BLOB нечитаемом формате!!!!!!!!!!!!!!!!!!!!!
Кроме того, если проживать = Один, то нужно смотреть еще не совпадение полов!!!!!!!!!!!!!!!!!!!!!
withWho
withWho
withWho
withWho
withWho
*/

// Ограничение на проживание с детьми
$str['children'] = "";
if ($children == "0" || $children == "без детей") $str['children'] = "";
if ($children == "с детьми старше 4-х лет") $str['children'] = " (children != 'только без детей')";
if ($children == "с детьми младше 4-х лет") $str['children'] = " (children != 'только без детей' AND children != 'с детьми старше 4-х лет')";

// Ограничение на проживание с животными
$str['animals'] = "";
if ($animals == "0" || $animals == "без животных") $str['animals'] = "";
if ($animals == "с животным(ми)") $str['animals'] = " (animals != 'только без животных')";

// Ограничение на длительность аренды
$str['termOfLease'] = "";
if ($termOfLease == "0") $str['termOfLease'] = "";
if ($termOfLease == "длительный срок") $str['termOfLease'] = " (termOfLease = 'длительный срок')";
if ($termOfLease == "несколько месяцев") $str['termOfLease'] = " (termOfLease = 'несколько месяцев')";

// Показываем только опубликованные объявления
$str['status'] = " (status = 'опубликовано')";

// Собираем строку WHERE для поискового запроса к БД
$strWHERE = "";
foreach ($str as $value) {
    if ($value == "") continue;
    if ($strWHERE != "") $strWHERE .= " AND" . $value; else $strWHERE .= $value;
}

// Собираем и выполняем поисковый запрос
$rowPropertyArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
$rezProperty = mysql_query("SELECT * FROM property WHERE" . $strWHERE . " ORDER BY costOfRenting LIMIT 100"); // Сортируем по стоимости аренды и ограничиваем количество 100 объявлениями
if ($rezProperty != false) {
    for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
        $rowPropertyArr[] = mysql_fetch_assoc($rezProperty);
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

    <title>Поиск недвижимости в аренду</title>
    <meta name="description" content="Поиск недвижимости в аренду">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Стили для параметров поиска*/
        #fastSearchInput {
            line-height: 2.4;
        }

        .actionsOnSearch {
            float: right;
            margin-top: 10px;
        }

        #extendedSearchButton {
            margin-left: 20px;
        }
    </style>

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- Загружаем библиотеку для работы с картой от Яндекса -->
    <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

</head>

<body>
<div class="page_without_footer">
<!-- Сформируем и вставим заголовок страницы -->
<?php
include("header.php");
?>

<div class="page_main_content">
<div class="wrapperOfTabs">
<div class="headerOfPage">
    Найдите подходящие Вам объявления
</div>
<div id="tabs">
<ul>
    <li>
        <a href="#tabs-1">Быстрый поиск</a>
    </li>
    <li>
        <a href="#tabs-2">Расширенный поиск</a>
    </li>
</ul>
<div id="tabs-1">
    <form name="fastSearch" method="post">
							<span id="fastSearchInput"> Я хочу арендовать
								<select name="typeOfObjectFast" id="typeOfObjectFast">
                                    <option value="0" <?php if ($typeOfObject == "0") echo "selected";?>></option>
                                    <option value="квартира" <?php if ($typeOfObject == "квартира") echo "selected";?>>
                                        квартира
                                    </option>
                                    <option value="комната" <?php if ($typeOfObject == "комната") echo "selected";?>>
                                        комната
                                    </option>
                                    <option value="дом" <?php if ($typeOfObject == "дом") echo "selected";?>>дом,
                                        коттедж
                                    </option>
                                    <option value="таунхаус" <?php if ($typeOfObject == "таунхаус") echo "selected";?>>
                                        таунхаус
                                    </option>
                                    <option value="дача" <?php if ($typeOfObject == "дача") echo "selected";?>>дача
                                    </option>
                                    <option value="гараж" <?php if ($typeOfObject == "гараж") echo "selected";?>>гараж
                                    </option>
                                </select>
                                в районе
                                <select name="districtFast" id="districtFast">
                                    <option value="0"></option>
                                    <?php
                                    if (isset($allDistrictsInCity)) {
                                        foreach ($allDistrictsInCity as $value) { // Для каждого названия района формируем option в селекте
                                            echo "<option value='" . $value . "'";
                                            if (isset($district[0]) && $value == $district[0]) echo "selected"; // В качестве выбранного в селекте назначаем первый район из списка выбранных пользователем
                                            echo ">" . $value . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
								стоимостью от
								<input type="text" name="minCostFast" size="10"
                                       maxlength="8" <?php echo "value='$minCost'";?>>
								до
								<input type="text" name="maxCostFast" size="10"
                                       maxlength="8" <?php echo "value='$maxCost'";?>>
								руб./мес.
								&nbsp;
								<button type="submit" name="fastSearchButton" id="fastSearchButton">
                                    Найти
                                </button>
                            </span>
    </form>
</div>
<div id="tabs-2">
<form name="extendedSearch" method="post">
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
                    <option value="квартира" <?php if ($typeOfObject == "квартира") echo "selected";?>>квартира</option>
                    <option value="комната" <?php if ($typeOfObject == "комната") echo "selected";?>>комната</option>
                    <option value="дом" <?php if ($typeOfObject == "дом") echo "selected";?>>дом, коттедж</option>
                    <option value="таунхаус" <?php if ($typeOfObject == "таунхаус") echo "selected";?>>таунхаус</option>
                    <option value="дача" <?php if ($typeOfObject == "дача") echo "selected";?>>дача</option>
                    <option value="гараж" <?php if ($typeOfObject == "гараж") echo "selected";?>>гараж</option>
                </select>
            </div>
        </div>
        <div class="searchItem">
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
        <div class="searchItem">
            <span class="searchItemLabel"> Комнаты смежные: </span>

            <div class="searchItemBody">
                <select name="adjacentRooms">
                    <option value="0" <?php if ($adjacentRooms == "0") echo "selected";?>></option>
                    <option
                        value="не имеет значения" <?php if ($adjacentRooms == "не имеет значения") echo "selected";?>>не
                        имеет значения
                    </option>
                    <option
                        value="только изолированные" <?php if ($adjacentRooms == "только изолированные") echo "selected";?>>
                        только изолированные
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Этаж: </span>

            <div class="searchItemBody">
                <select name="floor">
                    <option value="0" <?php if ($floor == "0") echo "selected";?>></option>
                    <option value="любой" <?php if ($floor == "любой") echo "selected";?>>любой</option>
                    <option value="не первый" <?php if ($floor == "не первый") echo "selected";?>>не первый</option>
                    <option
                        value="не первый и не последний" <?php if ($floor == "не первый и не последний") echo "selected";?>>
                        не первый и не
                        последний
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Мебель: </span>

            <div class="searchItemBody">
                <select name="furniture">
                    <option value="0" <?php if ($furniture == "0") echo "selected";?>></option>
                    <option value="не имеет значения" <?php if ($furniture == "не имеет значения") echo "selected";?>>не
                        имеет значения
                    </option>
                    <option
                        value="с мебелью и быт. техникой" <?php if ($furniture == "с мебелью и быт. техникой") echo "selected";?>>
                        с мебелью и быт. техникой
                    </option>
                    <option value="без мебели" <?php if ($furniture == "без мебели") echo "selected";?>>без мебели
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
                    <option value="12" <?php if ($prepayment == "12") echo "selected";?>></option>
                    <option value="0" <?php if ($prepayment == "0") echo "selected";?>>нет</option>
                    <option value="1" <?php if ($prepayment == "1") echo "selected";?>>1 месяц</option>
                    <option value="2" <?php if ($prepayment == "2") echo "selected";?>>2 месяца</option>
                    <option value="3" <?php if ($prepayment == "3") echo "selected";?>>3 месяца</option>
                    <option value="4" <?php if ($prepayment == "4") echo "selected";?>>4 месяца</option>
                    <option value="5" <?php if ($prepayment == "5") echo "selected";?>>5 месяцев</option>
                    <option value="6" <?php if ($prepayment == "6") echo "selected";?>>6 месяцев</option>
                </select>
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
    <div class="searchItem">
        <span class="searchItemLabel">Как собираетесь проживать: </span>

        <div class="searchItemBody">
            <select name="withWho" id="withWho">
                <option value="0" <?php if ($withWho == "0") echo "selected";?>></option>
                <option value="один" <?php if ($withWho == "один") echo "selected";?>>один</option>
                <option value="семейная пара" <?php if ($withWho == "семейная пара") echo "selected";?>>семейная пара
                </option>
                <option value="несемейная пара" <?php if ($withWho == "несемейная пара") echo "selected";?>>несемейная
                    пара
                </option>
                <option value="со знакомыми" <?php if ($withWho == "со знакомыми") echo "selected";?>>со знакомыми
                </option>
            </select>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Дети: </span>

        <div class="searchItemBody">
            <select name="children" id="children">
                <option value="0" <?php if ($children == "0") echo "selected";?>></option>
                <option value="без детей" <?php if ($children == "без детей") echo "selected";?>>без детей</option>
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
    <div class="searchItem">
        <span class="searchItemLabel">Животные: </span>

        <div class="searchItemBody">
            <select name="animals" id="animals">
                <option value="0" <?php if ($animals == "0") echo "selected";?>></option>
                <option value="без животных" <?php if ($animals == "без животных") echo "selected";?>>без животных
                </option>
                <option value="с животным(ми)" <?php if ($animals == "с животным(ми)") echo "selected";?>>с
                    животным(ми)
                </option>
            </select>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Срок аренды:</span>

        <div class="searchItemBody">
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
</fieldset>
<div class="actionsOnSearch">
    <a href="#">Запомнить условия поиска</a>
    <button type="submit" name="extendedSearchButton" id="extendedSearchButton">
        Найти
    </button>
</div>
<div class="clearBoth"></div>
</form>
</div>
<!-- /end.tabs-2 -->
</div>
<!-- /end.tabs -->
</div>
<!-- /end.wrapperOfTabs -->

<div class="choiceViewSearchResult">
    <span id="expandList"><a href="#">Список</a>&nbsp;&nbsp;&nbsp;</span><span id="listPlusMap"><a href="#">Список +
    карта</a>&nbsp;&nbsp;&nbsp;</span><span id="expandMap"><a href="#">Карта</a></span>
</div>
<div id="resultOnSearchPage" style="height: 100%;">

<!-- Информация об объектах, подходящих условиям поиска -->
<table class="listOfRealtyObjects" id="shortListOfRealtyObjects">
<tbody>
<?php
// Для целей ускорения загрузки перенес блок php кода сюда - это позволит браузеру грузить нужные библиотеки в то время, как сервер будет готовить представление для таблиц с данными об объектах недвижимости

/***************************************************************************************************************
 * Оформляем полученные объявления в красивые HTML-блоки для размещения на странице
 **************************************************************************************************************/

// Шаблон для всплывающего баллуна с описанием объекта недвижимости на карте Яндекса
$tmpl_balloonContentBody = "
<div class='headOfBalloon'>{address}</div>
<div class='fotosWrapper'>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src='{urlFoto1}'>
    </div>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src='{urlFoto2}'>
    </div>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src='{urlFoto3}'>
    </div>
</div>
<ul class='listDescription'>
    <li>
        <span class='headOfString'>Плата за аренду:</span> {costOfRenting} {currency} в месяц
    </li>
    <li>
        <span class='headOfString'>Коммунальные услуги:</span> {utilities}
    </li>
    <li>
        <span class='headOfString'>Единовременная комиссия:</span><a href='#'> {compensationMoney} {currency} ({compensationPercent}%) собственнику</a>
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
</ul>
<div class='clearBoth'></div>
<div style='width:100%;'>
    <a href='{urlProperty}'>Подробнее</a>
    <div style='float: right; cursor: pointer;'>
        <div class='blockOfIcon'>
            <a><img class='icon' title='Добавить в избранное' src='img/blue_star.png'></a>
        </div>
        <a id='addToFavorit'> добавить в избранное</a>
    </div>
</div>
";

// Шаблон для блока с кратким описанием объекта недвижимости в таблице
$tmpl_shortAdvert = "
<tr class='realtyObject' coordX='{coordX}' coordY='{coordY}' balloonContentBody=\"{balloonContentBody}\">
    <td>
	    <div class='numberOfRealtyObject'>{number}</div>
	    <div class='blockOfIcon'>
		    <a><img class='icon' title='Добавить в избранное' src='img/blue_star.png'></a>
	    </div>
	</td>
	<td>
	    <div class='fotosWrapper resultSearchFoto'>
		    <div class='middleFotoWrapper'>
			    <img class='middleFoto' src='{urlFoto1}'>
			</div>
		</div>
    </td>
	<td>{address}
	    <div class='linkToDescriptionBlock'>
		<a class='linkToDescription' href='{urlProperty}'>Подробнее</a>
		</div>
	</td>
	<td>{costOfRenting} {currency} в месяц</td>
</tr>
";

// Инициализируем переменные, в которые сложим HTML блоки каждого из объявлений, именно эти блоки в нужном месте страницы мы и вставим для передачи в браузер
$matterOfShortList = "";
$matterOfFullParametersList = "";

// Инициализируем счетчик объявлений
$number = 0;

// Начинаем перебор каждого из полученных ранее объявлений для наполнения их данными шаблонов и получения красивых HTML-блоков для публикации на странице
foreach ($rowPropertyArr as $oneProperty) {

    // Увеличиваем счетчик объявлений при каждом проходе
    $number++;

/* Готовим баллун */

    // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне баллуна
    $arrBalloonReplace = array();

    // Наполняем массив $arrBalloonReplace данными, которые заменят болванки в шаблоне
    // Адрес
    $arrBalloonReplace['address'] = "";
    if (isset($oneProperty['address'])) $arrBalloonReplace['address'] = $oneProperty['address'];

    // Фото
    $arrBalloonReplace['urlFoto1'] = "";
    $arrBalloonReplace['urlFoto2'] = "";
    $arrBalloonReplace['urlFoto3'] = "";
    // Получаем данные по всем фотографиям для данного объекта недвижимости
    $rowPropertyFotosArr = array(); // Массив, в который запишем массивы, каждый из которых будет содержать данные по 1 фотке объекта
    $rezPropertyFotos = mysql_query("SELECT id, extension FROM propertyFotos WHERE propertyId = '" . $oneProperty['id'] . "'");
    if ($rezPropertyFotos != false) {
        for ($i = 0; $i < mysql_num_rows($rezPropertyFotos); $i++) {
            $rowPropertyFotosArr[] = mysql_fetch_assoc($rezPropertyFotos);
        }
    }
    if (isset($rowPropertyFotosArr[0])) $arrBalloonReplace['urlFoto1'] = "uploaded_files/" . $rowPropertyFotosArr[0]['id'] . "." . $rowPropertyFotosArr[0]['extension'];
    if (isset($rowPropertyFotosArr[1])) $arrBalloonReplace['urlFoto2'] = "uploaded_files/" . $rowPropertyFotosArr[1]['id'] . "." . $rowPropertyFotosArr[1]['extension'];
    if (isset($rowPropertyFotosArr[2])) $arrBalloonReplace['urlFoto3'] = "uploaded_files/" . $rowPropertyFotosArr[2]['id'] . "." . $rowPropertyFotosArr[2]['extension'];

    // Все, что касается СТОИМОСТИ АРЕНДЫ
    $arrBalloonReplace['costOfRenting'] = "";
    if (isset($oneProperty['costOfRenting'])) $arrBalloonReplace['costOfRenting'] = $oneProperty['costOfRenting'];
    $arrBalloonReplace['currency'] = "";
    if (isset($oneProperty['currency'])) $arrBalloonReplace['currency'] = $oneProperty['currency'];
    $arrBalloonReplace['utilities'] = "";
    if (isset($oneProperty['utilities']) && $oneProperty['utilities'] == "да") $arrBalloonReplace['utilities'] = "от " . $oneProperty['costInSummer'] . " до " . $oneProperty['costInWinter'] . " " . $oneProperty['currency'] . " в месяц"; else $arrBalloonReplace['utilities'] = "нет";
    $arrBalloonReplace['compensationMoney'] = "";
    if (isset($oneProperty['compensationMoney'])) $arrBalloonReplace['compensationMoney'] = $oneProperty['compensationMoney'];
    $arrBalloonReplace['compensationPercent'] = "";
    if (isset($oneProperty['compensationPercent'])) $arrBalloonReplace['compensationPercent'] = $oneProperty['compensationPercent'];

    // Комнаты
    if (isset($oneProperty['amountOfRooms']) && $oneProperty['amountOfRooms'] != "0") {
        $arrBalloonReplace['amountOfRoomsName'] = "Количество комнат:";
        $arrBalloonReplace['amountOfRooms'] = $oneProperty['amountOfRooms'];
    } else {
        $arrBalloonReplace['amountOfRoomsName'] = "";
        $arrBalloonReplace['amountOfRooms'] = "";
    }
    if (isset($oneProperty['adjacentRooms']) && $oneProperty['adjacentRooms'] == "да") {
        if ($oneProperty['amountOfAdjacentRooms'] != "0") {
            $arrBalloonReplace['adjacentRooms'] = ", из них смежных: " . $oneProperty['amountOfAdjacentRooms'];
        } else {
            $arrBalloonReplace['adjacentRooms'] = ", смежные";
        }
    } else {
        $arrBalloonReplace['adjacentRooms'] = "";
    }

    // Площади помещений
    $arrBalloonReplace['areaNames'] = "";
    $arrBalloonReplace['areaValues'] = "";
    if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "квартира" && $oneProperty['typeOfObject'] != "дом" && $oneProperty['typeOfObject'] != "таунхаус" && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
        $arrBalloonReplace['areaNames'] .= "комнаты";
        $arrBalloonReplace['areaValues'] .= $oneProperty['roomSpace'];
    }
    if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната") {
        $arrBalloonReplace['areaNames'] .= "общая";
        $arrBalloonReplace['areaValues'] .= $oneProperty['totalArea'];
    }
    if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['typeOfObject'] != "гараж") {
        $arrBalloonReplace['areaNames'] .= "/жилая";
        $arrBalloonReplace['areaValues'] .= " / " . $oneProperty['livingSpace'];
    }
    if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
        $arrBalloonReplace['areaNames'] .= "/кухни";
        $arrBalloonReplace['areaValues'] .= " / " . $oneProperty['kitchenSpace'];
    }

    // Этаж
    $arrBalloonReplace['floorName'] = "";
    $arrBalloonReplace['floor'] = "";
    if (isset($oneProperty['floor']) && isset($oneProperty['totalAmountFloor']) && $oneProperty['floor'] != "0" && $oneProperty['totalAmountFloor'] != "0") {
        $arrBalloonReplace['floorName'] = "Этаж:";
        $arrBalloonReplace['floor'] = $oneProperty['floor'] . " из " . $oneProperty['totalAmountFloor'];
    }
    if (isset($oneProperty['numberOfFloor']) && $oneProperty['numberOfFloor'] != "0") {
        $arrBalloonReplace['floorName'] = "Этажность:";
        $arrBalloonReplace['floor'] = $oneProperty['numberOfFloor'];
    }

    // Мебель
    $arrBalloonReplace['furnitureName'] = "";
    $arrBalloonReplace['furniture'] = "";
    if (isset($oneProperty['furnitureInLivingArea']) && count(unserialize($oneProperty['furnitureInLivingArea'])) != 0 || $oneProperty['furnitureInLivingAreaExtra'] != "") $arrBalloonReplace['furniture'] = "есть в жилой зоне";
    if (isset($oneProperty['furnitureInKitchen']) && count(unserialize($oneProperty['furnitureInKitchen'])) != 0 || $oneProperty['furnitureInKitchenExtra'] != "") if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "есть на кухне"; else $arrBalloonReplace['furniture'] .= ", есть на кухне";
    if (isset($oneProperty['appliances']) && count(unserialize($oneProperty['appliances'])) != 0 || $oneProperty['appliancesExtra'] != "") if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "есть бытовая техника"; else $arrBalloonReplace['furniture'] .= ", есть бытовая техника";
    if (isset($oneProperty['furniture']) && $arrBalloonReplace['furniture'] != "") $arrBalloonReplace['furnitureName'] = "Мебель:";

    // Ссылка "Подробно"
    if (isset($oneProperty['id'])) $arrBalloonReplace['urlProperty'] = "objdescription.php?propertyId=" . $oneProperty['id'];

    // Производим заполнение шаблона баллуна
    // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
    $arrBalloonTemplVar = array('{address}', '{urlFoto1}', '{urlFoto2}', '{urlFoto3}', '{costOfRenting}', '{currency}', '{utilities}', '{compensationMoney}', '{compensationPercent}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaNames}', '{areaValues}', '{floorName}', '{floor}', '{furnitureName}', '{furniture}', '{urlProperty}');
    // Копируем html-текст шаблона баллуна
    $currentAdvertBalloon = str_replace($arrBalloonTemplVar, $arrBalloonReplace, $tmpl_balloonContentBody);

/* Готовим блок shortList таблицы для данного объекта недвижимости */

    // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне shortList строки таблицы
    $arrShortListReplace = array();

    $arrShortListReplace['coordX'] = "";
    if (isset($oneProperty['coordX'])) $arrShortListReplace['coordX'] = $oneProperty['coordX'];

    $arrShortListReplace['coordY'] = "";
    if (isset($oneProperty['coordY'])) $arrShortListReplace['coordY'] = $oneProperty['coordY'];

    $arrShortListReplace['balloonContentBody'] = $currentAdvertBalloon;

    $arrShortListReplace['number'] = $number;

    $arrShortListReplace['urlFoto1'] = "";
    if (isset($rowPropertyFotosArr[0]['id']) && isset($rowPropertyFotosArr[0]['extension'])) $arrShortListReplace['urlFoto1'] = "uploaded_files/" . $rowPropertyFotosArr[0]['id'] . "." . $rowPropertyFotosArr[0]['extension'];

    $arrShortListReplace['address'] = "";
    if (isset($oneProperty['address'])) $arrShortListReplace['address'] = $oneProperty['address'];

    $arrShortListReplace['urlProperty'] = "";
    if (isset($oneProperty['id'])) $arrShortListReplace['urlProperty'] = "objdescription.php?propertyId=" . $oneProperty['id'];

    $arrShortListReplace['costOfRenting'] = "";
    if (isset($oneProperty['costOfRenting'])) $arrShortListReplace['costOfRenting'] = $oneProperty['costOfRenting'];

    $arrShortListReplace['currency'] = "";
    if (isset($oneProperty['currency'])) $arrShortListReplace['currency'] = $oneProperty['currency'];

    // Производим заполнение шаблона строки (блока) shortList таблицы по данному объекту недвижимости
    // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
    $arrShortListTemplVar = array('{coordX}', '{coordY}', '{balloonContentBody}', '{number}', '{urlFoto1}', '{address}', '{urlProperty}', '{costOfRenting}', '{currency}');
    // Копируем html-текст шаблона баллуна
    $currentAdvertShortList = str_replace($arrShortListTemplVar, $arrShortListReplace, $tmpl_shortAdvert);

    // Полученный HTML текст складываем в "копилочку"
    $matterOfShortList .= $currentAdvertShortList;

}

echo $matterOfShortList; // Вставляем текст объявлений по недвижимости с короткими данными и данными для баллуной на Яндекс карте

?>

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
                    <a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
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
                    <a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
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
                    <a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
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
                    <a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
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
                    <a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
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
<script src="js/search.js"></script>
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
