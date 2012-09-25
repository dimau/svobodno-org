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
    if (isset($_POST['districtFast'])) $district = array($_POST['districtFast']);
    if (isset($_POST['minCostFast']) && preg_match("/^\d{0,8}$/", $_POST['minCostFast'])) $minCost = htmlspecialchars($_POST['minCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
    if (isset($_POST['maxCostFast'])&& preg_match("/^\d{0,8}$/", $_POST['maxCostFast'])) $maxCost = htmlspecialchars($_POST['maxCostFast']); // Значение, введенное пользователем, затирает значение по умолчанию только если оно соответствует формату
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

// Ограничение на тип объекта
$strTypeOfObject = "";
if ($typeOfObject == "0") $strTypeOfObject = "";
if ($typeOfObject == "квартира" || $typeOfObject == "комната" || $typeOfObject == "дом" || $typeOfObject == "таунхаус" || $typeOfObject == "дача" || $typeOfObject == "гараж") {
    $strTypeOfObject = " (typeOfObject = '" . $typeOfObject . "')"; // Думаю, что с точки зрения безопасности (чтобы нельзя было подсунуть в запрос левые SQL подобные строки), нужно перечислять все доступные варианты
}

// Ограничение на количество комнат
$strAmountOfRooms = "";
if (count($amountOfRooms) != "0") {
    $strAmountOfRooms = " (";
    for ($i = 0; $i < count($amountOfRooms); $i++) {
        $strAmountOfRooms .= " amountOfRooms = '" . $amountOfRooms[$i] . "'";
        if ($i < count($amountOfRooms) - 1) $strAmountOfRooms .= " OR";
    }
    $strAmountOfRooms = " )";
}

// Ограничение на смежность комнат
$strAdjacentRooms = "";
if ($adjacentRooms == "0") $strAdjacentRooms = "";
if ($adjacentRooms == "не имеет значения") $strAdjacentRooms = "";
if ($adjacentRooms == "только изолированные") $strAdjacentRooms = " (adjacentRooms != 'да')";

// Ограничение на этаж
$strFloor = "";
if ($floor == "0") $strFloor = "";
if ($floor == "любой") $strFloor = "";
if ($floor == "не первый") $strFloor = " (floor != '1')";
if ($floor == "не первый и не последний") $strFloor = " (floor != '1' AND floor != totalAmountFloor)";

// Ограничение на мебель
/*Работает только после извлечения и получения данных, так как в БД данные содержатся в BLOB нечитаемом формате!!!!!!!!!!!!!!!!!!!!!
$strFurniture = "";
if ($furniture == "0") $strFurniture = "";
if ($furniture == "не имеет значения") $strFurniture = "";
if ($furniture == "с мебелью и быт. техникой") $strFurniture = ;
if ($furniture == "без мебели") $strFurniture = ;*/

// Ограничение на минимальную сумму арендной платы
$strMinCost = "";
if ($minCost == "") $strMinCost = "";
if ($minCost != "") $strMinCost =  " (costOfRenting >= '" . $minCost . "')";

// Ограничение на максимальную сумму арендной платы
$strMaxCost = "";
if ($maxCost == "") $strMaxCost = "";
if ($maxCost != "") $strMaxCost =  " (costOfRenting <= '" . $maxCost . "')";

// Ограничение на максимальный залог
$strPledge = "";
if ($pledge == "") $strPledge = "";
if ($pledge != "") $strPledge =  " (bailCost <= '" . $pledge . "')";

// Ограничение на предоплату
/*Нужно менять формат указания предоплаты при формировании объявлений, чтобы их можно было численно сравнивать!!!!!!!!!!!!!!!!!!!!!
prepayment
prepayment
prepayment
prepayment
*/

// Ограничение на район
/*Работает только после извлечения и получения данных, так как в БД данные содержатся в BLOB нечитаемом формате!!!!!!!!!!!!!!!!!!!!!
district
district
district
district
district*/

// Ограничение на формат проживания (с кем собираетесь проживать)
/*Работает только после извлечения и получения данных, так как в БД данные содержатся в BLOB нечитаемом формате!!!!!!!!!!!!!!!!!!!!!
Кроме того, если проживать = Один, то нужно смотреть еще не совпадение полов!!!!!!!!!!!!!!!!!!!!!
withWho
withWho
withWho
withWho
withWho
*/

// Ограничение на проживание с детьми
$strChildren = "";
if ($children == "0" || $children == "без детей") $strChildren = "";
if ($children == "с детьми старше 4-х лет") $strChildren = " (children != 'только без детей')";
if ($children == "с детьми младше 4-х лет") $strChildren = " (children != 'только без детей' AND children != 'с детьми старше 4-х лет')";

// Ограничение на проживание с животными
$strAnimals = "";
if ($animals == "0" || $animals == "без животных") $strAnimals = "";
if ($animals == "с животным(ми)") $strAnimals = " (animals != 'только без животных')";

// Ограничение на длительность аренды
$strTermOfLease = "";
if ($termOfLease == "0") $strTermOfLease = "";
if ($termOfLease == "длительный срок") $strTermOfLease = " (termOfLease == 'длительный срок')";
if ($termOfLease == "несколько месяцев") $strTermOfLease = " (termOfLease == 'несколько месяцев')";

// Собираем поисковый запрос
$rowPropertyArr = array(); // в итоге получаем массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
$rezProperty = mysql_query("SELECT * FROM property WHERE" . . " (status = 'опубликовано') ORDER BY costOfRenting";
"userId = '" . $rowUsers['id'] . "'");

for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
    $rowPropertyArr[] = mysql_fetch_assoc($rezProperty);
}


/***************************************************************************************************************
 * Формируем список всех объявлений по недвижимости, которые соответствуют запросу и имеют статус "опубликовано"
 **************************************************************************************************************/

// Шаблон для всплывающего баллуна с описанием объекта недвижимости на карте Яндекса
$tmpl_balloonContentBody = "
<div class='headOfBalloon'>ул. Ленина 13</div>
<div class='fotosWrapper'>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src=''>
    </div>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src=''>
    </div>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src=''>
    </div>
</div>
<ul class='listDescription'>
    <li>
        <span class='headOfString'>Тип:</span> Квартира
    </li>
    <li>
        <span class='headOfString'>Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
    </li>
    <li>
        <span class='headOfString'>Единовременная комиссия:</span><a href='#'> 3000 руб. (40%) собственнику</a>
    </li>
    <li>
        <span class='headOfString'>Адрес:</span> улица Посадская 51
    </li>
    <li>
        <span class='headOfString'>Количество комнат:</span> 2, смежные
    </li>
    <li>
        <span class='headOfString'>Площадь (жилая/общая):</span> 22.4/34 м²
    </li>
    <li>
        <span class='headOfString'>Этаж:</span> 3 из 10
    </li>
    <li>
        <span class='headOfString'>Срок сдачи:</span> долгосрочно
    </li>
    <li>
        <span class='headOfString'>Мебель:</span> есть
    </li>
    <li>
        <span class='headOfString'>Район:</span> Центр
    </li>
    <li>
        <span class='headOfString'>Телефон собственника:</span> 89221431615, <a href='#'>Алексей Иванович</a>
    </li>
</ul>
<div class='clearBoth'></div>
<div style='width:100%;'>
    <a href='descriptionOfObject.html'>Подробнее</a>
    <div style='float: right; cursor: pointer;'>
        <div class='blockOfIcon'>
            <a><img class='icon' title='Добавить в избранное' src='img/blue_star.png'></a>
        </div>
        <a id='addToFavorit'> добавить в избранное</a>
    </div>
</div>
";

// Шаблон для блока с кратким описанием объекта недвижимости в таблице
$tmpl_MyAdvert = "
<tr class='realtyObject' coordX='56.836396' coordY='60.588662' balloonContentBody='{balloonContentBody}'>
    <td>
	    <div class='numberOfRealtyObject'>1</div>
	    <div class='blockOfIcon'>
		    <a><img class='icon' title='Добавить в избранное' src='img/blue_star.png'></a>
	    </div>
	</td>
	<td>
	    <div class='fotosWrapper resultSearchFoto'>
		    <div class='middleFotoWrapper'>
			    <img class='middleFoto' src=''>
			</div>
		</div>
    </td>
	<td>ул. Ленина 13
	    <div class='linkToDescriptionBlock'>
		<a class='linkToDescription' href='objdescription.php'>Подробнее</a>
		</div>
	</td>
	<td>15000</td>
</tr>
";

// Наполняем шаблон для баллуна данными для каждого из объявлений. Ограничим количество объектов недвижимости, выводимых по запросу пользователя 100 штуками - все, соответствующие запросу в порядке увеличения цены. Большее количество требует изменения механизма работы. Например выдавать сначала только координаты, а остальные сведения по клику на объявлении. К тому же пользователь всегда может уточнить поиск и уложиться в этот лимит
// Найдем все объявления, соответствующие запросу. Но максимум - 100 шт, чтобы не перегружать сервер и клиента.
//$rez = mysql_query("SELECT * FROM property WHERE city = '" .  . "' AND id = '" .  . "'");


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
                                    <option value="квартира" <?php if ($typeOfObject == "квартира") echo "selected";?>>квартира</option>
                                    <option value="комната" <?php if ($typeOfObject == "комната") echo "selected";?>>комната</option>
                                    <option value="дом" <?php if ($typeOfObject == "дом") echo "selected";?>>дом, коттедж</option>
                                    <option value="таунхаус" <?php if ($typeOfObject == "таунхаус") echo "selected";?>>таунхаус</option>
                                    <option value="дача" <?php if ($typeOfObject == "дача") echo "selected";?>>дача</option>
                                    <option value="гараж" <?php if ($typeOfObject == "гараж") echo "selected";?>>гараж</option>
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
								<input type="text" name="minCostFast" size="10" maxlength="8" <?php echo "value='$minCost'";?>>
								до
								<input type="text" name="maxCostFast" size="10" maxlength="8" <?php echo "value='$maxCost'";?>>
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
                                                if ($value == "1") { echo "checked"; break; }
                                            }
                                            ?>>
                                        1
                                        <input type="checkbox" value="2"
                                               name="amountOfRooms[]" <?php
                                            foreach ($amountOfRooms as $value) {
                                                if ($value == "2") { echo "checked"; break; }
                                            }
                                            ?>>
                                        2
                                        <input type="checkbox" value="3"
                                               name="amountOfRooms[]" <?php
                                            foreach ($amountOfRooms as $value) {
                                                if ($value == "3") { echo "checked"; break; }
                                            }
                                            ?>>
                                        3
                                        <input type="checkbox" value="4"
                                               name="amountOfRooms[]" <?php
                                            foreach ($amountOfRooms as $value) {
                                                if ($value == "4") { echo "checked"; break; }
                                            }
                                            ?>>
                                        4
                                        <input type="checkbox" value="5"
                                               name="amountOfRooms[]" <?php
                                            foreach ($amountOfRooms as $value) {
                                                if ($value == "5") { echo "checked"; break; }
                                            }
                                            ?>>
                                        5
                                        <input type="checkbox" value="6"
                                               name="amountOfRooms[]" <?php
                                            foreach ($amountOfRooms as $value) {
                                                if ($value == "6") { echo "checked"; break; }
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
                                            <option value="не имеет значения" <?php if ($adjacentRooms == "не имеет значения") echo "selected";?>>не имеет значения</option>
                                            <option value="только изолированные" <?php if ($adjacentRooms == "только изолированные") echo "selected";?>>только изолированные
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
                                            <option value="не первый и не последний" <?php if ($floor == "не первый и не последний") echo "selected";?>>не первый и не
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
                                            <option value="не имеет значения" <?php if ($furniture == "не имеет значения") echo "selected";?>>не имеет значения</option>
                                            <option value="с мебелью и быт. техникой" <?php if ($furniture == "с мебелью и быт. техникой") echo "selected";?>>с мебелью и быт. техникой</option>
                                            <option value="без мебели" <?php if ($furniture == "без мебели") echo "selected";?>>без мебели</option>
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
                                                        if ($valueDistrict == $value) { echo "checked"; break; }
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
                                        <option value="семейная пара" <?php if ($withWho == "семейная пара") echo "selected";?>>семейная пара</option>
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
                                        <option value="с детьми младше 4-х лет" <?php if ($children == "с детьми младше 4-х лет") echo "selected";?>>с детьми
                                            младше 4-х лет
                                        </option>
                                        <option value="с детьми старше 4-х лет" <?php if ($children == "с детьми старше 4-х лет") echo "selected";?>>с детьми
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
                                        <option value="без животных" <?php if ($animals == "без животных") echo "selected";?>>без животных</option>
                                        <option value="с животным(ми)" <?php if ($animals == "с животным(ми)") echo "selected";?>>с животным(ми)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="searchItem">
                                <span class="searchItemLabel">Срок аренды:</span>
                                <div class="searchItemBody">
                                    <select name="termOfLease" id="termOfLease">
                                        <option value="0" <?php if ($termOfLease == "0") echo "selected";?>></option>
                                        <option value="длительный срок" <?php if ($termOfLease == "длительный срок") echo "selected";?>>длительный срок (от года)</option>
                                        <option value="несколько месяцев" <?php if ($termOfLease == "несколько месяцев") echo "selected";?>>несколько месяцев (до года)</option>
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
						</div><!-- /end.tabs-2 -->
					</div><!-- /end.tabs -->
				</div><!-- /end.wrapperOfTabs -->

				<div class="choiceViewSearchResult">
					<span id="expandList"><a href="#">Список</a>&nbsp;&nbsp;&nbsp;</span><span id="listPlusMap"><a href="#">Список + карта</a>&nbsp;&nbsp;&nbsp;</span><span id="expandMap"><a href="#">Карта</a></span>
				</div>
				<div id="resultOnSearchPage" style="height: 100%;">

					<!-- Информация об объектах, подходящих условиям поиска -->
					<table class="listOfRealtyObjects" id="shortListOfRealtyObjects">
						<tbody>

							<tr class="realtyObject" coordX="56.836396" coordY="60.588662" balloonContentBody='<div class="headOfBalloon">ул. Ленина 13</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
								<td>
								<div class="numberOfRealtyObject">
									1
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Ленина 13
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 15000 </td>
							</tr>
							<tr class="realtyObject" coordX="56.819927" coordY="60.539264" balloonContentBody='<div class="headOfBalloon">ул. Репина 105</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
								<td>
								<div class="numberOfRealtyObject">
									2
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Репина 105
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 35000 </td>
							</tr>
							<tr class="realtyObject" coordX="56.817405" coordY="60.558452" balloonContentBody='<div class="headOfBalloon">ул. Шаумяна 107</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
								<td>
								<div class="numberOfRealtyObject">
									3
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Шаумяна 107
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 150000 </td>
							</tr>
							<tr class="realtyObject" coordX="56.825483" coordY="60.57357" balloonContentBody='<div class="headOfBalloon">ул. Гурзуфская 38</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
								<td>
								<div class="numberOfRealtyObject">
									123
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Гурзуфская 38
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 6000 </td>
							</tr>
							<tr class="realtyObject" coordX="56.820769" coordY="60.560742" balloonContentBody='<div class="headOfBalloon">ул. Серафимы Дерябиной 17</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
								<td>
								<div class="numberOfRealtyObject">
									1254
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Серафимы Дерябиной 17
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 2000 </td>
							</tr>
							<tr class="realtyObject" coordX="56.820769" coordY="60.560742" balloonContentBody='<div class="headOfBalloon">ул. Серафимы Дерябиной 17</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
								<td>
								<div class="numberOfRealtyObject">
									12
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Серафимы Дерябиной 17
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 350000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="numberOfRealtyObject">
									15
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>улица Сибирский тракт 50 летия 107
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 15000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="numberOfRealtyObject">
									15
								</div>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Сумасранка 4
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 35000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Серафимы Дерябиной 154
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 150000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Белореченская 24
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 6000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Маврода 2012
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 2000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Пискуна 1
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 350000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>улица Сибирский тракт 50 летия 107
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 15000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Сумасранка 4
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 35000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Серафимы Дерябиной 154
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 150000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Белореченская 24
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 6000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Маврода 2012
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 2000 </td>
							</tr>
							<tr class="realtyObject">
								<td>
								<div class="blockOfIcon">
									<a><img class="icon" src="img/blue_star.png"></a>
								</div></td>
								<td>
								<div class="fotosWrapper resultSearchFoto">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div></td>
								<td>ул. Пискуна 1
								<div class="linkToDescriptionBlock">
									<a class="linkToDescription" href="objdescription.php">Подробнее</a>
								</div></td>
								<td> 350000 </td>
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
									<th> Фото </th>
									<th> Адрес </th>
									<th> Район </th>
									<th> Комнат </th>
									<th> Площадь </th>
									<th> Этаж </th>
									<th class="top right"> Цена, руб. </th>
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
									</div></td>
									<td>
									<div class="fotosWrapper resultSearchFoto">
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
									</div></td>
									<td>ул. Серафимы Дерябиной 17</td>
									<td> ВИЗ </td>
									<td> 2 </td>
									<td> 22.4/34 </td>
									<td> 2/13</td>
									<td> 15000 </td>
								</tr>
								<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
									<td>
									<div class="numberOfRealtyObject">
										15
									</div>
									<div class="blockOfIcon">
										<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
									</div></td>
									<td>
									<div class="fotosWrapper resultSearchFoto">
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
									</div></td>
									<td>ул. Гурзуфская 38</td>
									<td> ВИЗ </td>
									<td> 2 </td>
									<td> 22.4/34 </td>
									<td> 2/13</td>
									<td> 15000 </td>
								</tr>
								<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
									<td>
									<div class="numberOfRealtyObject">
										15
									</div>
									<div class="blockOfIcon">
										<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
									</div></td>
									<td>
									<div class="fotosWrapper resultSearchFoto">
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
									</div></td>
									<td>ул. Шаумяна 107</td>
									<td> ВИЗ </td>
									<td> 2 </td>
									<td> 22.4/34 </td>
									<td> 2/13</td>
									<td> 15000 </td>
								</tr>
								<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
									<td>
									<div class="numberOfRealtyObject">
										15
									</div>
									<div class="blockOfIcon">
										<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
									</div></td>
									<td>
									<div class="fotosWrapper resultSearchFoto">
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
									</div></td>
									<td>ул. Репина 105</td>
									<td> ВИЗ </td>
									<td> 2 </td>
									<td> 22.4/34 </td>
									<td> 2/13</td>
									<td> 15000 </td>
								</tr>
								<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
									<td>
									<div class="blockOfIcon">
										<a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a>
									</div></td>
									<td>
									<div class="fotosWrapper resultSearchFoto">
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
										<div class="middleFotoWrapper">
											<img class="middleFoto" src="">
										</div>
									</div></td>
									<td>ул. Ленина 13</td>
									<td> ВИЗ </td>
									<td> 2 </td>
									<td> 22.4/34 </td>
									<td> 2/13</td>
									<td> 15000 </td>
								</tr>
							</tbody>
						</table>
					</div>
				</div><!-- /end.resultOnSearchPage -->

			</div><!-- /end.page_main_content -->
			<!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
			<div class="page-buffer"></div>
		</div><!-- /end.page_without_footer -->
		<div class="footer">
			2012 «Хани Хом», вопросы и пожелания по работе портала можно передавать по телефону 8-922-143-16-15
		</div><!-- /end.footer -->

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
