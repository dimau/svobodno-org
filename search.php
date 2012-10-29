<?php
    include_once 'lib/connect.php'; //подключаемся к БД
    include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями
    include_once 'lib/function_searchResult.php'; // Подключаем файл с функциями по HTML оформлению результатов поиска

    /*************************************************************************************
     * Если пользователь авторизован - получим его id
     ************************************************************************************/

    $userId = login($DBlink);

    /*************************************************************************************
     * Присваиваем поисковым переменным значения по умолчанию
     ************************************************************************************/

    $typeOfObject = "0";
    $amountOfRooms = array();
    $adjacentRooms = "0";
    $floor = "0";
    $minCost = "";
    $maxCost = "";
    $pledge = "";
    $prepayment = "0";
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

    if (isset($_GET['fastSearchButton'])) {
        if (isset($_GET['typeOfObjectFast'])) $typeOfObject = htmlspecialchars($_GET['typeOfObjectFast']);
        if (isset($_GET['districtFast']) && $_GET['districtFast'] != "0") $district = array($_GET['districtFast']);
        if (isset($_GET['minCostFast']) && preg_match("/^\d{0,8}$/", $_GET['minCostFast'])) $minCost = htmlspecialchars($_GET['minCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
        if (isset($_GET['maxCostFast']) && preg_match("/^\d{0,8}$/", $_GET['maxCostFast'])) $maxCost = htmlspecialchars($_GET['maxCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
    }

    if (isset($_GET['extendedSearchButton'])) {
        if (isset($_GET['typeOfObject'])) $typeOfObject = htmlspecialchars($_GET['typeOfObject']);
        if (isset($_GET['amountOfRooms']) && is_array($_GET['amountOfRooms'])) $amountOfRooms = $_GET['amountOfRooms'];
        if (isset($_GET['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_GET['adjacentRooms']);
        if (isset($_GET['floor'])) $floor = htmlspecialchars($_GET['floor']);
        if (isset($_GET['minCost']) && preg_match("/^\d{0,8}$/", $_GET['minCost'])) $minCost = htmlspecialchars($_GET['minCost']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
        if (isset($_GET['maxCost']) && preg_match("/^\d{0,8}$/", $_GET['maxCost'])) $maxCost = htmlspecialchars($_GET['maxCost']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
        if (isset($_GET['pledge']) && preg_match("/^\d{0,8}$/", $_GET['pledge'])) $pledge = htmlspecialchars($_GET['pledge']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
        if (isset($_GET['prepayment'])) $prepayment = htmlspecialchars($_GET['prepayment']);
        if (isset($_GET['district']) && is_array($_GET['district'])) $district = $_GET['district'];
        if (isset($_GET['withWho'])) $withWho = htmlspecialchars($_GET['withWho']);
        if (isset($_GET['children'])) $children = htmlspecialchars($_GET['children']);
        if (isset($_GET['animals'])) $animals = htmlspecialchars($_GET['animals']);
        if (isset($_GET['termOfLease'])) $termOfLease = htmlspecialchars($_GET['termOfLease']);
    }

    /***************************************************************************************************************
     * Если пользователь залогинен и указал в личном кабинете параметры поиска, но еще не нажимал кнопки Поиск на этой странице
     **************************************************************************************************************/

    if (!isset($_GET['fastSearchButton']) && !isset($_GET['extendedSearchButton']) && $userId != FALSE) {
        // Получаем данные поискового запроса данного пользователя из БД, если они конечно там есть
        $rezSearchRequests = mysql_query("SELECT * FROM searchrequests WHERE userId = '" . $userId . "'");
        $rowSearchRequests = mysql_fetch_assoc($rezSearchRequests);

        // Выполняем только если есть в БД у данного пользователя поисковый запрос
        if ($rowSearchRequests != FALSE) {
            if (isset($rowSearchRequests['typeOfObject'])) $typeOfObject = htmlspecialchars($rowSearchRequests['typeOfObject']);
            if (isset($rowSearchRequests['amountOfRooms'])) $amountOfRooms = unserialize($rowSearchRequests['amountOfRooms']);
            if (isset($rowSearchRequests['adjacentRooms'])) $adjacentRooms = htmlspecialchars($rowSearchRequests['adjacentRooms']);
            if (isset($rowSearchRequests['floor'])) $floor = htmlspecialchars($rowSearchRequests['floor']);
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
    $searchLimits = array();

    // Ограничение на тип объекта
    $searchLimits['typeOfObject'] = "";
    if ($typeOfObject == "0") $searchLimits['typeOfObject'] = "";
    if ($typeOfObject == "квартира" || $typeOfObject == "комната" || $typeOfObject == "дом" || $typeOfObject == "таунхаус" || $typeOfObject == "дача" || $typeOfObject == "гараж") {
        $searchLimits['typeOfObject'] = " (typeOfObject = '" . $typeOfObject . "')"; // Думаю, что с точки зрения безопасности (чтобы нельзя было подсунуть в запрос левые SQL подобные строки), нужно перечислять все доступные варианты
    }

    // Ограничение на количество комнат
    $searchLimits['amountOfRooms'] = "";
    if (count($amountOfRooms) != "0") {
        $searchLimits['amountOfRooms'] = " (";
        for ($i = 0; $i < count($amountOfRooms); $i++) {
            $searchLimits['amountOfRooms'] .= " amountOfRooms = '" . $amountOfRooms[$i] . "'";
            if ($i < count($amountOfRooms) - 1) $searchLimits['amountOfRooms'] .= " OR";
        }
        $searchLimits['amountOfRooms'] .= " )";
    }

    // Ограничение на смежность комнат
    $searchLimits['adjacentRooms'] = "";
    if ($adjacentRooms == "0") $searchLimits['adjacentRooms'] = "";
    if ($adjacentRooms == "не имеет значения") $searchLimits['adjacentRooms'] = "";
    if ($adjacentRooms == "только изолированные") $searchLimits['adjacentRooms'] = " (adjacentRooms != 'да')";

    // Ограничение на этаж
    $searchLimits['floor'] = "";
    if ($floor == "0") $searchLimits['floor'] = "";
    if ($floor == "любой") $searchLimits['floor'] = " (floor != 0)";
    if ($floor == "не первый") $searchLimits['floor'] = " (floor != 0 AND floor != 1)";
    if ($floor == "не первый и не последний") $searchLimits['floor'] = " (floor != 0 AND floor != 1 AND floor != totalAmountFloor)";

    // Ограничение на минимальную сумму арендной платы
    $searchLimits['minCost'] = "";
    if ($minCost == "") $searchLimits['minCost'] = "";
    if ($minCost != "") $searchLimits['minCost'] = " (realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting >= " . $minCost . ")";

    // Ограничение на максимальную сумму арендной платы
    $searchLimits['maxCost'] = "";
    if ($maxCost == "") $searchLimits['maxCost'] = "";
    if ($maxCost != "") $searchLimits['maxCost'] = " (realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting <= " . $maxCost . ")";

    // Ограничение на максимальный залог
    $searchLimits['pledge'] = "";
    if ($pledge == "") $searchLimits['pledge'] = "";
    if ($pledge != "") $searchLimits['pledge'] = " (bailCost * realCostOfRenting / costOfRenting <= " . $pledge . ")"; // отношение realCostOfRenting / costOfRenting позволяет вычислить курс валюты, либо получить 1, если стоимость аренды указана собственником в рублях

    // Ограничение на предоплату
    $searchLimits['prepayment'] = "";
    if ($prepayment == "0") $searchLimits['prepayment'] = "";
    if ($prepayment != "0") $searchLimits['prepayment'] = " (prepayment + 0 <= '" . $prepayment . "')";

    // Ограничение на район
    $searchLimits['district'] = "";
    if (count($district) == 0) $searchLimits['district'] = "";
    if (count($district) != 0) {
        $searchLimits['district'] = " (";
        for ($i = 0; $i < count($district); $i++) {
            $searchLimits['district'] .= " district = '" . $district[$i] . "'";
            if ($i < count($district) - 1) $searchLimits['district'] .= " OR";
        }
        $searchLimits['district'] .= " )";
    }

    // Ограничение на формат проживания (с кем собираетесь проживать)
    $searchLimits['withWho'] = "";
    if ($withWho == "0") $searchLimits['withWho'] = "";
    if ($withWho == "самостоятельно") $searchLimits['withWho'] = "(relations LIKE '%один человек%' OR relations = '')";
    if ($withWho == "семья") $searchLimits['withWho'] = "(relations LIKE '%семья%' OR relations = '')";
    if ($withWho == "пара") $searchLimits['withWho'] = "(relations LIKE '%пара%' OR relations = '')";
    if ($withWho == "2 мальчика") $searchLimits['withWho'] = "(relations LIKE '%2 мальчика%' OR relations = '')";
    if ($withWho == "2 девочки") $searchLimits['withWho'] = "(relations LIKE '%2 девочки%' OR relations = '')";
    if ($withWho == "со знакомыми") $searchLimits['withWho'] = "(relations LIKE '%группа людей%' OR relations = '')";

    // Ограничение на проживание с детьми
    $searchLimits['children'] = "";
    if ($children == "0" || $children == "без детей") $searchLimits['children'] = "";
    if ($children == "с детьми старше 4-х лет") $searchLimits['children'] = " (children != 'только без детей')";
    if ($children == "с детьми младше 4-х лет") $searchLimits['children'] = " (children != 'только без детей' AND children != 'с детьми старше 4-х лет')";

    // Ограничение на проживание с животными
    $searchLimits['animals'] = "";
    if ($animals == "0" || $animals == "без животных") $searchLimits['animals'] = "";
    if ($animals == "с животным(ми)") $searchLimits['animals'] = " (animals != 'только без животных')";

    // Ограничение на длительность аренды
    $searchLimits['termOfLease'] = "";
    if ($termOfLease == "0") $searchLimits['termOfLease'] = "";
    if ($termOfLease == "длительный срок") $searchLimits['termOfLease'] = " (termOfLease = 'длительный срок')";
    if ($termOfLease == "несколько месяцев") $searchLimits['termOfLease'] = " (termOfLease = 'несколько месяцев')";

    // Показываем только опубликованные объявления
    $searchLimits['status'] = " (status = 'опубликовано')";

    // Собираем строку WHERE для поискового запроса к БД
    $strWHERE = "";
    foreach ($searchLimits as $value) {
        if ($value == "") continue;
        if ($strWHERE != "") $strWHERE .= " AND" . $value; else $strWHERE .= $value;
    }

    // Собираем и выполняем поисковый запрос - получаем ВСЕ подходящие объявления
    $propertyLightArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
    $rezProperty = mysql_query("SELECT id, coordX, coordY FROM property WHERE" . $strWHERE . " ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting"); // Сортируем по стоимости аренды и не ограничиваем количество объявлений - все, подходящие под условия пользователя
    if ($rezProperty != FALSE) {
        for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
            $propertyLightArr[] = mysql_fetch_assoc($rezProperty);
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
    <link rel="stylesheet" href="css/colorbox.css">
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
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- ColorBox - плагин jQuery, позволяющий делать модальное окно для просмотра фотографий -->
    <script src="js/vendor/jquery.colorbox-min.js"></script>
    <!-- Загружаем библиотеку для работы с картой от Яндекса -->
    <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

</head>

<body>
<div class="page_without_footer">

    <!-- Сформируем и вставим заголовок страницы -->
    <?php
    include("header.php");

    // Для целей ускорения загрузки перенес блок php кода по формированию HTML результатов поиска сюда - это позволит браузеру грузить нужные библиотеки в то время, как сервер будет готовить представление для таблиц с данными об объектах недвижимости
    $searchResultHTML = getSearchResultHTML($propertyLightArr, $userId, "search");
    ?>

    <div class="page_main_content">
        <div class="headerOfPage">
            Приятного поиска!
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
                <form name="fastSearch" method="get">
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
								<input type="text" name="minCostFast" id="minCostFast" size="10"
                                       maxlength="8" <?php echo "value='$minCost'";?>>
								до
								<input type="text" name="maxCostFast" id="maxCostFast" size="10"
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
                <form name="extendedSearch" method="get">
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
                                        <option
                                            value="квартира" <?php if ($typeOfObject == "квартира") echo "selected";?>>
                                            квартира
                                        </option>
                                        <option
                                            value="комната" <?php if ($typeOfObject == "комната") echo "selected";?>>
                                            комната
                                        </option>
                                        <option value="дом" <?php if ($typeOfObject == "дом") echo "selected";?>>дом,
                                            коттедж
                                        </option>
                                        <option
                                            value="таунхаус" <?php if ($typeOfObject == "таунхаус") echo "selected";?>>
                                            таунхаус
                                        </option>
                                        <option value="дача" <?php if ($typeOfObject == "дача") echo "selected";?>>дача
                                        </option>
                                        <option value="гараж" <?php if ($typeOfObject == "гараж") echo "selected";?>>
                                            гараж
                                        </option>
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
                                        <option value="любой" <?php if ($floor == "любой") echo "selected";?>>любой
                                        </option>
                                        <option value="не первый" <?php if ($floor == "не первый") echo "selected";?>>не
                                            первый
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
                                    <input type="text" name="pledge" size="10"
                                           maxlength="8" <?php echo "value='$pledge'";?>>
                                    руб.
                                </div>
                            </div>
                            <div class="searchItem"
                                 title="Какую предоплату за проживание Вы готовы внести">
                                <span class="searchItemLabel"> Максимальная предоплата: </span>

                                <div class="searchItemBody">
                                    <select name="prepayment">
                                        <option value="0" <?php if ($prepayment == "0") echo "selected";?>></option>
                                        <option value="нет" <?php if ($prepayment == "нет") echo "selected";?>>нет
                                        </option>
                                        <option value="1 месяц" <?php if ($prepayment == "1 месяц") echo "selected";?>>1
                                            месяц
                                        </option>
                                        <option
                                            value="2 месяца" <?php if ($prepayment == "2 месяца") echo "selected";?>>2
                                            месяца
                                        </option>
                                        <option
                                            value="3 месяца" <?php if ($prepayment == "3 месяца") echo "selected";?>>3
                                            месяца
                                        </option>
                                        <option
                                            value="4 месяца" <?php if ($prepayment == "4 месяца") echo "selected";?>>4
                                            месяца
                                        </option>
                                        <option
                                            value="5 месяцев" <?php if ($prepayment == "5 месяцев") echo "selected";?>>5
                                            месяцев
                                        </option>
                                        <option
                                            value="6 месяцев" <?php if ($prepayment == "6 месяцев") echo "selected";?>>6
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
                            <span class="searchItemLabel">Как собираетесь проживать: </span>

                            <div class="searchItemBody">
                                <select name="withWho" id="withWho">
                                    <option value="0" <?php if ($withWho == "0") echo "selected";?>></option>
                                    <option
                                        value="самостоятельно" <?php if ($withWho == "самостоятельно") echo "selected";?>>
                                        самостоятельно
                                    </option>
                                    <option value="семья" <?php if ($withWho == "семья") echo "selected";?>>семьей
                                    </option>
                                    <option value="пара" <?php if ($withWho == "пара") echo "selected";?>>парой
                                    </option>
                                    <option value="2 мальчика" <?php if ($withWho == "2 мальчика") echo "selected";?>>2
                                        мальчика
                                    </option>
                                    <option value="2 девочки" <?php if ($withWho == "2 девочки") echo "selected";?>>2
                                        девочки
                                    </option>
                                    <option
                                        value="со знакомыми" <?php if ($withWho == "со знакомыми") echo "selected";?>>со
                                        знакомыми
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="searchItem" notavailability="typeOfObject_гараж">
                            <span class="searchItemLabel">Дети: </span>

                            <div class="searchItemBody">
                                <select name="children" id="children">
                                    <option value="0" <?php if ($children == "0") echo "selected";?>></option>
                                    <option value="без детей" <?php if ($children == "без детей") echo "selected";?>>без
                                        детей
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
                        <div class="searchItem" notavailability="typeOfObject_гараж">
                            <span class="searchItemLabel">Животные: </span>

                            <div class="searchItemBody">
                                <select name="animals" id="animals">
                                    <option value="0" <?php if ($animals == "0") echo "selected";?>></option>
                                    <option
                                        value="без животных" <?php if ($animals == "без животных") echo "selected";?>>
                                        без животных
                                    </option>
                                    <option
                                        value="с животным(ми)" <?php if ($animals == "с животным(ми)") echo "selected";?>>
                                        с
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
                                    <option
                                        value="длительный срок" <?php if ($termOfLease == "длительный срок") echo "selected";?>>
                                        длительный срок (от года)
                                    </option>
                                    <option
                                        value="несколько месяцев" <?php if ($termOfLease == "несколько месяцев") echo "selected";?>>
                                        несколько месяцев (до года)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    <div class="clearBoth"></div>
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

        <?php
        /***************************************************************************************************************
         * Размещаем на странице полученный с сервера HTML для результатов поиска
         **************************************************************************************************************/
        echo $searchResultHTML;
        ?>

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
<script src="js/searchResult.js"></script>
<script>
    /* Навешиваем обработчик на переключение вкладок с режимами поиска */
    $('#tabs').bind('tabsshow', function (event, ui) {
        newTabId = ui.panel.id; // Определяем идентификатор вновь открытой вкладки
        if (newTabId == "tabs-1") {
            // Переносим тип объекта
            $("#typeOfObjectFast").val($("#typeOfObject").val());

            // Так как между районами при расширенном поиске и районом при быстром поиске невозможно построить взаимнооднозначную конвертацию, не будем этого делать, дабы не запутать пользователя

            // Переносим стоимости
            $("#minCostFast").val($("#minCost").val());
            $("#maxCostFast").val($("#maxCost").val());
        }
        if (newTabId == "tabs-2") {
            // Переносим тип объекта
            $("#typeOfObject").val($("#typeOfObjectFast").val());

            // Переносим стоимости
            $("#minCost").val($("#minCostFast").val());
            $("#maxCost").val($("#maxCostFast").val());
        }
    });

    /* Активируем механизм скрытия ненужных полей в зависимости от заполнения формы */
    // При изменении перечисленных здесь полей алгоритм пробегает форму с целью показать нужные элементы и скрыть ненужные
    $(document).ready(notavailability);
    $("#typeOfObject").change(notavailability);

</script>
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
