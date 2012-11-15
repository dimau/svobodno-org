<?php

    class View
    {

        private $DBlink = FALSE; // Переменная для хранения объекта соединения с базой данных
        private $globFunc = FALSE; // Переменная для хранения глобальных функций

        // КОНСТРУКТОР
        public function __construct($globFunc = FALSE, $DBlink = FALSE)
        {

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
        public function generate($templ, $dataArr)
        {
            include "templates/" . $templ;
        }

        // Метод возвращает блок (div) для отображения фотографии (и, если нужно, по клику галереи фотографий) пользователя
        // На входе: $sizeForPrimary - размер основной фотографии (small, middle, big); $isInteractive - нужно ли по клику включать галерею фотографий(TRUE- нужно, FALSE - нет), $forTable - блок будет размещаться в таблице? (если TRUE - блок центрируется)
        public function getHTMLfotosWrapper($sizeForPrimary = "small", $isInteractive = FALSE, $forTable = FALSE, $uploadedFoto = FALSE)
        {

            // Шаблон для формируемого HTML блока с фотографиями
            $templ = "
                <div class='fotosWrapper {isInteractive} {forTable}'>
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

            // Делаем блок фотографий для таблицы (с центрированием) или нет?
            $arrForReplace['forTable'] = "";
            if ($forTable) $arrForReplace['forTable'] = "fotoInTable";

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
                $arrForReplace['urlFotoPrimary'] = "uploaded_files/1/" . $arrForReplace['size'] . "/1c1dfa378d4d9caaa93703c0b89f4077.jpeg";
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
            $arrTemplVar = array('{isInteractive}', '{forTable}', '{size}', '{gallery}', '{urlFotoPrimary}', '{hrefFotoPrimary}', '{numberOfFotos}', '{hiddensLinksToOtherFotos}');
            // Копируем html-текст шаблона баллуна
            $fotosWrapperHTML = str_replace($arrTemplVar, $arrForReplace, $templ);

            return $fotosWrapperHTML;

        }

        // Метод возвращает строку команды добавления в избранное или удаления из избранного в зависимости от ситуации
        // На входе: $propertyId - id объекта недвижимости, для которого формируется строка, $favoritesPropertysId - массив идентификаторов всех избранных объявлений недвижимости данного пользователя,
        // $typeOfHTML - задает используемый шаблон. Если = "onlyIcon" - выдается шаблон исключительно с иконкой избранного. Если = "stringWithIcon" - выдается строка и иконка избранного
        public function getHTMLforFavorites($propertyId = 0, $favoritesPropertysId = array(), $typeOfHTML = "stringWithIcon")
        {

            // Шаблон для формируемого HTML блока с командой добавления в избранное / удаления из избранного
            $templ = "
                <span class='{actionFavorites}' propertyId='{propertyId}'><img src='{imgFavorites}'><a>{textFavorites}</a></span>
            ";

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
            $arrForReplace = array();
            $arrForReplace['actionFavorites'] = "";
            $arrForReplace['propertyId'] = "";
            $arrForReplace['imgFavorites'] = "";
            $arrForReplace['textFavorites'] = "";

            if ($propertyId != 0) $arrForReplace['propertyId'] = $propertyId;

            if ($propertyId != 0 && count($favoritesPropertysId) != 0) {
                // Проверяем наличие данного объявления среди избранных у пользователя
                if (in_array($propertyId, $favoritesPropertysId)) {
                    $arrForReplace['actionFavorites'] = "removeFromFavorites";
                    $arrForReplace['imgFavorites'] = "img/gold_star.png";
                    $arrForReplace['textFavorites'] = "убрать из избранного";
                } else {
                    $arrForReplace['actionFavorites'] = "addToFavorites";
                    $arrForReplace['imgFavorites'] = "img/blue_star.png";
                    $arrForReplace['textFavorites'] = "добавить в избранное";
                }
            } else {
                $arrForReplace['actionFavorites'] = "addToFavorites";
                $arrForReplace['imgFavorites'] = "img/blue_star.png";
                $arrForReplace['textFavorites'] = "добавить в избранное";
            }

            // Заполняем шаблон
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
            $arrTemplVar = array('{actionFavorites}', '{propertyId}', '{imgFavorites}', '{textFavorites}');
            // Копируем html-текст шаблона
            $favoritesHTML = str_replace($arrTemplVar, $arrForReplace, $templ);

            return $favoritesHTML;

        }

        public function getSearchResultHTML($propertyLightArr = array(), $favoritesPropertysId = array(), $typeOfRequest = "search")
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

            // Получаем данные из БД
            $res = $this->DBlink->query("SELECT * FROM property WHERE" . $strWHERE . " ORDER BY realCostOfRenting + costInSummer * realCostOfRenting / costOfRenting LIMIT 20");
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

        public function getLightBalloonHTML($oneProperty)
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

        /**
         * Возвращает HTML для всплывающего баллуна на Яндекс карте с описанием объекта недвижимости
         *
         * @param $oneProperty - ассоциированный массив данных по конкретному объявлению
         * @param bool $propertyFotosArr - массив массивов, каждый из которых содержит информацию о конкретной фотографии объекта
         * @param array $favoritesPropertysId - массив со списком идентификаторов избранных объектов текущего пользователя
         * @return mixed - строка HTML в соответствии с шаблоном баллуна
         */
        public function getFullBalloonHTML($oneProperty, $propertyFotosArr = FALSE, $favoritesPropertysId = array())
        {
            // Шаблон для всплывающего баллуна с описанием объекта недвижимости на карте Яндекса
            $tmpl_balloonContentBody = "
            <div class='headOfBalloon'>{typeOfObject}{address}</div>
            {fotosWrapper}
            <ul class='listDescriptionSmall forBalloon'>
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
                <a href='objdescription.php?propertyId={propertyId}' target='_blank'>подробнее</a>
                <div style='float: right;'>
                    {favorites}
                </div>
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
            $arrBalloonReplace['fotosWrapper'] = $this->getHTMLfotosWrapper("small", TRUE, FALSE, $propertyFotosArr);

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
            if (!($arrBalloonReplace['favorites'] = $this->getHTMLforFavorites($oneProperty['id'], $favoritesPropertysId, "stringWithIcon"))) {
                $arrBalloonReplace['favorites'] = "";
            }

            // Производим заполнение шаблона баллуна
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrBalloonTemplVar = array('{propertyId}', '{typeOfObject}', '{address}', '{fotosWrapper}', '{costOfRenting}', '{currency}', '{utilities}', '{compensationMoney}', '{compensationPercent}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaNames}', '{areaValues}', '{floorName}', '{floor}', '{furnitureName}', '{furniture}', '{favorites}');
            // Копируем html-текст шаблона баллуна
            $currentAdvertBalloonList = str_replace($arrBalloonTemplVar, $arrBalloonReplace, $tmpl_balloonContentBody);

            return $currentAdvertBalloonList;
        }

        /**
         * Возвращает HTML для блока с картким описанием объекта недвижимости (используется при отображении результатов поиска Список + Карта)
         *
         * @param $oneProperty - ассоциированный массив данных по конкретному объявлению
         * @param bool $propertyFotosArr - массив массивов, каждый из которых содержит информацию о конкретной фотографии объекта
         * @param array $favoritesPropertysId - массив со списком идентификаторов избранных объектов текущего пользователя
         * @param $number - указывает какое число нужно присвоить блоку для его нумераци в выдаче
         * @return mixed - строка HTML в соответствии с шаблоном блока с кратким описанием объекта недвижимости
         */
        public function getShortListItemHTML($oneProperty, $propertyFotosArr = FALSE, $favoritesPropertysId = array(), $number)
        {
            $tmpl_shortAdvert = "
            <tr class='realtyObject' propertyId='{propertyId}'>
                <td>
            	    <div class='numberOfRealtyObject'>{number}</div>
               	    <span class='{actionFavorites} aloneStar' propertyId='{propertyId}'><img src='{imgFavorites}'></span>
               	</td>
               	<td>
               	    {fotosWrapper}
                </td>
                <td>
                    <ul class='listDescriptionSmall'>
                        <li>
                            <span class='headOfString'>{typeOfObject}</span> {address}
                        </li>
                        <li>
                            <span class='headOfString'>Плата:</span> {costOfRenting} {currency}/мес.{utilities}
                        </li>
                        <li>
                            <span class='headOfString'>{amountOfRoomsName}</span> {amountOfRooms}{adjacentRooms}
                        </li>
                        <li>
                            <span class='headOfString'>Площадь:</span> {areaValues} м²
                        </li>
                        <li>
                            <span class='headOfString'>{floorName}</span> {floor}
                        </li>
                    </ul>
                    <div class='advertActions'>
                        <a class='linkToDescription' href='objdescription.php?propertyId={propertyId}' target='_blank'>подробнее</a>
                    </div>
                </td>
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
            $arrShortListReplace['fotosWrapper'] = $this->getHTMLfotosWrapper("small", TRUE, TRUE, $propertyFotosArr);

            // Тип
            $arrShortListReplace['typeOfObject'] = "";
            if (isset($oneProperty['typeOfObject'])) $arrShortListReplace['typeOfObject'] = $this->globFunc->getFirstCharUpper($oneProperty['typeOfObject']) . ":";

            // Адрес
            $arrShortListReplace['address'] = "";
            if (isset($oneProperty['address'])) $arrShortListReplace['address'] = $oneProperty['address'];

            // Стоимость
            $arrShortListReplace['costOfRenting'] = "";
            if (isset($oneProperty['costOfRenting'])) $arrShortListReplace['costOfRenting'] = $oneProperty['costOfRenting'];
            $arrShortListReplace['currency'] = "";
            if (isset($oneProperty['currency'])) $arrShortListReplace['currency'] = $oneProperty['currency'];
            $arrShortListReplace['utilities'] = "";
            if (isset($oneProperty['utilities']) && $oneProperty['utilities'] == "да") $arrShortListReplace['utilities'] = " <span style='white-space: nowrap;'>+ ком.усл.</span>";

            // Комнаты
            if (isset($oneProperty['amountOfRooms']) && $oneProperty['amountOfRooms'] != "0") {
                $arrShortListReplace['amountOfRoomsName'] = "Комнат:";
                $arrShortListReplace['amountOfRooms'] = $oneProperty['amountOfRooms'];
            } else {
                $arrShortListReplace['amountOfRoomsName'] = "";
                $arrShortListReplace['amountOfRooms'] = "";
            }
            if (isset($oneProperty['adjacentRooms']) && $oneProperty['adjacentRooms'] == "да") {
                if (isset($oneProperty['amountOfAdjacentRooms']) && $oneProperty['amountOfAdjacentRooms'] != "0") {
                    $arrShortListReplace['adjacentRooms'] = ", смежных: " . $oneProperty['amountOfAdjacentRooms'];
                } else {
                    $arrShortListReplace['adjacentRooms'] = ", смежные";
                }
            } else {
                $arrShortListReplace['adjacentRooms'] = "";
            }

            // Площади помещений
            $arrShortListReplace['areaValues'] = "";
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "квартира" && $oneProperty['typeOfObject'] != "дом" && $oneProperty['typeOfObject'] != "таунхаус" && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
                $arrShortListReplace['areaValues'] .= $oneProperty['roomSpace'];
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната") {
                $arrShortListReplace['areaValues'] .= $oneProperty['totalArea'];
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['typeOfObject'] != "гараж") {
                $arrShortListReplace['areaValues'] .= " / " . $oneProperty['livingSpace'];
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж") {
                $arrShortListReplace['areaValues'] .= " / " . $oneProperty['kitchenSpace'];
            }

            // Этаж
            $arrShortListReplace['floorName'] = "";
            $arrShortListReplace['floor'] = "";
            if (isset($oneProperty['floor']) && isset($oneProperty['totalAmountFloor']) && $oneProperty['floor'] != "0" && $oneProperty['totalAmountFloor'] != "0") {
                $arrShortListReplace['floorName'] = "Этаж:";
                $arrShortListReplace['floor'] = $oneProperty['floor'] . " из " . $oneProperty['totalAmountFloor'];
            }
            if (isset($oneProperty['numberOfFloor']) && $oneProperty['numberOfFloor'] != "0") {
                $arrShortListReplace['floorName'] = "Этажность:";
                $arrShortListReplace['floor'] = $oneProperty['numberOfFloor'];
            }

            // Производим заполнение шаблона строки (блока) shortList таблицы по данному объекту недвижимости
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrShortListTemplVar = array('{propertyId}', '{number}', '{actionFavorites}', '{imgFavorites}', '{fotosWrapper}', '{typeOfObject}', '{address}', '{costOfRenting}', '{currency}', '{utilities}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{floorName}', '{floor}');
            // Копируем html-текст шаблона блока (строки таблицы)
            $currentAdvertShortList = str_replace($arrShortListTemplVar, $arrShortListReplace, $tmpl_shortAdvert);

            return $currentAdvertShortList;
        }

        public function getFullParametersListItemHTML($oneProperty, $propertyFotosArr = FALSE, $favoritesPropertysId = array(), $number)
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
            $arrExtendedListReplace['fotosWrapper'] = $this->getHTMLfotosWrapper("small", TRUE, TRUE, $propertyFotosArr);

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

        // Возвращает HTML, который нужно поместить на страницу при отсутствии результатов поиска
        public function searchResultIsEmptyHTML($typeOfRequest, $allAmountAdverts)
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

        // Возвращает HTML для списка объектов недвижимости собственника
        public function getHTMLforOwnersCollectionProperty($allPropertiesCharacteristic = array(), $allPropertiesFotoInformation = FALSE, $allPropertiesTenantPretenders = FALSE)
        {
            // Шаблон блока с описанием отдельного объекта недвижимости
            $tmpl_MyAdvert = "
                <div class='news advertForPersonalPage {statusEng}'>
                    <div class='newsHeader'>
                        <span class='advertHeaderAddress'>{typeOfObject} по адресу: {address}{apartmentNumber}</span>
                        <div class='advertHeaderStatus'>
                            статус: {status}
                        </div>
                    </div>
                    {fotosWrapper}
                    <ul class='setOfInstructions'>
                        {instructionPublish}
                        <li>
                            <a href='editadvert.php?propertyId={propertyId}'>редактировать</a>
                        </li>
                        <li>
                            <a href='objdescription.php?propertyId={propertyId}'>подробнее</a>
                        </li>
                    </ul>
                    <ul class='listDescriptionSmall forMyAdverts'>
                        <li>
                            <span class='headOfString' style='vertical-align: top;' title='Пользователи, запросившие контакты собственника по этому объявлению'>Возможные арендаторы:</span>{probableTenants}
                        </li>
                        <li>
                            <br>
                        </li>
                        <li>
                            <span class='headOfString'>Плата за аренду:</span> {costOfRenting} {currency} {utilities} {electricPower}
                        </li>
                        <li>
                            <span class='headOfString'>Залог:</span> {bail}
                        </li>
                        <li>
                            <span class='headOfString'>Предоплата:</span> {prepayment}
                        </li>
                        <li>
                            <span class='headOfString'>Срок аренды:</span> {termOfLease}, c {dateOfEntry} {dateOfCheckOut}
                        </li>
                        <li>
                            <span class='headOfString'>{furnitureName}</span> {furniture}
                        </li>
                        <li>
                            <span class='headOfString'>{repairName}</span> {repair}
                        </li>
                        <li>
                            <span class='headOfString'>Контактный телефон:</span>
                            {contactTelephonNumber}, c {timeForRingBegin} до {timeForRingEnd}
                        </li>
                    </ul>
                    <div class='clearBoth'></div>
                </div>
            ";

            // Проверяем наличие хотя бы 1 объекта недвижимости, в противном случае отдаем пустую HTML строку
            if (count($allPropertiesCharacteristic) == 0) return "";

            // Создаем бриф для каждого объявления пользователя на основе шаблона (для вкладки МОИ ОБЪЯВЛЕНИЯ), и в цикле объединяем их в один HTML блок - $briefOfAdverts.
            // Если объявлений у пользователя несколько, то в переменную, содержащую весь HTML - $briefOfAdverts, записываем каждое из них последовательно
            $briefOfAdverts = "";
            for ($i = 0; $i < count($allPropertiesCharacteristic); $i++) {

                // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
                $arrMyAdvertReplace = array();

                // Подставляем класс в заголовок html объявления для применения соответствующего css оформления
                $arrMyAdvertReplace['statusEng'] = "";
                if ($allPropertiesCharacteristic[$i]['status'] == "не опубликовано") $arrMyAdvertReplace['statusEng'] = "unpublished";
                if ($allPropertiesCharacteristic[$i]['status'] == "опубликовано") $arrMyAdvertReplace['statusEng'] = "published";

                // В заголовке блока отображаем тип недвижимости, для красоты первую букву типа сделаем в верхнем регистре
                $arrMyAdvertReplace['typeOfObject'] = "";
                $arrMyAdvertReplace['typeOfObject'] = $this->globFunc->getFirstCharUpper($allPropertiesCharacteristic[$i]['typeOfObject']);

                // Адрес и номер квартиры, если он есть
                $arrMyAdvertReplace['address'] = "";
                if (isset($allPropertiesCharacteristic[$i]['address'])) $arrMyAdvertReplace['address'] = $allPropertiesCharacteristic[$i]['address'];
                $arrMyAdvertReplace['apartmentNumber'] = "";
                if (isset($allPropertiesCharacteristic[$i]['apartmentNumber']) && $allPropertiesCharacteristic[$i]['apartmentNumber'] != "") $arrMyAdvertReplace['apartmentNumber'] = ", № " . $allPropertiesCharacteristic[$i]['apartmentNumber'];

                // Статус объявления
                $arrMyAdvertReplace['status'] = "";
                $arrMyAdvertReplace['status'] = $allPropertiesCharacteristic[$i]['status'];

                // Фото
                $arrMyAdvertReplace['fotosWrapper'] = "";
                if ($allPropertiesFotoInformation != FALSE) $fotosArr = $allPropertiesFotoInformation[$i]; else $fotosArr = array();
                $arrMyAdvertReplace['fotosWrapper'] = $this->getHTMLfotosWrapper("small", FALSE, FALSE, $fotosArr);

                // Корректируем список инструкций, доступных пользователю
                $arrMyAdvertReplace['instructionPublish'] = "";
                $arrMyAdvertReplace['propertyId'] = "";
                if ($allPropertiesCharacteristic[$i]['status'] == "опубликовано") {
                    $arrMyAdvertReplace['instructionPublish'] = "<li><a href='personal.php?propertyId=".$allPropertiesCharacteristic[$i]['id']."&action=publicationOff'>снять с публикации</a></li>";
                }
                if ($allPropertiesCharacteristic[$i]['status'] == "не опубликовано") {
                    $arrMyAdvertReplace['instructionPublish'] = "<li><a href='personal.php?propertyId=".$allPropertiesCharacteristic[$i]['id']."&action=publicationOn'>опубликовать</a></li>";
                }
                $arrMyAdvertReplace['propertyId'] = $allPropertiesCharacteristic[$i]['id'];

                /******* Список потенциальных арендаторов ******/
               $arrMyAdvertReplace['probableTenants'] = "";
                if ($allPropertiesTenantPretenders != FALSE) {
                    for ($j = 0; $j < count($allPropertiesTenantPretenders[$i]); $j++) {
                        // Перебираем данные по потенциальным арендаторам, проявившим интерес к данному объекту и добавляем их в строку $arrMyAdvertReplace['probableTenants']
                        // Формируем из имен и отчеств строку гиперссылок с ссылками на страницы арендаторов
                        if ($allPropertiesTenantPretenders[$i][$j]['typeTenant'] == "TRUE") { // Если данный пользователь (арендатор) еще ищет недвижимость
                            $compId = $allPropertiesTenantPretenders[$i][$j]['id'] * 5 + 2;
                            $arrMyAdvertReplace['probableTenants'] .= "<a href='man.php?compId=" . $compId . "'>" . $allPropertiesTenantPretenders[$i][$j]['name'] . " " . $allPropertiesTenantPretenders[$i][$j]['secondName'] . "</a>";
                        } else {
                            $arrMyAdvertReplace['probableTenants'] .= "<span title='Пользователь уже нашел недвижимость'>" . $allPropertiesTenantPretenders[$i][$j]['name'] . " " . $allPropertiesTenantPretenders[$i][$j]['secondName'] . "</span>";
                        }
                        if ($j < count($allPropertiesCharacteristic[$i]) - 1) $arrMyAdvertReplace['probableTenants'] .= ", ";
                    }
                }
                if ($arrMyAdvertReplace['probableTenants'] == "") $arrMyAdvertReplace['probableTenants'] = " <span title='Пока никто из арендаторов не проявил интереса к этому объявлению'>-</span>"; // Если нет ни одного потенциального арендатора

                // Все, что касается СТОИМОСТИ АРЕНДЫ
                $arrMyAdvertReplace['costOfRenting'] = "";
                $arrMyAdvertReplace['costOfRenting'] = $allPropertiesCharacteristic[$i]['costOfRenting'];
                $arrMyAdvertReplace['currency'] = "";
                $arrMyAdvertReplace['currency'] = $allPropertiesCharacteristic[$i]['currency'];
                $arrMyAdvertReplace['utilities'] = "";
                if ($allPropertiesCharacteristic[$i]['utilities'] == "да") $arrMyAdvertReplace['utilities'] = "+ коммунальные услуги от " . $allPropertiesCharacteristic[$i]['costInSummer'] . " до " . $allPropertiesCharacteristic[$i]['costInWinter'] . " " . $allPropertiesCharacteristic[$i]['currency'];
                $arrMyAdvertReplace['electricPower'] = "";
                if ($allPropertiesCharacteristic[$i]['electricPower'] == "да") $arrMyAdvertReplace['electricPower'] = "+ плата за электричество";
                $arrMyAdvertReplace['bail'] = "";
                if ($allPropertiesCharacteristic[$i]['bail'] == "есть") $arrMyAdvertReplace['bail'] = $allPropertiesCharacteristic[$i]['bailCost'] . " " . $allPropertiesCharacteristic[$i]['currency'];
                if ($allPropertiesCharacteristic[$i]['bail'] == "нет") $arrMyAdvertReplace['bail'] = "нет";
                $arrMyAdvertReplace['prepayment'] = "";
                $arrMyAdvertReplace['prepayment'] = $allPropertiesCharacteristic[$i]['prepayment'];

                // Срок аренды
                $arrMyAdvertReplace['termOfLease'] = "";
                $arrMyAdvertReplace['dateOfEntry'] = "";
                $arrMyAdvertReplace['dateOfCheckOut'] = "";
                $arrMyAdvertReplace['termOfLease'] = $allPropertiesCharacteristic[$i]['termOfLease'];
                $arrMyAdvertReplace['dateOfEntry'] = $this->globFunc->dateFromDBToView($allPropertiesCharacteristic[$i]['dateOfEntry']);
                if ($allPropertiesCharacteristic[$i]['dateOfCheckOut'] != "0000-00-00") $arrMyAdvertReplace['dateOfCheckOut'] = " по " . $this->globFunc->dateFromDBToView($allPropertiesCharacteristic[$i]['dateOfCheckOut']);

                // Мебель
                $arrMyAdvertReplace['furnitureName'] = "";
                $arrMyAdvertReplace['furniture'] = "";
                if ($allPropertiesCharacteristic[$i]['typeOfObject'] != "0" && $allPropertiesCharacteristic[$i]['typeOfObject'] != "гараж") {
                    $arrMyAdvertReplace['furnitureName'] = "Мебель:";
                    if (count(unserialize($allPropertiesCharacteristic[$i]['furnitureInLivingArea'])) != 0 || $allPropertiesCharacteristic[$i]['furnitureInLivingAreaExtra'] != "") $arrMyAdvertReplace['furniture'] = "есть в жилой зоне";
                    if (count(unserialize($allPropertiesCharacteristic[$i]['furnitureInKitchen'])) != 0 || $allPropertiesCharacteristic[$i]['furnitureInKitchenExtra'] != "") if ($arrMyAdvertReplace['furniture'] == "") $arrMyAdvertReplace['furniture'] = "есть на кухне"; else $arrMyAdvertReplace['furniture'] .= ", есть на кухне";
                    if (count(unserialize($allPropertiesCharacteristic[$i]['appliances'])) != 0 || $allPropertiesCharacteristic[$i]['appliancesExtra'] != "") if ($arrMyAdvertReplace['furniture'] == "") $arrMyAdvertReplace['furniture'] = "есть бытовая техника"; else $arrMyAdvertReplace['furniture'] .= ", есть бытовая техника";
                    if ($arrMyAdvertReplace['furniture'] == "") $arrMyAdvertReplace['furniture'] = "нет";
                }

                // Ремонт
                $arrMyAdvertReplace['repairName'] = "";
                $arrMyAdvertReplace['repair'] = "";
                if ($allPropertiesCharacteristic[$i]['repair'] != "0" && $allPropertiesCharacteristic[$i]['furnish'] != "0") {
                    $arrMyAdvertReplace['repairName'] = "Ремонт:";
                    $arrMyAdvertReplace['repair'] = $allPropertiesCharacteristic[$i]['repair'] . ", отделка " . $allPropertiesCharacteristic[$i]['furnish'];
                }

                // Контакты собственника
                $arrMyAdvertReplace['contactTelephonNumber'] = "";
                $arrMyAdvertReplace['contactTelephonNumber'] = $allPropertiesCharacteristic[$i]['contactTelephonNumber'];
                $arrMyAdvertReplace['timeForRingBegin'] = "";
                $arrMyAdvertReplace['timeForRingBegin'] = $allPropertiesCharacteristic[$i]['timeForRingBegin'];
                $arrMyAdvertReplace['timeForRingEnd'] = "";
                $arrMyAdvertReplace['timeForRingEnd'] = $allPropertiesCharacteristic[$i]['timeForRingEnd'];

                // Производим заполнение шаблона
                // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
                $arrMyAdvertTemplVar = array('{statusEng}', '{typeOfObject}', '{address}', '{apartmentNumber}', '{status}', '{fotosWrapper}', '{instructionPublish}', '{propertyId}', '{probableTenants}', '{costOfRenting}', '{currency}', '{utilities}', '{electricPower}', '{bail}', '{prepayment}', '{termOfLease}', '{dateOfEntry}', '{dateOfCheckOut}', '{furnitureName}', '{furniture}', '{repairName}', '{repair}', '{contactTelephonNumber}', '{timeForRingBegin}', '{timeForRingEnd}');
                // Копируем html-текст шаблона
                $currentMyAdvert = str_replace($arrMyAdvertTemplVar, $arrMyAdvertReplace, $tmpl_MyAdvert);

                // Сформированный блок с описанием объявления добавляем в общую копилку. На вкладке tabs-3 (Мои объявления) полученный HTML всех блоков вставим в страницу.
                $briefOfAdverts .= $currentMyAdvert; // Добавим html-текст еще одного объявления. Готовим html-текст к добавлению на вкладку tabs-3 в Мои объявления
            }

            return $briefOfAdverts;
        }
    }
