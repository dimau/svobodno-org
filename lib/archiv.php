<?php

    /**********************************************************************************
     * БАЗА ДАННЫХ
     *********************************************************************************/

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



    // Функция возвращает массив массивов с названиями районов в городе $city - до переделки из-за PHP 5.3.3 и отсутствия mysqlnd
    public function getAllDistrictsInCity($city) {
    // Получим из БД данные ($res) по пользователю с логином = $login
    $stmt = $this->DBlink->stmt_init();
    if (($stmt->prepare("SELECT name FROM districts WHERE city=? ORDER BY name ASC") === FALSE)
        OR ($stmt->bind_param("s", $city) === FALSE)
        OR ($stmt->execute() === FALSE)
        OR (($res = $stmt->get_result()) === FALSE)
        OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
        OR ($stmt->close() === FALSE)
    ) {
        $res = array();
        // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
    }

    return $res;
}

    /**********************************************************************************
     * man.php
     *********************************************************************************/

    // Получаем список пользователей, чьей недвижимостью интересовался наш пользователь ($userId) в качестве арендатора, и чьи анкеты он имеет право смотреть
    $tenantsWithSignUpToViewRequest = array();
    if ($rez = mysql_query("SELECT interestingPropertysId FROM searchRequests WHERE userId = '" . $userId . "'")) {
        if ($row = mysql_fetch_assoc($rez)) {
            $interestingPropertysId = unserialize($row['interestingPropertysId']);

            // По каждому объекту недвижимости выясняем статус и собственника. Если статус = опубликовано, то собственника добавляем в массив ($visibleUsersIdOwners)
            if ($interestingPropertysId != FALSE && is_array($interestingPropertysId) && count($interestingPropertysId) != 0) {
                // Составляем условие запроса к БД, указывая интересующие нас id объявлений
                $selectValue = "";
                for ($i = 0; $i < count($interestingPropertysId); $i++) {
                    $selectValue .= " id = '" . $interestingPropertysId[$i] . "'";
                    if ($i < count($interestingPropertysId) - 1) $selectValue .= " OR";
                }
                // Перебираем полученные строки из таблицы, каждая из которых соответствует 1 объявлению
                if ($rez = mysql_query("SELECT userId, status FROM property WHERE " . $selectValue)) {
                    for ($i = 0; $i < mysql_num_rows($rez); $i++) {
                        if ($row = mysql_fetch_assoc($rez)) {
                            if ($row['status'] == "опубликовано") {
                                $visibleUsersIdOwners[] = $row['userId'];
                            }
                        }
                    }
                }
            }
        }
    }