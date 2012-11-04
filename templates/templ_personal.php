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
<?php echo "<input type='hidden' class='userType' typeTenant='" . $userCharacteristic['typeTenant'] . "' typeOwner='" . $userCharacteristic['typeOwner'] . "' correctNewSearchRequest='" . $correctEditSearchRequest . "'>"; ?>

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
    <div class="setOfInstructions">
        <a href="#">редактировать</a>
        <br>
    </div>
    <?php

        // Формируем и размещаем на странице блок для основной фотографии пользователя
        // TODO: переделать echo $user->getHTMLfotosWrapper("middle", FALSE);

    ?>
    <div class="profileInformation">
        <ul class="listDescription">
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
<form method="post" name="profileParameters" id="editingProfileParametersBlock" class="descriptionFieldsetsWrapper formWithFotos" enctype="multipart/form-data"
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
        На этой вкладке располагается информация о важных событиях, случившихся на ресурсе Хани Хом, как например:
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
        <ul class="listDescription">
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
        <ul class="listDescription">
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
                объявление опубликовано на ресурсе Хани Хом, а также поставлено в очередь на автоматическую ежедневную
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
        <ul class="listDescription">
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
        <ul class="listDescription">
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
    На этой вкладке Вы можете задать параметры, в соответствии с которыми ресурс Хани Хом будет осуществлять
    автоматический поиск объявлений на портале и будет оповещать Вас о появлении новых объектов по указанному в
    профиле
    e-mail
</div>
<?php if ($userCharacteristic['typeTenant'] != TRUE && $correctNewSearchRequest !== TRUE && $correctEditSearchRequest === NULL): ?>
<!-- Если пользователь еще не сформировал поисковый запрос (а значит не является арендатором) и он либо не нажимал на кнопку формирования запроса, либо нажимал, но не прошел проверку на полноту информации о пользователи, то ему доступна только кнопка формирования нового запроса. В ином случае будет отображаться сам поисковый запрос пользователя, либо форма для его заполнения -->
<form name="createSearchRequest" method="post">
    <button type="submit" name="createSearchRequestButton" id='createSearchRequestButton' class='left-bottom'>
        Запрос на поиск
    </button>
</form>
    <?php endif;?>
<?php if ($userCharacteristic['typeTenant'] == TRUE && $correctEditSearchRequest !== FALSE): ?>
<!-- Если пользователь является арендатором и (если он редактировал пар-ры поиска) после редактирования параметров поиска ошибок не обнаружено, то у пользователя уже сформирован корректный поисковый запрос, который мы и показываем на этой вкладке -->
    <!--
