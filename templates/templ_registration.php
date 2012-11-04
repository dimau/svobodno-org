<?

    // Инициализируем используемые в шаблоне переменные

    $userCharacteristic = $dataArr['userCharacteristic'];
    $userFotoInformation = $dataArr['userFotoInformation'];
    $userSearchRequest = $dataArr['userSearchRequest'];

    $errors = $dataArr['errors'];

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

    <title>Форма регистрации</title>
    <meta name="description" content="Форма регистрации">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/fileuploader.css">
    <link rel="stylesheet" href="css/main.css">
    <style>

            /* Основные стили для элементов управления формы */
        .bottomControls {
            padding: 10px 0px 0px 0px;
        }

        .backButton {
            float: left;
        }

        .forwardButton, .submitButton {
            float: right;
        }

            /* Стили для страницы социальных сетей */
        .social input[type=text] {
            width: 400px;
        }

    </style>

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- Русификатор виджета календарь -->
    <script src="js/vendor/jquery.ui.datepicker-ru.js"></script>
    <!-- Загрузчик фотографий на AJAX -->
    <script src="js/vendor/fileuploader.js" type="text/javascript"></script>

</head>

<body>
<div class="page_without_footer">

<!-- Добавялем невидимый input для того, чтобы передать тип пользователя (собственник/арендатор) - это используется в JS для простановки обязательности полей для заполнения -->
<?php echo "<input type='hidden' class='userType' typeTenant='" . $user->isTenant() . "' typeOwner='" . $user->isOwner() . "'>"; ?>

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
                if (is_array($errors) && count($errors) != 0) {
                    foreach ($errors as $key => $value) {
                        echo "<li>$value</li>";
                    }
                }
                ?></ol>
        </div>
    </div>
</div>

<!-- Сформируем и вставим заголовок страницы -->
<?php
    include("templates/templ_header.php");
?>

<div class="page_main_content">

<div class="headerOfPageContentBlock">
    <div class="headerOfPage">
        Зарегистрируйтесь
    </div>
    <?php if ($userCharacteristic['typeTenant']): ?>
    <div class="importantAddInfBlock">
        <div class="importantAddInfHeader">
            Регистрация позволит:
        </div>
        <ul>
            <li>
                Записаться на просмотр любой недвижимости
            </li>
            <li>
                Получать уведомления о появлении подходящих вариантов недвижимости
            </li>
            <li>
                Добавлять объявления в избранные и в любой момент просматривать их
            </li>
            <li>
                Не указывать повторно условия поиска - система запомнит их
            </li>
        </ul>
    </div>
    <?php endif; ?>
    <div class="clearBoth"></div>
</div>

<form name="personalInformation" id="personalInformationForm" class="formWithFotos" method="post"
      enctype="multipart/form-data">
<div id="tabs">
<ul>
    <li>
        <a href="#tabs-1">Личные данные</a>
    </li>
    <li>
        <a href="#tabs-2">Образование / Работа</a>
    </li>
    <li>
        <a href="#tabs-3">Социальные сети</a>
    </li>
    <?php if ($userCharacteristic['typeTenant']): ?>
    <li>
        <a href="#tabs-4">Что ищете?</a>
    </li>
    <?php endif; ?>
</ul>

<div id="tabs-1">
    <div class="shadowText">
        Информация, указаннная при регистрации, необходима для того, чтобы представить Вас собственникам тех объектов,
        которыми Вы заинтересутесь.
        <br>
        <span class="required">* </span> - обязательное для заполнения поле
    </div>

    <?php
        // Подключим форму для ввода и редактирования данных о ФИО, логине, контактах пользователя, а также о фотографиях
        include "templates/templ_editablePersonalFIO.php";
    ?>

    <div class="bottomControls">
        <button class="forwardButton">Далее</button>
        <div class="clearBoth"></div>
    </div>
</div>

<div id="tabs-2">
    <div class="shadowText">
        Данные об образовании и работе арендатора - одни из самых востребованных для любого собственника жилья. Эта
        информация предоставляется собственникам только тех объектов, которыми Вы заинтересуетесь.
    </div>

    <?php
        // Подключим форму для ввода и редактирования данных об образовании, работе и месте рождения
        include "templates/templ_editablePersonalEducAndWork.php";
    ?>

    <div class="bottomControls">
        <button class="backButton">Назад</button>
        <button class="forwardButton">Далее</button>
        <div class="clearBoth"></div>
    </div>
</div>

