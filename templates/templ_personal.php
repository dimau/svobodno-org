<?php
    // Инициализируем используемые в шаблоне переменные
    $userCharacteristic = $dataArr['userCharacteristic'];
    $userFotoInformation = $dataArr['userFotoInformation'];
    $userSearchRequest = $dataArr['userSearchRequest'];
    $errors = $dataArr['errors'];
    $correctNewSearchRequest = $dataArr['correctNewSearchRequest'];
    $correctEditSearchRequest = $dataArr['correctEditSearchRequest'];
    $correctEditProfileParameters = $dataArr['correctEditProfileParameters'];
    $allDistrictsInCity = $dataArr['allDistrictsInCity'];
    $isLoggedIn = $dataArr['isLoggedIn'];
    $propertyLightArr = $dataArr['propertyLightArr'];
    $favoritesPropertysId = $dataArr['favoritesPropertysId'];
    $whatPage = $dataArr['whatPage'];
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
    <!-- Русификатор виджета календарь -->
    <script src="js/vendor/jquery.ui.datepicker-ru.js"></script>
    <!-- Загрузчик фотографий на AJAX -->
    <script src="js/vendor/fileuploader.js" type="text/javascript"></script>
    <!-- ColorBox - плагин jQuery, позволяющий делать модальное окно для просмотра фотографий -->
    <script src="js/vendor/jquery.colorbox-min.js"></script>
    <!-- Загружаем библиотеку для работы с картой от Яндекса -->
    <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

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

<!-- Добавялем невидимый input для того, чтобы передать идентификатор вкладки, которую нужно открыть через JS -->
<?php
    // При загрузке страницы открываем вкладку № 4 "Поиск", если пользователь создает поисковый запрос и его личные данные для этого достаточны ($correctNewSearchRequest == "true"), либо если он редактирует поисковый запрос ($correctEditSearchRequest == TRUE, $correctEditSearchRequest == FALSE). В ином случае - открываем вкладку №1.
    if ($correctNewSearchRequest === TRUE || $correctEditSearchRequest === TRUE || $correctEditSearchRequest === FALSE) {
        $tabsId = "tabs-4";
    } elseif (isset($_GET['tabsId'])) {
        $tabsId = $_GET['tabsId'];
    } else {
        $tabsId = "tabs-1";
    }
    echo "<input type='hidden' class='tabsId' tabsId='" . $tabsId . "'>";
?>

