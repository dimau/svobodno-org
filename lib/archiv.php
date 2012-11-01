<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dimau
 * Date: 29.10.12
 * Time: 11:28
 * To change this template use File | Settings | File Templates.
 */

    // Функция выполняет запросы к БД
    function executeSQL($DBlink, $request, $paramsType, $paramsArr) {

        $stmt = mysqli_prepare($DBlink, $request);
        if ($stmt) {

            // Подготовим массив для передачи в mysqli_stmt_bind_param
            $arr = array($stmt, $paramsType);
            $arr = array_merge($arr, $paramsArr);

            call_user_func_array('mysqli_stmt_bind_param', $arr);
            mysqli_stmt_execute($stmt);
            $res = mysqli_affected_rows($DBlink);
            mysqli_stmt_close($stmt);
        }

        return $res;
    }

    /**********************************************************************************
     * БАЗА ДАННЫХ
     *********************************************************************************/

    // Получить результаты выполнения SQL запроса SELECT в виде массива ассоциированных массивов
    function getResultSQLSelect($DBlink, $request) {
        $res = mysqli_query($DBlink, mysqli_real_escape_string($DBlink, $request));
        if ($res != FALSE) {
            $value = mysqli_fetch_all($res, MYSQLI_ASSOC); // Получаем массив массивов, каждый из которых содержит параметры отдельной строки БД
        } else {
            $value = array();
            // TODO: сообщить в лог об ошибке обращения к БД!
        }
        if ($res != FALSE) mysqli_free_result($res); // Очищаем занятую память

        return $value;
    }