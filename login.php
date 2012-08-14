<?php
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

if (login()) //вызываем функцию login, определяющую, авторизирован юзер или нет
{
    $UID = $_SESSION['id']; //если юзер авторизирован, присвоим переменной $UID его id
    header('Location: personal.php'); // пересылаем юзера сразу в личный кабинет
}
else //если пользователь не авторизирован, то проверим, была ли нажата кнопка входа на сайт
{
    if(isset($_POST['buttonSubmit']))
    {
        $error = enter(); //функция входа на сайт

        if (count($error) == 0) //если нет ошибок, авторизируем юзера
        {
            $UID = $_SESSION['id'];
            header('Location: personal.php');
        }
        else
        {

            // TODO:что-то нужно делать в случае, если возникли ошибки при авторизации - как минимум вывести их текст во всплывающем окошке
        }
    }
}
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

		<title>Вход на портал Хани Хом</title>
		<meta name="description" content="Вход на портал Хани Хом">

		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="initialscale=1.0, width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

		<link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
		<link rel="stylesheet" href="css/main.css">

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

                <div id="userMistakesBlock" class="ui-widget" style="width: 600px; margin: auto;">
                    <div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
                        <p>
                        <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                        <span id="userMistakesText">Текст ошибки</span>
                        </p>
                    </div>
                </div>

				<div class="miniBlock">
					<div class="miniBlockHeader">
						Введите логин и пароль
					</div>
					<div class="miniBlockContent">
						<form name="loginParol" method="post">
							<table>
								<tbody>
									<tr>
										<td><label>Логин: </label></td>
										<td>
										<input type="text" name="login" size="23" tabindex="0" placeholder=" e-mail или телефон" autofocus>
										</td>
									</tr>
									<tr>
										<td><label>Пароль: </label></td>
										<td>
										<input type="password" name="password" size="23">
										</td>
									</tr>
									<tr>
										<td></td>
										<td><a href="#">Забыли пароль?</a></td>
									</tr>
								</tbody>
							</table>
							<div>
							<button type="submit" id="buttonSubmit" name="buttonSubmit">Войти</button>
							<a class="buttonRegistration" href="choiceOfRole.php">Зарегистрироваться</a>
							</div>
						</form>
					</div><!-- /end.miniBlockContent -->
					<div class="clearBoth"></div>
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
