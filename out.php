<?php

    // Единственная цель страницы - "разавторизовать" пользователя
    if (!isset($_SESSION)) {
        session_start();
    }
    //$id = $_SESSION['id'];
    //mysql_query("UPDATE users SET online=0 WHERE id='$id'"); //обнуляем поле online, говорящее, что пользователь вышел с сайта (пригодится в будущем)

    unset($_SESSION['id']); //удаляем переменную сессии
    $_SESSION = array();
    session_unset();
    session_destroy();
    SetCookie("login", "", time() - 3600, '/'); //удаляем cookie с логином
    SetCookie("password", "", time() - 3600, '/'); //удаляем cookie с паролем
    header("Location: index.php"); //перенаправляем на главную страницу сайта
?>