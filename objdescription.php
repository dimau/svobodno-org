<?php
    include_once 'lib/connect.php'; //подключаемся к БД
    include_once 'lib/function_global.php'; //подключаем файл с глобальными функциями

    /*************************************************************************************
     * Если в строке не указан идентификатор объявления, то пересылаем пользователя на спец. страницу
     ************************************************************************************/
    $propertyId = "0";
    if (isset($_GET['propertyId'])) {
        $propertyId = $_GET['propertyId']; // Получаем идентификатор объявления для показа из строки запроса
    } else {
        header('Location: 404.html'); // Если в запросе не указан идентификатор объявления для редактирования, то пересылаем пользователя в личный кабинет к списку его объявлений
    }

    /*************************************************************************************
     * Получаем данные объявления для просмотра, а также другие данные из БД
     ************************************************************************************/

    // Получаем информацию о нужном объекте недвижимости
    $rezProperty = mysql_query("SELECT * FROM property WHERE id = '" . $propertyId . "'");
    $rowProperty = mysql_fetch_assoc($rezProperty);

    // Получаем информацию о фотографиях объекта недвижимости пользователя
    // Массив $rowPropertyFotosArr представляет собой массив массивов, каждый из которых содержит информацию об одной фотографии объекта недвижимости
    $rezPropertyFotos = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $propertyId . "'");
    $rowPropertyFotosArr = array();
    for ($i = 0; $i < mysql_num_rows($rezPropertyFotos); $i++) {
        $rowPropertyFotosArr[] = mysql_fetch_assoc($rezPropertyFotos);
    }

    // Получаем информацию о собственнике недвижимости
    $rezOwner = mysql_query("SELECT id, name, secondName, telephon FROM users WHERE id = '" . $rowProperty['userId'] . "'");
    $rowOwner = mysql_fetch_assoc($rezOwner);

    /*************************************************************************************
     * Проверяем - может ли данный пользователь просматривать данное объявление
     ************************************************************************************/

    // Проверяем авторизованность пользователя и, если он авторизован, то получаем его id
    $userId = login();

    // Если объявление опубликовано, то его может просматривать каждый
    // Если объявление закрыто (снято с публикации), то его может просматривать только сам собственник
    if ($rowProperty['status'] == "не опубликовано" && $rowProperty['userId'] != $userId) header('Location: 404.html');
    //TODO: реализовать соответствующую 404 страницу

    /*************************************************************************************
     * Получаем заголовок страницы
     ************************************************************************************/
    $strHeaderOfPage = getFirstCharUpper($rowProperty['typeOfObject']) . " по адресу: " . $rowProperty['address'];

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
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Особые стили для блоков с описанием объекта - для выравнивания*/
        fieldset.notEdited {
            min-width: 45%;
        }

        #showContacts {
            color: #1A238B;
            text-decoration: underline;
            cursor: pointer;
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
    <!-- Загружаем библиотеку для работы с картой от Яндекса -->
    <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

</head>

<body>
<div class="page_without_footer">
<!-- Сформируем и вставим заголовок страницы -->
<?php
    include("header.php");
?>

<div class="page_main_content">

