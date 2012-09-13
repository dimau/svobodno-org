<?php
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

if (login()) //вызываем функцию login, определяющую, авторизирован юзер или нет
{
    header('Location: personal.php'); // пересылаем юзера сразу в личный кабинет
}
else //если пользователь не авторизирован, то проверим, была ли нажата кнопка входа на сайт
{
    if(isset($_POST['buttonSubmit']))
    {
        $error = enter(); //функция входа на сайт

        if (count($error) == 0) //если нет ошибок, отправляем пользователя в личный кабинет
        {
            header('Location: personal.php');
        }
        // Если при авторизации возникли ошибки, мы их покажем в специальном всплывающем сверху блоке вместе со страницей авторизации
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
	</head>

	<body>
		<div class="page_without_footer">

            <!-- Всплывающее поле для отображения списка ошибок, полученных при проверке данных на сервере (PHP)-->
            <div id="userMistakesBlock" class="ui-widget">
                <div class="ui-state-highlight ui-corner-all">
                    <div>
                        <p>
                            <span class="icon-mistake ui-icon ui-icon-info"></span>
                            <span id="userMistakesText">Для продолжения, пожалуйста, дополните или исправьте следующие данные:</span>
                        </p>
                        <ol><?php
                            if (isset($error) && count($error) != 0) {
                                foreach ($error as $key => $value) {
                                    echo "<li>$value</li>";
                                }
                            }
                            ?></ol>
                    </div>
                </div>
            </div>

            <!-- Сформируем и вставим заголовок страницы -->
            <?php
                include("header.php");
            ?>

			<div class="page_main_content">


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
										<input type="password" name="password" size="23" maxlength="50">
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

        <script>
            // Отображение результатов обработки формы на PHP
            if ($('#userMistakesBlock ol').html() != "") {
                $('#userMistakesBlock').on('click', function() {
                    $(this).slideUp(800);
                });
                $('#userMistakesBlock').css('display', 'block');
            }
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
