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
$correct = null; // Инициализируем переменную корректности - нужно для того, чтобы не менять лишний раз идентификатор в input hidden у фотографий

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
$notWorkCheckbox = "";
$placeOfWork = "";
$workPosition = "";
$regionOfBorn = "";
$cityOfBorn = "";
$shortlyAboutMe = "";

$vkontakte = "";
$odnoklassniki = "";
$facebook = "";
$twitter = "";

$typeOfObject = "flat";
$amountOfRooms1 = "";
$amountOfRooms2 = "";
$amountOfRooms3 = "";
$amountOfRooms4 = "";
$amountOfRooms5 = "";
$amountOfRooms6 = "";

$adjacentRooms = "yes";
$floor = "any";
$furniture = "any";
$minCost = "";
$maxCost = "";
$pledge = "";
$district1 = "";
$district2 = "";
$district3 = "";
$district4 = "";
$district5 = "";
$district6 = "";
$district7 = "";
$district8 = "";
$district9 = "";
$district10 = "";
$district11 = "";
$district12 = "";
$district13 = "";
$district14 = "";
$district15 = "";
$district16 = "";
$district17 = "";
$district18 = "";
$district19 = "";
$district20 = "";
$district21 = "";
$district22 = "";
$district23 = "";
$district24 = "";
$district25 = "";
$district26 = "";
$district27 = "";
$district28 = "";
$district29 = "";
$district30 = "";
$district31 = "";
$district32 = "";
$district33 = "";
$district34 = "";
$district35 = "";
$district36 = "";
$district37 = "";
$district38 = "";
$district39 = "";
$district40 = "";
$district41 = "";
$district42 = "";
$district43 = "";
$district44 = "";
$district45 = "";
$district46 = "";

$withWho = "alone";
$linksToFriends = "";
$children = "without";
$howManyChildren = "";
$animals = "without";
$howManyAnimals = "";
$period = "";
$additionalDescriptionOfSearch = "";

$lic = "";