<div class="headerOfPageContentBlock">
    <div class="headerOfPage">
        <?php echo $strHeaderOfPage; ?>
    </div>
    <div class="importantAddInfBlock">
        <ul>
            <li>
                <button>Записаться на просмотр</button>
            </li>
            <li>
                <div class="blockOfIcon">
                    <a><img class="icon" src="img/blue_star.png"></a>
                </div>
                <a id="addToFavorit"> добавить в избранное</a>
            </li>
            <li>
                <a href="#"> отправить по e-mail</a>
            </li>
            <li>
                <a href="#"> показать похожие объявления</a>
            </li>
        </ul>
    </div>
    <div class="clearBoth"></div>
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
    <!-- Подробное описание объекта -->
    <div>
        <?php
        foreach ($rowPropertyFotosArr as $value) {
            $strUrl = "uploaded_files/" . $value['id'] . "." . $value['extension'];
            echo "<div class='bigFotoWrapper'><img src='" . $strUrl . "' class='bigFoto'></div>";
        }
        ?>
    </div>
    <div class="objectDescription">
        <fieldset class="notEdited">
            <legend>
                Комнаты и помещения
            </legend>
            <table>
                <tbody>
                    <?php
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Количество комнат в квартире, доме:</td><td class='objectDescriptionBody'><span>" . $rowProperty['amountOfRooms'] . "</span></td></tr>";
                    if ($rowProperty['amountOfRooms'] != "0" && $rowProperty['amountOfRooms'] != "1") echo "<tr><td class='objectDescriptionItemLabel'>Комнаты смежные:</td><td class='objectDescriptionBody'><span>" . $rowProperty['adjacentRooms'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "комната" && $rowProperty['typeOfObject'] != "гараж" && $rowProperty['adjacentRooms'] != "0" && $rowProperty['adjacentRooms'] != "нет" && $rowProperty['amountOfRooms'] != "0" && $rowProperty['amountOfRooms'] != "1" && $rowProperty['amountOfRooms'] != "2") echo "<tr><td class='objectDescriptionItemLabel'>Количество смежных комнат:</td><td class='objectDescriptionBody'><span>" . $rowProperty['amountOfAdjacentRooms'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Санузел:</td><td class='objectDescriptionBody'><span>" . $rowProperty['typeOfBathrooms'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Балкон/лоджия:</td><td class='objectDescriptionBody'><span>" . $rowProperty['typeOfBalcony'] . "</span></td></tr>";
                    if ($rowProperty['typeOfBalcony'] != "0" && $rowProperty['typeOfBalcony'] != "нет" && $rowProperty['typeOfBalcony'] != "эркер" && $rowProperty['typeOfBalcony'] != "2 эркера и более") echo "<tr><td class='objectDescriptionItemLabel'>Остекление балкона/лоджии:</td><td class='objectDescriptionBody'><span>" . $rowProperty['balconyGlazed'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "квартира" && $rowProperty['typeOfObject'] != "дом" && $rowProperty['typeOfObject'] != "таунхаус" && $rowProperty['typeOfObject'] != "дача" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Площадь комнаты:</td><td class='objectDescriptionBody'><span>" . $rowProperty['roomSpace'] . " м²</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "комната") echo "<tr><td class='objectDescriptionItemLabel'>Площадь общая:</td><td class='objectDescriptionBody'><span>" . $rowProperty['totalArea'] . " м²</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "комната" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Площадь жилая:</td><td class='objectDescriptionBody'><span>" . $rowProperty['livingSpace'] . " м²</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "дача" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Площадь кухни:</td><td class='objectDescriptionBody'><span>" . $rowProperty['kitchenSpace'] . " м²</span></td></tr>";
                    ?>
                </tbody>
            </table>
        </fieldset>

        <fieldset class="notEdited">
            <legend>
                Стоимость, условия оплаты
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Плата за аренду:</td>
                        <td class="objectDescriptionBody"><?php echo "<span>" . $rowProperty['costOfRenting'] . "</span>" . " " . $rowProperty['currency'] . " в месяц" ?></td>
                    </tr>
                    <tr title="Выплачивается собственнику при заключении договора аренды">
                        <td class="objectDescriptionItemLabel">Единовременная комиссия:</td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['compensationMoney'] . " " . $rowProperty['currency'] . " (" . $rowProperty['compensationPercent'] . "%)" ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Коммунальные услуги<br>оплачиваются
                            дополнительно:
                        </td>
                        <td class="objectDescriptionBody"><?php if ($rowProperty['utilities'] == "да") echo "<span>" . $rowProperty['utilities'] . ", от " . $rowProperty['costInSummer'] . " до " . $rowProperty['costInWinter'] . " " . $rowProperty['currency'] . "</span>"; else echo "<span>" . $rowProperty['utilities'] . "</span>"; ?></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Электроэнергия<br>оплачивается
                            дополнительно:
                        </td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['electricPower'] ?></span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Залог:</td>
                        <td class="objectDescriptionBody">
                            <span><?php if ($rowProperty['bail'] == "есть") echo $rowProperty['bailCost'] . " " . $rowProperty['currency']; else echo "нет"; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Предоплата:</td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['prepayment']; ?></span></td>
                    </tr>
                </tbody>
            </table>
        </fieldset>

        <?php if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж"): ?>
        <fieldset class="notEdited">
            <legend>
                Этаж и подъезд
            </legend>
            <table>
                <tbody>
                    <?php
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "дом" && $rowProperty['typeOfObject'] != "таунхаус" && $rowProperty['typeOfObject'] != "дача" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Этаж:</td><td class='objectDescriptionBody'><span>" . $rowProperty['floor'] . " из " . $rowProperty['totalAmountFloor'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "квартира" && $rowProperty['typeOfObject'] != "комната" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Этажность дома:</td><td class='objectDescriptionBody'><span>" . $rowProperty['numberOfFloor'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "дом" && $rowProperty['typeOfObject'] != "таунхаус" && $rowProperty['typeOfObject'] != "дача" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Консьерж:</td><td class='objectDescriptionBody'><span>" . $rowProperty['concierge'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "дача" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Домофон:</td><td class='objectDescriptionBody'><span>" . $rowProperty['intercom'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "дача" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Парковка во дворе:</td><td class='objectDescriptionBody'><span>" . $rowProperty['parking'] . "</span></td></tr>";
                    ?>
                </tbody>
            </table>
        </fieldset>
        <?php endif; ?>

        <?php if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж"): ?>
        <fieldset class="notEdited">
            <legend>
                Текущее состояние
            </legend>
            <table>
                <tbody>
                    <?php
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Ремонт:</td><td class='objectDescriptionBody'><span>" . $rowProperty['repair'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Отделка:</td><td class='objectDescriptionBody'><span>" . $rowProperty['furnish'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Окна:</td><td class='objectDescriptionBody'><span>" . $rowProperty['windows'] . "</span></td></tr>";
                    ?>
                </tbody>
            </table>
        </fieldset>
        <?php endif; ?>

        <fieldset class="notEdited">
            <legend>
                Тип и сроки
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Тип объекта:</td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['typeOfObject']; ?></span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">С какого числа можно въезжать:</td>
                        <td class="objectDescriptionBody">
                            <span><?php echo dateFromDBToView($rowProperty['dateOfEntry']); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">На какой срок сдается:</td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['termOfLease']; ?></span></td>
                    </tr>
                    <?php
                    if ($rowProperty['termOfLease'] != "0" && $rowProperty['termOfLease'] != "длительный срок") echo "<tr><td class='objectDescriptionItemLabel'>Крайний срок выезда арендатора(ов):</td><td class='objectDescriptionBody'><span>" . dateFromDBToView($rowProperty['dateOfCheckOut']) . "</span></td></tr>";
                    ?>
                </tbody>
            </table>
        </fieldset>

        <?php if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж"): ?>
        <fieldset class="notEdited">
            <legend>
                Связь
            </legend>
            <table>
                <tbody>
                    <?php
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Интернет:</td><td class='objectDescriptionBody'><span>" . $rowProperty['internet'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Телефон:</td><td class='objectDescriptionBody'><span>" . $rowProperty['telephoneLine'] . "</span></td></tr>";
                    if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж") echo "<tr><td class='objectDescriptionItemLabel'>Кабельное ТВ:</td><td class='objectDescriptionBody'><span>" . $rowProperty['cableTV'] . "</span></td></tr>";
                    ?>
                </tbody>
            </table>
        </fieldset>
        <?php endif; ?>

        <?php if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж"): ?>
        <fieldset class="notEdited">
            <legend>
                Мебель и бытовая техника
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Мебель в жилой зоне:</td>
                        <td class="objectDescriptionBody"><span>
                                                    <?php
                            $furniture = array(); // Инициализируем переменную для хранения списка мебели
                            // Скидываем в массив $furniture всю мебель, которую собственник отметил галочками
                            $furnitureInLivingAreaArr = unserialize($rowProperty['furnitureInLivingArea']);
                            foreach ($furnitureInLivingAreaArr as $value) {
                                $furniture[] = $value;
                            }

                            // Скидываем в массив $furniture всю мебель, которую собственник добавил вручную
                            $furnitureInLivingAreaExtraArr = explode(', ', $rowProperty['furnitureInLivingAreaExtra']);
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
                            $furnitureInKitchenArr = unserialize($rowProperty['furnitureInKitchen']);
                            foreach ($furnitureInKitchenArr as $value) {
                                $furniture[] = $value;
                            }

                            // Скидываем в массив $furniture всю мебель, которую собственник добавил вручную
                            $furnitureInKitchenExtraArr = explode(', ', $rowProperty['furnitureInKitchenExtra']);
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
                            $appliancesArr = unserialize($rowProperty['appliances']);
                            foreach ($appliancesArr as $value) {
                                $furniture[] = $value;
                            }

                            // Скидываем в массив $furniture всю бытовую технику, которую собственник добавил вручную
                            $appliancesExtraArr = explode(', ', $rowProperty['appliancesExtra']);
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
        </fieldset>
        <?php endif; ?>

        <?php if ($rowProperty['typeOfObject'] != "0" && $rowProperty['typeOfObject'] != "гараж"): ?>
        <fieldset class="notEdited">
            <legend>
                Требования к арендатору
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Пол:</td>
                        <td class="objectDescriptionBody"><span>
                                                    <?php
                            $sexOfTenantArr = explode("_", $rowProperty['sexOfTenant']);

                            // Если собственник указал только один пол в качестве предпочтительного, то выводим его на страницу
                            if (count($sexOfTenantArr) == 1) echo $sexOfTenantArr[0];

                            // Если указаны оба пола - пишем фразу "не имеет значения"
                            if (count($sexOfTenantArr) == 2) echo "не имеет значения";
                            ?>
												</span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Отношения между арендаторами:</td>
                        <td class="objectDescriptionBody"><span>
                                                    <?php
                            $relations = explode("_", $rowProperty['relations']);
                            for ($i = 0; $i < count($relations); $i++) {
                                echo $relations[$i];
                                if ($i < count($relations) - 1) echo ",<br>"; // Если элемент в массиве не последний - добавляем запятую
                            }
                            ?>
                                                </span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Дети:</td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['children']; ?></span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Животные:</td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['animals']; ?></span></td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
        <?php endif; ?>

        <fieldset class="notEdited">
            <legend>
                Особые условия
            </legend>
            <table>
                <tbody>
                    <tr>
                        <td class="objectDescriptionItemLabel">Как часто собственник проверяет
                            недвижимость:
                        </td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['checking']; ?></span></td>
                    </tr>
                    <tr>
                        <td class="objectDescriptionItemLabel">Ответственность за состояние и ремонт
                            недвижимости:
                        </td>
                        <td class="objectDescriptionBody">
                            <span><?php echo $rowProperty['responsibility']; ?></span></td>
                    </tr>
                    <?php
                    if ($rowProperty['comment'] != "") echo "<tr><td class='objectDescriptionItemLabel'>Дополнительный комментарий:</td><td class='objectDescriptionBody'><span>" . $rowProperty['comment'] . "</span></td></tr>";
                    ?>
                </tbody>
            </table>
        </fieldset>

        <?php if ($rowProperty['schemeOfWork'] == 'улучшенный' || $rowProperty['schemeOfWork'] == 'оптимальный'
    ):
        /**********************************************************************
         * Контакты собственника
         * Выводим информацию о контактах собственника в соответствии с текущим состоянием пользователя
         * Схема работы = улучшенная или оптимальная
         *      Пользователь НЕ зарегистрирован
         *      Пользователь зарегистрирован
         *          Пользователь НЕ является арендатором
         *          Пользователь является арендатором
         *              Пользователь уже получал доступ к контактам собственника данного объявления
         *              Пользователь ЕЩЕ НЕ получал доступ к контактам собственника данного объявления (но имеет  на это полное право)
         * Схема работы = классическая
         *       Пользователь НЕ зарегистрирован
         *       Пользователь зарегистрирован
         *          Заявка на просмотр ЕЩЕ НЕ отправлялась
         *          Заявка на просмотр уже была отправлена
         *              Заявка отправлялась, но еще не была рассмотрена
         *              Заявка отправлялась и уже была рассмотрена
         *********************************************************************/
        ?>
        <fieldset class="notEdited">
            <legend>
                Контакты собственника
            </legend>
            <table>
                <tbody id="ownerContactsContainer">

                    <?php if (!$userId): // Если пользователь не зарегистрирован ?>
                    <tr>
                        <td class="objectDescriptionItemLabel">Для получения контактов необходимо
                            <a href="login.php">войти</a> или <a
                                href="registration.php?typeTenant=true">зарегистрироваться</a></td>
                    </tr>
                    <?php endif; ?>

                    <?php if ($userId
                ): // Если пользователь зарегистрирован
                    // Получаем информацию о пользователе, чтобы принять решение об отображении контактов пользователя
                    $rez1 = mysql_query("SELECT typeTenant FROM users WHERE id = '" . $userId . "'");
                    $rez2 = mysql_query("SELECT interestingPropertysId FROM searchRequests WHERE userId = '" . $userId . "'");
                    if ($rez1 != FALSE) $row1 = mysql_fetch_assoc($rez1); else $row1 = FALSE;
                    if ($rez2 != FALSE) $row2 = mysql_fetch_assoc($rez2); else $row2 = FALSE;?>
                    <?php if ($row1 == FALSE || $row2 == FALSE || $row1['typeTenant'] != "true"): // Если пользователь зарегистрирован, но он не является арендатором ?>
                    <tr>
                        <td class="objectDescriptionBody">Для получения контактов укажите свои <a
                            href="personal.php?tabsId=4">условия
                            поиска</a></td>
                    </tr>
                    <?php endif; ?>

                    <?php if ($row1 != FALSE && $row2 != FALSE && $row1['typeTenant'] == "true"): // Если пользователь зарегистрирован и является арендатором ?>
                    <?php if (in_array($propertyId, unserialize($row2['interestingPropertysId']))): // Проверяем, получал ли ранее этот арендатор доступ к контактам собственника данного объявления ?>
                        <tr>
                            <td class="objectDescriptionItemLabel">Имя:</td>
                            <td class="objectDescriptionBody"><a
                                href="man.php?compId=<?php echo $rowOwner['id'] * 5 + 2; ?>"><?php echo $rowOwner['name'] . " " . $rowOwner['secondName']; ?></a>
                            </td>
                        </tr>
                        <tr>
                            <td class="objectDescriptionItemLabel">Телефон:</td>
                            <td class="objectDescriptionBody"><?php echo $rowOwner['telephon']; ?></td>
                        </tr>
                        <?php else: // Если арендатор еще не получал доступ к контактам по данному объявлению ?>
                        <tr>
                            <td class="objectDescriptionBody"><span id="showContacts">Показать</span>
                            </td>
                        </tr>
                        <?php endif; // Конец обработки, если пользователь получал / не получал ранее доступ к контактам собственника по данному объявлению ?>
                    <?php endif; // Конец обработки, если пользователь зарегистрирован и является арендатором ?>
                    <?php endif; // Конец обработки, если пользователь зарегистрирован ?>

                </tbody>
            </table>
        </fieldset>

        <?php endif; // Конец обработки, если схема работы по данному объявлению = улучшенный или оптимальный ?>
        <?php if ($rowProperty['schemeOfWork'] == 'улучшенный' || $rowProperty['schemeOfWork'] == 'оптимальный'
    ):
        /**********************************************************************
         * Контакты посредника
         * Выводим информацию о том, как связаться с посредником (мои сотрудники) в соответствии с текущим состоянием пользователя
         *
         * Схема работы = классическая
         *       Пользователь НЕ зарегистрирован
         *       Пользователь зарегистрирован
         *          Заявка на просмотр ЕЩЕ НЕ отправлялась
         *          Заявка на просмотр уже была отправлена
         *              Заявка отправлялась, но еще не была рассмотрена
         *              Заявка отправлялась и уже была рассмотрена
         *********************************************************************/
        ?>
        <fieldset class="notEdited">
            <legend>
                Заявка на просмотр
            </legend>
            <table>
                <tbody id="ownerContactsContainer">

                    <?php if (!$userId): // Если пользователь не зарегистрирован ?>
                    <tr>
                        <td class="objectDescriptionItemLabel"><a href="#">Заполнить и отправить</a></td>
                    </tr>
                    <?php endif; ?>

                    <?php if ($userId
                ): // Если пользователь зарегистрирован
                    // Получаем информацию о пользователе, чтобы принять решение об отображении контактов пользователя
                    $rez1 = mysql_query("SELECT typeTenant FROM users WHERE id = '" . $userId . "'");
                    $rez2 = mysql_query("SELECT interestingPropertysId FROM searchRequests WHERE userId = '" . $userId . "'");
                    if ($rez1 != FALSE) $row1 = mysql_fetch_assoc($rez1); else $row1 = FALSE;
                    if ($rez2 != FALSE) $row2 = mysql_fetch_assoc($rez2); else $row2 = FALSE;?>
                    <?php if ($row1 == FALSE || $row2 == FALSE || $row1['typeTenant'] != "true"): // Если пользователь зарегистрирован, но он не является арендатором ?>
                    <tr>
                        <td class="objectDescriptionBody">Для получения контактов укажите свои <a
                            href="personal.php?tabsId=4">условия
                            поиска</a></td>
                    </tr>
                    <?php endif; ?>

                    <?php if ($row1 != FALSE && $row2 != FALSE && $row1['typeTenant'] == "true"): // Если пользователь зарегистрирован и является арендатором ?>
                    <?php if (in_array($propertyId, unserialize($row2['interestingPropertysId']))): // Проверяем, получал ли ранее этот арендатор доступ к контактам собственника данного объявления ?>
                        <tr>
                            <td class="objectDescriptionItemLabel">Имя:</td>
                            <td class="objectDescriptionBody"><a
                                href="man.php?compId=<?php echo $rowOwner['id'] * 5 + 2; ?>"><?php echo $rowOwner['name'] . " " . $rowOwner['secondName']; ?></a>
                            </td>
                        </tr>
                        <tr>
                            <td class="objectDescriptionItemLabel">Телефон:</td>
                            <td class="objectDescriptionBody"><?php echo $rowOwner['telephon']; ?></td>
                        </tr>
                        <?php else: // Если арендатор еще не получал доступ к контактам по данному объявлению ?>
                        <tr>
                            <td class="objectDescriptionBody"><span id="showContacts">Показать</span>
                            </td>
                        </tr>
                        <?php endif; // Конец обработки, если пользователь получал / не получал ранее доступ к контактам собственника по данному объявлению ?>
                    <?php endif; // Конец обработки, если пользователь зарегистрирован и является арендатором ?>
                    <?php endif; // Конец обработки, если пользователь зарегистрирован ?>

                </tbody>
            </table>
        </fieldset>

        <?php endif; // Конец обработки, если схема работы по данному объявлению = улучшенный или оптимальный ?>
    </div>
    <a href="search.php">Найти похожие объявления</a>
</div>
<div id="tabs-2">
    <!-- Описание метоположения объекта -->

    <fieldset class="notEdited" style="float: left; margin: 0 20px 20px 0;">
        <input type="hidden" name="coordX"
               id="coordX" <?php echo "value='" . $rowProperty['coordX'] . "'";?>>
        <input type="hidden" name="coordY"
               id="coordY" <?php echo "value='" . $rowProperty['coordY'] . "'";?>>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Город:</td>
                    <td class="objectDescriptionBody"><span><?php echo $rowProperty['city'];?></span>
                    </td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Район:</td>
                    <td class="objectDescriptionBody"><span>
                                                <?php
                        if (isset($rowProperty['district'])) echo $rowProperty['district'];
                        ?>
                                            </span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Адрес:</td>
                    <td class="objectDescriptionBody"><span><?php echo $rowProperty['address'];?></span>
                    </td>
                </tr>
                <?php
                if ($rowProperty['subwayStation'] != "0" && $rowProperty['subwayStation'] != "нет") echo "<tr><td class='objectDescriptionItemLabel'>Станция метро рядом:</td><td class='objectDescriptionBody'><span>" . $rowProperty['subwayStation'] . ",<br>" . $rowProperty['distanceToMetroStation'] . " мин. ходьбы" . "</span></td></tr>";
                ?>
            </tbody>
        </table>
    </fieldset>
    <!-- Карта Яндекса -->
    <div id="mapForAdvertView" style="width: 50%; min-width: 300px; height: 400px; float: left;"></div>
    <a href="search.php">Найти похожие объявления</a>

    <div class="clearBoth"></div>
</div>
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

    $("#showContacts").click(function () {
        jQuery.post("lib/returnOwnerContacts.php", {"propertyId": <?php echo $propertyId ?>}, function (data) {
            $(data).find("span[access='successful']").each(function () {
                strHTML = "<tr><td class='objectDescriptionItemLabel'>Имя:</td><td class='objectDescriptionBody'><a href='man.php?compId=";
                strHTML += $(this).attr("id") + "'> " + $(this).attr("name") + " " + $(this).attr("secondName") + "</a></td></tr>";

                strHTML += "<tr><td class='objectDescriptionItemLabel'>Телефон:</td><td class='objectDescriptionBody'>";
                strHTML += $(this).attr("telephon") + "</td></tr>";

                $("#ownerContactsContainer").html(strHTML);
            });
            $(data).find("span[access='denied']").each(function () {
                strHTML = "<tr><td class='objectDescriptionBody'>К сожалению, получить данные не удалось</td></tr>";
                $("#ownerContactsContainer").html(strHTML);
            });
        }, "xml");
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
