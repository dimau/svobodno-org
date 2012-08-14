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

		<title>Описание объекта недвижимости</title>
		<meta name="description" content="Описание объекта недвижимости">

		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="initialscale=1.0, width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

		<link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
		<link rel="stylesheet" href="css/main.css">
		<style>
			/* Особые стили для блоков с описанием объекта - для выравнивания*/
			fieldset.notEdited {
				min-width: 45%;
			}

			/* Стили для контактов собственника */
			#showName, #showNumber, #addToFavorit {
				color: #1A238B;
				cursor: pointer;
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
                include("header.php");
            ?>

			<div class="page_main_content">

				<div class="wrapperOfTabs">
					<div class="headerOfPage">
						Подробное описание <span>квартиры</span> по адресу: <span>улица Гурзуфская 38</span>,
						<div class="blockOfIcon">
							<a><img class="icon" src="img/blue_star.png"></a>
						</div><a id="addToFavorit"> добавить в избранное</a>
					</div>
					<div id="tabs">
						<ul>
							<li>
								<a href="#tabs-1">Описание</a>
							</li>
							<li>
								<a href="#tabs-2">Местоположение</a>
							</li>
						</ul>
						<div id="tabs-1">
							<!-- Подробное описание объекта -->

							<div class="imagines">
								<div class="bigFotoWrapper">
									<img src="img/maxiDom.jpg" class="bigFoto">
								</div>
								<div class="bigFotoWrapper">
									<img src="img/miniDom.jpg" class="bigFoto">
								</div>
								<div class="bigFotoWrapper">
									<img class="bigFoto">
								</div>
								<div class="bigFotoWrapper">
									<img class="bigFoto">
								</div>
								<div class="bigFotoWrapper">
									<img class="bigFoto">
								</div>
							</div>

							<div class="objectDescription">

								<fieldset class="notEdited">
									<legend>
										Комнаты и помещения
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Количество комнат в квартире, доме:</td>
												<td class="objectDescriptionBody"><span>1</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Комнаты смежные:</td>
												<td class="objectDescriptionBody"><span>нет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Санузел:</td>
												<td class="objectDescriptionBody"><span>раздельный</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Балкон/лоджия:</td>
												<td class="objectDescriptionBody"><span>балкон</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Остекление балкона/лоджии:</td>
												<td class="objectDescriptionBody"><span>нет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Общая площадь:</td>
												<td class="objectDescriptionBody"><span>38</span> м²</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Жилая площадь:</td>
												<td class="objectDescriptionBody"><span>24</span> м²</td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Стоимость, условия оплаты
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Плата за аренду:</td>
												<td class="objectDescriptionBody"><span class="highlighted">18000</span> руб. в месяц</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Единовременная комиссия:</td>
												<td class="objectDescriptionBody"><span>7200</span> руб. <span>(40%)</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Коммунальные услуги
												<br>
												оплачиваются дополнительно:</td>
												<td class="objectDescriptionBody"><span>да,
													<br>
												</span>летом<span> 1500</span> руб. зимой<span> 2500 </span>руб.</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Электроэнергия
												<br>
												оплачивается дополнительно:</td>
												<td class="objectDescriptionBody"><span>нет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Залог:</td>
												<td class="objectDescriptionBody"><span>есть</span><span> 18000 </span>руб.</td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Этаж и подъезд
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Этаж:</td>
												<td class="objectDescriptionBody"><span>8</span> из <span>14</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Консьерж:</td>
												<td class="objectDescriptionBody"><span>нет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Домофон:</td>
												<td class="objectDescriptionBody"><span>есть, но не работает</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Парковка:</td>
												<td class="objectDescriptionBody"><span>стихийная</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Текущее состояние
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Ремонт:</td>
												<td class="objectDescriptionBody"><span>меньше 1 года назад</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Отделка (жилых помещений):</td>
												<td class="objectDescriptionBody"><span>косметическая
													<br>
													(новые обои, побелка потолков)</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Окна:</td>
												<td class="objectDescriptionBody"><span>стеклопакет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Санузел, отделка:</td>
												<td class="objectDescriptionBody"><span>кафель</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Половое покрытие в комнатах:</td>
												<td class="objectDescriptionBody"><span>ламинат</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Тип и сроки
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Тип объекта:</td>
												<td class="objectDescriptionBody"><span>квартира</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">С какого числа можно въезжать:</td>
												<td class="objectDescriptionBody"><span>01.09.2012</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">На какой срок сдается:</td>
												<td class="objectDescriptionBody"><span>долговременно</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Связь
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Интернет:</td>
												<td class="objectDescriptionBody"><span>не проведен, можно провести</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Телефон:</td>
												<td class="objectDescriptionBody"><span>не проведен, нельзя провести</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Кабельное ТВ:</td>
												<td class="objectDescriptionBody"><span>не проведен, нельзя провести</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Мебель и бытовая техника
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Наличие мебели и бытовой техники:</td>
												<td class="objectDescriptionBody"><span>cдается с мебелью
													<br>
													и бытовой техникой</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Диван:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Кресло-кровать:</td>
												<td class="objectDescriptionBody"><span>1</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Кресло:</td>
												<td class="objectDescriptionBody"><span>1</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Стул:</td>
												<td class="objectDescriptionBody"><span>1</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Табурет:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Стол письменный, компьютерный:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Стол журнальный:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Диван:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Стенка:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Шкаф для одежды:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Стол обеденный:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Кухонный гарнитур:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Шкаф для посуды:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Телевизор:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Холодильник:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Стиральная машина:</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Плита (газовая, электрическая):</td>
												<td class="objectDescriptionBody"><span>2</span>, состояние: <span>старый</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Требования к арендатору
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Пол:</td>
												<td class="objectDescriptionBody"><span>не имеет значения</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Отношения между арендаторами:</td>
												<td class="objectDescriptionBody"><span>семейная пара,
													<br>
													несемейная пара</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Национальность:</td>
												<td class="objectDescriptionBody"><span>русским</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Дети:</td>
												<td class="objectDescriptionBody"><span>не имеет значения</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Животные:</td>
												<td class="objectDescriptionBody"><span>только без животных</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Особые условия
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Частота проверки недвижимости собственником:</td>
												<td class="objectDescriptionBody"><span>1 раз в месяц (при получении оплаты)</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Ответственность собственника за состояние и ремонт объекта:</td>
												<td class="objectDescriptionBody"><span>Возмещает затраты на ремонт сантехники и бытовой техники, а также на ремонт телевизора и обоев</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Другие условия:</td>
												<td class="objectDescriptionBody"><span>Чтоб не пили, не курили и баб не водили. Нельзя проводить Дни рождения и другие пьянки так, чтобы соседи приходили и ходили в милицию полицию</td>
												</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

								<fieldset class="notEdited">
									<legend>
										Контакты собственника
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Имя:</td>
												<td class="objectDescriptionBody"><a href="man.php" class="highlighted">Дмитрий Владимирович</a></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Телефон:</td>
												<td class="objectDescriptionBody"><a id="showNumber">показать номер</a></td>
											</tr>
										</tbody>
									</table>
								</fieldset>

							</div>
						</div>
						<div id="tabs-2">
							<!-- Описание метоположения объекта -->

							<fieldset class="notEdited" style="float: left;">
								<table>
									<tbody>
										<tr>
											<td class="objectDescriptionItemLabel">Город:</td>
											<td class="objectDescriptionBody"><span>Екатеринбург</span></td>
										</tr>
										<tr>
											<td class="objectDescriptionItemLabel">Район:</td>
											<td class="objectDescriptionBody"><span>ВИЗ</span></td>
										</tr>
										<tr>
											<td class="objectDescriptionItemLabel">Адрес:</td>
											<td class="objectDescriptionBody"><span>улица Гурзуфская 38</span></td>
										</tr>
										<tr>
											<td class="objectDescriptionItemLabel">Станция метро рядом:</td>
											<td class="objectDescriptionBody"><span>Уралмаш</span>, <span>20</span> мин. ходьбы</td>
										</tr>
									</tbody>
								</table>
							</fieldset>
							<!-- Карта Яндекса --><div id="map" style="width: 500px; height: 500px; float: left;"></div>
							<div style="clear: both;"></div>
						</div>
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

		<!-- Загружаем библиотеку для работы с картой от Яндекса -->
		<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

		<!-- scripts concatenated and minified via build script -->
		<script src="js/main.js"></script>
		<script src="js/descriptionOfObject.js"></script>

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
