<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Подать объявление</title>
    <meta name="description" content="Подать объявление">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        form {
            background-color: #ffffff;
            padding: 5px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>

</head>

<body>
<div class="page_without_footer">
    <!-- Сформируем и вставим заголовок страницы -->
    <?php
    include("header.php");
    ?>

    <div class="page_main_content">
        <div class="commentForPageBlock" style="position: relative;">
            <div class="headerOfPage" style="position: absolute; bottom: -4px; padding: 0;">
                Подайте объявление
            </div>
            <div class="why" style="border-radius: 5px; background-color: #ffffff; padding: 5px; float: right; min-width: 100px; text-align: left; margin-left: 250px;">
                <div class="whyHeader">
                    Что будет дальше?
                </div>
                <ul>
                    <ul>
                        <li>
                            В течение дня Вам перезвонит оператор и уточнит удобное время для выезда специалиста
                        </li>
                        <li>
                            Наш специалист приедет и сформирует подробное объявление по Вашему объекту
                        </li>
                        <li>
                            Объявление попадет на все основные интернет-ресурсы для привлечения арендаторов
                        </li>
                        <li>
                            Заинтересовавшиеся арендаторы заполнят анкету, которая попадет к Вам в личный кабинет
                        </li>
                        <li>
                            Понравившийся Вам арендатор приедет вместе с нашим специалистом на просмотр и заключение договора
                        </li>
                        <li>
                            В итоге: ваша недвижмость сдана порядочным людям!
                        </li>
                    </ul>
                </ul>
            </div>
            <div class="clearBoth"></div>
        </div>

        <form name="requestNewOwner" method="post">
                Как к Вам обращаться?
                <input type="text" size="30" name="Name">
                <br>
                На какой номер Вам перезвонить?
                <input type="text" size="15" name="telNumber">
                <br>
                По какому адресу собираетесь сдать объект?
                <input type="text" size="40" name="address">
                <br>
                Комментарии (например, в какое время Вам будет удобно принять наш звонок)?
                <br>
                <textarea rows="3" cols="90" name="comment"></textarea>

                <div class="clearBoth"></div>
                <button type="submit" style="float: right; margin-top: 10px;">
                    Отправить заявку
                </button>
                <div class="clearBoth"></div>
        </form>
    </div>
    <!-- /end.page_main_content -->
    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.page_without_footer -->
<div class="footer">
    2012 «Хани Хом», вопросы и пожелания по работе портала можно передавать по телефону 8-922-143-16-15
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
