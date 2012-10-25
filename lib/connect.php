<?php
    @mysql_connect("localhost", "dimau1_dimau", "udvudv") or die ("Ошибка подключения к базе данных");
    mysql_query('SET NAMES utf8');
    @mysql_select_db("dimau1_homes");
?>