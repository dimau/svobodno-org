<!DOCTYPE html>
<html>
<head>

    <!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-language" content="ru">
    <meta name="description" content="Поиск недвижимости в аренду">
    <!-- Если у пользователя IE: использовать последний доступный стандартный режим отображения независимо от <!DOCTYPE> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- Информация для поисковых систем об индексации страницы -->
    <meta name="document-state" content="Dynamic">
    <meta name="keywords" content="Недвижимость, в Екатеринбурге, аренда, свободно">
    <meta name="robots" content="index,follow">
    <!-- Оптимизация отображения на мобильных устройствах -->
    <!--<meta name="viewport" content="initialscale=1.0, width=device-width">-->
    <!-- end meta -->

    <title>Поиск недвижимости</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/colorbox.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* Отступ под блок класса advertActions */
        .realtyObject .listDescriptionSmall {
            margin-bottom: 21px;
        }

            /* Отступ слева для описания объекта в баллуне */
        .listDescriptionSmall.forBalloon {
            margin-left: 6px;
        }
    </style>
    <!-- end CSS -->

</head>

<body>
<div class="pageWithoutFooter">

    <?php
    // Сформируем и вставим заголовок страницы
    require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_header.php";
    ?>

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

    <?php
    // Пока пользователь любуется заголовком страницы, а браузер загружает нужные библиотеки, вычислим представление для результатов поиска. Размещать же его на странице мы будем несколько позже
    $matterOfBalloonList = View::getMatterOfBalloonList($propertyFullArr, $favoritePropertiesId, "search");
    $matterOfShortList = View::getMatterOfShortList($propertyFullArr, $favoritePropertiesId, 1, "search");
    $matterOfFullParametersList = View::getMatterOfFullParametersList($propertyFullArr, $favoritePropertiesId, 1, "search");
    ?>

    <div class="headerOfPage">
        Приятного поиска!
    </div>
    <div id="tabs" class="mainContentBlock" style="border: 2px solid #4b9baa;">
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
		        <span id="fastSearchInput">
                Я хочу арендовать
					<select name="typeOfObjectFast" id="typeOfObjectFast">
                        <option value="0" <?php if ($userSearchRequest['typeOfObject'] == "0") echo "selected";?>></option>
                        <option value="квартира" <?php if ($userSearchRequest['typeOfObject'] == "квартира") echo "selected";?>>
                            квартиру
                        </option>
                        <option value="комната" <?php if ($userSearchRequest['typeOfObject'] == "комната") echo "selected";?>>
                            комнату
                        </option>
                        <option value="дом" <?php if ($userSearchRequest['typeOfObject'] == "дом") echo "selected";?>>
                            дом,
                            коттедж
                        </option>
                        <option value="таунхаус" <?php if ($userSearchRequest['typeOfObject'] == "таунхаус") echo "selected";?>>
                            таунхаус
                        </option>
                        <option value="дача" <?php if ($userSearchRequest['typeOfObject'] == "дача") echo "selected";?>>
                            дачу
                        </option>
                        <option value="гараж" <?php if ($userSearchRequest['typeOfObject'] == "гараж") echo "selected";?>>
                            гараж
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
				руб./мес. &nbsp;
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
                require $_SERVER['DOCUMENT_ROOT'] . "/templates/editableBlocks/templ_editableSearchRequest.php";
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

    <?php
    // Размещаем на странице HTML для результатов поиска
    require $_SERVER['DOCUMENT_ROOT'] . "/templates/searchResultBlocks/templ_searchResult.php";
    ?>

    <?php
    // Модальное окно для незарегистрированных пользователей, которые нажимают на кнопку добавления в Избранное
    if ($isLoggedIn === FALSE) require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_addToFavotitesDialog_ForLoggedOut.php";
    ?>

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.pageWithoutFooter -->
<div class="footer">
    2013 г. Если нужна помощь или хочется оставить отзыв (пожелание) на сервис Svobodno.org, свяжитесь с нами по
    телефону: 8-922-160-95-14, или e-mail:
    support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
<script>
    // Сервер сохранит в эту переменную данные об объектах недвижимости в формате JSON
    // Переменная allProperties содержит массив объектов, каждый из которых представляет информацию по 1 объявлению
    var allProperties = JSON.parse('<?php echo json_encode($propertyLightArr);?>');
</script>
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
    $(document).ready(function () {
        document.getElementsByClassName("choiceViewSearchResult")[0].scrollIntoView(true);
    });

</script>
<!-- end scripts -->

</body>
</html>