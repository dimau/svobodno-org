<?php
// $typeOfValidation = registration - режим проверки при поступлении данных на регистрацию пользователя (включает в себя проверки параметров профиля и поискового запроса как для арендатора, так и для собственника)
// $typeOfValidation = createSearchRequest - режим проверки при потуплении команды на создание поискового запроса (нет проверки данных поисковой формы, проверка параметров профиля как у арендатора)
// $typeOfValidation = validateSearchRequest - режим проверки указанных пользователем параметров поиска в совокупности с данными Профиля (причем вне зависимости от того, является ли пользователь арендатором, проверка осуществляется как будто бы является, так как он желает стать арендатором, формируя поисковый запрос)
// $typeOfValidation = validateProfileParameters - режим проверки отредактированных пользователем данных Профиля (учитывается, является ли пользователь арендатором, или собственником)
function userDataCorrect($typeOfValidation)
{
    // Подготовим массив для сохранения сообщений об ошибках
    $errors = array();

    // Получаем переменные, содержащие данные пользователя, для проверки
    global $typeTenant, $typeOwner, $name, $secondName, $surname, $sex, $nationality, $birthday, $login, $oldLogin, $password, $telephon, $email, $fileUploadId, $currentStatusEducation, $almamater, $speciality, $kurs, $ochnoZaochno, $yearOfEnd, $notWorkCheckbox, $placeOfWork, $workPosition, $regionOfBorn, $cityOfBorn, $vkontakte, $odnoklassniki, $facebook, $twitter, $minCost, $maxCost, $pledge, $period, $lic;

    // Проверки для блока "Личные данные"
    if ($name == "") $errors[] = 'Укажите имя';
    if (strlen($name) > 50) $errors[] = 'Слишком длинное имя. Можно указать не более 50-ти символов';
    if ($secondName == "") $errors[] = 'Укажите отчество';
    if (strlen($secondName) > 50) $errors[] = 'Слишком длинное отчество. Можно указать не более 50-ти символов';
    if ($surname == "") $errors[] = 'Укажите фамилию';
    if (strlen($surname) > 50) $errors[] = 'Слишком длинная фамилия. Можно указать не более 50-ти символов';
    if ($sex == "0") $errors[] = 'Укажите пол';
    if ($nationality == "0") $errors[] = 'Укажите национальность';

    if ($birthday != "") {
        if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $birthday)) $errors[] = 'Неправильный формат даты рождения, должен быть: дд.мм.гггг';
        if (substr($birthday, 0, 2) < "01" || substr($birthday, 0, 2) > "31") $errors[] = 'Проверьте дату Дня рождения (допустимо от 01 до 31)';
        if (substr($birthday, 3, 2) < "01" || substr($birthday, 3, 2) > "12") $errors[] = 'Проверьте месяц Дня рождения (допустимо от 01 до 12)';
        if (substr($birthday, 6, 4) < "1000" || substr($birthday, 6, 4) > "9999") $errors[] = 'Проверьте год Дня рождения (допустимо от 1000 до 9999)';
    } else {
        $errors[] = 'Укажите дату рождения';
    }

    if ($login == "") $errors[] = 'Укажите логин';
    if (strlen($login) > 50) $errors[] = "Слишком длинный логин. Можно указать не более 50-ти символов";
    if ($login != "" && strlen($login) < 50 && $typeOfValidation == "registration") { // Проверяем логин на занятость
        $rez = mysql_query("SELECT * FROM users WHERE login='".$login."'");
        if (mysql_num_rows($rez) != 0) $errors[] = 'Пользователь с таким логином уже существует, укажите другой логин'; // проверка на существование в БД такого же логина
    }
    if ($login != "" && strlen($login) < 50 && $typeOfValidation == "validateProfileParameters" && $oldLogin != $login) { // Проверяем новый логин на занятость
        $rez = mysql_query("SELECT * FROM users WHERE login='".$login."'");
        if (mysql_num_rows($rez) != 0) $errors[] = 'Пользователь с таким логином уже существует, укажите другой логин'; // проверка на существование в БД такого же логина
    }
    if ($password == "" && ($typeOfValidation == "registration" || $typeOfValidation == "validateProfileParameters")) $errors[] = 'Укажите пароль'; // Проверить наличие пароля при типе валидации = createSearchRequest не представляется возможным, так как он не хранится в БД

    if ($telephon != "") {
        if (!preg_match('/^[0-9]{10}$/', $telephon)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019';
    }
    else {
        $errors[] = 'Укажите контактный (мобильный) телефон';
    }

    if (($typeOfValidation == "registration" && $typeTenant == "true" && $email == "") || ($typeOfValidation == "createSearchRequest" && $email == "") || ($typeOfValidation == "validateSearchRequest" && $email == "") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true" && $email == "")) $errors[] = 'Укажите e-mail';
    if ($email != "" && !preg_match("/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/", $email)) $errors[] = 'Укажите, пожалуйста, Ваш настоящий e-mail (указанный Вами e-mail не прошел проверку формата)'; //соответствует ли поле e-mail регулярному выражению

    // Проверяем наличие хотя бы 1 фотографии пользователя
    if ($typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest") { // Валидации при попытке пользователя добавить поисковый запрос (из личного кабинета) (не при регистрации!)
        $rez = mysql_query("SELECT id FROM users WHERE login='".$login."'"); // Нужно получить id пользователя, чтобы проверить, есть ли у него хотя бы 1 фотка в БД
        $row = mysql_fetch_assoc($rez);
        $rez = mysql_query("SELECT * FROM userFotos WHERE userId='".$row['id']."'");
        if (mysql_num_rows($rez) == 0) $errors[] = 'Загрузите как минимум 1 Вашу фотографию'; // проверка на хотя бы 1 фотку
    }
    if ($typeOfValidation == "registration" && $typeTenant == "true" && $fileUploadId != "") // Эта ветка выполняется, если валидации производятся при попытке регистрации пользователем
    {
        $rez = mysql_query("SELECT * FROM tempFotos WHERE fileuploadid='".$fileUploadId."'");
        if (mysql_num_rows($rez) == 0) $errors[] = 'Загрузите как минимум 1 Вашу фотографию'; // проверка на хотя бы 1 фотку
    }
    if ($typeOfValidation == "validateProfileParameters") // Эта ветка выполняется, если валидации производятся при попытке редактирования Профайл параметров пользователя
    {
        $rez = mysql_query("SELECT id FROM users WHERE login='".$oldLogin."'"); // Нужно получить id пользователя, чтобы проверить, есть ли у него хотя бы 1 фотка в БД
        $row = mysql_fetch_assoc($rez);
        $rez1 = mysql_query("SELECT * FROM userFotos WHERE userId='".$row['id']."'");
        $rez2 = mysql_query("SELECT * FROM tempFotos WHERE fileuploadid='".$fileUploadId."'");
        if (mysql_num_rows($rez1) == 0 && mysql_num_rows($rez2) == 0) $errors[] = 'Загрузите как минимум 1 Вашу фотографию'; // проверка на хотя бы 1 фотку
    }
    if (($typeOfValidation == "registration" || $typeOfValidation == "validateProfileParameters") && $fileUploadId == "") $errors[] = 'Перезагрузите браузер, пожалуйста: возникла ошибка при формировании формы для загрузки фотографий';

    // Проверки для блока "Образование"
    if ($currentStatusEducation == "0" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше образование (текущий статус)';
    if ($almamater == "" && ($currentStatusEducation == "learningNow" || $currentStatusEducation == "finishedEducation") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите учебное заведение';
    if (isset($almamater) && strlen($almamater) > 100) $errors[] = 'Слишком длинное название учебного заведения (используйте не более 100 символов)';
    if ($speciality == "" && ($currentStatusEducation == "learningNow" || $currentStatusEducation == "finishedEducation") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите специальность';
    if (isset($speciality) && strlen($speciality) > 100) $errors[] = 'Слишком длинное название специальности (используйте не более 100 символов)';
    if ($kurs == "" && $currentStatusEducation == "learningNow" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите курс обучения';
    if (isset($kurs) && strlen($kurs) > 30) $errors[] = 'Курс. Указана слишком длинная строка (используйте не более 30 символов)';
    if ($ochnoZaochno == "0" && ($currentStatusEducation == "learningNow" || $currentStatusEducation == "finishedEducation") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите форму обучения (очная, заочная)';
    if ($yearOfEnd == "" && $currentStatusEducation == "finishedEducation" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите год окончания учебного заведения';
    if (isset($yearOfEnd) && strlen($yearOfEnd) > 20) $errors[] = 'Год окончания учебного заведения. Указана слишком длинная строка (используйте не более 20 символов)';
    if ($yearOfEnd != "" && !preg_match("/^[12]{1}[0-9]{3}$/", $yearOfEnd)) $errors[] = 'Укажите год окончания учебного заведения в формате: "гггг". Например: 2007';

    // Проверки для блока "Работа"
    if ($placeOfWork == "" && $notWorkCheckbox != "isNotWorking" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше место работы (название организации)';
    if (isset($placeOfWork) && strlen($placeOfWork) > 100) $errors[] = 'Слишком длинное наименование места работы (используйте не более 100 символов)';
    if ($workPosition == "" && $notWorkCheckbox != "isNotWorking" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Вашу должность';
    if (isset($workPosition) && strlen($workPosition) > 100) $errors[] = 'Слишком длинное название должности (используйте не более 100 символов)';

    // Проверки для блока "Коротко о себе"
    if ($regionOfBorn == "" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите регион, в котором Вы родились';
    if (isset($regionOfBorn) && strlen($regionOfBorn) > 50) $errors[] = 'Слишком длинное наименование региона, в котором Вы родились (используйте не более 50 символов)';
    if ($cityOfBorn == "" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите город (населенный пункт), в котором Вы родились';
    if (isset($cityOfBorn) && strlen($cityOfBorn) > 50) $errors[] = 'Слишком длинное наименование города, в котором Вы родились (используйте не более 50 символов)';

    // Проверки для блока "Социальные сети"
    if (strlen($vkontakte) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу Вконтакте (используйте не более 100 символов)';
    if (strlen($vkontakte) > 0 && !preg_match("/vk\.com/", $vkontakte)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу Вконтакте, либо оставьте поле пустым (ссылка должна содержать строчку "vk.com")';
    if (strlen($odnoklassniki) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Одноклассниках (используйте не более 100 символов)';
    if (strlen($odnoklassniki) > 0 && !preg_match("/www\.odnoklassniki\.ru\/profile\//", $odnoklassniki)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Одноклассниках, либо оставьте поле пустым (ссылка должна содержать строчку "www.odnoklassniki.ru/profile/")';
    if (strlen($facebook) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу на Facebook (используйте не более 100 символов)';
    if (strlen($facebook) > 0 && !preg_match("/www\.facebook\.com\/profile\.php/", $facebook)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу на Facebook, либо оставьте поле пустым (ссылка должна содержать строчку с "www.facebook.com/profile.php")';
    if (strlen($twitter) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Twitter (используйте не более 100 символов)';
    if (strlen($twitter) > 0 && !preg_match("/twitter\.com/", $twitter)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Twitter, либо оставьте поле пустым (ссылка должна содержать строчку "twitter.com")';

    // Проверки для блока "Параметры поиска"
    if ((($typeOfValidation == "registration" && $typeTenant == "true")  || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $minCost)) $errors[] = 'Неправильный формат числа в поле минимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
    if ((($typeOfValidation == "registration" && $typeTenant == "true")  || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $maxCost)) $errors[] = 'Неправильный формат числа в поле максимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
    if ((($typeOfValidation == "registration" && $typeTenant == "true")  || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $pledge)) $errors[] = 'Неправильный формат числа в поле максимальной величины залога (проверьте: только числа, не более 8 символов)';
    if ((($typeOfValidation == "registration" && $typeTenant == "true")  || $typeOfValidation == "validateSearchRequest") && $minCost > $maxCost) $errors[] = 'Минимальная стоимость аренды не может быть больше, чем максимальная. Исправьте поля, в которых указаны Ваши требования к диапазону стоимости аренды';
    if ((($typeOfValidation == "registration" && $typeTenant == "true")  || $typeOfValidation == "validateSearchRequest") && $period == "") $errors[] = 'Укажите ориентировочный срок аренды, например: долговременно (более года)';
    if (isset($period) && strlen($period) > 80) $errors[] = 'Указана слишком длинная строка в поле для ориентировочного срока проживания (используйте не более 80 символов)';

    // Проверка согласия пользователя с лицензией
    if ($typeOfValidation == "registration" && $lic != "yes") $errors[] = 'Регистрация возможна только при согласии с условиями лицензионного соглашения'; //приняты ли правила

    return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
}

//Функция для генерации случайной строки
function generateCode($length=6)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
    $code = "";

    $clen = strlen($chars) - 1;
    while (strlen($code) < $length)
    {
        $code .= $chars[mt_rand(0,$clen)];
    }

    return $code;
}

function newSession($userId)
{
    $hash = md5(generateCode(10)); // генерируем случайное 32-х значное число - идентификатор сессии
    mysql_query("UPDATE users SET user_hash='".$hash."' WHERE id='".$userId."'");
    $_SESSION['id'] = $hash; //записываем id сессии
}


function lastAct($id)
{
    $tm = time();
    mysql_query("UPDATE users SET online='$tm', last_act='$tm' WHERE id='$id'");
}

function birthdayFromDBToView($birthdayFromDB) {
    $date = substr($birthdayFromDB, 8, 2);
    $month = substr($birthdayFromDB, 5, 2);
    $year = substr($birthdayFromDB, 0, 4);
    return $date . "." . $month . "." . $year;
}

function birthdayFromViewToDB($birthdayFromView) {
    $date = substr($birthdayFromView, 0, 2);
    $month = substr($birthdayFromView, 3, 2);
    $year = substr($birthdayFromView, 6, 4);
    return $year . "." . $month . "." . $date;
}

// Функция для авторизации (входа) пользователя на сайте
function enter()
{
    $error = array(); //массив для ошибок
    if ($_POST['login'] != "" && $_POST['password'] != "") //если поля заполнены
    {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $rez = mysql_query("SELECT * FROM users WHERE login='".$login."'"); //запрашиваем строку из БД с логином, введённым пользователем
        if ($rez != false && mysql_num_rows($rez) == 1) //если нашлась одна строка, значит такой юзер существует в БД
        {
            $row = mysql_fetch_assoc($rez);
            if ($password == $row['password']) // Cравниваем указанный пользователем пароль с паролем из БД
            {
                //пишем логин и хэшированный пароль в cookie, также создаём переменную сессии
                setcookie("login", $row['login'], time() + 60*60*24*7);
                setcookie("password", md5($row['login'] . $row['password']), time() + 60*60*24*7);
                newSession($row['id']);

                lastAct($row['id']);
                return $error;
            }
            else //если пароли не совпали
            {
                $error[] = "Неверный пароль";
                return $error;
            }
        }
        else //если такого пользователя не найдено в БД
        {
            $error[] = "Неверный логин и пароль";
            return $error;
        }
    }
    else {
        $error[] = "Укажите Ваш логин и пароль";
        return $error;
    }
}


function login()
{
    // Запускаем сессию для работы с ней и готовим переменную rez
    if(!isset($_SESSION))
    {
        session_start();
    }
    $rez = false;

    if (isset($_SESSION['id'])) //если какая-то сесcия есть - проверим ее актуальность
    {
        $rez = mysql_query("SELECT * FROM users WHERE user_hash='" . $_SESSION['id'] . "'");
    }

    if ($rez != false && mysql_num_rows($rez) == 1 ) // Если текущая сессия актуальна - добавим куки, чтобы после перезапуска браузера сессия не слетала
    {
        $row = mysql_fetch_assoc($rez);

        // выдается ошибка при попытке обновить куки из header.php, так как уже начал отправляться текст странички - html
           /* setcookie("login", "", time() - 1, '/');
            setcookie("password", "", time() - 1, '/');
            setcookie("login", $row['login'], time() + 60*60*24*7, '/');
            setcookie("password", md5($row['login'] . $row['password']), time() + 60*60*24*7, '/'); */

        return true;
    }
    else // Если сессия уже потеряла актуальность или не существовала
    {
            if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) // смотрим куки, если cookie есть, то проверим их актуальность
            {
                $rez = mysql_query("SELECT * FROM users WHERE login='{$_COOKIE['login']}'"); //запрашиваем строку с искомым логином

                // чтобы избежать ошибок при вычислении row -  делаем это с проверкой переменной rez
                if ($rez != false)
                {
                    $row = mysql_fetch_assoc($rez);
                }

                if ($rez != false && mysql_num_rows($rez) == 1 && isset($row['login']) && isset($row['password']) &&  md5($row['login'] . $row['password']) == $_COOKIE['password']) //если логин и пароль нашлись в БД
                {
                    newSession($row['id']);

                    lastAct($row['id']);
                    return true;
                }
                else //если данные из cookie не подошли, то удаляем эти куки, ибо нахуй они такие нам не нужны
                {
                    setcookie("login", "", time() - 360000, '/');
                    setcookie("password", "", time() - 360000, '/');
                    return false;
                }
            }
            else // Если сессия не актуальна и куки не существуют
            {
                return false;
            }
    }
}
?>