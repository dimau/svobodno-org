<?php
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

/*************************************************************************************
 * Если пользователь не авторизирован, то пересылаем юзера на страницу авторизации
 ************************************************************************************/
if (!login()) {
    header('Location: login.php');
}

// Готовим массив со списком районов в городе пользователя
$rezDistricts = mysql_query("SELECT * FROM districts WHERE city = '" . "Екатеринбург" . "'");
for ($i = 0; $i < mysql_num_rows($rezDistricts); $i++) {
    $rowDistricts = mysql_fetch_assoc($rezDistricts);
    $allDistrictsInCity[$rowDistricts['id']] = $rowDistricts['name'];
}

// Проверить личные данные пользователя на полноту для его работы в качестве собственника, если у него typeOwner != "true"

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Новое объявление</title>
    <meta name="description" content="Новое объявление">

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
                <span id="userMistakesText">Для продолжения, пожалуйста, дополните или исправьте следующие данные:</span>
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

<!-- Сформируем и вставим заголовок страницы -->
<?php
include("header.php");
?>

<div class="page_main_content">

<div class="wrapperOfTabs">
<div class="headerOfPage">
    Новое объявление
</div>

<form name="advert0" class="advertDescriptionEdit">
<div class="advertDescriptionChapter" id="mainParametersChapter">
    <div class="advertDescriptionChapterHeader">
        Описание объекта
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Тип объекта:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfObject" id="typeOfObject">
                <option value="0" selected></option>
                <option value="flat">квартира</option>
                <option value="room">комната</option>
                <option value="house">дом, коттедж</option>
                <option value="townhouse">таунхаус</option>
                <option value="dacha">дача</option>
                <option value="garage">гараж</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            С какого числа можно въезжать:
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfEntry" type="text" id="datepicker1" size="15" placeholder="дд.мм.гггг">
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            На какой срок сдается:
        </div>
        <div class="objectDescriptionBody">
            <select name="termOfLease" id="termOfLease">
                <option value="0" selected></option>
                <option value="long">длительный срок (от года)</option>
                <option value="little">несколько месяцев (до года)</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottermOfLeaselong">
        <div class="objectDescriptionItemLabel">
            Крайний срок выезда арендатора(ов):
        </div>
        <div class="objectDescriptionBody">
            <input name="dateOfCheckOut" type="text" id="datepicker2" size="15" placeholder="дд.мм.гггг">
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Количество комнат в квартире, доме:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfRooms" id="amountOfRooms">
                <option value="0" selected></option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6 и более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem notamountOfRooms1">
        <div class="objectDescriptionItemLabel">
            Комнаты смежные:
        </div>
        <div class="objectDescriptionBody">
            <select name="adjacentRooms" id="adjacentRooms">
                <option value="0" selected></option>
                <option value="yes">да</option>
                <option value="no">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectroom notadjacentRoomsno notamountOfRooms2">
        <div class="objectDescriptionItemLabel">
            Количество смежных комнат в квартире, доме:
        </div>
        <div class="objectDescriptionBody">
            <select name="amountOfAdjacentRooms">
                <option value="0" selected></option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6 и более</option>
            </select>
            Появляется только, если селект "Комнаты смежные" == да и тип объекта не равен Комната и Квартира с 1 или 2-мя комнатами
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Санузел:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBathrooms">
                <option value="0" selected></option>
                <option value="separate">раздельный</option>
                <option value="combined">совмещенный</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Балкон/лоджия:
        </div>
        <div class="objectDescriptionBody">
            <select name="typeOfBalcony" id="typeOfBalcony">
                <option value="0" selected></option>
                <option value="not">нет</option>
                <option value="balcony">балкон</option>
                <option value="loggia">лоджия</option>
                <option value="oriel">эркер</option>
                <option value="balconyAndLoggia">балкон и лоджия</option>
                <option value="balconyAndOriel">балкон и эркер</option>
                <option value="2balcony">2 балкона и более</option>
                <option value="2loggia">2 лоджии и более</option>
                <option value="2oriel">2 эркера и более</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfBalconynot nottypeOfBalconyoriel nottypeOfBalcony2oriel">
        <div class="objectDescriptionItemLabel">
            Остекление балкона/лоджии:
        </div>
        <div class="objectDescriptionBody">
            <select name="balconyGlazed">
                <option value="0" selected></option>
                <option value="yes">да</option>
                <option value="no">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectflat nottypeOfObjecthouse nottypeOfObjecttownhouse nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Площадь комнаты:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="roomSpace" value="">
            м² (Проверка на число)
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectroom">
        <div class="objectDescriptionItemLabel">
            Площадь общая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="totalАrea" value="">
            м² (Проверка на число)
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectroom nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Площадь жилая:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="livingSpace" value="">
            м² (Проверка на число)
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Площадь кухни:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="kitchenSpace" value="">
            м² (Проверка на число)
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjecthouse nottypeOfObjecttownhouse nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Этаж:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="3" name="floor" value="">
            из
            <input type="text" size="3" name="totalAmountFloor" value="">
            Проверка на число
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectflat nottypeOfObjectroom nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Этажность дома:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="3" name="numberOfFloor" value="">
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjecthouse nottypeOfObjecttownhouse nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Консьерж:
        </div>
        <div class="objectDescriptionBody">
            <select name="concierge">
                <option value="0" selected></option>
                <option value="yes">есть</option>
                <option value="no">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Домофон:
        </div>
        <div class="objectDescriptionBody">
            <select name="intercom">
                <option value="0" selected></option>
                <option value="yes">есть</option>
                <option value="no">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Парковка во дворе:
        </div>
        <div class="objectDescriptionBody">
            <select name="parking">
                <option value="0" selected></option>
                <option value="guarded">охраняемая</option>
                <option value="unguarded">неохраняемая</option>
                <option value="underground">подземная</option>
                <option value="no">отсутствует</option>
            </select>
            Не отображается, если тип == Гараж
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Фотографии:
        </div>
        <div class="objectDescriptionBody">
            <input type="hidden" name="fileUploadId" id="fileUploadId" <?php /* echo "value='$fileUploadId'"; */?>>
            <?php /*
            // Получаем информацию о всех загруженных фото и формируем для каждого свой input type hidden для передачи данных в обработчик яваскрипта
            if ($rez = mysql_query("SELECT * FROM tempFotos WHERE fileuploadid = '" . $fileUploadId . "'")) // ищем уже загруженные пользователем фотки
            {
                $numUploadedFiles = mysql_num_rows($rez);
                for ($i = 0; $i < $numUploadedFiles; $i++) {
                    $row = mysql_fetch_assoc($rez);
                    echo "<input type='hidden' class='uploadedFoto' filename='" . $row['filename'] . "' filesizeMb='" . $row['filesizeMb'] . "'>";
                }
            } */
            ?>
            <div id="file-uploader">
                <noscript>
                    <p>Пожалуйста, активируйте JavaScript для загрузки файлов</p>
                    <!-- or put a simple form for upload here -->
                </noscript>
            </div>
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
            <select name="district">
                <option value="0"></option>
            <?php
            if (isset($allDistrictsInCity)) {
                foreach ($allDistrictsInCity as $key => $value) { // Для каждого идентификатора района и названия формируем пункт селекта
                    echo "<option value='" . $key . "'>" . $value . "</option>";
                }
            }
            ?>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Улица и номер дома:
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <table>
                <tbody>
                <tr>
                    <td>
                        <input type="text" name="address" id="addressTextBox" size="30" value="">
                        <input type="button" value="Проверить адрес" id="checkAddressButton">
                    </td>
                </tr>
                <tr>
                    <td><!-- Карта Яндекса -->
                        <div id="mapForNewAdvert" style="width: 400px; height: 400px; margin-top: 8px;"></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjecthouse nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Номер квартиры:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="apartmentNumber" size="7" value="">
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectdacha nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Станция метро рядом:
        </div>
        <div class="objectDescriptionBody">
            <select name="subwayStation" id="subwayStation">
                <option value="0" selected></option>
                <option value="not">Нет</option>
                <option value="ProspectCosmonauts">Проспект Космонавтов</option>
                <option value="Uralmash">Уралмаш</option>
                <option value="Mashinostroiteley">Машиностроителей</option>
                <option value="Uralskaya">Уральская</option>
                <option value="Dinamo">Динамо</option>
                <option value="Ploshchad1905g">Площадь 1905 г.</option>
                <option value="Geologicheskaya">Геологическая</option>
                <option value="Chkalovskaya">Чкаловская</option>
                <option value="Botanicheskaya">Ботаническая</option>
            </select>
            <span class="notsubwayStationnot">
            <input type="text" name="distanceToMetroStation" size="7" value="">
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
            <select name="currency">
                <option value="0" selected></option>
                <option value="rubl">рубль</option>
                <option value="dollar">доллар США</option>
                <option value="euro">евро</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Плата за аренду:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" name="costOfRenting" size="7" value="">
            руб. в месяц
            Подставлять валюту из селекта
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Коммунальные услуги оплачиваются арендатором дополнительно:
        </div>
        <div class="objectDescriptionBody">
            <select name="utilities" id="utilities">
                <option value="0" selected></option>
                <option value="yes">да</option>
                <option value="no">нет</option>
            </select>
            <span class="notutilitiesno">
            Летом
            <input type="text" name="costInSummer" size="7" value="">
            руб. Зимой
            <input type="text" name="costInWinter" size="7" value="">
            руб.
            Подставлять валюту из селекта
            </span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Электроэнергия оплачивается дополнительно:
        </div>
        <div class="objectDescriptionBody">
            <select name="electricPower">
                <option value="0" selected></option>
                <option value="yes">да</option>
                <option value="no">нет</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Залог:
        </div>
        <div class="objectDescriptionBody">
            <select name="bail" id="bail">
                <option value="0" selected></option>
                <option value="yes">есть</option>
                <option value="no">нет</option>
            </select>
            <span class="notbailno">
            <input type="text" name="bailCost" size="7" value=""> Появляется только, если селект == есть
            руб.
            </span>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Предоплата:
        </div>
        <div class="objectDescriptionBody">
            <select name="prepayment">
                <option value="0" selected></option>
                <option value="no">нет</option>
                <option value="1">1 месяц</option>
                <option value="2">2 месяца</option>
                <option value="3">3 месяца</option>
                <option value="4">4 месяца</option>
                <option value="5">5 месяцев</option>
                <option value="6">6 месяцев</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem" title="Предназначена для компенсации затрат собственника на публикацию объявления и поиск арендатора">
        <div class="objectDescriptionItemLabel">
            Единоразовая комиссия собственника:
        </div>
        <div class="objectDescriptionBody">
            <input type="text" size="7" name="compensationMoney" value="">
            руб. или <input type="text" size="7" name="compensationPercent" value=""> % от стоимости аренды
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="currentStatus">
    <div class="advertDescriptionChapterHeader">
        Текущее состояние
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Ремонт:
        </div>
        <div class="objectDescriptionBody">
            <select name="repair">
                <option value="0" selected></option>
                <option value="no">не выполнялся (новый дом)</option>
                <option value="just">сделан только что</option>
                <option value="less1year">меньше 1 года назад</option>
                <option value="over1year">больше года назад</option>
                <option value="long">выполнялся давно</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Отделка:
        </div>
        <div class="objectDescriptionBody" style="min-width: 400px">
            <select name="furnish">
                <option value="0" selected></option>
                <option value="euro">евростандарт</option>
                <option value="fresh">свежая (новые обои, побелка потолков)</option>
                <option value="grandma">бабушкин вариант</option>
                <option value="needsUpdated">требует обновления</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Окна:
        </div>
        <div class="objectDescriptionBody">
            <select name="windows">
                <option value="0" selected></option>
                <option value="wooden">деревянные</option>
                <option value="plastic">пластиковые</option>
                <option value="otherwise">иное</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="communication">
    <div class="advertDescriptionChapterHeader">
        Связь
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Интернет:
        </div>
        <div class="objectDescriptionBody">
            <select name="internet">
                <option value="0" selected></option>
                <option value="nono">не проведен, нельзя провести</option>
                <option value="no">не проведен, можно провести</option>
                <option value="yes">проведен, можно использовать</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Телефон:
        </div>
        <div class="objectDescriptionBody">
            <select name="telephoneLine">
                <option value="0" selected></option>
                <option value="nono">не проведен, нельзя провести</option>
                <option value="no">не проведен, можно провести</option>
                <option value="yes">проведен, можно использовать</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem nottypeOfObjectgarage">
        <div class="objectDescriptionItemLabel">
            Кабельное ТВ:
        </div>
        <div class="objectDescriptionBody">
            <select name="cableTV">
                <option value="0" selected></option>
                <option value="nono">не проведено, нельзя провести</option>
                <option value="no">не проведено, можно провести</option>
                <option value="yes">проведено, можно использовать</option>
            </select>
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="furniture">
<div class="advertDescriptionChapterHeader">
    Мебель и бытовая техника