<div id="tabs-3">
    <div class="shadowText">
        Укажите, пожалуйста, адрес Вашей личной страницы минимум в одной социальной сети. Это позволит системе
        представить Вас собственникам (только тех объектов, которыми Вы сами заинтересуетесь).
    </div>

    <?php
        // Подключим форму для ввода и редактирования данных о социальных сетях пользователя
        include "templates/templ_editablePersonalSocial.php";
    ?>

    <?php if ($userCharacteristic['typeTenant']): ?>
    <div class="bottomControls">
        <button class="backButton">Назад</button>
        <button class="forwardButton">Далее</button>
        <div class="clearBoth"></div>
    </div>
    <?php endif; ?>
    <?php if (!$userCharacteristic['typeTenant']): ?>
    <div class="bottomControls">
        <div style="float: right; margin-bottom: 10px; text-align: left;">
            <input type="checkbox" name="lic" id="lic" value="yes" <?php if ($user->lic == "yes") echo "checked";?>> Я
            принимаю условия <a
            href="#">лицензионного соглашения</a>
        </div>
        <div class="clearBoth"></div>
        <button class="backButton">Назад</button>
        <button type="submit" name="submitButton" class="submitButton">Отправить</button>
        <div class="clearBoth"></div>
    </div>
    <?php endif; ?>
</div>

