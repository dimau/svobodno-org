<?php
    // Инициализируем используемые в шаблоне переменные
    $userCharacteristic = $dataArr['userCharacteristic'];
    $userFotoInformation = $dataArr['userFotoInformation'];
    $userSearchRequest = $dataArr['userSearchRequest'];
    $allPropertiesCharacteristic = $dataArr['allPropertiesCharacteristic'];
    $allPropertiesFotoInformation = $dataArr['allPropertiesFotoInformation'];
    $allPropertiesTenantPretenders = $dataArr['allPropertiesTenantPretenders'];
    $errors = $dataArr['errors'];
    $correctNewSearchRequest = $dataArr['correctNewSearchRequest'];
    $correctEditSearchRequest = $dataArr['correctEditSearchRequest'];
    $correctEditProfileParameters = $dataArr['correctEditProfileParameters'];
    $allDistrictsInCity = $dataArr['allDistrictsInCity'];
    $isLoggedIn = $dataArr['isLoggedIn'];
    $propertyLightArr = $dataArr['propertyLightArr'];
    $propertyFullArr = $dataArr['propertyFullArr'];
    $favoritesPropertysId = $dataArr['favoritesPropertysId'];
    $whatPage = $dataArr['whatPage']; // Режим в котором будет работать шаблон для редактирования поискового запроса (templ_editableSearchRequest.php)
    $tabsId = $dataArr['tabsId']; // Идентификатор вкладки, которая будет открыта по умолчанию после загрузки страницы
    $mode = $dataArr['mode']; // Режим в котором будет работать шаблон анкеты пользователя на вкладке №1 (templ_notEditedProfile.php)
    $messagesArr = $dataArr['messagesArr']; // массив массивов, каждый из которых представляет инфу по 1-ому сообщению (новости пользователя)
    $amountUnreadMessages = $dataArr['amountUnreadMessages'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
    More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Личный кабинет</title>
    <meta name="description" content="Личный кабинет">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/fileuploader.css">
    <link rel="stylesheet" href="css/colorbox.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        #newAdvertButton {
            margin-bottom: 10px;
        }

        #notEditedDistricts table tr {
            border-bottom: none;
        }

        #notEditedSpecialParams {
            width: 100%;
        }

        #notEditedSpecialParams .objectDescriptionItemLabel, #notEditedSpecialParams .objectDescriptionBody {
            width: auto;
        }

            /* Оформление команд на редактирование профайла и условий поиска */
        #editProfileButton,
        #editSearchRequestButton {
            cursor: pointer;
        }

            /* Отступ слева для описания объекта в баллуне */
        .listDescriptionSmall.forBalloon {
            margin-left: 6px;
        }
    </style>

</head>

