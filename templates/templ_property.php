<!DOCTYPE html>
<html>
<head>

    <!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-language" content="ru">
    <meta name="description"
          content="<?php echo GlobFunc::getFirstCharUpper($propertyCharacteristic['typeOfObject']) . " по адресу: " . $propertyCharacteristic['address']; ?>">
    <!-- Если у пользователя IE: использовать последний доступный стандартный режим отображения независимо от <!DOCTYPE> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- Оптимизация отображения на мобильных устройствах -->
    <!--<meta name="viewport" content="initialscale=1.0, width=device-width">-->
    <!-- end meta -->

    <title><?php echo $propertyCharacteristic['address']; ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/colorbox.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Блок с кратким описанием объявления */
        .shortlyAboutAdvert {
            margin-bottom: 20px;
        }

        .shortlyAboutAdvert .address {
            text-align: left;
        }

        .shortlyAboutAdvert .address .addressString {
            font-size: 22px;
            white-space: normal;
        }

        .shortlyAboutAdvert .costOfRenting {
            float: right;
            padding-left: 2em; /* Чтобы цена не сливалась с адресом при маленьком разрешении и длинном адресе */
            text-align: right;
        }

        .shortlyAboutAdvert .costOfRenting .costOfRentingString {
            font-size: 22px;
        }

        .shortlyAboutAdvert .secondaryOptionsBlock {
            clear: right;
            padding-top: 12px;
        }

        .shortlyAboutAdvert .secondaryOption {
            display: inline-block;
            width: 32%;
            text-align: left;
        }

            /* Если экран меньше 1052px в ширину, то второстепенные характеристики в кратком описании объявления нужно сделать в несколько строк */
        @media screen and (max-width: 1150px) {
            .shortlyAboutAdvert .secondaryOptionsBlock {
                padding-top: 6px;
            }

            .shortlyAboutAdvert .secondaryOptionsBlock .secondaryOption {
                display: block;
                width: 100%;
            }
        }

            /* Блок с командами управления объявлением */
        .setOfInstructions {
            float: left;
            text-align: left;
            margin: 0;
        }

        .setOfInstructions .instruction {
            margin: 6px 0 6px 0;
        }

        .setOfInstructions .instruction:first-child {
            margin-top: 0;
        }

        .setOfInstructions .instruction:last-child {
            margin-bottom: 0;
        }

        .furnitureList {
            margin: 0;
            padding: 0;
            list-style: square;
        }
    </style>
    <!-- end CSS -->

</head>

<body>
<div class="pageWithoutFooter">

<?php
// Сформируем и вставим заголовок страницы
require $websiteRoot . "/templates/templ_header.php";
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
<script src="js/main.js"></script>
<script>
    $(document).ready(function () {

        $("#getOwnerContactsDialog").dialog({
            autoOpen:false,
            modal:true,
            width:600,
            dialogClass:"edited",
            draggable:true
        });

        $(".getOwnerContactsButton").click(function () {
            // Узнаем - заготовлено ли диалоговое окно на случай клика
            var getOwnerContactsDialog = $("#getOwnerContactsDialog");
            if (getOwnerContactsDialog.length == 1) {
                getOwnerContactsDialog.dialog("open");
            } else {
                jQuery.post("AJAXGetPropertyData.php", {"propertyId":propertyId}, function (data) {
                    if (data.access == "successful") {
                        // Добавляем на страницу полученные с сервера данные о собственнике
                        if (data.name != "") {
                            $(".ownerContactsName").html(data.name + " " + data.secondName);
                        } else {
                            $(".ownerContactsName").html("Телефонный номер:");
                        }
                        if (data.contactTelephonNumber != "") $(".ownerContactsTelephon").html(data.contactTelephonNumber);
                        if (data.sourceOfAdvert != "") $(".ownerContactsSourceOfAdvert a.ownerContactsSourceOfAdvertHref").html("Источник объявления").attr("href", data.sourceOfAdvert);
                        // Прячем кнопку запроса контактов собственника, показываем полученные данные
                        $(".ownerContacts").css("display", "");
                        $(".getOwnerContactsButton").css("display", "none");

                        // Если пользователь не оплатил премиум-доступ, то выдадим рекламное сообщение
                        if (!isPremiumAccess) {
                            $(".ourAd").show();
                        }
                    }
                }, 'json');
            }
        });

    });

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
                type:"yandex#satellite",
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

