<?php

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

    // $typeOfValidation = newAdvert - режим первичной (для нового объявления) проверки указанных пользователем параметров объекта недвижимости
    // $typeOfValidation = editAdvert - режим вторичной (при редактировании уже существующего объявления) проверки указанных пользователем параметров объекта недвижимости
    function isAdvertCorrect($typeOfValidation, $DBlink)
    {

        // Подготовим массив для сохранения сообщений об ошибках
        $errors = array();

        // Получаем переменные, содержащие данные пользователя, для проверки
        global $propertyId, $typeOfObject, $dateOfEntry, $termOfLease, $dateOfCheckOut, $amountOfRooms, $adjacentRooms, $amountOfAdjacentRooms, $typeOfBathrooms, $typeOfBalcony, $balconyGlazed, $roomSpace, $totalArea, $livingSpace, $kitchenSpace, $floor, $totalAmountFloor, $numberOfFloor, $concierge, $intercom, $parking, $city, $district, $coordX, $coordY, $address, $apartmentNumber, $subwayStation, $distanceToMetroStation, $currency, $costOfRenting, $utilities, $costInSummer, $costInWinter, $electricPower, $bail, $bailCost, $prepayment, $compensationMoney, $compensationPercent, $repair, $furnish, $windows, $internet, $telephoneLine, $cableTV, $furnitureInLivingArea, $furnitureInLivingAreaExtra, $furnitureInKitchen, $furnitureInKitchenExtra, $appliances, $appliancesExtra, $sexOfTenant, $relations, $children, $animals, $contactTelephonNumber, $timeForRingBegin, $timeForRingEnd, $checking, $responsibility, $comment, $fileUploadId;

        // Проверяем переменные
        if ($typeOfObject == "0") $errors[] = 'Укажите тип объекта';
        if ($dateOfEntry == "") $errors[] = 'Укажите с какого числа арендатору можно въезжать в вашу недвижимость';
        if ($dateOfEntry != "") {
            if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $dateOfEntry)) $errors[] = 'Неправильный формат даты въезда для арендатора, должен быть: дд.мм.гггг';
            if (substr($dateOfEntry, 0, 2) < "01" || substr($dateOfEntry, 0, 2) > "31") $errors[] = 'Проверьте число даты въезда (допустимо от 01 до 31)';
            if (substr($dateOfEntry, 3, 2) < "01" || substr($dateOfEntry, 3, 2) > "12") $errors[] = 'Проверьте месяц даты въезда (допустимо от 01 до 12)';
            if (substr($dateOfEntry, 6, 4) < "1000" || substr($dateOfEntry, 6, 4) > "9999") $errors[] = 'Проверьте год даты въезда (допустимо от 1000 до 9999)';
        }
        if ($termOfLease == "0") $errors[] = 'Укажите на какой срок сдается недвижимость';
        if ($dateOfCheckOut == "" && $termOfLease != "0" && $termOfLease != "длительный срок") $errors[] = 'Укажите крайний срок выезда для арендатора(ов)';
        if ($dateOfCheckOut != "") {
            if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $dateOfCheckOut)) $errors[] = 'Неправильный формат крайней даты выезда для арендатора, должен быть: дд.мм.гггг';
            if (substr($dateOfCheckOut, 0, 2) < "01" || substr($dateOfCheckOut, 0, 2) > "31") $errors[] = 'Проверьте число даты выезда (допустимо от 01 до 31)';
            if (substr($dateOfCheckOut, 3, 2) < "01" || substr($dateOfCheckOut, 3, 2) > "12") $errors[] = 'Проверьте месяц даты выезда (допустимо от 01 до 12)';
            if (substr($dateOfCheckOut, 6, 4) < "1000" || substr($dateOfCheckOut, 6, 4) > "9999") $errors[] = 'Проверьте год даты выезда (допустимо от 1000 до 9999)';
        }

        // Проверяем наличие хотя бы 1 фотографии объекта недвижимости
        if ($typeOfValidation == "newAdvert" && $fileUploadId != "") {
            $rez = getResultSQLSelect($DBlink, "SELECT * FROM tempFotos WHERE fileuploadid='" . $fileUploadId . "'");
            if (count($rez) == 0) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений';
        }
        if ($typeOfValidation == "editAdvert") // Эта ветка выполняется, если валидации производятся при попытке редактирования параметров объекта недвижимости
        {
            $rez1 = getResultSQLSelect($DBlink, "SELECT * FROM propertyFotos WHERE propertyId='" . $propertyId . "'");
            $rez2 = getResultSQLSelect($DBlink, "SELECT * FROM tempFotos WHERE fileuploadid='" . $fileUploadId . "'");
            if (count($rez1) == 0 && count($rez2) == 0) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений'; // проверка на хотя бы 1 фотку
        }
        if ($fileUploadId == "") $errors[] = 'Перезагрузите браузер, пожалуйста: возникла ошибка при формировании формы для загрузки фотографий';

        if ($amountOfRooms == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите количество комнат в квартире, доме';
        if ($adjacentRooms == "0" && $amountOfRooms != "0" && $amountOfRooms != "1") $errors[] = 'Укажите: есть ли смежные комнаты в сдаваемом объекте недвижимости';
        if ($amountOfAdjacentRooms == "0" && $typeOfObject != "0" && $typeOfObject != "комната" && $typeOfObject != "гараж" && $adjacentRooms != "0" && $adjacentRooms != "нет" && $amountOfRooms != "0" && $amountOfRooms != "1" && $amountOfRooms != "2") $errors[] = 'Укажите количество смежных комнат';
        if ($amountOfAdjacentRooms > $amountOfRooms && $typeOfObject != "0" && $typeOfObject != "комната" && $typeOfObject != "гараж" && $adjacentRooms != "0" && $adjacentRooms != "нет" && $amountOfRooms != "0" && $amountOfRooms != "1" && $amountOfRooms != "2") $errors[] = 'Исправьте: количество смежных комнат не может быть больше общего количества комнат';
        if ($typeOfBathrooms == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите тип санузла';
        if ($typeOfBalcony == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите: есть ли балкон, лоджия или эркер в сдаваемом объекте недвижимости';
        if ($balconyGlazed == "0" && $typeOfBalcony != "0" && $typeOfBalcony != "нет" && $typeOfBalcony != "эркер" && $typeOfBalcony != "2 эркера и более") $errors[] = 'Укажите остекление балкона/лоджии';
        if ($roomSpace == "" && $typeOfObject != "0" && $typeOfObject != "квартира" && $typeOfObject != "дом" && $typeOfObject != "таунхаус" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите площадь комнаты';
        if ($roomSpace != "") {
            if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $roomSpace)) $errors[] = 'Неправильный формат для площади комнаты, используйте только цифры и точку, например: 16.55';
        }
        if ($totalArea == "" && $typeOfObject != "0" && $typeOfObject != "комната") $errors[] = 'Укажите общую площадь';
        if ($totalArea != "") {
            if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $totalArea)) $errors[] = 'Неправильный формат для общей площади, используйте только цифры и точку, например: 86.55';
        }
        if ($livingSpace == "" && $typeOfObject != "0" && $typeOfObject != "комната" && $typeOfObject != "гараж") $errors[] = 'Укажите жилую площадь';
        if ($livingSpace != "") {
            if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $livingSpace)) $errors[] = 'Неправильный формат для жилой площади, используйте только цифры и точку, например: 86.55';
        }
        if ($kitchenSpace == "" && $typeOfObject != "0" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите площадь кухни';
        if ($kitchenSpace != "") {
            if (!preg_match('/^\d{0,5}\.{0,1}\d{0,2}$/', $kitchenSpace)) $errors[] = 'Неправильный формат для площади кухни, используйте только цифры и точку, например: 86.55';
        }
        if ($floor == "" && $typeOfObject != "0" && $typeOfObject != "дом" && $typeOfObject != "таунхаус" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите этаж, на котором расположена квартира, комната';
        if ($floor != "") {
            if (!preg_match('/^\d{0,3}$/', $floor)) $errors[] = 'Неправильный формат для этажа, на котором расположена квартира, комната: должно быть не более 3 цифр';
        }
        if ($totalAmountFloor == "" && $typeOfObject != "0" && $typeOfObject != "дом" && $typeOfObject != "таунхаус" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите количество этажей в доме';
        if ($totalAmountFloor != "") {
            if (!preg_match('/^\d{0,3}$/', $totalAmountFloor)) $errors[] = 'Неправильный формат для количества этажей: должно быть не более 3 цифр';
        }
        if ($totalAmountFloor != "" && $floor != "" && $floor > $totalAmountFloor) $errors[] = 'Общее количество этажей в доме не может быть меньше этажа, на котором расположена Ваше недвижимость';
        if ($numberOfFloor == "" && $typeOfObject != "0" && $typeOfObject != "квартира" && $typeOfObject != "комната" && $typeOfObject != "гараж") $errors[] = 'Укажите количество этажей в доме';
        if ($numberOfFloor != "") {
            if (!preg_match('/^\d{0,2}$/', $numberOfFloor)) $errors[] = 'Неправильный формат для количества этажей: должно быть не более 2 цифр';
        }
        if ($concierge == "0" && $typeOfObject != "0" && $typeOfObject != "дом" && $typeOfObject != "таунхаус" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите: есть ли в доме консьерж';
        if ($intercom == "0" && $typeOfObject != "0" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите наличие домофона';
        if ($parking == "0" && $typeOfObject != "0" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите наличие и тип парковки во дворе';

        if ($city != "Екатеринбург") $errors[] = 'Укажите в качестве города местонахождения Екатеринбург';
        if ($district == "0") $errors[] = 'Укажите район';
        if ($coordX == "" || $coordY == "") $errors[] = 'Укажите улицу и номер дома, затем нажмите кнопку "Проверить адрес"';
        if ($coordX != "" && $coordY != "") {
            if (!preg_match('/^\d{0,3}\.\d{0,10}$/', $coordX) || !preg_match('/^\d{0,3}\.\d{0,10}$/', $coordY)) $errors[] = 'Убедитесь, что на карте метка указывает на Ваш дом';
        }
        if ($address == "") $errors[] = 'Укажите улицу и номер дома';
        if (strlen($address) > 60) $errors[] = 'Указан слишком длинный адрес (используйте не более 60 символов)';
        if ($apartmentNumber == "" && $typeOfObject != "0" && $typeOfObject != "дом" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите номер квартиры';
        if (strlen($apartmentNumber) > 20) $errors[] = 'Указан слишком длинный номер квартиры (используйте не более 20 символов)';

        // Убеждаемся что данный пользователь еще не публиковал объявлений по этому адресу. Не стоит позволять публиковать несколько разных объявлений одному человеку с привязкой к одному и тому же адресу
        if ($typeOfValidation == "newAdvert") {
            $rez = getResultSQLSelect($DBlink, "SELECT * FROM property WHERE (address='" . $address . "' OR (coordX='" . $coordX . "' AND coordY='" . $coordY . "')) AND apartmentNumber='" . $apartmentNumber . "'");
            if (count($rez) != 0) {
                if ($rez[0]['apartmentNumber'] != "") $errors[] = 'Вы уже завели ранее объявление по данному адресу с таким же номером квартиры. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
                if ($rez[0]['apartmentNumber'] == "") $errors[] = 'Вы уже завели ранее объявление по данному адресу. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
            }
        }

        if ($subwayStation == "0" && $typeOfObject != "0" && $typeOfObject != "дача" && $typeOfObject != "гараж") $errors[] = 'Укажите станцию метро рядом';
        if ($distanceToMetroStation == "" && $typeOfObject != "0" && $typeOfObject != "дача" && $typeOfObject != "гараж" && $subwayStation != "0" && $subwayStation != "нет") $errors[] = 'Укажите количество минут ходьбы до ближайшей станции метро';
        if ($distanceToMetroStation != "") {
            if (!preg_match('/^\d{0,3}$/', $distanceToMetroStation)) $errors[] = 'Неправильный формат для количества минут ходьбы до ближайшей станции метро: должно быть не более 3 цифр';
        }
        if ($currency == "0") $errors[] = 'Укажите валюту для рассчетов с арендатором(ами)';
        if ($costOfRenting == "") $errors[] = 'Укажите плату за аренду в месяц';
        if ($costOfRenting != "") {
            if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $costOfRenting)) $errors[] = 'Неправильный формат для платы за аренду, используйте только цифры и точку, например: 25550.50';
        }
        if ($utilities == "0") $errors[] = 'Укажите условия оплаты коммунальных услуг';
        if ($costInSummer == "" && $utilities != "0" && $utilities != "нет") $errors[] = 'Укажите примерную стоимость коммунальных услуг летом';
        if ($costInSummer != "") {
            if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $costInSummer)) $errors[] = 'Неправильный формат для стоимости коммунальных услуг летом, используйте только цифры и точку, например: 2550.50';
        }
        if ($costInWinter == "" && $utilities != "0" && $utilities != "нет") $errors[] = 'Укажите примерную стоимость коммунальных услуг зимой';
        if ($costInWinter != "") {
            if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $costInWinter)) $errors[] = 'Неправильный формат для стоимости коммунальных услуг зимой, используйте только цифры и точку, например: 2550.50';
        }
        if ($electricPower == "0") $errors[] = 'Укажите условия оплаты электроэнергии';
        if ($bail == "0") $errors[] = 'Укажите наличие залога';
        if ($bailCost == "" && $bail != "0" && $bail != "нет") $errors[] = 'Укажите величину залога';
        if ($bailCost != "") {
            if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $bailCost)) $errors[] = 'Неправильный формат для величины залога, используйте только цифры и точку, например: 2550.50';
        }
        if ($prepayment == "0") $errors[] = 'Укажите: есть ли предоплата';
        if ($compensationMoney == "" || $compensationPercent == "") $errors[] = 'Укажите величину единоразовой комиссии собственника. Если Вы не собираетесь брать ее с арендатора, укажите 0';
        if ($compensationMoney != "") {
            if (!preg_match('/^\d{0,7}\.{0,1}\d{0,2}$/', $compensationMoney)) $errors[] = 'Неправильный формат для величины единоразовой комиссии собственника, используйте только цифры и точку, например: 1550.50';
        }
        if ($compensationPercent != "") {
            if (!preg_match('/^\d{0,3}\.{0,1}\d{0,2}$/', $compensationPercent)) $errors[] = 'Неправильный формат для величины единоразовой комиссии собственника, используйте только цифры и точку, например: 15.75'; else {
                if ($compensationPercent > 30) $errors[] = "Слишком большая единовременная комиссия. При работе с нашим сайтом разрешается устанавливать размер единовременной комиссии собственника не более 30% от месячной платы за аренду недвижимости";
            }
        }
        if ($repair == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите текущее состояние ремонта';
        if ($furnish == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите текущее состояние отделки';
        if ($windows == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите материал окон';
        if ($internet == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите наличие интернета';
        if ($telephoneLine == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите наличие телефонной линии';
        if ($cableTV == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите наличие кабельного телевидения';

        if (count($sexOfTenant) == 0 && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите допустимый пол арендатора';
        if (count($relations) == 0 && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите допустимые взаимоотношения между арендаторами';
        if ($children == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите: готовы ли Вы поселить арендаторов с детьми';
        if ($animals == "0" && $typeOfObject != "0" && $typeOfObject != "гараж") $errors[] = 'Укажите: готовы ли Вы поселить арендаторов с животными';
        if ($contactTelephonNumber != "") {
            if (!preg_match('/^[0-9]{10}$/', $contactTelephonNumber)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226540018';
        } else {
            $errors[] = 'Укажите контактный номер телефона для арендаторов по этому объявлению';
        }
        if ($timeForRingBegin == "0" || $timeForRingEnd == "0") $errors[] = 'Укажите время, в которое Вы готовы принимать звонки от арендаторов';
        if ($timeForRingBegin + 0 > $timeForRingEnd + 0 && $timeForRingBegin != "0" && $timeForRingEnd != "0") $errors[] = 'Исправьте: время начала приема звонков не может быть больше, чем время окончания приема звонков';
        if ($checking == "0") $errors[] = 'Укажите: как часто Вы собираетесь проверять сдаваемую недвижимость';
        if ($responsibility == "") $errors[] = 'Укажите: какую ответственность за состояние и ремонт объекта Вы берете на себя, а какую арендатор';

        return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
    }

    // Функция делает первый символ строки в верхнем регистре
    function getFirstCharUpper($str)
    {
        $firstChar = mb_substr($str, 0, 1, 'UTF-8'); // Первая буква
        $lastStr = mb_substr($str, 1); // Все кроме первой буквы
        $firstChar = mb_strtoupper($firstChar, 'UTF-8');
        $lastStr = mb_strtolower($lastStr, 'UTF-8');
        $str = $firstChar . $lastStr;
        return $str;
    }



    /**********************************************************************
     * ПАРАМЕТРЫ ДЛЯ БЛОКА .fotosWrapper
     *
     * *********************************************************************
     * Функция возвращает ассоциативный массив, содержащий ссылки на основную фотку и HTML блок (из input hidden) с данными о неосновных фотках
     * $propertyFotosArr - массив массивов, каждый из которых содержит сведения об 1 фотографии объекта (или пользователя)
     * $propertyId - идентификатор объекта недвижимости
     * $size - размер для фотографии, выдаваемой сразу на странице (в качестве миниатюры к галерее). Возможны значения: small, middle, big
     **********************************************************************/

    function getLinksToFotos($propertyFotosArr = FALSE, $propertyId = 0, $size = 'small') {
        $urlFoto1 = ""; // TODO: Вставить адрес фотки по умолчанию (должна подходить как для бъекта, так и для человека)
        $hrefFoto1 = ""; // TODO: Вставить адрес фотки по умолчанию "нет фото"
        $numberOfFotos = ""; // Текст о количестве фотографий (по шаблону 'еще ___ фото')
        $hiddensLinksToOtherFotos = "";
        // Получаем данные по всем фотографиям для данного объекта недвижимости
        if ($propertyFotosArr == FALSE) {
            $rezPropertyFotos = mysqli_query($link, "SELECT * FROM propertyFotos WHERE propertyId = '" . $propertyId . "'");

            if ($rezPropertyFotos != FALSE) {
                $propertyFotosArr = mysqli_fetch_all($rezPropertyFotos, MYSQLI_ASSOC); // Массив, в который запишем массивы, каждый из которых будет содержать данные по 1 фотке объекта
            } else {
                $propertyFotosArr = array();
                // TODO: сообщить в лог об ошибке обращения к БД!
            }
        }

        // Если удалось получить информацию хотя бы об 1 фотографии
        if (is_array($propertyFotosArr) && count($propertyFotosArr) != 0) {
            // Находим основную фотку и получаем адрес миниатюры и ссылку на большую фотографию для галереи
            foreach ($propertyFotosArr as $value) {
                if ($value['status'] == "основная") {
                    $urlFoto1 = $value['folder'] . '\\' . $size . '\\' . $value['id'] . "." . $value['extension'];
                    $hrefFoto1 = $value['folder'] . '\\big\\' . $value['id'] . "." . $value['extension'];
                    continue;
                } else {
                    // Из неосновных фотографий формируем input hidden блоки для передачи клиентскому JS информации об адресе большой фотографии (для галереи)
                    $hiddensLinksToOtherFotos .= "<input type='hidden' class='gallery' href='" . $value['folder'] . "\\big\\" . $value['id'] . "." . $value['extension'] . "'>";
                }
            }
        }

        // Если фотографий больше чем 1, то нужно внизу сделать приписку об оставшемся кол-ве фоток по шаблону: 'еще ___ фото'
        if (is_array($propertyFotosArr) && count($propertyFotosArr) > 1) {
            $count = count($propertyFotosArr) - 1; // Считаем сколько еще фотографий осталось кроме основной
            $numberOfFotos = "еще $count фото";
        }

        return array('urlFoto1' => $urlFoto1, 'hrefFoto1' => $hrefFoto1, 'numberOfFotos' => $numberOfFotos, 'hiddensLinksToOtherFotos' => $hiddensLinksToOtherFotos);
    }

?>