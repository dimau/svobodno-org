<?php

    class View
    {

        private $DBlink = FALSE; // Переменная для хранения объекта соединения с базой данных
        private $globFunc = FALSE; // Переменная для хранения глобальных функций

        // КОНСТРУКТОР
        public function __construct($globFunc = FALSE, $DBlink = FALSE) {

            // Если объект с глобальными функциями получен - сделаем его доступным для всех методов класса
            if ($globFunc != FALSE) {
                $this->globFunc = $globFunc;
            }

            // Если объект соединения с БД получен - сделаем его доступным для всех методов класса
            if ($DBlink != FALSE) {
                $this->DBlink = $DBlink;
            }

        }

        // Генерация HTML страницы через заполнение шаблона $templ данными $dataArr
        public function generate($templ, $dataArr) {
            include "templates/".$templ;
        }

        // Метод возвращает блок (div) для отображения фотографии (и, если нужно, по клику галереи фотографий) пользователя
        // На входе: $sizeForPrimary - размер основной фотографии (small, middle, big); $isInteractive - нужно ли по клику включать галерею фотографий(TRUE- нужно, FALSE - нет)
        public function getHTMLfotosWrapper($sizeForPrimary = "small", $isInteractive = FALSE, $uploadedFoto = FALSE) {

            // Шаблон для формируемого HTML блока с фотографиями
            $templ = "
                <div class='fotosWrapper {isInteractive}'>
                    <div class='{size}FotoWrapper'>
                        <img class='{size}Foto {gallery}' src='{urlFotoPrimary}' href='{hrefFotoPrimary}'>
                    </div>
                    <div class='numberOfFotos'>{numberOfFotos}</div>
                    {hiddensLinksToOtherFotos}
                </div>
            ";

            // Если информация о фотографиях пользователя не была передана (или передена в неподходящем формате), то ничего не делаем
            if (!is_array($uploadedFoto)) return FALSE;

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
            $arrForReplace = array();

            // Делаем блок фотографий интерактивным или нет?
            $arrForReplace['isInteractive'] = "";
            if (!$isInteractive) $arrForReplace['isInteractive'] = "fotoNonInteractive";

            // Размер для блока (и следовательно для основной фотографии)
            $arrForReplace['size'] = "";
            if ($sizeForPrimary == "small") $arrForReplace['size'] = "small";
            if ($sizeForPrimary == "middle") $arrForReplace['size'] = "middle";
            if ($sizeForPrimary == "big") $arrForReplace['size'] = "big";

            // Галерея?
            $arrForReplace['gallery'] = "";
            if ($isInteractive) $arrForReplace['gallery'] = "gallery";

            // URL до показываемой в качестве основной фотографии. Атрибут href будет полезен, если эту же фотографию нужно будет открыть в галерее - он всегда показывает путь до данного фото в большом формате
            $arrForReplace['urlFotoPrimary'] = "";
            $arrForReplace['hrefFotoPrimary'] = "";
            $arrForReplace['numberOfFotos'] = "";
            $arrForReplace['hiddensLinksToOtherFotos'] = "";
            // Перебираем все имеющиеся фотографии пользователя
            if (count($uploadedFoto) == 0) {
                // Если у пользователя нет фото - присваиваем картинку по умолчанию
                $arrForReplace['urlFotoPrimary'] = "uploaded_files/1/".$arrForReplace['size']."/1c1dfa378d4d9caaa93703c0b89f4077.jpeg";
                $arrForReplace['hrefFotoPrimary'] = "uploaded_files/1/big/1c1dfa378d4d9caaa93703c0b89f4077.jpeg";
            } else {
                // Если у пользователя есть фото - найдем среди них основное. А из неосновных сделаем hidden блоки для галереи
                foreach ($uploadedFoto as $value) {
                    if ($value['status'] == "основная") {
                        $arrForReplace['urlFotoPrimary'] = $value['folder'] . '/' . $arrForReplace['size'] . '/' . $value['id'] . "." . $value['extension'];
                        $arrForReplace['hrefFotoPrimary'] = $value['folder'] . '/big/' . $value['id'] . "." . $value['extension'];
                    } else {
                        // Из неосновных фотографий формируем input hidden блоки для передачи клиентскому JS информации об адресе большой фотографии (для галереи)
                        $arrForReplace['hiddensLinksToOtherFotos'] .= "<input type='hidden' class='gallery' href='" . $value['folder'] . "/big/" . $value['id'] . "." . $value['extension'] . "'>";
                    }
                }
            }

            // Если фотографий больше чем 1 и блок с фотками интерактивный (можно по клику открыть галерею), то нужно внизу сделать приписку об оставшемся кол-ве фоток по шаблону: 'еще ___ фото'
            if (count($uploadedFoto) > 1 && $isInteractive) {
                $count = count($uploadedFoto) - 1; // Считаем сколько еще фотографий осталось кроме основной
                $arrForReplace['numberOfFotos'] = "еще $count фото";
            }

            // Заполняем шаблон
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrTemplVar = array('{isInteractive}', '{size}', '{gallery}', '{urlFotoPrimary}', '{hrefFotoPrimary}', '{numberOfFotos}', '{hiddensLinksToOtherFotos}');
            // Копируем html-текст шаблона баллуна
            $fotosWrapperHTML = str_replace($arrTemplVar, $arrForReplace, $templ);

            return $fotosWrapperHTML;

        }

        function getSearchResultHTML($propertyLightArr, $favoritesPropertysId = array(), $typeOfRequest = "search")
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


            $res = $this->DBlink->query("SELECT * FROM property WHERE".$strWHERE." ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting LIMIT 20");
            if (($this->DBlink->errno)
                OR (($propertyFullArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
            ) {
                // Логируем ошибку
                //TODO: сделать логирование ошибки
                $propertyFullArr = array();
            }

            // Превращаем полученный массив массивов в ассоциированный массив массивов, в качестве ключей - идентификаторы объектов недвижимости
            for ($i = 0; $i < count($propertyFullArr); $i++) {
                $propertyFullArrNew[$propertyFullArr[$i]['id']] = $propertyFullArr[$i];
            }


            /*// Собираем и выполняем поисковый запрос - получаем подробные сведения по не более чем 20-ти первым в списке объявлениям
            $propertyFullArr = array(); // в итоге получим массив, каждый элемент которого представляет собой еще один массив значений конкретного объявления по недвижимости
            if ($strWHERE != "") {
                $rezProperty = mysql_query("SELECT * FROM property WHERE" . $strWHERE . " ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting LIMIT 20"); // Сортируем по стоимости аренды и ограничиваем количество 20 объявлениями, чтобы запрос не проходил таблицу до конца, когда выделит нужные нам 20 объектов
                if ($rezProperty != FALSE) {
                    for ($i = 0; $i < mysql_num_rows($rezProperty); $i++) {
                        $row = mysql_fetch_assoc($rezProperty);
                        if ($row != FALSE) $propertyFullArr[$row['id']] = $row; // Применение ассоциативного массива для сохранения помогает при построении
                    }
                }
            } */

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
                    $matterOfBalloonList .= $this->getLightBalloonHTML($propertyLightArr[$i]);
                    continue;
                }

                // Получаем фотографии объекта
                $res = $this->DBlink->query("SELECT * FROM propertyFotos WHERE propertyId = '" . $currentPropertyId . "'");
                if (($this->DBlink->errno)
                    OR (($propertyFotosArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
                ) {
                    // Логируем ошибку
                    //TODO: сделать логирование ошибки
                    $propertyFotosArr = array();
                }


                /*// Получаем фотографии объекта
                $propertyFotosArr = array(); // Массив, в который запишем массивы, каждый из которых будет содержать данные по 1 фотке объекта
                $rezPropertyFotos = mysql_query("SELECT * FROM propertyFotos WHERE propertyId = '" . $currentPropertyId . "'");
                if ($rezPropertyFotos != FALSE) {
                    for ($j = 0; $j < mysql_num_rows($rezPropertyFotos); $j++) {
                        $propertyFotosArr[] = mysql_fetch_assoc($rezPropertyFotos);
                    }
                }*/





                // Если у нам удалось ранее получить полные сведения по объекту $propertyLightArr[$i], то отрабатываем его, в противном случае - игнорируем.
                // Кажется, что данное условие будет НЕ выполняться крайне редко - при сбоях в работе с БД, но для большей безопасности, думаю, не помещает такая проверка
                if (isset($propertyFullArrNew[$currentPropertyId])) {

                    /************** Готовим полный баллун **************/
                    // Полученный HTML текст складываем в "копилочку"
                    $matterOfBalloonList .= substr($this->getLightBalloonHTML($propertyLightArr[$i]), 0, -6); // Получаем минимальный баллун, который служит рамкой для полного баллуна. И сразу отрезаем </div> в конце строки
                    $matterOfBalloonList .= $this->getFullBalloonHTML($propertyFullArrNew[$currentPropertyId], $propertyFotosArr, $favoritesPropertysId);
                    $matterOfBalloonList .= "</div>"; // Возвращаем назад отрезанный ранее div

                    /***** Готовим блок shortList таблицы для данного объекта недвижимости *****/
                    // Полученный HTML текст складываем в "копилочку"
                    $matterOfShortList .= $this->getShortListItemHTML($propertyFullArrNew[$currentPropertyId], $propertyFotosArr, $favoritesPropertysId, $number);

                    /***** Готовим блок fullParametersList таблицы для данного объекта недвижимости *****/
                    // Полученный HTML текст складываем в "копилочку"
                    $matterOfFullParametersList .= $this->getFullParametersListItemHTML($propertyFullArrNew[$currentPropertyId], $propertyFotosArr, $favoritesPropertysId, $number);

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


                $res = $this->DBlink->query("SELECT COUNT(*) FROM property WHERE status = 'опубликовано'");
                if (($this->DBlink->errno)
                    OR (($amountOfRows = $res->fetch_row()) === NULL)
                ) {
                    // Логируем ошибку
                    //TODO: сделать логирование ошибки
                    $allAmountAdverts = "";
                } else {
                    $allAmountAdverts = $amountOfRows[0];
                }

                // Выдаем вместо пустого результата:
                $searchResultHTML .= $this->searchResultIsEmptyHTML($typeOfRequest, $allAmountAdverts);

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
                $searchResultHTML .= $this->searchResultIsEmptyHTML($typeOfRequest, $allAmountAdverts);
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

            $currentAdvertBalloonList = "<div class='balloonBlock' coordX='".$coordX."' coordY='".$coordY."' propertyId='".$propertyId."'></div>";

            return $currentAdvertBalloonList;
        }

        function getFullBalloonHTML($oneProperty, $propertyFotosArr = FALSE, $favoritesPropertysId = array())
        {
            // Шаблон для всплывающего баллуна с описанием объекта недвижимости на карте Яндекса
            $tmpl_balloonContentBody = "
            <div class='headOfBalloon'>{typeOfObject}{address}</div>
            {fotosWrapper}
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
            if (isset($oneProperty['typeOfObject'])) $arrBalloonReplace['typeOfObject'] = $this->globFunc->getFirstCharUpper($oneProperty['typeOfObject']) . ": ";

            // Адрес
            $arrBalloonReplace['address'] = "";
            if (isset($oneProperty['address'])) $arrBalloonReplace['address'] = $oneProperty['address'];

            // Фото
            $arrBalloonReplace['fotosWrapper'] = "";
            $arrBalloonReplace['fotosWrapper'] = $this->getHTMLfotosWrapper("small", TRUE, $propertyFotosArr);

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
            if (count($favoritesPropertysId) != 0) {
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
            $arrBalloonTemplVar = array('{propertyId}', '{typeOfObject}', '{address}', '{fotosWrapper}', '{costOfRenting}', '{currency}', '{utilities}', '{compensationMoney}', '{compensationPercent}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaNames}', '{areaValues}', '{floorName}', '{floor}', '{furnitureName}', '{furniture}', '{actionFavorites}', '{imgFavorites}', '{textFavorites}');
            // Копируем html-текст шаблона баллуна
            $currentAdvertBalloonList = str_replace($arrBalloonTemplVar, $arrBalloonReplace, $tmpl_balloonContentBody);

            return $currentAdvertBalloonList;
        }

        function getShortListItemHTML($oneProperty, $propertyFotosArr = FALSE, $favoritesPropertysId = array(), $number)
        {
            // Шаблон для блока с кратким описанием объекта недвижимости в таблице
            $tmpl_shortAdvert = "
            <tr class='realtyObject' propertyId='{propertyId}'>
                <td>
            	    <div class='numberOfRealtyObject'>{number}</div>
               	    <span class='{actionFavorites} aloneStar' propertyId='{propertyId}'><img src='{imgFavorites}'></span>
               	</td>
               	<td>
               	    {fotosWrapper}
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
            if (count($favoritesPropertysId) != 0) {
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
            $arrShortListReplace['fotosWrapper'] = "";
            $arrShortListReplace['fotosWrapper'] = $this->getHTMLfotosWrapper("small", TRUE, $propertyFotosArr);

            $arrShortListReplace['address'] = "";
            if (isset($oneProperty['address'])) $arrShortListReplace['address'] = $oneProperty['address'];

            $arrShortListReplace['costOfRenting'] = "";
            if (isset($oneProperty['costOfRenting'])) $arrShortListReplace['costOfRenting'] = $oneProperty['costOfRenting'];

            $arrShortListReplace['currency'] = "";
            if (isset($oneProperty['currency'])) $arrShortListReplace['currency'] = $oneProperty['currency'];

            // Производим заполнение шаблона строки (блока) shortList таблицы по данному объекту недвижимости
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrShortListTemplVar = array('{propertyId}', '{number}', '{actionFavorites}', '{imgFavorites}', '{fotosWrapper}', '{address}', '{costOfRenting}', '{currency}');
            // Копируем html-текст шаблона блока (строки таблицы)
            $currentAdvertShortList = str_replace($arrShortListTemplVar, $arrShortListReplace, $tmpl_shortAdvert);

            return $currentAdvertShortList;
        }

        function getFullParametersListItemHTML($oneProperty, $propertyFotosArr = FALSE, $favoritesPropertysId = array(), $number)
        {
            // Шаблон для блока (строки) с подробным описанием объекта недвижимости в таблице
            $tmpl_extendedAdvert = "
        <tr class='realtyObject' linkToDescription='objdescription.php?propertyId={propertyId}' propertyId='{propertyId}'>
            <td>
                <div class='numberOfRealtyObject'>{number}</div>
                <span class='{actionFavorites} aloneStar' propertyId='{propertyId}'><img src='{imgFavorites}'></span>
            </td>
            <td>
                {fotosWrapper}
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
            if (count($favoritesPropertysId) != 0) {
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
            $arrExtendedListReplace['fotosWrapper'] = "";
            $arrExtendedListReplace['fotosWrapper'] = $this->getHTMLfotosWrapper("small", TRUE, $propertyFotosArr);

            // Тип
            $arrExtendedListReplace['typeOfObject'] = "<br><br>";
            if (isset($oneProperty['typeOfObject'])) $arrExtendedListReplace['typeOfObject'] = $this->globFunc->getFirstCharUpper($oneProperty['typeOfObject']) . "<br><br>";

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
            $arrExtendedListTemplVar = array('{propertyId}', '{number}', '{actionFavorites}', '{imgFavorites}', '{fotosWrapper}', '{typeOfObject}', '{district}', '{address}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{floor}', '{furniture}', '{costOfRenting}', '{utilities}', '{compensationMoney}', '{compensationPercent}');
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
    }
