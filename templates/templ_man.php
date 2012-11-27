<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title><?php echo $strHeaderOfPage; ?></title>
    <meta name="description" content="<?php echo $strHeaderOfPage; ?>">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/colorbox.css">
    <link rel="stylesheet" href="css/main.css">

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- ColorBox - плагин jQuery, позволяющий делать модальное окно для просмотра фотографий -->
    <script src="js/vendor/jquery.colorbox-min.js"></script>

</head>

<body>
<div class="page_without_footer">

    <?php
        // Сформируем и вставим заголовок страницы
        include("templates/templ_header.php");
    ?>

    <div class="page_main_content">
        <div class="headerOfPage">
            Характеристика пользователя
        </div>
        <div id="tabs">
            <ul>
                <li>
                    <a href="#tabs-1">Профиль</a>
                </li>
                <li>
                    <a href="#tabs-2">Условия поиска</a>
                </li>
            </ul>
            <div id="tabs-1">
                <div id="notEditingProfileParametersBlock">
                    <?php
                        // Формируем и размещаем на странице блок для основной фотографии пользователя
                        echo View::getHTMLfotosWrapper("middle", TRUE, FALSE, $userFotoInformation['uploadedFoto']);

                        // Вставляем анкетные данные пользователя
                        include ("templates/templ_notEditedProfile.php");
                    ?>
                </div>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.tabs-1 -->
            <div id="tabs-2">
                <?php if ($userSearchRequest == FALSE): ?>
                <div class="shadowText">
                    Пользователь не ищет недвижимость в данный момент
                </div>
                <?php endif;?>
                <?php if ($userSearchRequest != FALSE): ?>
                <div class="shadowText">
                    Какого рода недвижимость ищет данный пользователь
                </div>
                <?php
                    // Шаблон для представления нередактируемых параметров поисковго запроса пользователя
                    include ("templ_notEditedSearchRequest.php");
                ?>
                <?php endif;?>
            </div>
            <!-- /end.tabs-2 -->
        </div>
    </div>
    <!-- /end.page_main_content -->
    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.page_without_footer -->
<div class="footer">
    2012 г. Вопросы и пожелания по работе портала можно передавать по телефону: 8-922-143-16-15, e-mail:
    support@svobodno.org
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