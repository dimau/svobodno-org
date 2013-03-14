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
            text-align: left;
        }

        .miniBlockContent .strong {
            font-weight: bold;
        }

        .miniBlockContent .benefits {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }

        .miniBlockContent .paymentButtonsBlock {
            margin-top: 10px;
            text-align: center;
        }

        .miniBlockContent .alterHref {
            margin-top: 10px;
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
<div class="pageWithoutFooter">

    <?php
    // Сформируем и вставим заголовок страницы
    require $websiteRoot . "/templates/templ_header.php";
    ?>

    <div class="miniBlock">

        <div class="miniBlockHeader">
            Вы успешно зарегистрировались на портале Svobodno.org!
        </div>

        <div class="miniBlockContent">

            <div>
                Теперь Вы можете оплатить <span class="strong">Премиум доступ</span>, чтобы:
            </div>
            <ul class="benefits">
                <li>
                    Просматривать исходные объявления с ФОТОГРАФИЯМИ недвижимости
                </li>
                <li>
                    Получать e-mail оповещения о появлении новых подходящих Вам объектов
                </li>
                <li>
                    Помочь ресурсу и в следующий раз воспользоваться таким же удобным и дешевым сервисом
                </li>
            </ul>

            <div class="paymentButtonsBlock">
                <?php
                require $websiteRoot . "/templates/templ_paymentButtons.php";
                ?>
            </div>

            <div class="alterHref">
                <?php
                if ($url_initial != "" && $url_initial != "http://svobodno.org/index.php" && $url_initial != "http://localhost/index.php" && $url_initial != "http://svobodno.org/" && $url_initial != "http://localhost/") {
                    echo "<a href='" . $url_initial . "'>Вернуться на страницу</a>, с которой Вы перешли к регистрации";
                } else {
                    echo "Воспользоваться <a href='search.php'>Поиском недвижимости</a>";
                }
                ?>
            </div>

            <div class="alterHref">
                Либо посетить <a href="personal.php">Личный кабинет</a>
            </div>

        </div>

        <div class="clearBoth"></div>

    </div>

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.pageWithoutFooter -->
<div class="footer">
    2013 г. Мы будем рады ответить на Ваши вопросы, отзывы, предложения по телефону: 8-922-160-95-14, или e-mail:
    support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
<script src="js/main.js"></script>
<!-- end scripts -->

</body>
</html>