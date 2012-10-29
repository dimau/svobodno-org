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

