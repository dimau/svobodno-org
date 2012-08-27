<?php
function registrationCorrect()
{
    $errors = array();

    global $typeTenant, $typeOwner, $name, $secondName, $surname, $sex, $nationality, $birthday, $login, $password, $telephon, $email, $fileUploadId, $currentStatusEducation, $almamater, $speciality, $kurs, $ochnoZaochno, $yearOfEnd, $notWorkCheckbox, $placeOfWork, $workPosition, $regionOfBorn, $cityOfBorn, $minCost, $maxCost, $pledge, $period, $lic;

    // Обязательные проверки и для арендатора и для собственника
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
    } else {
        $errors[] = 'Укажите дату рождения';
    }

    if ($login != "") {
        $rez = mysql_query("SELECT * FROM users WHERE login='".$login."'");
        if (@mysql_num_rows($rez) != 0) $errors[] = 'Пользователь с таким логином уже существует, укажите другой логин'; // проверка на существование в БД такого же логина
        if (strlen($login) > 50) $errors[] = "Слишком длинный логин. Можно указать не более 50-ти символов";
    } else {
        $errors[] = 'Укажите логин';
    }
    if ($password == "") $errors[] = 'Укажите пароль'; //не пусто ли поле пароля

    if ($telephon != "") {
        if (!preg_match('/^[0-9]{10}$/', $telephon)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019';
    }
    else {
        $errors[] = 'Укажите контактный (мобильный) телефон';
    }

    // Обязательные проверки только для арендатора
    if ($typeTenant == true) {
        if ($email != "") {
            if (!preg_match("/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/", $email)) $errors[] = 'Укажите, пожалуйста, Ваш настоящий e-mail (указанный Вами e-mail не прошел проверку формата)'; //соответствует ли поле e-mail регулярному выражению
        }
        else {
            $errors[] = 'Укажите e-mail';
        }

        if ($fileUploadId != "") {
            $rez = mysql_query("SELECT * FROM tempregfotos WHERE fileuploadid='".$fileUploadId."'");
            if (@mysql_num_rows($rez) == 0) $errors[] = 'Загрузите как минимум 1 Вашу фотографию'; // проверка на хотя бы 1 фотку
        } else {
            $errors[] = 'Перезагрузите браузер, пожалуйста: возникла ошибка при формировании формы для загрузки фотографий';
        }

        if ($currentStatusEducation == "0") $errors[] = 'Укажите Ваше образование (текущий статус)';
        if (($currentStatusEducation == 2 || $currentStatusEducation == 3) && $almamater == "") $errors[] = 'Укажите учебное заведение';
        if (($currentStatusEducation == 2 || $currentStatusEducation == 3) && $speciality == "") $errors[] = 'Укажите специальность';
        if ($currentStatusEducation == 2 && $kurs == "") $errors[] = 'Укажите курс обучения';
        if (($currentStatusEducation == 2 || $currentStatusEducation == 3) && $ochnoZaochno == "0") $errors[] = 'Укажите форму обучения (очная, заочная)';
        if ($currentStatusEducation == 3 && $yearOfEnd == "") $errors[] = 'Укажите год окончания учебного заведения';
        if ($notWorkCheckbox != "isNotWorking" && $placeOfWork == "") $errors[] = 'Укажите Ваше место работы (название организации)';
        if ($notWorkCheckbox != "isNotWorking" && $workPosition == "") $errors[] = 'Укажите Вашу должность';

        if ($regionOfBorn == "") $errors[] = 'Укажите регион, в котором Вы родились';
        if ($cityOfBorn == "") $errors[] = 'Укажите город (населенный пункт), в котором Вы родились';

        if ($minCost == "") $minCost = 0;
        if ($maxCost == "") $maxCost = 99999999;
        if ($pledge == "") $pledge = 99999999;
        if ($period == "") $errors[] = 'Укажите ориентировочный срок аренды, например: долговременно (более года)';

    }

    if ($lic != "yes") $errors[] = 'Регистрация возможна только при согласии с условиями лицензионного соглашения'; //приняты ли правила

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


// Функция для авторизации пользователя на сайте
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
            if (md5(md5($password) . $row['salt']) == $row['password']) //сравниваем хэшированный пароль из БД с хэшированными паролем, введённым пользователем и солью (алгоритм хэширования описан в предыдущей статье)
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
        $error[] = "Поля не должны быть пустыми!";
        return $error;
    }
}


function login()
{
    //ini_set("session.use_trans_sid", true); выдает ошибку при использовании, да и вроде как команда не нужна на самом деле
    if(!isset($_SESSION))
    {
        session_start();
    }
    $rez = false;

    if (isset($_SESSION['id'])) //если какая-то сесcия есть - проверим ее актуальность
    {
        $rez = mysql_query("SELECT * FROM users WHERE user_hash='{$_SESSION['id']}'");
    }

    if ($rez != false && mysql_num_rows($rez) == 1 ) // Если текущая сессия актуальна - добавим куки, чтобы после перезапуска браузера сессия не слетала
    {
        $row = mysql_fetch_assoc($rez);

            setcookie("login", "", time() - 1, '/');
            setcookie("password", "", time() - 1, '/');
            setcookie("login", $row['login'], time() + 60*60*24*7, '/');
            setcookie("password", md5($row['login'] . $row['password']), time() + 60*60*24*7, '/');

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

                if ($rez != false && mysql_num_rows($rez) == 1 && md5($row['login'] . $row['password']) == $_COOKIE['password']) //если логин и пароль нашлись в БД
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