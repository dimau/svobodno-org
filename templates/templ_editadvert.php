<?php
    // Инициализируем используемые в шаблоне переменные
    $propertyCharacteristic = $dataArr['propertyCharacteristic'];
    $propertyFotoInformation = $dataArr['propertyFotoInformation'];
    $errors = $dataArr['errors'];
    $allDistrictsInCity = $dataArr['allDistrictsInCity'];
    $isLoggedIn = $dataArr['isLoggedIn']; // Используется в templ_header.php
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Редактирование объявления</title>
    <meta name="description" content="Редактирование объявления">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/fileuploader.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Стили для создания нового Объявления*/

        .objectDescriptionItem .objectDescriptionItemLabel {
            min-width: 150px;
            width: 49%;
            text-align: right;
            display: inline-block;
            vertical-align: top;
        }

        .objectDescriptionItem .objectDescriptionBody {
            display: inline-block;
            width: 49%;
        }

        .objectDescriptionBody ul {
            list-style: none;
            padding: 0;
            margin: 0;
            line-height: 1.6em;
        }

        .advertDescriptionEdit {
            border: none;
            border-radius: 5px;
            padding: 0.2em;
            background-color: #ffffff;
        }

        .advertDescriptionChapterHeader {
            background-color: #6A9D02;
            font-size: 1.2em;
            padding-left: 15px;
            border-radius: 5px;
            color: white;
            text-align: left;
            line-height: 1.8em;
        }

        .tableForMap td {
            padding: 0;
        }

        .bottomButton {
            margin: 10px 10px 10px 10px;
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

<?php
    // Сформируем и вставим заголовок страницы
    include("templates/templ_header.php");
?>

<div class="page_main_content">

<div class="headerOfPage">
    Редактирование объявления.
    <?php
    if ($propertyCharacteristic['apartmentNumber'] != "") $apartmentNumberInHeader = ", № " . $propertyCharacteristic['apartmentNumber']; else $apartmentNumberInHeader = "";
    echo $this->globFunc->getFirstCharUpper($propertyCharacteristic['typeOfObject']) . " по адресу: " . $propertyCharacteristic['address'] . $apartmentNumberInHeader;
    ?>
</div>

<form method="post" name="newAdvert" class="advertDescriptionEdit">
<div class="advertDescriptionChapter" id="typeAndPeriodChapter">
    <div class="advertDescriptionChapterHeader">
        Тип и сроки
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Тип объекта:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="typeOfObject" id="typeOfObject" value='<?php echo $propertyCharacteristic['typeOfObject']; ?>'>
            <?php
            // Значение поля необходимо сохранить еще и в скрытом input, так как JS в зависимости от него будет делать некоторые элементы недоступными для редактирования
            echo $propertyCharacteristic['typeOfObject'];
            ?>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            С какого числа можно въезжать:
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfEntry" type="text" id="datepicker1" size="15"
                   placeholder="дд.мм.гггг" value='<?php echo $propertyCharacteristic['dateOfEntry'];?>'>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            На какой срок сдается:
        </div>
        <div class="objectDescriptionBody">
            <select name="termOfLease" id="termOfLease">
                <option value="0" <?php if ($propertyCharacteristic['termOfLease'] == "0") echo "selected";?>></option>
                <option value="длительный срок" <?php if ($propertyCharacteristic['termOfLease'] == "длительный срок") echo "selected";?>>
                    длительный срок (от года)
                </option>
                <option value="несколько месяцев" <?php if ($propertyCharacteristic['termOfLease'] == "несколько месяцев") echo "selected";?>>
                    несколько месяцев (до года)
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="termOfLease_0&termOfLease_длительный срок">
        <div class="objectDescriptionItemLabel">
            Крайний срок выезда арендатора(ов):
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfCheckOut" type="text" id="datepicker2" size="15"
                   placeholder="дд.мм.гггг" value='<?php echo $propertyCharacteristic['dateOfCheckOut'];?>'>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Фотографии:
        </div>
        <div class="objectDescriptionBody">
            <fieldset id='fotoWrapperBlock' class="edited private" style="min-width: 300px;">
                <input type='hidden' name='fileUploadId' id='fileUploadId' value='<?php echo $propertyFotoInformation['fileUploadId'];?>'>
                <input type='hidden' name='uploadedFoto' id='uploadedFoto' value=''>
                <div id="file-uploader">
                    <noscript>
                        <p>Пожалуйста, активируйте JavaScript для загрузки файлов</p>
                        <!-- or put a simple form for upload here -->
                    </noscript>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="roomsAndFacilitiesChapter">
    <div class="advertDescriptionChapterHeader">
        Комнаты и помещения
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Количество комнат в квартире, доме:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfRooms" id="amountOfRooms">
                <option value="0" <?php if ($propertyCharacteristic['amountOfRooms'] == "0") echo "selected";?>></option>
                <option value="1" <?php if ($propertyCharacteristic['amountOfRooms'] == "1") echo "selected";?>>1</option>
                <option value="2" <?php if ($propertyCharacteristic['amountOfRooms'] == "2") echo "selected";?>>2</option>
                <option value="3" <?php if ($propertyCharacteristic['amountOfRooms'] == "3") echo "selected";?>>3</option>
                <option value="4" <?php if ($propertyCharacteristic['amountOfRooms'] == "4") echo "selected";?>>4</option>
                <option value="5" <?php if ($propertyCharacteristic['amountOfRooms'] == "5") echo "selected";?>>5</option>
                <option value="6" <?php if ($propertyCharacteristic['amountOfRooms'] == "6") echo "selected";?>>6 или более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="amountOfRooms_0&amountOfRooms_1">
        <div class="objectDescriptionItemLabel">
            Комнаты смежные:
        </div>
        <div class="objectDescriptionBody">
            <select name="adjacentRooms" id="adjacentRooms">
                <option value="0" <?php if ($propertyCharacteristic['adjacentRooms'] == "0") echo "selected";?>></option>
                <option value="да" <?php if ($propertyCharacteristic['adjacentRooms'] == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($propertyCharacteristic['adjacentRooms'] == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_комната&typeOfObject_гараж&adjacentRooms_0&adjacentRooms_нет&amountOfRooms_0&amountOfRooms_1&amountOfRooms_2">
        <div class="objectDescriptionItemLabel">
            Количество смежных комнат:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfAdjacentRooms">
                <option value="0" <?php if ($propertyCharacteristic['amountOfAdjacentRooms'] == "0") echo "selected";?>></option>
                <option value="2" <?php if ($propertyCharacteristic['amountOfAdjacentRooms'] == "2") echo "selected";?>>2</option>
                <option value="3" <?php if ($propertyCharacteristic['amountOfAdjacentRooms'] == "3") echo "selected";?>>3</option>
                <option value="4" <?php if ($propertyCharacteristic['amountOfAdjacentRooms'] == "4") echo "selected";?>>4</option>
                <option value="5" <?php if ($propertyCharacteristic['amountOfAdjacentRooms'] == "5") echo "selected";?>>5</option>
                <option value="6" <?php if ($propertyCharacteristic['amountOfAdjacentRooms'] == "6") echo "selected";?>>6 или более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Санузел:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBathrooms">
                <option value="0" <?php if ($propertyCharacteristic['typeOfBathrooms'] == "0") echo "selected";?>></option>
                <option value="раздельный" <?php if ($propertyCharacteristic['typeOfBathrooms'] == "раздельный") echo "selected";?>>раздельный
                </option>
                <option value="совмещенный" <?php if ($propertyCharacteristic['typeOfBathrooms'] == "совмещенный") echo "selected";?>>
                    совмещенный
                </option>
                <option value="2 шт." <?php if ($propertyCharacteristic['typeOfBathrooms'] == "2 шт.") echo "selected";?>>2</option>
                <option value="3 шт." <?php if ($propertyCharacteristic['typeOfBathrooms'] == "3 шт.") echo "selected";?>>3</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Балкон/лоджия:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBalcony" id="typeOfBalcony">
                <option value="0" <?php if ($propertyCharacteristic['typeOfBalcony'] == "0") echo "selected";?>></option>
                <option value="нет" <?php if ($propertyCharacteristic['typeOfBalcony'] == "нет") echo "selected";?>>нет</option>
                <option value="балкон" <?php if ($propertyCharacteristic['typeOfBalcony'] == "балкон") echo "selected";?>>балкон</option>
                <option value="лоджия" <?php if ($propertyCharacteristic['typeOfBalcony'] == "лоджия") echo "selected";?>>лоджия</option>
                <option value="эркер" <?php if ($propertyCharacteristic['typeOfBalcony'] == "эркер") echo "selected";?>>эркер</option>
                <option value="балкон и лоджия" <?php if ($propertyCharacteristic['typeOfBalcony'] == "балкон и лоджия") echo "selected";?>>балкон
                    и лоджия
                </option>
                <option value="балкон и эркер" <?php if ($propertyCharacteristic['typeOfBalcony'] == "балкон и эркер") echo "selected";?>>балкон и
                    эркер
                </option>
                <option value="2 балкона и более" <?php if ($propertyCharacteristic['typeOfBalcony'] == "2 балкона и более") echo "selected";?>>2
                    балкона и более
                </option>
                <option value="2 лоджии и более" <?php if ($propertyCharacteristic['typeOfBalcony'] == "2 лоджии и более") echo "selected";?>>2
                    лоджии и более
                </option>
                <option value="2 эркера и более" <?php if ($propertyCharacteristic['typeOfBalcony'] == "2 эркера и более") echo "selected";?>>2
                    эркера и более
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfBalcony_0&typeOfBalcony_нет&typeOfBalcony_эркер&typeOfBalcony_2 эркера и более">
        <div class="objectDescriptionItemLabel">
            Остекление балкона/лоджии:
        </div>
        <div class="objectDescriptionBody">
            <select name="balconyGlazed">
                <option value="0" <?php if ($propertyCharacteristic['balconyGlazed'] == "0") echo "selected";?>></option>
                <option value="да" <?php if ($propertyCharacteristic['balconyGlazed'] == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($propertyCharacteristic['balconyGlazed'] == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_квартира&typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Площадь комнаты:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="roomSpace" value='<?php echo $propertyCharacteristic['roomSpace'];?>'>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_комната">
        <div class="objectDescriptionItemLabel">
            Площадь общая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="totalArea" value='<?php echo $propertyCharacteristic['totalArea'];?>'>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_комната&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Площадь жилая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="livingSpace" value='<?php echo $propertyCharacteristic['livingSpace'];?>'>
            м²
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Площадь кухни:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="kitchenSpace" value='<?php echo $propertyCharacteristic['kitchenSpace'];?>'>
            м²
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="floorAndPorchChapter">
    <div class="advertDescriptionChapterHeader">
        Этаж и подъезд
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Этаж:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="floor" value='<?php echo $propertyCharacteristic['floor'];?>'>
            <?php echo $propertyCharacteristic['floor'];?>
            из
            <input type="hidden" name="totalAmountFloor" value='<?php echo $propertyCharacteristic['totalAmountFloor'];?>'>
            <?php echo $propertyCharacteristic['totalAmountFloor'];?>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_квартира&typeOfObject_комната&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Этажность дома:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="numberOfFloor" value='<?php echo $propertyCharacteristic['numberOfFloor'];?>'>
            <?php echo $propertyCharacteristic['numberOfFloor'];?>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Консьерж:
        </div>
        <div class="objectDescriptionBody">
            <select name="concierge">
                <option value="0" <?php if ($propertyCharacteristic['concierge'] == "0") echo "selected";?>></option>
                <option value="есть" <?php if ($propertyCharacteristic['concierge'] == "есть") echo "selected";?>>есть</option>
                <option value="нет" <?php if ($propertyCharacteristic['concierge'] == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Домофон:
        </div>
        <div class="objectDescriptionBody">
            <select name="intercom">
                <option value="0" <?php if ($propertyCharacteristic['intercom'] == "0") echo "selected";?>></option>
                <option value="есть" <?php if ($propertyCharacteristic['intercom'] == "есть") echo "selected";?>>есть</option>
                <option value="нет" <?php if ($propertyCharacteristic['intercom'] == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Парковка во дворе:
        </div>
        <div class="objectDescriptionBody">
            <select name="parking">
                <option value="0" <?php if ($propertyCharacteristic['parking'] == "0") echo "selected";?>></option>
                <option value="охраняемая" <?php if ($propertyCharacteristic['parking'] == "охраняемая") echo "selected";?>>охраняемая</option>
                <option value="неохраняемая" <?php if ($propertyCharacteristic['parking'] == "неохраняемая") echo "selected";?>>неохраняемая
                </option>
                <option value="подземная" <?php if ($propertyCharacteristic['parking'] == "подземная") echo "selected";?>>подземная</option>
                <option value="отсутствует" <?php if ($propertyCharacteristic['parking'] == "отсутствует") echo "selected";?>>отсутствует</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="addressChapter">
    <div class="advertDescriptionChapterHeader">
        Местоположение
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Город:
        </div>
        <div class="objectDescriptionBody">
            <span>Екатеринбург</span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Район:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="district" value='<?php echo $propertyCharacteristic['district'];?>'>
            <?php
            if (isset($propertyCharacteristic['district'])) echo $propertyCharacteristic['district'];
            ?>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel" style="line-height: 2.3em;">
            Улица и номер дома:
        </div>
        <div class="objectDescriptionBody" style="min-width: 470px">
            <input type="hidden" name="coordX" id="coordX" value='<?php echo $propertyCharacteristic['coordX'];?>'>
            <input type="hidden" name="coordY" id="coordY" value='<?php echo $propertyCharacteristic['coordY'];?>'>
            <table class="tableForMap">
                <tbody>
                    <tr>
                        <td>
                            <input type="hidden" name="address" id="addressTextBox" value='<?php echo $propertyCharacteristic['address'];?>'>
                            <?php echo $propertyCharacteristic['address']; ?>
                        </td>
                        <td>
                            <button id="checkAddressButton" style='margin-left: 0.7em;'>Подтвердить адрес</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'><!-- Карта Яндекса -->
                            <div id="mapForNewAdvert" style="width: 100%; height: 400px; margin-top: 15px;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="objectDescriptionItem"
         notavailability="typeOfObject_0&typeOfObject_дом&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Номер квартиры:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="apartmentNumber" value='<?php echo $propertyCharacteristic['apartmentNumber'];?>'>
            <!-- Значение поля необходимо сохранить, так как JS в зависимости от него будет делать некоторые элементы недоступными для редактирования -->
            <?php if ($propertyCharacteristic['apartmentNumber'] != "") echo $propertyCharacteristic['apartmentNumber']; ?>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_дача&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Станция метро рядом:
        </div>
        <div class="objectDescriptionBody">
            <select name="subwayStation" id="subwayStation">
                <option value="0" <?php if ($propertyCharacteristic['subwayStation'] == "0") echo "selected";?>></option>
                <option value="нет" <?php if ($propertyCharacteristic['subwayStation'] == "нет") echo "selected";?>>Нет</option>
                <option
                    value="Проспект Космонавтов" <?php if ($propertyCharacteristic['subwayStation'] == "Проспект Космонавтов") echo "selected";?>>
                    Проспект Космонавтов
                </option>
                <option value="Уралмаш" <?php if ($propertyCharacteristic['subwayStation'] == "Уралмаш") echo "selected";?>>Уралмаш</option>
                <option value="Машиностроителей" <?php if ($propertyCharacteristic['subwayStation'] == "Машиностроителей") echo "selected";?>>
                    Машиностроителей
                </option>
                <option value="Уральская" <?php if ($propertyCharacteristic['subwayStation'] == "Уральская") echo "selected";?>>Уральская</option>
                <option value="Динамо" <?php if ($propertyCharacteristic['subwayStation'] == "Динамо") echo "selected";?>>Динамо</option>
                <option value="Площадь 1905 г." <?php if ($propertyCharacteristic['subwayStation'] == "Площадь 1905 г.") echo "selected";?>>
                    Площадь 1905 г.
                </option>
                <option value="Геологическая" <?php if ($propertyCharacteristic['subwayStation'] == "Геологическая") echo "selected";?>>
                    Геологическая
                </option>
                <option value="Чкаловская" <?php if ($propertyCharacteristic['subwayStation'] == "Чкаловская") echo "selected";?>>Чкаловская
                </option>
                <option value="Ботаническая" <?php if ($propertyCharacteristic['subwayStation'] == "Ботаническая") echo "selected";?>>
                    Ботаническая
                </option>
            </select>
            <span notavailability="subwayStation_0&subwayStation_нет">
            <input type="text" name="distanceToMetroStation" size="7" value='<?php echo $propertyCharacteristic['distanceToMetroStation'];?>'>
            мин. ходьбы
            </span>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="costChapter">
    <div class="advertDescriptionChapterHeader">
        Стоимость, условия оплаты
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Валюта для расчетов:
        </div>
        <div class="objectDescriptionBody">
            <select name="currency" id="currency">
                <option value="0" <?php if ($propertyCharacteristic['currency'] == "0") echo "selected";?>></option>
                <option value="руб." <?php if ($propertyCharacteristic['currency'] == "руб.") echo "selected";?>>рубль</option>
                <option value="дол. США" <?php if ($propertyCharacteristic['currency'] == "дол. США") echo "selected";?>>доллар США</option>
                <option value="евро" <?php if ($propertyCharacteristic['currency'] == "евро") echo "selected";?>>евро</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Плата за аренду:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="costOfRenting" id="costOfRenting" size="7" value='<?php echo $propertyCharacteristic['costOfRenting'];?>'>
            <span class="currency"></span> в месяц
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Коммунальные услуги оплачиваются арендатором дополнительно:
        </div>
        <div class="objectDescriptionBody">
            <select name="utilities" id="utilities">
                <option value="0" <?php if ($propertyCharacteristic['utilities'] == "0") echo "selected";?>></option>
                <option value="да" <?php if ($propertyCharacteristic['utilities'] == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($propertyCharacteristic['utilities'] == "нет") echo "selected";?>>нет</option>
            </select>
            <span notavailability="utilities_0&utilities_нет">
            Летом
            <input type="text" name="costInSummer" size="7" value='<?php echo $propertyCharacteristic['costInSummer'];?>'>
            <span class="currency"></span> Зимой
            <input type="text" name="costInWinter" size="7" value='<?php echo $propertyCharacteristic['costInWinter'];?>'>
            <span class="currency"></span>
            </span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Электроэнергия оплачивается дополнительно:
        </div>
        <div class="objectDescriptionBody">
            <select name="electricPower">
                <option value="0" <?php if ($propertyCharacteristic['electricPower'] == "0") echo "selected";?>></option>
                <option value="да" <?php if ($propertyCharacteristic['electricPower'] == "да") echo "selected";?>>да</option>
                <option value="нет" <?php if ($propertyCharacteristic['electricPower'] == "нет") echo "selected";?>>нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Залог:
        </div>
        <div class="objectDescriptionBody">
            <select name="bail" id="bail">
                <option value="0" <?php if ($propertyCharacteristic['bail'] == "0") echo "selected";?>></option>
                <option value="есть" <?php if ($propertyCharacteristic['bail'] == "есть") echo "selected";?>>есть</option>
                <option value="нет" <?php if ($propertyCharacteristic['bail'] == "нет") echo "selected";?>>нет</option>
            </select>
            <span notavailability="bail_0&bail_нет">
            <input type="text" name="bailCost" size="7" value='<?php echo $propertyCharacteristic['bailCost'];?>'>
            <span class="currency"></span>
            </span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Предоплата:
        </div>
        <div class="objectDescriptionBody">
            <select name="prepayment">
                <option value="0" <?php if ($propertyCharacteristic['prepayment'] == "0") echo "selected";?>></option>
                <option value="нет" <?php if ($propertyCharacteristic['prepayment'] == "нет") echo "selected";?>>нет</option>
                <option value="1 месяц" <?php if ($propertyCharacteristic['prepayment'] == "1 месяц") echo "selected";?>>1 месяц</option>
                <option value="2 месяца" <?php if ($propertyCharacteristic['prepayment'] == "2 месяца") echo "selected";?>>2 месяца</option>
                <option value="3 месяца" <?php if ($propertyCharacteristic['prepayment'] == "3 месяца") echo "selected";?>>3 месяца</option>
                <option value="4 месяца" <?php if ($propertyCharacteristic['prepayment'] == "4 месяца") echo "selected";?>>4 месяца</option>
                <option value="5 месяцев" <?php if ($propertyCharacteristic['prepayment'] == "5 месяцев") echo "selected";?>>5 месяцев</option>
                <option value="6 месяцев" <?php if ($propertyCharacteristic['prepayment'] == "6 месяцев") echo "selected";?>>6 месяцев</option>
            </select>
        </div>
    </div>
    <input type="hidden" name="compensationMoney" id="compensationMoney" value='<?php echo $propertyCharacteristic['compensationMoney'];?>'>
    <input type="hidden" name="compensationPercent" id="compensationPercent" value='<?php echo $propertyCharacteristic['compensationPercent'];?>'>
</div>

<div class="advertDescriptionChapter" id="currentStatus">
    <div class="advertDescriptionChapterHeader">
        Текущее состояние
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Ремонт:
        </div>
        <div class="objectDescriptionBody">
            <select name="repair">
                <option value="0" <?php if ($propertyCharacteristic['repair'] == "0") echo "selected";?>></option>
                <option
                    value="не выполнялся (новый дом)" <?php if ($propertyCharacteristic['repair'] == "не выполнялся (новый дом)") echo "selected";?>>
                    не выполнялся (новый дом)
                </option>
                <option value="сделан только что" <?php if ($propertyCharacteristic['repair'] == "сделан только что") echo "selected";?>>сделан
                    только что
                </option>
                <option value="меньше 1 года назад" <?php if ($propertyCharacteristic['repair'] == "меньше 1 года назад") echo "selected";?>>
                    меньше 1 года назад
                </option>
                <option value="больше года назад" <?php if ($propertyCharacteristic['repair'] == "больше года назад") echo "selected";?>>больше
                    года назад
                </option>
                <option value="выполнялся давно" <?php if ($propertyCharacteristic['repair'] == "выполнялся давно") echo "selected";?>>выполнялся
                    давно
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Отделка:
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <select name="furnish">
                <option value="0" <?php if ($propertyCharacteristic['furnish'] == "0") echo "selected";?>></option>
                <option value="евростандарт" <?php if ($propertyCharacteristic['furnish'] == "евростандарт") echo "selected";?>>евростандарт
                </option>
                <option
                    value="свежая (новые обои, побелка потолков)" <?php if ($propertyCharacteristic['furnish'] == "свежая (новые обои, побелка потолков)") echo "selected";?>>
                    свежая (новые обои, побелка потолков)
                </option>
                <option value="бабушкин вариант" <?php if ($propertyCharacteristic['furnish'] == "бабушкин вариант") echo "selected";?>>бабушкин
                    вариант
                </option>
                <option value="требует обновления" <?php if ($propertyCharacteristic['furnish'] == "требует обновления") echo "selected";?>>
                    требует обновления
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Окна:
        </div>
        <div class="objectDescriptionBody">
            <select name="windows">
                <option value="0" <?php if ($propertyCharacteristic['windows'] == "0") echo "selected";?>></option>
                <option value="деревянные" <?php if ($propertyCharacteristic['windows'] == "деревянные") echo "selected";?>>деревянные</option>
                <option value="пластиковые" <?php if ($propertyCharacteristic['windows'] == "пластиковые") echo "selected";?>>пластиковые</option>
                <option value="иное" <?php if ($propertyCharacteristic['windows'] == "иное") echo "selected";?>>иное</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="communication">
    <div class="advertDescriptionChapterHeader">
        Связь
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Интернет:
        </div>
        <div class="objectDescriptionBody">
            <select name="internet">
                <option value="0" <?php if ($propertyCharacteristic['internet'] == "0") echo "selected";?>></option>
                <option value="проведен" <?php if ($propertyCharacteristic['internet'] == "проведен") echo "selected";?>>проведен</option>
                <option value="не проведен" <?php if ($propertyCharacteristic['internet'] == "не проведен") echo "selected";?>>не проведен
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Телефон:
        </div>
        <div class="objectDescriptionBody">
            <select name="telephoneLine">
                <option value="0" <?php if ($propertyCharacteristic['telephoneLine'] == "0") echo "selected";?>></option>
                <option value="проведен" <?php if ($propertyCharacteristic['telephoneLine'] == "проведен") echo "selected";?>>проведен</option>
                <option value="не проведен" <?php if ($propertyCharacteristic['telephoneLine'] == "не проведен") echo "selected";?>>не проведен
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Кабельное ТВ:
        </div>
        <div class="objectDescriptionBody">
            <select name="cableTV">
                <option value="0" <?php if ($propertyCharacteristic['cableTV'] == "0") echo "selected";?>></option>
                <option value="проведено" <?php if ($propertyCharacteristic['cableTV'] == "проведено") echo "selected";?>>проведено</option>
                <option value="не проведено" <?php if ($propertyCharacteristic['cableTV'] == "не проведено") echo "selected";?>>не проведено
                </option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="furniture">
    <div class="advertDescriptionChapterHeader">
        Мебель и бытовая техника
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Мебель в жилой зоне:
        </div>
        <div class="objectDescriptionBody">
            <ul>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]" value="диван раскладной"
                        <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "диван раскладной") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> диван раскладной
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="диван нераскладной" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "диван нераскладной") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> диван нераскладной
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кровать одноместная" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "кровать одноместная") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кровать одноместная
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кровать двухместная" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "кровать двухместная") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кровать двухместная
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кровать детская" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "кровать детская") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кровать детская
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол письменный" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "стол письменный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол письменный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол компьютерный" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "стол компьютерный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол компьютерный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол журнальный" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "стол журнальный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол журнальный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стол раскладной" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "стол раскладной") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол раскладной
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кресло раскладное" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "кресло раскладное") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кресло раскладное
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="кресло нераскладное" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "кресло нераскладное") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кресло нераскладное
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стулья и табуретки" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "стулья и табуретки") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стулья и табуретки
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="стенка" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "стенка") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стенка
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="шкаф для одежды" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "шкаф для одежды") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкаф для одежды
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="шкаф-купе" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "шкаф-купе") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкаф-купе
                </li>
                <li>
                    <input type="checkbox" name="furnitureInLivingArea[]"
                           value="комод" <?php foreach ($propertyCharacteristic['furnitureInLivingArea'] as $value) {
                        if ($value == "комод") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> комод
                </li>
                <li>
                    <input type="text" name="furnitureInLivingAreaExtra" maxlength="254"
                           title='Перечислите через запятую те предметы мебели в жилой зоне, что предоставляются вместе с арендуемой недвижимостью и не были указаны в списке выше. Например: "трюмо, тумбочка под телевизор"' value='<?php echo $propertyCharacteristic['furnitureInLivingAreaExtra'];?>'>
                </li>
            </ul>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Мебель на кухне:
        </div>
        <div class="objectDescriptionBody">
            <ul>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="стол обеденный" <?php foreach ($propertyCharacteristic['furnitureInKitchen'] as $value) {
                        if ($value == "стол обеденный") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стол обеденный
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="стулья, табуретки" <?php foreach ($propertyCharacteristic['furnitureInKitchen'] as $value) {
                        if ($value == "стулья, табуретки") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стулья, табуретки
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="диван" <?php foreach ($propertyCharacteristic['furnitureInKitchen'] as $value) {
                        if ($value == "диван") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> диван
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="кухонный гарнитур" <?php foreach ($propertyCharacteristic['furnitureInKitchen'] as $value) {
                        if ($value == "кухонный гарнитур") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кухонный гарнитур
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="шкафчики навесные" <?php foreach ($propertyCharacteristic['furnitureInKitchen'] as $value) {
                        if ($value == "шкафчики навесные") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкафчики навесные
                </li>
                <li>
                    <input type="checkbox" name="furnitureInKitchen[]"
                           value="шкафчики напольные" <?php foreach ($propertyCharacteristic['furnitureInKitchen'] as $value) {
                        if ($value == "шкафчики напольные") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> шкафчики напольные
                </li>
                <li>
                    <input type="text" name="furnitureInKitchenExtra" maxlength="254"
                           title='Перечислите через запятую те предметы мебели на кухне, что предоставляются вместе с арендуемой недвижимостью и не были указаны в списке выше. Например: "трюмо, тумбочка под телевизор"' value='<?php echo $propertyCharacteristic['furnitureInKitchenExtra'];?>'>
                </li>
            </ul>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Бытовая техника:
        </div>
        <div class="objectDescriptionBody">
            <ul>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="холодильник" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "холодильник") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> холодильник
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="микроволновая печь" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "микроволновая печь") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> микроволновая печь
                </li>
                <li>
                    <input type="checkbox" name="appliances[]" value="телевизор" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "телевизор") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> телевизор
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="стиральная машина (автомат)" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "стиральная машина (автомат)") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стиральная машина (автомат)
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="стиральная машина (не автомат)" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "стиральная машина (не автомат)") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> стиральная машина (не автомат)
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="нагреватель воды" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "нагреватель воды") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> нагреватель воды
                </li>
                <li>
                    <input type="checkbox" name="appliances[]" value="пылесос" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "пылесос") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> пылесос
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="кондиционер" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "кондиционер") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> кондиционер
                </li>
                <li>
                    <input type="checkbox" name="appliances[]"
                           value="охранная сигнализация" <?php foreach ($propertyCharacteristic['appliances'] as $value) {
                        if ($value == "охранная сигнализация") {
                            echo "checked";
                            break;
                        }
                    }
                        ?>> охранная сигнализация
                </li>
                <li>
                    <input type="text" name="appliancesExtra" maxlength="254"
                           title='Перечислите через запятую ту бытовую технику, что предоставляется вместе с арендуемой недвижимостью и не была указана в списке выше. Например: "кухонный комбайн, компьютер"' value='<?php echo $propertyCharacteristic['appliancesExtra'];?>'>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="requirementsForTenant">
    <div class="advertDescriptionChapterHeader">
        Требования к арендатору
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Пол:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="sexOfTenant[]" value="мужчина" <?php foreach ($propertyCharacteristic['sexOfTenant'] as $value) {
                if ($value == "мужчина") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            мужчина
            <br>
            <input type="checkbox" name="sexOfTenant[]" value="женщина" <?php foreach ($propertyCharacteristic['sexOfTenant'] as $value) {
                if ($value == "женщина") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            женщина
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Отношения между арендаторами:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="relations[]" value="один человек" <?php foreach ($propertyCharacteristic['relations'] as $value) {
                if ($value == "один человек") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            один человек
            <br>
            <input type="checkbox" name="relations[]" value="семья" <?php foreach ($propertyCharacteristic['relations'] as $value) {
                if ($value == "семья") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            семья
            <br>
            <input type="checkbox" name="relations[]" value="пара" <?php foreach ($propertyCharacteristic['relations'] as $value) {
                if ($value == "пара") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            пара
            <br>
            <input type="checkbox" name="relations[]" value="2 мальчика" <?php foreach ($propertyCharacteristic['relations'] as $value) {
                if ($value == "2 мальчика") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            2 мальчика
            <br>
            <input type="checkbox" name="relations[]" value="2 девочки" <?php foreach ($propertyCharacteristic['relations'] as $value) {
                if ($value == "2 девочки") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            2 девочки
            <br>
            <input type="checkbox" name="relations[]" value="группа людей" <?php foreach ($propertyCharacteristic['relations'] as $value) {
                if ($value == "группа людей") {
                    echo "checked";
                    break;
                }
            }
                ?>>
            группа людей
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Дети:
        </div>
        <div class="objectDescriptionBody">
            <select name="children">
                <option value="0" <?php if ($propertyCharacteristic['children'] == "0") echo "selected";?>></option>
                <option value="не имеет значения" <?php if ($propertyCharacteristic['children'] == "не имеет значения") echo "selected";?>>не
                    имеет значения
                </option>
                <option
                    value="с детьми старше 4-х лет" <?php if ($propertyCharacteristic['children'] == "с детьми старше 4-х лет") echo "selected";?>>
                    с детьми старше 4-х лет
                </option>
                <option value="только без детей" <?php if ($propertyCharacteristic['children'] == "только без детей") echo "selected";?>>только
                    без детей
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" notavailability="typeOfObject_0&typeOfObject_гараж">
        <div class="objectDescriptionItemLabel">
            Животные:
        </div>
        <div class="objectDescriptionBody">
            <select name="animals">
                <option value="0" <?php if ($propertyCharacteristic['animals'] == "0") echo "selected";?>></option>
                <option value="не имеет значения" <?php if ($propertyCharacteristic['animals'] == "не имеет значения") echo "selected";?>>не имеет
                    значения
                </option>
                <option value="только без животных" <?php if ($propertyCharacteristic['animals'] == "только без животных") echo "selected";?>>
                    только без животных
                </option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="specialConditions">
    <div class="advertDescriptionChapterHeader">
        Особые условия
    </div>
    <div class="objectDescriptionItem" title="Этот номер будет доступен для заинтересовавшихся арендаторов">
        <div class="objectDescriptionItemLabel">
            Контактный номер телефона:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="contactTelephonNumber" size="15" value='<?php echo $propertyCharacteristic['contactTelephonNumber'];?>'>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel" style="line-height: 1.8em;">
            Время для звонков:
        </div>
        <div class="objectDescriptionBody">
            с
            <select name="timeForRingBegin">
                <option value="0" <?php if ($propertyCharacteristic['timeForRingBegin'] == "0") echo "selected";?>></option>
                <option value="6:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "6:00") echo "selected";?>>6:00</option>
                <option value="7:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "7:00") echo "selected";?>>7:00</option>
                <option value="8:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "8:00") echo "selected";?>>8:00</option>
                <option value="9:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "9:00") echo "selected";?>>9:00</option>
                <option value="10:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "10:00") echo "selected";?>>10:00</option>
                <option value="11:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "11:00") echo "selected";?>>11:00</option>
                <option value="12:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "12:00") echo "selected";?>>12:00</option>
                <option value="13:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "13:00") echo "selected";?>>13:00</option>
                <option value="14:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "14:00") echo "selected";?>>14:00</option>
                <option value="15:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "15:00") echo "selected";?>>15:00</option>
                <option value="16:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "16:00") echo "selected";?>>16:00</option>
                <option value="17:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "17:00") echo "selected";?>>17:00</option>
                <option value="18:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "18:00") echo "selected";?>>18:00</option>
                <option value="19:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "19:00") echo "selected";?>>19:00</option>
                <option value="20:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "20:00") echo "selected";?>>20:00</option>
                <option value="21:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "21:00") echo "selected";?>>21:00</option>
                <option value="22:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "22:00") echo "selected";?>>22:00</option>
                <option value="23:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "23:00") echo "selected";?>>23:00</option>
                <option value="24:00" <?php if ($propertyCharacteristic['timeForRingBegin'] == "24:00") echo "selected";?>>24:00</option>
            </select>
            до
            <select name="timeForRingEnd">
                <option value="0" <?php if ($propertyCharacteristic['timeForRingEnd'] == "0") echo "selected";?>></option>
                <option value="6:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "6:00") echo "selected";?>>6:00</option>
                <option value="7:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "7:00") echo "selected";?>>7:00</option>
                <option value="8:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "8:00") echo "selected";?>>8:00</option>
                <option value="9:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "9:00") echo "selected";?>>9:00</option>
                <option value="10:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "10:00") echo "selected";?>>10:00</option>
                <option value="11:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "11:00") echo "selected";?>>11:00</option>
                <option value="12:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "12:00") echo "selected";?>>12:00</option>
                <option value="13:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "13:00") echo "selected";?>>13:00</option>
                <option value="14:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "14:00") echo "selected";?>>14:00</option>
                <option value="15:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "15:00") echo "selected";?>>15:00</option>
                <option value="16:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "16:00") echo "selected";?>>16:00</option>
                <option value="17:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "17:00") echo "selected";?>>17:00</option>
                <option value="18:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "18:00") echo "selected";?>>18:00</option>
                <option value="19:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "19:00") echo "selected";?>>19:00</option>
                <option value="20:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "20:00") echo "selected";?>>20:00</option>
                <option value="21:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "21:00") echo "selected";?>>21:00</option>
                <option value="22:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "22:00") echo "selected";?>>22:00</option>
                <option value="23:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "23:00") echo "selected";?>>23:00</option>
                <option value="24:00" <?php if ($propertyCharacteristic['timeForRingEnd'] == "24:00") echo "selected";?>>24:00</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Где проживает собственник:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <select name="checking">
                <option value="0"  <?php if ($propertyCharacteristic['checking'] == "0") echo "selected";?>></option>
                <option
                    value="в другом городе" <?php if ($propertyCharacteristic['checking'] == "в другом городе") echo "selected";?>>
                    в другом городе
                </option>
                <option
                    value="отдельно" <?php if ($propertyCharacteristic['checking'] == "отдельно") echo "selected";?>>
                    отдельно
                </option>
                <option
                    value="рядом (в качестве соседа)" <?php if ($propertyCharacteristic['checking'] == "рядом (в качестве соседа)") echo "selected";?>>
                    рядом (в качестве соседа)
                </option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem"
         title="Какую ответственность за состояние и ремонт объекта берет на себя собственник">
        <div class="objectDescriptionItemLabel">
            Ответственность за состояние и ремонт недвижимости:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="responsibility" maxlength="2000" rows="7" cols="43"><?php echo $propertyCharacteristic['responsibility'];?></textarea>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Дополнительный комментарий:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="comment" maxlength="2000" rows="7" cols="43"><?php echo $propertyCharacteristic['comment'];?></textarea>
        </div>
    </div>
</div>

<div class="bottomButton">
    <a href="personal.php?tabsId=3" style="margin-right: 10px;">Отмена</a>
    <button type="submit" name="saveAdvertButton" id="saveAdvertButton" class="button mainButton">
        Сохранить
    </button>
</div>
<div class="clearBoth"></div>

</form>

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
    var uploadedFoto = JSON.parse('<?php echo json_encode($propertyFotoInformation['uploadedFoto']);?>');
</script>
<script src="js/main.js"></script>
<script src="js/newOrEditAdvert.js"></script>
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