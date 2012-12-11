<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Админка</title>
    <meta name="description" content="Админка">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* =============================================================================
            АДМИНКА
           ========================================================================== */

        .section.admin {
            width: 48.5%;
            min-width: 430px;
            margin-top: 10px;
            margin-bottom: 10px;
            margin-left: 0;
            margin-right: 0;
            padding: 0.75em;
            border-radius: 5px;
            border: 2px solid #6A9D02;
            text-align: left;
            vertical-align: top;
            background-color: #ffffff;
        }

        .section.admin.left {
            float: left;
            clear: left;
            margin-right: 1.4%;
        }

        .section.admin.right {
            float: right;
            clear: right;
            margin-left: 1.4%;
        }

        .section.admin.fullWidth {
            width: 100%;
            float: none;
            clear: both;
        }

        .section.admin .headerSection {
            text-align: center;
            font-size: 1.1em;
            margin-bottom: 12px;
        }

        .section.admin ul {
            list-style: none;
            line-height: 2em;
            padding: 0;
            margin: 0;
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

    <div class="page_main_content">
        <div class="headerOfPage">
            Панель администратора
        </div>
        <div id="allSections">

            <div class="section admin left" id="newOwnerSection">
                <div class="headerSection">Новый собственник</div>
                <ul>
                    <li>
                        <a href="adminpanel.php?action=registrationNewOwner" target="_blank">Зарегистрировать нового собственника</a>
                    </li>
                    <li>
                        <a href="newadvert.php" target="_blank">Создать новое объявление</a>
                    </li>
                </ul>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.newOwnerSection -->

            <div class="section admin right" id="newAdvertAlienSection">
                <div class="headerSection">Новое объявление (чужое)</div>
                <ul>
                    <li>
                        <a href="registration.php?typeOwner=true&alienOwner=true" target="_blank">Новый чужой собственник</a>
                    </li>
                    <li>
                        <a href="newadvert.php?alienOwner=true" target="_blank">Новое чужое объявление</a>
                    </li>
                </ul>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.newAdvertAlienSection -->

            <div class="section admin left" id="searchUserSection">
                <div class="headerSection">Поиск пользователя</div>
				<form name="findUserForm" method="post" action="adminFindUser.php" target = "_blank">
                <ul style="line-height: 2.5em; text-align: right;">
                    <li>
                        <label>Фамилия</label> <input name="surname" id="surname" type="text" size="20">
                    </li>
                    <li>
                        <label>Имя</label> <input name="name" id="name" type="text" size="20">
                    </li>
                    <li>
                        <label>Отчество</label> <input name="secondName" id="secondName" type="text" size="20">
                    </li>
                    <li>
                        <label>Логин</label> <input name='login' id='login' type="text" size="20">
                    </li>
                    <li>
                        <label>Номер телефона (без 8-ки)</label> <input name="telephon" id="telephon" type="text" size="20">
                    </li>
                    <li>
                        <label>E-mail</label> <input name="email" id="email" type="text" size="20">
                    </li>
					<div style="line-height: 0.7em;">-------------------------------------------------------</div>
                    <li>
                        <label>Адрес недвижимости</label> <input name="address" id="address" type="text" size="20">
                    </li>
                    <li>
                        <button type="submit" name="findUserButton" id="findUserButton">Найти</button>
                    </li>
                </ul>
				</form>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.searchUserSection -->

            <div class="section admin right" id="requestFromOwnerSection">
                <div class="headerSection">Запросы собственников</div>
                <ul>
                    <li>
                        <a href=""></a>
                    </li>
                </ul>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.requestFromOwnerSection -->

            <div class="section admin left" id="signUpToViewSection">
                <div class="headerSection">Заявки на просмотр</div>
                <ul>
                    <li>
                        <a target="_blank" href="adminAllRequestsToView.php?action=Новая">Новые заявки</a>
                    </li>
                    <li>
                        <a target="_blank" href="adminAllRequestsToView.php?action=Назначен просмотр">Назначен просмотр</a>
                    </li>
                    <li>
                        <a target="_blank" href="adminAllRequestsToView.php?action=Отложена">Отложенные заявки</a>
                    </li>
                    <li>
                        <a target="_blank" href="adminAllRequestsToView.php?action=Успешный просмотр">Успешные просмотры</a>
                    </li>
                    <li>
                        <a target="_blank" href="adminAllProperties.php?action=allWithEarliestDate" style="font-weight: bold;">Ближайшие просмотры</a>
                    </li>
                    <li>
                        <a target="_blank" href="adminAllProperties.php?action=allRemovedWithRequestsToView" style="font-weight: bold;">Недозакрытые объявления</a>
                    </li>
                </ul>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.signUpToViewSection -->

            <div class="section admin right" id="logsFromServerSection">
                <div class="headerSection">Логи сервера</div>
                <ul>
                    <li>
                        <a href=""></a>
                    </li>
                </ul>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.logsFromServerSection -->

        </div>
    </div>
    <!-- /end.page_main_content -->
    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.page_without_footer -->
<div class="footer">
    2012 г. Вопросы и пожелания по работе портала можно передавать по телефону: 8-922-143-16-15, e-mail: support@svobodno.org
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