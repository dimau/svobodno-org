<?php

    /***************************************************************************************************************
     * Функция формирует на основе входных данных HTML код для выдачи результатов поиска
     *
     * $propertyLightArr - массив, содержащий минимум данных (координаты и id) по ВСЕМ объектам из БД, соответствующим поисковому запросу пользователя. Данный массив уже отсортирован в том порядке, в каком объявления должны идти в выдаваемых функцией результатах
     * $userId - id пользователя, либо если он не авторизован - false
     * $typeOfRequest - тип запроса. От страницы поиска (search.php): $typeOfRequest="search". От страницы Избранного (personal.php): $typeOfRequest="favorites".
     *
     * Эта функция для первых 20-ти объектов из списка формирует соответствующие полные баллуны для Яндекс карты,
     * а также строки в таблицах с краткими и подробными сведениями. Для оставшихся объектов недвижимости функция формирует
     * только HTML код для "легкого" баллуна (то есть фактически обертку для содержимого баллуна, в заголовке которой указаны
     * координаты и идентификатор объекта). Этих сведений достаточно для формирования метки на карте, остальные могут быть подгружены
     * по требованию (клик пользователя по метке).
     * Это позволяет на карте выводить метки ВСЕХ без исключения объектов недвижимости, удовлетворяющих условиям пользователя,
     * но в списке выдавать только первые 20, отсортированные по увеличению стоимости.
     * Сведения по остальным объектам подгужаются либо по клику по метке, либо при промотке списка через AJAX
     **************************************************************************************************************/

    function getSearchResultHTML($propertyLightArr, $userId = FALSE, $typeOfRequest = "search")
    {

        // Инициализируем переменную, в которую сложим весь HTML код результатов поиска
        $searchResultHTML = "";

        // Собираем строку WHERE для поискового запроса к БД по полным данным для не более чем 20-ти первых объектов
        $strWHERE = "";
        if (count($propertyLightArr) < 20) $limit = count($propertyLightArr); else $limit = 20;
        if ($limit != 0) {
            $strWHERE = " (";
            for ($i = 0; $i < $limit; $i++) {
                $strWHERE .= " id = '" . $propertyLightArr[$i]['id'] . "'";
                if ($i < $limit - 1) $strWHERE .= " OR";
            }
            $strWHERE .= ")";
        }

        // Собираем и выполняем поисковый запрос - получаем подробные сведения по не более чем 20-ти первым в списке объявлениям
        $propertyFullArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
        if ($strWHERE != "") {
            $rezProperty = mysql_query("SELECT * FROM property WHERE" . $strWHERE . " ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting LIMIT 20"); // Сортируем по стоимости аренды и ограничиваем количество 20 объявлениями, чтобы запрос не проходил таблицу до конца, когда выделит нужные нам 20 объектов
            if ($rezProperty != FALSE) {
                for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
                    $row = mysql_fetch_assoc($rezProperty);
                    if ($row != FALSE) $propertyFullArr[$row['id']] = $row; // Применение ассоциативного массива для сохранения помогает при построении
                }
            }
        }

        // Получаем идентификаторы избранных объявлений для данного пользователя
        $favoritesPropertysId = array();
        if ($userId != FALSE) {
            $rowUsers = FALSE;
            $rezUsers = mysql_query("SELECT favoritesPropertysId FROM users WHERE id = '" . $userId . "'");
            if ($rezUsers != FALSE) $rowUsers = mysql_fetch_assoc($rezUsers);
            if ($rowUsers != FALSE) $favoritesPropertysId = unserialize($rowUsers['favoritesPropertysId']);
        }

        // Инициализируем переменные, в которые сложим HTML блоки каждого из объявлений.
        $matterOfBalloonList = ""; // Содержимое невидимого блока с HTML данными для всех баллунов
        $matterOfShortList = ""; // Содержимое таблицы объявлений с краткими данными по каждому из них
        $matterOfFullParametersList = ""; // Содержимое таблицы объявлений с подробными данными по каждому из них

        // Инициализируем счетчик общего количества опубликованных объявлений в базе. Пригодится только, если по условиям поиска не найдено ни одно объявление
        $allAmountAdverts = "";

        // Инициализируем счетчик объявлений
        $number = 0;

        // Начинаем перебор каждого из полученных ранее объявлений для наполнения их данными шаблонов и получения красивых HTML-блоков для публикации на странице
        for ($i = 0; $i < count($propertyLightArr); $i++) {

            // Вычисляем идентификатор текущего объявления
            $currentPropertyId = $propertyLightArr[$i]['id'];

            // Увеличиваем счетчик объявлений при каждом проходе
            $number++;

            // Для объектов, расположенных дальше 20-ого - только минимум данных для размещения метки на карте
            if ($number > 20) {
                /************** Готовим минимальный баллун **************/
                $matterOfBalloonList .= getLightBalloonHTML($propertyLightArr[$i]);
                continue;
            }

            // Получаем фотографии объекта
            $propertyFotosArr = array(); // Массив, в который запишем массивы, каждый из которых будет содержать данные по 1 фотке объекта
            $rezPropertyFotos = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $currentPropertyId . "'");
            if ($rezPropertyFotos != FALSE) {
                for ($j = 0; $j < mysql_num_rows($rezPropertyFotos); $j++) {
                    $propertyFotosArr[] = mysql_fetch_assoc($rezPropertyFotos);
                }
            }

            // Если у нам удалось ранее получить полные сведения по объекту $propertyLightArr[$i], то отрабатываем его, в противном случае - игнорируем.
            // Кажется, что данное условие будет НЕ выполняться крайне редко - при сбоях в работе с БД, но для большей безопасности, думаю, не помещает такая проверка
            if (isset($propertyFullArr[$currentPropertyId])) {

                /************** Готовим полный баллун **************/
                // Полученный HTML текст складываем в "копилочку"
                $matterOfBalloonList .= substr(getLightBalloonHTML($propertyLightArr[$i]), 0, -6); // Получаем минимальный баллун, который служит рамкой для полного баллуна. И сразу отрезаем </div> в конце строки
                $matterOfBalloonList .= getFullBalloonHTML($propertyFullArr[$currentPropertyId], $propertyFotosArr, $userId, $favoritesPropertysId);
                $matterOfBalloonList .= "</div>"; // Возвращаем назад отрезанный ранее div

                /***** Готовим блок shortList таблицы для данного объекта недвижимости *****/
                // Полученный HTML текст складываем в "копилочку"
                $matterOfShortList .= getShortListItemHTML($propertyFullArr[$currentPropertyId], $propertyFotosArr, $userId, $favoritesPropertysId, $number);

                /***** Готовим блок fullParametersList таблицы для данного объекта недвижимости *****/
                // Полученный HTML текст складываем в "копилочку"
                $matterOfFullParametersList .= getFullParametersListItemHTML($propertyFullArr[$currentPropertyId], $propertyFotosArr, $userId, $favoritesPropertysId, $number);

            }
        }

        // Складываем элементы управления для выбора формы представления результатов выдачи (карта, список, карта + список)
        $searchResultHTML .= "
        <div class='choiceViewSearchResult'>
            <span id='expandList'><a href='#'>Список</a>&nbsp;&nbsp;&nbsp;</span><span id='listPlusMap'><a href='#'>Список +
            карта</a>&nbsp;&nbsp;&nbsp;</span><span id='expandMap'><a href='#'>Карта</a></span>
        </div>
        <div id='resultOnSearchPage' style='height: 100%;'>
            <div id='allBalloons' style='display: none;'>
        ";

        // Складываем содержимое блоков с баллунами для Яндекс карты
        if ($matterOfBalloonList != "") {
            $searchResultHTML .= $matterOfBalloonList; // Вставляем HTML-текст баллунов для Яндекс карты объявлений по недвижимости с короткими данными и данными для баллунов на Яндекс карте
        } else {
            // Если ничего не нашли то блок allBalloons будет пустым
        }

        // Закрываем блок с баллунами для Яндекс карты
        $searchResultHTML .= "</div><!-- end allBalloons -->";

        $searchResultHTML .= "
        <!-- Информация об объектах, подходящих условиям поиска -->
            <table class='listOfRealtyObjects' id='shortListOfRealtyObjects'>
                <tbody>
        ";

        // Складываем содержимое таблицы объявлений с краткими сведениями по каждому объекту
        if ($matterOfShortList != "") {
            $searchResultHTML .= $matterOfShortList; // Вставляем HTML-текст объявлений по недвижимости с короткими данными и данными для баллунов на Яндекс карте
        } else { // Если ничего не нашли

            // Считаем общее количество опубликованных объявлений
            $rez = mysql_query("SELECT COUNT(*) FROM property WHERE status = 'опубликовано'");
            $row = mysql_fetch_assoc($rez);
            if ($row != FALSE) $allAmountAdverts = $row['COUNT(*)'];

            // Выдаем вместо пустого результата:
            $searchResultHTML .= searchResultIsEmptyHTML($typeOfRequest, $allAmountAdverts);

        }

        // Закрываем таблицу объявлений с краткими сведениями
        $searchResultHTML .= "</tbody></table>";

        // Добавляем область показа карты и таблицу объявлений, содержащую подробные сведения по каждому объявлению
        $searchResultHTML .= "
            <!-- Область показа карты -->
            <div id='map'></div>

            <div class='clearBoth'></div>

            <!-- Первоначально скрытый раздел с подробным списком объявлений-->
            <div id='fullParametersListOfRealtyObjects' style='display: none;'>
                <table class='listOfRealtyObjects' style='width: 100%; float:none;'>
                    <thead id='headOfBigTable'
                           style='background-color: #ffffff; line-height: 2em; border-bottom: 1px solid #000000;'>
                        <tr class='listOfRealtyObjectsHeader'>
                            <th class='top left'></th>
                            <th>Фото</th>
                            <th>Адрес</th>
                            <th>Комнаты</th>
                            <th>Площадь</th>
                            <th>Этаж</th>
                            <th>Мебель</th>
                            <th class='top right'>Цена</th>
                        </tr>
                    </thead>
                    <tbody>
        ";

        // Складываем содержимое таблицы объявлений с подробными сведениями по каждому объекту
        if ($matterOfFullParametersList != "") {
            $searchResultHTML .= $matterOfFullParametersList; // Формируем содержимое таблицы со списком объявлений и расширенными данными по ним
        } else { // Если ничего не нашли
            // Выдаем вместо пустого результата:
            $searchResultHTML .= searchResultIsEmptyHTML($typeOfRequest, $allAmountAdverts);
        }

        // Закрываем таблицу объявлений с подробными сведениями
        $searchResultHTML .= "
                    </tbody>
                </table>
            </div>
        </div><!-- /end.resultOnSearchPage -->
        ";

        // Возвращаем HTML код со сведениями об искомых объектах недвижимости
        return $searchResultHTML;

    }

    function getLightBalloonHTML($oneProperty)
    {

        // Координаты объекта
        $coordX = "";
        if (isset($oneProperty['coordX'])) $coordX = $oneProperty['coordX'];
        $coordY = "";
        if (isset($oneProperty['coordY'])) $coordY = $oneProperty['coordY'];

        // Идентификатор объекта
        $propertyId = "";
        if (isset($oneProperty['id'])) $propertyId = $oneProperty['id'];

        $currentAdvertBalloonList = "<div class='balloonBlock' coordX='" . $coordX . "' coordY='" . $coordY . "' propertyId='" . $propertyId . "'></div>";

        return $currentAdvertBalloonList;
    }

    function getFullBalloonHTML($oneProperty, $propertyFotosArr = FALSE, $userId = FALSE, $favoritesPropertysId = FALSE)
    {
        // Шаблон для всплывающего баллуна с описанием объекта недвижимости на карте Яндекса
        $tmpl_balloonContentBody = "
            <div class='headOfBalloon'>{typeOfObject}{address}</div>
            <div class='fotosWrapper'>
                <div class='smallFotoWrapper'>
                    <img class='smallFoto gallery' src='{urlFoto1}' href='{hrefFoto1}'>
                </div>
                <div class='numberOfFotos'>{numberOfFotos}</div>
                {hiddensLinksToOtherFotos}
            </div>
            <ul class='listDescription'>
                <li>
                    <span class='headOfString'>Плата:</span> {costOfRenting} {currency} в месяц
                </li>
                <li>
                    <span class='headOfString'>Ком. услуги:</span> {utilities}
                </li>
                <li>
                    <span class='headOfString'>Комиссия:</span> {compensationMoney} {currency} ({compensationPercent}%)
                </li>
                <li>
                    <span class='headOfString'>{amountOfRoomsName}</span> {amountOfRooms}{adjacentRooms}
                </li>
                <li>
                    <span class='headOfString'>Площадь ({areaNames}):</span> {areaValues} м²
                </li>
                <li>
                    <span class='headOfString'>{floorName}</span> {floor}
                </li>
                <li>
                    <span class='headOfString'>{furnitureName}</span> {furniture}
                </li>
            </ul>
            <div class='clearBoth'></div>
            <div style='width:100%;'>
                <a href='objdescription.php?propertyId={propertyId}'>Подробнее</a>
                <span class='{actionFavorites}' propertyId='{propertyId}' style='float: right;'><img src='{imgFavorites}'><a>{textFavorites}</a></span>
            </div>
            ";

        // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне баллуна
        $arrBalloonReplace = array();

        // Идентификатор объекта
        $arrBalloonReplace['propertyId'] = "";
        if (isset($oneProperty['id'])) $arrBalloonReplace['propertyId'] = $oneProperty['id'];

        // Тип
        $arrBalloonReplace['typeOfObject'] = "";
        if (isset($oneProperty['typeOfObject'])) $arrBalloonReplace['typeOfObject'] = getFirstCharUpper($oneProperty['typeOfObject']) . ": ";

        // Адрес
        $arrBalloonReplace['address'] = "";
        if (isset($oneProperty['address'])) $arrBalloonReplace['address'] = $oneProperty['address'];

        // Фото
        $linksToFotosArr = getLinksToFotos($propertyFotosArr, $oneProperty['id']);
        $arrBalloonReplace['urlFoto1'] = $linksToFotosArr['urlFoto1'];
        $arrBalloonReplace['hrefFoto1'] = $linksToFotosArr['hrefFoto1'];
        $arrBalloonReplace['numberOfFotos'] = $linksToFotosArr['numberOfFotos'];
        $arrBalloonReplace['hiddensLinksToOtherFotos'] = $linksToFotosArr['hiddensLinksToOtherFotos'];

        // Все, что касается СТОИМОСТИ АРЕНДЫ
        $arrBalloonReplace['costOfRenting'] = "";
        if (isset($oneProperty['costOfRenting'])) $arrBalloonReplace['costOfRenting'] = $oneProperty['costOfRenting'];
        $arrBalloonReplace['currency'] = "";
        if (isset($oneProperty['currency'])) $arrBalloonReplace['currency'] = $oneProperty['currency'];
        $arrBalloonReplace['utilities'] = "";
        if (isset($oneProperty['utilities']) && $oneProperty['utilities'] == "да") $arrBalloonReplace['utilities'] = "от " . $oneProperty['costInSummer'] . " до " . $oneProperty['costInWinter'] . " " . $oneProperty['currency'] . " в месяц"; else $arrBalloonReplace['utilities'] = "нет";
        $arrBalloonReplace['compensationMoney'] = "";
        if (isset($oneProperty['compensationMoney'])) $arrBalloonReplace['compensationMoney'] = $oneProperty['compensationMoney'];
        $arrBalloonReplace['compensationPercent'] = "";
        if (isset($oneProperty['compensationPercent'])) $arrBalloonReplace['compensationPercent'] = $oneProperty['compensationPercent'];

        // Комнаты
        if (isset($oneProperty['amountOfRooms']) && $oneProperty['amountOfRooms'] != "0") {
            $arrBalloonReplace['amountOfRoomsName'] = "Комнат:";
            $arrBalloonReplace['amountOfRooms'] = $oneProperty['amountOfRooms'];
        } else {
            $arrBalloonReplace['amountOfRoomsName'] = "";
            $arrBalloonReplace['amountOfRooms'] = "";
        }
        if (isset($oneProperty['adjacentRooms']) && $oneProperty['adjacentRooms'] == "да") {
            if (isset($oneProperty['amountOfAdjacentRooms']) && $oneProperty['amountOfAdjacentRooms'] != "0") {
                $arrBalloonReplace['adjacentRooms'] = ", из них смежных: " . $oneProperty['amountOfAdjacentRooms'];
            } else {
                $arrBalloonReplace['adjacentRooms'] = ", смежные";
            }
        } else {
            $arrBalloonReplace['adjacentRooms'] = "";
        }

        // Площади помещений
        $arrBalloonReplace['areaNames'] = "";
        $arrBalloonReplace['areaValues'] = "";
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "квартира" && $oneProperty['typeOfObject'] != "дом" && $oneProperty['typeOfObject'] != "таунхаус" && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
            $arrBalloonReplace['areaNames'] .= "комнаты";
            $arrBalloonReplace['areaValues'] .= $oneProperty['roomSpace'];
        }
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната") {
            $arrBalloonReplace['areaNames'] .= "общая";
            $arrBalloonReplace['areaValues'] .= $oneProperty['totalArea'];
        }
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['typeOfObject'] != "гараж") {
            $arrBalloonReplace['areaNames'] .= "/жилая";
            $arrBalloonReplace['areaValues'] .= " / " . $oneProperty['livingSpace'];
        }
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
            $arrBalloonReplace['areaNames'] .= "/кухни";
            $arrBalloonReplace['areaValues'] .= " / " . $oneProperty['kitchenSpace'];
        }

        // Этаж
        $arrBalloonReplace['floorName'] = "";
        $arrBalloonReplace['floor'] = "";
        if (isset($oneProperty['floor']) && isset($oneProperty['totalAmountFloor']) && $oneProperty['floor'] != "0" && $oneProperty['totalAmountFloor'] != "0") {
            $arrBalloonReplace['floorName'] = "Этаж:";
            $arrBalloonReplace['floor'] = $oneProperty['floor'] . " из " . $oneProperty['totalAmountFloor'];
        }
        if (isset($oneProperty['numberOfFloor']) && $oneProperty['numberOfFloor'] != "0") {
            $arrBalloonReplace['floorName'] = "Этажность:";
            $arrBalloonReplace['floor'] = $oneProperty['numberOfFloor'];
        }

        // Мебель
        $arrBalloonReplace['furnitureName'] = "";
        $arrBalloonReplace['furniture'] = "";
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "0" && $oneProperty['typeOfObject'] != "гараж") {
            $arrBalloonReplace['furnitureName'] = "Мебель:";
            if ((isset($oneProperty['furnitureInLivingArea']) && count(unserialize($oneProperty['furnitureInLivingArea'])) != 0) || (isset($oneProperty['furnitureInLivingAreaExtra']) && $oneProperty['furnitureInLivingAreaExtra'] != "")) $arrBalloonReplace['furniture'] = "есть в жилой зоне";
            if ((isset($oneProperty['furnitureInKitchen']) && count(unserialize($oneProperty['furnitureInKitchen'])) != 0) || (isset($oneProperty['furnitureInKitchenExtra']) && $oneProperty['furnitureInKitchenExtra'] != "")) if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "есть на кухне"; else $arrBalloonReplace['furniture'] .= ", есть на кухне";
            if ((isset($oneProperty['appliances']) && count(unserialize($oneProperty['appliances'])) != 0) || (isset($oneProperty['appliancesExtra']) && $oneProperty['appliancesExtra'] != "")) if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "есть бытовая техника"; else $arrBalloonReplace['furniture'] .= ", есть бытовая техника";
            if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "нет";
        }

        // Избранное
        $arrBalloonReplace['actionFavorites'] = "";
        $arrBalloonReplace['imgFavorites'] = "";
        $arrBalloonReplace['textFavorites'] = "";
        if ($userId != FALSE) {
            // Если список избранных объявлений не был передан - получаем его
            if ($favoritesPropertysId == FALSE) {
                $favoritesPropertysId = array();
                $rowUsers = FALSE;
                $rezUsers = mysql_query("SELECT favoritesPropertysId FROM users WHERE id = '" . $userId . "'");
                if ($rezUsers != FALSE) $rowUsers = mysql_fetch_assoc($rezUsers);
                if ($rowUsers != FALSE) $favoritesPropertysId = unserialize($rowUsers['favoritesPropertysId']);
            }
            // Проверяем наличие данного объявления среди избранных у пользователя
            if (in_array($arrBalloonReplace['propertyId'], $favoritesPropertysId)) {
                $arrBalloonReplace['actionFavorites'] = "removeFromFavorites";
                $arrBalloonReplace['imgFavorites'] = "img/gold_star.png";
                $arrBalloonReplace['textFavorites'] = "убрать из избранного";
            } else {
                $arrBalloonReplace['actionFavorites'] = "addToFavorites";
                $arrBalloonReplace['imgFavorites'] = "img/blue_star.png";
                $arrBalloonReplace['textFavorites'] = "добавить в избранное";
            }
        } else {
            $arrBalloonReplace['actionFavorites'] = "addToFavorites";
            $arrBalloonReplace['imgFavorites'] = "img/blue_star.png";
            $arrBalloonReplace['textFavorites'] = "добавить в избранное";
        }

        // Производим заполнение шаблона баллуна
        // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
        $arrBalloonTemplVar = array('{propertyId}', '{typeOfObject}', '{address}', '{urlFoto1}', '{hrefFoto1}', '{numberOfFotos}', '{hiddensLinksToOtherFotos}', '{costOfRenting}', '{currency}', '{utilities}', '{compensationMoney}', '{compensationPercent}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaNames}', '{areaValues}', '{floorName}', '{floor}', '{furnitureName}', '{furniture}', '{actionFavorites}', '{imgFavorites}', '{textFavorites}');
        // Копируем html-текст шаблона баллуна
        $currentAdvertBalloonList = str_replace($arrBalloonTemplVar, $arrBalloonReplace, $tmpl_balloonContentBody);

        return $currentAdvertBalloonList;
    }

    function getShortListItemHTML($oneProperty, $propertyFotosArr = FALSE, $userId = FALSE, $favoritesPropertysId = FALSE, $number)
    {
        // Шаблон для блока с кратким описанием объекта недвижимости в таблице
        $tmpl_shortAdvert = "
            <tr class='realtyObject' propertyId='{propertyId}'>
                <td>
            	    <div class='numberOfRealtyObject'>{number}</div>
               	    <span class='{actionFavorites} aloneStar' propertyId='{propertyId}'><img src='{imgFavorites}'></span>
               	</td>
               	<td>
               	    <div class='fotosWrapper fotoInTable'>
                        <div class='smallFotoWrapper'>
                            <img class='smallFoto gallery' src='{urlFoto1}' href='{hrefFoto1}'>
                        </div>
                        <div class='numberOfFotos'>{numberOfFotos}</div>
                        {hiddensLinksToOtherFotos}
                    </div>
                </td>
                <td>{address}
                    <div class='linkToDescriptionBlock'>
                        <a class='linkToDescription' href='objdescription.php?propertyId={propertyId}'>Подробнее</a>
                    </div>
                </td>
                <td>{costOfRenting} {currency} в месяц</td>
            </tr>
        ";

        // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне shortList строки таблицы
        $arrShortListReplace = array();

        // Идентификатор объекта
        $arrShortListReplace['propertyId'] = "";
        if (isset($oneProperty['id'])) $arrShortListReplace['propertyId'] = $oneProperty['id'];

        // Порядковый номер объявления в выдаче
        $arrShortListReplace['number'] = $number;

        // Избранное
        $arrShortListReplace['actionFavorites'] = "";
        $arrShortListReplace['imgFavorites'] = "";
        if ($userId != FALSE) {
            // Если список избранных объявлений не был передан - получаем его
            if ($favoritesPropertysId == FALSE) {
                $favoritesPropertysId = array();
                $rowUsers = FALSE;
                $rezUsers = mysql_query("SELECT favoritesPropertysId FROM users WHERE id = '" . $userId . "'");
                if ($rezUsers != FALSE) $rowUsers = mysql_fetch_assoc($rezUsers);
                if ($rowUsers != FALSE) $favoritesPropertysId = unserialize($rowUsers['favoritesPropertysId']);
            }
            // Проверяем наличие данного объявления среди избранных у пользователя
            if (in_array($arrShortListReplace['propertyId'], $favoritesPropertysId)) {
                $arrShortListReplace['actionFavorites'] = "removeFromFavorites";
                $arrShortListReplace['imgFavorites'] = "img/gold_star.png";
            } else {
                $arrShortListReplace['actionFavorites'] = "addToFavorites";
                $arrShortListReplace['imgFavorites'] = "img/blue_star.png";
            }
        } else {
            $arrShortListReplace['actionFavorites'] = "addToFavorites";
            $arrShortListReplace['imgFavorites'] = "img/blue_star.png";
        }

        // Фото
        $linksToFotosArr = getLinksToFotos($propertyFotosArr, $oneProperty['id']);
        $arrShortListReplace['urlFoto1'] = $linksToFotosArr['urlFoto1'];
        $arrShortListReplace['hrefFoto1'] = $linksToFotosArr['hrefFoto1'];
        $arrShortListReplace['numberOfFotos'] = $linksToFotosArr['numberOfFotos'];
        $arrShortListReplace['hiddensLinksToOtherFotos'] = $linksToFotosArr['hiddensLinksToOtherFotos'];

        $arrShortListReplace['address'] = "";
        if (isset($oneProperty['address'])) $arrShortListReplace['address'] = $oneProperty['address'];

        $arrShortListReplace['costOfRenting'] = "";
        if (isset($oneProperty['costOfRenting'])) $arrShortListReplace['costOfRenting'] = $oneProperty['costOfRenting'];

        $arrShortListReplace['currency'] = "";
        if (isset($oneProperty['currency'])) $arrShortListReplace['currency'] = $oneProperty['currency'];

        // Производим заполнение шаблона строки (блока) shortList таблицы по данному объекту недвижимости
        // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
        $arrShortListTemplVar = array('{propertyId}', '{number}', '{actionFavorites}', '{imgFavorites}', '{urlFoto1}', '{hrefFoto1}', '{numberOfFotos}', '{hiddensLinksToOtherFotos}', '{address}', '{costOfRenting}', '{currency}');
        // Копируем html-текст шаблона блока (строки таблицы)
        $currentAdvertShortList = str_replace($arrShortListTemplVar, $arrShortListReplace, $tmpl_shortAdvert);

        return $currentAdvertShortList;
    }

    function getFullParametersListItemHTML($oneProperty, $propertyFotosArr = FALSE, $userId = FALSE, $favoritesPropertysId = FALSE, $number)
    {
        // Шаблон для блока (строки) с подробным описанием объекта недвижимости в таблице
        $tmpl_extendedAdvert = "
        <tr class='realtyObject' linkToDescription='objdescription.php?propertyId={propertyId}' propertyId='{propertyId}'>
            <td>
                <div class='numberOfRealtyObject'>{number}</div>
                <span class='{actionFavorites} aloneStar' propertyId='{propertyId}'><img src='{imgFavorites}'></span>
            </td>
            <td>
                <div class='fotosWrapper fotoInTable'>
                    <div class='smallFotoWrapper'>
                        <img class='smallFoto gallery' src='{urlFoto1}' href='{hrefFoto1}'>
                    </div>
                    <div class='numberOfFotos'>{numberOfFotos}</div>
                    {hiddensLinksToOtherFotos}
                </div>
            </td>
            <td>{typeOfObject}{district}{address}</td>
            <td>{amountOfRooms}{adjacentRooms}</td>
            <td>{areaValues}</td>
            <td>{floor}</td>
            <td>{furniture}</td>
            <td>{costOfRenting}{utilities}{compensationMoney} ({compensationPercent}%)</td>
        </tr>
        ";

        // Инициализируем массив, в который будут сохранены значения, используемые для замены констант в шаблоне
        $arrExtendedListReplace = array();

        // Идентификатор объекта
        $arrExtendedListReplace['propertyId'] = "";
        if (isset($oneProperty['id'])) $arrExtendedListReplace['propertyId'] = $oneProperty['id'];

        // Номер объявления
        $arrExtendedListReplace['number'] = $number;

        // Избранное
        $arrExtendedListReplace['actionFavorites'] = "";
        $arrExtendedListReplace['imgFavorites'] = "";
        if ($userId != FALSE) {
            // Если список избранных объявлений не был передан - получаем его
            if ($favoritesPropertysId == FALSE) {
                $favoritesPropertysId = array();
                $rowUsers = FALSE;
                $rezUsers = mysql_query("SELECT favoritesPropertysId FROM users WHERE id = '" . $userId . "'");
                if ($rezUsers != FALSE) $rowUsers = mysql_fetch_assoc($rezUsers);
                if ($rowUsers != FALSE) $favoritesPropertysId = unserialize($rowUsers['favoritesPropertysId']);
            }
            if (in_array($arrExtendedListReplace['propertyId'], $favoritesPropertysId)) {
                $arrExtendedListReplace['actionFavorites'] = "removeFromFavorites";
                $arrExtendedListReplace['imgFavorites'] = "img/gold_star.png";
            } else {
                $arrExtendedListReplace['actionFavorites'] = "addToFavorites";
                $arrExtendedListReplace['imgFavorites'] = "img/blue_star.png";
            }
        } else {
            $arrExtendedListReplace['actionFavorites'] = "addToFavorites";
            $arrExtendedListReplace['imgFavorites'] = "img/blue_star.png";
        }

        // Фото
        $linksToFotosArr = getLinksToFotos($propertyFotosArr, $oneProperty['id']);
        $arrExtendedListReplace['urlFoto1'] = $linksToFotosArr['urlFoto1'];
        $arrExtendedListReplace['hrefFoto1'] = $linksToFotosArr['hrefFoto1'];
        $arrExtendedListReplace['numberOfFotos'] = $linksToFotosArr['numberOfFotos'];
        $arrExtendedListReplace['hiddensLinksToOtherFotos'] = $linksToFotosArr['hiddensLinksToOtherFotos'];

        // Тип
        $arrExtendedListReplace['typeOfObject'] = "<br><br>";
        if (isset($oneProperty['typeOfObject'])) $arrExtendedListReplace['typeOfObject'] = getFirstCharUpper($oneProperty['typeOfObject']) . "<br><br>";

        // Район
        $arrExtendedListReplace['district'] = "";
        if (isset($oneProperty['district'])) $arrExtendedListReplace['district'] = $oneProperty['district'] . "<br>";

        // Адрес
        $arrExtendedListReplace['address'] = "";
        if (isset($oneProperty['address'])) $arrExtendedListReplace['address'] = $oneProperty['address'];

        // Комнаты
        if (isset($oneProperty['amountOfRooms']) && $oneProperty['amountOfRooms'] != "0") {
            $arrExtendedListReplace['amountOfRooms'] = "<span title='количество комнат'>" . $oneProperty['amountOfRooms'] . "</span><br>";
        } else {
            $arrExtendedListReplace['amountOfRooms'] = "<span title='количество комнат'>-</span><br>";
        }
        if (isset($oneProperty['adjacentRooms']) && $oneProperty['adjacentRooms'] == "да") {
            if (isset($oneProperty['amountOfAdjacentRooms']) && $oneProperty['amountOfAdjacentRooms'] != "0") {
                $arrExtendedListReplace['adjacentRooms'] = "смежных: " . $oneProperty['amountOfAdjacentRooms'];
            } else {
                $arrExtendedListReplace['adjacentRooms'] = "смежные";
            }
        } else {
            $arrExtendedListReplace['adjacentRooms'] = "";
        }

        // Площади помещений
        $arrExtendedListReplace['areaValues'] = "";
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "квартира" && $oneProperty['typeOfObject'] != "дом" && $oneProperty['typeOfObject'] != "таунхаус" && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
            $arrExtendedListReplace['areaValues'] .= "<span title='площадь комнаты'>" . $oneProperty['roomSpace'] . " м²</span><br>";
        }
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната") {
            $arrExtendedListReplace['areaValues'] .= "<span title='общая площадь'>" . $oneProperty['totalArea'] . " м²</span><br>";
        }
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['typeOfObject'] != "гараж") {
            $arrExtendedListReplace['areaValues'] .= "<span title='жилая площадь'>" . $oneProperty['livingSpace'] . " м²</span><br>";
        }
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
            $arrExtendedListReplace['areaValues'] .= "<span title='площадь кухни'>" . $oneProperty['kitchenSpace'] . " м²</span><br>";
        }

        // Этаж
        $arrExtendedListReplace['floor'] = "";
        if (isset($oneProperty['floor']) && isset($oneProperty['totalAmountFloor']) && $oneProperty['floor'] != "0" && $oneProperty['totalAmountFloor'] != "0") {
            $arrExtendedListReplace['floor'] = "<span title='этаж'>" . $oneProperty['floor'] . "</span><br><span title='общее количество этажей в доме'>из " . $oneProperty['totalAmountFloor'] . "</span>";
        }
        if (isset($oneProperty['numberOfFloor']) && $oneProperty['numberOfFloor'] != "0") {
            $arrExtendedListReplace['floor'] = "<span title='этажность дома'>" . $oneProperty['numberOfFloor'] . "</span>";
        }
        if ($arrExtendedListReplace['floor'] == "") $arrExtendedListReplace['floor'] = "<span title='этаж'>-</span>";

        // Мебель
        $arrExtendedListReplace['furniture'] = "";
        if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "0" && $oneProperty['typeOfObject'] != "гараж") {
            if ((isset($oneProperty['furnitureInLivingArea']) && count(unserialize($oneProperty['furnitureInLivingArea'])) != 0) || (isset($oneProperty['furnitureInLivingAreaExtra']) && $oneProperty['furnitureInLivingAreaExtra'] != "")) $arrExtendedListReplace['furniture'] .= "<span title='наличие мебели в жилой зоне'>+</span><br>"; else $arrExtendedListReplace['furniture'] .= "<span title='наличие мебели в жилой зоне'>-</span><br>";
            if ((isset($oneProperty['furnitureInKitchen']) && count(unserialize($oneProperty['furnitureInKitchen'])) != 0) || (isset($oneProperty['furnitureInKitchenExtra']) && $oneProperty['furnitureInKitchenExtra'] != "")) $arrExtendedListReplace['furniture'] .= "<span title='наличие мебели на кухне'>+</span><br>"; else $arrExtendedListReplace['furniture'] .= "<span title='наличие мебели на кухне'>-</span><br>";
            if ((isset($oneProperty['appliances']) && count(unserialize($oneProperty['appliances'])) != 0) || (isset($oneProperty['appliancesExtra']) && $oneProperty['appliancesExtra'] != "")) $arrExtendedListReplace['furniture'] .= "<span title='наличие бытовой техники'>+</span><br>"; else $arrExtendedListReplace['furniture'] .= "<span title='наличие бытовой техники'>-</span><br>";
        } else {
            $arrExtendedListReplace['furniture'] = "<span title='наличие мебели и бытовой техники'>-<br>-<br>-</span>";
        }

        // Все, что касается СТОИМОСТИ АРЕНДЫ
        $arrExtendedListReplace['costOfRenting'] = "";
        if (isset($oneProperty['costOfRenting']) && isset($oneProperty['currency'])) $arrExtendedListReplace['costOfRenting'] = "<span title='стоимость аренды в месяц'>" . $oneProperty['costOfRenting'] . " " . $oneProperty['currency'] . "</span><br>";
        $arrExtendedListReplace['utilities'] = "";
        if (isset($oneProperty['utilities']) && $oneProperty['utilities'] == "да") $arrExtendedListReplace['utilities'] = "<span title='коммунальные услуги оплачиваются дополнительно'>+ ком. усл.</span><br>";
        $arrExtendedListReplace['compensationMoney'] = "";
        $arrExtendedListReplace['compensationPercent'] = "";
        if (isset($oneProperty['compensationMoney']) && isset($oneProperty['compensationPercent']) && isset($oneProperty['currency'])) {
            $arrExtendedListReplace['compensationMoney'] = "<span title='единоразовая комиссия при заключении договора'>" . $oneProperty['compensationMoney'] . " " . $oneProperty['currency'];
            $arrExtendedListReplace['compensationPercent'] = $oneProperty['compensationPercent'] . "</span>";
        }

        // Производим заполнение шаблона строки (блока) fullParametersList таблицы по данному объекту недвижимости
        // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
        $arrExtendedListTemplVar = array('{propertyId}', '{number}', '{actionFavorites}', '{imgFavorites}', '{urlFoto1}', '{hrefFoto1}', '{numberOfFotos}', '{hiddensLinksToOtherFotos}', '{typeOfObject}', '{district}', '{address}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{floor}', '{furniture}', '{costOfRenting}', '{utilities}', '{compensationMoney}', '{compensationPercent}');
        // Копируем html-текст шаблона блока (строки таблицы)
        $currentAdvertExtendedList = str_replace($arrExtendedListTemplVar, $arrExtendedListReplace, $tmpl_extendedAdvert);

        return $currentAdvertExtendedList;
    }


    // Функция возвращает HTML код, который нужно поместить на страницу при отсутствии результатов поиска
    function searchResultIsEmptyHTML($typeOfRequest, $allAmountAdverts)
    {

        $searchResultHTML = "";

        if ($typeOfRequest == "search") $searchResultHTML .= "
                    <tr><td><div style='margin-top: 2em; margin-left: 1em;'>
                        К сожалению, поиск не дал результатов<br>
                        Попробуйте изменить условия поиска<br><br>
                        Посмотреть все объекты в Екатеринбурге: <a href='search.php?fastSearchButton='>" . $allAmountAdverts . " шт.</a></div></td></tr>
                    ";

        if ($typeOfRequest == "favorites") $searchResultHTML .= "
                    <tr><td><div style='margin-top: 2em; margin-left: 1em;'>
                        Вы пока ничего не добавили в Избранное<br><br>
                        Посмотреть все объекты в Екатеринбурге: <a href='search.php?fastSearchButton='>" . $allAmountAdverts . " шт.</a></div></td></tr>
                    ";

        return $searchResultHTML;

    }

?>