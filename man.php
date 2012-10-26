<?php
    include_once 'lib/connect.php'; //подключаемся к БД
    include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

    /*************************************************************************************
     * Если в строке не указан идентификатор интересующего (целевого) пользователя, то пересылаем нашего пользователя на спец. страницу
     ************************************************************************************/
    $targetUserId = "0";
    if (isset($_GET['compId'])) {
        $targetUserId = ($_GET['compId'] - 2) / 5; // Получаем идентификатор пользователя для показа его страницы из строки запроса
    } else {
        header('Location: 404.html'); // Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет к списку его объявлений
    }

    /*************************************************************************************
     * Проверяем, имеет ли право даннй пользователь смотреть анкету целевого пользователя
     *
     * Правила следующие:
     *
     * Неавторизованный пользователь не имеет права смотреть чью-либо анкету
     *
     * Арендатор может смотреть анкеты собственников тех объектов недвижимости, у которых он нажал на кнопку "Получить контакты собственника" и получил их.
     * Если собственник снял с публикации объект недвижимости, которым интересовался арендатор, то арендатор теряет право смотреть анкету этого собственника
     * Если арендатор удалил поисковый запрос (то есть перестал быть арендатором), то он теряет право смотреть любые анкеты собственников, к которым имел доступ ранее
     *
     * Собственник может смотреть анкеты арендаторов, которые заинтересовались его объектом недвижимости (нажали на кнопку "Получить контакты собственника").
     * Собственник теряет право смотреть анкету арендатора, если тот удалил свой поисковый запрос (то есть перестал быть арендатором)
     * Если собственник снял с публикации свое объявление, то информация о всех арендаторах, интересовавшихся этим объектом удаляется через некоторое время (предположительно - 10 дней), таким образом собственник, в том числе, и теряет право смотреть их анкеты
     ************************************************************************************/

    // Если пользователь не авторизован, то он не сможет посмотреть ни одной анкеты
    $userId = login();
    if ($userId == FALSE) {
        header('Location: 404.html'); //TODO: реализовать страницу Отказано в доступе
    }

    // Получаем список пользователей, чьей недвижимостью интересовался наш пользователь ($userId) в качестве арендатора, и чьи анкеты он имеет право смотреть
    $visibleUsersIdOwners = array();
    if ($rez = mysql_query("SELECT interestingPropertysId FROM searchrequests WHERE userId = '" . $userId . "'")) {
        if ($row = mysql_fetch_assoc($rez)) {
            $interestingPropertysId = unserialize($row['interestingPropertysId']);

            // По каждому объекту недвижимости выясняем статус и собственника. Если статус = опубликовано, то собственника добавляем в массив ($visibleUsersIdOwners)
            if ($interestingPropertysId != FALSE && is_array($interestingPropertysId) && count($interestingPropertysId) != 0) {
                // Составляем условие запроса к БД, указывая интересующие нас id объявлений
                $selectValue = "";
                for ($i = 0; $i < count($interestingPropertysId); $i++) {
                    $selectValue .= " id = '" . $interestingPropertysId[$i] . "'";
                    if ($i < count($interestingPropertysId) - 1) $selectValue .= " OR";
                }
                // Перебираем полученные строки из таблицы, каждая из которых соответствует 1 объявлению
                if ($rez = mysql_query("SELECT userId, status FROM property WHERE " . $selectValue)) {
                    for ($i = 0; $i < mysql_num_rows($rez); $i++) {
                        if ($row = mysql_fetch_assoc($rez)) {
                            if ($row['status'] == "опубликовано") {
                                $visibleUsersIdOwners[] = $row['userId'];
                            }
                        }
                    }
                }
            }
        }
    }

    // Получаем список пользователей, которые интересовались недвижимостью нашего пользователя ($userId), теперь он выступает в качестве собственника
    // Проверяем, является ли еще целевой пользователь арендатором, если нет, то собственник не имеет права смотреть его анкету. Таким образом реализуется правило: собственник может видеть только анкеты тех пользователей, которые заинтересовались его недвижимостью и в текущий момент времени являются арендаторами (= имеют поисковый запрос)
    $visibleUsersIdTenants = array();
    if ($rez = mysql_query("SELECT typeTenant FROM users WHERE id = '" . $targetUserId . "'")) {
        if ($row = mysql_fetch_assoc($rez)) {
            // Формировать список имеет смысл только, если целевой пользователь на текущий момент времени является арендатором. В ином случае, доступ к анкете целевого пользователя для собственников - закрыт
            if ($row['typeTenant'] == "true") {
                if ($rez = mysql_query("SELECT visibleUsersId FROM property WHERE userId = '" . $userId . "'")) {
                    for ($i = 0; $i < mysql_num_rows($rez); $i++) {
                        if ($row = mysql_fetch_assoc($rez)) $visibleUsersIdTenants = $visibleUsersIdTenants + unserialize($row['visibleUsersId']);
                    }
                }
            }
        }
    }

    // Проверяем, есть ли среди этого списка текущий целевой пользователь ($targetUserId)
    if (!in_array($targetUserId, $visibleUsersIdOwners) && !in_array($targetUserId, $visibleUsersIdTenants) && $userId != $targetUserId) {
        header('Location: 404.html'); //TODO: реализовать страницу Отказано в доступе
    }

    /*************************************************************************************
     * Получаем информацию о целевом пользователе по его идентификатору, указанному в GET запросе
     ************************************************************************************/

    // Данные профиля пользователя
    $rowTargetUser = array();
    $rezTargetUser = mysql_query("SELECT * FROM users WHERE id = '" . $targetUserId . "'");
    if ($rezTargetUser != FALSE) $rowTargetUser = mysql_fetch_assoc($rezTargetUser);

    // Получаем информацию о личных фотографиях пользователя
    // Массив $rowUserFotos представляет собой массив массивов, каждый из которых содержит информацию об одной фотографии пользователя
    $rowUserFotos = array();
    $rezUserFotos = mysql_query("SELECT * FROM userfotos WHERE userId = '" . $targetUserId . "'");
    if ($rezUserFotos != FALSE) {
        for ($i = 0; $i < mysql_num_rows($rezUserFotos); $i++) {
            $rowUserFotos[] = mysql_fetch_assoc($rezUserFotos);
        }
    }

    // Получаем информацию о поисковом запросе пользователя, если он есть
    $rowSearchRequests = array();
    $rezSearchRequests = mysql_query("SELECT * FROM searchrequests WHERE userId = '" . $targetUserId . "'");
    if ($rezSearchRequests != FALSE) $rowSearchRequests = mysql_fetch_assoc($rezSearchRequests);
    // Преобразование сериализованных массивов к виду, удобному для обработки
    if (isset($rowSearchRequests['amountOfRooms'])) $rowSearchRequests['amountOfRooms'] = unserialize($rowSearchRequests['amountOfRooms']);
    if (isset($rowSearchRequests['district'])) $rowSearchRequests['district'] = unserialize($rowSearchRequests['district']);


    /*************************************************************************************
     * Получаем заголовок страницы
     ************************************************************************************/
    $strHeaderOfPage = $rowTargetUser['surname'] . " " . $rowTargetUser['name'] . " " . $rowTargetUser['secondName'];

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title><?php echo $strHeaderOfPage; ?></title>
    <meta name="description" content="<?php echo $strHeaderOfPage; ?>">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/colorbox.css">
    <link rel="stylesheet" href="css/main.css">

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

