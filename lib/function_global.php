<?php
function registrationCorrect()
{
    $errors = array();

    global $name, $secondName, $surname, $sex, $nationality, $birthday, $login, $password, $telephon, $email, $fileUploadId, $currentStatusEducation, $almamater, $speciality, $kurs, $ochnoZaochno, $yearOfEnd, $placeOfWork, $workPosition, $regionOfBorn, $cityOfBorn, $shortlyAboutMe, $vkontakte, $odnoklassniki, $facebook, $twitter, $typeOfObject, $adjacentRooms, $floor, $minCost, $maxCost, $pledge, $withWho, $liksToFriends, $children, $howManyChildren, $animals, $howManyAnimals, $period, $additionalDescriptionOfSearch;

    if ($name == "") $errors[] = 'Укажите имя';
    if ($secondName == "") $errors[] = 'Укажите отчество';
    if ($surname == "") $errors[] = 'Укажите фамилию';
    if ($sex == "0") $errors[] = 'Укажите пол';
    if ($nationality == "0") $errors[] = 'Укажите национальность';
    if ($birthday == "") $errors[] = 'Укажите дату рождения';
    //if (!preg_match('/^([a-zA-Z0-9])(\w|-|_)+([a-z0-9])$/is', $_POST['login'])) $errors[] = 'Неправильный формат даты рождения, должен быть: дд.мм.гггг';

    if ($login == "") $errors[] = 'Укажите логин'; //не пусто ли поле логина
    $rez = mysql_query("SELECT * FROM users WHERE login='".$login."'");
    if (@mysql_num_rows($rez) != 0) $errors[] = 'Пользователь с таким логином уже существует, укажите другой логин'; // проверка на существование в БД такого же логина
    //if (!preg_match('/^([a-zA-Z0-9])(\w|-|_)+([a-z0-9])$/is', $_POST['login'])) return false; // соответствует ли логин регулярному выражению
    if ($password == "") $errors[] = 'Укажите пароль'; //не пусто ли поле пароля
    //if (!preg_match('/^([a-zA-Z0-9])(\w|-|_)+([a-z0-9])$/is', $_POST['password'])) return false; // соответствует ли пароль регулярному выражению


    if ($telephon == "") $errors[] = 'Укажите контактный (мобильный) телефон';
    if ($email == "") $errors[] = 'Укажите e-mail';
    if ($fileUploadId == "") $errors[] = 'Перезагрузите браузер, пожалуйста, возникла ошибка при формировании формы для загрузки фотографий';
    if ($currentStatusEducation == "0") $errors[] = 'Укажите Ваше образование (текущий статус)';
    if ($almamater == "") $errors[] = 'Укажите учебное заведение';
    if ($speciality == "") $errors[] = 'Укажите специальность';
    if ($kurs == "") $errors[] = 'Укажите курс обучения';
    if ($ochnoZaochno == "0") $errors[] = 'Укажите форму обучения (очная, заочная)';
    if ($yearOfEnd == "") $errors[] = 'Укажите e-mail';
    if ($placeOfWork == "") $errors[] = 'Укажите e-mail';
    if ($workPosition == "") $errors[] = 'Укажите e-mail';
    if ($regionOfBorn == "") $errors[] = 'Укажите e-mail';
    if ($cityOfBorn == "") $errors[] = 'Укажите e-mail';
    if ($shortlyAboutMe == "") $errors[] = 'Укажите e-mail';
    if ($vkontakte == "") $errors[] = 'Укажите e-mail';
    if ($odnoklassniki == "") $errors[] = 'Укажите e-mail';
    if ($facebook == "") $errors[] = 'Укажите e-mail';
    if ($twitter == "") $errors[] = 'Укажите e-mail';
    if ($typeOfObject == "") $errors[] = 'Укажите e-mail';
    if ($adjacentRooms == "") $errors[] = 'Укажите e-mail';
    if ($floor == "") $errors[] = 'Укажите e-mail';
    if ($minCost == "") $errors[] = 'Укажите e-mail';
    if ($maxCost == "") $errors[] = 'Укажите e-mail';
    if ($pledge == "") $errors[] = 'Укажите e-mail';
    if ($withWho == "") $errors[] = 'Укажите e-mail';
    if ($liksToFriends == "") $errors[] = 'Укажите e-mail';
    if ($children == "") $errors[] = 'Укажите e-mail';
    if ($howManyChildren == "") $errors[] = 'Укажите e-mail';
    if ($animals == "") $errors[] = 'Укажите e-mail';
    if ($howManyAnimals == "") $errors[] = 'Укажите e-mail';
    if ($period == "") $errors[] = 'Укажите e-mail';
    if ($additionalDescriptionOfSearch == "") $errors[] = 'Укажите e-mail';



    //if ($_POST['lic'] != "ok") return false; //приняты ли правила
    //if (!preg_match('/^([a-z0-9])(\w|[.]|-|_)+([a-z0-9])@([a-z0-9])([a-z0-9.-]*)([a-z0-9])([.]{1})([a-z]{2,4})$/is', $_POST['mail'])) return false; //соответствует ли поле e-mail регулярному выражению
    //if (strlen($_POST['password']) < 5) return false; //не меньше ли 5 символов длина пароля


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