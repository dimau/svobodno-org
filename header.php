<?php
include_once ('lib/connect.php'); //подключаемся к БД
include_once ('lib/function_global.php'); //подключаем файл с глобальными функциями

if (login()) //вызываем функцию login, определяющую, авторизирован юзер или нет
{
    $UID = $_SESSION['id']; //если юзер авторизирован, присвоим переменной $UID его id
}
else //если пользователь не авторизирован, то обнуляем переменную $UID
{
    $UID = false;
}

?>

<div class="header">
    <div class="nameOfServiceBox">
        <div>
            <span class='slogan'>Лучший способ аренды недвижимости!</span>
            <br>
            <span class="underslogan">Мы помогаем людям сдать в аренду и снять жилье в Екатеринбурге</span>
        </div>
    </div>
    <div class="menu">
        <ul>
            <?php
            if ($_SERVER['PHP_SELF'] == "/index.php") {
                echo ("<li class='left selected' style='width:15.5%'><span>Главная</span>");
            } else {
                echo ("<li class='left' style='width:15.5%'><a href='index.php'>Главная</a>");
            }

            if ($_SERVER['PHP_SELF'] == "/search.php") {
                echo ("<li class='selected' style='width:38%'><span>Поиск недвижимости</span>");
            } else {
                echo ("<li class='' style='width:38%'><a href='search.php'>Поиск недвижимости</a>");
            }

            if ($_SERVER['PHP_SELF'] == "/forowner.php") {
                echo ("<li class='selected' style='width:25.5%'><span>Собственнику</span>");
            } else {
                echo ("<li class='' style='width:25.5%'><a href='forowner.php'>Собственнику</a>");
            }

            if ($_SERVER['PHP_SELF'] == "/fortenant.php") {
                echo ("<li class='right selected' style='width:21%'><span>Арендатору</span>");
            } else {
                echo ("<li class='right' style='width:21%'><a href='fortenant.php'>Арендатору</a>");
            }
            ?>
        </ul>
        <div class="clearBoth"></div>
    </div>
    <div class="iconBox"></div>
    <div class="enter">
        <?php
        if ($UID == false)
        {
            if ($_SERVER['PHP_SELF'] == "/registration.php" || $_SERVER['PHP_SELF'] == "/choiceOfRole.php")
            {
                echo ("<span>Регистрация</span><br>");
            }
            else
            {
                echo ("<a href='choiceOfRole.php'>Регистрация</a><br>");
            }

            if ($_SERVER['PHP_SELF'] == "/login.php")
            {
                echo ("<span>Вход</span><br>");
            }
            else
            {
                echo ("<a href='login.php'>Вход</a><br>");
            }
        }
        else
        {
            if ($_SERVER['PHP_SELF'] == "/personal.php")
            {
                echo ("<span>Личный кабинет</span><br>");
            }
            else
            {
                echo ("<a href='personal.php'>Личный кабинет</a><br>");
            }
            ?>
            <a href="personal.php?tabsId=2">Новости: <span class="amountOfNewsInEnter">3</span></a>
            <br>
            <a href="out.php">Выйти</a>
            <br>
            <?php } ?>
    </div>
</div><!-- /end.header -->