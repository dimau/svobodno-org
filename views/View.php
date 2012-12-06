<?php
	/* Статический класс, содержащий методы, используемые при формировании представления (HTML) */

    class View
    {
        // КОНСТРУКТОР делаем недоступным для вызова - данный класс является статическим и не нуждается в создании экземпляров
        private function __construct() {}

        // Метод возвращает блок (div) для отображения фотографии (и, если нужно, по клику галереи фотографий) пользователя
        // На входе: $sizeForPrimary - размер основной фотографии (small, middle, big); $isInteractive - нужно ли по клику включать галерею фотографий(TRUE- нужно, FALSE - нет), $forTable - блок будет размещаться в таблице? (если TRUE - блок центрируется)
        public static function getHTMLfotosWrapper($sizeForPrimary = "small", $isInteractive = FALSE, $forTable = FALSE, $uploadedFoto = FALSE)
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
				// А также делаем фотографию по умолчанию не интерактивной - в ее кликабельности нет смысла
				$arrForReplace['isInteractive'] = "fotoNonInteractive";
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
        public static function getHTMLforFavorites($propertyId = 0, $favoritesPropertysId = array(), $typeOfHTML = "stringWithIcon")
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

        /**
         * Возвращает HTML для блока с баллунами Яндекс карты
         *
         * @param $propertyFullArr - массив массивов, содержащий подробные сведения по объектам недвижимости, для которых и нужно построить список баллунов
         * @param $favoritesPropertysId - массив идентификаторов избранных объектов пользователя
         * @param $typeOfRequest - тип запроса ("search" - для страницы поиска, "favorites" - для личного кабинета, вкладка Избранное)
         * @return string - возвращаем строку, содержащую HTML для списка баллунов
         */
        public static function getMatterOfBalloonList($propertyFullArr, $favoritesPropertysId, $typeOfRequest) {

            // Проверка входящих параметров
            if (!isset($propertyFullArr) || !is_array($propertyFullArr)) return "";

            // Инициализируем, если это нужно, список избранных объявлений так, чтобы функция не сломалась в непредвиденных ситуациях
            if (!isset($favoritesPropertysId) || !is_array($favoritesPropertysId)) $favoritesPropertysId = array();

            // Инициализируем переменную для хранения содержимого невидимого блока с HTML данными для всех баллунов
            $matterOfBalloonList = "";

            // Перебираем входящий массив ($propertyFullArr), создавая соответствующие баллуны для каждого объекта недвижимости
            for ($i = 0; $i < count($propertyFullArr); $i++ ) {
                // Получаем HTML для баллуна и добавляем его в общую копилку
                $matterOfBalloonList .= View::getFullBalloonHTML($propertyFullArr[$i], $favoritesPropertysId);
            }

            return $matterOfBalloonList;
        }

        /**
         * Возвращает HTML для списка объектов недвижимости с кратким описанием
         *
         * @param $propertyFullArr - массив массивов, содержащий подробные сведения по объектам недвижимости, для которых и нужно построить список
         * @param $favoritesPropertysId - массив идентификаторов избранных объектов пользователя
         * @param $number - число, с которого нужно начать нумеровать по порядку объявления в формируемом списке
         * @param $typeOfRequest - тип запроса ("search" - для страницы поиска, "favorites" - для личного кабинета, вкладка Избранное)
         * @return string - возвращаем строку, содержащую HTML для списка
         */
        public static function getMatterOfShortList($propertyFullArr, $favoritesPropertysId, $number, $typeOfRequest){

            // Проверка входящих параметров
            if (!isset($propertyFullArr) || !is_array($propertyFullArr)) return "";

            // Инициализируем, если это нужно, список избранных объявлений так, чтобы функция не сломалась в непредвиденных ситуациях
            if (!isset($favoritesPropertysId) || !is_array($favoritesPropertysId)) $favoritesPropertysId = array();

            // Содержимое списка объявлений с краткими данными по каждому из них
            $matterOfShortList = "";

            // Перебираем входящий массив ($propertyFullArr), создавая соответствующие блоки для каждого объекта недвижимости
            for ($i = 0; $i < count($propertyFullArr); $i++ ) {
                // Получаем HTML для блока и добавляем его в общую копилку
                $matterOfShortList .= View::getShortListItemHTML($propertyFullArr[$i], $favoritesPropertysId, $number + $i);
            }

            // Если не нашлось ни одного объекта - возвращаем специальное сообщение
            if ($matterOfShortList == "") $matterOfShortList = View::searchResultIsEmptyHTML($typeOfRequest);

            return $matterOfShortList;
        }

        /**
         * Возвращает HTML для списка объектов недвижимости с подробным описанием
         *
         * @param $propertyFullArr - массив массивов, содержащий подробные сведения по объектам недвижимости, для которых и нужно построить список
         * @param $favoritesPropertysId - массив идентификаторов избранных объектов пользователя
         * @param $number - число, с которого нужно начать нумеровать по порядку объявления в формируемом списке
         * @param $typeOfRequest - тип запроса ("search" - для страницы поиска, "favorites" - для личного кабинета, вкладка Избранное)
         * @return string - возвращаем строку, содержащую HTML для списка
         */
        public static function getMatterOfFullParametersList($propertyFullArr, $favoritesPropertysId, $number, $typeOfRequest){

            // Проверка входящих параметров
            if (!isset($propertyFullArr) || !is_array($propertyFullArr)) return "";

            // Инициализируем, если это нужно, список избранных объявлений так, чтобы функция не сломалась в непредвиденных ситуациях
            if (!isset($favoritesPropertysId) || !is_array($favoritesPropertysId)) $favoritesPropertysId = array();

            // Содержимое списка объявлений с подробными данными по каждому из них
            $matterOfFullParametersList = "";

            // Перебираем входящий массив ($propertyFullArr), создавая соответствующие блоки для каждого объекта недвижимости
            for ($i = 0; $i < count($propertyFullArr); $i++ ) {
                // Получаем HTML для блока и добавляем его в общую копилку
                $matterOfFullParametersList .= View::getFullParametersListItemHTML($propertyFullArr[$i], $favoritesPropertysId, $number + $i);
            }

            // Если не нашлось ни одного объекта - возвращаем специальное сообщение
            if ($matterOfFullParametersList == "") $matterOfFullParametersList = View::searchResultIsEmptyHTML($typeOfRequest);

            return $matterOfFullParametersList;
        }

        /**
         * Возвращает HTML для всплывающего баллуна на Яндекс карте с описанием объекта недвижимости
         *
         * @param $oneProperty - ассоциированный массив данных по конкретному объявлению
         * @param array $favoritesPropertysId - массив со списком идентификаторов избранных объектов текущего пользователя
         * @return mixed - строка HTML в соответствии с шаблоном баллуна
         */
        public static function getFullBalloonHTML($oneProperty, $favoritesPropertysId = array())
        {
            // Получим HTML шаблон блока из файла
            $templ = file_get_contents('templates/searchResultBlocks/fullBalloonListItem.php');

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне баллуна
            $arrBalloonReplace = array();

            // Координаты объекта
            $arrBalloonReplace['coordX'] = "";
            if (isset($oneProperty['coordX'])) $arrBalloonReplace['coordX'] = $oneProperty['coordX'];
            $arrBalloonReplace['coordY'] = "";
            if (isset($oneProperty['coordY'])) $arrBalloonReplace['coordY'] = $oneProperty['coordY'];

            // Идентификатор объекта
            $arrBalloonReplace['propertyId'] = "";
            if (isset($oneProperty['id'])) $arrBalloonReplace['propertyId'] = $oneProperty['id'];

            // Тип
            $arrBalloonReplace['typeOfObject'] = "";
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "0") $arrBalloonReplace['typeOfObject'] = GlobFunc::getFirstCharUpper($oneProperty['typeOfObject']) . ": ";

            // Адрес
            $arrBalloonReplace['address'] = "";
            if (isset($oneProperty['address'])) $arrBalloonReplace['address'] = $oneProperty['address'];

            // Фото
            $arrBalloonReplace['fotosWrapper'] = "";
            $arrBalloonReplace['fotosWrapper'] = View::getHTMLfotosWrapper("small", TRUE, FALSE, $oneProperty['propertyFotos']);

            // Все, что касается СТОИМОСТИ АРЕНДЫ
            $arrBalloonReplace['costOfRenting'] = "";
			$arrBalloonReplace['currency'] = "";
			$arrBalloonReplace['costOfRentingName'] = "";
            if ($oneProperty['costOfRenting'] != "" && $oneProperty['costOfRenting'] != "0" && $oneProperty['currency'] != "" && $oneProperty['currency'] != "0") {
				$arrBalloonReplace['costOfRenting'] = $oneProperty['costOfRenting'];
				$arrBalloonReplace['currency'] = $oneProperty['currency']."/мес.";
				$arrBalloonReplace['costOfRentingName'] = "Плата:";
			}
			$arrBalloonReplace['utilities'] = "";
			$arrBalloonReplace['utilitiesName'] = "";
			if ($oneProperty['utilities'] == "да" && $arrBalloonReplace['currency'] != "0" && ($oneProperty['costInSummer'] != "0" && $oneProperty['costInWinter'] != "0")) {
				$arrBalloonReplace['utilities'] = "от " . $oneProperty['costInSummer'] . " до " . $oneProperty['costInWinter'] . " " . $oneProperty['currency'] . " в месяц";
				$arrBalloonReplace['utilitiesName'] = "Ком. услуги:";
			}
			if ($oneProperty['utilities'] == "да" && $arrBalloonReplace['currency'] != "0" && ($oneProperty['costInSummer'] == "0" || $oneProperty['costInWinter'] == "0")) {
				$arrBalloonReplace['utilities'] = "оплачиваются дополнительно";
				$arrBalloonReplace['utilitiesName'] = "Ком. услуги:";
			}
			if ($oneProperty['utilities'] == "нет") {
				$arrBalloonReplace['utilities'] = "включены в стоимость";
				$arrBalloonReplace['utilitiesName'] = "Ком. услуги:";
			}
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
            $arrBalloonReplace['areaNames'] = "Площадь (";
            $arrBalloonReplace['areaValues'] = "";
			$arrBalloonReplace['areaValuesMeasure'] = "";
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "квартира" && $oneProperty['typeOfObject'] != "дом" && $oneProperty['typeOfObject'] != "таунхаус" && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['roomSpace'] != "0.00") {
                $arrBalloonReplace['areaNames'] .= "комнаты";
                $arrBalloonReplace['areaValues'] .= $oneProperty['roomSpace'];
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['totalArea'] != "0.00") {
                $arrBalloonReplace['areaNames'] .= "общая";
                $arrBalloonReplace['areaValues'] .= $oneProperty['totalArea'];
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['livingSpace'] != "0.00") {
                $arrBalloonReplace['areaNames'] .= "/жилая";
                $arrBalloonReplace['areaValues'] .= " / " . $oneProperty['livingSpace'];
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['kitchenSpace'] != "0.00") {
                $arrBalloonReplace['areaNames'] .= "/кухни";
                $arrBalloonReplace['areaValues'] .= " / " . $oneProperty['kitchenSpace'];
            }
			if ($arrBalloonReplace['areaNames'] == "Площадь (") {
				$arrBalloonReplace['areaNames'] = "";
			} else {
				$arrBalloonReplace['areaNames'] .= "):";
				$arrBalloonReplace['areaValuesMeasure'] = "м²";
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
			$furnitureInLivingArea = unserialize($oneProperty['furnitureInLivingArea']);
			$furnitureInKitchen = unserialize($oneProperty['furnitureInKitchen']);
			$appliances = unserialize($oneProperty['appliances']);
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "0" && $oneProperty['typeOfObject'] != "гараж" && !($oneProperty['completeness'] == "0" && count($furnitureInLivingArea) == 0 && count($furnitureInKitchen) == 0 && count($appliances) == 0 && $oneProperty['furnitureInLivingAreaExtra'] == "" && $oneProperty['furnitureInKitchenExtra'] == "" && $oneProperty['appliancesExtra'] == "")) {
				$arrBalloonReplace['furnitureName'] = "Мебель:";
				if ((isset($oneProperty['furnitureInLivingArea']) && count($furnitureInLivingArea) != 0) || (isset($oneProperty['furnitureInLivingAreaExtra']) && $oneProperty['furnitureInLivingAreaExtra'] != "")) $arrBalloonReplace['furniture'] = "есть в жилой зоне";
				if ((isset($oneProperty['furnitureInKitchen']) && count($furnitureInKitchen) != 0) || (isset($oneProperty['furnitureInKitchenExtra']) && $oneProperty['furnitureInKitchenExtra'] != "")) if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "есть на кухне"; else $arrBalloonReplace['furniture'] .= ", есть на кухне";
				if ((isset($oneProperty['appliances']) && count($appliances) != 0) || (isset($oneProperty['appliancesExtra']) && $oneProperty['appliancesExtra'] != "")) if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "есть бытовая техника"; else $arrBalloonReplace['furniture'] .= ", есть бытовая техника";
				if ($arrBalloonReplace['furniture'] == "") $arrBalloonReplace['furniture'] = "нет";
            }

            // Избранное
            if (!($arrBalloonReplace['favorites'] = View::getHTMLforFavorites($oneProperty['id'], $favoritesPropertysId, "stringWithIcon"))) {
                $arrBalloonReplace['favorites'] = "";
            }

            // Производим заполнение шаблона баллуна
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrBalloonTemplVar = array('{coordX}', '{coordY}', '{propertyId}', '{typeOfObject}', '{address}', '{fotosWrapper}', '{costOfRenting}', '{currency}', '{costOfRentingName}', '{utilities}', '{utilitiesName}', '{compensationMoney}', '{compensationPercent}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaNames}', '{areaValues}', '{areaValuesMeasure}', '{floorName}', '{floor}', '{furnitureName}', '{furniture}', '{favorites}');
            // Копируем html-текст шаблона баллуна
            $currentAdvertBalloonList = str_replace($arrBalloonTemplVar, $arrBalloonReplace, $templ);

            return $currentAdvertBalloonList;
        }

        /**
         * Возвращает HTML для блока с картким описанием объекта недвижимости (используется при отображении результатов поиска Список + Карта)
         *
         * @param $oneProperty - ассоциированный массив данных по конкретному объявлению
         * @param array $favoritesPropertysId - массив со списком идентификаторов избранных объектов текущего пользователя
         * @param $number - указывает какое число нужно присвоить блоку для его нумераци в выдаче
         * @return mixed - строка HTML в соответствии с шаблоном блока с кратким описанием объекта недвижимости
         */
       public static function getShortListItemHTML($oneProperty, $favoritesPropertysId = array(), $number)
       {
           // Получим HTML шаблон блока из файла
           $tmpl_shortAdvert = file_get_contents('templates/searchResultBlocks/shortListItem.php');

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
           $arrShortListReplace['fotosWrapper'] = View::getHTMLfotosWrapper("small", TRUE, TRUE, $oneProperty['propertyFotos']);

           // Тип
           $arrShortListReplace['typeOfObject'] = "";
           if ($oneProperty['typeOfObject'] != "" && $oneProperty['typeOfObject'] != "0") $arrShortListReplace['typeOfObject'] = GlobFunc::getFirstCharUpper($oneProperty['typeOfObject']) . ":";

           // Адрес
           $arrShortListReplace['address'] = "";
           if (isset($oneProperty['address'])) $arrShortListReplace['address'] = $oneProperty['address'];

           // Стоимость
           $arrShortListReplace['costOfRenting'] = "";
		   $arrShortListReplace['currency'] = "";
		   $arrShortListReplace['costOfRentingName'] = "";
           if (isset($oneProperty['costOfRenting']) && $oneProperty['costOfRenting'] != "" && $oneProperty['costOfRenting'] != "0" && isset($oneProperty['currency']) && $oneProperty['currency'] != "" && $oneProperty['currency'] != "0") {
			   $arrShortListReplace['costOfRenting'] = $oneProperty['costOfRenting'];
			   $arrShortListReplace['currency'] = $oneProperty['currency']."/мес.";
		   }
           $arrShortListReplace['utilities'] = "";
           if (isset($oneProperty['utilities']) && $oneProperty['utilities'] == "да") $arrShortListReplace['utilities'] = " <span style='white-space: nowrap;'>+ ком.усл.</span>";
			if ($arrShortListReplace['costOfRenting'] != "") $arrShortListReplace['costOfRentingName'] = "Плата:";

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
		   $arrShortListReplace['areaValuesName'] = "";
		   $arrShortListReplace['areaValuesMeasure'] = "";
           if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "квартира" && $oneProperty['typeOfObject'] != "дом" && $oneProperty['typeOfObject'] != "таунхаус" && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['roomSpace'] != "0.00") {
               $arrShortListReplace['areaValues'] .= $oneProperty['roomSpace'];
           }
           if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['totalArea'] != "0.00") {
               $arrShortListReplace['areaValues'] .= $oneProperty['totalArea'];
           }
           if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['livingSpace'] != "0.00") {
               $arrShortListReplace['areaValues'] .= " / " . $oneProperty['livingSpace'];
           }
           if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['kitchenSpace'] != "0.00") {
               $arrShortListReplace['areaValues'] .= " / " . $oneProperty['kitchenSpace'];
           }
		   if ($arrShortListReplace['areaValues'] != "") {
			   $arrShortListReplace['areaValuesName'] = "Площадь:";
			   $arrShortListReplace['areaValuesMeasure'] = "м²";
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
           // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
           $arrShortListTemplVar = array('{propertyId}', '{number}', '{actionFavorites}', '{imgFavorites}', '{fotosWrapper}', '{typeOfObject}', '{address}', '{costOfRenting}', '{currency}', '{costOfRentingName}', '{utilities}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{areaValuesName}', '{areaValuesMeasure}', '{floorName}', '{floor}');
           // Копируем html-текст шаблона блока (строки таблицы)
           $currentAdvertShortList = str_replace($arrShortListTemplVar, $arrShortListReplace, $tmpl_shortAdvert);

           return $currentAdvertShortList;
       }

        /**
         * Возвращает HTML для блока с подробным описанием объекта недвижимости (используется при отображении результатов поиска в режиме "Список")
         *
         * @param $oneProperty - ассоциированный массив данных по конкретному объявлению
         * @param array $favoritesPropertysId - массив со списком идентификаторов избранных объектов текущего пользователя
         * @param $number - указывает какое число нужно присвоить блоку для его нумераци в выдаче
         * @return mixed - строка HTML в соответствии с шаблоном блока с подробным описанием объекта недвижимости
         */
        public static function getFullParametersListItemHTML($oneProperty, $favoritesPropertysId = array(), $number)
        {
            // Получим HTML шаблон блока из файла
            $tmpl_extendedAdvert = file_get_contents('templates/searchResultBlocks/fullListItem.php');

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
            $arrExtendedListReplace['fotosWrapper'] = View::getHTMLfotosWrapper("small", TRUE, TRUE, $oneProperty['propertyFotos']);

            // Тип
            $arrExtendedListReplace['typeOfObject'] = "<br>";
            if (isset($oneProperty['typeOfObject'])) $arrExtendedListReplace['typeOfObject'] = GlobFunc::getFirstCharUpper($oneProperty['typeOfObject']) . "<br>";

            // Район
            $arrExtendedListReplace['district'] = "";
            if (isset($oneProperty['district']) && $oneProperty['district'] != "0") $arrExtendedListReplace['district'] = $oneProperty['district'] . "<br>";

            // Адрес
            $arrExtendedListReplace['address'] = "";
            if (isset($oneProperty['address'])) $arrExtendedListReplace['address'] = $oneProperty['address'];

            // Комнаты
            if (isset($oneProperty['amountOfRooms']) && $oneProperty['amountOfRooms'] != "0") {
                $arrExtendedListReplace['amountOfRooms'] = "<span title='количество комнат'>" . $oneProperty['amountOfRooms'] . "</span><br>";
            } else {
                $arrExtendedListReplace['amountOfRooms'] = "<span title='нет данных о количестве комнат'>-</span><br>";
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
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "квартира" && $oneProperty['typeOfObject'] != "дом" && $oneProperty['typeOfObject'] != "таунхаус" && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['roomSpace'] != "" && $oneProperty['roomSpace'] != "0.00") {
                $arrExtendedListReplace['areaValues'] .= "<span title='площадь комнаты'>" . $oneProperty['roomSpace'] . " м²</span><br>";
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['totalArea'] != "" && $oneProperty['totalArea'] != "0.00") {
                $arrExtendedListReplace['areaValues'] .= "<span title='общая площадь'>" . $oneProperty['totalArea'] . " м²</span><br>";
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "комната" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['livingSpace'] != "" && $oneProperty['livingSpace'] != "0.00") {
                $arrExtendedListReplace['areaValues'] .= "<span title='жилая площадь'>" . $oneProperty['livingSpace'] . " м²</span><br>";
            }
            if (isset($oneProperty['typeOfObject']) && $oneProperty['typeOfObject'] != "дача" && $oneProperty['typeOfObject'] != "гараж" && $oneProperty['kitchenSpace'] != "" && $oneProperty['kitchenSpace'] != "0.00") {
                $arrExtendedListReplace['areaValues'] .= "<span title='площадь кухни'>" . $oneProperty['kitchenSpace'] . " м²</span><br>";
            }
			if ($arrExtendedListReplace['areaValues'] == "") $arrExtendedListReplace['areaValues'] = "<span title='нет данных о площади'>-</span>";

            // Этаж
            $arrExtendedListReplace['floor'] = "";
            if (isset($oneProperty['floor']) && isset($oneProperty['totalAmountFloor']) && $oneProperty['floor'] != "0" && $oneProperty['totalAmountFloor'] != "0") {
                $arrExtendedListReplace['floor'] = "<span title='этаж'>" . $oneProperty['floor'] . "</span><br><span title='общее количество этажей в доме'>из " . $oneProperty['totalAmountFloor'] . "</span>";
            }
            if (isset($oneProperty['numberOfFloor']) && $oneProperty['numberOfFloor'] != "0") {
                $arrExtendedListReplace['floor'] = "<span title='этажность дома'>" . $oneProperty['numberOfFloor'] . "</span>";
            }
            if ($arrExtendedListReplace['floor'] == "") $arrExtendedListReplace['floor'] = "<span title='нет данных об этаже'>-</span>";

            // Мебель
            $arrExtendedListReplace['furniture'] = "";
			$furnitureInLivingArea = unserialize($oneProperty['furnitureInLivingArea']);
			$furnitureInKitchen = unserialize($oneProperty['furnitureInKitchen']);
			$appliances = unserialize($oneProperty['appliances']);
            if ($oneProperty['typeOfObject'] == "0" || $oneProperty['typeOfObject'] == "гараж" || ($oneProperty['completeness'] == "0" && count($furnitureInLivingArea) == 0 && count($furnitureInKitchen) == 0 && count($appliances) == 0 && $oneProperty['furnitureInLivingAreaExtra'] == "" && $oneProperty['furnitureInKitchenExtra'] == "" && $oneProperty['appliancesExtra'] == "")) {
				$arrExtendedListReplace['furniture'] = "<span title='нет данных о мебели и бытовой технике'>-</span>";
			} else {
				if (count($furnitureInLivingArea) != 0 || $oneProperty['furnitureInLivingAreaExtra'] != "") $arrExtendedListReplace['furniture'] .= "<span title='с мебелью в жилой зоне'>+</span><br>"; else $arrExtendedListReplace['furniture'] .= "<span title='без мебели в жилой зоне'>-</span><br>";
				if (count($furnitureInKitchen) != 0 || $oneProperty['furnitureInKitchenExtra'] != "") $arrExtendedListReplace['furniture'] .= "<span title='с мебелью на кухне'>+</span><br>"; else $arrExtendedListReplace['furniture'] .= "<span title='без мебели на кухне'>-</span><br>";
				if (count($appliances) != 0 || $oneProperty['appliancesExtra'] != "") $arrExtendedListReplace['furniture'] .= "<span title='с бытовой техникой'>+</span><br>"; else $arrExtendedListReplace['furniture'] .= "<span title='без бытовой техники'>-</span><br>";
            }

            // Все, что касается СТОИМОСТИ АРЕНДЫ
            $arrExtendedListReplace['costOfRenting'] = "";
            if ($oneProperty['costOfRenting'] != "" && $oneProperty['costOfRenting'] != "0" && $oneProperty['currency'] != "" && $oneProperty['currency'] != "0") $arrExtendedListReplace['costOfRenting'] = "<span title='стоимость аренды в месяц'>" . $oneProperty['costOfRenting'] . " " . $oneProperty['currency'] . "</span><br>";
            $arrExtendedListReplace['utilities'] = "";
            if ($oneProperty['utilities'] == "да") $arrExtendedListReplace['utilities'] = "<span title='коммунальные услуги оплачиваются дополнительно'>+ ком. усл.</span><br>";
			$arrExtendedListReplace['compensationMoney'] = "";
            $arrExtendedListReplace['compensationPercent'] = "";
            if ($oneProperty['compensationMoney'] != "" && $oneProperty['compensationMoney'] != "0.00" && $oneProperty['compensationPercent'] != "" && $oneProperty['compensationPercent'] != "0.00" && $oneProperty['currency'] != "" && $oneProperty['currency'] != "0") {
                $arrExtendedListReplace['compensationMoney'] = "<span title='единоразовая комиссия при заключении договора'>" . $oneProperty['compensationMoney'] . " " . $oneProperty['currency'];
                $arrExtendedListReplace['compensationPercent'] = "(". $oneProperty['compensationPercent'] . "%)</span>";
            } else {
				$arrExtendedListReplace['compensationMoney'] = "<span title='нет данных о цене'>-</span>";
			}

            // Производим заполнение шаблона строки (блока) fullParametersList таблицы по данному объекту недвижимости
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrExtendedListTemplVar = array('{propertyId}', '{number}', '{actionFavorites}', '{imgFavorites}', '{fotosWrapper}', '{typeOfObject}', '{district}', '{address}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{floor}', '{furniture}', '{costOfRenting}', '{utilities}', '{compensationMoney}', '{compensationPercent}');
            // Копируем html-текст шаблона блока (строки таблицы)
            $currentAdvertExtendedList = str_replace($arrExtendedListTemplVar, $arrExtendedListReplace, $tmpl_extendedAdvert);

            return $currentAdvertExtendedList;
        }

        // Возвращает HTML, который нужно поместить на страницу при отсутствии результатов поиска
        public static function searchResultIsEmptyHTML($typeOfRequest)
        {
            $searchResultHTML = "";

            // Вычисляем сколько всего опубликовано объявлений
            $res = DBconnect::get()->query("SELECT COUNT(*) FROM property WHERE status = 'опубликовано'");
            if ((DBconnect::get()->errno)
                OR (($amountOfRows = $res->fetch_row()) === NULL)
            ) {
                // Логируем ошибку
                //TODO: сделать логирование ошибки
                $allAmountAdverts = "";
            } else {
                $allAmountAdverts = $amountOfRows[0];
            }

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
        public static function getHTMLforOwnersCollectionProperty($allPropertiesCharacteristic, $allPropertiesFotoInformation, $allPropertiesTenantPretenders)
        {
			// Валидируем входные данные
			// Проверяем наличие хотя бы 1 объекта недвижимости, в противном случае отдаем пустую HTML строку
			if (!isset($allPropertiesCharacteristic) || !isset($allPropertiesFotoInformation) || !isset($allPropertiesTenantPretenders)) return "";
            if (!is_array($allPropertiesCharacteristic) || count($allPropertiesCharacteristic) == 0 || !is_array($allPropertiesFotoInformation) || !is_array($allPropertiesTenantPretenders)) return "";

			// Получим из файла HTML шаблон блока для описания отдельного объекта недвижимости
			$tmpl_MyAdvert = file_get_contents('templates/templ_descriptionPropertyForOwner.php');

            // Создаем бриф для каждого объявления пользователя на основе шаблона (для вкладки МОИ ОБЪЯВЛЕНИЯ), и в цикле объединяем их в один HTML блок - $briefOfAdverts.
            // Если объявлений у пользователя несколько, то в переменную, содержащую весь HTML - $briefOfAdverts, записываем каждое из них последовательно
            $briefOfAdverts = "";
            for ($i = 0, $s = count($allPropertiesCharacteristic); $i < $s; $i++) {

                // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
                $arrMyAdvertReplace = array();

                // Подставляем класс в заголовок html объявления для применения соответствующего css оформления
                $arrMyAdvertReplace['statusEng'] = "";
                if ($allPropertiesCharacteristic[$i]['status'] == "не опубликовано") $arrMyAdvertReplace['statusEng'] = "unpublished";
                if ($allPropertiesCharacteristic[$i]['status'] == "опубликовано") $arrMyAdvertReplace['statusEng'] = "published";

                // В заголовке блока отображаем тип недвижимости, для красоты первую букву типа сделаем в верхнем регистре
                $arrMyAdvertReplace['typeOfObject'] = "";
                $arrMyAdvertReplace['typeOfObject'] = GlobFunc::getFirstCharUpper($allPropertiesCharacteristic[$i]['typeOfObject']);

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
                if (isset($allPropertiesFotoInformation[$i])) $fotosArr = $allPropertiesFotoInformation[$i]; else $fotosArr = array();
                $arrMyAdvertReplace['fotosWrapper'] = View::getHTMLfotosWrapper("small", FALSE, FALSE, $fotosArr);

                // Корректируем список инструкций, доступных пользователю
                $arrMyAdvertReplace['instructionPublish'] = "";
                $arrMyAdvertReplace['propertyId'] = "";
                if ($allPropertiesCharacteristic[$i]['status'] == "опубликовано") {
                    $arrMyAdvertReplace['instructionPublish'] = "<li><a href='personal.php?compId=".GlobFunc::idToCompId($allPropertiesCharacteristic[$i]['userId'])."&propertyId=" . $allPropertiesCharacteristic[$i]['id'] . "&action=publicationOff'>снять с публикации</a></li>";
                }
                if ($allPropertiesCharacteristic[$i]['status'] == "не опубликовано") {
                    $arrMyAdvertReplace['instructionPublish'] = "<li><a href='personal.php?compId=".GlobFunc::idToCompId($allPropertiesCharacteristic[$i]['userId'])."&propertyId=" . $allPropertiesCharacteristic[$i]['id'] . "&action=publicationOn'>опубликовать</a></li>";
                }
                $arrMyAdvertReplace['propertyId'] = $allPropertiesCharacteristic[$i]['id'];

				// Дата и время ближайшего просмотра
				$arrMyAdvertReplace['earliestDateName'] = "";
				$arrMyAdvertReplace['earliestDate'] = "";
				if ($allPropertiesCharacteristic[$i]['earliestDate'] != "" && $allPropertiesCharacteristic[$i]['earliestDate'] != "0000-00-00" && $allPropertiesCharacteristic[$i]['earliestTimeHours'] != "" && $allPropertiesCharacteristic[$i]['earliestTimeMinutes'] != "") {
					$arrMyAdvertReplace['earliestDateName'] = "Назначен просмотр:";
					$arrMyAdvertReplace['earliestDate'] = $allPropertiesCharacteristic[$i]['earliestDate']." в ".$allPropertiesCharacteristic[$i]['earliestTimeHours'].":".$allPropertiesCharacteristic[$i]['earliestTimeMinutes'];
				}


                /******* Список потенциальных арендаторов ******/
                $arrMyAdvertReplace['probableTenants'] = "";
                if (isset($allPropertiesTenantPretenders[$i]) && is_array($allPropertiesTenantPretenders[$i])) {
                    for ($j = 0, $s1 = count($allPropertiesTenantPretenders[$i]); $j < $s1; $j++) {
                        // Перебираем данные по потенциальным арендаторам, проявившим интерес к данному объекту и добавляем их в строку $arrMyAdvertReplace['probableTenants']
                        // Формируем из имен и отчеств строку гиперссылок с ссылками на страницы арендаторов
                        if ($allPropertiesTenantPretenders[$i][$j]['typeTenant'] == "TRUE") { // Если данный пользователь (арендатор) еще ищет недвижимость
                            $compId = GlobFunc::idToCompId($allPropertiesTenantPretenders[$i][$j]['id']);
                            $arrMyAdvertReplace['probableTenants'] .= '<a target="_blank" href="man.php?compId=' . $compId . '">' . $allPropertiesTenantPretenders[$i][$j]['name'] . " " . $allPropertiesTenantPretenders[$i][$j]['secondName'] . "</a>";
                        } else {
                            $arrMyAdvertReplace['probableTenants'] .= "<span title='Пользователь уже нашел недвижимость'>" . $allPropertiesTenantPretenders[$i][$j]['name'] . " " . $allPropertiesTenantPretenders[$i][$j]['secondName'] . "</span>";
                        }
                        if ($j < $s1 - 1) $arrMyAdvertReplace['probableTenants'] .= ", ";
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
                $arrMyAdvertReplace['dateOfEntry'] = GlobFunc::dateFromDBToView($allPropertiesCharacteristic[$i]['dateOfEntry']);
                if ($allPropertiesCharacteristic[$i]['dateOfCheckOut'] != "0000-00-00") $arrMyAdvertReplace['dateOfCheckOut'] = " по " . GlobFunc::dateFromDBToView($allPropertiesCharacteristic[$i]['dateOfCheckOut']);

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
                $arrMyAdvertTemplVar = array('{statusEng}', '{typeOfObject}', '{address}', '{apartmentNumber}', '{status}', '{fotosWrapper}', '{instructionPublish}', '{propertyId}', '{earliestDateName}', '{earliestDate}', '{probableTenants}', '{costOfRenting}', '{currency}', '{utilities}', '{electricPower}', '{bail}', '{prepayment}', '{termOfLease}', '{dateOfEntry}', '{dateOfCheckOut}', '{furnitureName}', '{furniture}', '{repairName}', '{repair}', '{contactTelephonNumber}', '{timeForRingBegin}', '{timeForRingEnd}');
                // Копируем html-текст шаблона
                $currentMyAdvert = str_replace($arrMyAdvertTemplVar, $arrMyAdvertReplace, $tmpl_MyAdvert);

                // Сформированный блок с описанием объявления добавляем в общую копилку. На вкладке tabs-3 (Мои объявления) полученный HTML всех блоков вставим в страницу.
                $briefOfAdverts .= $currentMyAdvert; // Добавим html-текст еще одного объявления. Готовим html-текст к добавлению на вкладку tabs-3 в Мои объявления
            }

            return $briefOfAdverts;
        }

        /**
         * Возвращает HTML всех блоков с новостями для Личного кабинета пользователя
         *
         * @param $messagesArr - массив ассоциированных массивов, каждый из которых представляет сведения по 1 новости
         * @return string
         */
        public static function getHTMLforMessages($messagesArr) {

            // Если массив с уведомлениями пользователя не передан, то возвращаем пустую строку вместо HTML
            if (!isset($messagesArr) || !is_array($messagesArr) || count($messagesArr) == 0) {
                return "<div class='shadowText'>
                            На этой вкладке располагается информация о важных событиях, случившихся на ресурсе Svobodno.org, как например: появление новых потенциальных арендаторов, заинтересовавшихся Вашим объявлением, или новых объявлений, которые подходят под Ваш запрос
                        </div>";
            }

            // Инициализируем переменную, которую в итоге вернем в качестве результата выполнения метода
            $allMessagesHTML = "";

            // Перебираем все новости, формируя для каждой на основе шаблона, блок и, складывая в общий HTML
            for ($i = 0, $s = count($messagesArr); $i < $s; $i++) {

                if ($messagesArr[$i]['messageType'] == "newProperty") $allMessagesHTML .= View::getHTMLforMessageNewProperty($messagesArr[$i]);
              /*  if ($messagesArr[$i]['messageType'] == "newTenant") $allMessagesHTML .= View::getHTMLforMessageNewTenant($messagesArr[$i]);
                if ($messagesArr[$i]['messageType'] == "requestToViewConfirmed") $allMessagesHTML .= View::getHTMLforMessageRequestToViewConfirmed($messagesArr[$i]);
                if ($messagesArr[$i]['messageType'] == "editedTimeToView") $allMessagesHTML .= View::getHTMLforMessageEditedTimeToView($messagesArr[$i]); */
            }

            return $allMessagesHTML;
        }

        /**
         * Возвращает HTML для блока с описанием новости о новом объекте недвижимости
         *
         * @param $sourceArr - ассоциированный массив со сведениями об уведомлении
         * @return mixed|string
         */
        public static function getHTMLforMessageNewProperty($sourceArr)
        {
			// Получим HTML шаблон блока из файла
			$templ = file_get_contents('templates/messagesBlocks/templ_messageNewProperty.php');

            // Если массив с параметрами уведомления не передан, то возвращаем пустую строку вместо HTML блока
            if (!isset($sourceArr) || !is_array($sourceArr)) {
                return "";
            }

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
            $valuesArr = array();

            // Уведомление прочитано или не прочитано
            $valuesArr['unread'] = "";
            if (isset($sourceArr['isReaded']) && $sourceArr['isReaded'] == "не прочитано") $valuesArr['unread'] = "unread";

            // Фото
            $valuesArr['fotosWrapper'] = "";
            $valuesArr['fotosWrapper'] = View::getHTMLfotosWrapper("small", FALSE, FALSE, $sourceArr['fotoArr']);

            // Идентификатор объекта
            $valuesArr['propertyId'] = "";
            if (isset($sourceArr['targetId'])) $valuesArr['propertyId'] = $sourceArr['targetId'];

            // Тип
            $valuesArr['typeOfObject'] = "";
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "0") $valuesArr['typeOfObject'] = GlobFunc::getFirstCharUpper($sourceArr['typeOfObject']) . ":";

            // Адрес
            $valuesArr['address'] = "";
            if (isset($sourceArr['address']) && $sourceArr['address'] != "") $valuesArr['address'] = $sourceArr['address'];

            // Стоимость
            $valuesArr['costOfRenting'] = "";
            if (isset($sourceArr['costOfRenting']) && $sourceArr['costOfRenting'] != "" && $sourceArr['costOfRenting'] != "0.00") $valuesArr['costOfRenting'] = $sourceArr['costOfRenting'];
            $valuesArr['currency'] = "";
            if (isset($sourceArr['currency']) && $sourceArr['currency'] != "0") $valuesArr['currency'] = $sourceArr['currency']."/мес.";
            $valuesArr['utilities'] = "";
            if (isset($sourceArr['utilities']) && $sourceArr['utilities'] == "да") $valuesArr['utilities'] = " <span style='white-space: nowrap;'>+ ком.усл.</span>";

            // Комнаты
            if (isset($sourceArr['amountOfRooms']) && $sourceArr['amountOfRooms'] != "0") {
                $valuesArr['amountOfRoomsName'] = "Комнат:";
                $valuesArr['amountOfRooms'] = $sourceArr['amountOfRooms'];
            } else {
                $valuesArr['amountOfRoomsName'] = "";
                $valuesArr['amountOfRooms'] = "";
            }
            if (isset($sourceArr['adjacentRooms']) && $sourceArr['adjacentRooms'] == "да") {
                if (isset($sourceArr['amountOfAdjacentRooms']) && $sourceArr['amountOfAdjacentRooms'] != "0") {
                    $valuesArr['adjacentRooms'] = ", смежных: " . $sourceArr['amountOfAdjacentRooms'];
                } else {
                    $valuesArr['adjacentRooms'] = ", смежные";
                }
            } else {
                $valuesArr['adjacentRooms'] = "";
            }

            // Площади помещений
            $valuesArr['areaValues'] = "";
			$valuesArr['areaValuesName'] = "";
			$valuesArr['areaValuesMeasure'] = "";
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "квартира" && $sourceArr['typeOfObject'] != "дом" && $sourceArr['typeOfObject'] != "таунхаус" && $sourceArr['typeOfObject'] != "дача" && $sourceArr['typeOfObject'] != "гараж" && $sourceArr['roomSpace'] != "0.00") {
                $valuesArr['areaValues'] .= $sourceArr['roomSpace'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "комната" && $sourceArr['totalArea'] != "0.00") {
                $valuesArr['areaValues'] .= $sourceArr['totalArea'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "комната" && $sourceArr['typeOfObject'] != "гараж" && $sourceArr['livingSpace'] != "0.00") {
                $valuesArr['areaValues'] .= " / " . $sourceArr['livingSpace'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "дача" && $sourceArr['typeOfObject'] != "гараж" && $sourceArr['kitchenSpace'] != "0.00") {
                $valuesArr['areaValues'] .= " / " . $sourceArr['kitchenSpace'];
            }
			if ($valuesArr['areaValues'] != "") {
				$valuesArr['areaValuesName'] = "Площадь:";
				$valuesArr['areaValuesMeasure'] = "м²";
			}

            // Этаж
            $valuesArr['floorName'] = "";
            $valuesArr['floor'] = "";
            if (isset($sourceArr['floor']) && isset($sourceArr['totalAmountFloor']) && $sourceArr['floor'] != "0" && $sourceArr['totalAmountFloor'] != "0") {
                $valuesArr['floorName'] = "Этаж:";
                $valuesArr['floor'] = $sourceArr['floor'] . " из " . $sourceArr['totalAmountFloor'];
            }
            if (isset($sourceArr['numberOfFloor']) && $sourceArr['numberOfFloor'] != "0") {
                $valuesArr['floorName'] = "Этажность:";
                $valuesArr['floor'] = $sourceArr['numberOfFloor'];
            }

            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
            $stringForReplaceArr = array('{unread}', '{fotosWrapper}', '{propertyId}', '{typeOfObject}', '{address}', '{costOfRenting}', '{currency}', '{utilities}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{areaValuesName}', '{areaValuesMeasure}', '{floorName}', '{floor}');
            // Заполнение шаблона
            $resultHTML = str_replace($stringForReplaceArr, $valuesArr, $templ);

            return $resultHTML;
        }

        /**
         * Возвращает HTML для блока с описанием новости о новом претенденте на аренду недвижимости
         *
         * @param $sourceArr - ассоциированный массив со сведениями об уведомлении
         * @return mixed|string
         */
       /* public static function getHTMLforMessageNewTenant($sourceArr)
        {
            // Шаблон блока
            $templ = "
                <div class='news {unread}'>
                    <div class='newsHeader'>
                        Новый претендент
                    </div>
                    На Вашу недвижимость: {address}{apartmentNumber}
                    {fotosWrapper}
                    <ul class='setOfInstructions'>
                        <li>
                            <a href='#'>прочитал</a>
                        </li>
                        <li>
                            <a href='#'>удалить</a>
                        </li>
                        <li>
                            <a href='man.php?compId={targetId}' target='_blank'>подробнее</a>
                        </li>
                    </ul>
                    <ul class='listDescriptionSmall'>
                        <li>
                            <span class='headOfString'>ФИО:</span> {fio}
                        </li>
                        <li>
                            <span class='headOfString'>Возраст:</span> {age}
                        </li>
                        <li>
                            <span class='headOfString'>С кем:</span> {withWho}
                        </li>
                        <li>
                            <span class='headOfString'>Дети:</span> {children}
                        </li>
                        <li>
                            <span class='headOfString'>Домашние животные:</span> {animals}
                        </li>
                    </ul>
                    <div class='clearBoth'></div>
                </div>
            ";

            // Если массив с параметрами уведомления не передан, то возвращаем пустую строку вместо HTML блока
            if (!isset($sourceArr) || !is_array($sourceArr)) {
                return "";
            }

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
            $valuesArr = array();

            // Уведомление прочитано или не прочитано
            $valuesArr['unread'] = "";
            if (isset($sourceArr['isReaded']) && $sourceArr['isReaded'] == "не прочитано") $valuesArr['unread'] = "unread";

            // Адрес
            $valuesArr['address'] = "";
            if (isset($sourceArr['address'])) $valuesArr['address'] = $sourceArr['address'];
            $valuesArr['apartmentNumber'] = "";
            if (isset($sourceArr['apartmentNumber']) && $sourceArr['apartmentNumber'] != "") $valuesArr['apartmentNumber'] = ", № " . $sourceArr['apartmentNumber'];

            // Фото
            $valuesArr['fotosWrapper'] = "";
            $valuesArr['fotosWrapper'] = View::getHTMLfotosWrapper("small", FALSE, FALSE, $sourceArr['fotoArr']);

            // Идентификатор объекта
            $valuesArr['targetId'] = "";
            if (isset($sourceArr['targetId'])) $valuesArr['targetId'] = $sourceArr['targetId'];

            // ФИО
            $valuesArr['fio'] = "";
            if (isset($sourceArr['surname']) && isset($sourceArr['name']) && isset($sourceArr['secondName'])) $valuesArr['fio'] = $sourceArr['surname']." ".$sourceArr['name']." " .$sourceArr['secondName'];

            // Возраст
            $valuesArr['age'] = "";
            if (isset($sourceArr['birthday'])) $valuesArr['age'] = GlobFunc::calculate_age($sourceArr['birthday']);

            // С кем
            $valuesArr['withWho'] = "";
            if (isset($sourceArr['withWho'])) $valuesArr['withWho'] = $sourceArr['withWho'];

            // Дети
            $valuesArr['children'] = "";
            if (isset($sourceArr['children'])) $valuesArr['children'] = $sourceArr['children'];

            // Домашние животные
            $valuesArr['animals'] = "";
            if (isset($sourceArr['animals'])) $valuesArr['animals'] = $sourceArr['animals'];

            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
            $stringForReplaceArr = array('{unread}', '{address}', '{apartmentNumber}', '{fotosWrapper}', '{targetId}', '{fio}', '{age}', '{withWho}', '{children}', '{animals}');
            // Заполнение шаблона
            $resultHTML = str_replace($stringForReplaceArr, $valuesArr, $templ);

            return $resultHTML;
        }

        /**
         * Возвращает HTML для блока с описанием новости о назначении даты и времени просмотра недвижимости
         *
         * @param $sourceArr - ассоциированный массив со сведениями об уведомлении
         * @return mixed|string
         * TODO: реализовать
         */
    /*    public static function getHTMLforMessageRequestToViewConfirmed($sourceArr)
        {
            // Шаблон блока
            $templ = "
                <div class='news {unread}'>
                    <div class='newsHeader'>
                        Новое предложение по Вашему поиску
                    </div>
                    {fotosWrapper}
                    <ul class='setOfInstructions'>
                        <li>
                            <a href='#'>прочитал</a>
                        </li>
                        <li>
                            <a href='#'>удалить</a>
                        </li>
                        <li>
                            <a href='objdescription.php?propertyId={propertyId}' target='_blank'>подробнее</a>
                        </li>
                    </ul>
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
                    <div class='clearBoth'></div>
                </div>
            ";

            // Если массив с параметрами уведомления не передан, то возвращаем пустую строку вместо HTML блока
            if (!isset($sourceArr) || !is_array($sourceArr)) {
                return "";
            }

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
            $valuesArr = array();

            // Уведомление прочитано или не прочитано
            $valuesArr['unread'] = "";
            if (isset($sourceArr['isReaded']) && $sourceArr['isReaded'] == "не прочитано") $valuesArr['unread'] = "unread";

            // Фото
            $valuesArr['fotosWrapper'] = "";
            $valuesArr['fotosWrapper'] = View::getHTMLfotosWrapper("small", FALSE, FALSE, $sourceArr['fotoArr']);

            // Идентификатор объекта
            $valuesArr['propertyId'] = "";
            if (isset($sourceArr['targetId'])) $valuesArr['propertyId'] = $sourceArr['targetId'];

            // Тип
            $valuesArr['typeOfObject'] = "";
            if (isset($sourceArr['typeOfObject'])) $valuesArr['typeOfObject'] = GlobFunc::getFirstCharUpper($sourceArr['typeOfObject']) . ":";

            // Адрес
            $valuesArr['address'] = "";
            if (isset($sourceArr['address'])) $valuesArr['address'] = $sourceArr['address'];

            // Стоимость
            $valuesArr['costOfRenting'] = "";
            if (isset($sourceArr['costOfRenting'])) $valuesArr['costOfRenting'] = $sourceArr['costOfRenting'];
            $valuesArr['currency'] = "";
            if (isset($sourceArr['currency'])) $valuesArr['currency'] = $sourceArr['currency'];
            $valuesArr['utilities'] = "";
            if (isset($sourceArr['utilities']) && $sourceArr['utilities'] == "да") $valuesArr['utilities'] = " <span style='white-space: nowrap;'>+ ком.усл.</span>";

            // Комнаты
            if (isset($sourceArr['amountOfRooms']) && $sourceArr['amountOfRooms'] != "0") {
                $valuesArr['amountOfRoomsName'] = "Комнат:";
                $valuesArr['amountOfRooms'] = $sourceArr['amountOfRooms'];
            } else {
                $valuesArr['amountOfRoomsName'] = "";
                $valuesArr['amountOfRooms'] = "";
            }
            if (isset($sourceArr['adjacentRooms']) && $sourceArr['adjacentRooms'] == "да") {
                if (isset($sourceArr['amountOfAdjacentRooms']) && $sourceArr['amountOfAdjacentRooms'] != "0") {
                    $valuesArr['adjacentRooms'] = ", смежных: " . $sourceArr['amountOfAdjacentRooms'];
                } else {
                    $valuesArr['adjacentRooms'] = ", смежные";
                }
            } else {
                $valuesArr['adjacentRooms'] = "";
            }

            // Площади помещений
            $valuesArr['areaValues'] = "";
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "квартира" && $sourceArr['typeOfObject'] != "дом" && $sourceArr['typeOfObject'] != "таунхаус" && $sourceArr['typeOfObject'] != "дача" && $sourceArr['typeOfObject'] != "гараж") {
                $valuesArr['areaValues'] .= $sourceArr['roomSpace'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "комната") {
                $valuesArr['areaValues'] .= $sourceArr['totalArea'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "комната" && $sourceArr['typeOfObject'] != "гараж") {
                $valuesArr['areaValues'] .= " / " . $sourceArr['livingSpace'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "дача" && $sourceArr['typeOfObject'] != "гараж") {
                $valuesArr['areaValues'] .= " / " . $sourceArr['kitchenSpace'];
            }

            // Этаж
            $valuesArr['floorName'] = "";
            $valuesArr['floor'] = "";
            if (isset($sourceArr['floor']) && isset($sourceArr['totalAmountFloor']) && $sourceArr['floor'] != "0" && $sourceArr['totalAmountFloor'] != "0") {
                $valuesArr['floorName'] = "Этаж:";
                $valuesArr['floor'] = $sourceArr['floor'] . " из " . $sourceArr['totalAmountFloor'];
            }
            if (isset($sourceArr['numberOfFloor']) && $sourceArr['numberOfFloor'] != "0") {
                $valuesArr['floorName'] = "Этажность:";
                $valuesArr['floor'] = $sourceArr['numberOfFloor'];
            }

            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
            $stringForReplaceArr = array('{unread}', '{fotosWrapper}', '{propertyId}', '{typeOfObject}', '{address}', '{costOfRenting}', '{currency}', '{utilities}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{floorName}', '{floor}');
            // Заполнение шаблона
            $resultHTML = str_replace($stringForReplaceArr, $valuesArr, $templ);

            return $resultHTML;
        }

        /**
         * Возвращает HTML для блока с описанием новости об изменении даты и времени просмотра недвижимости
         *
         * @param $sourceArr - ассоциированный массив со сведениями об уведомлении
         * @return mixed|string
         * TODO: реализовать
         */
    /*    public static function getHTMLforMessageEditedTimeToView($sourceArr)
        {
            // Шаблон блока
            $templ = "
                <div class='news {unread}'>
                    <div class='newsHeader'>
                        Новое предложение по Вашему поиску
                    </div>
                    {fotosWrapper}
                    <ul class='setOfInstructions'>
                        <li>
                            <a href='#'>прочитал</a>
                        </li>
                        <li>
                            <a href='#'>удалить</a>
                        </li>
                        <li>
                            <a href='objdescription.php?propertyId={propertyId}' target='_blank'>подробнее</a>
                        </li>
                    </ul>
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
                    <div class='clearBoth'></div>
                </div>
            ";

            // Если массив с параметрами уведомления не передан, то возвращаем пустую строку вместо HTML блока
            if (!isset($sourceArr) || !is_array($sourceArr)) {
                return "";
            }

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне
            $valuesArr = array();

            // Уведомление прочитано или не прочитано
            $valuesArr['unread'] = "";
            if (isset($sourceArr['isReaded']) && $sourceArr['isReaded'] == "не прочитано") $valuesArr['unread'] = "unread";

            // Фото
            $valuesArr['fotosWrapper'] = "";
            $valuesArr['fotosWrapper'] = View::getHTMLfotosWrapper("small", FALSE, FALSE, $sourceArr['fotoArr']);

            // Идентификатор объекта
            $valuesArr['propertyId'] = "";
            if (isset($sourceArr['targetId'])) $valuesArr['propertyId'] = $sourceArr['targetId'];

            // Тип
            $valuesArr['typeOfObject'] = "";
            if (isset($sourceArr['typeOfObject'])) $valuesArr['typeOfObject'] = GlobFunc::getFirstCharUpper($sourceArr['typeOfObject']) . ":";

            // Адрес
            $valuesArr['address'] = "";
            if (isset($sourceArr['address'])) $valuesArr['address'] = $sourceArr['address'];

            // Стоимость
            $valuesArr['costOfRenting'] = "";
            if (isset($sourceArr['costOfRenting'])) $valuesArr['costOfRenting'] = $sourceArr['costOfRenting'];
            $valuesArr['currency'] = "";
            if (isset($sourceArr['currency'])) $valuesArr['currency'] = $sourceArr['currency'];
            $valuesArr['utilities'] = "";
            if (isset($sourceArr['utilities']) && $sourceArr['utilities'] == "да") $valuesArr['utilities'] = " <span style='white-space: nowrap;'>+ ком.усл.</span>";

            // Комнаты
            if (isset($sourceArr['amountOfRooms']) && $sourceArr['amountOfRooms'] != "0") {
                $valuesArr['amountOfRoomsName'] = "Комнат:";
                $valuesArr['amountOfRooms'] = $sourceArr['amountOfRooms'];
            } else {
                $valuesArr['amountOfRoomsName'] = "";
                $valuesArr['amountOfRooms'] = "";
            }
            if (isset($sourceArr['adjacentRooms']) && $sourceArr['adjacentRooms'] == "да") {
                if (isset($sourceArr['amountOfAdjacentRooms']) && $sourceArr['amountOfAdjacentRooms'] != "0") {
                    $valuesArr['adjacentRooms'] = ", смежных: " . $sourceArr['amountOfAdjacentRooms'];
                } else {
                    $valuesArr['adjacentRooms'] = ", смежные";
                }
            } else {
                $valuesArr['adjacentRooms'] = "";
            }

            // Площади помещений
            $valuesArr['areaValues'] = "";
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "квартира" && $sourceArr['typeOfObject'] != "дом" && $sourceArr['typeOfObject'] != "таунхаус" && $sourceArr['typeOfObject'] != "дача" && $sourceArr['typeOfObject'] != "гараж") {
                $valuesArr['areaValues'] .= $sourceArr['roomSpace'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "комната") {
                $valuesArr['areaValues'] .= $sourceArr['totalArea'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "комната" && $sourceArr['typeOfObject'] != "гараж") {
                $valuesArr['areaValues'] .= " / " . $sourceArr['livingSpace'];
            }
            if (isset($sourceArr['typeOfObject']) && $sourceArr['typeOfObject'] != "дача" && $sourceArr['typeOfObject'] != "гараж") {
                $valuesArr['areaValues'] .= " / " . $sourceArr['kitchenSpace'];
            }

            // Этаж
            $valuesArr['floorName'] = "";
            $valuesArr['floor'] = "";
            if (isset($sourceArr['floor']) && isset($sourceArr['totalAmountFloor']) && $sourceArr['floor'] != "0" && $sourceArr['totalAmountFloor'] != "0") {
                $valuesArr['floorName'] = "Этаж:";
                $valuesArr['floor'] = $sourceArr['floor'] . " из " . $sourceArr['totalAmountFloor'];
            }
            if (isset($sourceArr['numberOfFloor']) && $sourceArr['numberOfFloor'] != "0") {
                $valuesArr['floorName'] = "Этажность:";
                $valuesArr['floor'] = $sourceArr['numberOfFloor'];
            }

            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне
            $stringForReplaceArr = array('{unread}', '{fotosWrapper}', '{propertyId}', '{typeOfObject}', '{address}', '{costOfRenting}', '{currency}', '{utilities}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{floorName}', '{floor}');
            // Заполнение шаблона
            $resultHTML = str_replace($stringForReplaceArr, $valuesArr, $templ);

            return $resultHTML;
        } */
    }
