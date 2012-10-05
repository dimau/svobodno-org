<?php

    include_once 'connect.php'; //подключаемся к БД
    include_once 'function_global.php'; //подключаем файл с глобальными функциями

    // Вспомогательная функция отказа в доступе
    function accessDenied()
    {
        header('Content-Type: text/xml; charset=UTF-8');
        echo "<xml><span access='denied'></span></xml>";
        exit();
    }

    /*************************************************************************************
     * Проверяем, имеет ли право данный пользователь смотреть контакты собственника
     *
     * Правила следующие:
     *
     * Неавторизованный пользователь не имеет права смотреть чьи-либо контакты
     * Не арендатор не имеет права смотреть контакты
     * Если собственник снял с публикации объект, то его контакты в контексте этого объекта становятся недоступны
     ************************************************************************************/

    // Если пользователь не авторизован, то он не сможет посмотреть ничьи контакты
    $userId = login();
    if (!$userId) {
        accessDenied();
    }

    // Проверяем, является ли пользователь арендатором
    $rez = mysql_query("SELECT typeTenant FROM users WHERE id = '" . $userId . "'");
    if ($rez != FALSE) {
        $row = mysql_fetch_assoc($rez);
        if ($row == FALSE || $row['typeTenant'] != "true") accessDenied();
    }

    // Проверяем, что объявление еще имеет статус "опубликовано". По ходу получим также список id арендаторов по этому объявлению, он позже может нам пригодиться
    $propertyId = 0;
    if (isset($_POST['propertyId'])) $propertyId = $_POST['propertyId']; else accessDenied();
    $rezProperty = mysql_query("SELECT userId, status, visibleUsersId FROM property WHERE id = '" . $propertyId . "'");
    if ($rezProperty != FALSE) $rowProperty = mysql_fetch_assoc($rezProperty); else accessDenied();

    if ($rowProperty == FALSE || $rowProperty['status'] != "опубликовано") accessDenied();

    /*************************************************************************************
     * Если все хорошо - Записываем id объявления в БД, в поисковый запрос пользователя - тем самым фиксируем, что он "заинтересовался" данным объявлением
     *************************************************************************************/

    $rezSearchRequest = mysql_query("SELECT interestingPropertysId FROM searchRequests WHERE userId = '" . $userId . "'");
    if ($rezSearchRequest != FALSE) $rowSearchRequest = mysql_fetch_assoc($rezSearchRequest); else accessDenied();
    if ($rowSearchRequest == FALSE) accessDenied();

    $interestingPropertysId = unserialize($rowSearchRequest['interestingPropertysId']);
    if (!in_array($propertyId, $interestingPropertysId)) $interestingPropertysId[] = $propertyId;
    $interestingPropertysId = serialize($interestingPropertysId);

    // Сохраняем новые изменения в БД в таблицу поисковых запросов
    $rez = mysql_query("UPDATE searchRequests SET
            interestingPropertysId = '" . $interestingPropertysId . "'
            WHERE userId = '" . $userId . "'");
    if (!$rez) accessDenied();

    /*************************************************************************************
     * Записываем id арендатора в БД, в объявление - тем самым фиксируем, что пользователь "заинтересовался" данным объявлением
     *************************************************************************************/

    // Дополняем список id потенциальных арендаторов данного объявления текущим, если его еще в этом списке нет
    $visibleUsersId = unserialize($rowProperty['visibleUsersId']);
    if (!in_array($userId, $visibleUsersId)) $visibleUsersId[] = $userId; //TODO: подстраховаться в будущем от ошибок, которые могут стереть все данные в этой ячейке, если вдруг мы достали из нее не массив
    $visibleUsersId = serialize($visibleUsersId);

    // Сохраняем новые изменения в БД в таблицу по данному объекту недвижмости
    $rez = mysql_query("UPDATE property SET
            visibleUsersId = '" . $visibleUsersId . "'
            WHERE id = '" . $propertyId . "'");

    //TODO: нужно еще новость для собственника сформировать о появлении нового арендатора по его объявлению

    /*************************************************************************************
     * Если все хорошо - возвращаем основной контент (контакты собственника)
     *************************************************************************************/

    // Получаем необходимые данные из БД
    $rezTargetUsers = mysql_query("SELECT id, name, secondName, telephon FROM users WHERE id = '" . $rowProperty['userId'] . "'");
    if ($rezTargetUsers != FALSE) $rowTargetUsers = mysql_fetch_assoc($rezTargetUsers); else accessDenied();
    if ($rowTargetUsers == FALSE) accessDenied();

    // Приводим идентификатор целевого пользователя к "защищенному виду", который и передаем на страницу
    $targetUserId = $rowTargetUsers['id'] * 5 + 2;

    // Непосредственный возврат данных
    header('Content-Type: text/xml; charset=UTF-8');
    echo '<xml>';
    echo "<span access='successful' name='" . $rowTargetUsers['name'] . "' secondName = '" . $rowTargetUsers['secondName'] . "' telephon = '" . $rowTargetUsers['telephon'] . "' id = '" . $targetUserId . "'></span>";
    echo '</xml>';

?>