// Если была нажата кнопка регистрации, проверим данные на корректность и, если данные введены и введены правильно, добавим запись с новым пользователем в БД
if (isset($_POST['readyButton'])) {

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
    if (isset($_POST['notWorkCheckbox'])) $notWorkCheckbox = htmlspecialchars($_POST['notWorkCheckbox']);
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

    // Обрабатываем массив amountOfRooms: после заполнения формы пользователем, этот массив приходит в виде набора значений (value) тех checkbox, которые выбрал пользователь (это позволяет массив сразу же сохранить в БД с миним. преобразованиями). Но для того, чтобы в случае ошибки вернуть пользователю заполненные им поля в том же виде, нам нужно разобрать массив на отдельные переменные вида: $amountOfRooms1, 2, 3 и т.д.
    if (isset($_POST['amountOfRooms']) && is_array($_POST['amountOfRooms'])) // Проверяем, передан ли массив значений
    {
        $amountOfRooms = $_POST['amountOfRooms']; // Будем использовать переменную при записи данных в таблицу в виде массива
        foreach ($_POST['amountOfRooms'] as $value) {
            if ($value == "1") $amountOfRooms1 = "1";
            if ($value == "2") $amountOfRooms2 = "2";
            if ($value == "3") $amountOfRooms3 = "3";
            if ($value == "4") $amountOfRooms4 = "4";
            if ($value == "5") $amountOfRooms5 = "5";
            if ($value == "6") $amountOfRooms6 = "6";
        }
    }
    else {
        $amountOfRooms = array("1", "2", "3", "4", "5", "6");
    }

    // Обрабатываем массив district: после заполнения формы пользователем, этот массив приходит в виде набора значений (value) тех checkbox, которые выбрал пользователь. Почему именно такое решение написано выше ( про массив amountOfRooms)
    if (isset($_POST['district']) && is_array($_POST['district'])) // Проверяем, передан ли массив значений
    {
        $district = $_POST['district']; // Будем использовать переменную при записи данных в таблицу в виде массива
        foreach ($_POST['district'] as $value) {
            if ($value == "1") $district1 = "1";
            if ($value == "2") $district2 = "2";
            if ($value == "3") $district3 = "3";
            if ($value == "4") $district4 = "4";
            if ($value == "5") $district5 = "5";
            if ($value == "6") $district6 = "6";
            if ($value == "7") $district7 = "7";
            if ($value == "8") $district8 = "8";
            if ($value == "9") $district9 = "9";
            if ($value == "10") $district10 = "10";
            if ($value == "11") $district11 = "11";
            if ($value == "12") $district12 = "12";
            if ($value == "13") $district13 = "13";
            if ($value == "14") $district14 = "14";
            if ($value == "15") $district15 = "15";
            if ($value == "16") $district16 = "16";
            if ($value == "17") $district17 = "17";
            if ($value == "18") $district18 = "18";
            if ($value == "19") $district19 = "19";
            if ($value == "20") $district20 = "20";
            if ($value == "21") $district21 = "21";
            if ($value == "22") $district22 = "22";
            if ($value == "23") $district23 = "23";
            if ($value == "24") $district24 = "24";
            if ($value == "25") $district25 = "25";
            if ($value == "26") $district26 = "26";
            if ($value == "27") $district27 = "27";
            if ($value == "28") $district28 = "28";
            if ($value == "29") $district29 = "29";
            if ($value == "30") $district30 = "30";
            if ($value == "31") $district31 = "31";
            if ($value == "32") $district32 = "32";
            if ($value == "33") $district33 = "33";
            if ($value == "34") $district34 = "34";
            if ($value == "35") $district35 = "35";
            if ($value == "36") $district36 = "36";
            if ($value == "37") $district37 = "37";
            if ($value == "38") $district38 = "38";
            if ($value == "39") $district39 = "39";
            if ($value == "40") $district40 = "40";
            if ($value == "41") $district41 = "41";
            if ($value == "42") $district42 = "42";
            if ($value == "43") $district43 = "43";
            if ($value == "44") $district44 = "44";
            if ($value == "45") $district45 = "45";
            if ($value == "46") $district46 = "46";
        }
    }
    else {
        $district = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46");
    }

    if (isset($_POST['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_POST['adjacentRooms']);
    if (isset($_POST['floor'])) $floor = htmlspecialchars($_POST['floor']);
    if (isset($_POST['furniture'])) $furniture = htmlspecialchars($_POST['furniture']);
    if (isset($_POST['minCost']) && $_POST['minCost'] != "") $minCost = htmlspecialchars($_POST['minCost']); else $minCost = "0";
    if (isset($_POST['maxCost']) && $_POST['maxCost'] != "") $maxCost = htmlspecialchars($_POST['maxCost']); else $maxCost = "99999999";
    if (isset($_POST['pledge']) && $_POST['pledge'] != "") $pledge = htmlspecialchars($_POST['pledge']); else $pledge = "99999999";

    if (isset($_POST['withWho'])) $withWho = htmlspecialchars($_POST['withWho']);
    if (isset($_POST['linksToFriends'])) $linksToFriends = htmlspecialchars($_POST['linksToFriends']);
    if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']);
    if (isset($_POST['howManyChildren'])) $howManyChildren = htmlspecialchars($_POST['howManyChildren']);
    if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']);
    if (isset($_POST['howManyAnimals'])) $howManyAnimals = htmlspecialchars($_POST['howManyAnimals']);
    if (isset($_POST['period'])) $period = htmlspecialchars($_POST['period']);
    if (isset($_POST['additionalDescriptionOfSearch'])) $additionalDescriptionOfSearch = htmlspecialchars($_POST['additionalDescriptionOfSearch']);

    if (isset($_POST['lic'])) $lic = htmlspecialchars($_POST['lic']);

    // Проверяем корректность данных пользователя. Функции userDataCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
    $errors = userDataCorrect("registration");
    if (count($errors) == 0) $correct = true; else $correct = false; // Считаем ошибки, если 0, то можно будет записать данные в БД

    // Если данные, указанные пользователем, корректны, запишем их в базу данных
    if ($correct) {
        // Корректируем дату дня рождения для того, чтобы сделать ее пригодной для сохранения в базу данных
        $birthday = birthdayFromViewToDB($birthday);

        $salt = mt_rand(100, 999);
        $tm = time();
        $last_act = $tm;
        $reg_date = $tm;
        $password = md5(md5($password) . $salt);

        if (mysql_query("INSERT INTO users (typeTenant,typeOwner,name,secondName,surname,sex,nationality,birthday,login,password,telephon,emailReg,email,currentStatusEducation,almamater,speciality,kurs,ochnoZaochno,yearOfEnd,notWorkCheckbox,placeOfWork,workPosition,regionOfBorn,cityOfBorn,shortlyAboutMe,vkontakte,odnoklassniki,facebook,twitter,lic,salt,last_act,reg_date) VALUES ('" . $typeTenant . "','" . $typeOwner . "','" . $name . "','" . $secondName . "','" . $surname . "','" . $sex . "','" . $nationality . "','" . $birthday . "','" . $login . "','" . $password . "','" . $telephon . "','" . $email . "','" . $email . "','" . $currentStatusEducation . "','" . $almamater . "','" . $speciality . "','" . $kurs . "','" . $ochnoZaochno . "','" . $yearOfEnd . "','" . $notWorkCheckbox . "','" . $placeOfWork . "','" . $workPosition . "','" . $regionOfBorn . "','" . $cityOfBorn . "','" . $shortlyAboutMe . "','" . $vkontakte . "','" . $odnoklassniki . "','" . $facebook . "','" . $twitter . "','" . $lic . "','" . $salt . "','" . $last_act . "','" . $reg_date . "')")) // Пишем данные нового пользователя в БД
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
            // Непосредственное сохранение данных о поисковом запросе
            if ($typeTenant == "true") {
                $rez = mysql_query("INSERT INTO searchRequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, furniture, minCost, maxCost, pledge, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, period, additionalDescriptionOfSearch) VALUES ('" . $rowId['id'] . "','" . $typeOfObject . "','" . $amountOfRoomsSerialized . "','" . $adjacentRooms . "','" . $floor . "','" . $furniture . "','" . $minCost . "','" . $maxCost . "','" . $pledge . "','" . $districtSerialized . "','" . $withWho . "','" . $linksToFriends . "','" . $children . "','" . $howManyChildren . "','" . $animals . "','" . $howManyAnimals . "','" . $period . "','" . $additionalDescriptionOfSearch . "')"); // Поисковый запрос пользователя сохраняется в специальной таблице
            }


            /******* Авторизовываем пользователя *******/
            $error = enter();
            if (count($error) == 0) //если нет ошибок, отправляем уже авторизованного пользователя на страницу успешной регистрации
            {
                header('Location: successfullRegistration.php'); //после успешной регистрации - переходим на соответствующую страницу
            }
            else {
                // TODO:что-то нужно делать в случае, если возникли ошибки при авторизации во время регистрации - как минимум вывести их текст во всплывающем окошке
            }

        }
        else {
            $correct = false;
            $errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку регистрации';
            // Сохранении данных в БД не прошло - пользователь не зарегистрирован
        }
    }
}
?>

<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!-- Consider specifying the language of your content by adding the `lang` attribute to <html> -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" xmlns="http://www.w3.org/1999/html">
<!--<![endif]-->
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
    <!-- Стили для оформления валидационных комментариев к полям при их заполнении -->
    <link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css" media="screen" title="no title"
          charset="utf-8"/>
    <link rel="stylesheet" href="css/main.css">
    <style>
            /* Стили для капчи и для Готово */
        .capcha {
            margin: 10px 10px 0px 0px;
            float: right;
        }

        .readyButton {
            float: right;
            margin: 10px 0px 0px 10px;
        }

            /* Стили для страницы социальных сетей*/
        #tabs-3 .searchItem {
            line-height: 2.8;
        }

        #tabs-3 .searchItemBody {
            margin-left: 10px;
        }

        #tabs-3 .searchItemBody input, #tabs-3 .searchItemBody img {
            vertical-align: middle;
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
                if ($correct == false && isset($errors)) {
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

<div class="wrapperOfTabs">
<form name="personalInformation" method="post" enctype="multipart/form-data">
<div class="headerOfPage">
    Зарегистрируйтесь
</div>

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
        которыми Вы заинтересутесь. Заполните форму на этой и следующих вкладках как можно подробнее.
        <br>
        <span class="required">* </span> - обязательное для заполнения поле
    </div>
    <div class="descriptionFieldsetsWrapper">
        <fieldset class="edited private">
            <legend>
                ФИО
            </legend>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Имя: </span>

                <div class="searchItemBody">
                    <input name="name" type="text" size="38" autofocus
                           validations="validate[required]" <?php echo "value='$name'";?>>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Отчество: </span>

                <div class="searchItemBody">
                    <input name="secondName" type="text" size="33"
                           validations="validate[required]" <?php echo "value='$secondName'";?>>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Фамилия: </span>

                <div class="searchItemBody">
                    <input name="surname" type="text" size="33"
                           validations="validate[required]" <?php echo "value='$surname'";?>>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Пол: </span>

                <div class="searchItemBody">
                    <select name="sex" validations="validate[required]">
                        <option value="0" <?php if ($sex == "0") echo "selected";?>></option>
                        <option value="man" <?php if ($sex == "man") echo "selected";?>>мужской</option>
                        <option value="woman" <?php if ($sex == "woman") echo "selected";?>>женский</option>
                    </select>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Национальность: </span>

                <div class="searchItemBody">
                    <select name="nationality" validations="validate[required]">
                        <option value="0" <?php if ($nationality == "0") echo "selected";?>></option>
                        <option value="russian" <?php if ($nationality == "russian") echo "selected";?>>русский</option>
                        <option value="west" <?php if ($nationality == "west") echo "selected";?>>европеец, американец
                        </option>
                        <option value="east" <?php if ($nationality == "east") echo "selected";?>>СНГ, восточная нац-сть
                        </option>
                    </select>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">День рождения: </span>

                <div class="searchItemBody">
                    <input name="birthday" type="text" id="datepicker" size="15"
                           placeholder="дд.мм.гггг" <?php echo "value='$birthday'";?>>
                </div>
            </div>
        </fieldset>

        <div style="display: inline-block; vertical-align: top;">
            <fieldset class="edited private" style="display: block;">
                <legend>
                    Логин и пароль
                </legend>
                <div class="searchItem" title="Используйте в качестве логина ваш e-mail или телефон">
                    <div class="required">
                        *
                    </div>
                    <span class="searchItemLabel">Логин: </span>

                    <div class="searchItemBody">
                        <input type="text" size="30" maxlength="50" name="login" placeholder="e-mail или номер телефона"
                               validations="validate[required]" <?php echo "value='$login'";?>>
                    </div>
                </div>
                <div class="searchItem">
                    <div class="required">
                        *
                    </div>
                    <span class="searchItemLabel">Пароль: </span>

                    <div class="searchItemBody">
                        <input type="password" size="29" maxlength="50"
                               name="password" validations="validate[required]" <?php echo "value='$password'";?>>
                    </div>
                </div>
            </fieldset>

            <fieldset class="edited private" style="display: block;">
                <legend>
                    Контакты
                </legend>
                <div class="searchItem">
                    <div class="required">
                        *
                    </div>
                    <span class="searchItemLabel">Телефон: </span>

                    <div class="searchItemBody">
                        <input name="telephon" type="text" size="27"
                               validations="validate[required,custom[telephone]]" <?php echo "value='$telephon'";?>>
                    </div>
                </div>
                <div class="searchItem">
                    <div class="required">
                        <?php if ($typeTenant == "true") {
                        echo "*";
                    } ?>
                    </div>
                    <span class="searchItemLabel">E-mail: </span>

                    <div class="searchItemBody">
                        <input name="email" type="text" size="30" <?php if ($typeTenant == "true") {
                            echo "validations='validate[required,custom[email]]'";
                        } echo "value='$email'"; ?>>
                    </div>
                </div>
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
    <div class="shadowText" style="margin-top: 7px;">
        По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
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
        <div class="searchItem">
            <div class="required">
                <?php if ($typeTenant == "true") {
                echo "*";
            } ?>
            </div>
            <span class="searchItemLabel">Текущий статус: </span>

            <div class="searchItemBody">
                <select name="currentStatusEducation" id="currentStatusEducation" <?php if ($typeTenant == "true") {
                    echo "validations='validate[required]'";
                } ?>>
                    <option value="0" <?php if ($currentStatusEducation == "0") echo "selected";?>></option>
                    <option
                        value="withoutEducation" <?php if ($currentStatusEducation == "withoutEducation") echo "selected";?>>
                        Нигде не учился
                    </option>
                    <option value="learningNow" <?php if ($currentStatusEducation == "learningNow") echo "selected";?>>
                        Сейчас учусь
                    </option>
                    <option
                        value="finishedEducation" <?php if ($currentStatusEducation == "finishedEducation") echo "selected";?>>
                        Закончил
                    </option>
                </select>
            </div>
        </div>
        <div id="almamater" class="searchItem ifLearned"
             title="Укажите учебное заведение, в котором учитесь сейчас, либо последнее из тех, что заканчивали">
            <div class="required">
            </div>
            <span class="searchItemLabel">Учебное заведение: </span>

            <div class="searchItemBody">
                <input name="almamater" class="ifLearned" type="text" size="50" <?php echo "value='$almamater'";?>>
            </div>
        </div>
        <div id="speciality" class="searchItem ifLearned">
            <div class="required">
            </div>
            <span class="searchItemLabel">Специальность: </span>

            <div class="searchItemBody">
                <input name="speciality" class="ifLearned" type="text" size="55" <?php echo "value='$speciality'";?>>
            </div>
        </div>
        <div id="kurs" class="searchItem ifLearned" title="Укажите курс, на котором учитесь">
            <div class="required">
            </div>
            <span class="searchItemLabel">Курс: </span>

            <div class="searchItemBody">
                <input name="kurs" class="ifLearned" type="text" size="19" <?php echo "value='$kurs'";?>>
            </div>
        </div>
        <div id="formatEducation" class="searchItem ifLearned" title="Укажите форму обучения">
            <div class="required">
            </div>
            <span class="searchItemLabel">Очно / Заочно: </span>

            <div class="searchItemBody">
                <select name="ochnoZaochno" class="ifLearned">
                    <option value="0" <?php if ($ochnoZaochno == "0") echo "selected";?>></option>
                    <option value="ochno" <?php if ($ochnoZaochno == "ochno") echo "selected";?>>Очно</option>
                    <option value="zaochno" <?php if ($ochnoZaochno == "zaochno") echo "selected";?>>Заочно</option>
                </select>
            </div>
        </div>
        <div id="yearOfEnd" class="searchItem ifLearned" title="Укажите год окончания учебного заведения">
            <div class="required">
            </div>
            <span class="searchItemLabel">Год окончания: </span>

            <div class="searchItemBody">
                <input name="yearOfEnd" class="ifLearned" type="text" size="9" <?php echo "value='$yearOfEnd'";?>>
            </div>
        </div>
    </fieldset>

    <fieldset class="edited private">
        <legend>
            Работа
        </legend>
        <div>
            <input type="checkbox" name="notWorkCheckbox" value="isNotWorking"
                   id="notWorkCheckbox" <?php if ($notWorkCheckbox == "isNotWorking") echo "checked";?>>
            Я не работаю
        </div>
        <div class="searchItem ifWorked">
            <div class="required">
            </div>
            <span class="searchItemLabel">Место работы: </span>

            <div class="searchItemBody">
                <input name="placeOfWork" class="ifWorked" type="text" size="30" <?php echo "value='$placeOfWork'";?>>
            </div>
        </div>
        <div class="searchItem ifWorked">
            <div class="required">
            </div>
            <span class="searchItemLabel">Должность: </span>

            <div class="searchItemBody">
                <input name="workPosition" class="ifWorked" type="text" size="33" <?php echo "value='$workPosition'";?>>
            </div>
        </div>
    </fieldset>

    <fieldset class="edited private">
        <legend>
            Коротко о себе
        </legend>
        <div class="searchItem">
            <div class="required">
                <?php if ($typeTenant == "true") {
                echo "*";
            } ?>
            </div>
            <span class="searchItemLabel">В каком регионе родились: </span>

            <div class="searchItemBody">
                <input name="regionOfBorn" type="text" size="42" <?php if ($typeTenant == "true") {
                    echo "validations='validate[required]'";
                } echo "value='$regionOfBorn'";?>>
            </div>
        </div>
        <div class="searchItem">
            <div class="required">
                <?php if ($typeTenant == "true") {
                echo "*";
            } ?>
            </div>
            <span class="searchItemLabel">Родной город, населенный пункт: </span>

            <div class="searchItemBody">
                <input name="cityOfBorn" type="text" size="36" <?php if ($typeTenant == "true") {
                    echo "validations='validate[required]'";
                } echo "value='$cityOfBorn'";?>>
            </div>
        </div>
        <div class="searchItem">
            <div class="required"></div>
            <span class="searchItemLabel">Коротко о себе и своих интересах: </span>
        </div>
        <div class="searchItem">
            <div class="required"></div>
            <textarea name="shortlyAboutMe" cols="71" rows="4"><?php echo $shortlyAboutMe;?></textarea>
        </div>
    </fieldset>
    <div class="shadowText" style="margin-top: 7px;">
        По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
    </div>
</div>
<div id="tabs-3">
    <div class="shadowText">
        Укажите, пожалуйста, адрес Вашей личной страницы минимум в одной социальной сети. Это позволит системе
        представить Вас собственникам (только тех объектов, которыми Вы сами заинтересуетесь).
    </div>
    <fieldset class="edited private">
        <legend>
            Страницы в социальных сетях
        </legend>
        <div class="searchItem"
             title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
            <div class="required"></div>
            <img src="img/vkontakte.jpg">

            <div class="searchItemBody">
                <input type="text" name="vkontakte" size="62"
                       placeholder="http://vk.com/..." <?php echo "value='$vkontakte'";?>>
            </div>
        </div>
        <div class="searchItem"
             title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
            <div class="required"></div>
            <img src="img/odnoklassniki.png">

            <div class="searchItemBody">
                <input type="text" name="odnoklassniki" size="68"
                       placeholder="http://www.odnoklassniki.ru/profile/..." <?php echo "value='$odnoklassniki'";?>>
            </div>
        </div>
        <div class="searchItem"
             title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
            <div class="required"></div>
            <img src="img/facebook.jpg">

            <div class="searchItemBody">
                <input type="text" name="facebook" size="71"
                       placeholder="https://www.facebook.com/profile.php?..." <?php echo "value='$facebook'";?>>
            </div>
        </div>
        <div class="searchItem"
             title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
            <div class="required"></div>
            <img src="img/twitter.png">

            <div class="searchItemBody">
                <input type="text" name="twitter" size="62"
                       placeholder="https://twitter.com/..." <?php echo "value='$twitter'";?>>
            </div>
        </div>
    </fieldset>
    <div class="shadowText" style="margin-top: 7px;">
        По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
    </div>
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
        <div class="searchItem">
            <span class="searchItemLabel"> Тип: </span>

            <div class="searchItemBody">
                <select name="typeOfObject">
                    <option value="flat" <?php if ($typeOfObject == "flat") echo "selected";?>>квартира</option>
                    <option value="room" <?php if ($typeOfObject == "room") echo "selected";?>>комната</option>
                    <option value="house" <?php if ($typeOfObject == "house") echo "selected";?>>дом, коттедж</option>
                    <option value="townhouse" <?php if ($typeOfObject == "townhouse") echo "selected";?>>таунхаус
                    </option>
                    <option value="dacha" <?php if ($typeOfObject == "dacha") echo "selected";?>>дача</option>
                    <option value="garage" <?php if ($typeOfObject == "garage") echo "selected";?>>гараж</option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Количество комнат: </span>

            <div class="searchItemBody">
                <input type="checkbox" value="1"
                       name="amountOfRooms[]" <?php if ($amountOfRooms1 == "1") echo "checked";?>>
                1
                <input type="checkbox" value="2"
                       name="amountOfRooms[]" <?php if ($amountOfRooms2 == "2") echo "checked";?>>
                2
                <input type="checkbox" value="3"
                       name="amountOfRooms[]" <?php if ($amountOfRooms3 == "3") echo "checked";?>>
                3
                <input type="checkbox" value="4"
                       name="amountOfRooms[]" <?php if ($amountOfRooms4 == "4") echo "checked";?>>
                4
                <input type="checkbox" value="5"
                       name="amountOfRooms[]" <?php if ($amountOfRooms5 == "5") echo "checked";?>>
                5
                <input type="checkbox" value="6"
                       name="amountOfRooms[]" <?php if ($amountOfRooms6 == "6") echo "checked";?>>
                6...
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Комнаты смежные: </span>

            <div class="searchItemBody">
                <select name="adjacentRooms">
                    <option value="yes" <?php if ($adjacentRooms == "yes") echo "selected";?>>не имеет значения</option>
                    <option value="no" <?php if ($adjacentRooms == "no") echo "selected";?>>только изолированные
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Этаж: </span>

            <div class="searchItemBody">
                <select name="floor">
                    <option value="any" <?php if ($floor == "any") echo "selected";?>>любой</option>
                    <option value="not1" <?php if ($floor == "not1") echo "selected";?>>не первый</option>
                    <option value="not1notLasted" <?php if ($floor == "not1notLasted") echo "selected";?>>не первый и не
                        последний
                    </option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Мебель: </span>

            <div class="searchItemBody">
                <select name="furniture">
                    <option value="any" <?php if ($furniture == "any") echo "selected";?>>не имеет значения</option>
                    <option value="with" <?php if ($furniture == "with") echo "selected";?>>с мебелью и быт. техникой</option>
                    <option value="without" <?php if ($furniture == "without") echo "selected";?>>без мебели</option>
                </select>
            </div>
        </div>
    </fieldset>
    <fieldset class="edited">
        <legend>
            Стоимость
        </legend>
        <div class="searchItem">
            <div class="searchItemLabel">
                Арендная плата (в месяц с учетом к.у.)
            </div>
            <div class="searchItemBody">
                от
                <input type="text" name="minCost" size="10" maxlength="8" <?php echo "value='$minCost'";?>>
                руб., до
                <input type="text" name="maxCost" size="10" maxlength="8" <?php echo "value='$maxCost'";?>>
                руб.
            </div>
        </div>
        <div class="searchItem"
             title="Какую сумму Вы готовы передать собственнику в качестве возвращаемого гарантийного депозита, а также предоплаты за проживание, кроме арендной платы за первый месяц">
            <span class="searchItemLabel"> Залог </span>

            <div class="searchItemBody">
                до
                <input type="text" name="pledge" size="10" maxlength="8" <?php echo "value='$pledge'";?>>
                руб.
            </div>
        </div>
    </fieldset>
</div>
<div id="rightBlockOfSearchParameters">
<fieldset>
<legend>
    Район
</legend>
<div class="searchItem">
<div class="searchItemBody">
<ul>
<li>
    <input type="checkbox" name="district[]"
           value="1" <?php if ($district1 == "1") echo "checked";?>>
    Автовокзал (южный)
</li>
<li>
    <input type="checkbox" name="district[]"
           value="2" <?php if ($district2 == "2") echo "checked";?>>
    Академический
</li>
<li>
    <input type="checkbox" name="district[]"
           value="3" <?php if ($district3 == "3") echo "checked";?>>
    Ботанический
</li>
<li>
    <input type="checkbox" name="district[]"
           value="4" <?php if ($district4 == "4") echo "checked";?>>
    ВИЗ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="5" <?php if ($district5 == "5") echo "checked";?>>
    Вокзальный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="6" <?php if ($district6 == "6") echo "checked";?>>
    Втузгородок
</li>
<li>
    <input type="checkbox" name="district[]"
           value="7" <?php if ($district7 == "7") echo "checked";?>>
    Горный щит
</li>
<li>
    <input type="checkbox" name="district[]"
           value="8" <?php if ($district8 == "8") echo "checked";?>>
    Елизавет
</li>
<li>
    <input type="checkbox" name="district[]"
           value="9" <?php if ($district9 == "9") echo "checked";?>>
    ЖБИ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="10" <?php if ($district10 == "10") echo "checked";?>>
    Завокзальный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="11" <?php if ($district11 == "11") echo "checked";?>>
    Заречный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="12" <?php if ($district12 == "12") echo "checked";?>>
    Изоплит
</li>
<li>
    <input type="checkbox" name="district[]"
           value="13" <?php if ($district13 == "13") echo "checked";?>>
    Исток
</li>
<li>
    <input type="checkbox" name="district[]"
           value="14" <?php if ($district14 == "14") echo "checked";?>>
    Калиновский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="15" <?php if ($district15 == "15") echo "checked";?>>
    Кольцово
</li>
<li>
    <input type="checkbox" name="district[]"
           value="16" <?php if ($district16 == "16") echo "checked";?>>
    Компрессорный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="17" <?php if ($district17 == "17") echo "checked";?>>
    Лечебный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="18" <?php if ($district18 == "18") echo "checked";?>>
    Малый исток
</li>
<li>
    <input type="checkbox" name="district[]"
           value="19" <?php if ($district19 == "19") echo "checked";?>>
    Нижнеисетский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="20" <?php if ($district20 == "20") echo "checked";?>>
    Парковый
</li>
<li>
    <input type="checkbox" name="district[]"
           value="21" <?php if ($district21 == "21") echo "checked";?>>
    Пионерский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="22" <?php if ($district22 == "22") echo "checked";?>>
    Птицефабрика
</li>
<li>
    <input type="checkbox" name="district[]"
           value="23" <?php if ($district23 == "23") echo "checked";?>>
    Рудный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="24" <?php if ($district24 == "24") echo "checked";?>>
    Садовый
</li>
<li>
    <input type="checkbox" name="district[]"
           value="25" <?php if ($district25 == "25") echo "checked";?>>
    Северка
</li>
<li>
    <input type="checkbox" name="district[]"
           value="26" <?php if ($district26 == "26") echo "checked";?>>
    Семь ключей
</li>
<li>
    <input type="checkbox" name="district[]"
           value="27" <?php if ($district27 == "27") echo "checked";?>>
    Сибирский тракт
</li>
<li>
    <input type="checkbox" name="district[]"
           value="28" <?php if ($district28 == "28") echo "checked";?>>
    Синие камни
</li>
<li>
    <input type="checkbox" name="district[]"
           value="29" <?php if ($district29 == "29") echo "checked";?>>
    Совхозный
</li>
<li>
    <input type="checkbox" name="district[]"
           value="30" <?php if ($district30 == "30") echo "checked";?>>
    Сортировка новая
</li>
<li>
    <input type="checkbox" name="district[]"
           value="31" <?php if ($district31 == "31") echo "checked";?>>
    Сортировка старая
</li>
<li>
    <input type="checkbox" name="district[]"
           value="32" <?php if ($district32 == "32") echo "checked";?>>
    Уктус
</li>
<li>
    <input type="checkbox" name="district[]"
           value="33" <?php if ($district33 == "33") echo "checked";?>>
    УНЦ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="34" <?php if ($district34 == "34") echo "checked";?>>
    Уралмаш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="35" <?php if ($district35 == "35") echo "checked";?>>
    Химмаш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="36" <?php if ($district36 == "36") echo "checked";?>>
    Центр
</li>
<li>
    <input type="checkbox" name="district[]"
           value="37" <?php if ($district37 == "37") echo "checked";?>>
    Чермет
</li>
<li>
    <input type="checkbox" name="district[]"
           value="38" <?php if ($district38 == "38") echo "checked";?>>
    Чусовское озеро
</li>
<li>
    <input type="checkbox" name="district[]"
           value="39" <?php if ($district39 == "39") echo "checked";?>>
    Шабровский
</li>
<li>
    <input type="checkbox" name="district[]"
           value="40" <?php if ($district40 == "40") echo "checked";?>>
    Шарташ
</li>
<li>
    <input type="checkbox" name="district[]"
           value="41" <?php if ($district41 == "41") echo "checked";?>>
    Шарташский рынок
</li>
<li>
    <input type="checkbox" name="district[]"
           value="42" <?php if ($district42 == "42") echo "checked";?>>
    Широкая речка
</li>
<li>
    <input type="checkbox" name="district[]"
           value="43" <?php if ($district43 == "43") echo "checked";?>>
    Шувакиш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="44" <?php if ($district44 == "44") echo "checked";?>>
    Эльмаш
</li>
<li>
    <input type="checkbox" name="district[]"
           value="45" <?php if ($district45 == "45") echo "checked";?>>
    Юго-запад
</li>
<li>
    <input type="checkbox" name="district[]"
           value="46" <?php if ($district46 == "46") echo "checked";?>>
    За городом
</li>
</ul>
</div>
</div>
</fieldset>
</div>
<!-- /end.rightBlockOfSearchParameters -->

<fieldset class="edited private">
    <legend>
        Особые параметры поиска
    </legend>
    <div class="searchItem">
        <span class="searchItemLabel">Как собираетесь проживать: </span>

        <div class="searchItemBody">
            <select name="withWho" id="withWho">
                <option value="alone" <?php if ($withWho == "alone") echo "selected";?>>один</option>
                <option value="couple" <?php if ($withWho == "couple") echo "selected";?>>семейная пара</option>
                <option value="nonFamilyPair" <?php if ($withWho == "nonFamilyPair") echo "selected";?>>несемейная
                    пара
                </option>
                <option value="withFriends" <?php if ($withWho == "withFriends") echo "selected";?>>со знакомыми
                </option>
            </select>
        </div>
    </div>
    <div class="searchItem" id="withWhoDescription" style="display: none;">
        <div class="searchItemLabel">
            Ссылки на страницы сожителей:
        </div>
        <div class="searchItemBody">
            <textarea name="linksToFriends" cols="40" rows="3"><?php echo $linksToFriends;?></textarea>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Дети: </span>

        <div class="searchItemBody">
            <select name="children" id="children">
                <option value="without" <?php if ($children == "without") echo "selected";?>>без детей</option>
                <option value="childrenUnder4" <?php if ($children == "childrenUnder4") echo "selected";?>>с детьми
                    младше 4-х лет
                </option>
                <option value="childrenOlder4" <?php if ($children == "childrenOlder4") echo "selected";?>>с детьми
                    старше 4-х лет
                </option>
            </select>
        </div>
    </div>
    <div class="searchItem" id="childrenDescription" style="display: none;">
        <div class="searchItemLabel">
            Сколько у Вас детей и какого возраста:
        </div>
        <div class="searchItemBody">
            <textarea name="howManyChildren" cols="40" rows="3"><?php echo $howManyChildren;?></textarea>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Животные: </span>

        <div class="searchItemBody">
            <select name="animals" id="animals">
                <option value="without" <?php if ($animals == "without") echo "selected";?>>без животных</option>
                <option value="with" <?php if ($animals == "with") echo "selected";?>>с животным(ми)</option>
            </select>
        </div>
    </div>
    <div class="searchItem" id="animalsDescription" style="display: none;">
        <div class="searchItemLabel">
            Сколько у Вас животных и какого вида:
        </div>
        <div class="searchItemBody">
            <textarea name="howManyAnimals" cols="40" rows="3"><?php echo $howManyAnimals;?></textarea>
        </div>
    </div>
    <div class="searchItem">
        <span class="searchItemLabel">Ориентировочный срок аренды:</span>

        <div class="searchItemBody">
            <input type="text" name="period" size="18" maxlength="80"
                   validations="validate[required]" <?php echo "value='$period'";?>>
        </div>
    </div>
    <div class="searchItem">
        <div class="searchItemLabel">
            Дополнительные условия поиска:
        </div>
        <div class="searchItemBody">
            <textarea name="additionalDescriptionOfSearch" cols="50"
                      rows="4"><?php echo $additionalDescriptionOfSearch;?></textarea>
        </div>
    </div>
</fieldset>
</div>
<div class="shadowText" style="margin-top: 7px;">
    По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
</div>
</div>
<!-- /end.tabs-4 -->
    <?php endif;?>
</div>
<!-- /end.tabs -->
<div style="float: right;">
    <div style="text-align: left; margin-top: 7px;">
        <input type="checkbox" name="lic" value="yes" <?php if ($lic == "yes") echo "checked";?>> Я принимаю условия <a href="#">лицензионного соглашения</a>
    </div>
    <div class="readyButton">
        <button type="submit" name="readyButton" id="readyButton">
        Готово
    </button>
    </div>
    <div class="capcha">
        <script type="text/javascript"
                src="http://www.google.com/recaptcha/api/challenge?k=6LfPj9QSAAAAADiTQL68cyA1TlIBZMq5wHe6n_TK"></script>
        <noscript>
            <iframe src="http://www.google.com/recaptcha/api/noscript?k=your_public_key" height="300" width="500"
                    frameborder="0"></iframe>
            <br>
            <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
            <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
        </noscript>
    </div>
</div>
<!-- Добавялем невидимый input для того, чтобы передать тип пользователя (собственник/арендатор) - это используется в JS для простановки обязательности полей для заполнения -->
<?php echo "<input type='hidden' class='userType' typeTenant='" . $typeTenant . "' typeOwner='" . $typeOwner . "'>"; ?>
</form>
</div>
<!-- /end.wrapperOfTabs-->
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
<!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->

<!-- jQuery UI с моей темой оформления -->
<script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>

<!-- Русификатор виджета календарь -->
<script src="js/vendor/jquery.ui.datepicker-ru.js"></script>

<!-- Скрипт для валидации на лету полей формы -->
<script src="js/vendor/jquery.validationEngine.js"></script>

<!-- Загрузчик фотографий на AJAX -->
<script src="js/vendor/fileuploader.js" type="text/javascript"></script>

<!-- scripts concatenated and minified via build script -->
<script src="js/main.js"></script>
<script src="js/registrationForm.js"></script>

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
