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
        Панель администратора
    </div>
    <div id="allSections">

        <?php if ($isAdmin['newOwner']): ?>
        <div class="section admin left" id="newOwnerSection">
            <div class="headerSection">Новый собственник</div>
            <ul>
                <li>
                    <a href="adminpanel.php?action=registrationNewOwner" target="_blank">Зарегистрировать нового
                        собственника</a>
                </li>
                <li>
                    <a href="newadvert.php" target="_blank">Создать новое объявление</a>
                </li>
            </ul>
            <div class="clearBoth"></div>
        </div>
        <?php endif; ?>
        <!-- /end.newOwnerSection -->

        <?php if ($isAdmin['newAdvertAlien']): ?>
        <div class="section admin right" id="newAdvertAlienSection">
            <div class="headerSection">Новое объявление (чужое)</div>
            <ul>
                <li>
                    <a href="registration.php?typeOwner=true&alienOwner=true" target="_blank">Новый чужой
                        собственник</a>
                </li>
                <li>
                    <a href="newadvert.php?completeness=0" target="_blank">Новое чужое объявление</a>
                </li>
                <li>
                    <form name="advertMerging" method="post" action="adminpanel.php?action=mergeAdverts">
                        Слить чужое объявление
                        <input name="alienAdvertId" type="text" value="" size="7"> с
                        <input name="ourAdvertId" type="text" value="" size="7">
                        <button type="submit">ок</button>
                    </form>
                </li>
            </ul>
            <div class="clearBoth"></div>
        </div>
        <?php endif; ?>
        <!-- /end.newAdvertAlienSection -->

        <?php if ($isAdmin['searchUser']): ?>
        <div class="section admin left" id="searchUserSection">
            <div class="headerSection">Поиск пользователя</div>
            <form name="findUserForm" method="post" action="adminFindUser.php" target="_blank">
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
                        <label>Номер телефона (без 8-ки)</label> <input name="telephon" id="telephon" type="text"
                                                                        size="20">
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
        <?php endif; ?>
        <!-- /end.searchUserSection -->

        <?php if ($isAdmin['searchUser']): ?>
        <div class="section admin right" id="requestFromOwnerSection">
            <div class="headerSection">Заявки собственников</div>
            <ul>
                <li>
                    <a href="adminAllRequestsFromOwners.php">Все имеющиеся запросы от собственников</a>
                </li>
            </ul>
            <div class="clearBoth"></div>
        </div>
        <?php endif; ?>
        <!-- /end.requestFromOwnerSection -->

        <?php if ($isAdmin['searchUser']): ?>
        <div class="section admin right" id="signUpToViewSection">
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
                    <a target="_blank" href="adminAllProperties.php?action=allWithEarliestDate"
                       title="Все объекты с назначенной датой просмотра" style="font-weight: bold;">Ближайшие
                        просмотры</a>
                </li>
                <li>
                    <a target="_blank" href="adminAllProperties.php?action=allRemovedWithRequestsToView"
                       title="Снятые с публикации объекты, по которым остались активные заявки на просмотр"
                       style="font-weight: bold;">Недозакрытые объявления</a>
                </li>
            </ul>
            <div class="clearBoth"></div>
        </div>
        <?php endif; ?>
        <!-- /end.signUpToViewSection -->

        <?php if ($isAdmin['searchUser']): ?>
        <div class="section admin right" id="logsFromServerSection">
            <div class="headerSection">Логи сервера</div>
            <ul>
                <li>
                    <a href=""></a>
                </li>
            </ul>
            <div class="clearBoth"></div>
        </div>
        <?php endif; ?>
        <!-- /end.logsFromServerSection -->

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
<!-- end scripts -->

</body>
</html>