<?php if ($userCharacteristic['typeTenant']): ?>
<div id="tabs-4">
    <div class="shadowText">
        Заполните форму как можно подробнее, это позволит системе подобрать для Вас наиболее интересные предложения
    </div>
    <div id="extendedSearchParametersBlock">
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
                                    <option value="0" <?php if ($user->typeOfObject == "0") echo "selected";?>></option>
                                    <option value="квартира" <?php if ($user->typeOfObject == "квартира") echo "selected";?>>
                                        квартира
                                    </option>
                                    <option value="комната" <?php if ($user->typeOfObject == "комната") echo "selected";?>>
                                        комната
                                    </option>
                                    <option value="дом" <?php if ($user->typeOfObject == "дом") echo "selected";?>>дом,
                                        коттедж
                                    </option>
                                    <option value="таунхаус" <?php if ($user->typeOfObject == "таунхаус") echo "selected";?>>
                                        таунхаус
                                    </option>
                                    <option value="дача" <?php if ($user->typeOfObject == "дача") echo "selected";?>>дача
                                    </option>
                                    <option value="гараж" <?php if ($user->typeOfObject == "гараж") echo "selected";?>>гараж
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
                                    foreach ($user->amountOfRooms as $value) {
                                        if ($value == "1") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                1
                                <input type="checkbox" value="2"
                                       name="amountOfRooms[]" <?php
                                    foreach ($user->amountOfRooms as $value) {
                                        if ($value == "2") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                2
                                <input type="checkbox" value="3"
                                       name="amountOfRooms[]" <?php
                                    foreach ($user->amountOfRooms as $value) {
                                        if ($value == "3") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                3
                                <input type="checkbox" value="4"
                                       name="amountOfRooms[]" <?php
                                    foreach ($user->amountOfRooms as $value) {
                                        if ($value == "4") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                4
                                <input type="checkbox" value="5"
                                       name="amountOfRooms[]" <?php
                                    foreach ($user->amountOfRooms as $value) {
                                        if ($value == "5") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                5
                                <input type="checkbox" value="6"
                                       name="amountOfRooms[]" <?php
                                    foreach ($user->amountOfRooms as $value) {
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
                                    <option value="0" <?php if ($user->adjacentRooms == "0") echo "selected";?>></option>
                                    <option
                                        value="не имеет значения" <?php if ($user->adjacentRooms == "не имеет значения") echo "selected";?>>
                                        не
                                        имеет значения
                                    </option>
                                    <option
                                        value="только изолированные" <?php if ($user->adjacentRooms == "только изолированные") echo "selected";?>>
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
                                    <option value="0" <?php if ($user->floor == "0") echo "selected";?>></option>
                                    <option value="любой" <?php if ($user->floor == "любой") echo "selected";?>>любой</option>
                                    <option value="не первый" <?php if ($user->floor == "не первый") echo "selected";?>>не
                                        первый
                                    </option>
                                    <option
                                        value="не первый и не последний" <?php if ($user->floor == "не первый и не последний") echo "selected";?>>
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
                                       maxlength="8" <?php echo "value='$user->minCost'";?>>
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
                                       maxlength="8" <?php echo "value='$user->maxCost'";?>>
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
                                       maxlength="8" <?php echo "value='$user->pledge'";?>>
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
                                    <option value="0" <?php if ($user->prepayment == "0") echo "selected";?>></option>
                                    <option value="нет" <?php if ($user->prepayment == "нет") echo "selected";?>>нет</option>
                                    <option value="1 месяц" <?php if ($user->prepayment == "1 месяц") echo "selected";?>>1
                                        месяц
                                    </option>
                                    <option value="2 месяца" <?php if ($user->prepayment == "2 месяца") echo "selected";?>>2
                                        месяца
                                    </option>
                                    <option value="3 месяца" <?php if ($user->prepayment == "3 месяца") echo "selected";?>>3
                                        месяца
                                    </option>
                                    <option value="4 месяца" <?php if ($user->prepayment == "4 месяца") echo "selected";?>>4
                                        месяца
                                    </option>
                                    <option value="5 месяцев" <?php if ($user->prepayment == "5 месяцев") echo "selected";?>>5
                                        месяцев
                                    </option>
                                    <option value="6 месяцев" <?php if ($user->prepayment == "6 месяцев") echo "selected";?>>6
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
                            foreach ($user->district as $valueDistrict) {
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
                                <option value="0" <?php if ($user->withWho == "0") echo "selected";?>></option>
                                <option
                                    value="самостоятельно" <?php if ($user->withWho == "самостоятельно") echo "selected";?>>
                                    самостоятельно
                                </option>
                                <option value="семья" <?php if ($user->withWho == "семья") echo "selected";?>>семьей
                                </option>
                                <option value="пара" <?php if ($user->withWho == "пара") echo "selected";?>>парой
                                </option>
                                <option value="2 мальчика" <?php if ($user->withWho == "2 мальчика") echo "selected";?>>2
                                    мальчика
                                </option>
                                <option value="2 девочки" <?php if ($user->withWho == "2 девочки") echo "selected";?>>2
                                    девочки
                                </option>
                                <option value="со знакомыми" <?php if ($user->withWho == "со знакомыми") echo "selected";?>>со
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
                                      rows="3"><?php echo $user->linksToFriends;?></textarea>
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
                                <option value="0" <?php if ($user->children == "0") echo "selected";?>></option>
                                <option value="без детей" <?php if ($user->children == "без детей") echo "selected";?>>без
                                    детей
                                </option>
                                <option
                                    value="с детьми младше 4-х лет" <?php if ($user->children == "с детьми младше 4-х лет") echo "selected";?>>
                                    с детьми
                                    младше 4-х лет
                                </option>
                                <option
                                    value="с детьми старше 4-х лет" <?php if ($user->children == "с детьми старше 4-х лет") echo "selected";?>>
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
                                      rows="3"><?php echo $user->howManyChildren;?></textarea>
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
                                <option value="0" <?php if ($user->animals == "0") echo "selected";?>></option>
                                <option value="без животных" <?php if ($user->animals == "без животных") echo "selected";?>>
                                    без
                                    животных
                                </option>
                                <option
                                    value="с животным(ми)" <?php if ($user->animals == "с животным(ми)") echo "selected";?>>с
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
                                      rows="3"><?php echo $user->howManyAnimals;?></textarea>
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
                                <option value="0" <?php if ($user->termOfLease == "0") echo "selected";?>></option>
                                <option
                                    value="длительный срок" <?php if ($user->termOfLease == "длительный срок") echo "selected";?>>
                                    длительный срок (от года)
                                </option>
                                <option
                                    value="несколько месяцев" <?php if ($user->termOfLease == "несколько месяцев") echo "selected";?>>
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
                                      rows="4"><?php echo $user->additionalDescriptionOfSearch;?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    </div>

    <div class="bottomControls">
        <div style="float: right; margin-bottom: 10px; text-align: left;">
            <input type="checkbox" name="lic" id="lic" value="yes" <?php if ($user->lic == "yes") echo "checked";?>> Я
            принимаю условия <a
            href="#">лицензионного соглашения</a>
        </div>
        <div class="clearBoth"></div>
        <button class="backButton">Назад</button>
        <button type="submit" name="submitButton" class="submitButton">Отправить</button>
        <div class="clearBoth"></div>
    </div>

</div>
<!-- /end.tabs-4 -->
    <?php endif;?>
</div>
<!-- /end.tabs -->

</form>
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

<!-- JavaScript -->
<script>
    // Сервер сохранит в эту переменную данные о загруженных фотографиях в формате JSON
    // Переменная uploadedFoto содержит массив объектов, каждый из которых представляет информацию по 1 фотографии
    var uploadedFoto = JSON.parse('<?php echo json_encode($userFotoInformation['uploadedFoto']);?>');
</script>
<script src="js/main.js"></script>
<script src="js/registration.js"></script>
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