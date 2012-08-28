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

		<title>Пользователь</title>
		<meta name="description" content="Описание пользователя портала">

		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="initialscale=1.0, width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

		<link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
		<link rel="stylesheet" href="css/main.css">
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
						Личная страница пользователя: <span>Ушаков Дмитрий Владимирович</span>
					</div>
					<div id="tabs">
						<ul>
							<li>
								<a href="#tabs-1">Профиль</a>
							</li>
							<li>
								<a href="#tabs-2">Условия поиска</a>
							</li>
							<li>
								<a href="#tabs-3">Объявления</a>
							</li>
						</ul>
						<div id="tabs-1">
							<div class="fotosWrapper">
								<div class="bigFotoWrapper">
									<img class="bigFoto">
								</div>
								<div class="bigFotoWrapper">
									<img class="bigFoto">
								</div>
							</div>
							<div class="profileInformation">
								<ul class="listDescription">
									<li>
										<span class="FIO">Ушаков Дмитрий Владимирович</span>
									</li>
									<li>
										<br>
									</li>
									<li>
										<span class="headOfString">Образование:</span> УГТУ-УПИ, инженер автоматики и управления в информационных системах, закончил в 2009 г. причем с отличием
									</li>
									<li>
										<span class="headOfString">Работа:</span> СКБ Контур, менеджер проектов
									</li>
									<li>
										<span class="headOfString">Национальность:</span> русский
									</li>
									<li>
										<span class="headOfString">Пол:</span> мужской
									</li>
									<li>
										<span class="headOfString">День рождения:</span> 27.01.1987
									</li>
									<li>
										<span class="headOfString">Возраст:</span> 25
									</li>
									<li>
										<br>
									</li>
									<li>
										<span style="font-weight: bold;">Контакты:</span>
									</li>
									<li>
										<a href="#" id="showNumber">показать номер</a>
									</li>
									<li>
										<br>
									</li>
									<li>
										<span style="font-weight: bold;">Малая Родина:</span>
									</li>
									<li>
										<span class="headOfString">Город (населенный пункт):</span> Лысьва
									</li>
									<li>
										<span class="headOfString">Регион:</span> Пермский край
									</li>
									<li>
										<br>
									</li>
									<li>
										<span style="font-weight: bold;">Коротко о себе и своих интересах:</span>
									</li>
									<li>
										Я немного замкнутый перфекционист и вообще неадекватный человек, возьмите меня замуж или в жены, ха-ха-ха
									</li>
									<li>
										<br>
									</li>
									<li>
										<span style="font-weight: bold;">Страницы в социальных сетях:</span>
									</li>
									<li>
										<ul class="linksToAccounts">
											<li>
												<a href="http://vk.com/ushakovd">http://vk.com/ushakovd</a>
											</li>
											<li>
												<a href="http://vk.com/ushakovd">http://vk.com/ushakovd</a>
											</li>
											<li>
												<a href="http://vk.com/ushakovd">http://vk.com/ushakovd</a>
											</li>
										</ul>
									</li>
								</ul>
							</div>
							<div class="clearBoth"></div>
						</div><!-- /end.tabs-1 -->
						<div id="tabs-2">
							<div class="shadowText">
								Какого рода недвижимость ищет данный пользователь
							</div>
							<div id="notEditingSearchParametersBlock" class="objectDescription">
								<fieldset class="notEdited">
									<legend>
										Характеристика объекта
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Тип:</td>
												<td class="objectDescriptionBody"><span>квартира</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Количество комнат:</td>
												<td class="objectDescriptionBody"><span>1, 2</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Комнаты смежные:</td>
												<td class="objectDescriptionBody"><span>только изолированные</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Этаж:</td>
												<td class="objectDescriptionBody"><span>не первый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">С мебелью и бытовой техникой:</td>
												<td class="objectDescriptionBody"><span>нет</span></td>
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
												<td class="objectDescriptionBody"><span>0</span> руб.</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
												<td class="objectDescriptionBody"><span>20000</span> руб.</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Залог до:</td>
												<td class="objectDescriptionBody"><span>25000</span> руб.</td>
											</tr>
										</tbody>
									</table>
								</fieldset>
								<fieldset class="notEdited" id="additionalSearchDescription">
									<legend>
										Особые параметры поиска
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel" id="firstTableColumnSpecial">Как собирается проживать:</td>
												<td class="objectDescriptionBody"><span>семейная пара</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Ссылки на страницы сожителей:</td>
												<td class="objectDescriptionBody"><span><a href="#">http://vk.com/audio</a></span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Дети:</td>
												<td class="objectDescriptionBody"><span>С детьми младше 4-х лет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Количество детей и их возраст:</td>
												<td class="objectDescriptionBody"><span>2 ребенка по 2 и 6 лет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Животные:</td>
												<td class="objectDescriptionBody"><span>С животным(ми)</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Количество животных и их вид:</td>
												<td class="objectDescriptionBody"><span>1 кошка</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Ориентировочный срок аренды:</td>
												<td class="objectDescriptionBody"><span>долгосрочно</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
												<td class="objectDescriptionBody"><span>Хотелось бы жить рядом с парком: Зеленая роща или 50 лет ВЛКСМ, чтобы можно было по утрам бегать и заниматься спортом. У меня уже несколько олимпийских медалей и я хочу получить еще одну</span></td>
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
											<tr>
												<td class="objectDescriptionItemLabel">Академический</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Юго-Западный</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">ВИЗ</td>
											</tr>
										</tbody>
									</table>
								</fieldset>
							</div>
						</div><!-- /end.tabs-2 -->
						<div id="tabs-3">
							<div class="news advertForPersonalPage published">
								<div class="newsHeader">
									<span class="advertHeaderAddress">Квартира по улице Кирова 15, №3</span>
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
										89221431615, <a href="#">Алексей Иванович</a>
									</li>
								</ul>
								<div class="clearBoth"></div>
							</div>
						</div><!-- /end.tabs-3 -->
					</div>
				</div>
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

		<!-- scripts concatenated and minified via build script -->
		<script src="js/main.js"></script>

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
