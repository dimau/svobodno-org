<?php
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

/***************************************************************************************************************
 * Получаем get параметры запроса или присваиваем им значения по умолчанию
 **************************************************************************************************************/

// TODO: тест Присваиваем тестовые значения переменным
$district = 12;
$typeOfObject = "дача";

if (isset($_GET['typeOfObject'])) $typeOfObject = htmlspecialchars($_POST['typeOfObject']);

/***************************************************************************************************************
 * Формируем список всех объявлений недвижимости, которые соответствуют запросу и имеют статус "опубликовано"
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
							<!-- Раздел для параметров поиска -->
							<span id="fastSearchInput"> Я хочу арендовать
								<select name="typeOfObject">
                                    <option value="квартира" <?php if ($typeOfObject == "квартира") echo "selected";?>>квартира</option>
                                    <option value="комната" <?php if ($typeOfObject == "комната") echo "selected";?>>комната</option>
                                    <option value="дом" <?php if ($typeOfObject == "дом") echo "selected";?>>дом, коттедж</option>
                                    <option value="таунхаус" <?php if ($typeOfObject == "таунхаус") echo "selected";?>>таунхаус
                                    </option>
                                    <option value="дача" <?php if ($typeOfObject == "дача") echo "selected";?>>дача</option>
                                    <option value="гараж" <?php if ($typeOfObject == "гараж") echo "selected";?>>гараж</option>
                                </select>
                                в районе
                                <select name="districtFastSearchInput">
                                    <option value="0"></option>
                                    <?php
                                    if (isset($allDistrictsInCity)) {
                                        foreach ($allDistrictsInCity as $key => $value) { // Для каждого идентификатора района и названия формируем пункт селекта
                                            echo "<option value='" . $key . "'";
                                            if ($key == $district[0]) echo "selected";
                                            echo ">" . $value . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
								стоимостью от
								<input type="text" size="10" value="0">
								до
								<input type="text" size="10">
								руб./мес.
								&nbsp;
								<button id="fastSearchButton">
									Найти
								</button>
                            </span>
						</div>
						<div id="tabs-2">
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
                                <span class="searchItemLabel">Предполагаемый срок аренды:</span>

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