</div>
<div class="objectDescriptionItem nottypeOfObjectgarage">
    <div class="objectDescriptionItemLabel">
        Мебель в жилой зоне:
    </div>
    <div class="objectDescriptionBody">
        <ul>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="sofa"> диван раскладной
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="sofaNeraskladnoy"> диван нераскладной
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="singleBed"> кровать одноместная
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="doubleBed"> кровать двухместная
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="babyBed"> кровать детская
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="writingDesk"> стол письменный
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="computerTable"> стол компьютерный
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="coffeeTable"> стол журнальный
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="foldingTable"> стол раскладной
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="foldingChair"> кресло раскладное
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="nonFoldingChair"> кресло нераскладное
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="chairsAndStools"> стулья и табуретки
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="wall"> стенка
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="wardrobe"> шкаф для одежды
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="ShkafKupe"> шкаф-купе
        </li>
        <li>
            <input type="checkbox" name="furnitureInLivingArea[]" value="komod"> комод
        </li>
        <li>
            <input type="text" name="furnitureInLivingAreaExtra" value="">
        </li>
            </ul>
    </div>
</div>
<div class="objectDescriptionItem nottypeOfObjectgarage">
    <div class="objectDescriptionItemLabel">
        Мебель на кухне:
    </div>
    <div class="objectDescriptionBody">
        <ul>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="diningTable"> стол обеденный
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="chairsAndStools"> стулья, табуретки
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="sofa"> диван
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="kitchenSet"> кухонный гарнитур
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="mountedCabinets"> шкафчики навесные
        </li>
        <li>
            <input type="checkbox" name="furnitureInKitchen[]" value="lockersFloor"> шкафчики напольные
        </li>
        <li>
            <input type="text" name="furnitureInKitchenExtra" value="">
        </li>
            </ul>
    </div>