<!-- Сформируем и вставим заголовок страницы -->
<?php
    include("templates/templ_header.php");
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
        <a href="#tabs-2">Сообщения (<span class='amountOfNewMessages' id="amountUnreadNews">15</span>)</a>
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
            <li><a href="#">редактировать</a></li>
        </ul>

        <?php
        // Формируем и размещаем на странице блок для основной фотографии пользователя
        echo $this->getHTMLfotosWrapper("middle", FALSE, FALSE, $userFotoInformation['uploadedFoto']);
        ?>

        <div class="profileInformation">
            <ul class="listDescriptionBig">
                <li>
                <span
                    class="FIO"><?php echo $userCharacteristic['surname'] . " " . $userCharacteristic['name'] . " " . $userCharacteristic['secondName'] ?></span>
                </li>
                <li>
                    <br>
                </li>
                <li>
                    <span class="headOfString">Образование:</span> <?php
                    if ($userCharacteristic['currentStatusEducation'] == "0") {
                        echo "";
                    }
                    if ($userCharacteristic['currentStatusEducation'] == "нет") {
                        echo "нет";
                    }
                    if ($userCharacteristic['currentStatusEducation'] == "сейчас учусь") {
                        if (isset($userCharacteristic['almamater'])) echo $userCharacteristic['almamater'] . ", ";
                        if (isset($userCharacteristic['speciality'])) echo $userCharacteristic['speciality'] . ", ";
                        if (isset($userCharacteristic['ochnoZaochno'])) echo $userCharacteristic['ochnoZaochno'] . ", ";
                        if (isset($userCharacteristic['kurs'])) echo "курс: " . $userCharacteristic['kurs'];
                    }
                    if ($userCharacteristic['currentStatusEducation'] == "закончил") {
                        if (isset($userCharacteristic['almamater'])) echo $userCharacteristic['almamater'] . ", ";
                        if (isset($userCharacteristic['speciality'])) echo $userCharacteristic['speciality'] . ", ";
                        if (isset($userCharacteristic['ochnoZaochno'])) echo $userCharacteristic['ochnoZaochno'] . ", ";
                        if (isset($userCharacteristic['yearOfEnd'])) echo "<span style='white-space: nowrap;'>закончил в " . $userCharacteristic['yearOfEnd'] . " году</span>";
                    }
                    ?>
                </li>
                <li>
                    <span class="headOfString">Работа:</span> <?php
                    if ($userCharacteristic['statusWork'] == "не работаю") {
                        echo "не работаю";
                    } else {
                        if (isset($userCharacteristic['placeOfWork']) && $userCharacteristic['placeOfWork'] != "") {
                            echo $userCharacteristic['placeOfWork'] . ", ";
                        }
                        if (isset($userCharacteristic['workPosition'])) {
                            echo $userCharacteristic['workPosition'];
                        }
                    }
                    ?>
                </li>
                <li>
                    <span class="headOfString">Внешность:</span> <?php
                    if (isset($userCharacteristic['nationality']) && $userCharacteristic['nationality'] != "0") echo "<span style='white-space: nowrap;'>" . $userCharacteristic['nationality'] . "</span>";
                    ?>
                </li>
                <li>
                    <span class="headOfString">Пол:</span> <?php
                    if (isset($userCharacteristic['sex'])) echo $userCharacteristic['sex'];
                    ?>
                </li>
                <li>
                    <span class="headOfString">День рождения:</span> <?php
                    if (isset($userCharacteristic['birthday'])) echo $userCharacteristic['birthday'];
                    ?>
                </li>
                <li>
                    <span class="headOfString">Возраст:</span>
                    <?php
                    $date = substr($userCharacteristic['birthday'], 0, 2);
                    $month = substr($userCharacteristic['birthday'], 3, 2);
                    $year = substr($userCharacteristic['birthday'], 6, 4);
                    $birthdayForAge = mktime(0, 0, 0, $month, $date, $year);
                    $currentDate = time();
                    echo date_interval_format(date_diff(new DateTime("@{$currentDate}"), new DateTime("@{$birthdayForAge}")), '%y');
                    ?>
                </li>
                <li>
                    <br>
                </li>
                <li>
                    <span style="font-weight: bold;">Контакты:</span>
                </li>
                <li>
                    <span class="headOfString">E-mail:</span> <?php
                    if (isset($userCharacteristic['email'])) echo $userCharacteristic['email'];
                    ?>
                </li>
                <li>
                    <span class="headOfString">Телефон:</span> <?php
                    if (isset($userCharacteristic['telephon'])) echo $userCharacteristic['telephon'];
                    ?>
                </li>
                <li>
                    <br>
                </li>
                <li>
                    <span style="font-weight: bold;">Малая Родина:</span>
                </li>
                <li>
                    <span class="headOfString">Город (населенный пункт):</span> <?php
                    if (isset($userCharacteristic['cityOfBorn'])) echo $userCharacteristic['cityOfBorn'];
                    ?>
                </li>
                <li>
                    <span class="headOfString">Регион:</span> <?php
                    if (isset($userCharacteristic['regionOfBorn'])) echo $userCharacteristic['regionOfBorn'];
                    ?>
                </li>
                <li>
                    <br>
                </li>
                <li>
                    <span style="font-weight: bold;">Коротко о себе и своих интересах:</span>
                </li>
                <li>
                    <?php
                    if (isset($userCharacteristic['shortlyAboutMe'])) echo $userCharacteristic['shortlyAboutMe'];
                    ?>
                </li>
                <li>
                    <br>
                </li>
                <li>
                    <span style="font-weight: bold;">Страницы в социальных сетях:</span>
                </li>
                <li>
                    <ul class="linksToAccounts">
                        <?php
                        if (isset($userCharacteristic['vkontakte'])) echo "<li><a href='" . $userCharacteristic['vkontakte'] . "'>" . $userCharacteristic['vkontakte'] . "</a></li>";
                        ?>
                        <?php
                        if (isset($userCharacteristic['odnoklassniki'])) echo "<li><a href='" . $userCharacteristic['odnoklassniki'] . "'>" . $userCharacteristic['odnoklassniki'] . "</a></li>";
                        ?>
                        <?php
                        if (isset($userCharacteristic['facebook'])) echo "<li><a href='" . $userCharacteristic['facebook'] . "'>" . $userCharacteristic['facebook'] . "</a></li>";
                        ?>
                        <?php
                        if (isset($userCharacteristic['twitter'])) echo "<li><a href='" . $userCharacteristic['twitter'] . "'>" . $userCharacteristic['twitter'] . "</a></li>";
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
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
    <div class="shadowText">
        На этой вкладке располагается информация о важных событиях, случившихся на ресурсе Svobodno.org, как например:
        появление
        новых потенциальных арендаторов, заинтересовавшихся Вашим объявлением, или новых объявлений, которые подходят
        под
        Ваш запрос
    </div>
    <div class="news unread">
        <div class="newsHeader">
            Претендент на квартиру по адресу: улица Сибирский тракт 50 летия 107, кв 70.
            <div class="actionReaded">
                <a href="#">прочитал</a>
            </div>
            <div class="clearBoth"></div>
        </div>

        <div class="fotosWrapper">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
        <ul class="setOfInstructions">
            <li>
                <a href="#">подробнее</a>
            </li>
        </ul>
        <ul class="listDescriptionSmall">
            <li>
                <span class="headOfString">ФИО:</span>
                Ушаков Дмитрий Владимирович
            </li>
            <li>
                <span class="headOfString">Возраст:</span>
                25
            </li>
            <li>
                <span class="headOfString">Срок аренды:</span>
                долгосрочно
            </li>
            <li>
                <span class="headOfString">С кем жить:</span>
                несемейная пара
            </li>
            <li>
                <span class="headOfString">Дети:</span>
                нет
            </li>
            <li>
                <span class="headOfString">Животные:</span>
                нет
            </li>
            <li>
                <span class="headOfString">Телефон:</span>
                89221431615
            </li>
        </ul>
        <div class="clearBoth"></div>
    </div>
    <div class="news unread">
        <div class="newsHeader">
            Изменение статуса объявления
            <div class="actionReaded">
                <a href="#">прочитал</a>
            </div>
            <div class="clearBoth"></div>
        </div>
        <div class="fotosWrapper">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
        <ul class="setOfInstructions">
            <li>
                <a href="#">подробнее</a>
            </li>
        </ul>
        <ul class="listDescriptionSmall">
            <li>
                <span class="headOfString">Адрес объекта:</span>
                улица Шаумяна 107, кв 70
            </li>
            <li>
                <span class="headOfString">Статус изменен на:</span>
                <span style="color: green">объявление опубликовано</span>
            </li>
            <li>
                <span class="headOfString">Дата:</span>
                25.09.2012
            </li>
            <li>
                <span class="headOfString">Комментарий к статусу:</span>
                объявление опубликовано на ресурсе Svobodno.org, а также поставлено в очередь на автоматическую ежедневную
                публикацию на основных интернет-порталах города. Это обеспечит максимальный приток арендаторов, из
                которых
                Вы сможете выбрать наиболее ответственных и надежных
            </li>
        </ul>
        <div class="clearBoth"></div>
    </div>
    <div class="news">
        <div class="newsHeader">
            Претендент на квартиру по адресу: улица Сибирский тракт 50 летия 107, кв 70.
        </div>
        <div class="fotosWrapper">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
        <ul class="setOfInstructions">
            <li>
                <a href="#">подробнее</a>
            </li>
        </ul>
        <ul class="listDescriptionSmall">
            <li>
                <span class="headOfString">ФИО:</span>
                Ушаков Дмитрий Владимирович
            </li>
            <li>
                <span class="headOfString">Возраст:</span>
                25
            </li>
            <li>
                <span class="headOfString">Срок аренды:</span>
                долгосрочно
            </li>
            <li>
                <span class="headOfString">С кем жить:</span>
                несемейная пара
            </li>
            <li>
                <span class="headOfString">Дети:</span>
                нет
            </li>
            <li>
                <span class="headOfString">Животные:</span>
                нет
            </li>
            <li>
                <span class="headOfString">Телефон:</span>
                89221431615
            </li>
        </ul>
        <div class="clearBoth"></div>
    </div>
    <div class="news">
        <div class="newsHeader">
            Новое предложение по Вашему поиску
        </div>
        <div class="fotosWrapper">
            <div class="middleFotoWrapper">
                <img class="middleFoto" src="">
            </div>
        </div>
        <ul class="setOfInstructions">
            <li>
                <a href="#">подробнее</a>
            </li>
            <li>
                <a href="#">посмотреть на карте</a>
            </li>
        </ul>
        <ul class="listDescriptionSmall">
            <li>
                <span class="headOfString">Тип:</span> Квартира
            </li>
            <li>
                <span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
            </li>
            <li>
                <span class="headOfString">Единовременная комиссия:</span>
                <a href="#"> 3000 руб. (40%) собственнику</a>
            </li>
            <li>
                <span class="headOfString">Адрес:</span>
                улица Посадская 51
            </li>
            <li>
                <span class="headOfString">Количество комнат:</span>
                2, смежные
            </li>
            <li>
                <span class="headOfString">Площадь (жилая/общая):</span>
                22.4/34 м²
            </li>
            <li>
                <span class="headOfString">Этаж:</span>
                3 из 10
            </li>
            <li>
                <span class="headOfString">Срок сдачи:</span>
                долгосрочно
            </li>
            <li>
                <span class="headOfString">Мебель:</span>
                есть
            </li>
            <li>
                <span class="headOfString">Район:</span>
                Центр
            </li>
            <li>
                <span class="headOfString">Телефон собственника:</span>
                <a href="#">показать</a>
            </li>
        </ul>
        <div class="clearBoth"></div>
    </div>