<div class="headerOfPage">
    Характеристика недвижимости
</div>

<div class="mainContentBlock">

<!-- Карта Яндекса -->
<div id="mapForAdvertView"
     style="float: left; width: 50%; height: 400px; padding-right: 0.6em;"></div>

<!-- Основные сведения по объекту и команды управления -->
<div style="float: right; width: 50%; padding-left: 0.6em;">
    <!-- Краткая сводка по объявлению -->
    <div class='shortlyAboutAdvert'>
        <div class="costOfRenting">
            <div>
            <span class="costOfRentingString">
                <?php if ($propertyCharacteristic['costOfRenting'] != "" && $propertyCharacteristic['costOfRenting'] != "0.00") echo $propertyCharacteristic['costOfRenting']; else echo "цена договорная"; ?>
            </span>
            <span class="unimportantText">
                <?php if ($propertyCharacteristic['currency'] != "0") echo $propertyCharacteristic['currency']; ?>
            </span>
            </div>
            <div class="unimportantText">
                <?php if ($propertyCharacteristic['utilities'] == "да") echo " <span style='white-space: nowrap;' title='коммунальные платежи оплачиваются отдельно'>+ ком. усл.</span>"; elseif ($propertyCharacteristic['utilities'] == "нет") echo " <span style='white-space: nowrap;' title='коммунальные платежи включены в стоимость'> (ком. вкл.)</span>"; ?>
            </div>
        </div>
        <div class="address">
            <div class="addressString">
                <?php echo $propertyCharacteristic['address'];?>
            </div>
            <div>
                <span class="unimportantText"><?php if ($propertyCharacteristic['typeOfObject'] != "0") echo GlobFunc::getFirstCharUpper($propertyCharacteristic['typeOfObject']); ?>
                    / <?php if ($propertyCharacteristic['district'] != "0") echo $propertyCharacteristic['district']; ?></span>
            </div>
        </div>
        <div class="secondaryOptionsBlock">
            <div class="secondaryOption">
                <span class="unimportantText">
                    <?php if ($propertyCharacteristic['typeOfObject'] != "гараж") echo "Комнат:";?>
                </span>
                <?php if ($propertyCharacteristic['amountOfRooms'] != "0") echo $propertyCharacteristic['amountOfRooms'];?>
                <?php if ($propertyCharacteristic['adjacentRooms'] == "да") if ($propertyCharacteristic['amountOfAdjacentRooms'] != "0" && $propertyCharacteristic['amountOfRooms'] > 2) echo ", смежных: " . $propertyCharacteristic['amountOfAdjacentRooms']; else echo ", смежные"; ?>
            </div>
            <div class="secondaryOption">
                <span class="unimportantText">
                    Площадь:
                </span>
                <span style="white-space: nowrap;">
                <?php
                    if ($propertyCharacteristic['typeOfObject'] != "квартира" && $propertyCharacteristic['typeOfObject'] != "дом" && $propertyCharacteristic['typeOfObject'] != "таунхаус" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['roomSpace'] != "") echo $propertyCharacteristic['roomSpace'];
                    if ($propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['totalArea'] != "") echo $propertyCharacteristic['totalArea'];
                    if ($propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['livingSpace'] != "") echo "/" . $propertyCharacteristic['livingSpace'];
                    if ($propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['kitchenSpace'] != "") echo "/" . $propertyCharacteristic['kitchenSpace'];
                    if ($propertyCharacteristic['roomSpace'] != "" || $propertyCharacteristic['totalArea'] != "" || $propertyCharacteristic['livingSpace'] != "" || $propertyCharacteristic['kitchenSpace'] != "") echo " м²";
                    ?>
                </span>
            </div>
            <div class="secondaryOption">
                <span class="unimportantText">
                    <?php
                    if ($propertyCharacteristic['typeOfObject'] == "квартира" || $propertyCharacteristic['typeOfObject'] == "комната") echo "Этаж:";
                    if ($propertyCharacteristic['typeOfObject'] == "дом" || $propertyCharacteristic['typeOfObject'] == "таунхаус" || $propertyCharacteristic['typeOfObject'] == "дача") echo "Этажей:";
                    ?>
                </span>
                <?php
                if ($propertyCharacteristic['floor'] != "" || $propertyCharacteristic['totalAmountFloor'] != "") $propertyCharacteristic['floor'] . " из " . $propertyCharacteristic['totalAmountFloor'];
                if ($propertyCharacteristic['numberOfFloor'] != "") echo $propertyCharacteristic['numberOfFloor'];
                ?>
            </div>
        </div>
    </div>

    <?php if ($propertyCharacteristic['comment'] != ""): ?>
    <div style="margin-bottom: 20px;">
        <?php echo $propertyCharacteristic['comment']; ?>
    </div>
    <?php endif; ?>

    <ul class="setOfInstructions">
        <li class="instruction">
            <button class='mainButton getOwnerContactsButton'>
                Контакты собственника
            </button>
            <div class="ownerContacts" style="display: none; margin-bottom: 20px;">
                <div>
                    <span class="ownerContactsName"></span>
                    <span class="ownerContactsTelephon"></span>
                </div>
                <div class="ownerContactsSourceOfAdvert">
                    <a class="ownerContactsSourceOfAdvertHref" href=""></a>
                </div>
                <div class="ourAd" style="display: none; margin-top: 6px;">
                    <button class="mainButton" style="float: right;">
                        к оплате
                    </button>
                    Приобретите <span style="font-weight: bold;">Премиум-доступ</span> <span style="white-space: nowrap;">(от 50 руб.), чтобы:</span>
                    <ul class="benefits">
                        <li>
                            Просматривать исходные объявления с ФОТОГРАФИЯМИ недвижимости
                        </li>
                        <li>
                            Получать e-mail оповещения о появлении новых подходящих Вам объектов
                        </li>
                        <li>
                            Помочь ресурсу и в следующий раз воспользоваться таким же удобным и дешевым сервисом
                        </li>
                    </ul>
                </div>
            </div>
        </li>
        <li class="instruction">
            <?php
            echo View::getHTMLforFavorites($propertyCharacteristic["id"], $favoritePropertiesId, "stringWithIcon");
            ?>
        </li>
        <li class="instruction">
            <a href="#"> не актуально</a>
        </li>
        <li class="instruction">
            <a href="#"> ошибка в описании</a>
        </li>
        <li class="instruction">
            <a href="#"> это агент</a>
        </li>
        <!-- TODO: добавить функциональность!
        <li class="instruction">
            <a href="#"> отправить по e-mail</a>
        </li>
        <li class="instruction">
            <a href="#"> похожие объявления</a>
        </li>-->
    </ul>

