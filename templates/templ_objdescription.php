<?php
    // Инициализируем используемые в шаблоне переменные
    $userCharacteristic = $dataArr['userCharacteristic']; // Но для данной страницы данный массив содержит только имя, отчество, фамилию, телефон пользователя
    $propertyCharacteristic = $dataArr['propertyCharacteristic'];
    $propertyFotoInformation = $dataArr['propertyFotoInformation'];
    $isLoggedIn = $dataArr['isLoggedIn']; // Используется в templ_header.php
    $favoritesPropertysId = $dataArr['favoritesPropertysId'];
    $strHeaderOfPage = $dataArr['strHeaderOfPage'];
    $signUpToViewData = $dataArr['signUpToViewData'];
    $statusOfSaveParamsToDB = $dataArr['statusOfSaveParamsToDB'];

    /**************************************
     * Алгоритм выбора HTML оформления статуса Запроса на просмотр и модального окна для запроса на просмотр
     *
     * Пользователь не авторизован {
     *      Кнопка Записаться на просмотр + модальное окно для неавторизованного пользователя
     * }
     * Пользователь авторизован {
     *      Пользователь не является арендатором {
     *          Кнопка Записаться на просмотр + модальное окно для пользователей не арендаторов
     *      }
     *      Пользователь является арендатором {
     *          Для этого пользователя и этого объекта недвижимости еще не создано Запроса на просмотр {
     *              Кнопка Записаться на просмотр + модальное окно с формой Записи на просмотр
     *          }
     *          Для этого пользователя и этого объекта недвижимости уже был создан Запрос на просмотр {
     *              Статус Запроса = confirmed {
     *                  Вместо кнопки Запроса инфа о времени просмотра + кнопка Изменить
     *              }
     *              Статус Запроса = failure {
     *                  Вместо кнопки Запроса инфа об отказе собственника
     *              }
     *              Статус Запроса = inProgress {
     *                  Вместо кнопки Запроса инфа о том, что Заявка обрабатывается
     *              }
     *          }
     *      }
     *}
     **************************************/
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
    <style>
        .setOfInstructions {
            text-align: left;
            line-height: 2em;
            margin-top: 10px;
            margin-bottom: 20px;
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
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {

            $("#signUpToViewDialog").dialog({
                autoOpen:false,
                modal:true,
                width:600,
                dialogClass:"edited"
            });

            $(".signUpToViewButton").click(function () {
                $("#signUpToViewDialog").dialog("open");
            });

            $("#signUpToViewDialogCancel").on('click', function () {
                $("#signUpToViewDialog").dialog("close");
            });

        });
    </script>

</head>

