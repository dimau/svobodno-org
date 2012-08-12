<?php

function out() {
    session_start();
    //$id = $_SESSION['id'];
    //mysql_query("UPDATE users SET online=0 WHERE id='$id'"); //обнуляем поле online, говорящее, что пользователь вышел с сайта (пригодится в будущем)

    unset($_SESSION['id']); //удаляем переменную сессии
    session_unset();
    session_destroy();
    $_SESSION = array();
    SetCookie("login", ""); //удаляем cookie с логином
    SetCookie("password", ""); //удаляем cookie с паролем
    //header("Location: ".$_SERVER['PHP_SELF']); //перенаправляем на главную страницу сайта
}

if (isset($_GET['action']) && $_GET['action'] == "out") out(); //если передана переменная action, «разавторизируем» пользователя
?>