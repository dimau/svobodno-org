<?php

    /******
     * Скрипт возвращает максимум 20 значений.
     * Скрипт возвращает значения в формате JSON с помощью команды json_encode!
     *
     * $typeOperation == 'FullBalloons' - вернуть содержимое баллунов для объявлений с данными id
     * $typeOperation == 'FullData' - вернуть содержимое баллунов, а также HTML код для таблиц с краткими и полными параметрами объявлений с данными id
     *****/

    include_once 'connect.php'; //подключаемся к БД
    include_once 'function_global.php'; //подключаем файл с глобальными функциями
    include_once 'function_searchResult.php'; // Подключаем файл с функциями по HTML оформлению результатов поиска

    // Инициализируем переменные для работы
    $propertyId = array(); // Массив id объектов, по которым нужно получить и выслать данные
    $typeOperation = ""; // Тип операции - либо получить полные данные для баллуна, либо получить полные данные для всех 3-х форм представления результатов поиска: баллун + таблица с краткими параметрами + таблица с полными параметрами
    $number = 0; // С какого номера начать нумеровать выдаваемые объекты недвижимости. Точнее при $number = 0, нужно начать нумеровать с 1 (с последующего номера)

    // Получаем запрос на предоставление данных
    if (isset($_POST['propertyId'])) $propertyId = $_POST['propertyId'];
    if (isset($_POST['typeOperation'])) $typeOperation = $_POST['typeOperation'];
    if (isset($_POST['number'])) $number = $_POST['number'];

    /*************************************************************************************
     * Если пользователь авторизован - получим его id
     ************************************************************************************/

    $userId = login();

    /*************************************************************************************
     * Получаем данные из БД по запрашиваемым объектам недвижимости (если они еще сдаются)
     ************************************************************************************/

    // Собираем строку WHERE для поискового запроса к БД по полным данным для не более чем 20-ти первых объектов
    $strWHERE = "";
    if (count($propertyId) < 20) $limit = count($propertyId); else $limit = 20;
    if ($limit != 0) {
        $strWHERE = " (";
        for ($i = 0; $i < $limit; $i++) {
            $strWHERE .= " id = '" . $propertyId[$i] . "'";
            if ($i < $limit - 1) $strWHERE .= " OR";
        }
        $strWHERE .= ") AND (status = 'опубликовано')";
    }

    // Собираем и выполняем поисковый запрос - получаем подробные сведения по не более чем 20-ти первым в списке объявлениям, которые и вернем клиенту, но только после их оформления в HTML
    $propertyFullArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
    if ($strWHERE != "") {
        // Ограничиваем количество 20 объявлениями, чтобы запрос не проходил таблицу до конца, когда выделит нужные нам 20 объектов
        $rezProperty = mysql_query("SELECT * FROM property WHERE" . $strWHERE . " LIMIT 20");
        if ($rezProperty != FALSE) {
            for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
                $row = mysql_fetch_assoc($rezProperty);
                if ($row != false) $propertyFullArr[] = $row;
            }
        }
    }

    // Необходимо упорядочить полученные результаты из БД в том порядке, в котором в массиве $_POST['propertyId'] были указаны соответствующие id объектов недвижимости
    $tempArr = array();
    // считаем лимит
    for ($i = 0; $i < $limit; $i++) {
        foreach ($propertyFullArr as $value) {
            if ($propertyId[$i] == $value['id']) {
                $tempArr[$i] = $value;
                break;
            }
        }
    }
    $propertyFullArr = $tempArr;

    // Получаем идентификаторы избранных объявлений для данного пользователя
    $favoritesPropertysId = array();
    if ($userId != FALSE) {
        $rowUsers = FALSE;
        $rezUsers = mysql_query("SELECT favoritesPropertysId FROM users WHERE id = '" . $userId . "'");
        if ($rezUsers != FALSE) $rowUsers = mysql_fetch_assoc($rezUsers);
        if ($rowUsers != FALSE) $favoritesPropertysId = unserialize($rowUsers['favoritesPropertysId']);
    }

    /*************************************************************************************
     * Оформляем в HTML данные по полученным ранее объектам недвижимости
     ************************************************************************************/

    // Инициализируем переменные, в которые сложим HTML блоки каждого из объявлений.
    $arrayOfBalloonList = array(); // Массив с содержимым баллунов для всех объектов, по которым нужно предоставить сведения
    $matterOfShortList = ""; // Содержимое таблицы объявлений с краткими данными по каждому из них
    $matterOfFullParametersList = ""; // Содержимое таблицы объявлений с подробными данными по каждому из них

    // Начинаем перебор каждого из полученных ранее объявлений для получения красивых HTML-блоков, которые и нужно будет вернуть клиенту
    for ($i = 0; $i < count($propertyFullArr); $i++) {

        // Увеличиваем счетчик объявлений при каждом проходе
        $number++;

        // Получаем фотографии объекта
        $propertyFotosArr = array(); // Массив, в который запишем массивы, каждый из которых будет содержать данные по 1 фотке объекта
        $rezPropertyFotos = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $propertyFullArr[$i]['id'] . "'");
        if ($rezPropertyFotos != FALSE) {
            for ($j = 0; $j < mysql_num_rows($rezPropertyFotos); $j++) {
                $propertyFotosArr[] = mysql_fetch_assoc($rezPropertyFotos);
            }
        }

        /************** Готовим СОДЕРЖИМОЕ полного баллуна **************/
        // Полученный HTML текст складываем в "копилочку"
        $arrayOfBalloonList[$propertyFullArr[$i]['id']] = getFullBalloonHTML($propertyFullArr[$i], $propertyFotosArr, $userId, $favoritesPropertysId);

        if ($typeOperation == "FullData") {

            /***** Готовим блок shortList таблицы для данного объекта недвижимости *****/
            // Полученный HTML текст складываем в "копилочку"
            $matterOfShortList .= getShortListItemHTML($propertyFullArr[$i], $propertyFotosArr, $userId, $favoritesPropertysId, $number);

            /***** Готовим блок fullParametersList таблицы для данного объекта недвижимости *****/
            // Полученный HTML текст складываем в "копилочку"
            $matterOfFullParametersList .= getFullParametersListItemHTML($propertyFullArr[$i], $propertyFotosArr, $userId, $favoritesPropertysId, $number);

        }

    }

    /*************************************************************************************
     * Возвращаем данные по всем объявлениям в формате JSON
     *
     * Возвращается объект (на клиенте этот объект скорее всего называтеся - data), который содержит:
     * data.arrayOfBalloonList - объект, содержащий пары - {идентификтатор объекта недвижимости: HTML код баллуна, ...}
     * data.matterOfShortList - HTML код строк таблицы, которые нужно добавить к #shortListOfRealtyObjects
     * data.matterOfFullParametersList - HTML код строк таблицы, которые нужно добавить к #fullParametersListOfRealtyObjects
     ************************************************************************************/

    echo json_encode(array('arrayOfBalloonList' => $arrayOfBalloonList,'matterOfShortList' => $matterOfShortList, 'matterOfFullParametersList' => $matterOfFullParametersList));
    exit();

?>