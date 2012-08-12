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

		<title>Как мы работаем с арендатором</title>
		<meta name="description" content="Как мы работаем с арендатором">

		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="initialscale=1.0, width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

		<link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
		<link rel="stylesheet" href="css/main.css">
		<style>
			#registration {
				margin-top: 7px;
				margin-left: 15px;
			}
			
			/* Стиль для панели табов - ul*/
			/* Важно, что пока он плавающий и основной текст из .ui-tabs-panel его обтекает*/
			.ui-tabs-vertical .ui-tabs-nav {
				padding: .2em .1em .2em .2em;
				float: left;
			}

			/* Стиль для не выбранного таба - элемента li*/
			.ui-tabs-vertical .ui-tabs-nav li {
				clear: left;
				width: 100%;
				border-bottom-width: 1px !important;
				border-right-width: 0 !important;
				margin: 0px 0px 0px 0px;
				padding: 0;
				cursor: pointer;
			}
			.ui-tabs-vertical .ui-tabs-nav li a {
				display: block;
			}
			/* Стиль для текущего выбранного таба - элемента li*/
			.ui-tabs-vertical .ui-tabs-nav li.ui-tabs-selected {
				margin: 0px 0px 0px 0px;
				padding: 0;
				cursor: text;
			}
			/* Стиль для основного текста, отображаемого справа от табов*/
			.ui-tabs-vertical .ui-tabs-panel {
				padding: 1em;
				overflow: auto;
			}

			a.button {
				margin: 14px 0px 0px 50px;
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

				<div class="wrapperOfTabs">
					<div class="headerOfPage">
						Современный способ снять недвижимость - с Хани Хом за 6 шагов
					</div>
					<div id="tabs">
						<ul>
							<li>
								<a href="#tabs-1">1. Регистрация</a>
							</li>
							<li>
								<a href="#tabs-2">2. Подбор недвижимости</a>
							</li>
							<li>
								<a href="#tabs-3">3. Просмотр</a>
							</li>
							<li>
								<a href="#tabs-4">4. Договор</a>
							</li>
							<li>
								<a href="#tabs-5">5. Расчет</a>
							</li>
							<li>
								<a href="#tabs-6">6. Успех!</a>
							</li>
						</ul>
						<div id="tabs-1">
							<p>
								Зарегистрируйтесь на портале Хани Хом (воспользовавшись кнопкой ниже на этом экране). При регистрации укажите данные о себе и параметры недвижимости, которую ищете. Чем полезна регистрация для Ваших целей:
								<ul>
									<li>
										<p>
											Портал запомнит введенные Вами условия поиска, и при каждом следующем входе система автоматически будет их подставлять, экономя Ваше время и делая жизнь немного комфортнее.
										</p>
										<p>
											Кроме того, портал будет отслеживать появление новых объектов, соответствующих Вашим условиям, и сообщать о них с помощью специального раздела Личного кабинета (Новости)
										</p>
									</li>
									<li>
										<p>
											Максимально подробные и честные сведения о себе, которые Вы укажете на портале, позволят избежать лишних выездов и просмотров тех объектов, чьи собственники выставляют к арендаторам особенные требования (например: без животных, без детей, национальность, женатые пары...).
										</p>
										<p>
											Опять же подробность и честность при указании информации о себе позволит съэкономить время и Вам, и собственникам.
										</p>
										<p>
											Собственники склонны сдавать недвижимость тем людям, о которых они знают больше информации, в открытости и порядочности которых, они могут быть в большей степени уверены.
										</p>
									</li>
								</ul>
							</p>
						</div>
						<div id="tabs-2">
							<p>
								С помощью специального раздела <a href="search.php">Поиск недвижимости</a> Вы можете просмотреть текущие доступные для аренды объекты, узнать максимально подробные сведения по каждому из них. Вся информация, публикуемая на портале, проверяется нашими специалистами.
							</p>
							<p>
								Понравившиеся объекты Вы можете добавлять в Избранные. Все избранные объявления доступны в личном кабинете.
							</p>
							<p>
								Если не удалось найти нужный объект с первого раза - не расстраивайтесь, периодически проверяйте наличие новых объектов, соответствующих Вашим условиям в личном кабинете (раздел Новости).
							</p>
							<p>
								Мы рекомендуем звонить собственнику сразу же, как только Вы найдете объект по душе. Иначе это сделает кто-то другой.
							</p>
						</div>
						<div id="tabs-3">
							<p>
								Свяжитесь с собственником напрямую по телефону, указанному на портале, это позволит:
								<ul>
									<li>
										<p>
											Выяснить, уточнить все моменты, которые могли еще остаться для Вас не ясными.
										</p>
									</li>
									<li>
										<p>
											Договориться о встрече и демонстрации недвижимости в удобное для Вас и собственника время. В отличие от обычных агентств, при работе с нами Вы не зависите от времени наших сотрудников, так как общаетесь напрямую с собственниками.
										</p>
									</li>
								</ul>
							</p>
						</div>
						<div id="tabs-4">
							<p>
								Если демонстрация объекта прошла успешно: Вам он понравился, а собственник остался доволен Вами, то на этой же встрече можно будет подписать договор аренды и стать полноценным арендатором понравившегося объекта.
							</p>
							<p>
								Обязательно возьмите с собой на встречу паспорта всех, кто будет проживать, и деньги - про них подробнее следующий пункт.
							</p>
						</div>
						<div id="tabs-5">
							<p>
								Услуги нашего портала для арендаторов бесплатны. Но после заключения договора аренды Вам нужно будет выплатить единоразовую комиссию собственнику - 40% от месячной стоимости недвижимости. Размер комиссии для каждого объекта вычисляется заранее и показывается при просмотре объявлений.
							</p>
							<p>
								Такая единоразовая выплата компенсирует расходы собственника на услуги нашей компании, и позволяет нам совместно с собственниками предоставлять качественный быстрый и комфортный сервис по поиску недвижимости для аренды.
							</p>
							<p>
								В любом случае, данная комиссия выплачивается только по факту заселения и она меньше, чем в любом обычном агентстве.
							</p>
							<p>
								Конечно же после подписания договора аренды, Вам нужно будет заплатить собственнику стоимость проживания за первый месяц.
							</p>
							<p>
								Если в описании объекта говорится о залоге, то будьте готовы выплатить также и его стоимость. Как правило сумма залога возвращается при выезде из объекта, либо идет в счет погашения стоимости последнего месяца проживания.
							</p>
						</div>
						<div id="tabs-6">
							<p>
								Поздравляем, Вы подобрали себе комфортное жилье с минимальной комиссией и минимальными затратами Вашего времени на поиск и просмотр объектов с помощью Хани Хом!
							</p>
						</div>
					</div>
				</div>

				<button id="registration">Зарегистрироваться</button>
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
		<script src="js/fortenant.js"></script>
		<script>
			$("#registration").on('click', function() {
				window.open('registrationForm.html?type=tenant');
			});
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
