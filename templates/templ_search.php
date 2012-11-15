<?php
    // Инициализируем используемые в шаблоне переменные
    $whatPage = $dataArr['whatPage'];
    $propertyLightArr = $dataArr['propertyLightArr'];
    $userSearchRequest = $dataArr['userSearchRequest'];
    $allDistrictsInCity = $dataArr['allDistrictsInCity'];
    $isLoggedIn = $dataArr['isLoggedIn'];
    $favoritesPropertysId = $dataArr['favoritesPropertysId'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Поиск недвижимости в аренду</title>
    <meta name="description" content="Поиск недвижимости в аренду">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/colorbox.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Стили для параметров поиска*/
        #fastSearchInput {
            line-height: 2.4;
        }

        #extendedSearchButton {
            margin-left: 20px;
        }

        /* Отступ под блок класса advertActions */
        .realtyObject .listDescriptionSmall {
            margin-bottom: 21px;
        }

        /* Отступ слева для описания объекта в баллуне */
        .listDescriptionSmall.forBalloon {
            margin-left: 6px;
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
    <!-- ColorBox - плагин jQuery, позволяющий делать модальное окно для просмотра фотографий -->
    <script src="js/vendor/jquery.colorbox-min.js"></script>
    <!-- Загружаем библиотеку для работы с картой от Яндекса -->
    <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

</head>

<body>
<div class="page_without_footer">

    <?php
        // Сформируем и вставим заголовок страницы
        include("templates/templ_header.php");

        // Для целей ускорения загрузки перенес блок php кода по формированию HTML результатов поиска сюда - это позволит браузеру грузить нужные библиотеки в то время, как сервер будет готовить представление для таблиц с данными об объектах недвижимости
        $searchResultHTML = $this->getSearchResultHTML($propertyLightArr, $favoritesPropertysId, "search");
    ?>

    <div class="page_main_content">
        <div class="headerOfPage">
            Приятного поиска!
        </div>
        <div id="tabs">
            <ul>
                <li>
                    <a href="#tabs-1">Быстрый поиск</a>
                </li>
                <li>
                    <a href="#tabs-2">Расширенный поиск</a>
                </li>
            </ul>
            <div id="tabs-1">
                <form name="fastSearch" method="get">
							<span id="fastSearchInput"> Я хочу арендовать
								<select name="typeOfObjectFast" id="typeOfObjectFast">
                                    <option value="0" <?php if ($userSearchRequest['typeOfObject'] == "0") echo "selected";?>></option>
                                    <option value="квартира" <?php if ($userSearchRequest['typeOfObject'] == "квартира") echo "selected";?>>
                                        квартира
                                    </option>
                                    <option value="комната" <?php if ($userSearchRequest['typeOfObject'] == "комната") echo "selected";?>>
                                        комната
                                    </option>
                                    <option value="дом" <?php if ($userSearchRequest['typeOfObject'] == "дом") echo "selected";?>>дом,
                                        коттедж
                                    </option>
                                    <option value="таунхаус" <?php if ($userSearchRequest['typeOfObject'] == "таунхаус") echo "selected";?>>
                                        таунхаус
                                    </option>
                                    <option value="дача" <?php if ($userSearchRequest['typeOfObject'] == "дача") echo "selected";?>>дача
                                    </option>
                                    <option value="гараж" <?php if ($userSearchRequest['typeOfObject'] == "гараж") echo "selected";?>>гараж
                                    </option>
                                </select>
                                в районе
                                <select name="districtFast" id="districtFast">
                                    <option value="0"></option>
                                    <?php
                                    if (isset($allDistrictsInCity)) {
                                        foreach ($allDistrictsInCity as $value) { // Для каждого названия района формируем option в селекте
                                            echo "<option value='" . $value['name'] . "'";
                                            if (isset($userSearchRequest['district'][0]) && $value['name'] == $userSearchRequest['district'][0]) echo "selected"; // В качестве выбранного в селекте назначаем первый район из списка выбранных пользователем
                                            echo ">" . $value['name'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
								стоимостью от
								<input type="text" name="minCostFast" id="minCostFast" size="10"
                                       maxlength="8" value='<?php echo $userSearchRequest['minCost'];?>'>
								до
								<input type="text" name="maxCostFast" id="maxCostFast" size="10"
                                       maxlength="8" value='<?php echo $userSearchRequest['maxCost'];?>'>
								руб./мес.
								&nbsp;
								<button type="submit" name="fastSearchButton" id="fastSearchButton" class="mainButton">
                                    Найти
                                </button>
                            </span>
                </form>
            </div>
            <div id="tabs-2">
                <form name="extendedSearch" method="get">

                    <?php
                        // Форма с параметрами поиска
                        include "templates/templ_editableSearchRequest.php";
                    ?>

                    <div class="bottomButton">
                        <button type="submit" name="extendedSearchButton" id="extendedSearchButton" class="mainButton">
                            Найти
                        </button>
                    </div>
                    <div class="clearBoth"></div>

                </form>
            </div>
            <!-- /end.tabs-2 -->
        </div>
        <!-- /end.tabs -->

        <?php
        /***************************************************************************************************************
         * Размещаем на странице полученный с сервера HTML для результатов поиска
         **************************************************************************************************************/
        echo $searchResultHTML;
        ?>

        <?php
        // Модальное окно для незарегистрированных пользователей, которые нажимают на кнопку добавления в Избранное
        if ($isLoggedIn === FALSE) include "templates/templ_addToFavotitesDialog_ForLoggedOut.php";
        ?>

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
<script src="js/searchResult.js"></script>
<script>
    /* Навешиваем обработчик на переключение вкладок с режимами поиска */
    $('#tabs').bind('tabsshow', function (event, ui) {
        newTabId = ui.panel.id; // Определяем идентификатор вновь открытой вкладки
        if (newTabId == "tabs-1") {
            // Переносим тип объекта
            $("#typeOfObjectFast").val($("#typeOfObject").val());

            // Так как между районами при расширенном поиске и районом при быстром поиске невозможно построить взаимнооднозначную конвертацию, не будем этого делать, дабы не запутать пользователя

            // Переносим стоимости
            $("#minCostFast").val($("#minCost").val());
            $("#maxCostFast").val($("#maxCost").val());
        }
        if (newTabId == "tabs-2") {
            // Переносим тип объекта
            $("#typeOfObject").val($("#typeOfObjectFast").val());

            // Переносим стоимости
            $("#minCost").val($("#minCostFast").val());
            $("#maxCost").val($("#maxCostFast").val());
        }
    });

    /* Активируем механизм скрытия ненужных полей в зависимости от заполнения формы */
    // При изменении перечисленных здесь полей алгоритм пробегает форму с целью показать нужные элементы и скрыть ненужные
    $(document).ready(notavailability);
    $("#typeOfObject").change(notavailability);

    /* Проматываем на область результатов поиска */
    $(document).ready(function() {
        document.getElementsByClassName("choiceViewSearchResult")[0].scrollIntoView(true);
    });

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