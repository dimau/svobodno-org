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

		<title>Хани Хом</title>
		<meta name="description" content="Аренда недвижимости">

		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="initialscale=1.0, width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

		<link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
		<link rel="stylesheet" href="css/main.css">
		<style>
			#descriptionBox {
				clear: both;
				padding-top: 10px;
				width: 100%;
				min-width: 703px;
			}

			.descriptionBlockHeader {
				font-family: Georgia, "Times New Roman", Times, serif;
				font-size: 1.3em;
				margin-bottom: 8px;
			}

			.descriptionBlock1 {
				float: left;
				width: 49.5%;
				text-align: center;
			}

			.descriptionBlock2 {
				float: right;
				width: 49.5%;
				text-align: center;
			}

			.accordion {
				text-align: left;
			}

			.accordionContentUnit {
				font-size: 0.9em;
			}

			button, a.button {
				margin-top: 30px;
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
				<div id="descriptionBox">
					<div class="descriptionBlock1">
						<div class='descriptionBlockHeader'>
							Для собственников
						</div>
						<div class="accordion">
							<h3><a href="#">Бонусные выплаты каждому собственнику</a></h3>
							<div class="accordionContentUnit">
								<p>
									Найдите арендатора для вашей недвижимости с помощью House Choice и Вы получите единоразовый бонус - 20% от месячной стоимости аренды квартиры.
									Деньги выплачиваются самим нанимателем при заключении договора аренды. Все наши клиенты ознакомлены с этим условием и готовы его соблюдать.
								</p>
							</div>
							<h3><a href="#">Профессиональный договор аренды</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div>
							<h3><a href="#">Минимум действий - наш агент приедет к Вам и все сделает</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div>
							<h3><a href="#">Легко найти порядочных нанимателей</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div><h3><a href="#">Просто сдать недвижимость с первого показа</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div>
						</div>
						<button id="forowner">
							Я - собственник
						</button>
					</div>

					<div class="descriptionBlock2">
						<div class='descriptionBlockHeader'>
							Для арендаторов
						</div>
						<div class="accordion">
							<h3><a href="#">Оплата только по факту заселения</a></h3>
							<div class="accordionContentUnit">
								<p></p>
							</div>
							<h3><a href="#">Общение напрямую с собственниками</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div>
							<h3><a href="#">Подробная информация по каждому объекту</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div>
							<h3><a href="#">Невысокий размер комиссии</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div>
							<h3><a href="#">Только эксклюзивные предложения от реальных собственников</a></h3>
							<div class="accordionContentUnit">
								<p>

								</p>
							</div>
						</div>
						<button id="fortenant">
							Я - арендатор
						</button>
					</div>
				</div><!-- /end.descriptionBox -->
				<!--
				<div class="descriptionBlock">
				<span class='descriptionBlockHeader'>Для собственников</span>
				<ul>
				<li><span class="highlighted">Бонусные выплаты</span> каждому собственнику - 20% от месячной стоимости аренды квартиры</li>
				<li>Профессионально составленный <span class="highlighted">договор аренды</span> недвижимости</li>
				<li><span class="highlighted">Минимум действий</span> - наш агент приедет к Вам и все сделает</li>
				<li>Легко найти <span class="highlighted">порядочных нанимателей</span> - Вы получаете доступ к подробной информации о каждом претенденте</li>
				<li>Просто сдать недвижимость <span class="highlighted">с первого показа</span> - Вы сэкономите Ваше время и время потенциальный нанимателей - все стороны сделки располагают максимально подробной информацией друг о друге, а значит могут принимать взвешеные решения</li>
				</ul>
				</div>
				<div class="descriptionBlock">
				<span class='descriptionBlockHeader'>Для нанимателей</span>
				<ul>
				<li>Комиссия выплачивается <span class="highlighted">только по факту</span> - после подписания договора аренды</li>
				<li>Невысокий размер комиссии - 40% от месячной стоимости аренды</li>
				<li>Общение <span class="highlighted">напрямую с собственниками</span> - а значит быстрее и честнее</li>
				<li>Максимально <span class="highlighted">подробная информация</span> по каждому объекту - экономьте Ваше время</li>
				</ul>
				</div>
				</div>
				-->
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

		<script src="js/main.js"></script>
		<script>
			$("#fortenant").on('click', function() {
				window.open('fortenant.html');
			});
			$("#forowner").on('click', function() {
				window.open('forowner.html');
			});
		</script>
		<!-- scripts concatenated and minified via build script -->

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
