<?php
/* Единственная цель страницы - гарантированно "разавторизовать" пользователя */

if (!isset($_SESSION)) {
    session_start();
}

unset($_SESSION['id']); //удаляем переменную сессии
$_SESSION = array();
session_unset();
session_destroy();
SetCookie("login", "", time() - 3600, '/'); //удаляем cookie с логином
SetCookie("password", "", time() - 3600, '/'); //удаляем cookie с паролем
header("Location: index.php"); //перенаправляем на главную страницу сайта
exit();