</div>

<div id="tabs-3">
    <button id="newAdvertButton">
        Новое объявление
    </button>
    <?php
    /*echo $briefOfAdverts;*/
    //TODO: поправить как надо
    ?>
</div>

<div id="tabs-4">
    <div class="shadowText">
        На этой вкладке Вы можете задать параметры, в соответствии с которыми ресурс Svobodno.org будет осуществлять
        автоматический поиск объявлений на портале и будет оповещать Вас о появлении новых объектов по указанному в
        профиле
        e-mail
    </div>

    <?php if ($userCharacteristic['typeTenant'] !== TRUE && $correctNewSearchRequest !== TRUE && $correctEditSearchRequest === NULL): ?>
    <!-- Если пользователь еще не сформировал поисковый запрос (а значит не является арендатором) и он либо не нажимал на кнопку формирования запроса, либо нажимал, но не прошел проверку на полноту информации о пользователи, то ему доступна только кнопка формирования нового запроса. В ином случае будет отображаться сам поисковый запрос пользователя, либо форма для его заполнения -->
    <form name="createSearchRequest" method="post">
        <button type="submit" name="createSearchRequestButton" id='createSearchRequestButton' class='left-bottom'>
            Запрос на поиск
        </button>
    </form>
    <?php endif;?>

    <?php if ($userCharacteristic['typeTenant'] === TRUE && $correctEditSearchRequest !== FALSE): ?>
    <!-- Если пользователь является арендатором и (если он редактировал пар-ры поиска) после редактирования параметров поиска ошибок не обнаружено, то у пользователя уже сформирован корректный поисковый запрос, который мы и показываем на этой вкладке -->
    <div id="notEditingSearchParametersBlock" class="objectDescription">
        <ul class="setOfInstructions">
            <li><a href="#">редактировать</a></li>
            <li><a href="personal.php?action=deleteSearchRequest&tabsId=4"
                   title="Удаляет запрос на поиск - кликните по этой ссылке, когда Вы найдете недвижимость">удалить</a>
            </li>
            <br>
        </ul>
        <div id="notEditedDistricts" class="notEdited left">
            <div class="legend">
                Район
            </div>
            <table>
                <tbody>
                    <?php
                    if (isset($userSearchRequest['district']) && count($userSearchRequest['district']) != 0) { // Если район указан пользователем
                        echo "<tr><td>";
                        for ($i = 0; $i < count($userSearchRequest['district']); $i++) { // Выводим названия всех районов, в которых ищет недвижимость пользователь
                            echo $userSearchRequest['district'][$i];
                            if ($i < count($userSearchRequest['district']) - 1) echo ", ";
                        }
                        echo  "</td></tr>";
                    } else {
                        echo "<tr><td>" . "любой" . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="notEdited left">
            <div class="legend">
                Характеристика объекта
            </div>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Тип:</td>
                        <td class="objectDescriptionBody">
            <span>
            <?php
                if (isset($userSearchRequest['typeOfObject']) && $userSearchRequest['typeOfObject'] != "0") echo $userSearchRequest['typeOfObject']; else echo "любой";
                ?>
            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Количество комнат:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['amountOfRooms']) && count($userSearchRequest['amountOfRooms']) != "0") for ($i = 0; $i < count($userSearchRequest['amountOfRooms']); $i++) {
                                echo $userSearchRequest['amountOfRooms'][$i];
                                if ($i < count($userSearchRequest['amountOfRooms']) - 1) echo ", ";
                            } else echo "любое";
                            ?></span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Комнаты смежные:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['adjacentRooms']) && $userSearchRequest['adjacentRooms'] != "0") echo $userSearchRequest['adjacentRooms']; else echo "любые";
                            ?></span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Этаж:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['floor']) && $userSearchRequest['floor'] != "0") echo $userSearchRequest['floor']; else echo "любой";
                            ?></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="notEdited right">
            <div class="legend">
                Стоимость
            </div>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Арендная плата в месяц от:</td>
                        <td class="objectDescriptionBody"><?php
                            if (isset($userSearchRequest['minCost']) && $userSearchRequest['minCost'] != "0") echo "<span>" . $userSearchRequest['minCost'] . "</span> руб."; else echo "любая";
                            ?></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
                        <td class="objectDescriptionBody"><?php
                            if (isset($userSearchRequest['maxCost']) && $userSearchRequest['maxCost'] != "0") echo "<span>" . $userSearchRequest['maxCost'] . "</span> руб."; else echo "любая";
                            ?></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Залог до:</td>
                        <td class="objectDescriptionBody"><?php
                            if (isset($userSearchRequest['pledge']) && $userSearchRequest['pledge'] != "0") echo "<span>" . $userSearchRequest['pledge'] . "</span> руб."; else echo "любой";
                            ?></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Максимальная предоплата:</td>
                        <td class="objectDescriptionBody"><?php
                            if (isset($userSearchRequest['prepayment']) && $userSearchRequest['prepayment'] != "0") echo "<span>" . $userSearchRequest['prepayment'] . "</span>"; else echo "любая";
                            ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="notEditedSpecialParams" class="notEdited left" style="width: 100%;">
            <div class="legend">
                Особые параметры поиска
            </div>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Как собираетесь проживать:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['withWho']) && $userSearchRequest['withWho'] != "0") echo $userSearchRequest['withWho']; else echo "не указано";
                            ?></span></td>
                    </tr>
                    <?php
                    if ($userSearchRequest['withWho'] != "самостоятельно" && $userSearchRequest['withWho'] != "0") {
                        echo "<tr><td class='objectDescriptionItemLabel'>Информация о сожителях:</td><td class='objectDescriptionBody''><span>";
                        if (isset($userSearchRequest['linksToFriends'])) echo $userSearchRequest['linksToFriends'];
                        echo "</span></td></tr>";
                    }
                    ?>
                    <tr>
                        <td class="objectDescriptionItemLabel">Дети:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['children']) && $userSearchRequest['children'] != "0") echo $userSearchRequest['children']; else echo "не указано";
                            ?></span></td>
                    </tr>
                    <?php
                    if ($userSearchRequest['children'] != "без детей" && $userSearchRequest['children'] != "0") {
                        echo "<tr><td class='objectDescriptionItemLabel'>Количество детей и их возраст:</td><td class='objectDescriptionBody''><span>";
                        if (isset($userSearchRequest['howManyChildren'])) echo $userSearchRequest['howManyChildren'];
                        echo "</span></td></tr>";
                    }
                    ?>
                    <tr>
                        <td class="objectDescriptionItemLabel">Животные:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['animals']) && $userSearchRequest['animals'] != "0") echo $userSearchRequest['animals']; else echo "не указано";
                            ?></span></td>
                    </tr>
                    <?php
                    if ($userSearchRequest['animals'] != "без животных" && $userSearchRequest['animals'] != "0") {
                        echo "<tr><td class='objectDescriptionItemLabel'>Количество животных и их вид:</td><td class='objectDescriptionBody''><span>";
                        if (isset($userSearchRequest['howManyAnimals'])) echo $userSearchRequest['howManyAnimals'];
                        echo "</span></td></tr>";
                    }
                    ?>
                    <tr>
                        <td class="objectDescriptionItemLabel">Срок аренды:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['termOfLease']) && $userSearchRequest['termOfLease'] != "0") echo $userSearchRequest['termOfLease']; else echo "не указан";
                            ?></span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
                        <td class="objectDescriptionBody"><span><?php
                            if (isset($userSearchRequest['additionalDescriptionOfSearch'])) echo $userSearchRequest['additionalDescriptionOfSearch'];
                            ?></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="clearBoth"></div>
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
            <button type="submit" name="saveSearchParametersButton" id="saveSearchParametersButton" class="button">
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
    // Для целей ускорения загрузки перенес блок php кода сюда - это позволит браузеру грузить нужные библиотеки в то время, как сервер будет готовить представление для таблиц с данными об объектах недвижимости

    /***************************************************************************************************************
     * Оформляем полученные объявления в красивый HTML для размещения на странице
     **************************************************************************************************************/
    echo $this->getSearchResultHTML($propertyLightArr, $favoritesPropertysId, "favorites");

    ?>

</div>

</div>
<!-- /end.tabs -->

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
<script>
    // Сервер сохранит в эту переменную данные о загруженных фотографиях в формате JSON
    // Переменная uploadedFoto содержит массив объектов, каждый из которых представляет информацию по 1 фотографии
    var uploadedFoto = JSON.parse('<?php echo json_encode($userFotoInformation['uploadedFoto']);?>');
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