<body>
<div class="page_without_footer">

    <!-- Всплывающее поле для отображения списка ошибок, полученных при проверке данных на сервере (PHP)-->
    <div id="userMistakesBlock" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <div>
                <p>
                    <span class="icon-mistake ui-icon ui-icon-info"></span>
                <span
                    id="userMistakesText">Для продолжения, пожалуйста, дополните или исправьте следующие данные:</span>
                </p>
                <ol><?php
                    if (isset($errors) && count($errors) != 0) {
                        foreach ($errors as $value) {
                            echo "<li>$value</li>";
                        }
                    }
                    ?></ol>
            </div>
        </div>
    </div>

    <!-- Добавялем невидимый input для того, чтобы передать тип пользователя (собственник/арендатор) - это используется в JS для простановки обязательности полей для заполнения -->
    <input type='hidden' class='userType'
           typeTenant='<?php if ($userCharacteristic['typeTenant']) echo "TRUE"; else echo "FALSE";?>'
           typeOwner='<?php if ($userCharacteristic['typeOwner']) echo "TRUE"; else echo "FALSE";?>'
           correctEditSearchRequest='<?php
               if ($correctEditSearchRequest) echo "TRUE"; else if ($correctEditSearchRequest === FALSE) echo "FALSE"; else echo "NULL"; ?>'>

    <?php
    // Добавяем невидимый input для того, чтобы передать идентификатор вкладки, которую нужно открыть через JS
    echo "<input type='hidden' class='tabsId' tabsId='" . $tabsId . "'>";
    ?>

    <?php
    // Сформируем и вставим заголовок страницы
    include("templates/templ_header.php");
    ?>

    <?php
    // Модальное окно для незарегистрированных пользователей, которые нажимают на кнопку добавления в Избранное
    if ($isLoggedIn === FALSE) include "templates/templ_addToFavotitesDialog_ForLoggedOut.php";
    ?>

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- Русификатор виджета календарь -->
    <script src="js/vendor/jquery.ui.datepicker-ru.js"></script>
    <!-- Загрузчик фотографий на AJAX -->
    <script src="js/vendor/fileuploader.js" type="text/javascript"></script>
    <!-- ColorBox - плагин jQuery, позволяющий делать модальное окно для просмотра фотографий -->
    <script src="js/vendor/jquery.colorbox-min.js"></script>
    <!-- Загружаем библиотеку для работы с картой от Яндекса -->
    <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

    <?php
        // Пока пользователь любуется заголовком страницы, а браузер загружает нужные библиотеки, вычислим представление для результатов поиска (избранных объявлений). Размещать же его на странице мы будем несколько позже
        $matterOfBalloonList = $this->getMatterOfBalloonList($propertyFullArr, $favoritesPropertysId, "favorites");
        $matterOfShortList = $this->getMatterOfShortList($propertyFullArr, $favoritesPropertysId, "favorites");
        $matterOfFullParametersList = $this->getMatterOfFullParametersList($propertyFullArr, $favoritesPropertysId, "favorites");
    ?>

    <div class="page_main_content">
        <div class="headerOfPage">
            Личный кабинет
        </div>
        <div id="tabs">
            <ul>
                <li>
                    <a href="#tabs-1">Профиль</a>
                </li>
                <li>
                    <a href="#tabs-2">Сообщения<?php
                        // Сколько сообщений не прочитано?
                        if ($amountUnreadMessages != 0) echo " (<span class='amountOfNewMessages'>".$amountUnreadMessages."</span>)"; ?>
                    </a>
                </li>
                <li>
                    <a href="#tabs-3">Мои объявления</a>
                </li>
                <li>
                    <a href="#tabs-4">Поиск</a>
                </li>
                <li>
                    <a href="#tabs-5">Избранное</a>
                </li>
            </ul>

            <div id="tabs-1">
                <?php if ($correctEditProfileParameters !== FALSE): ?>
                <!-- Блок с нередактируемыми параметрами Профайла не выдается только в 1 случае: если пользователь корректировал свои параметры, и они не прошли проверку -->
                <div id="notEditingProfileParametersBlock">
                    <ul class="setOfInstructions">
                        <li><a id="editProfileButton">редактировать</a></li>
                    </ul>
                    <?php

                    // Формируем и размещаем на странице блок для основной фотографии пользователя
                    echo $this->getHTMLfotosWrapper("middle", TRUE, FALSE, $userFotoInformation['uploadedFoto']);

                    // Вставляем анкетные данные пользователя
                    include ("templates/templ_notEditedProfile.php");

                    ?>
                </div>
                <?php endif; ?>
                <form method="post" name="profileParameters" id="editingProfileParametersBlock"
                      class="descriptionFieldsetsWrapper formWithFotos" enctype="multipart/form-data"
                      style='<?php if ($correctEditProfileParameters !== FALSE) echo "display: none;"?>'>

                    <?php
                    // Подключим форму для ввода и редактирования данных о ФИО, логине, контактах пользователя, а также о фотографиях
                    include "templates/templ_editablePersonalFIO.php";

                    // Подключим форму для ввода и редактирования данных об образовании, работе и месте рождения
                    include "templates/templ_editablePersonalEducAndWork.php";

                    // Подключим форму для ввода и редактирования данных о социальных сетях пользователя
                    include "templates/templ_editablePersonalSocial.php";
                    ?>

                    <div class="clearBoth"></div>

                    <div class="bottomButton">
                        <a href="personal.php?tabsId=1" style="margin-right: 10px;">Отмена</a>
                        <button type="submit" name="saveProfileParameters" id="saveProfileParameters" class="button">
                            Сохранить
                        </button>
                    </div>

                    <div class="clearBoth"></div>

                </form>
                <!-- /end.descriptionFieldsetsWrapper -->
                <div class="clearBoth"></div>
            </div>
            <!-- /end.tabs-1 -->

            <div id="tabs-2">
                <?php
                    // Формируем и выдаем HTML списка сообщений (новостей) пользователя
                    echo $this->getHTMLforMessages($messagesArr);
                ?>
            </div>

            <div id="tabs-3">
                <a href="forowner.php" class="button" id="newAdvertButton">
                    Новое объявление
                </a>

                <?php
                echo $this->getHTMLforOwnersCollectionProperty($allPropertiesCharacteristic, $allPropertiesFotoInformation, $allPropertiesTenantPretenders);
                ?>

            </div>

            <div id="tabs-4">

                <?php if ($userCharacteristic['typeTenant'] !== TRUE && $correctNewSearchRequest !== TRUE && $correctEditSearchRequest === NULL): ?>
                <div class="shadowText">
                    На этой вкладке Вы можете задать параметры недвижимости, в соответствии с которыми ресурс Svobodno.org будет
                    осуществлять
                    автоматический поиск объявлений на портале и будет оповещать Вас о появлении новых объектов.
                </div>
                <!-- Если пользователь еще не сформировал поисковый запрос (а значит не является арендатором) и он либо не нажимал на кнопку формирования запроса, либо нажимал, но не прошел проверку на полноту информации о пользователи, то ему доступна только кнопка формирования нового запроса. В ином случае будет отображаться сам поисковый запрос пользователя, либо форма для его заполнения -->
                <form name="createSearchRequest" method="post">
                    <button type="submit" name="createSearchRequestButton" id='createSearchRequestButton'
                            class='left-bottom'>
                        Запрос на поиск
                    </button>
                </form>
                <?php endif;?>

                <?php if ($userCharacteristic['typeTenant'] === TRUE && $correctEditSearchRequest !== FALSE): ?>
                <!-- Если пользователь является арендатором и (если он редактировал пар-ры поиска) после редактирования параметров поиска ошибок не обнаружено, то у пользователя уже сформирован корректный поисковый запрос, который мы и показываем на этой вкладке -->
                <div id="notEditedSearchRequestBlock">
                    <ul id="setOfInstructions" class="setOfInstructions">
                        <li><a id="editSearchRequestButton">редактировать</a></li>
                        <li><a href="personal.php?action=deleteSearchRequest&tabsId=4"
                               title="Удаляет запрос на поиск - кликните по этой ссылке, когда Вы найдете недвижимость">удалить</a>
                        </li>
                        <br>
                    </ul>
                    <?php
                    // Шаблон для представления нередактируемых параметров поисковго запроса пользователя
                    include ("templ_notEditedSearchRequest.php");
                    ?>
                </div>
                <?php endif;?>

                <?php if ($userCharacteristic['typeTenant'] === TRUE || $correctNewSearchRequest === TRUE || $correctEditSearchRequest === FALSE): ?>
                <!-- Если пользователь является арендатором, то вместе с отображением текущих параметров поискового запроса мы выдаем скрытую форму для их редактирования, также мы выдаем видимую форму для редактирования параметров поиска в случае, если пользователь нажал на кнопку Нового поискового запроса и проверка на корректность его данных Профиля профла успешно, а также в случае если пользователь корректировал данные поискового запроса, но они не прошли проверку -->
                <form method="post" name="searchParameters" id="extendedSearchParametersBlock">

                    <?php
                    // Подключим форму для ввода и редактирования данных о социальных сетях пользователя
                    include "templates/templ_editableSearchRequest.php";
                    ?>

                    <div class="clearBoth"></div>
                    <div class="bottomButton">
                        <a href="personal.php?tabsId=4" style="margin-right: 10px;">Отмена</a>
                        <button type="submit" name="saveSearchParametersButton" id="saveSearchParametersButton"
                                class="button">
                            Сохранить
                        </button>
                    </div>

                    <div class="clearBoth"></div>
                </form>
                <?php endif;?>

            </div>
            <!-- /end.tabs-4 -->

            <div id="tabs-5">
                <?php
                    // Размещаем на странице HTML для результатов поиска (списка избранных объектов недвижимости)
                    include("templates/templ_searchResult.php");
                ?>
            </div>

        </div>
        <!-- /end.tabs -->

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
<script>
    // Сервер сохранит в эту переменную данные о загруженных фотографиях в формате JSON
    // Переменная uploadedFoto содержит массив объектов, каждый из которых представляет информацию по 1 фотографии
    var uploadedFoto = JSON.parse('<?php echo json_encode($userFotoInformation['uploadedFoto']);?>');
    // Сервер сохранит в эту переменную данные об объектах недвижимости в формате JSON
    // Переменная allProperties содержит массив объектов, каждый из которых представляет информацию по 1 объявлению
    var allProperties = JSON.parse('<?php echo json_encode($propertyLightArr);?>');
</script>
<script src="js/main.js"></script>
<script src="js/personal.js"></script>
<script src="js/searchResult.js"></script>
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