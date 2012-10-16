<?php

    // $typeOfValidation = registration - режим проверки при поступлении данных на регистрацию пользователя (включает в себя проверки параметров профиля и поискового запроса как для арендатора, так и для собственника)
    // $typeOfValidation = createSearchRequest - режим проверки при потуплении команды на создание поискового запроса (нет проверки данных поисковой формы, проверка параметров профиля как у арендатора)
    // $typeOfValidation = validateSearchRequest - режим проверки указанных пользователем параметров поиска в совокупности с данными Профиля (причем вне зависимости от того, является ли пользователь арендатором, проверка осуществляется как будто бы является, так как он желает стать арендатором, формируя поисковый запрос)
    // $typeOfValidation = validateProfileParameters - режим проверки отредактированных пользователем данных Профиля (учитывается, является ли пользователь арендатором, или собственником)
    function userDataCorrect($typeOfValidation)
    {
        // Подготовим массив для сохранения сообщений об ошибках
        $errors = array();

        // Получаем переменные, содержащие данные пользователя, для проверки
        global $typeTenant, $typeOwner, $name, $secondName, $surname, $sex, $nationality, $birthday, $login, $oldLogin, $password, $telephon, $email, $fileUploadId, $currentStatusEducation, $almamater, $speciality, $kurs, $ochnoZaochno, $yearOfEnd, $statusWork, $placeOfWork, $workPosition, $regionOfBorn, $cityOfBorn, $vkontakte, $odnoklassniki, $facebook, $twitter, $typeOfObject, $minCost, $maxCost, $pledge, $withWho, $children, $animals, $termOfLease, $lic;

        // Проверки для блока "Личные данные"
        if ($name == "") $errors[] = 'Укажите имя';
        if (strlen($name) > 50) $errors[] = 'Слишком длинное имя. Можно указать не более 50-ти символов';
        if ($secondName == "") $errors[] = 'Укажите отчество';
        if (strlen($secondName) > 50) $errors[] = 'Слишком длинное отчество. Можно указать не более 50-ти символов';
        if ($surname == "") $errors[] = 'Укажите фамилию';
        if (strlen($surname) > 50) $errors[] = 'Слишком длинная фамилия. Можно указать не более 50-ти символов';
        if ($sex == "0") $errors[] = 'Укажите пол';
        if ($nationality == "0") $errors[] = 'Укажите внешность';

        if ($birthday != "") {
            if (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $birthday)) $errors[] = 'Неправильный формат даты рождения, должен быть: дд.мм.гггг'; else {
                if (substr($birthday, 0, 2) < "01" || substr($birthday, 0, 2) > "31") $errors[] = 'Проверьте дату Дня рождения (допустимо от 01 до 31)';
                if (substr($birthday, 3, 2) < "01" || substr($birthday, 3, 2) > "12") $errors[] = 'Проверьте месяц Дня рождения (допустимо от 01 до 12)';
                if (substr($birthday, 6, 4) < "1800" || substr($birthday, 6, 4) > "2100") $errors[] = 'Проверьте год Дня рождения (допустимо от 1800 до 2100)';
            }
        } else {
            $errors[] = 'Укажите дату рождения';
        }

        if ($login == "") $errors[] = 'Укажите логин';
        if (strlen($login) > 50) $errors[] = "Слишком длинный логин. Можно указать не более 50-ти символов";
        if ($login != "" && strlen($login) < 50 && $typeOfValidation == "registration") { // Проверяем логин на занятость
            $rez = mysql_query("SELECT * FROM users WHERE login='" . $login . "'");
            if (mysql_num_rows($rez) != 0) $errors[] = 'Пользователь с таким логином уже существует, укажите другой логин'; // проверка на существование в БД такого же логина
        }
        if ($password == "" && ($typeOfValidation == "registration" || $typeOfValidation == "validateProfileParameters")) $errors[] = 'Укажите пароль'; // Проверить наличие пароля при типе валидации = createSearchRequest не представляется возможным, так как он не хранится в БД

        if ($telephon != "") {
            if (!preg_match('/^[0-9]{10}$/', $telephon)) $errors[] = 'Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019';
        } else {
            $errors[] = 'Укажите контактный (мобильный) телефон';
        }

        if (($typeOfValidation == "registration" && $typeTenant == "true" && $email == "") || ($typeOfValidation == "createSearchRequest" && $email == "") || ($typeOfValidation == "validateSearchRequest" && $email == "") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true" && $email == "")) $errors[] = 'Укажите e-mail';
        if ($email != "" && !preg_match("/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/", $email)) $errors[] = 'Укажите, пожалуйста, Ваш настоящий e-mail (указанный Вами e-mail не прошел проверку формата)'; //соответствует ли поле e-mail регулярному выражению

        // Проверки для блока "Образование"
        if ($currentStatusEducation == "0" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше образование (текущий статус)';
        if ($almamater == "" && ($currentStatusEducation == "сейчас учусь" || $currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите учебное заведение';
        if (isset($almamater) && strlen($almamater) > 100) $errors[] = 'Слишком длинное название учебного заведения (используйте не более 100 символов)';
        if ($speciality == "" && ($currentStatusEducation == "сейчас учусь" || $currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите специальность';
        if (isset($speciality) && strlen($speciality) > 100) $errors[] = 'Слишком длинное название специальности (используйте не более 100 символов)';
        if ($kurs == "" && $currentStatusEducation == "сейчас учусь" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите курс обучения';
        if (isset($kurs) && strlen($kurs) > 30) $errors[] = 'Курс. Указана слишком длинная строка (используйте не более 30 символов)';
        if ($ochnoZaochno == "0" && ($currentStatusEducation == "сейчас учусь" || $currentStatusEducation == "закончил") && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите форму обучения (очная, заочная)';
        if ($yearOfEnd == "" && $currentStatusEducation == "закончил" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите год окончания учебного заведения';
        if ($yearOfEnd != "" && !preg_match("/^[12]{1}[0-9]{3}$/", $yearOfEnd)) $errors[] = 'Укажите год окончания учебного заведения в формате: "гггг". Например: 2007';

        // Проверки для блока "Работа"
        if ($statusWork == "0" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите статус занятости';
        if ($placeOfWork == "" && $statusWork == "работаю" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Ваше место работы (название организации)';
        if (isset($placeOfWork) && strlen($placeOfWork) > 100) $errors[] = 'Слишком длинное наименование места работы (используйте не более 100 символов)';
        if ($workPosition == "" && $statusWork == "работаю" && (($typeOfValidation == "registration" && $typeTenant == "true") || ($typeOfValidation == "validateProfileParameters" && $typeTenant == "true") || $typeOfValidation == "createSearchRequest" || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите Вашу должность';
        if (isset($workPosition) && strlen($workPosition) > 100) $errors[] = 'Слишком длинное название должности (используйте не более 100 символов)';

        // Проверки для блока "Коротко о себе"
        if (isset($regionOfBorn) && strlen($regionOfBorn) > 50) $errors[] = 'Слишком длинное наименование региона, в котором Вы родились (используйте не более 50 символов)';
        if (isset($cityOfBorn) && strlen($cityOfBorn) > 50) $errors[] = 'Слишком длинное наименование города, в котором Вы родились (используйте не более 50 символов)';

        // Проверки для блока "Социальные сети"
        if (strlen($vkontakte) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу Вконтакте (используйте не более 100 символов)';
        if (strlen($vkontakte) > 0 && !preg_match("/vk\.com/", $vkontakte)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу Вконтакте, либо оставьте поле пустым (ссылка должна содержать строчку "vk.com")';
        if (strlen($odnoklassniki) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Одноклассниках (используйте не более 100 символов)';
        if (strlen($odnoklassniki) > 0 && !preg_match("/www\.odnoklassniki\.ru\/profile\//", $odnoklassniki)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Одноклассниках, либо оставьте поле пустым (ссылка должна содержать строчку "www.odnoklassniki.ru/profile/")';
        if (strlen($facebook) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу на Facebook (используйте не более 100 символов)';
        if (strlen($facebook) > 0 && !preg_match("/www\.facebook\.com\/profile\.php/", $facebook)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу на Facebook, либо оставьте поле пустым (ссылка должна содержать строчку с "www.facebook.com/profile.php")';
        if (strlen($twitter) > 100) $errors[] = 'Указана слишком длинная ссылка на личную страницу в Twitter (используйте не более 100 символов)';
        if (strlen($twitter) > 0 && !preg_match("/twitter\.com/", $twitter)) $errors[] = 'Укажите, пожалуйста, Вашу настоящую личную страницу в Twitter, либо оставьте поле пустым (ссылка должна содержать строчку "twitter.com")';

        // Проверки для блока "Параметры поиска"
        if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $minCost)) $errors[] = 'Неправильный формат числа в поле минимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
        if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $maxCost)) $errors[] = 'Неправильный формат числа в поле максимальной величины арендной платы (проверьте: только числа, не более 8 символов)';
        if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && !preg_match("/^\d{0,8}$/", $pledge)) $errors[] = 'Неправильный формат числа в поле максимальной величины залога (проверьте: только числа, не более 8 символов)';
        if ((($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest") && $minCost > $maxCost) $errors[] = 'Минимальная стоимость аренды не может быть больше, чем максимальная. Исправьте поля, в которых указаны Ваши требования к диапазону стоимости аренды';
        if ($withWho == "0" && $typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, как Вы собираетесь проживать в арендуемой недвижимости (с кем)';
        if ($children == "0" && $typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с детьми или без них';
        if ($animals == "0" && $typeOfObject != "гараж" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите, собираетесь ли Вы проживать вместе с животными или без них';
        if ($termOfLease == "0" && (($typeOfValidation == "registration" && $typeTenant == "true") || $typeOfValidation == "validateSearchRequest")) $errors[] = 'Укажите предполагаемый срок аренды';

        // Проверка согласия пользователя с лицензией
        if ($typeOfValidation == "registration" && $lic != "yes") $errors[] = 'Регистрация возможна только при согласии с условиями лицензионного соглашения'; //приняты ли правила

        return $errors; // Возвращаем список ошибок, если все в порядке, то он будет пуст
    }

    // $typeOfValidation = newAdvert - режим первичной (для нового объявления) проверки указанных пользователем параметров объекта недвижимости
    // $typeOfValidation = editAdvert - режим вторичной (при редактировании уже существующего объявления) проверки указанных пользователем параметров объекта недвижимости
    function isAdvertCorrect($typeOfValidation)
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
            $rez = mysql_query("SELECT * FROM tempFotos WHERE fileuploadid='" . $fileUploadId . "'");
            if (mysql_num_rows($rez) == 0) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений';
        }
        if ($typeOfValidation == "editAdvert") // Эта ветка выполняется, если валидации производятся при попытке редактирования параметров объекта недвижимости
        {
            $rez1 = mysql_query("SELECT * FROM propertyFotos WHERE propertyId='" . $propertyId . "'");
            $rez2 = mysql_query("SELECT * FROM tempFotos WHERE fileuploadid='" . $fileUploadId . "'");
            if (mysql_num_rows($rez1) == 0 && mysql_num_rows($rez2) == 0) $errors[] = 'Загрузите несколько фотографий вашего объекта недвижимости, представив каждое из помещений'; // проверка на хотя бы 1 фотку
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
            $rez = mysql_query("SELECT * FROM property WHERE (address='" . $address . "' OR (coordX='" . $coordX . "' AND coordY='" . $coordY . "')) AND apartmentNumber='" . $apartmentNumber . "'");
            if ($rez != FALSE && mysql_num_rows($rez) != 0) {
                $row = mysql_fetch_assoc($rez);
                if ($row['apartmentNumber'] != "") $errors[] = 'Вы уже завели ранее объявление по данному адресу с таким же номером квартиры. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
                if ($row['apartmentNumber'] == "") $errors[] = 'Вы уже завели ранее объявление по данному адресу. Пожалуйста, воспользуйтесь ранее сформированным Вами объявлением в личном кабинете';
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


    /***************************************************************************************************************
     * Функция формирует на сонове входных данных HTML код для выдачи результатов поиска
     **************************************************************************************************************/
    function getSearchResultHTML($propertyArr) {

        // Инициализируем переменную, в которую сложим весь HTML код результатов поиска
        $searchResultHTML = "";

        // Шаблон для всплывающего баллуна с описанием объекта недвижимости на карте Яндекса
        $tmpl_balloonContentBody = "
<div class='headOfBalloon'>{typeOfObject}{address}</div>
<div class='fotosWrapper'>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src='{urlFoto1}'>
    </div>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src='{urlFoto2}'>
    </div>
    <div class='middleFotoWrapper'>
        <img class='middleFoto' src='{urlFoto3}'>
    </div>
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
    <a href='{urlProperty}'>Подробнее</a>
    <div style='float: right; cursor: pointer;'>
        <div class='blockOfIcon'>
            <a><img class='icon' title='Добавить в избранное' src='img/blue_star.png'></a>
        </div>
        <a id='addToFavorit'> добавить в избранное</a>
    </div>
</div>
";

        // Шаблон для блока с кратким описанием объекта недвижимости в таблице
        $tmpl_shortAdvert = "
<tr class='realtyObject' coordX='{coordX}' coordY='{coordY}' balloonContentBody=\"{balloonContentBody}\">
    <td>
	    <div class='numberOfRealtyObject'>{number}</div>
	    <div class='blockOfIcon'>
		    <a><img class='icon' title='Добавить в избранное' src='img/blue_star.png'></a>
	    </div>
	</td>
	<td>
	    <div class='fotosWrapper resultSearchFoto'>
		    <div class='middleFotoWrapper'>
			    <img class='middleFoto' src='{urlFoto1}'>
			</div>
		</div>
    </td>
	<td>{address}
	    <div class='linkToDescriptionBlock'>
		<a class='linkToDescription' href='{urlProperty}'>Подробнее</a>
		</div>
	</td>
	<td>{costOfRenting} {currency} в месяц</td>
</tr>
";

        // Шаблон для блока (строки) с подробным описанием объекта недвижимости в таблице
        $tmpl_extendedAdvert = "
<tr class='realtyObject' linkToDescription='{urlProperty}'>
            <td>
                <div class='numberOfRealtyObject'>{number}</div>
                <div class='blockOfIcon'>
                    <a><img class='icon' title='Добавить в избранное' src='img/blue_star.png'></a>
                </div>
            </td>
            <td>
                <div class='fotosWrapper resultSearchFoto'>
                    <div class='middleFotoWrapper'>
                        <img class='middleFoto' src='{urlFoto1}'>
                    </div>
                    <div class='middleFotoWrapper'>
                        <img class='middleFoto' src='{urlFoto2}'>
                    </div>
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

        // Инициализируем переменные, в которые сложим HTML блоки каждого из объявлений.
        $matterOfShortList = ""; // Содержимое таблицы объявлений с краткими данными по каждому из них
        $matterOfFullParametersList = ""; // Содержимое таблицы объявлений с подробными данными по каждому из них

        // Инициализируем счетчик общего количества опубликованных объявлений в базе. Пригодится только, если по условиям поиска не найдено ни одно объявление
        $allAmountAdverts = "";

        // Инициализируем счетчик объявлений
        $number = 0;

        // Начинаем перебор каждого из полученных ранее объявлений для наполнения их данными шаблонов и получения красивых HTML-блоков для публикации на странице
        foreach ($propertyArr as $oneProperty) {

            // Увеличиваем счетчик объявлений при каждом проходе
            $number++;

            /* Готовим баллун */

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне баллуна
            $arrBalloonReplace = array();

            // Наполняем массив $arrBalloonReplace данными, которые заменят болванки в шаблоне
            // Тип
            $arrBalloonReplace['typeOfObject'] = "";
            if (isset($oneProperty['typeOfObject'])) $arrBalloonReplace['typeOfObject'] = getFirstCharUpper($oneProperty['typeOfObject']) . ": ";

            // Адрес
            $arrBalloonReplace['address'] = "";
            if (isset($oneProperty['address'])) $arrBalloonReplace['address'] = $oneProperty['address'];

            // Фото
            $arrBalloonReplace['urlFoto1'] = "";
            $arrBalloonReplace['urlFoto2'] = "";
            $arrBalloonReplace['urlFoto3'] = "";
            // Получаем данные по всем фотографиям для данного объекта недвижимости
            $rowPropertyFotosArr = array(); // Массив, в который запишем массивы, каждый из которых будет содержать данные по 1 фотке объекта
            $rezPropertyFotos = mysql_query("SELECT id, extension FROM propertyFotos WHERE propertyId = '" . $oneProperty['id'] . "'");
            if ($rezPropertyFotos != FALSE) {
                for ($i = 0; $i < mysql_num_rows($rezPropertyFotos); $i++) {
                    $rowPropertyFotosArr[] = mysql_fetch_assoc($rezPropertyFotos);
                }
            }
            if (isset($rowPropertyFotosArr[0])) $arrBalloonReplace['urlFoto1'] = "uploaded_files/" . $rowPropertyFotosArr[0]['id'] . "." . $rowPropertyFotosArr[0]['extension'];
            if (isset($rowPropertyFotosArr[1])) $arrBalloonReplace['urlFoto2'] = "uploaded_files/" . $rowPropertyFotosArr[1]['id'] . "." . $rowPropertyFotosArr[1]['extension'];
            if (isset($rowPropertyFotosArr[2])) $arrBalloonReplace['urlFoto3'] = "uploaded_files/" . $rowPropertyFotosArr[2]['id'] . "." . $rowPropertyFotosArr[2]['extension'];

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

            // Ссылка "Подробно"
            $arrBalloonReplace['urlProperty'] = "";
            if (isset($oneProperty['id'])) $arrBalloonReplace['urlProperty'] = "objdescription.php?propertyId=" . $oneProperty['id'];

            // Производим заполнение шаблона баллуна
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrBalloonTemplVar = array('{typeOfObject}', '{address}', '{urlFoto1}', '{urlFoto2}', '{urlFoto3}', '{costOfRenting}', '{currency}', '{utilities}', '{compensationMoney}', '{compensationPercent}', '{amountOfRoomsName}', '{amountOfRooms}', '{adjacentRooms}', '{areaNames}', '{areaValues}', '{floorName}', '{floor}', '{furnitureName}', '{furniture}', '{urlProperty}');
            // Копируем html-текст шаблона баллуна
            $currentAdvertBalloon = str_replace($arrBalloonTemplVar, $arrBalloonReplace, $tmpl_balloonContentBody);

            /* Готовим блок shortList таблицы для данного объекта недвижимости */

            // Инициализируем массив, в который будут сохранены значения, используемые для замены в шаблоне shortList строки таблицы
            $arrShortListReplace = array();

            $arrShortListReplace['coordX'] = "";
            if (isset($oneProperty['coordX'])) $arrShortListReplace['coordX'] = $oneProperty['coordX'];

            $arrShortListReplace['coordY'] = "";
            if (isset($oneProperty['coordY'])) $arrShortListReplace['coordY'] = $oneProperty['coordY'];

            $arrShortListReplace['balloonContentBody'] = $currentAdvertBalloon;

            $arrShortListReplace['number'] = $number;

            $arrShortListReplace['urlFoto1'] = "";
            if (isset($rowPropertyFotosArr[0]['id']) && isset($rowPropertyFotosArr[0]['extension'])) $arrShortListReplace['urlFoto1'] = "uploaded_files/" . $rowPropertyFotosArr[0]['id'] . "." . $rowPropertyFotosArr[0]['extension'];

            $arrShortListReplace['address'] = "";
            if (isset($oneProperty['address'])) $arrShortListReplace['address'] = $oneProperty['address'];

            $arrShortListReplace['urlProperty'] = "";
            if (isset($oneProperty['id'])) $arrShortListReplace['urlProperty'] = "objdescription.php?propertyId=" . $oneProperty['id'];

            $arrShortListReplace['costOfRenting'] = "";
            if (isset($oneProperty['costOfRenting'])) $arrShortListReplace['costOfRenting'] = $oneProperty['costOfRenting'];

            $arrShortListReplace['currency'] = "";
            if (isset($oneProperty['currency'])) $arrShortListReplace['currency'] = $oneProperty['currency'];

            // Производим заполнение шаблона строки (блока) shortList таблицы по данному объекту недвижимости
            // Инициализируем массив с строками, которые будут использоваться для подстановки в шаблоне баллуна
            $arrShortListTemplVar = array('{coordX}', '{coordY}', '{balloonContentBody}', '{number}', '{urlFoto1}', '{address}', '{urlProperty}', '{costOfRenting}', '{currency}');
            // Копируем html-текст шаблона блока (строки таблицы)
            $currentAdvertShortList = str_replace($arrShortListTemplVar, $arrShortListReplace, $tmpl_shortAdvert);
            // Полученный HTML текст складываем в "копилочку"
            $matterOfShortList .= $currentAdvertShortList;

            /* Готовим блок fullParametersList таблицы для данного объекта недвижимости */

            // Инициализируем массив, в который будут сохранены значения, используемые для замены констант в шаблоне
            $arrExtendedListReplace = array();

            // Ссылка "Подробно"
            $arrExtendedListReplace['urlProperty'] = "";
            if (isset($oneProperty['id'])) $arrExtendedListReplace['urlProperty'] = "objdescription.php?propertyId=" . $oneProperty['id'];

            // Номер объявления
            $arrExtendedListReplace['number'] = $number;

            // Фото
            $arrExtendedListReplace['urlFoto1'] = "";
            $arrExtendedListReplace['urlFoto2'] = ""; //TODO: решить, нужно ли в широкой таблице показывать 2 фотки, если да - реализовать
            if (isset($rowPropertyFotosArr[0]['id']) && isset($rowPropertyFotosArr[0]['extension'])) $arrExtendedListReplace['urlFoto1'] = "uploaded_files/" . $rowPropertyFotosArr[0]['id'] . "." . $rowPropertyFotosArr[0]['extension'];

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
            $arrExtendedListTemplVar = array('{urlProperty}', '{number}', '{urlFoto1}', '{urlFoto2}', '{typeOfObject}', '{district}', '{address}', '{amountOfRooms}', '{adjacentRooms}', '{areaValues}', '{floor}', '{furniture}', '{costOfRenting}', '{utilities}', '{compensationMoney}', '{compensationPercent}');
            // Копируем html-текст шаблона блока (строки таблицы)
            $currentAdvertExtendedList = str_replace($arrExtendedListTemplVar, $arrExtendedListReplace, $tmpl_extendedAdvert);
            // Полученный HTML текст складываем в "копилочку"
            $matterOfFullParametersList .= $currentAdvertExtendedList;
        }

        // Складываем элементы управления для выбора формы представления результатов выдачи (карта, список, карта + список)
        $searchResultHTML .= "
        <div class='choiceViewSearchResult'>
            <span id='expandList'><a href='#'>Список</a>&nbsp;&nbsp;&nbsp;</span><span id='listPlusMap'><a href='#'>Список +
            карта</a>&nbsp;&nbsp;&nbsp;</span><span id='expandMap'><a href='#'>Карта</a></span>
        </div>
        <div id='resultOnSearchPage' style='height: 100%;'>

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
            $searchResultHTML .= "
                    <tr><td><div style='margin-top: 2em; margin-left: 1em;'>
                        К сожалению, поиск не дал результатов.<br>
                        Попробуйте изменить условия поиска.<br><br>
                        Еще <a href='search.php?fastSearchButton='>" . $allAmountAdverts . " объявлений</a> ждут своих арендаторов</div></td></tr>
                    ";
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
            $searchResultHTML .= "
            <tr><td><div style='margin-top: 2em; margin-left: 1em;'>
                К сожалению, поиск не дал результатов.<br>
                Попробуйте изменить условия поиска.<br><br>
                Еще <a href='search.php?fastSearchButton='>" . $allAmountAdverts . " объявлений</a> ждут своих арендаторов
            </div></td></tr>
        ";
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

    //Функция для генерации случайной строки
    function generateCode($length = 6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
        $code = "";

        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }

        return $code;
    }

    function newSession($userId)
    {
        $hash = md5(generateCode(10)); // генерируем случайное 32-х значное число - идентификатор сессии
        mysql_query("UPDATE users SET user_hash='" . $hash . "' WHERE id='" . $userId . "'");
        $_SESSION['id'] = $hash; //записываем id сессии
    }


    function lastAct($id)
    {
        $tm = time();
        mysql_query("UPDATE users SET online='$tm', last_act='$tm' WHERE id='$id'");
    }

    function dateFromDBToView($birthdayFromDB)
    {
        $date = substr($birthdayFromDB, 8, 2);
        $month = substr($birthdayFromDB, 5, 2);
        $year = substr($birthdayFromDB, 0, 4);
        return $date . "." . $month . "." . $year;
    }

    function dateFromViewToDB($birthdayFromView)
    {
        $date = substr($birthdayFromView, 0, 2);
        $month = substr($birthdayFromView, 3, 2);
        $year = substr($birthdayFromView, 6, 4);
        return $year . "." . $month . "." . $date;
    }

    // Функция для авторизации (входа) пользователя на сайте
    function enter()
    {
        $error = array(); //массив для ошибок
        if ($_POST['login'] != "" && $_POST['password'] != "") //если поля заполнены
        {
            $login = $_POST['login'];
            $password = $_POST['password'];

            $rez = mysql_query("SELECT * FROM users WHERE login='" . $login . "'"); //запрашиваем строку из БД с логином, введённым пользователем
            if ($rez != FALSE && mysql_num_rows($rez) == 1) //если нашлась одна строка, значит такой юзер существует в БД
            {
                $row = mysql_fetch_assoc($rez);
                if ($password == $row['password']) // Cравниваем указанный пользователем пароль с паролем из БД
                {
                    //пишем логин и хэшированный пароль в cookie, также создаём переменную сессии
                    setcookie("login", $row['login'], time() + 60 * 60 * 24 * 7);
                    setcookie("password", md5($row['login'] . $row['password']), time() + 60 * 60 * 24 * 7);
                    newSession($row['id']);

                    lastAct($row['id']);
                    return $error;
                } else //если пароли не совпали
                {
                    $error[] = "Неверный пароль";
                    return $error;
                }
            } else //если такого пользователя не найдено в БД
            {
                $error[] = "Неверный логин и пароль";
                return $error;
            }
        } else {
            $error[] = "Укажите Ваш логин и пароль";
            return $error;
        }
    }


    function login()
    {
        // Запускаем сессию для работы с ней и готовим переменную rez
        if (!isset($_SESSION)) {
            session_start();
        }
        $rez = FALSE;

        if (isset($_SESSION['id'])) //если какая-то сесcия есть - проверим ее актуальность
        {
            $rez = mysql_query("SELECT * FROM users WHERE user_hash='" . $_SESSION['id'] . "'");
        }

        if ($rez != FALSE && mysql_num_rows($rez) == 1) // Если текущая сессия актуальна - добавим куки, чтобы после перезапуска браузера сессия не слетала
        {
            $row = mysql_fetch_assoc($rez);

            // выдается ошибка при попытке обновить куки из header.php, так как уже начал отправляться текст странички - html
            /* setcookie("login", "", time() - 1, '/');
    setcookie("password", "", time() - 1, '/');
    setcookie("login", $row['login'], time() + 60*60*24*7, '/');
    setcookie("password", md5($row['login'] . $row['password']), time() + 60*60*24*7, '/'); */

            return $row['id']; // возвращаем id пользователя
        } else // Если сессия уже потеряла актуальность или не существовала
        {
            if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) // смотрим куки, если cookie есть, то проверим их актуальность
            {
                $rez = mysql_query("SELECT * FROM users WHERE login='{$_COOKIE['login']}'"); //запрашиваем строку с искомым логином

                // чтобы избежать ошибок при вычислении row -  делаем это с проверкой переменной rez
                if ($rez != FALSE) {
                    $row = mysql_fetch_assoc($rez);
                }

                if ($rez != FALSE && mysql_num_rows($rez) == 1 && isset($row['login']) && isset($row['password']) && md5($row['login'] . $row['password']) == $_COOKIE['password']) //если логин и пароль нашлись в БД
                {
                    newSession($row['id']);

                    lastAct($row['id']);
                    return $row['id'];
                } else //если данные из cookie не подошли, то удаляем эти куки, ибо нахуй они такие нам не нужны
                {
                    setcookie("login", "", time() - 360000, '/');
                    setcookie("password", "", time() - 360000, '/');
                    return FALSE;
                }
            } else // Если сессия не актуальна и куки не существуют
            {
                return FALSE;
            }
        }
    }

?>