<div id="notEditingSearchParametersBlock" class="objectDescription">
    <div class="setOfInstructions">
        <li><a href="#">редактировать</a></li>
        <li><a href="personal.php?action=deleteSearchRequest&tabsId=4"
               title="Удаляет запрос на поиск - кликните по этой ссылке, когда Вы найдете недвижимость">удалить</a>
        </li>
        <br>
    </div>
    <fieldset class="notEdited">
        <legend>
            Характеристика объекта
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Тип:</td>
                    <td class="objectDescriptionBody">
            <span>
            <?php
    //if (isset($user->typeOfObject) && $user->typeOfObject != "0") echo $user->typeOfObject; else echo "любой";
    ?>
            </span>
                    </td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Количество комнат:</td>
                    <td class="objectDescriptionBody"><span><?php
    /*  if (isset($user->amountOfRooms) && count($user->amountOfRooms) != "0") for ($i = 0; $i < count($user->amountOfRooms); $i++) {
    echo $amountOfRooms[$i];
    if ($i < count($amountOfRooms) - 1) echo ", ";
} else echo "любое"; */
    ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Комнаты смежные:</td>
                    <td class="objectDescriptionBody"><span><?php
    // if (isset($adjacentRooms) && $adjacentRooms != "0") echo $adjacentRooms; else echo "любые";
    ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Этаж:</td>
                    <td class="objectDescriptionBody"><span><?php
    //  if (isset($floor) && $floor != "0") echo $floor; else echo "любой";
    ?></span></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <fieldset class="notEdited">
        <legend>
            Стоимость
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Арендная плата в месяц от:</td>
                    <td class="objectDescriptionBody"><?php
    //   if (isset($minCost) && $minCost != "0") echo "<span>" . $minCost . "</span> руб."; else echo "любая";
    ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
                    <td class="objectDescriptionBody"><?php
    //   if (isset($maxCost) && $maxCost != "0") echo "<span>" . $maxCost . "</span> руб."; else echo "любая";
    ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Залог до:</td>
                    <td class="objectDescriptionBody"><?php
    //  if (isset($pledge) && $pledge != "0") echo "<span>" . $pledge . "</span> руб."; else echo "любой";
    ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Максимальная предоплата:</td>
                    <td class="objectDescriptionBody"><?php
    //  if (isset($prepayment) && $prepayment != "0") echo "<span>" . $prepayment . "</span>"; else echo "любая";
    ?></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <fieldset class="notEdited">
        <legend>
            Район
        </legend>
        <table>
            <tbody>
                <?php
    /*  if (isset($district) && count($district) != 0) { // Если район указан пользователем
       echo "<tr><td>";
       for ($i = 0; $i < count($district); $i++) { // Выводим названия всех районов, в которых ищет недвижимость пользователь
           echo $district[$i];
           if ($i < count($district) - 1) echo ", ";
       }
       echo  "</td></tr>";
   } else {
       echo "<tr><td>" . "любой" . "</td></tr>";
   } */
    ?>
            </tbody>
        </table>
    </fieldset>
    <div class="clearBoth"></div>
    <fieldset class="notEdited">
        <legend>
            Особые параметры поиска
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Как собираетесь проживать:</td>
                    <td class="objectDescriptionBody"><span><?php
    //   if (isset($withWho) && $withWho != "0") echo $withWho; else echo "не указано";
    ?></span></td>
                </tr>
                <?php
    /*  if ($withWho != "самостоятельно" && $withWho != "0") {
       echo "<tr><td class='objectDescriptionItemLabel'>Информация о сожителях:</td><td class='objectDescriptionBody''><span>";
       if (isset($linksToFriends)) echo $linksToFriends;
       echo "</span></td></tr>";
   } */
    ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Дети:</td>
                    <td class="objectDescriptionBody"><span><?php
    //   if (isset($children) && $children != "0") echo $children; else echo "не указано";
    ?></span></td>
                </tr>
                <?php
    /* if ($children != "без детей" && $children != "0") {
       echo "<tr><td class='objectDescriptionItemLabel'>Количество детей и их возраст:</td><td class='objectDescriptionBody''><span>";
       if (isset($howManyChildren)) echo $howManyChildren;
       echo "</span></td></tr>";
   } */
    ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Животные:</td>
                    <td class="objectDescriptionBody"><span><?php
    //    if (isset($animals) && $animals != "0") echo $animals; else echo "не указано";
    ?></span></td>
                </tr>
                <?php
    /*   if ($animals != "без животных" && $animals != "0") {
       echo "<tr><td class='objectDescriptionItemLabel'>Количество животных и их вид:</td><td class='objectDescriptionBody''><span>";
       if (isset($howManyAnimals)) echo $howManyAnimals;
       echo "</span></td></tr>";
   } */
    ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Срок аренды:</td>
                    <td class="objectDescriptionBody"><span><?php
    //     if (isset($termOfLease) && $termOfLease != "0") echo $termOfLease; else echo "не указан";
    ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
                    <td class="objectDescriptionBody"><span><?php
    //    if (isset($additionalDescriptionOfSearch)) echo $additionalDescriptionOfSearch;
    ?></span></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>
    <?php endif;?>
