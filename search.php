<?php
include_once 'lib/out.php';
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

		<title>Поиск недвижимости в аренду</title>
		<meta name="description" content="Страница поиска объявлений">

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

		<!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

		<!-- All JavaScript at the bottom, except this Modernizr build.
		Modernizr enables HTML5 elements & feature detects for optimal performance.
		Create your own custom Modernizr build: www.modernizr.com/download/ -->
		<script src="js/vendor/modernizr-2.5.3.min.js"></script>

	</head>
	<body>
		<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
		chromium.org/developers/how-tos/chrome-frame-getting-started -->
		<!--[if lt IE 7]><p class="chromeframe">Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->

		<!-- Add your site or application content here -->
		<div class="page_without_footer">

        <!-- Сформируем и вставим заголовок страницы -->
        <?php
             include("lib/header.php");
         ?>

			<div class="page_main_content">
				<div class="headerOfPage">
					Найдите подходящие Вам объявления
				</div>
				<div class="wrapperOfTabs">
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
									<option value="flat" selected>квартиру</option>
									<option value="room">комнату</option>
									<option value="house">дом, коттедж</option>
									<option value="townhouse">таунхаус</option>
									<option value="dacha">дачу</option>
									<option value="garage">гараж</option>
								</select> в районе
								<select  name="12" style="width: 150px;">
									<option value="0" selected> любой </option>
									<option value="1"> Автовокзал (южный) </option>
									<option value="2"> Академический </option>
									<option value="3"> Ботанический </option>
									<option value="4"> ВИЗ </option>
									<option value="5"> Вокзальный </option>
									<option value="6"> Втузгородок </option>
									<option value="7"> Горный щит </option>
									<option value="8"> Елизавет </option>
									<option value="9"> ЖБИ </option>
									<option value="10"> Завокзальный </option>
									<option value="11"> Заречный </option>
									<option value="12"> Изоплит </option>
									<option value="13"> Исток </option>
									<option value="14"> Калиновский </option>
									<option value="15"> Кольцово </option>
									<option value="16"> Компрессорный </option>
									<option value="17"> Лечебный </option>
									<option value="18"> Медный </option>
									<option value="19"> Нижнеисетский </option>
									<option value="20"> Парковый </option>
									<option value="21"> Пионерский </option>
									<option value="22"> Птицефабрика </option>
									<option value="23"> Семь ключей </option>
									<option value="24"> Сибирский тракт </option>
									<option value="25"> Синие камни </option>
									<option value="26"> Совхозный </option>
									<option value="27"> Сортировка новая </option>
									<option value="28"> Сортировка старая </option>
									<option value="29"> Уктус </option>
									<option value="30"> УНЦ </option>
									<option value="31"> Уралмаш </option>
									<option value="32"> Химмаш </option>
									<option value="33"> Центр </option>
									<option value="34"> Чермет </option>
									<option value="35"> Шарташ </option>
									<option value="36"> Широкая речка </option>
									<option value="37"> Эльмаш </option>
									<option value="38"> Юго-запад </option>
									<option value="39"> За городом </option>
								</select> стоимостью от
								<input type="text" size="10" value="0">
								до
								<input type="text" size="10">
								руб./мес.
								&nbsp;
								<button id="fastSearchButton">
									Найти
								</button> </span>
						</div>
						<div id="tabs-2">
							<form id="searchConditions">
								<div id="extendedSearchParametersBlock">
									<div id="leftBlockOfSearchParameters">
										<fieldset class="edited">
											<legend>
												Характеристика объекта
											</legend>
											<div class="searchItem">
												<span class="searchItemLabel"> Тип: </span>
												<div class="searchItemBody">
													<select name="typeOfObject">
														<option value="flat" selected>квартира</option>
														<option value="room">комната</option>
														<option value="house">дом, коттедж</option>
														<option value="townhouse">таунхаус</option>
														<option value="dacha">дача</option>
														<option value="garage">гараж</option>
													</select>
												</div>
											</div>
											<div class="searchItem">
												<span class="searchItemLabel"> Количество комнат: </span>
												<div class="searchItemBody">
													<input type="checkbox" value="1" name="amountOfRooms">
													1
													<input type="checkbox" value="2" name="amountOfRooms">
													2
													<input type="checkbox" value="3" name="amountOfRooms">
													3
													<input type="checkbox" value="4" name="amountOfRooms">
													4
													<input type="checkbox" value="5" name="amountOfRooms">
													5
													<input type="checkbox" value="6" name="amountOfRooms">
													6...
												</div>
											</div>
											<div class="searchItem">
												<span class="searchItemLabel"> Комнаты смежные: </span>
												<div class="searchItemBody">
													<select name="adjacentRooms">
														<option value="1" selected>не имеет значения</option>
														<option value="2">только изолированные</option>
													</select>
												</div>
											</div>
											<div class="searchItem">
												<span class="searchItemLabel"> Этаж: </span>
												<div class="searchItemBody">
													<select name="floor">
														<option value="1" selected>любой</option>
														<option value="2">не первый</option>
														<option value="3">не первый и не последний</option>
													</select>
												</div>
											</div>
											<div>
												<input type="checkbox">
												С мебелью и бытовой техникой
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
													<input type="text" name="minCost" size="10">
													руб., до
													<input type="text" name="maxCost" size="10">
													руб.
												</div>
											</div>
											<div class="searchItem">
												<span class="searchItemLabel"> Залог </span>
												<div class="searchItemBody">
													до
													<input type="text" name="maxCost" size="10">
													руб.
												</div>
											</div>
										</fieldset>
									</div>
									<div id="rightBlockOfSearchParameters">
										<fieldset style="height: 100%;">
											<legend>
												Район
											</legend>
											<div class="searchItem" style="height: 100%;">
												<div class="searchItemBody" style="height: 95%; width:200px; overflow-y: auto">
													<input type="checkbox" name="district" value="1">
													Автовокзал (южный)
													<br>
													<input type="checkbox" name="district" value="2">
													Академический
													<br>
													<input type="checkbox" name="district" value="3">
													Ботанический
													<br>
													<input type="checkbox" name="district" value="4">
													ВИЗ
													<br>
													<input type="checkbox" name="district" value="5">
													Вокзальный
													<br>
													<input type="checkbox" name="district" value="6">
													Втузгородок
													<br>
													<input type="checkbox" name="district" value="7">
													Горный щит
													<br>
													<input type="checkbox" name="district" value="8">
													Елизавет
													<br>
													<input type="checkbox" name="district" value="9">
													ЖБИ
													<br>
													<input type="checkbox" name="district" value="10">
													Завокзальный
													<br>
													<input type="checkbox" name="district" value="11">
													Заречный
													<br>
													<input type="checkbox" name="district" value="12">
													Изоплит
													<br>
													<input type="checkbox" name="district" value="13">
													Исток
													<br>
													<input type="checkbox" name="district" value="14">
													Калиновский
													<br>
													<input type="checkbox" name="district" value="15">
													Кольцово
													<br>
													<input type="checkbox" name="district" value="16">
													Компрессорный
													<br>
													<input type="checkbox" name="district" value="17">
													Лечебный
													<br>
													<input type="checkbox" name="district" value="18">
													Медный
													<br>
													<input type="checkbox" name="district" value="19">
													Нижнеисетский
													<br>
													<input type="checkbox" name="district" value="20">
													Парковый
													<br>
													<input type="checkbox" name="district" value="21">
													Пионерский
													<br>
													<input type="checkbox" name="district" value="22">
													Птицефабрика
													<br>
													<input type="checkbox" name="district" value="23">
													Семь ключей
													<br>
													<input type="checkbox" name="district" value="24">
													Сибирский тракт
													<br>
													<input type="checkbox" name="district" value="25">
													Синие камни
													<br>
													<input type="checkbox" name="district" value="26">
													Совхозный
													<br>
													<input type="checkbox" name="district" value="27">
													Сортировка новая
													<br>
													<input type="checkbox" name="district" value="28">
													Сортировка старая
													<br>
													<input type="checkbox" name="district" value="29">
													Уктус
													<br>
													<input type="checkbox" name="district" value="30">
													УНЦ
													<br>
													<input type="checkbox" name="district" value="31">
													Уралмаш
													<br>
													<input type="checkbox" name="district" value="32">
													Химмаш
													<br>
													<input type="checkbox" name="district" value="33">
													Центр
													<br>
													<input type="checkbox" name="district" value="34">
													Чермет
													<br>
													<input type="checkbox" name="district" value="35">
													Шарташ
													<br>
													<input type="checkbox" name="district" value="36">
													Широкая речка
													<br>
													<input type="checkbox" name="district" value="37">
													Эльмаш
													<br>
													<input type="checkbox" name="district" value="38">
													Юго-запад
													<br>
													<input type="checkbox" name="district" value="39">
													За городом
													<br>
												</div>
											</div>
										</fieldset>
									</div>
									<fieldset class="edited private" style="display: inline-block;">
										<legend>
											Особые параметры поиска
										</legend>
										<div class="searchItem">
											<span class="searchItemLabel">Как собираетесь проживать: </span>
											<div class="searchItemBody">
												<select name="withWho" id="withWho">
													<option value="1" selected>один</option>
													<option value="2">семейная пара</option>
													<option value="3">несемейная пара</option>
													<option value="4">со знакомыми</option>
												</select>
											</div>
										</div>
										<div class="searchItem">
											<span class="searchItemLabel">Дети: </span>
											<div class="searchItemBody">
												<select name="children" id="children">
													<option value="0" selected>без детей</option>
													<option value="1">с детьми младше 4-х лет</option>
													<option value="2">с детьми старше 4-х лет</option>
												</select>
											</div>
										</div>
										<div class="searchItem">
											<span class="searchItemLabel">Животные: </span>
											<div class="searchItemBody">
												<select name="animals" id="animals">
													<option value="0" selected>без животных</option>
													<option value="1">с животным(ми)</option>
												</select>
											</div>
										</div>
									</fieldset>
								</div>
								<div class="actionsOnSearch">
									<a href="#">Запомнить условия поиска</a>
									<button id="extendedSearchButton">
										Найти
									</button>
								</div>
								<div class="clearBoth"></div>
							</form>
						</div>
					</div>
				</div>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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
									<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
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

		<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

		<!-- jQuery UI с моей темой оформления -->
		<script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>

		<!-- Загружаем библиотеку для работы с картой от Яндекса -->
		<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

		<!-- scripts concatenated and minified via build script -->
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
