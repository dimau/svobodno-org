<!DOCTYPE html>
<html>
<head>

	<!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-language" content="ru">
    <meta name="description" content="Успешное окончание регистрации">
    <!-- Если у пользователя IE: использовать последний доступный стандартный режим отображения независимо от <!DOCTYPE> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- Оптимизация отображения на мобильных устройствах -->
    <!--<meta name="viewport" content="initialscale=1.0, width=device-width">-->
 	<!-- end meta -->

    <title>Успешная регистрация</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .miniBlockContent {
            text-align: center;
        }

        .miniBlockContent .text {
            display: inline-block;
            text-align: center;
            line-height: normal;
        }
    </style>
    <!-- end CSS -->

    <!-- JS -->
    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- end JS -->

</head>

<body>
<div class="page_without_footer">

    <?php
        // Сформируем и вставим заголовок страницы
		require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_header.php";
    ?>

    <div class="page_main_content">
        <div class="miniBlock">
            <div class="miniBlockHeader">
                Вы успешно зарегистрировались на портале Svobodno.org!
            </div>
            <div class="miniBlockContent">
                <div class="text">
                    <p style="text-align: left;">
                        <?php
                            if ($url_initial != "" && $url_initial != "http://svobodno.org/index.php" && $url_initial != "http://localhost/index.php" && $url_initial != "http://svobodno.org/" && $url_initial != "http://localhost/") {
                                echo "<a href='".$url_initial."'>Вернуться на страницу</a>, с которой Вы перешли к регистрации";
                            } else {
                                echo "Воспользоваться <a href='search.php'>Поиском недвижимости</a>";
                            }
                        ?>
                    </p>

                    <p style="text-align: left;">
                        Либо посетить <a href="personal.php">Личный кабинет</a>
                    </p>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>

    </div>
    <!-- /end.page_main_content -->

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.page_without_footer -->
<div class="footer">
    2012 г. Вопросы и пожелания по работе портала можно передавать по телефону: 8-922-160-95-14, e-mail: support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
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