<?php

    // Инициализируем используемые в шаблоне переменные

    $userIsLoggedIn = $dataArr['isLoggedIn'];

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
            // Задаем первоначальные ширины пунктов меню в % в зависимости от того, авторизован пользователь или нет (чтобы при загрузке пункты меню выглядели более-менее равномерно распределенными)
            if ($userIsLoggedIn) {
                $width = array(13.08, 32.71, 30.84, 23.37); // ширины для каждого пункта меню определены в соответствии с количеством букв в каждом из них
            } else {
                $width = array(18.07, 42.69, 39.24, 0); //
            }

            // Элемент для выравнивания. С помощью JS при загрузке страницы и при изменении ее размеров всем сепараторам присвоим одинаковую ширину, которая заполнит расстояния между пунктами меню
            echo "<li class='left separator'></li>";

            if ($_SERVER['PHP_SELF'] == "/index.php") {
                echo ("<li class='selected choice' style='width:" . $width[0] . "%'><span>Главная</span>");
            } else {
                echo ("<li class='choice' style='width:" . $width[0] . "%'><a href='index.php'>Главная</a>");
            }

            echo "<li class='separator'></li>";

            if ($_SERVER['PHP_SELF'] == "/search.php") {
                echo ("<li class='selected choice' style='width:" . $width[1] . "%'><span>Найти недвижимость</span>");
            } else {
                echo ("<li class='choice' style='width:" . $width[1] . "%'><a href='search.php'>Найти недвижимость</a>");
            }

            echo "<li class='separator'></li>";

            if ($_SERVER['PHP_SELF'] == "/forowner.php") { // TODO: поменять ссылку, на которую нужно переходить forowner
                echo ("<li class='selected choice' style='width:" . $width[2] . "%'><span>Подать объявление</span>");
            } else {
                echo ("<li class='choice' style='width:" . $width[2] . "%'><a href='forowner.php'>Подать объявление</a>"); //TODO: также поменять ссылку forowner
            }

            if ($userIsLoggedIn) echo "<li class='separator'></li>"; else echo "<li class='right separator'></li>";

            if ($userIsLoggedIn) { // Пункт меню "Сообщения" выдается только авторизованным пользователям
                if ($_SERVER['PHP_SELF'] == "/fortenant.php") { // TODO: поменять ссылку, на которую нужно переходить fortenant - cltkfnm c gjvjom. JS правильное выделение ссылки при нахождении на вкладке новости
                    echo ("<li class='selected choice' style='width:" . $width[3] . "%'><span>Сообщения (<span class='amountOfNewMessages'>15</span>)</span>"); // TODO: научиться рассчитывать количество сообщений
                } else {
                    echo ("<li class='choice' style='width:" . $width[3] . "%'><a href='personal.php?tabsId=2'>Сообщения (<span class='amountOfNewMessages'>15</span>)</a>"); // TODO: научиться рассчитывать количество сообщений
                }

                echo "<li class='right separator'></li>";
            }
            ?>
        </ul>
        <div class="clearBoth"></div>
    </div>
    <div class="iconBox"></div>
    <div class="enter">
        <?php
        if ($userIsLoggedIn == FALSE) {
            if ($_SERVER['PHP_SELF'] == "/registration.php") {
                echo ("<span>Регистрация</span><br>");
            } else {
                echo ("");
            }

            if ($_SERVER['PHP_SELF'] == "/login.php") {
                echo ("<span>Вход</span><br>");
            } else {
                echo ("");
            }
        } else {
            if ($_SERVER['PHP_SELF'] == "/personal.php") {
                echo ("<span>Личный кабинет</span><br>");
            } else {
                echo ("");
            }
            ?>
            <a href="../out.php">Выйти</a>
            <br>
            <?php } ?>
    </div>
</div><!-- /end.header -->