</head>

<body>
<div class="page_without_footer">
    <!-- Сформируем и вставим заголовок страницы -->
    <?php
    include("header.php");
    ?>

    <div class="page_main_content">
        <div class="headerOfPage">
            Характеристика пользователя
        </div>
        <div id="tabs">
            <ul>
                <li>
                    <a href="#tabs-1">Профиль</a>
                </li>
                <li>
                    <a href="#tabs-2">Условия поиска</a>
                </li>
            </ul>
            <div id="tabs-1">
                <div id="notEditingProfileParametersBlock">
                    <div class='fotosWrapper'>
                        <?php
                        // Фото
                        $linksToFotosArr = getLinksToFotos($rowUserFotos, 0, 'middle');
                        $urlFoto1 = $linksToFotosArr['urlFoto1'];
                        $hrefFoto1 = $linksToFotosArr['hrefFoto1'];
                        $numberOfFotos = $linksToFotosArr['numberOfFotos'];
                        $hiddensLinksToOtherFotos = $linksToFotosArr['hiddensLinksToOtherFotos'];

                        echo "<div class='middleFotoWrapper'>
                                <img class='middleFoto gallery' src='$urlFoto1' href='$hrefFoto1'>
                            </div>
                            <div class='numberOfFotos'>$numberOfFotos</div>
                            $hiddensLinksToOtherFotos";
                        ?>
                    </div>
                    <div class="profileInformation">
                        <ul class="listDescription">
                            <li>
                                <span class="FIO"><?php echo $strHeaderOfPage; ?></span>
                            </li>
                            <li>
                                <br>
                            </li>
                            <li>
                                <span class="headOfString">Образование:</span> <?php
                                if ($rowTargetUser['currentStatusEducation'] == "0") {
                                    echo "";
                                }
                                if ($rowTargetUser['currentStatusEducation'] == "нет") {
                                    echo "нет";
                                }
                                if ($rowTargetUser['currentStatusEducation'] == "сейчас учусь") {
                                    if (isset($rowTargetUser['almamater'])) echo $rowTargetUser['almamater'] . ", ";
                                    if (isset($rowTargetUser['speciality'])) echo $rowTargetUser['speciality'] . ", ";
                                    if (isset($rowTargetUser['ochnoZaochno'])) echo $rowTargetUser['ochnoZaochno'] . ", ";
                                    if (isset($rowTargetUser['kurs'])) echo "курс: " . $rowTargetUser['kurs'];
                                }
                                if ($rowTargetUser['currentStatusEducation'] == "закончил") {
                                    if (isset($rowTargetUser['almamater'])) echo $rowTargetUser['almamater'] . ", ";
                                    if (isset($rowTargetUser['speciality'])) echo $rowTargetUser['speciality'] . ", ";
                                    if (isset($rowTargetUser['ochnoZaochno'])) echo $rowTargetUser['ochnoZaochno'] . ", ";
                                    if (isset($rowTargetUser['yearOfEnd'])) echo "<span style='white-space: nowrap;'>закончил в " . $rowTargetUser['yearOfEnd'] . " году</span>";
                                }
                                ?>
                            </li>
                            <li>
                                <span class="headOfString">Работа:</span> <?php
                                if ($rowTargetUser['statusWork'] == "не работаю") {
                                    echo "не работаю";
                                } else {
                                    if (isset($rowTargetUser['placeOfWork']) && $rowTargetUser['placeOfWork'] != "") {
                                        echo $rowTargetUser['placeOfWork'] . ", ";
                                    }
                                    if (isset($rowTargetUser['workPosition'])) {
                                        echo $rowTargetUser['workPosition'];
                                    }
                                }
                                ?>
                            </li>
                            <li>
                                <span class="headOfString">Внешность:</span> <?php
                                if (isset($rowTargetUser['nationality']) && $rowTargetUser['nationality'] != "0") echo "<span style='white-space: nowrap;'>" . $rowTargetUser['nationality'] . "</span>";
                                ?>
                            </li>
                            <li>
                                <span class="headOfString">Пол:</span> <?php
                                if (isset($rowTargetUser['sex'])) echo $rowTargetUser['sex'];
                                ?>
                            </li>
                            <li>
                                <span class="headOfString">День рождения:</span> <?php
                                if (isset($rowTargetUser['birthday'])) echo $rowTargetUser['birthday'];
                                ?>
                            </li>
                            <li>
                                <span class="headOfString">Возраст:</span> <?php
                                $date = substr($rowTargetUser['birthday'], 7, 2);
                                $month = substr($rowTargetUser['birthday'], 5, 2);
                                $year = substr($rowTargetUser['birthday'], 0, 4);
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
                                if (isset($rowTargetUser['email'])) echo $rowTargetUser['email'];
                                ?>
                            </li>
                            <li>
                                <span class="headOfString">Телефон:</span> <?php
                                if (isset($rowTargetUser['telephon'])) echo $rowTargetUser['telephon'];
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
                                if (isset($rowTargetUser['cityOfBorn'])) echo $rowTargetUser['cityOfBorn'];
                                ?>
                            </li>
                            <li>
                                <span class="headOfString">Регион:</span> <?php
                                if (isset($rowTargetUser['regionOfBorn'])) echo $rowTargetUser['regionOfBorn'];
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
                                if (isset($rowTargetUser['shortlyAboutMe'])) echo $rowTargetUser['shortlyAboutMe'];
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
                                    if (isset($rowTargetUser['vkontakte'])) echo "<li><a href='" . $rowTargetUser['vkontakte'] . "'>" . $rowTargetUser['vkontakte'] . "</a></li>";
                                    ?>
                                    <?php
                                    if (isset($rowTargetUser['odnoklassniki'])) echo "<li><a href='" . $rowTargetUser['odnoklassniki'] . "'>" . $rowTargetUser['odnoklassniki'] . "</a></li>";
                                    ?>
                                    <?php
                                    if (isset($rowTargetUser['facebook'])) echo "<li><a href='" . $rowTargetUser['facebook'] . "'>" . $rowTargetUser['facebook'] . "</a></li>";
                                    ?>
                                    <?php
                                    if (isset($rowTargetUser['twitter'])) echo "<li><a href='" . $rowTargetUser['twitter'] . "'>" . $rowTargetUser['twitter'] . "</a></li>";
                                    ?>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="clearBoth"></div>
            </div>
            <!-- /end.tabs-1 -->
            <div id="tabs-2">
                <?php if ($rowSearchRequests == FALSE || count($rowSearchRequests) == 0): ?>
                <div class="shadowText">
                    Пользователь не ищет недвижимость в данный момент
                </div>
                <?php endif;?>
                <?php if ($rowSearchRequests != FALSE && is_array($rowSearchRequests) && count($rowSearchRequests) != 0): ?>
                <div class="shadowText">
                    Какого рода недвижимость ищет данный пользователь
                </div>
                <div id="notEditingSearchParametersBlock" class="objectDescription">
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
                                                    if (isset($rowSearchRequests['typeOfObject']) && $rowSearchRequests['typeOfObject'] != "0") echo $rowSearchRequests['typeOfObject']; else echo "любой";
                                                    ?>
                                                </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Количество комнат:</td>
                                    <td class="objectDescriptionBody"><span><?php
                                        if (isset($rowSearchRequests['amountOfRooms']) && count($rowSearchRequests['amountOfRooms']) != "0") for ($i = 0; $i < count($rowSearchRequests['amountOfRooms']); $i++) {
                                            echo $rowSearchRequests['amountOfRooms'][$i];
                                            if ($i < count($rowSearchRequests['amountOfRooms']) - 1) echo ", ";
                                        } else echo "любое";
                                        ?></span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Комнаты смежные:</td>
                                    <td class="objectDescriptionBody"><span><?php
                                        if (isset($rowSearchRequests['adjacentRooms']) && $rowSearchRequests['adjacentRooms'] != "0") echo $rowSearchRequests['adjacentRooms']; else echo "любые";
                                        ?></span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Этаж:</td>
                                    <td class="objectDescriptionBody"><span><?php
                                        if (isset($rowSearchRequests['floor']) && $rowSearchRequests['floor'] != "0") echo $rowSearchRequests['floor']; else echo "любой";
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
                                        if (isset($rowSearchRequests['minCost']) && $rowSearchRequests['minCost'] != "0") echo "<span>" . $rowSearchRequests['minCost'] . "</span> руб."; else echo "любая";
                                        ?></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
                                    <td class="objectDescriptionBody"><?php
                                        if (isset($rowSearchRequests['maxCost']) && $rowSearchRequests['maxCost'] != "0") echo "<span>" . $rowSearchRequests['maxCost'] . "</span> руб."; else echo "любая";
                                        ?></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Залог до:</td>
                                    <td class="objectDescriptionBody"><?php
                                        if (isset($rowSearchRequests['pledge']) && $rowSearchRequests['pledge'] != "0") echo "<span>" . $rowSearchRequests['pledge'] . "</span> руб."; else echo "любой";
                                        ?></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Максимальная предоплата:</td>
                                    <td class="objectDescriptionBody"><?php
                                        if (isset($rowSearchRequests['prepayment']) && $rowSearchRequests['prepayment'] != "0") echo "<span>" . $rowSearchRequests['prepayment'] . "</span>"; else echo "любая";
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
                                if (isset($rowSearchRequests['district']) && count($rowSearchRequests['district']) != 0) { // Если район указан пользователем
                                    echo "<tr><td>";
                                    for ($i = 0; $i < count($rowSearchRequests['district']); $i++) { // Выводим названия всех районов, в которых ищет недвижимость пользователь
                                        echo $rowSearchRequests['district'][$i];
                                        if ($i < count($rowSearchRequests['district']) - 1) echo ", ";
                                    }
                                    echo  "</td></tr>";
                                } else {
                                    echo "<tr><td>" . "любой" . "</td></tr>";
                                }
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
                                        if (isset($rowSearchRequests['withWho']) && $rowSearchRequests['withWho'] != "0") echo $rowSearchRequests['withWho']; else echo "не указано";
                                        ?></span></td>
                                </tr>
                                <?php
                                if (isset($rowSearchRequests['withWho']) && $rowSearchRequests['withWho'] != "самостоятельно" && $rowSearchRequests['withWho'] != "0") {
                                    echo "<tr><td class='objectDescriptionItemLabel'>Информация о сожителях:</td><td class='objectDescriptionBody''><span>";
                                    if (isset($rowSearchRequests['linksToFriends'])) echo $rowSearchRequests['linksToFriends'];
                                    echo "</span></td></tr>";
                                }
                                ?>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Дети:</td>
                                    <td class="objectDescriptionBody"><span><?php
                                        if (isset($rowSearchRequests['children']) && $rowSearchRequests['children'] != "0") echo $rowSearchRequests['children']; else echo "не указано";
                                        ?></span></td>
                                </tr>
                                <?php
                                if (isset($rowSearchRequests['children']) && $rowSearchRequests['children'] != "без детей" && $rowSearchRequests['children'] != "0") {
                                    echo "<tr><td class='objectDescriptionItemLabel'>Количество детей и их возраст:</td><td class='objectDescriptionBody''><span>";
                                    if (isset($rowSearchRequests['howManyChildren'])) echo $rowSearchRequests['howManyChildren'];
                                    echo "</span></td></tr>";
                                }
                                ?>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Животные:</td>
                                    <td class="objectDescriptionBody"><span><?php
                                        if (isset($rowSearchRequests['animals']) && $rowSearchRequests['animals'] != "0") echo $rowSearchRequests['animals']; else echo "не указано";
                                        ?></span></td>
                                </tr>
                                <?php
                                if (isset($rowSearchRequests['animals']) && $rowSearchRequests['animals'] != "без животных" && $rowSearchRequests['animals'] != "0") {
                                    echo "<tr><td class='objectDescriptionItemLabel'>Количество животных и их вид:</td><td class='objectDescriptionBody''><span>";
                                    if (isset($rowSearchRequests['howManyAnimals'])) echo $rowSearchRequests['howManyAnimals'];
                                    echo "</span></td></tr>";
                                }
                                ?>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Срок аренды:</td>
                                    <td class="objectDescriptionBody"><span><?php
                                        if (isset($rowSearchRequests['termOfLease']) && $rowSearchRequests['termOfLease'] != "0") echo $rowSearchRequests['termOfLease']; else echo "не указан";
                                        ?></span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
                                    <td class="objectDescriptionBody"><span><?php
                                        if (isset($rowSearchRequests['additionalDescriptionOfSearch'])) echo $rowSearchRequests['additionalDescriptionOfSearch'];
                                        ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <?php endif;?>
            </div>
            <!-- /end.tabs-2 -->
        </div>
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
