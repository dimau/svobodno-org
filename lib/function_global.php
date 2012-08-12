<?php
function registrationCorrect()
{
    if ($_POST['login'] == "") return false; //не пусто ли поле логина
    if ($_POST['password'] == "") return false; //не пусто ли поле пароля
    //if ($_POST['mail'] == "") return false; //не пусто ли поле e-mail
    //if ($_POST['lic'] != "ok") return false; //приняты ли правила
    //if (!preg_match('/^([a-z0-9])(\w|[.]|-|_)+([a-z0-9])@([a-z0-9])([a-z0-9.-]*)([a-z0-9])([.]{1})([a-z]{2,4})$/is', $_POST['mail'])) return false; //соответствует ли поле e-mail регулярному выражению
    //if (!preg_match('/^([a-zA-Z0-9])(\w|-|_)+([a-z0-9])$/is', $_POST['login'])) return false; // соответствует ли логин регулярному выражению
    //if (strlen($_POST['password']) < 5) return false; //не меньше ли 5 символов длина пароля
    $login = $_POST['login'];
    $rez = mysql_query("SELECT * FROM users WHERE login=$login");
    if (@mysql_num_rows($rez) != 0) return false; // проверка на существование в БД такого же логина
    return true; //если выполнение функции дошло до этого места, возвращаем true
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
                setcookie("login", $row['login'], time() + 50000);
                setcookie("password", md5($row['login'] . $row['password']), time() + 50000);
                $_SESSION['id'] = $row['id']; //записываем в сессию id пользователя

                $id = $_SESSION['id'];
                lastAct($id);
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
    session_start();
    if (isset($_SESSION['id'])) //если сесcия есть
    {
        if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) //если cookie есть, то просто обновим время их жизни и вернём true
        {
            SetCookie("login", "", time() - 1, '/');
            SetCookie("password", "", time() - 1, '/');
            setcookie("login", $_COOKIE['login'], time() + 50000, '/');
            setcookie("password", $_COOKIE['password'], time() + 50000, '/');

            $id = $_SESSION['id'];
            lastAct($id);
            return true;
        }
        else //иначе добавим cookie с логином и паролем, чтобы после перезапуска браузера сессия не слетала
        {
            $rez = mysql_query("SELECT * FROM users WHERE id='{$_SESSION['id']}'"); //запрашиваем строку с искомым id

            if (mysql_num_rows($rez) == 1) //если получена одна строка
            {
                $row = mysql_fetch_assoc($rez); //записываем её в ассоциативный массив

                setcookie("login", $row['login'], time() + 50000, '/');
                setcookie("password", md5($row['login'] . $row['password']), time() + 50000, '/');

                $id = $_SESSION['id'];
                lastAct($id);
                return true;
            }
            else return false;
        }
    }
    else //если сессии нет, то проверим существование cookie. Если они существуют, то проверим их валидность по БД
    {
        if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) //если куки существуют.

        {
            $rez = mysql_query("SELECT * FROM users WHERE login='{$_COOKIE['login']}'"); //запрашиваем строку с искомым логином и паролем
            @$row = mysql_fetch_assoc($rez);

            if (@mysql_num_rows($rez) == 1 && md5($row['login'] . $row['password']) == $_COOKIE['password']) //если логин и пароль нашлись в БД
            {
                $_SESSION['id'] = $row['id']; //записываем в сесиию id
                $id = $_SESSION['id'];

                lastAct($id);
                return true;
            }
            else //если данные из cookie не подошли, то удаляем эти куки, ибо нахуй они такие нам не нужны
            {
                SetCookie("login", "", time() - 360000, '/');

                SetCookie("password", "", time() - 360000, '/');
                return false;
            }
        }
        else //если куки не существуют
        {
            return false;
        }
    }
}
?>