<?php if ($userCharacteristic['typeTenant'] === TRUE || $correctNewSearchRequest === TRUE || $correctEditSearchRequest === FALSE): ?>
<!-- Если пользователь является арендатором, то вместе с отображением текущих параметров поискового запроса мы выдаем скрытую форму для их редактирования, также мы выдаем видимую форму для редактирования параметров поиска в случае, если пользователь нажал на кнопку Нового поискового запроса и проверка на корректность его данных Профиля профла успешно, а также в случае если пользователь корректировал данные поискового запроса, но они не прошли проверку -->
<form method="post" name="searchParameters" id="extendedSearchParametersBlock">
    <div id="leftBlockOfSearchParameters" style="display: inline-block;">
        <fieldset class="edited">
            <legend>
                Характеристика объекта
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="itemLabel">
                            Тип
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="typeOfObject" id="typeOfObject">
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
                        </td>
                    </tr>
                    <tr notavailability="typeOfObject_гараж">
                        <td class="itemLabel">
                            Количество комнат
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="checkbox" value="1" name="amountOfRooms[]"
                                <?php
                                foreach ($userSearchRequest['amountOfRooms'] as $value) {
                                    if ($value == "1") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            1
                            <input type="checkbox" value="2"
                                   name="amountOfRooms[]" <?php
                                foreach ($userSearchRequest['amountOfRooms'] as $value) {
                                    if ($value == "2") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            2
                            <input type="checkbox" value="3"
                                   name="amountOfRooms[]" <?php
                                foreach ($userSearchRequest['amountOfRooms'] as $value) {
                                    if ($value == "3") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            3
                            <input type="checkbox" value="4"
                                   name="amountOfRooms[]" <?php
                                foreach ($userSearchRequest['amountOfRooms'] as $value) {
                                    if ($value == "4") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            4
                            <input type="checkbox" value="5"
                                   name="amountOfRooms[]" <?php
                                foreach ($userSearchRequest['amountOfRooms'] as $value) {
                                    if ($value == "5") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            5
                            <input type="checkbox" value="6"
                                   name="amountOfRooms[]" <?php
                                foreach ($userSearchRequest['amountOfRooms'] as $value) {
                                    if ($value == "6") {
                                        echo "checked";
                                        break;
                                    }
                                }
                                ?>>
                            6...
                        </td>
                    </tr>
                    <tr notavailability="typeOfObject_гараж">
                        <td class="itemLabel">
                            Комнаты смежные
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="adjacentRooms" id="adjacentRooms">
                                <option value="0" <?php if ($userSearchRequest['adjacentRooms'] == "0") echo "selected";?>></option>
                                <option
                                    value="не имеет значения" <?php if ($userSearchRequest['adjacentRooms'] == "не имеет значения") echo "selected";?>>
                                    не
                                    имеет значения
                                </option>
                                <option
                                    value="только изолированные" <?php if ($userSearchRequest['adjacentRooms'] == "только изолированные") echo "selected";?>>
                                    только изолированные
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr notavailability="typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
                        <td class="itemLabel">
                            Этаж
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="floor" id="floor">
                                <option value="0" <?php if ($userSearchRequest['floor'] == "0") echo "selected";?>></option>
                                <option value="любой" <?php if ($userSearchRequest['floor'] == "любой") echo "selected";?>>любой</option>
                                <option value="не первый" <?php if ($userSearchRequest['floor'] == "не первый") echo "selected";?>>не
                                    первый
                                </option>
                                <option
                                    value="не первый и не последний" <?php if ($userSearchRequest['floor'] == "не первый и не последний") echo "selected";?>>
                                    не первый и не
                                    последний
                                </option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset class="edited cost">
            <legend>
                Стоимость
            </legend>
            <table>
                <tbody>
                    <tr title="В месяц за аренду недвижимости с учетом стоимости коммунальных услуг (если они оплачиваются дополнительно)">
                        <td class="itemLabel">
                            Арендная плата от
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="text" name="minCost" id="minCost"
                                   maxlength="8" value='<?php echo $userSearchRequest['minCost'];?>'>
                            руб.
                        </td>
                    </tr>
                    <tr title="В месяц за аренду недвижимости с учетом стоимости коммунальных услуг (если они оплачиваются дополнительно)">
                        <td class="itemLabel">
                            Арендная плата до
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="text" name="maxCost" id="maxCost"
                                   maxlength="8" value='<?php echo $userSearchRequest['maxCost'];?>'>
                            руб.
                        </td>
                    </tr>
                    <tr title="Какую сумму Вы готовы передать собственнику в качестве возвращаемого гарантийного депозита">
                        <td class="itemLabel">
                            Залог до
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <input type="text" name="pledge" id="pledge"
                                   maxlength="8" value='<?php echo $userSearchRequest['pledge'];?>'>
                            руб.
                        </td>
                    </tr>
                    <tr title="Какую предоплату за проживание Вы готовы внести">
                        <td class="itemLabel">
                            Макс. предоплата
                        </td>
                        <td class="itemRequired">
                        </td>
                        <td class="itemBody">
                            <select name="prepayment" id="prepayment">
                                <option value="0" <?php if ($userSearchRequest['prepayment'] == "0") echo "selected";?>></option>
                                <option value="нет" <?php if ($userSearchRequest['prepayment'] == "нет") echo "selected";?>>нет</option>
                                <option value="1 месяц" <?php if ($userSearchRequest['prepayment'] == "1 месяц") echo "selected";?>>1
                                    месяц
                                </option>
                                <option value="2 месяца" <?php if ($userSearchRequest['prepayment'] == "2 месяца") echo "selected";?>>2
                                    месяца
                                </option>
                                <option value="3 месяца" <?php if ($userSearchRequest['prepayment'] == "3 месяца") echo "selected";?>>3
                                    месяца
                                </option>
                                <option value="4 месяца" <?php if ($userSearchRequest['prepayment'] == "4 месяца") echo "selected";?>>4
                                    месяца
                                </option>
                                <option value="5 месяцев" <?php if ($userSearchRequest['prepayment'] == "5 месяцев") echo "selected";?>>5
                                    месяцев
                                </option>
                                <option value="6 месяцев" <?php if ($userSearchRequest['prepayment'] == "6 месяцев") echo "selected";?>>6
                                    месяцев
                                </option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div id="rightBlockOfSearchParameters">
        <fieldset class="edited">
            <legend>
                Район
            </legend>
            <ul>
                <?php
                if (isset($allDistrictsInCity)) {
                    foreach ($allDistrictsInCity as $value) { // Для каждого идентификатора района и названия формируем чекбокс
                        echo "<li><input type='checkbox' name='district[]' value='" . $value['name'] . "'";
                        foreach ($userSearchRequest['district'] as $valueDistrict) {
                            if ($valueDistrict == $value['name']) {
                                echo "checked";
                                break;
                            }
                        }
                        echo "> " . $value['name'] . "</li>";
                    }
                }
                ?>
            </ul>
        </fieldset>
    </div>
    <!-- /end.rightBlockOfSearchParameters -->
    <fieldset class="edited private">
        <legend>
            Особые параметры поиска
        </legend>
        <table>
            <tbody>
                <tr notavailability="typeOfObject_гараж">
                    <td class="itemLabel">
                        Как собираетесь проживать
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="withWho" id="withWho">
                            <option value="0" <?php if ($userSearchRequest['withWho'] == "0") echo "selected";?>></option>
                            <option
                                value="самостоятельно" <?php if ($userSearchRequest['withWho'] == "самостоятельно") echo "selected";?>>
                                самостоятельно
                            </option>
                            <option value="семья" <?php if ($userSearchRequest['withWho'] == "семья") echo "selected";?>>семьей
                            </option>
                            <option value="пара" <?php if ($userSearchRequest['withWho'] == "пара") echo "selected";?>>парой
                            </option>
                            <option value="2 мальчика" <?php if ($userSearchRequest['withWho'] == "2 мальчика") echo "selected";?>>2
                                мальчика
                            </option>
                            <option value="2 девочки" <?php if ($userSearchRequest['withWho'] == "2 девочки") echo "selected";?>>2
                                девочки
                            </option>
                            <option value="со знакомыми" <?php if ($userSearchRequest['withWho'] == "со знакомыми") echo "selected";?>>со
                                знакомыми
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="withWhoDescription" style="display: none;">
                    <td class="itemLabel" colspan="3">
                        Что Вы можете сказать о сожителях:
                    </td>
                </tr>
                <tr class="withWhoDescription" style="display: none;">
                    <td colspan="3">
                        <textarea name="linksToFriends" id="linksToFriends"
                                  rows="3"><?php echo $userSearchRequest['linksToFriends'];?></textarea>
                    </td>
                </tr>

                <tr notavailability="typeOfObject_гараж">
                    <td class="itemLabel">
                        Дети
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="children" id="children">
                            <option value="0" <?php if ($userSearchRequest['children'] == "0") echo "selected";?>></option>
                            <option value="без детей" <?php if ($userSearchRequest['children'] == "без детей") echo "selected";?>>без
                                детей
                            </option>
                            <option
                                value="с детьми младше 4-х лет" <?php if ($userSearchRequest['children'] == "с детьми младше 4-х лет") echo "selected";?>>
                                с детьми
                                младше 4-х лет
                            </option>
                            <option
                                value="с детьми старше 4-х лет" <?php if ($userSearchRequest['children'] == "с детьми старше 4-х лет") echo "selected";?>>
                                с детьми
                                старше 4-х лет
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="childrenDescription" style="display: none;">
                    <td class="itemLabel" colspan="3">
                        Сколько у Вас детей и какого возраста:
                    </td>
                </tr>
                <tr class="childrenDescription" style="display: none;">
                    <td colspan="3">
                        <textarea name="howManyChildren" id="howManyChildren"
                                  rows="3"><?php echo $userSearchRequest['howManyChildren'];?></textarea>
                    </td>
                </tr>

                <tr notavailability="typeOfObject_гараж">
                    <td class="itemLabel">
                        Домашние животные
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="animals" id="animals">
                            <option value="0" <?php if ($userSearchRequest['animals'] == "0") echo "selected";?>></option>
                            <option value="без животных" <?php if ($userSearchRequest['animals'] == "без животных") echo "selected";?>>
                                без
                                животных
                            </option>
                            <option
                                value="с животным(ми)" <?php if ($userSearchRequest['animals'] == "с животным(ми)") echo "selected";?>>с
                                животным(ми)
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="animalsDescription" style="display: none;">
                    <td class="itemLabel" colspan="3">
                        Сколько у Вас животных и какого вида:
                    </td>
                </tr>
                <tr class="animalsDescription" style="display: none;">
                    <td colspan="3">
                        <textarea name="howManyAnimals" id="howManyAnimals"
                                  rows="3"><?php echo $userSearchRequest['howManyAnimals'];?></textarea>
                    </td>
                </tr>

                <tr>
                    <td class="itemLabel">
                        Срок аренды
                    </td>
                    <td class="itemRequired typeTenantRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="termOfLease" id="termOfLease">
                            <option value="0" <?php if ($userSearchRequest['termOfLease'] == "0") echo "selected";?>></option>
                            <option
                                value="длительный срок" <?php if ($userSearchRequest['termOfLease'] == "длительный срок") echo "selected";?>>
                                длительный срок (от года)
                            </option>
                            <option
                                value="несколько месяцев" <?php if ($userSearchRequest['termOfLease'] == "несколько месяцев") echo "selected";?>>
                                несколько месяцев (до года)
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="itemLabel">
                        Дополнительные условия поиска:
                    </td>
                    <td class="itemRequired">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <textarea name="additionalDescriptionOfSearch" id="additionalDescriptionOfSearch"
                                  rows="4"><?php echo $userSearchRequest['additionalDescriptionOfSearch'];?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <div class="clearBoth"></div>
    <div class="bottomButton">
        <a href="personal.php?tabsId=4" style="margin-right: 10px;">Отмена</a>
        <button type="submit" name="saveSearchParametersButton" id="saveSearchParametersButton" class="button">
            Сохранить
        </button>
    </div>

    <div class="clearBoth"></div>
</form>
<!-- /end.extendedSearchParametersBlock -->
    <?php endif;?>
</div>
<!-- /end.tabs-4 -->
<div id="tabs-5">

    <?php
    // Для целей ускорения загрузки перенес блок php кода сюда - это позволит браузеру грузить нужные библиотеки в то время, как сервер будет готовить представление для таблиц с данными об объектах недвижимости

    /***************************************************************************************************************
     * Оформляем полученные объявления в красивый HTML для размещения на странице
     **************************************************************************************************************/
    //echo getSearchResultHTML($propertyLightArr, $userId, "favorites");

    ?>

</div>

</div><!-- /end.tabs -->

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
<script>
    // Сервер сохранит в эту переменную данные о загруженных фотографиях в формате JSON
    // Переменная uploadedFoto содержит массив объектов, каждый из которых представляет информацию по 1 фотографии
    var uploadedFoto = JSON.parse('<?php echo json_encode($userFotoInformation['uploadedFoto']);?>');
</script>
<script src="js/main.js"></script>
<script src="js/personal.js"></script>
<!-- TODO: тест <script src="../js/searchResult.js"></script>-->
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