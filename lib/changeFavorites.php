<?php

    include_once 'connect.php'; //подключаемся к БД
    include_once 'function_global.php'; //подключаем файл с глобальными функциями

    // Вспомогательная функция отказа в доступе
    function accessDenied() {
        header('Content-Type: text/xml; charset=UTF-8');
        echo "<xml><span status='denied'></span></xml>";
        exit();
    }

     // Проверяем, залогинен ли пользователь, если нет - то отказываем в доступе
    $userId = login();
    if (!$userId) {
        accessDenied();
    }

    // Получаем идентификатор объявления, которое пользователь хочет добавить/удалить в Избранное и действие, которое нужно совершить с объявлением (добавить в избранное или удалить)
    $propertyId = 0;
    if (isset($_POST['propertyId'])) $propertyId = $_POST['propertyId']; else accessDenied();
    $action = "";
    if (isset($_POST['action'])) $action = $_POST['action']; else accessDenied();

    // Если все хорошо - получаем список избранных объявлений данного пользователя
    $rowUsers = FALSE;
    $rezUsers = mysql_query("SELECT favoritesPropertysId FROM users WHERE id = '" . $userId . "'");
    if ($rezUsers != FALSE) $rowUsers = mysql_fetch_assoc($rezUsers); else accessDenied();
    if ($rowUsers == FALSE) accessDenied();
    $favoritesPropertysId = unserialize($rowUsers['favoritesPropertysId']);

    // Если все хорошо и требуемое действие = Добавить в избранное, то записываем id объявления в БД, в поле favoritesPropertysId пользователя - тем самым фиксируем, что он добавил данное объявление к себе в избранные
    if ($action == "addToFavorites") {
        if (!in_array($propertyId, $favoritesPropertysId)) {
            $favoritesPropertysId[] = $propertyId;
            $favoritesPropertysId = serialize($favoritesPropertysId);

            // Сохраняем новые изменения в БД в таблицу поисковых запросов
            $rez = mysql_query("UPDATE users SET
            favoritesPropertysId = '" . $favoritesPropertysId . "'
            WHERE id = '" . $userId . "'");
            if (!$rez) accessDenied();
        }
    }

    // Если все хорошо и требуемое действие = Удалить из избранного, то удаляем id объявления из БД, из поля favoritesPropertysId пользователя
    if ($action == "removeFromFavorites") {
        if (in_array($propertyId, $favoritesPropertysId)) {
            $arrForDelete = array($propertyId);
            $favoritesPropertysId = array_diff($favoritesPropertysId, $arrForDelete);
            $favoritesPropertysId = serialize($favoritesPropertysId);

            // Сохраняем новые изменения в БД в таблицу поисковых запросов
            $rez = mysql_query("UPDATE users SET
            favoritesPropertysId = '" . $favoritesPropertysId . "'
            WHERE id = '" . $userId . "'");
            if (!$rez) accessDenied();
        }
    }

    /*************************************************************************************
     * Если все хорошо - возвращаем положительный статус выполнения операции
     *************************************************************************************/

    header('Content-Type: text/xml; charset=UTF-8');
    echo "<xml><span status='successful'></span></xml>";

?>