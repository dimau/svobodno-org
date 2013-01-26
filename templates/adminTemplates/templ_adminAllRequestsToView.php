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

    <title>Админка: Заявки на просмотр</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .simpleBlockForAnyContent {
            margin: 10px 0 10px 0;
            font-size: 0.9em;
            line-height: 2em;
        }

        .simpleBlockForAnyContent .content {
            font-size: 1.1em;
            color: #6A9D02;
            font-weight: bold;
        }

        .simpleBlockForAnyContent .setOfInstructions {
            float: left;
            margin-left: 15px;
            list-style: none;
        }

        .simpleBlockForAnyContent .setOfInstructions li {
            display: inline-block;
            margin-left: 10px;
            margin-right: 10px;
            font-size: 1em;
        }

            /* Используется для выделения описания той заявки на просмотр, что интересует админа */
        .highlightedBlock {
            padding: 5px;
            border: 2px solid red;
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
        Панель администратора -> Все заявки на просмотр со статусом "<?php echo $action;?>"
    </div>

    <div class="simpleBlockForAnyContent">
        <?php foreach ($allRequestsToView as $requestToView): ?>
        <?php require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminRequestToViewDetailedItem.php"; ?>
        <hr>
        <?php endforeach; ?>
    </div>

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.pageWithoutFooter -->
<div class="footer">
    2013 г. Вопросы и пожелания по работе портала можно передавать по телефону: 8-922-160-95-14, e-mail:
    support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
<script src="js/main.js"></script>
<script src="js/admin.js"></script>
<!-- end scripts -->

</body>
</html>