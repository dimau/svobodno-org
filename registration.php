<?php
    session_start();
    include_once 'lib/connect.php'; //подключаемся к БД
    include_once 'lib/function_global.php'; //подключаем библиотеку функций

    // Проверим, быть может пользователь уже авторизирован. Если это так, перенаправим его на главную страницу сайта
    if (login()) {
        header('Location: index.php');
    }

    // Выясняем роль пользователя - только арендатор, только собственник, или и то и другое.
    if (isset($_GET['typeTenant'])) {
        $typeTenant = "true";
    } else {
        $typeTenant = "false";
    }
    if (isset($_GET['typeOwner'])) {
        $typeOwner = "true";
    } else {
        $typeOwner = "false";
    }
    if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {
        $typeTenant = "true";
        $typeOwner = "true";
    }

    // Присваиваем всем переменным значения по умолчанию
    $correct = NULL; // Инициализируем переменную корректности - нужно для того, чтобы не менять лишний раз идентификатор в input hidden у фотографий

    $name = "";
    $secondName = "";
    $surname = "";
    $sex = "0";
    $nationality = "0";
    $birthday = "";
    $login = "";
    $password = "";
    $telephon = "";
    $email = "";
    $fileUploadId = generateCode(7);

    $currentStatusEducation = "0";
    $almamater = "";
    $speciality = "";
    $kurs = "";
    $ochnoZaochno = "0";
    $yearOfEnd = "";
    $statusWork = "0";
    $placeOfWork = "";
    $workPosition = "";
    $regionOfBorn = "";
    $cityOfBorn = "";
    $shortlyAboutMe = "";

    $vkontakte = "";
    $odnoklassniki = "";
    $facebook = "";
    $twitter = "";

    $typeOfObject = "0";
    $amountOfRooms = array();
    $adjacentRooms = "0";
    $floor = "0";
    $minCost = "";
    $maxCost = "";
    $pledge = "";
    $prepayment = "0";
    $district = array();
    $withWho = "0";
    $linksToFriends = "";
    $children = "0";
    $howManyChildren = "";
    $animals = "0";
    $howManyAnimals = "";
    $termOfLease = "0";
    $additionalDescriptionOfSearch = "";

    $lic = "";

    // Готовим массив со списком районов в городе пользователя
    $allDistrictsInCity = array();
    $rezDistricts = mysql_query("SELECT name FROM districts WHERE city = '" . "Екатеринбург" . "' ORDER BY name ASC");
    for ($i = 0; $i < mysql_num_rows($rezDistricts); $i++) {
        $rowDistricts = mysql_fetch_assoc($rezDistricts);
        $allDistrictsInCity[] = $rowDistricts['name'];
    }

    // Если была нажата кнопка регистрации, проверим данные на корректность и, если данные введены и введены правильно, добавим запись с новым пользователем в БД
    if (isset($_POST['submitButton'])) {

        // Формируем набор переменных для сохранения в базу данных, либо для возвращения вместе с формой при их некорректности
        if (isset($_POST['name'])) $name = htmlspecialchars($_POST['name']);
        if (isset($_POST['secondName'])) $secondName = htmlspecialchars($_POST['secondName']);
        if (isset($_POST['surname'])) $surname = htmlspecialchars($_POST['surname']);
        if (isset($_POST['sex'])) $sex = htmlspecialchars($_POST['sex']);
        if (isset($_POST['nationality'])) $nationality = htmlspecialchars($_POST['nationality']);
        if (isset($_POST['birthday'])) $birthday = htmlspecialchars($_POST['birthday']);
        if (isset($_POST['login'])) $login = htmlspecialchars($_POST['login']);
        if (isset($_POST['password'])) $password = htmlspecialchars($_POST['password']);
        if (isset($_POST['telephon'])) $telephon = htmlspecialchars($_POST['telephon']);
        if (isset($_POST['email'])) $email = htmlspecialchars($_POST['email']);
        $fileUploadId = $_POST['fileUploadId'];

        if (isset($_POST['currentStatusEducation'])) $currentStatusEducation = htmlspecialchars($_POST['currentStatusEducation']);
        if (isset($_POST['almamater'])) $almamater = htmlspecialchars($_POST['almamater']);
        if (isset($_POST['speciality'])) $speciality = htmlspecialchars($_POST['speciality']);
        if (isset($_POST['kurs'])) $kurs = htmlspecialchars($_POST['kurs']);
        if (isset($_POST['ochnoZaochno'])) $ochnoZaochno = htmlspecialchars($_POST['ochnoZaochno']);
        if (isset($_POST['yearOfEnd'])) $yearOfEnd = htmlspecialchars($_POST['yearOfEnd']);
        if (isset($_POST['statusWork'])) $statusWork = htmlspecialchars($_POST['statusWork']);
        if (isset($_POST['placeOfWork'])) $placeOfWork = htmlspecialchars($_POST['placeOfWork']);
        if (isset($_POST['workPosition'])) $workPosition = htmlspecialchars($_POST['workPosition']);
        if (isset($_POST['regionOfBorn'])) $regionOfBorn = htmlspecialchars($_POST['regionOfBorn']);
        if (isset($_POST['cityOfBorn'])) $cityOfBorn = htmlspecialchars($_POST['cityOfBorn']);
        if (isset($_POST['shortlyAboutMe'])) $shortlyAboutMe = htmlspecialchars($_POST['shortlyAboutMe']);

        if (isset($_POST['vkontakte'])) $vkontakte = htmlspecialchars($_POST['vkontakte']);
        if (isset($_POST['odnoklassniki'])) $odnoklassniki = htmlspecialchars($_POST['odnoklassniki']);
        if (isset($_POST['facebook'])) $facebook = htmlspecialchars($_POST['facebook']);
        if (isset($_POST['twitter'])) $twitter = htmlspecialchars($_POST['twitter']);

        if (isset($_POST['typeOfObject'])) $typeOfObject = htmlspecialchars($_POST['typeOfObject']);

        // Обрабатываем массив amountOfRooms: после заполнения формы пользователем, этот массив приходит в виде набора значений (value) тех checkbox, которые выбрал пользователь (это позволяет массив сразу же сохранить в БД с миним. преобразованиями).
        if (isset($_POST['amountOfRooms']) && is_array($_POST['amountOfRooms'])) $amountOfRooms = $_POST['amountOfRooms'];

        if (isset($_POST['district']) && is_array($_POST['district'])) $district = $_POST['district'];
        if (isset($_POST['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
        if (isset($_POST['floor'])) $floor = htmlspecialchars($_POST['floor']);
        if (isset($_POST['minCost'])) $minCost = htmlspecialchars($_POST['minCost']);
        if (isset($_POST['maxCost'])) $maxCost = htmlspecialchars($_POST['maxCost']);
        if (isset($_POST['pledge'])) $pledge = htmlspecialchars($_POST['pledge']);
        if (isset($_POST['prepayment'])) $prepayment = htmlspecialchars($_POST['prepayment']);
        if (isset($_POST['withWho'])) $withWho = htmlspecialchars($_POST['withWho']);
        if (isset($_POST['linksToFriends'])) $linksToFriends = htmlspecialchars($_POST['linksToFriends']);
        if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']);
        if (isset($_POST['howManyChildren'])) $howManyChildren = htmlspecialchars($_POST['howManyChildren']);
        if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']);
        if (isset($_POST['howManyAnimals'])) $howManyAnimals = htmlspecialchars($_POST['howManyAnimals']);
        if (isset($_POST['termOfLease'])) $termOfLease = htmlspecialchars($_POST['termOfLease']);
        if (isset($_POST['additionalDescriptionOfSearch'])) $additionalDescriptionOfSearch = htmlspecialchars($_POST['additionalDescriptionOfSearch']);

        if (isset($_POST['lic'])) $lic = htmlspecialchars($_POST['lic']);

        // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = userDataCorrect("registration");
        if (count($errors) == 0) $correct = TRUE; else $correct = FALSE; // Считаем ошибки, если 0, то можно будет записать данные в БД

        // Если данные, указанные пользователем, корректны, запишем их в базу данных
        if ($correct) {
            // Корректируем дату дня рождения для того, чтобы сделать ее пригодной для сохранения в базу данных
            $birthdayDB = dateFromViewToDB($birthday);
            $tm = time();
            $last_act = $tm;
            $reg_date = $tm;
            // Для простоты технической поддержки пользователей пойдем на небольшой риск с точки зрения безопасности и будем хранить пароли пользователей на сервере в БД без соли и шифрования
            /*$salt = mt_rand(100, 999);
        $password = md5(md5($password) . $salt);*/

            if (mysql_query("INSERT INTO users (typeTenant,typeOwner,name,secondName,surname,sex,nationality,birthday,login,password,telephon,emailReg,email,currentStatusEducation,almamater,speciality,kurs,ochnoZaochno,yearOfEnd,statusWork,placeOfWork,workPosition,regionOfBorn,cityOfBorn,shortlyAboutMe,vkontakte,odnoklassniki,facebook,twitter,lic,last_act,reg_date) VALUES ('" . $typeTenant . "','" . $typeOwner . "','" . $name . "','" . $secondName . "','" . $surname . "','" . $sex . "','" . $nationality . "','" . $birthdayDB . "','" . $login . "','" . $password . "','" . $telephon . "','" . $email . "','" . $email . "','" . $currentStatusEducation . "','" . $almamater . "','" . $speciality . "','" . $kurs . "','" . $ochnoZaochno . "','" . $yearOfEnd . "','" . $statusWork . "','" . $placeOfWork . "','" . $workPosition . "','" . $regionOfBorn . "','" . $cityOfBorn . "','" . $shortlyAboutMe . "','" . $vkontakte . "','" . $odnoklassniki . "','" . $facebook . "','" . $twitter . "','" . $lic . "','" . $last_act . "','" . $reg_date . "')")) // Пишем данные нового пользователя в БД
            {

                /******* Переносим информацию о фотографиях пользователя в таблицу для постоянного хранения *******/
                // Узнаем id пользователя - необходимо при сохранении информации о фотке в постоянную базу
                $rezId = mysql_query("SELECT id FROM users WHERE login = '" . $login . "'");
                $rowId = mysql_fetch_assoc($rezId);
                // Получим информацию о всех фотках, соответствующих текущему fileUploadId
                $rezTempFotos = mysql_query("SELECT id, filename, extension, filesizeMb FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");
                for ($i = 0; $i < mysql_num_rows($rezTempFotos); $i++) {
                    $rowTempFotos = mysql_fetch_assoc($rezTempFotos);
                    mysql_query("INSERT INTO userFotos (id, filename, extension, filesizeMb, userId) VALUES ('" . $rowTempFotos['id'] . "','" . $rowTempFotos['filename'] . "','" . $rowTempFotos['extension'] . "','" . $rowTempFotos['filesizeMb'] . "','" . $rowId['id'] . "')"); // Переносим информацию о фотографиях на постоянное хранение
                }
                // Удаляем записи о фотках в таблице для временного хранения данных
                mysql_query("DELETE FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");


                /******* Сохраняем поисковый запрос, если он был указан пользователем *******/

                // Преобразование формата инфы об искомом кол-ве комнат и районах, так как MySQL не умеет хранить массивы
                $amountOfRoomsSerialized = serialize($amountOfRooms);
                $districtSerialized = serialize($district);

                // Готовим пустой массив с идентификаторами объектов, которыми заинтересовался пользователь - на будущее
                $interestingPropertysId = array();
                $interestingPropertysId = serialize($interestingPropertysId);

                // Непосредственное сохранение данных о поисковом запросе
                if ($typeTenant == "true") {
                    $rez = mysql_query("INSERT INTO searchRequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, minCost, maxCost, pledge, prepayment, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, termOfLease, additionalDescriptionOfSearch, interestingPropertysId) VALUES ('" . $rowId['id'] . "','" . $typeOfObject . "','" . $amountOfRoomsSerialized . "','" . $adjacentRooms . "','" . $floor . "','" . $minCost . "','" . $maxCost . "','" . $pledge . "','" . $prepayment . "','" . $districtSerialized . "','" . $withWho . "','" . $linksToFriends . "','" . $children . "','" . $howManyChildren . "','" . $animals . "','" . $howManyAnimals . "','" . $termOfLease . "','" . $additionalDescriptionOfSearch . "','" . $interestingPropertysId . "')"); // Поисковый запрос пользователя сохраняется в специальной таблице
                }


                /******* Авторизовываем пользователя *******/
                $error = enter();
                if (count($error) == 0) //если нет ошибок, отправляем уже авторизованного пользователя на страницу успешной регистрации
                {
                    header('Location: successfullRegistration.php'); //после успешной регистрации - переходим на соответствующую страницу
                } else {
                    // TODO:что-то нужно делать в случае, если возникли ошибки при авторизации во время регистрации - как минимум вывести их текст во всплывающем окошке
                }

            } else {
                $correct = FALSE;

                //TODO: тест
                echo mysql_error();

                $errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку регистрации';
                // Сохранении данных в БД не прошло - пользователь не зарегистрирован
            }
        }
    }
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
                if ($correct == FALSE && isset($errors)) {
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
    include("header.php");
?>

<div class="page_main_content">

<div class="commentForPageBlock" style="position: relative;">
    <div class="headerOfPage" style="position: absolute; bottom: 0px;">
        Зарегистрируйтесь
    </div>
<?php if ($typeTenant == "true"): ?>
    <div class="why"
         style="border-radius: 5px; background-color: #ffffff; padding: 5px; float: right; min-width: 100px; text-align: left; margin-left: 250px; margin-bottom: 10px;">
        <div class="whyHeader">
            Регистрация позволит:
        </div>
        <ul>
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
        </ul>
    </div>
<?php endif; ?>
    <div class="clearBoth"></div>
</div>

<form name="personalInformation" method="post" enctype="multipart/form-data">
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
    <?php if ($typeTenant == "true"): ?>
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
    <div class="descriptionFieldsetsWrapper">
        <fieldset class="edited private">
            <legend>
                ФИО
            </legend>
            <table>
            <tbody>
                <tr>
                    <td class="itemLabel">
                        Фамилия
                    </td>
                    <td class="itemRequired">
                        *
                    </td>
                    <td class="itemBody">
                            <input name="surname" id="surname" type="text" autofocus <?php echo "value='$surname'";?>>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Имя
                    </td>
                    <td class="itemRequired">
                        *
                    </td>
                    <td class="itemBody">
                            <input name="name" id="name" type="text" <?php echo "value='$name'";?>>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Отчество
                    </td>
                    <td class="itemRequired">
                        *
                    </td>
                    <td class="itemBody">
                            <input name="secondName" id="secondName" type="text" <?php echo "value='$secondName'";?>>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Пол
                    </td>
                    <td class="itemRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="sex" id="sex">
                            <option value="0" <?php if ($sex == "0") echo "selected";?>></option>
                            <option value="мужской" <?php if ($sex == "мужской") echo "selected";?>>мужской</option>
                            <option value="женский" <?php if ($sex == "женский") echo "selected";?>>женский</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Внешность
                    </td>
                    <td class="itemRequired">
                        *
                    </td>
                    <td class="itemBody">
                        <select name="nationality" id="nationality">
                            <option value="0" <?php if ($nationality == "0") echo "selected";?>></option>
                            <option
                                value="славянская" <?php if ($nationality == "славянская") echo "selected";?>>
                                славянская
                            </option>
                            <option
                                value="европейская" <?php if ($nationality == "европейская") echo "selected";?>>
                                европейская
                            </option>
                            <option
                                value="азиатская" <?php if ($nationality == "азиатская") echo "selected";?>>
                                азиатская
                            </option>
                            <option
                                value="кавказская" <?php if ($nationality == "кавказская") echo "selected";?>>
                                кавказская
                            </option>
                            <option
                                value="африканская" <?php if ($nationality == "африканская") echo "selected";?>>
                                африканская
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        День рождения
                    </td>
                    <td class="itemRequired">
                        *
                    </td>
                    <td class="itemBody">
                            <input name="birthday" id="birthday" type="text"
                                   placeholder="дд.мм.гггг" <?php echo "value='$birthday'";?>>
                    </td>
                </tr>
            </tbody>
            </table>

        </fieldset>

        <div style="display: inline-block; vertical-align: top;">
            <fieldset class="edited private" style="display: block;">
                <legend>
                    Логин и пароль
                </legend>
                <table>
                    <tbody>
                        <tr title="Используйте в качестве логина ваш e-mail или телефон">
                            <td class="itemLabel">
                                Логин
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input type="text" name="login" id="login" maxlength="50"  placeholder="e-mail или номер телефона" <?php echo "value='$login'";?>>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemLabel">
                                Пароль
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input type="password" name="password" id="password" maxlength="50" <?php echo "value='$password'";?>>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>

            <fieldset class="edited private" style="display: block;">
                <legend>
                    Контакты
                </legend>
                <table>
                    <tbody>
                        <tr>
                            <td class="itemLabel">
                                Телефон
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input type="text" name="telephon" id="telephon" <?php echo "value='$telephon'";?>>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemLabel">
                                E-mail
                            </td>
                            <td class="itemRequired">
                                <?php if ($typeTenant == "true") {
                                echo "*";
                            } ?>
                            </td>
                            <td class="itemBody">
                                <input type="text" name="email" id="email" <?php echo "value='$email'"; ?>>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>

        <fieldset class="edited private" style="min-width: 300px;">
            <legend title="Для успешной регистрации должна быть загружена хотя бы 1 фотография">
                <div class="required">
                    <?php if ($typeTenant == "true") {
                    echo "*";
                } ?>
                </div>
                Фотографии
            </legend>
            <input type="hidden" name="fileUploadId" id="fileUploadId" <?php echo "value='$fileUploadId'";?>>
            <?php
            // Получаем информацию о всех загруженных фото и формируем для каждого свой input type hidden для передачи данных в обработчик яваскрипта
            if ($rez = mysql_query("SELECT * FROM tempFotos WHERE fileuploadid = '" . $fileUploadId . "'")) // ищем уже загруженные пользователем фотки
            {
                $numUploadedFiles = mysql_num_rows($rez);
                for ($i = 0; $i < $numUploadedFiles; $i++) {
                    $row = mysql_fetch_assoc($rez);
                    echo "<input type='hidden' class='uploadedFoto' filename='" . $row['filename'] . "' filesizeMb='" . $row['filesizeMb'] . "'>";
                }
            }
            ?>
            <div id="file-uploader">
                <noscript>
                    <p>Пожалуйста, активируйте JavaScript для загрузки файлов</p>
                    <!-- or put a simple form for upload here -->
                </noscript>
            </div>
        </fieldset>

    </div>
    <!-- /end.descriptionFieldsetsWrapper -->
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
    <fieldset class="edited private">
        <legend>
            Образование
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="itemLabel">
                        Текущий статус
                    </td>
                    <td class="itemRequired">
                        <?php if ($typeTenant == "true") {
                        echo "*";
                    } ?>
                    </td>
                    <td class="itemBody">
                        <select name="currentStatusEducation" id="currentStatusEducation">
                            <option value="0" <?php if ($currentStatusEducation == "0") echo "selected";?>></option>
                            <option
                                value="нет" <?php if ($currentStatusEducation == "нет") echo "selected";?>>
                                Нигде не учился
                            </option>
                            <option
                                value="сейчас учусь" <?php if ($currentStatusEducation == "сейчас учусь") echo "selected";?>>
                                Сейчас учусь
                            </option>
                            <option
                                value="закончил" <?php if ($currentStatusEducation == "закончил") echo "selected";?>>
                                Закончил
                            </option>
                        </select>
                    </td>
                </tr>
                <tr id="almamaterBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет" title="Укажите учебное заведение, в котором учитесь сейчас, либо последнее из тех, что заканчивали">
                    <td class="itemLabel">
                        Учебное заведение
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="almamater" id="almamater" class="ifLearned" <?php echo "value='$almamater'";?>>
                    </td>
                </tr>
                <tr id="specialityBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет">
                    <td class="itemLabel">
                        Специальность
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="speciality" id="speciality" class="ifLearned" <?php echo "value='$speciality'";?>>
                    </td>
                </tr>
                <tr id="kursBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_закончил" title="Укажите курс, на котором учитесь">
                    <td class="itemLabel">
                        Курс
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="kurs" id="kurs" class="ifLearned" <?php echo "value='$kurs'";?>>
                    </td>
                </tr>
                <tr id="formatEducation" notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_закончил" title="Укажите форму обучения">
                    <td class="itemLabel">
                        Очно / Заочно
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <select name="ochnoZaochno" id="ochnoZaochno" class="ifLearned">
                            <option value="0" <?php if ($ochnoZaochno == "0") echo "selected";?>></option>
                            <option value="очно" <?php if ($ochnoZaochno == "очно") echo "selected";?>>Очно</option>
                            <option value="заочно" <?php if ($ochnoZaochno == "заочно") echo "selected";?>>Заочно</option>
                        </select>
                    </td>
                </tr>
                <tr id="yearOfEndBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_сейчас учусь" title="Укажите год окончания учебного заведения">
                    <td class="itemLabel">
                        Год окончания
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="yearOfEnd" id="yearOfEnd" class="ifLearned" <?php echo "value='$yearOfEnd'";?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>


    <fieldset class="edited private">
        <legend>
            Работа
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="itemLabel">
                        Статус занятости
                    </td>
                    <td class="itemRequired">
                        <?php if ($typeTenant == "true") {
                        echo "*";
                    } ?>
                    </td>
                    <td class="itemBody">
                        <select name="statusWork" id="statusWork">
                            <option value="0" <?php if ($statusWork == "0") echo "selected";?>></option>
                            <option value="работаю" <?php if ($statusWork == "работаю") echo "selected";?>>работаю</option>
                            <option value="не работаю" <?php if ($statusWork == "не работаю") echo "selected";?>>не работаю</option>
                        </select>
                    </td>
                </tr>
                <tr notavailability="statusWork_0&statusWork_не работаю">
                    <td class="itemLabel">
                        Место работы
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="placeOfWork" id="placeOfWork" class="ifWorked" <?php echo "value='$placeOfWork'";?>>
                    </td>
                </tr>
                <tr notavailability="statusWork_0&statusWork_не работаю">
                    <td class="itemLabel">
                        Должность
                    </td>
                    <td class="itemRequired typeTenantRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="workPosition" id="workPosition" class="ifWorked" <?php echo "value='$workPosition'";?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>


    <fieldset class="edited private">
        <legend>
            Коротко о себе
        </legend>
        <table>
            <tbody>
                <tr>
                    <td class="itemLabel">
                        В каком регионе родились
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="regionOfBorn" id="regionOfBorn" <?php echo "value='$regionOfBorn'";?>>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Родной город, населенный пункт
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="cityOfBorn" id="cityOfBorn" <?php echo "value='$cityOfBorn'";?>>
                    </td>
                </tr>
                <tr>
                    <td class="itemLabel">
                        Коротко о себе и своих интересах:
                    </td>
                    <td class="itemRequired">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <textarea name="shortlyAboutMe" id="shortlyAboutMe" rows="4"><?php echo $shortlyAboutMe;?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

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
    <fieldset class="edited private social">
        <legend>
            Страницы в социальных сетях
        </legend>
        <table>
            <tbody>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/vkontakte.jpg">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="vkontakte" id="vkontakte" placeholder="http://vk.com/..." <?php echo "value='$vkontakte'";?>>
                    </td>
                </tr>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/odnoklassniki.png">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="odnoklassniki" id="odnoklassniki"
                               placeholder="http://www.odnoklassniki.ru/profile/..." <?php echo "value='$odnoklassniki'";?>>
                    </td>
                </tr>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/facebook.jpg">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="facebook" id="facebook"
                               placeholder="https://www.facebook.com/profile.php?..." <?php echo "value='$facebook'";?>>
                    </td>
                </tr>
                <tr title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                    <td class="itemLabel">
                        <img src="img/twitter.png">
                    </td>
                    <td class="itemRequired">
                    </td>
                    <td class="itemBody">
                        <input type="text" name="twitter" id="twitter"
                               placeholder="https://twitter.com/..." <?php echo "value='$twitter'";?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <?php if ($typeTenant == "true"): ?>
    <div class="bottomControls">
        <button class="backButton">Назад</button>
        <button class="forwardButton">Далее</button>
        <div class="clearBoth"></div>
    </div>
    <?php endif; ?>
    <?php if ($typeTenant != "true"): ?>
    <div class="bottomControls">
        <div style="float: right; margin-bottom: 10px; text-align: left;">
            <input type="checkbox" name="lic" id="lic" value="yes" <?php if ($lic == "yes") echo "checked";?>> Я принимаю условия <a
            href="#">лицензионного соглашения</a>
        </div>
        <div class="clearBoth"></div>
        <button class="backButton">Назад</button>
        <button type="submit" name="submitButton" class="submitButton">Отправить</button>
        <div class="clearBoth"></div>
    </div>
    <?php endif; ?>

</div>
<?php if ($typeTenant == "true"): ?>
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
                                    <option value="0" <?php if ($typeOfObject == "0") echo "selected";?>></option>
                                    <option value="квартира" <?php if ($typeOfObject == "квартира") echo "selected";?>>
                                        квартира
                                    </option>
                                    <option value="комната" <?php if ($typeOfObject == "комната") echo "selected";?>>комната
                                    </option>
                                    <option value="дом" <?php if ($typeOfObject == "дом") echo "selected";?>>дом, коттедж
                                    </option>
                                    <option value="таунхаус" <?php if ($typeOfObject == "таунхаус") echo "selected";?>>
                                        таунхаус
                                    </option>
                                    <option value="дача" <?php if ($typeOfObject == "дача") echo "selected";?>>дача</option>
                                    <option value="гараж" <?php if ($typeOfObject == "гараж") echo "selected";?>>гараж</option>
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
                                    foreach ($amountOfRooms as $value) {
                                        if ($value == "1") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                1
                                <input type="checkbox" value="2"
                                       name="amountOfRooms[]" <?php
                                    foreach ($amountOfRooms as $value) {
                                        if ($value == "2") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                2
                                <input type="checkbox" value="3"
                                       name="amountOfRooms[]" <?php
                                    foreach ($amountOfRooms as $value) {
                                        if ($value == "3") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                3
                                <input type="checkbox" value="4"
                                       name="amountOfRooms[]" <?php
                                    foreach ($amountOfRooms as $value) {
                                        if ($value == "4") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                4
                                <input type="checkbox" value="5"
                                       name="amountOfRooms[]" <?php
                                    foreach ($amountOfRooms as $value) {
                                        if ($value == "5") {
                                            echo "checked";
                                            break;
                                        }
                                    }
                                    ?>>
                                5
                                <input type="checkbox" value="6"
                                       name="amountOfRooms[]" <?php
                                    foreach ($amountOfRooms as $value) {
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
                                    <option value="0" <?php if ($adjacentRooms == "0") echo "selected";?>></option>
                                    <option
                                        value="не имеет значения" <?php if ($adjacentRooms == "не имеет значения") echo "selected";?>>
                                        не
                                        имеет значения
                                    </option>
                                    <option
                                        value="только изолированные" <?php if ($adjacentRooms == "только изолированные") echo "selected";?>>
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
                                    <option value="0" <?php if ($floor == "0") echo "selected";?>></option>
                                    <option value="любой" <?php if ($floor == "любой") echo "selected";?>>любой</option>
                                    <option value="не первый" <?php if ($floor == "не первый") echo "selected";?>>не первый
                                    </option>
                                    <option
                                        value="не первый и не последний" <?php if ($floor == "не первый и не последний") echo "selected";?>>
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
                                       maxlength="8" <?php echo "value='$minCost'";?>>
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
                                       maxlength="8" <?php echo "value='$maxCost'";?>>
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
                                <input type="text" name="pledge" id="pledge" maxlength="8" <?php echo "value='$pledge'";?>>
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
                                    <option value="0" <?php if ($prepayment == "0") echo "selected";?>></option>
                                    <option value="нет" <?php if ($prepayment == "нет") echo "selected";?>>нет</option>
                                    <option value="1 месяц" <?php if ($prepayment == "1 месяц") echo "selected";?>>1 месяц
                                    </option>
                                    <option value="2 месяца" <?php if ($prepayment == "2 месяца") echo "selected";?>>2 месяца
                                    </option>
                                    <option value="3 месяца" <?php if ($prepayment == "3 месяца") echo "selected";?>>3 месяца
                                    </option>
                                    <option value="4 месяца" <?php if ($prepayment == "4 месяца") echo "selected";?>>4 месяца
                                    </option>
                                    <option value="5 месяцев" <?php if ($prepayment == "5 месяцев") echo "selected";?>>5
                                        месяцев
                                    </option>
                                    <option value="6 месяцев" <?php if ($prepayment == "6 месяцев") echo "selected";?>>6
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
                            echo "<li><input type='checkbox' name='district[]' value='" . $value . "'";
                            foreach ($district as $valueDistrict) {
                                if ($valueDistrict == $value) {
                                    echo "checked";
                                    break;
                                }
                            }
                            echo "> " . $value . "</li>";
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
                                <option value="0" <?php if ($withWho == "0") echo "selected";?>></option>
                                <option value="самостоятельно" <?php if ($withWho == "самостоятельно") echo "selected";?>>
                                    самостоятельно
                                </option>
                                <option value="семья" <?php if ($withWho == "семья") echo "selected";?>>семьей
                                </option>
                                <option value="пара" <?php if ($withWho == "пара") echo "selected";?>>парой
                                </option>
                                <option value="2 мальчика" <?php if ($withWho == "2 мальчика") echo "selected";?>>2 мальчика
                                </option>
                                <option value="2 девочки" <?php if ($withWho == "2 девочки") echo "selected";?>>2 девочки
                                </option>
                                <option value="со знакомыми" <?php if ($withWho == "со знакомыми") echo "selected";?>>со
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
                            <textarea name="linksToFriends" id="linksToFriends" rows="3"><?php echo $linksToFriends;?></textarea>
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
                                <option value="0" <?php if ($children == "0") echo "selected";?>></option>
                                <option value="без детей" <?php if ($children == "без детей") echo "selected";?>>без детей
                                </option>
                                <option
                                    value="с детьми младше 4-х лет" <?php if ($children == "с детьми младше 4-х лет") echo "selected";?>>
                                    с детьми
                                    младше 4-х лет
                                </option>
                                <option
                                    value="с детьми старше 4-х лет" <?php if ($children == "с детьми старше 4-х лет") echo "selected";?>>
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
                            <textarea name="howManyChildren" id="howManyChildren" rows="3"><?php echo $howManyChildren;?></textarea>
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
                                <option value="0" <?php if ($animals == "0") echo "selected";?>></option>
                                <option value="без животных" <?php if ($animals == "без животных") echo "selected";?>>без
                                    животных
                                </option>
                                <option value="с животным(ми)" <?php if ($animals == "с животным(ми)") echo "selected";?>>с
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
                            <textarea name="howManyAnimals" id="howManyAnimals" rows="3"><?php echo $howManyAnimals;?></textarea>
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
                                <option value="0" <?php if ($termOfLease == "0") echo "selected";?>></option>
                                <option value="длительный срок" <?php if ($termOfLease == "длительный срок") echo "selected";?>>
                                    длительный срок (от года)
                                </option>
                                <option
                                    value="несколько месяцев" <?php if ($termOfLease == "несколько месяцев") echo "selected";?>>
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
                                      rows="4"><?php echo $additionalDescriptionOfSearch;?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    </div>

    <div class="bottomControls">
        <div style="float: right; margin-bottom: 10px; text-align: left;">
            <input type="checkbox" name="lic" id="lic" value="yes" <?php if ($lic == "yes") echo "checked";?>> Я принимаю условия <a
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

<!-- Добавялем невидимый input для того, чтобы передать тип пользователя (собственник/арендатор) - это используется в JS для простановки обязательности полей для заполнения -->
<?php echo "<input type='hidden' class='userType' typeTenant='" . $typeTenant . "' typeOwner='" . $typeOwner . "'>"; ?>
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

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
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