</div>
<div class="objectDescriptionItem nottypeOfObjectgarage">
    <div class="objectDescriptionItemLabel">
        Бытовая техника:
    </div>
    <div class="objectDescriptionBody">
        <ul>
        <li>
            <input type="checkbox" name="appliances[]" value="refrigerator"> холодильник
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="microwave"> микроволновая печь
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="televisor"> телевизор
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="washingMachineAutomatic"> стиральная машина (автомат)
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="washingMachineNonAutomatic"> стиральная машина (не автомат)
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="waterHeater"> нагреватель воды
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="vacuumCleaner"> пылесос
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="airConditioning"> кондиционер
        </li>
        <li>
            <input type="checkbox" name="appliances[]" value="alarm"> охранная сигнализация
        </li>
        <li>
            <input type="text" name="appliancesExtra" value="">
        </li>
            </ul>
    </div>
</div>
</div>

<div class="advertDescriptionChapter" id="requirementsForTenant">
    <div class="advertDescriptionChapterHeader">
        Требования к арендатору
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Пол:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="sexOfTenant[]" value="man">
            мужчина
            <br>
            <input type="checkbox" name="sexOfTenant[]" value="woman">
            женщина
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Отношения между арендаторами:
        </div>
        <div class="objectDescriptionBody">
            <input type="checkbox" name="relations[]" value="family">
            семейная пара
            <br>
            <input type="checkbox" name="relations[]" value="notFamily">
            несемейная пара
            <br>
            <input type="checkbox" name="relations[]" value="alone">
            один человек
            <br>
            <input type="checkbox" name="relations[]" value="group">
            группа людей
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Дети:
        </div>
        <div class="objectDescriptionBody">
            <select name="children">
                <option value="0" selected></option>
                <option value="any">не имеет значения</option>
                <option value="older4">с детьми старше 4-х лет</option>
                <option value="without">только без детей</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Животные:
        </div>
        <div class="objectDescriptionBody">
            <select name="animals">
                <option value="0" selected></option>
                <option value="any">не имеет значения</option>
                <option value="without">только без животных</option>
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
            <input type="text" name="contactTelephonNumber" size="15" value="">
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Время для звонков:
        </div>
        <div class="objectDescriptionBody">
            с
            <select name="timeForRingBegin">
                <option value="0" selected></option>
                <option value="6">6:00</option>
                <option value="7">7:00</option>
                <option value="8">8:00</option>
                <option value="9">9:00</option>
                <option value="10">10:00</option>
                <option value="11">11:00</option>
                <option value="12">12:00</option>
                <option value="13">13:00</option>
                <option value="14">14:00</option>
                <option value="15">15:00</option>
                <option value="16">16:00</option>
                <option value="17">17:00</option>
                <option value="18">18:00</option>
                <option value="19">19:00</option>
                <option value="20">20:00</option>
                <option value="21">21:00</option>
                <option value="22">22:00</option>
                <option value="23">23:00</option>
                <option value="24">24:00</option>
            </select>
            до
            <select name="timeForRingEnd">
                <option value="0" selected></option>
                <option value="6">6:00</option>
                <option value="7">7:00</option>
                <option value="8">8:00</option>
                <option value="9">9:00</option>
                <option value="10">10:00</option>
                <option value="11">11:00</option>
                <option value="12">12:00</option>
                <option value="13">13:00</option>
                <option value="14">14:00</option>
                <option value="15">15:00</option>
                <option value="16">16:00</option>
                <option value="17">17:00</option>
                <option value="18">18:00</option>
                <option value="19">19:00</option>
                <option value="20">20:00</option>
                <option value="21">21:00</option>
                <option value="22">22:00</option>
                <option value="23">23:00</option>
                <option value="24">24:00</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Как часто собственник проверяет сдаваемую недвижимость:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <select name="checking">
                <option value="0" selected></option>
                <option value="never">Никогда (проживает в другом городе)</option>
                <option value="1">1 раз в месяц (при получении оплаты)</option>
                <option value="more1">Периодически (чаще 1 раза в месяц)</option>
                <option value="constantly">Постоянно (проживает в этой же квартире)</option>
            </select>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Какую ответственность за состояние и ремонт объекта берет на себя собственник:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="responsibility" maxlength="2000" rows="7" cols="43"></textarea>
        </div>
    </div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel">
            Важные моменты, касающиеся сдаваемого объекта, которые не были указаны в форме выше:
        </div>
        <div class="objectDescriptionBody" style="min-width: 330px">
            <textarea name="comment" maxlength="2000" rows="7" cols="43"></textarea>
            Например в title можно указать: какое половое покрытие на объекте, если окна не дерево и не пластик или есть и те и другие, то здесь указать
        </div>
    </div>
</div>

<div class="advertDescriptionChapter" id="submitAdvertButton">
    <div class="advertDescriptionChapterHeader"></div>
    <div class="objectDescriptionItem">
        <div class="objectDescriptionItemLabel"></div>
        <div class="objectDescriptionBody">
            <div class="bottomButton">
                <a href="personal.php?tabsId=3" style="margin-right: 10px;">Отмена</a>
                <button type="submit" name="saveProfileParameters" id="saveAdvertButton" class="button">
                    Сохранить
                </button>
            </div>
            </div>
    </div>
</div>
</form>
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

<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

<!-- jQuery UI с моей темой оформления -->
<script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>

<!-- Русификатор виджета календарь -->
<script src="js/vendor/jquery.ui.datepicker-ru.js"></script>

<!-- Загрузчик фотографий на AJAX -->
<script src="js/vendor/fileuploader.js" type="text/javascript"></script>

<!-- Загружаем библиотеку для работы с картой от Яндекса -->
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

<!-- scripts concatenated and minified via build script -->
<script src="js/main.js"></script>
<script src="js/newadvert.js"></script>

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