<body>
<div class="page_without_footer">
    <!-- Сформируем и вставим заголовок страницы -->
    <?php
    include("templates/templ_header.php");
    ?>

    <div class="page_main_content">

        <div class="headerOfPage">
            <?php echo $strHeaderOfPage; ?>
        </div>

        <div id="tabs">
            <ul>
                <li>
                    <a href="#tabs-1">Описание</a>
                </li>
                <li>
                    <a href="#tabs-2">Местоположение</a>
                </li>
            </ul>
            <div id="tabs-1">

                <div>
                    <?php
                    // Формируем и размещаем на странице блок для фотографий объекта недвижимости
                    echo $this->getHTMLfotosWrapper("middle", TRUE, FALSE, $propertyFotoInformation['uploadedFoto']);
                    ?>

                    <ul class="setOfInstructions">

                        <?php
                        /* Оформляем пункт Меню о Заявке на просмотр */
                            // Если при передаче Запроса на показ возникли ошибки
                            if ($statusOfSaveParamsToDB === FALSE) {
                                echo "  <li>
                                            <span>Ошибка при отправке запроса<br>Попробуйте немного позже</span>
                                        </li>
                                     ";

                            } else { // Если ошибок не было

                                if ($isLoggedIn === FALSE || $userCharacteristic['typeTenant'] === FALSE || $signUpToViewData['ownerStatus'] == "") {
                                    echo "  <li>
                                                <button class='mainButton signUpToViewButton'>Записаться на просмотр</button>
                                            </li>
                                         ";
                                }

                                if ($isLoggedIn === TRUE && $userCharacteristic['typeTenant'] === TRUE && $signUpToViewData['ownerStatus'] == "confirmed") {
                                    echo "  <li>
                                                <span>Просмотр {$signUpToViewData['finalDate']} в {$signUpToViewData['finalTimeHours']}:{$signUpToViewData['finalTimeMinutes']}</span>
                                            </li>
                                          ";
                                }

                                if ($isLoggedIn === TRUE && $userCharacteristic['typeTenant'] === TRUE && $signUpToViewData['ownerStatus'] == "failure") {
                                    echo " <li>
                                               <span title='К сожалению, собственник отказался от показа'>Отказ собственника</span>
                                           </li>
                                         ";
                                }

                                if ($isLoggedIn === TRUE && $userCharacteristic['typeTenant'] === TRUE && $signUpToViewData['ownerStatus'] == "inProgress") {
                                    echo " <li>
                                             <span title='Оператор с Вами свяжется в ближайшее время'>Заявка отправлена</span>
                                           </li>
                                         ";
                                }
                            }
                        ?>

                        <li>
                            <?php
                            echo $this->getHTMLforFavorites($propertyCharacteristic["id"], $favoritesPropertysId, "stringWithIcon");
                            ?>
                        </li>
                        <!-- TODO: добавить функциональность!
                        <li>
                            <a href="#"> отправить по e-mail</a>
                        </li>
                        <li>
                            <a href="#"> похожие объявления</a>
                        </li>-->
                    </ul>

                    <div class="clearBoth"></div>

                </div>


                <?php
                    // Подключаем нужное модальное окно для Запроса на просмотр

                    if ($isLoggedIn === FALSE) include "templates/templ_signUpToViewDialog_ForLoggedOut.php";
                    if ($isLoggedIn === TRUE && $userCharacteristic['typeTenant'] === TRUE) include "templates/templ_signUpToViewDialog_ForTenant.php";

                ?>

                <div class="objectDescription">

                    <div class="notEdited left">
                        <div class='legend'>
                            Комнаты и помещения
                        </div>
                        <table>
                            <tbody>
                                <?php
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Кол-во комнат:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['amountOfRooms'] . "</span></td></tr>";
                                if ($propertyCharacteristic['amountOfRooms'] != "0" && $propertyCharacteristic['amountOfRooms'] != "1") echo "<tr><td class='objectDescriptionItemLabel'>Комнаты смежные:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['adjacentRooms'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['adjacentRooms'] != "0" && $propertyCharacteristic['adjacentRooms'] != "нет" && $propertyCharacteristic['amountOfRooms'] != "0" && $propertyCharacteristic['amountOfRooms'] != "1" && $propertyCharacteristic['amountOfRooms'] != "2") echo "<tr><td class='objectDescriptionItemLabel'>Кол-во смежных комнат:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['amountOfAdjacentRooms'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Санузел:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['typeOfBathrooms'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Балкон/лоджия:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['typeOfBalcony'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfBalcony'] != "0" && $propertyCharacteristic['typeOfBalcony'] != "нет" && $propertyCharacteristic['typeOfBalcony'] != "эркер" && $propertyCharacteristic['typeOfBalcony'] != "2 эркера и более") echo "<tr><td class='objectDescriptionItemLabel'>Остекление:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['balconyGlazed'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "квартира" && $propertyCharacteristic['typeOfObject'] != "дом" && $propertyCharacteristic['typeOfObject'] != "таунхаус" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Площадь комнаты:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['roomSpace'] . " м²</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "комната") echo "<tr><td class='objectDescriptionItemLabel'>Площадь общая:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['totalArea'] . " м²</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Площадь жилая:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['livingSpace'] . " м²</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Площадь кухни:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['kitchenSpace'] . " м²</span></td></tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="notEdited right">
                        <div class='legend'>
                            Стоимость, условия оплаты
                        </div>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Плата за аренду:</td>
                                    <td class="objectDescriptionBody"><?php echo "<span>" . $propertyCharacteristic['costOfRenting'] . "</span>" . " " . $propertyCharacteristic['currency'] . " в месяц" ?></td>
                                </tr>
                                <tr title="Выплачивается собственнику при заключении договора аренды">
                                    <td class="objectDescriptionItemLabel">Единовременная комиссия:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['compensationMoney'] . " " . $propertyCharacteristic['currency'] . " (" . $propertyCharacteristic['compensationPercent'] . "%)" ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Коммунальные услуги:
                                    </td>
                                    <td class="objectDescriptionBody"><?php if ($propertyCharacteristic['utilities'] == "да") echo "<span>оплачиваются
                            дополнительно,<br>от " . $propertyCharacteristic['costInSummer'] . " до " . $propertyCharacteristic['costInWinter'] . " " . $propertyCharacteristic['currency'] . "</span>"; else echo "<span>включены в стоимость</span>"; ?></td>
                                </tr>
                                <?php if ($propertyCharacteristic['electricPower'] == "да") echo "<tr><td class='objectDescriptionItemLabel'>Электроэнергия:</td><td class='objectDescriptionBody'><span>оплачивается дополнительно</span></td></tr>"; ?>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Залог:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php if ($propertyCharacteristic['bail'] == "есть") echo $propertyCharacteristic['bailCost'] . " " . $propertyCharacteristic['currency']; else echo "нет"; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Предоплата:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['prepayment']; ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж"): ?>
                    <div class="notEdited left">
                        <div class='legend'>
                            Этаж и подъезд
                        </div>
                        <table>
                            <tbody>
                                <?php
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дом" && $propertyCharacteristic['typeOfObject'] != "таунхаус" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Этаж:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['floor'] . " из " . $propertyCharacteristic['totalAmountFloor'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "квартира" && $propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Этажность дома:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['numberOfFloor'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дом" && $propertyCharacteristic['typeOfObject'] != "таунхаус" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Консьерж:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['concierge'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Домофон:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['intercom'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Парковка во дворе:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['parking'] . "</span></td></tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж"): ?>
                    <div class="notEdited right">
                        <div class='legend'>
                            Текущее состояние
                        </div>
                        <table>
                            <tbody>
                                <?php
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Ремонт:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['repair'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Отделка:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['furnish'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Окна:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['windows'] . "</span></td></tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <div class="notEdited left">
                        <div class='legend'>
                            Тип и сроки
                        </div>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Тип объекта:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['typeOfObject']; ?></span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">С какого числа можно въезжать:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['dateOfEntry']; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Срок аренды:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['termOfLease']; ?></span></td>
                                </tr>
                                <?php
                                if ($propertyCharacteristic['termOfLease'] != "0" && $propertyCharacteristic['termOfLease'] != "длительный срок") echo "<tr><td class='objectDescriptionItemLabel'>Крайний срок выезда:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['dateOfCheckOut'] . "</span></td></tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж"): ?>
                    <div class="notEdited right">
                        <div class='legend'>
                            Связь
                        </div>
                        <table>
                            <tbody>
                                <?php
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Интернет:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['internet'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Телефон:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['telephoneLine'] . "</span></td></tr>";
                                if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Кабельное ТВ:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['cableTV'] . "</span></td></tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж"): ?>
                    <div class="notEdited left">
                        <div class='legend'>
                            Мебель и бытовая техника
                        </div>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Мебель в жилой зоне:</td>
                                    <td class="objectDescriptionBody"><span>
                                                    <?php
                                        $furniture = array(); // Инициализируем переменную для хранения списка мебели
                                        // Скидываем в массив $furniture всю мебель, которую собственник отметил галочками
                                        foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                                            $furniture[] = $value;
                                        }

                                        // Скидываем в массив $furniture всю мебель, которую собственник добавил вручную
                                        $furnitureInLivingAreaExtraArr = explode(', ', $propertyCharacteristic['furnitureInLivingAreaExtra']);
                                        foreach ($furnitureInLivingAreaExtraArr as $value) {
                                            if ($value != "") $furniture[] = $value; // Дополнительная проверка на пустоту нужна, так как пустая строчка воспринимается как один из членов массива
                                        }

                                        for ($i = 0; $i < count($furniture); $i++) {
                                            echo $furniture[$i];
                                            if ($i < count($furniture) - 1) echo ",<br>"; // Если элемент в массиве не последний - добавляем запятую
                                        }

                                        // Если мебель не указана совсем - пишем слово "нет"
                                        if (count($furniture) == 0) echo "нет";
                                        ?>
                                                </span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Мебель на кухне:</td>
                                    <td class="objectDescriptionBody"><span>
                                                    <?php
                                        $furniture = array(); // Инициализируем переменную для хранения списка мебели
                                        // Скидываем в массив $furniture всю мебель, которую собственник отметил галочками
                                        foreach ($propertyCharacteristic['furnitureInKitchen'] as $value) {
                                            $furniture[] = $value;
                                        }

                                        // Скидываем в массив $furniture всю мебель, которую собственник добавил вручную
                                        $furnitureInKitchenExtraArr = explode(', ', $propertyCharacteristic['furnitureInKitchenExtra']);
                                        foreach ($furnitureInKitchenExtraArr as $value) {
                                            if ($value != "") $furniture[] = $value; // Дополнительная проверка на пустоту нужна, так как пустая строчка воспринимается как один из членов массива
                                        }

                                        for ($i = 0; $i < count($furniture); $i++) {
                                            echo $furniture[$i];
                                            if ($i < count($furniture) - 1) echo ",<br>"; // Если элемент в массиве не последний - добавляем запятую
                                        }

                                        // Если мебель не указана совсем - пишем слово "нет"
                                        if (count($furniture) == 0) echo "нет";
                                        ?>
                                                </span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Бытовая техника:</td>
                                    <td class="objectDescriptionBody"><span>
                                                    <?php
                                        $furniture = array(); // Инициализируем переменную для хранения списка бытовой техники
                                        // Скидываем в массив $furniture всю бытовую технику, которую собственник отметил галочками
                                        foreach ($propertyCharacteristic['appliances'] as $value) {
                                            $furniture[] = $value;
                                        }

                                        // Скидываем в массив $furniture всю бытовую технику, которую собственник добавил вручную
                                        $appliancesExtraArr = explode(', ', $propertyCharacteristic['appliancesExtra']);
                                        foreach ($appliancesExtraArr as $value) {
                                            if ($value != "") $furniture[] = $value; // Дополнительная проверка на пустоту нужна, так как пустая строчка воспринимается как один из членов массива
                                        }

                                        for ($i = 0; $i < count($furniture); $i++) {
                                            echo $furniture[$i];
                                            if ($i < count($furniture) - 1) echo ",<br>"; // Если элемент в массиве не последний - добавляем запятую
                                        }

                                        // Если бытовая техника не указана совсем - пишем слово "нет"
                                        if (count($furniture) == 0) echo "нет";
                                        ?>
                                                </span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж"): ?>
                    <div class="notEdited right">
                        <div class='legend'>
                            Требования к арендатору
                        </div>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Пол:</td>
                                    <td class="objectDescriptionBody"><span>
                        <?php
                                        // Если собственник указал только один пол в качестве предпочтительного, то выводим его на страницу
                                        if (count($propertyCharacteristic['sexOfTenant']) == 1) echo $propertyCharacteristic['sexOfTenant'][0];

                                        // Если указаны оба пола - пишем фразу "не имеет значения"
                                        if (count($propertyCharacteristic['sexOfTenant']) == 2) echo "не имеет значения";
                                        ?>
												</span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Отношения между арендаторами:</td>
                                    <td class="objectDescriptionBody"><span>
                        <?php
                                        for ($i = 0; $i < count($propertyCharacteristic['relations']); $i++) {
                                            echo $propertyCharacteristic['relations'][$i];
                                            if ($i < count($propertyCharacteristic['relations']) - 1) echo ",<br>"; // Если элемент в массиве не последний - добавляем запятую
                                        }
                                        ?>
                                                </span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Дети:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['children']; ?></span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Животные:</td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['animals']; ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <div class="notEdited left">
                        <div class='legend'>
                            Особые условия
                        </div>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Где проживает собственник:
                                    </td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['checking']; ?></span></td>
                                </tr>
                                <tr>
                                    <td class="objectDescriptionItemLabel">Ответственность за состояние и ремонт:
                                    </td>
                                    <td class="objectDescriptionBody">
                                        <span><?php echo $propertyCharacteristic['responsibility']; ?></span></td>
                                </tr>
                                <?php
                                if ($propertyCharacteristic['comment'] != "") echo "<tr><td class='objectDescriptionItemLabel'>Дополнительный комментарий:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['comment'] . "</span></td></tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="clearBoth"></div>
                </div>
            </div>
            <div id="tabs-2">
                <!-- Карта Яндекса -->
                <div id="mapForAdvertView" style="width: 50%; min-width: 300px; height: 400px; float: left;"></div>

                <ul class="setOfInstructions">
                    <li>
                        <button class="mainButton signUpToViewButton">Записаться на просмотр</button>
                    </li>
                    <li>
                        <?php
                        echo $this->getHTMLforFavorites($propertyCharacteristic["id"], $favoritesPropertysId, "stringWithIcon");
                        ?>
                    </li>
                </ul>

                <div class="notEdited right">
                    <input type="hidden" name="coordX"
                           id="coordX" <?php echo "value='" . $propertyCharacteristic['coordX'] . "'";?>>
                    <input type="hidden" name="coordY"
                           id="coordY" <?php echo "value='" . $propertyCharacteristic['coordY'] . "'";?>>
                    <table>
                        <tbody>
                            <tr>
                                <td class="objectDescriptionItemLabel">Город:</td>
                                <td class="objectDescriptionBody">
                                    <span><?php echo $propertyCharacteristic['city'];?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="objectDescriptionItemLabel">Район:</td>
                                <td class="objectDescriptionBody"><span>
                                                <?php
                                    if (isset($propertyCharacteristic['district'])) echo $propertyCharacteristic['district'];
                                    ?>
                                            </span></td>
                            </tr>
                            <tr>
                                <td class="objectDescriptionItemLabel">Адрес:</td>
                                <td class="objectDescriptionBody">
                                    <span><?php echo $propertyCharacteristic['address'];?></span>
                                </td>
                            </tr>
                            <?php
                            if ($propertyCharacteristic['subwayStation'] != "0" && $propertyCharacteristic['subwayStation'] != "нет") echo "<tr><td class='objectDescriptionItemLabel'>Станция метро рядом:</td><td class='objectDescriptionBody'><span>" . $propertyCharacteristic['subwayStation'] . ",<br>" . $propertyCharacteristic['distanceToMetroStation'] . " мин. ходьбы" . "</span></td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="clearBoth"></div>
            </div>
        </div>

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
    2012 г. Вопросы и пожелания по работе портала можно передавать по телефону: 8-922-143-16-15, e-mail:
    support@svobodno.org
</div>
<!-- /end.footer -->

<!-- scripts -->
<!-- Загружаем библиотеку для работы с картой от Яндекса -->
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>
<script>
    /* Как только будет загружен API и готов DOM, выполняем инициализацию */
    ymaps.ready(init);

    function init() {
        // Создание экземпляра карты и его привязка к контейнеру с
        // заданным id ("mapForAdvertView")
        // Получаем координаты объекта недвижимости
        var coordX = $("#coordX").val();
        var coordY = $("#coordY").val();

        // Непосредственно инициализируем карту
        if (coordX != "" && coordY != "") {
            var map = new ymaps.Map('mapForAdvertView', {
                // При инициализации карты, обязательно нужно указать
                // ее центр и коэффициент масштабирования
                center:[$("#coordX").val(), $("#coordY").val()],
                zoom:16,
                // Включим поведения по умолчанию (default) и,
                // дополнительно, масштабирование колесом мыши.
                // дополнительно включаем измеритель расстояний по клику левой кнопки мыши
                behaviors:['default', 'scrollZoom', 'ruler']
            });

            // Добавляем на карту метку объекта недвижимости
            currentPlacemark = new ymaps.Placemark([coordX, coordY]);
            map.geoObjects.add(currentPlacemark);

        } else {
            var map = new ymaps.Map('mapForAdvertView', {
                // При инициализации карты, обязательно нужно указать
                // ее центр и коэффициент масштабирования
                center:[56.829748, 60.617435], // Екатеринбург
                zoom:11,
                // Включим поведения по умолчанию (default) и,
                // дополнительно, масштабирование колесом мыши.
                // дополнительно включаем измеритель расстояний по клику левой кнопки мыши
                behaviors:['default', 'scrollZoom', 'ruler']
            });
        }

        /***** Добавляем элементы управления на карту *****/
            // Для добавления элемента управления на карту используется поле controls, ссылающееся на
            // коллекцию элементов управления картой. Добавление элемента в коллекцию производится с помощью метода add().
            // В метод add можно передать строковый идентификатор элемента управления и его параметры.
            // Список типов карты
        map.controls.add('typeSelector');
        // Кнопка изменения масштаба - компактный вариант
        // Расположим её ниже и левее левого верхнего угла
        map.controls.add('smallZoomControl', {
            left:5,
            top:55
        });
        // Стандартный набор кнопок
        map.controls.add('mapTools');

        // При переключении вкладки карту нужно перестраивать
        $('#tabs').bind('tabsshow', reDrawMap);

        /***** Функция перестроения карты - используется при изменении размеров блока *****/
        function reDrawMap() {
            //map.setCenter([56.829748, 60.617435]);
            map.container.fitToViewport();
        }
    }
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