</div>

<!-- Подробные сведения по объекту -->
<div class="objectDescription">

<div class="notEdited left">
    <div class='legend'>
        Комнаты и помещения
    </div>
    <table>
        <tbody>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['amountOfRooms'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Кол-во комнат:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['amountOfRooms'];?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['amountOfRooms'] != "0" && $propertyCharacteristic['amountOfRooms'] != "1" && $propertyCharacteristic['adjacentRooms'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Комнаты смежные:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['adjacentRooms'];?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['adjacentRooms'] != "0" && $propertyCharacteristic['adjacentRooms'] != "нет" && $propertyCharacteristic['amountOfRooms'] != "0" && $propertyCharacteristic['amountOfRooms'] != "1" && $propertyCharacteristic['amountOfRooms'] != "2" && $propertyCharacteristic['amountOfAdjacentRooms'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Кол-во смежных комнат:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['amountOfAdjacentRooms'];?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['typeOfBathrooms'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Санузел:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['typeOfBathrooms'];?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['typeOfBalcony'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Балкон/лоджия:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['typeOfBalcony'];?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfBalcony'] != "0" && $propertyCharacteristic['typeOfBalcony'] != "нет" && $propertyCharacteristic['typeOfBalcony'] != "эркер" && $propertyCharacteristic['typeOfBalcony'] != "2 эркера и более" && $propertyCharacteristic['balconyGlazed'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Остекление:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['balconyGlazed'];?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "квартира" && $propertyCharacteristic['typeOfObject'] != "дом" && $propertyCharacteristic['typeOfObject'] != "таунхаус" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['roomSpace'] != ""): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Площадь комнаты:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['roomSpace'];?> м²</span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['totalArea'] != ""): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Площадь общая:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['totalArea'];?> м²</span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['livingSpace'] != ""): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Площадь жилая:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['livingSpace'];?> м²</span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['kitchenSpace'] != ""): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Площадь кухни:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['kitchenSpace'];?> м²</span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['checking'] != "" && $propertyCharacteristic['checking'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Где проживает собственник:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['checking']; ?></span>
            </td>
        </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="notEdited right">
    <div class='legend'>
        Стоимость, условия оплаты
    </div>
    <table>
        <tbody>

        <?php if ($propertyCharacteristic['costOfRenting'] != "" && $propertyCharacteristic['costOfRenting'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">Стоимость:</td>
            <td class="objectDescriptionBody"><?php echo "<span>" . $propertyCharacteristic['costOfRenting'] . "</span>" . " " . $propertyCharacteristic['currency'] . " в месяц" ?></td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['compensationMoney'] != "" && $propertyCharacteristic['currency'] != "" && $propertyCharacteristic['compensationPercent'] != "" && $propertyCharacteristic['currency'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Комиссия:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['compensationMoney'] . " " . $propertyCharacteristic['currency'] . " (" . $propertyCharacteristic['compensationPercent'] . "%)" ?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['utilities'] != "" && $propertyCharacteristic['utilities'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Ком. услуги:
            </td>
            <td class="objectDescriptionBody">
                <?php if ($propertyCharacteristic['utilities'] == "да"): ?>
                <span>оплачиваются дополнительно<?php if ($propertyCharacteristic['costInSummer'] != "" && $propertyCharacteristic['costInWinter'] != "" && $propertyCharacteristic['currency'] != "" && $propertyCharacteristic['costInSummer'] != "0" && $propertyCharacteristic['costInWinter'] != "0" && $propertyCharacteristic['currency'] != "0") echo ",<br>от " . $propertyCharacteristic['costInSummer'] . " до " . $propertyCharacteristic['costInWinter'] . " " . $propertyCharacteristic['currency'];?></span>
                <?php endif; ?>
                <?php if ($propertyCharacteristic['utilities'] == "нет"): ?>
                <span>включены в стоимость</span>
                <?php endif; ?>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['electricPower'] == "да"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Электроэнергия:
            </td>
            <td class='objectDescriptionBody'>
                <span>оплачивается дополнительно</span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['bail'] != "" && $propertyCharacteristic['bail'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">Залог:</td>
            <td class="objectDescriptionBody">
               <span>
                   <?php
                   if ($propertyCharacteristic['bail'] == "есть" && $propertyCharacteristic['bailCost'] != "" && $propertyCharacteristic['currency'] != "" && $propertyCharacteristic['bailCost'] != "0" && $propertyCharacteristic['currency'] != "0") echo $propertyCharacteristic['bailCost'] . " " . $propertyCharacteristic['currency'];
                   if ($propertyCharacteristic['bail'] == "нет") echo "нет";
                   ?>
               </span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['prepayment'] != "" && $propertyCharacteristic['prepayment'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Предоплата:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['prepayment']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

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

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дом" && $propertyCharacteristic['typeOfObject'] != "таунхаус" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['floor'] != "" && $propertyCharacteristic['totalAmountFloor'] != "" && $propertyCharacteristic['floor'] != "0" && $propertyCharacteristic['totalAmountFloor'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Этаж:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['floor'] . " из " . $propertyCharacteristic['totalAmountFloor']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "квартира" && $propertyCharacteristic['typeOfObject'] != "комната" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['numberOfFloor'] != "" && $propertyCharacteristic['numberOfFloor'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Этажность дома:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['numberOfFloor']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дом" && $propertyCharacteristic['typeOfObject'] != "таунхаус" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['concierge'] != "" && $propertyCharacteristic['concierge'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Консьерж:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['concierge']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['intercom'] != "" && $propertyCharacteristic['intercom'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Домофон:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['intercom']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "дача" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['parking'] != "" && $propertyCharacteristic['parking'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Парковка во дворе:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['parking']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

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

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['repair'] != "" && $propertyCharacteristic['repair'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Ремонт:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['repair']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['furnish'] != "" && $propertyCharacteristic['furnish'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Отделка:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['furnish']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['windows'] != "" && $propertyCharacteristic['windows'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Окна:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['windows']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

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

        <?php if ($propertyCharacteristic['typeOfObject'] != "" && $propertyCharacteristic['typeOfObject'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Тип объекта:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['typeOfObject']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['dateOfEntry'] != "" && $propertyCharacteristic['dateOfEntry'] != "0000-00-00"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Дата въезда:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['dateOfEntry']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['termOfLease'] != "" && $propertyCharacteristic['termOfLease'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Срок аренды:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['termOfLease']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

        <?php if ($propertyCharacteristic['termOfLease'] != "0" && $propertyCharacteristic['termOfLease'] != "длительный срок" && $propertyCharacteristic['dateOfCheckOut'] != "" && $propertyCharacteristic['dateOfCheckOut'] != "0000-00-00"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Сдается до:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['dateOfCheckOut']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

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

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['internet'] != "" && $propertyCharacteristic['internet'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Интернет:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['internet']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['telephoneLine'] != "" && $propertyCharacteristic['telephoneLine'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Телефон:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['telephoneLine']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж" && $propertyCharacteristic['cableTV'] != "" && $propertyCharacteristic['cableTV'] != "0"): ?>
        <tr>
            <td class='objectDescriptionItemLabel'>
                Кабельное ТВ:
            </td>
            <td class='objectDescriptionBody'>
                <span><?php echo $propertyCharacteristic['cableTV']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

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

            <?php if (is_array($furnitureInLivingArea) && (count($furnitureInLivingArea) != 0 || $propertyCharacteristic['completeness'] == "1")): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                В жилой зоне:
            </td>
            <td class="objectDescriptionBody">
                <ul class="furnitureList">
                    <?php foreach ($furnitureInLivingArea as $value): ?>
                    <li>
                        <?php echo $value; ?>
                    </li>
                    <?php endforeach; ?>
                    <?php if ($propertyCharacteristic['completeness'] == "1" && count($furnitureInLivingArea) == 0): ?>
                    <li>
                        <span>нет</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </td>
        </tr>
            <?php endif; ?>

            <?php if (is_array($furnitureInKitchen) && (count($furnitureInKitchen) != 0 || $propertyCharacteristic['completeness'] == "1")): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                На кухне:
            </td>
            <td class="objectDescriptionBody">
                <ul class="furnitureList">
                    <?php foreach ($furnitureInKitchen as $value): ?>
                    <li>
                        <?php echo $value; ?>
                    </li>
                    <?php endforeach; ?>
                    <?php if ($propertyCharacteristic['completeness'] == "1" && count($furnitureInKitchen) == 0): ?>
                    <li>
                        <span>нет</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </td>
        </tr>
            <?php endif; ?>

            <?php if (is_array($appliances) && (count($appliances) != 0 || $propertyCharacteristic['completeness'] == "1")): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Быт. техника:
            </td>
            <td class="objectDescriptionBody">
                <ul class="furnitureList">
                    <?php foreach ($appliances as $value): ?>
                    <li>
                        <?php echo $value; ?>
                    </li>
                    <?php endforeach; ?>
                    <?php if ($propertyCharacteristic['completeness'] == "1" && count($appliances) == 0): ?>
                    <li>
                        <span>нет</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </td>
        </tr>
            <?php endif; ?>

        </tbody>
    </table>
</div>
    <?php endif; ?>

<?php if ($propertyCharacteristic['typeOfObject'] != "0" && $propertyCharacteristic['typeOfObject'] != "гараж"): ?>
<div class="notEdited right">
    <div class='legend'>
        Требования к арендаторам
    </div>
    <table>
        <tbody>

            <?php if (is_array($propertyCharacteristic['relations']) && (count($propertyCharacteristic['relations']) != 0 || $propertyCharacteristic['completeness'] == "1")): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Кто может проживать:
            </td>
            <td class="objectDescriptionBody">
                <ul class="furnitureList">
                    <?php foreach ($propertyCharacteristic['relations'] as $value): ?>
                    <li>
                        <?php
                        echo $value;
                        if ($value == "один человек" && count($propertyCharacteristic['sexOfTenant']) == 1) echo " (" . $propertyCharacteristic['sexOfTenant'][0] . ")";
                        ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['children'] != "" && $propertyCharacteristic['children'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Дети:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['children']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

            <?php if ($propertyCharacteristic['animals'] != "" && $propertyCharacteristic['animals'] != "0"): ?>
        <tr>
            <td class="objectDescriptionItemLabel">
                Животные:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['animals']; ?></span>
            </td>
        </tr>
            <?php endif; ?>

        </tbody>
    </table>
</div>
    <?php endif; ?>

<div class="notEdited right">
    <div class='legend'>
        Расположение
    </div>
    <input type="hidden" name="coordX"
           id="coordX" <?php echo "value='" . $propertyCharacteristic['coordX'] . "'";?>>
    <input type="hidden" name="coordY"
           id="coordY" <?php echo "value='" . $propertyCharacteristic['coordY'] . "'";?>>
    <table>
        <tbody>
        <tr>
            <td class="objectDescriptionItemLabel">
                Город:
            </td>
            <td class="objectDescriptionBody">
                <span><?php echo $propertyCharacteristic['city'];?></span>
            </td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">
                Район:
            </td>
            <td class="objectDescriptionBody">
                <span><?php if ($propertyCharacteristic['district'] != "" && $propertyCharacteristic['district'] != "0") echo $propertyCharacteristic['district'];?></span>
            </td>
        </tr>
        <tr>
            <td class="objectDescriptionItemLabel">
                Адрес:
            </td>
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
if ($isLoggedIn === FALSE) {
    // Модальное окно для незарегистрированных пользователей, которые нажимают на кнопку добавления в Избранное
    require $websiteRoot . "/templates/modalWindows/templ_addToFavotitesDialog_ForLoggedOut.php";
    // Подключаем нужное модальное окно для Запроса на получение контактов собственника
    require $websiteRoot . "/templates/modalWindows/templ_getOwnerContactsDialog_ForLoggedOut.php";
}
?>

<!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
<div class="page-buffer"></div>
</div>
<!-- /end.pageWithoutFooter -->
<div class="footer">
    2013 г. Мы будем рады ответить на Ваши вопросы, отзывы, предложения по телефону: 8-922-160-95-14, или e-mail: support@svobodno.org
</div>
<!-- /end.footer -->

<!-- scripts -->
<script>
    var typeTenant = <?php if ($userCharacteristic['typeTenant']) echo "true"; else echo "false"; // Является ли регистрируемый пользователь арендатором ?>;
    var typeOwner = <?php if ($userCharacteristic['typeOwner']) echo "true"; else echo "false"; // Является ли регистрируемый пользователь собственником ?>;
    var isLoggedIn = <?php if ($isLoggedIn) echo "true"; else echo "false"; // Авторизованный ли пользователь к нам пришел ?>;
    var isPremiumAccess = <?php if ($userCharacteristic['reviewFull'] > time()) echo "true"; else echo "false"; // Оплатил ли пользователь премиум-доступ ?>;
    var propertyId = <?php echo $propertyCharacteristic['id']; // Идентификатор объекта недвижимости, чье описание мы смотрим ?>;
</script>
<!-- end scripts -->

</body>
</html>