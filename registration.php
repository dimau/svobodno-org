<?php
session_start();
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем библиотеку функций
$correct = null; // Инициализируем переменную корректности - нужно для того, чтобы не менять лишний раз идентификатор в input hidden у фотографий

//проверим, быть может пользователь уже авторизирован. Если это так, перенаправим его на главную страницу сайта
if (login()) {
    header('Location: index.php');
}
else {
    // Выясняем роль пользователя - только арендатор, только собственник, или и то и другое.
    if (isset($_GET['typeTenant'])) {$typeTenant = true;} else {$typeTenant = false;}
    if (isset($_GET['typeOwner'])) {$typeOwner = true;} else {$typeOwner = false;}
    if (!isset($_GET['typeTenant']) && !isset($_GET['typeOwner'])) {$typeTenant = true; $typeOwner = true;}

    // Если была нажата кнопка регистрации, проверим данные на корректность и, если данные введены и введены правильно, добавим запись с новым пользователем в БД
    if (isset($_POST['readyButton']))
    {
        // Формируем набор переменных для сохранения в базу данных, либо для возвращения вместе с формой при их некорректности
        if (isset($_POST['name'])) $name = htmlspecialchars($_POST['name']); else $name = "";
        if (isset($_POST['secondName'])) $secondName = htmlspecialchars($_POST['secondName']); else $secondName = "";
        if (isset($_POST['surname'])) $surname = htmlspecialchars($_POST['surname']); else $surname = "";
        if (isset($_POST['sex'])) $sex = htmlspecialchars($_POST['sex']); else $sex = "0";
        if (isset($_POST['nationality'])) $nationality = htmlspecialchars($_POST['nationality']); else $nationality = "0";
        if (isset($_POST['birthday'])) $birthday = htmlspecialchars($_POST['birthday']); else $birthday = "";
        if (isset($_POST['login'])) $login = htmlspecialchars($_POST['login']); else $login = "";
        if (isset($_POST['password'])) $password = htmlspecialchars($_POST['password']); else $password = "";
        if (isset($_POST['telephon'])) $telephon = htmlspecialchars($_POST['telephon']); else $telephon = "";
        if (isset($_POST['email'])) $email = htmlspecialchars($_POST['email']); else $email = "";
        $fileUploadId = $_POST['fileUploadId'];

        if (isset($_POST['currentStatusEducation'])) $currentStatusEducation = htmlspecialchars($_POST['currentStatusEducation']); else $currentStatusEducation = "0";
        if (isset($_POST['almamater'])) $almamater = htmlspecialchars($_POST['almamater']); else $almamater = "";
        if (isset($_POST['speciality'])) $speciality = htmlspecialchars($_POST['speciality']); else $speciality = "";
        if (isset($_POST['kurs'])) $kurs = htmlspecialchars($_POST['kurs']); else $kurs = "";
        if (isset($_POST['ochnoZaochno'])) $ochnoZaochno = htmlspecialchars($_POST['ochnoZaochno']); else $ochnoZaochno = "";
        if (isset($_POST['yearOfEnd'])) $yearOfEnd = htmlspecialchars($_POST['yearOfEnd']); else $yearOfEnd = "";
        if (isset($_POST['notWorkCheckbox'])) $notWorkCheckbox = htmlspecialchars($_POST['notWorkCheckbox']); else $notWorkCheckbox = "";
        if (isset($_POST['placeOfWork'])) $placeOfWork = htmlspecialchars($_POST['placeOfWork']); else $placeOfWork = "";
        if (isset($_POST['workPosition'])) $workPosition = htmlspecialchars($_POST['workPosition']); else $workPosition = "";
        if (isset($_POST['regionOfBorn'])) $regionOfBorn = htmlspecialchars($_POST['regionOfBorn']); else $regionOfBorn = "";
        if (isset($_POST['cityOfBorn'])) $cityOfBorn = htmlspecialchars($_POST['cityOfBorn']); else $cityOfBorn = "";
        if (isset($_POST['shortlyAboutMe'])) $shortlyAboutMe = htmlspecialchars($_POST['shortlyAboutMe']); else $shortlyAboutMe = "";

        if (isset($_POST['vkontakte'])) $vkontakte = htmlspecialchars($_POST['vkontakte']); else $vkontakte = "";
        if (isset($_POST['odnoklassniki'])) $odnoklassniki = htmlspecialchars($_POST['odnoklassniki']); else $odnoklassniki = "";
        if (isset($_POST['facebook'])) $facebook = htmlspecialchars($_POST['facebook']); else $facebook = "";
        if (isset($_POST['twitter'])) $twitter = htmlspecialchars($_POST['twitter']); else $twitter = "";

        if (isset($_POST['typeOfObject'])) $typeOfObject = htmlspecialchars($_POST['typeOfObject']); else $typeOfObject = "flat";

        if (is_array($_POST['amountOfRooms'])) // Проверяем, передан ли массив значений
        {
            $amountOfRooms = $_POST['amountOfRooms']; // Будем использовать переменную при записи данных в таблицу в виде массива
            foreach ($_POST['amountOfRooms'] as $value)
            {
                if ($value == "1") $amountOfRooms1 = "1"; else $amountOfRooms1 = "";
                if ($value == "2") $amountOfRooms2 = "2"; else $amountOfRooms2 = "";
                if ($value == "3") $amountOfRooms3 = "3"; else $amountOfRooms3 = "";
                if ($value == "4") $amountOfRooms4 = "4"; else $amountOfRooms4 = "";
                if ($value == "5") $amountOfRooms5 = "5"; else $amountOfRooms5 = "";
                if ($value == "6") $amountOfRooms6 = "6"; else $amountOfRooms6 = "";
            }
        }
        else
        {
            $amountOfRooms = array("1", "2", "3", "4", "5", "6");
        }

        if (isset($_POST['adjacentRooms'])) $adjacentRooms = htmlspecialchars($_POST['adjacentRooms']); else $adjacentRooms = "yes";
        if (isset($_POST['floor'])) $floor = htmlspecialchars($_POST['floor']); else $floor = "any";
        if (isset($_POST['withWithoutFurniture'])) $withWithoutFurniture = htmlspecialchars($_POST['withWithoutFurniture']); else $withWithoutFurniture = "";
        if (isset($_POST['minCost']) && $_POST['minCost'] != "") $minCost = htmlspecialchars($_POST['minCost']); else $minCost = "0";
        if (isset($_POST['maxCost']) && $_POST['maxCost'] != "") $maxCost = htmlspecialchars($_POST['maxCost']); else $maxCost = "99999999";
        if (isset($_POST['pledge']) && $_POST['pledge'] != "") $pledge = htmlspecialchars($_POST['pledge']); else $pledge = "99999999";
        if (isset($_POST['district1'])) $district1 = htmlspecialchars($_POST['district1']); else $district1 = "";
        if (isset($_POST['district2'])) $district2 = htmlspecialchars($_POST['district2']); else $district2 = "";
        if (isset($_POST['district3'])) $district3 = htmlspecialchars($_POST['district3']); else $district3 = "";
        if (isset($_POST['district4'])) $district4 = htmlspecialchars($_POST['district4']); else $district4 = "";
        if (isset($_POST['district5'])) $district5 = htmlspecialchars($_POST['district5']); else $district5 = "";
        if (isset($_POST['district6'])) $district6 = htmlspecialchars($_POST['district6']); else $district6 = "";
        if (isset($_POST['district7'])) $district7 = htmlspecialchars($_POST['district7']); else $district7 = "";
        if (isset($_POST['district8'])) $district8 = htmlspecialchars($_POST['district8']); else $district8 = "";
        if (isset($_POST['district9'])) $district9 = htmlspecialchars($_POST['district9']); else $district9 = "";
        if (isset($_POST['district10'])) $district10 = htmlspecialchars($_POST['district10']); else $district10 = "";
        if (isset($_POST['district11'])) $district11 = htmlspecialchars($_POST['district11']); else $district11 = "";
        if (isset($_POST['district12'])) $district12 = htmlspecialchars($_POST['district12']); else $district12 = "";
        if (isset($_POST['district13'])) $district13 = htmlspecialchars($_POST['district13']); else $district13 = "";
        if (isset($_POST['district14'])) $district14 = htmlspecialchars($_POST['district14']); else $district14 = "";
        if (isset($_POST['district15'])) $district15 = htmlspecialchars($_POST['district15']); else $district15 = "";
        if (isset($_POST['district16'])) $district16 = htmlspecialchars($_POST['district16']); else $district16 = "";
        if (isset($_POST['district17'])) $district17 = htmlspecialchars($_POST['district17']); else $district17 = "";
        if (isset($_POST['district18'])) $district18 = htmlspecialchars($_POST['district18']); else $district18 = "";
        if (isset($_POST['district19'])) $district19 = htmlspecialchars($_POST['district19']); else $district19 = "";
        if (isset($_POST['district20'])) $district20 = htmlspecialchars($_POST['district20']); else $district20 = "";
        if (isset($_POST['district21'])) $district21 = htmlspecialchars($_POST['district21']); else $district21 = "";
        if (isset($_POST['district22'])) $district22 = htmlspecialchars($_POST['district22']); else $district22 = "";
        if (isset($_POST['district23'])) $district23 = htmlspecialchars($_POST['district23']); else $district23 = "";
        if (isset($_POST['district24'])) $district24 = htmlspecialchars($_POST['district24']); else $district24 = "";
        if (isset($_POST['district25'])) $district25 = htmlspecialchars($_POST['district25']); else $district25 = "";
        if (isset($_POST['district26'])) $district26 = htmlspecialchars($_POST['district26']); else $district26 = "";
        if (isset($_POST['district27'])) $district27 = htmlspecialchars($_POST['district27']); else $district27 = "";
        if (isset($_POST['district28'])) $district28 = htmlspecialchars($_POST['district28']); else $district28 = "";
        if (isset($_POST['district29'])) $district29 = htmlspecialchars($_POST['district29']); else $district29 = "";
        if (isset($_POST['district30'])) $district30 = htmlspecialchars($_POST['district30']); else $district30 = "";
        if (isset($_POST['district31'])) $district31 = htmlspecialchars($_POST['district31']); else $district31 = "";
        if (isset($_POST['district32'])) $district32 = htmlspecialchars($_POST['district32']); else $district32 = "";
        if (isset($_POST['district33'])) $district33 = htmlspecialchars($_POST['district33']); else $district33 = "";
        if (isset($_POST['district34'])) $district34 = htmlspecialchars($_POST['district34']); else $district34 = "";
        if (isset($_POST['district35'])) $district35 = htmlspecialchars($_POST['district35']); else $district35 = "";
        if (isset($_POST['district36'])) $district36 = htmlspecialchars($_POST['district36']); else $district36 = "";
        if (isset($_POST['district37'])) $district37 = htmlspecialchars($_POST['district37']); else $district37 = "";
        if (isset($_POST['district38'])) $district38 = htmlspecialchars($_POST['district38']); else $district38 = "";
        if (isset($_POST['district39'])) $district39 = htmlspecialchars($_POST['district39']); else $district39 = "";
        if (isset($_POST['district40'])) $district40 = htmlspecialchars($_POST['district40']); else $district40 = "";
        if (isset($_POST['district41'])) $district41 = htmlspecialchars($_POST['district41']); else $district41 = "";
        if (isset($_POST['district42'])) $district42 = htmlspecialchars($_POST['district42']); else $district42 = "";
        if (isset($_POST['district43'])) $district43 = htmlspecialchars($_POST['district43']); else $district43 = "";
        if (isset($_POST['district44'])) $district44 = htmlspecialchars($_POST['district44']); else $district44 = "";
        if (isset($_POST['district45'])) $district45 = htmlspecialchars($_POST['district45']); else $district45 = "";
        if (isset($_POST['district46'])) $district46 = htmlspecialchars($_POST['district46']); else $district46 = "";

        if (isset($_POST['withWho'])) $withWho = htmlspecialchars($_POST['withWho']); else $withWho = "alone";
        if (isset($_POST['linksToFriends'])) $linksToFriends = htmlspecialchars($_POST['linksToFriends']); else $linksToFriends = "";
        if (isset($_POST['children'])) $children = htmlspecialchars($_POST['children']); else $children = "without";
        if (isset($_POST['howManyChildren'])) $howManyChildren = htmlspecialchars($_POST['howManyChildren']); else $howManyChildren = "";
        if (isset($_POST['animals'])) $animals = htmlspecialchars($_POST['animals']); else $animals = "without";
        if (isset($_POST['howManyAnimals'])) $howManyAnimals = htmlspecialchars($_POST['howManyAnimals']); else $howManyAnimals = "";
        if (isset($_POST['period'])) $period = htmlspecialchars($_POST['period']); else $period = "";
        if (isset($_POST['additionalDescriptionOfSearch'])) $additionalDescriptionOfSearch = htmlspecialchars($_POST['additionalDescriptionOfSearch']); else $additionalDescriptionOfSearch = "";

        if (isset($_POST['lic'])) $lic = htmlspecialchars($_POST['lic']); else $lic = "";

        // Проверяем корректность данных пользователя. Функции registrationCorrect() возвращает пустой array, если введённые данные верны и array с описанием ошибок в противном случае
        $errors = registrationCorrect();
        if (count($errors) == 0) $correct = true; else $correct = false; // Считаем ошибки, если 0, то можно будет записать данные в БД

        // Если данные, указанные пользователем, корректны, запишем их в базу данных
        if ($correct)
        {
            // Корректируем дату дня рождения для того, чтобы сделать ее пригодной для сохранения в базу данных
            $date = substr($birthday, 0, 2);
            $month = substr($birthday, 3, 2);
            $year = substr($birthday, 6, 4);
            $birthday = $year.$month.$date;

            $salt = mt_rand(100, 999);
            $tm = time();
            $last_act = $tm;
            $reg_date = $tm;
            $password = md5(md5($password) . $salt);

            if (mysql_query("INSERT INTO users (typeTenant,typeOwner,name,secondName,surname,sex,nationality,birthday,login,password,telephon,emailReg,email,currentStatusEducation,almamater,speciality,kurs,ochnoZaochno,yearOfEnd,notWorkCheckbox,placeOfWork,workPosition,regionOfBorn,cityOfBorn,shortlyAboutMe,vkontakte,odnoklassniki,facebook,twitter,searchRequestId,lic,salt,last_act,reg_date) VALUES ('" . $typeTenant . "','" . $typeOwner . "','" . $name . "','" . $secondName . "','" . $surname . "','" . $sex . "','" . $nationality . "','" . $birthday . "','" . $login . "','" . $password . "','" . $telephon . "','" . $email . "','" . $email . "','" . $currentStatusEducation . "','" . $almamater . "','" . $speciality . "','" . $kurs . "','" . $ochnoZaochno . "','" . $yearOfEnd . "','" . $notWorkCheckbox . "','" . $placeOfWork . "','" . $workPosition . "','" . $regionOfBorn . "','" . $cityOfBorn . "','" . $shortlyAboutMe . "','" . $vkontakte . "','" . $odnoklassniki . "','" . $facebook . "','" . $twitter . "','" . $searchRequestId . "','" . $lic . "','" . $salt . "','" . $last_act . "','" . $reg_date . "')")) // Пишем данные нового пользователя в БД
            {

                /******* Переносим информацию о фотографиях пользователя в таблицу для постоянного хранения *******/
                // Узнаем id пользователя - необходимо при сохранении информации о фотке в постоянную базу
                $rezId = mysql_query("SELECT id FROM users WHERE login = '" . $login . "'");
                $rowId = mysql_fetch_assoc($rezId);
                // Получим информацию о всех фотках, соответствующих текущему fileUploadId
                $rezTempFotos = mysql_query("SELECT filename, extension, filesizeMb FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");
                for ($i = 0; $i < mysql_num_rows($rezTempFotos); $i++)
                {
                    $rowTempFotos = mysql_fetch_assoc($rezTempFotos);
                    mysql_query("INSERT INTO userFotos (filename, extension, filesizeMb, userId) VALUES ('" . $rowTempFotos['filename'] . "','" . $rowTempFotos['extension'] . "','" . $rowTempFotos['filesizeMb'] . "','" . $rowId['id'] . "')"); // Переносим информацию о фотографиях на постоянное хранение
                }
                // Удаляем записи о фотках в таблице для временного хранения данных
                mysql_query("DELETE FROM tempFotos WHERE fileUploadId = '" . $fileUploadId . "'");



                /******* Сохраняем поисковый запрос, если он был указан пользователем *******/
                // Преобразование формата инфы об искомом кол-ве комнат и районах, так как MySQL не умеет хранить массивы

                // Непосредственное сохранение данных о поисковом запросе
                if ($typeTenant == true)
                {
                    mysql_query("INSERT INTO searchRequests (id, typeOfObject, amountOfRooms, adjacentRooms, floor, withWithoutFurniture, minCost, maxCost, pledge, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, period, additionalDescriptionOfSearch) VALUES ('" . $rowId['id'] . "','" . $typeOfObject . "','" . $amountOfRooms . "','" . $adjacentRooms . "','" . $floor . "','" . $withWithoutFurniture . "','" . $minCost . "','" . $maxCost . "','" . $pledge . "','" . "!!!!!!!!!!!!!!!!!!" . "','" . $withWho . "','" . $linksToFriends . "','" . $children . "','" . $howManyChildren . "','" . $children . "','" . $howManyChildren . "','" . $animals . "','" . $howManyAnimals . "','" . $period . "','" . $additionalDescriptionOfSearch . "')"); // Поисковый запрос пользователя сохраняется в специальной таблице
                }



                /******* Авторизовываем пользователя *******/
                $error = enter();
                if (count($error) == 0) //если нет ошибок, отправляем уже авторизованного пользователя на страницу успешной регистрации
                {
                    header('Location: successfullRegistration.php'); //после успешной регистрации - переходим на соответствующую страницу
                }
                else
                {
                    // TODO:что-то нужно делать в случае, если возникли ошибки при авторизации во время регистрации - как минимум вывести их текст во всплывающем окошке
                }

            }
            else
            {
                $correct = false;
                $errors[] = 'К сожалению, при сохранении данных произошла ошибка: проверьте, пожалуйста, еще раз корректность Вашей информации и повторите попытку регистрации';
                // Сохранении данных в БД не прошло - пользователь не зарегистрирован
            }
        }
    }
    // Действия при первой загрузке страницы регистрации - пользователь еще не заполнял поля и не отправлял данные на проверку серверу
    else {
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
        $withWithoutFurniture = "";
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
    <link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css" media="screen" title="no title" charset="utf-8" />
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
<div id="userMistakesBlock" class="ui-widget" style="display: none; width: 94%; position: absolute; top: 0px; left: 3%; z-index: 100;">
    <div class="ui-state-highlight ui-corner-all" style="text-align: center; padding: 0 .7em;">
        <div style="display: inline-block; text-align: left;">
        <p>
            <span class="ui-icon ui-icon-info" style="display: inline-block; vertical-align: middle; margin-right: .3em;"></span>
            <span id="userMistakesText" style="vertical-align: middle;">При обработке данных возникли ошибки:</span>
        </p>
        <ol><?php
                if ($correct == false && isset($errors))
                {
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
<?php if($typeTenant):?>
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
                    <input name="name" type="text" size="38" autofocus validations="validate[required]" <?php echo "value='$name'";?>>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Отчество: </span>

                <div class="searchItemBody">
                    <input name="secondName" type="text" size="33" validations="validate[required]" <?php echo "value='$secondName'";?>>
                </div>
            </div>
            <div class="searchItem">
                <div class="required">
                    *
                </div>
                <span class="searchItemLabel">Фамилия: </span>

                <div class="searchItemBody">
                    <input name="surname" type="text" size="33" validations="validate[required]" <?php echo "value='$surname'";?>>
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
            <div class="searchItem"> <!-- TODO: поменять контроль поля при подключении календаря -->
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
                        <input type="text" size="30" maxlength="50" name="login" validations="validate[required]" <?php echo "value='$login'";?>>
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
                        <input name="telephon" type="text" size="27" validations="validate[required,custom[telephone]]" <?php echo "value='$telephon'";?>>
                    </div>
                </div>
                <div class="searchItem">
                    <div class="required">
                        <?php if ($typeTenant) {echo "*";} ?>
                    </div>
                    <span class="searchItemLabel">e-mail: </span>

                    <div class="searchItemBody">
                        <input name="email" type="text" size="30" <?php if ($typeTenant) {echo "validations='validate[required,custom[email]]'";} echo "value='$email'"; ?>>
                    </div>
                </div>
            </fieldset>
        </div>

        <!--

                                      Кроме того, для собственников не нужно передавать блоки Образование и Работа, Коротко о себе
                                      Также для собственника не формируется вкладка Условия поиска
                                      Фото становится необязательным - убрать звездочку

                                      Сделать проверку перед отправкой и серверную часть капчи

                                      Но собственнику отправляется дополнительно

                                      -->

        <fieldset class="edited private" style="min-width: 300px;">
            <legend title="Для успешной регистрации должна быть загружена хотя бы 1 фотография">
                <div class="required">
                    <?php if ($typeTenant) {echo "*";} ?>
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
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Текущий статус: </span>

            <div class="searchItemBody">
                <select name="currentStatusEducation" id="currentStatusEducation" <?php if ($typeTenant) {echo "validations='validate[required]'";} ?>>
                    <option value="0" <?php if ($currentStatusEducation == "0") echo "selected";?>></option>
                    <option value="withoutEducation" <?php if ($currentStatusEducation == "withoutEducation") echo "selected";?>>Нигде не учился
                    </option>
                    <option value="learningNow" <?php if ($currentStatusEducation == "learningNow") echo "selected";?>>Сейчас учусь</option>
                    <option value="finishedEducation" <?php if ($currentStatusEducation == "finishedEducation") echo "selected";?>>Закончил</option>
                </select>
            </div>
        </div>
        <div id="almamater" class="searchItem ifLearned"
             title="Укажите учебное заведение, в котором учитесь сейчас, либо последнее из тех, что заканчивали">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Учебное заведение: </span>

            <div class="searchItemBody">
                <input name="almamater" class="ifLearned" type="text" size="50" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$almamater'";?>>
            </div>
        </div>
        <div id="speciality" class="searchItem ifLearned">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Специальность: </span>

            <div class="searchItemBody">
                <input name="speciality" class="ifLearned" type="text" size="55" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$speciality'";?>>
            </div>
        </div>
        <div id="kurs" class="searchItem ifLearned" title="Укажите курс, на котором учитесь">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Курс: </span>

            <div class="searchItemBody">
                <input name="kurs" class="ifLearned" type="text" size="19" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$kurs'";?>>
            </div>
        </div>
        <div id="formatEducation" class="searchItem ifLearned" title="Укажите форму обучения">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Очно / Заочно: </span>

            <div class="searchItemBody">
                <select name="ochnoZaochno" class="ifLearned" <?php if ($typeTenant) {echo "validations='validate[required]'";} ?>>
                    <option value="0" <?php if ($ochnoZaochno == "0") echo "selected";?>></option>
                    <option value="ochno" <?php if ($ochnoZaochno == "ochno") echo "selected";?>>Очно</option>
                    <option value="zaochno" <?php if ($ochnoZaochno == "zaochno") echo "selected";?>>Заочно</option>
                </select>
            </div>
        </div>
        <div id="yearOfEnd" class="searchItem ifLearned" title="Укажите год окончания учебного заведения">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Год окончания: </span>

            <div class="searchItemBody">
                <input name="yearOfEnd" class="ifLearned" type="text" size="9" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$yearOfEnd'";?>>
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
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Место работы: </span>

            <div class="searchItemBody">
                <input name="placeOfWork" class="ifWorked" type="text" size="30" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$placeOfWork'";?>>
            </div>
        </div>
        <div class="searchItem ifWorked">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Должность: </span>

            <div class="searchItemBody">
                <input name="workPosition" class="ifWorked" type="text" size="33" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$workPosition'";?>>
            </div>
        </div>
    </fieldset>

    <fieldset class="edited private">
        <legend>
            Коротко о себе
        </legend>
        <div class="searchItem">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">В каком регионе родились: </span>

            <div class="searchItemBody">
                <input name="regionOfBorn" type="text" size="42" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$regionOfBorn'";?>>
            </div>
        </div>
        <div class="searchItem">
            <div class="required">
                <?php if ($typeTenant) {echo "*";} ?>
            </div>
            <span class="searchItemLabel">Родной город, населенный пункт: </span>

            <div class="searchItemBody">
                <input name="cityOfBorn" type="text" size="36" <?php if ($typeTenant) {echo "validations='validate[required]'";} echo "value='$cityOfBorn'";?>>
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
<?php if($typeTenant):?>
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
                    <option value="no" <?php if ($adjacentRooms == "no") echo "selected";?>>только изолированные</option>
                </select>
            </div>
        </div>
        <div class="searchItem">
            <span class="searchItemLabel"> Этаж: </span>

            <div class="searchItemBody">
                <select name="floor">
                    <option value="any" <?php if ($floor == "any") echo "selected";?>>любой</option>
                    <option value="not1" <?php if ($floor == "not1") echo "selected";?>>не первый</option>
                    <option value="not1notLasted" <?php if ($floor == "not1notLasted") echo "selected";?>>не первый и не последний</option>
                </select>
            </div>
        </div>
        <div>
            <input type="checkbox" value="with"
                   name="withWithoutFurniture" <?php if ($withWithoutFurniture == "with") echo "checked";?>>
            С мебелью и бытовой техникой
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
                        <input type="checkbox" name="district1"
                               value="1" <?php if ($district1 == "1") echo "checked";?>>
                        Автовокзал (южный)
                    </li>
                    <li>
                        <input type="checkbox" name="district2"
                               value="2" <?php if ($district2 == "2") echo "checked";?>>
                        Академический
                    </li>
                    <li>
                        <input type="checkbox" name="district3"
                               value="3" <?php if ($district3 == "3") echo "checked";?>>
                        Ботанический
                    </li>
                    <li>
                        <input type="checkbox" name="district4"
                               value="4" <?php if ($district4 == "4") echo "checked";?>>
                        ВИЗ
                    </li>
                    <li>
                        <input type="checkbox" name="district5"
                               value="5" <?php if ($district5 == "5") echo "checked";?>>
                        Вокзальный
                    </li>
                    <li>
                        <input type="checkbox" name="district6"
                               value="6" <?php if ($district6 == "6") echo "checked";?>>
                        Втузгородок
                    </li>
                    <li>
                        <input type="checkbox" name="district7"
                               value="7" <?php if ($district7 == "7") echo "checked";?>>
                        Горный щит
                    </li>
                    <li>
                        <input type="checkbox" name="district8"
                               value="8" <?php if ($district8 == "8") echo "checked";?>>
                        Елизавет
                    </li>
                    <li>
                        <input type="checkbox" name="district9"
                               value="9" <?php if ($district9 == "9") echo "checked";?>>
                        ЖБИ
                    </li>
                    <li>
                        <input type="checkbox" name="district10"
                               value="10" <?php if ($district10 == "10") echo "checked";?>>
                        Завокзальный
                    </li>
                    <li>
                        <input type="checkbox" name="district11"
                               value="11" <?php if ($district11 == "11") echo "checked";?>>
                        Заречный
                    </li>
                    <li>
                        <input type="checkbox" name="district12"
                               value="12" <?php if ($district12 == "12") echo "checked";?>>
                        Изоплит
                    </li>
                    <li>
                        <input type="checkbox" name="district13"
                               value="13" <?php if ($district13 == "13") echo "checked";?>>
                        Исток
                    </li>
                    <li>
                        <input type="checkbox" name="district14"
                               value="14" <?php if ($district14 == "14") echo "checked";?>>
                        Калиновский
                    </li>
                    <li>
                        <input type="checkbox" name="district15"
                               value="15" <?php if ($district15 == "15") echo "checked";?>>
                        Кольцово
                    </li>
                    <li>
                        <input type="checkbox" name="district16"
                               value="16" <?php if ($district16 == "16") echo "checked";?>>
                        Компрессорный
                    </li>
                    <li>
                        <input type="checkbox" name="district17"
                               value="17" <?php if ($district17 == "17") echo "checked";?>>
                        Лечебный
                    </li>
                    <li>
                        <input type="checkbox" name="district18"
                               value="18" <?php if ($district18 == "18") echo "checked";?>>
                        Малый исток
                    </li>
                    <li>
                        <input type="checkbox" name="district19"
                               value="19" <?php if ($district19 == "19") echo "checked";?>>
                        Нижнеисетский
                    </li>
                    <li>
                        <input type="checkbox" name="district20"
                               value="20" <?php if ($district20 == "20") echo "checked";?>>
                        Парковый
                    </li>
                    <li>
                        <input type="checkbox" name="district21"
                               value="21" <?php if ($district21 == "21") echo "checked";?>>
                        Пионерский
                    </li>
                    <li>
                        <input type="checkbox" name="district22"
                               value="22" <?php if ($district22 == "22") echo "checked";?>>
                        Птицефабрика
                    </li>
                    <li>
                        <input type="checkbox" name="district23"
                               value="23" <?php if ($district23 == "23") echo "checked";?>>
                        Рудный
                    </li>
                    <li>
                        <input type="checkbox" name="district24"
                               value="24" <?php if ($district24 == "24") echo "checked";?>>
                        Садовый
                    </li>
                    <li>
                        <input type="checkbox" name="district25"
                               value="25" <?php if ($district25 == "25") echo "checked";?>>
                        Северка
                    </li>
                    <li>
                        <input type="checkbox" name="district26"
                               value="26" <?php if ($district26 == "26") echo "checked";?>>
                        Семь ключей
                    </li>
                    <li>
                        <input type="checkbox" name="district27"
                               value="27" <?php if ($district27 == "27") echo "checked";?>>
                        Сибирский тракт
                    </li>
                    <li>
                        <input type="checkbox" name="district28"
                               value="28" <?php if ($district28 == "28") echo "checked";?>>
                        Синие камни
                    </li>
                    <li>
                        <input type="checkbox" name="district29"
                               value="29" <?php if ($district29 == "29") echo "checked";?>>
                        Совхозный
                    </li>
                    <li>
                        <input type="checkbox" name="district30"
                               value="30" <?php if ($district30 == "30") echo "checked";?>>
                        Сортировка новая
                    </li>
                    <li>
                        <input type="checkbox" name="district31"
                               value="31" <?php if ($district31 == "31") echo "checked";?>>
                        Сортировка старая
                    </li>
                    <li>
                        <input type="checkbox" name="district32"
                               value="32" <?php if ($district32 == "32") echo "checked";?>>
                        Уктус
                    </li>
                    <li>
                        <input type="checkbox" name="district33"
                               value="33" <?php if ($district33 == "33") echo "checked";?>>
                        УНЦ
                    </li>
                    <li>
                        <input type="checkbox" name="district34"
                               value="34" <?php if ($district34 == "34") echo "checked";?>>
                        Уралмаш
                    </li>
                    <li>
                        <input type="checkbox" name="district35"
                               value="35" <?php if ($district35 == "35") echo "checked";?>>
                        Химмаш
                    </li>
                    <li>
                        <input type="checkbox" name="district36"
                               value="36" <?php if ($district36 == "36") echo "checked";?>>
                        Центр
                    </li>
                    <li>
                        <input type="checkbox" name="district37"
                               value="37" <?php if ($district37 == "37") echo "checked";?>>
                        Чермет
                    </li>
                    <li>
                        <input type="checkbox" name="district38"
                               value="38" <?php if ($district38 == "38") echo "checked";?>>
                        Чусовское озеро
                    </li>
                    <li>
                        <input type="checkbox" name="district39"
                               value="39" <?php if ($district39 == "39") echo "checked";?>>
                        Шабровский
                    </li>
                    <li>
                        <input type="checkbox" name="district40"
                               value="40" <?php if ($district40 == "40") echo "checked";?>>
                        Шарташ
                    </li>
                    <li>
                        <input type="checkbox" name="district41"
                               value="41" <?php if ($district41 == "41") echo "checked";?>>
                        Шарташский рынок
                    </li>
                    <li>
                        <input type="checkbox" name="district42"
                               value="42" <?php if ($district42 == "42") echo "checked";?>>
                        Широкая речка
                    </li>
                    <li>
                        <input type="checkbox" name="district43"
                               value="43" <?php if ($district43 == "43") echo "checked";?>>
                        Шувакиш
                    </li>
                    <li>
                        <input type="checkbox" name="district44"
                               value="44" <?php if ($district44 == "44") echo "checked";?>>
                        Эльмаш
                    </li>
                    <li>
                        <input type="checkbox" name="district45"
                               value="45" <?php if ($district45 == "45") echo "checked";?>>
                        Юго-запад
                    </li>
                    <li>
                        <input type="checkbox" name="district46"
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
                <option value="nonFamilyPair" <?php if ($withWho == "nonFamilyPair") echo "selected";?>>несемейная пара</option>
                <option value="withFriends" <?php if ($withWho == "withFriends") echo "selected";?>>со знакомыми</option>
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
                <option value="childrenUnder4" <?php if ($children == "childrenUnder4") echo "selected";?>>с детьми младше 4-х лет</option>
                <option value="childrenOlder4" <?php if ($children == "childrenOlder4") echo "selected";?>>с детьми старше 4-х лет</option>
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
            <input type="text" name="period" size="18" maxlength="80" validations="validate[required]" <?php echo "value='$period'";?>>
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
        <input type="checkbox" name="lic" value="yes"> Я принимаю условия <a href="#">лицензионного соглашения</a>
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
