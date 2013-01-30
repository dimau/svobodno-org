<!DOCTYPE html>
<html>
<head>

    <!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-language" content="ru">
    <meta name="description" content="Админка">
    <!-- Если у пользователя IE: использовать последний доступный стандартный режим отображения независимо от <!DOCTYPE> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- Оптимизация отображения на мобильных устройствах -->
    <!--<meta name="viewport" content="initialscale=1.0, width=device-width">-->
    <!-- end meta -->

    <title>Админка</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        #allUsers {
            margin: 30px 0 30px 0;
            padding: 0;
            list-style: none;
        }

        .mainContentBlock{
            margin: 10px 0 10px 0;
            font-size: 0.9em;
            line-height: 2em;
        }

        .mainContentBlock.content {
            font-size: 1.1em;
            color: #6A9D02;
            font-weight: bold;
        }

        .mainContentBlock.setOfInstructions {
            float: left;
            margin-left: 15px;
            list-style: none;
        }

        .mainContentBlock.setOfInstructions li {
            display: inline-block;
            margin-left: 10px;
            margin-right: 10px;
            font-size: 1em;
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

    <div class="headerOfPage">
        Панель администратора -> Найденные пользователи
    </div>

    <ul id="allUsers">
        <?php foreach ($allUsers as $userCharacteristic): ?>
        <li>
            <?php
            // Возвращает HTML для блока с описанием 1 пользователя
            require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminFindUserItem.php";
            ?>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="shadowText">
        При поиске по параметрам пользователя выдается максимум 20 человек - с полными списками их недвижимости и заявок
        на просмотр, которые они отправляли<br>
        При поиске по адресу недвижимости выдается максимум 40 объектов с указанием собственников - в списке объектов
        собственника представлены лишь соответствующие запросу объявления. Заявки на просмотр данных пользователей не
        выдаются
    </div>

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.pageWithoutFooter -->
<div class="footer">
    2013 г. Если нужна помощь или хочется оставить отзыв (пожелание) на сервис Svobodno.org, свяжитесь с нами по телефону: 8-922-160-95-14, или e-mail:
    support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
<script src="js/main.js"></script>
<script src="js/admin.js"></script>
<!-- end scripts -->

</body>
</html>