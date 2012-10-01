<?php
/**
 * Формируем тестовые записи в БД о пользователях и объявлениях
 * Важно не забывать копировать соответствующие тестовые картинки
 */
include_once 'connect.php'; //подключаемся к БД

// Создаем пользователей
$rez = mysql_query("INSERT INTO users (id, typeTenant, typeOwner, name, secondName, surname, sex, nationality, birthday, login, password, telephon, emailReg, email, currentStatusEducation, almamater, speciality, kurs, ochnoZaochno, yearOfEnd, notWorkCheckbox, placeOfWork, workPosition, regionOfBorn, cityOfBorn, shortlyAboutMe, vkontakte, odnoklassniki, facebook, twitter, lic, user_hash, last_act, reg_date) VALUES
(1, 'false', 'true', 'Ольга', 'Леонидовна', 'Андреева', 'женский', 'немка', '1992-08-16', 'owner', 'owner', '9831541618', '', '', '0', '', '', '', '0', '', '', '', '', '', '', '', '', '', '', '', 'yes', '84fae0ea32a834378531e9b0e7c63e11', 1348563523, 1348563523),
(2, 'true', 'false', 'Максим', 'Леонидович', 'Андреев', 'мужской', 'русский', '1987-01-27', 'tenant', 'tenant', '9221431615', 'dimau777@gmail.com', 'maxim@ya.ru', 'закончил', 'УГТУ-УПИ', 'Менеджмент в спорте', '', 'очно', '2011', '', 'Банк ПромИмпериал', 'менеджер проектов', 'Свердловская область', 'Екатеринбург', 'Немного люблю спорт, бегаю по утрам.', 'http://vk.com/maxim', '', '', '', 'yes', '9c9cdf2a467b90d5d5f8135af3543882', 1348590806, 1348590806)
");

echo "Статус регистрации пользователей: " . $rez . "\n";

// Заполняем таблицу о поисковых запросах пользователей
$rez = mysql_query("INSERT INTO searchrequests (userId, typeOfObject, amountOfRooms, adjacentRooms, floor, minCost, maxCost, pledge, prepayment, district, withWho, linksToFriends, children, howManyChildren, animals, howManyAnimals, termOfLease, additionalDescriptionOfSearch) VALUES
(2, 'квартира', 0x613a323a7b693a303b733a313a2231223b693a313b733a313a2232223b7d, 'не имеет значения', 'не первый и не последний', 11000, 20000, 15000, 'нет', 0x613a333a7b693a303b733a32343a22d091d0bed182d0b0d0bdd0b8d187d0b5d181d0bad0b8d0b9223b693a313b733a363a22d092d098d097223b693a323b733a31373a22d0aed0b3d0be2dd0b7d0b0d0bfd0b0d0b4223b7d, 'пара', 'Анна Дрей', 'без детей', '', 'без животных', '', 'длительный срок', 'Рядом с парком, чтобы бегать утром можно было на свежем воздухе.')
");

echo "Статус регистрации поисковых запросов пользователей: " . $rez . "\n";

// Регистрируем данные о фотках пользователей
$rez = mysql_query("INSERT INTO userfotos (id, filename, extension, filesizeMb, userId) VALUES
('1aa240c376365dd8c4a38493a8dcff64', 'DSCN0319.JPG', 'JPG', 6.7, 2),
('8e26607a95ae215bd4831d2b9b0be1b0', 'Ольга Андреева.jpg', 'jpg', 0.1, 1)
");

echo "Статус регистрации фотографий пользователей: " . $rez . "\n";

// Создаем несколько объектов недвижимости
$rez = mysql_query("INSERT INTO property (id, userId, typeOfObject, dateOfEntry, termOfLease, dateOfCheckOut, amountOfRooms, adjacentRooms, amountOfAdjacentRooms, typeOfBathrooms, typeOfBalcony, balconyGlazed, roomSpace, totalArea, livingSpace, kitchenSpace, floor, totalAmountFloor, numberOfFloor, concierge, intercom, parking, city, district, coordX, coordY, address, apartmentNumber, subwayStation, distanceToMetroStation, currency, costOfRenting, realCostOfRenting, utilities, costInSummer, costInWinter, electricPower, bail, bailCost, prepayment, compensationMoney, compensationPercent, repair, furnish, windows, internet, telephoneLine, cableTV, furnitureInLivingArea, furnitureInLivingAreaExtra, furnitureInKitchen, furnitureInKitchenExtra, appliances, appliancesExtra, sexOfTenant, relations, children, animals, contactTelephonNumber, timeForRingBegin, timeForRingEnd, checking, responsibility, comment, last_act, reg_date, status) VALUES
(1, 1, 'квартира', '2012-09-25', 'длительный срок', '0000-00-00', '1', 'да', '4', 'раздельный', 'нет', 'да', 0, 68.5, 56, 12, 3, 4, 0, 'есть', 'нет', 'охраняемая', 'Екатеринбург', 'Юго-запад', '56.825483', '60.57357', 'улица Гурзуфская, 38', '64', 'нет', 0, 'руб.', 23000, 23000, 'да', 1590, 6450, 'нет', 'есть', 4600, '1 месяц', 2760, 12, 'меньше 1 года назад', 'евростандарт', 'деревянные', 'проведен', 'не проведен', 'проведено', 0x613a343a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a33373a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b2d183d185d0bcd0b5d181d182d0bdd0b0d18f223b693a323b733a33333a22d0bad180d0b5d181d0bbd0be20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b5223b693a333b733a31303a22d0bad0bed0bcd0bed0b4223b7d, 'пуффик', 0x613a303a7b7d, 'чморик-конторик, стеллаж под инструменты,дуван', 0x613a303a7b7d, 'пипикалка, мумукалка', 'мужчина', 'один человек_пара', 'не имеет значения', 'только без животных', '9221431645', '8:00', '16:00', '1 раз в месяц (при получении оплаты)', 'Никакой ответственности', '', 1348684721, 1348684721, 'опубликовано'),
(2, 1, 'квартира', '2012-09-26', 'несколько месяцев', '2012-12-31', '5', 'да', '3', '2 шт.', 'балкон и лоджия', 'нет', 0, 125.8, 67.89, 15, 4, 40, 0, 'нет', 'есть', 'подземная', 'Екатеринбург', 'Академический', '56.791911', '60.521028', 'улица Вильгельма Де Геннина, 31', '23', 'Площадь 1905 г.', 234, 'руб.', 36500, 36500, 'да', 2300, 3600, 'да', 'есть', 56000, '2 месяца', 10950, 30, 'не выполнялся (новый дом)', 'свежая (новые обои, побелка потолков)', 'пластиковые', 'проведен', 'не проведен', 'не проведено', 0x613a333a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a32393a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b5d182d181d0bad0b0d18f223b693a323b733a31303a22d0bad0bed0bcd0bed0b4223b7d, 'Стол компьютерный, пистолет', 0x613a323a7b693a303b733a32373a22d181d182d0bed0bb20d0bed0b1d0b5d0b4d0b5d0bdd0bdd18bd0b9223b693a313b733a33323a22d181d182d183d0bbd18cd18f2c20d182d0b0d0b1d183d180d0b5d182d0bad0b8223b7d, 'Портмоне', 0x613a323a7b693a303b733a32323a22d185d0bed0bbd0bed0b4d0b8d0bbd18cd0bdd0b8d0ba223b693a313b733a31383a22d182d0b5d0bbd0b5d0b2d0b8d0b7d0bed180223b7d, '', 'мужчина_женщина', 'семья_пара', 'только без детей', 'только без животных', '9221431645', '9:00', '13:00', '1 раз в месяц (при получении оплаты)', 'Все решаю сам', '', 1348683429, 1348683429, 'опубликовано'),
(3, 1, 'квартира', '2012-09-25', 'несколько месяцев', '2012-12-31', '1', 'да', '4', '3 шт.', 'лоджия', 'да', 0, 68.5, 56, 12, 1, 3, 0, 'нет', 'нет', 'неохраняемая', 'Екатеринбург', 'Шарташ', '56.819927', '60.539264', 'улица Репина, 105', '124', 'Машиностроителей', 12, 'руб.', 18000, 18000, 'да', 1500, 2500, 'да', 'есть', 5000, '2 месяца', 0, 0, 'меньше 1 года назад', 'евростандарт', 'деревянные', 'проведен', 'не проведен', 'проведено', 0x613a343a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a33373a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b2d183d185d0bcd0b5d181d182d0bdd0b0d18f223b693a323b733a33333a22d0bad180d0b5d181d0bbd0be20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b5223b693a333b733a31303a22d0bad0bed0bcd0bed0b4223b7d, 'пуффик', 0x613a303a7b7d, 'чморик-конторик, стеллаж под инструменты,дуван', 0x613a303a7b7d, 'пипикалка, мумукалка', 'женщина', 'семья_пара', 'не имеет значения', 'только без животных', '9221431645', '8:00', '16:00', '1 раз в месяц (при получении оплаты)', 'Никакой ответственности', '', 1348564004, 1348564004, 'опубликовано'),
(4, 1, 'дом', '2012-09-26', 'несколько месяцев', '2012-12-31', '2', 'да', '0', 'совмещенный', '2 эркера и более', '0', 0, 125.8, 67.89, 15, 0, 0, 2, '0', 'нет', 'неохраняемая', 'Екатеринбург', 'ВИЗ', '56.824951', '60.549792', 'улица Репина, 75', '', 'нет', 0, 'дол. США', 2000, 60000, 'да', 120, 230, 'нет', 'нет', 5600, '6 месяцев', 200, 10, 'выполнялся давно', 'требует обновления', 'деревянные', 'не проведен', 'проведен', 'проведено', 0x613a333a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a32393a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b5d182d181d0bad0b0d18f223b693a323b733a31303a22d0bad0bed0bcd0bed0b4223b7d, '', 0x613a323a7b693a303b733a32373a22d181d182d0bed0bb20d0bed0b1d0b5d0b4d0b5d0bdd0bdd18bd0b9223b693a313b733a33323a22d181d182d183d0bbd18cd18f2c20d182d0b0d0b1d183d180d0b5d182d0bad0b8223b7d, '', 0x613a323a7b693a303b733a32323a22d185d0bed0bbd0bed0b4d0b8d0bbd18cd0bdd0b8d0ba223b693a313b733a31383a22d182d0b5d0bbd0b5d0b2d0b8d0b7d0bed180223b7d, '', 'мужчина_женщина', 'один человек', 'не имеет значения', 'не имеет значения', '9221431645', '6:00', '23:00', 'Никогда (проживает в другом городе)', 'Все решаю сам', '', 1348683411, 1348683411, 'опубликовано'),
(5, 1, 'гараж', '2012-09-25', 'несколько месяцев', '2012-12-12', '0', '0', '0', '0', '0', '0', 0, 68.5, 0, 0, 0, 0, 0, '0', '0', '0', 'Екатеринбург', 'Автовокзал (южный)', '56.817089', '60.577199', 'улица Ясная, 16', '', '0', 0, 'руб.', 4600, 4600, 'нет', 1500, 2500, 'да', 'нет', 5000, '1 месяц', 1104, 24, '0', '0', '0', '0', '0', '0', 0x613a343a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a33373a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b2d183d185d0bcd0b5d181d182d0bdd0b0d18f223b693a323b733a33333a22d0bad180d0b5d181d0bbd0be20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b5223b693a333b733a31303a22d0bad0bed0bcd0bed0b4223b7d, '', 0x613a303a7b7d, '', 0x613a303a7b7d, '', '', '', '0', '0', '9221431645', '8:00', '16:00', '1 раз в месяц (при получении оплаты)', 'Никакой ответственности', '', 1348683874, 1348683874, 'опубликовано'),
(6, 1, 'дача', '2012-09-26', 'длительный срок', '0000-00-00', '2', 'нет', '0', 'раздельный', 'эркер', '0', 0, 23.57, 12.8, 0, 0, 0, 4, '0', '0', '0', 'Екатеринбург', 'Уралмаш', '56.848409', '60.601786', 'улица Мельковская, 3б', '', '0', 0, 'руб.', 12000, 12000, 'нет', 2300, 3600, 'да', 'нет', 56000, 'нет', 1200, 10, 'сделан только что', 'евростандарт', 'иное', 'не проведен', 'не проведен', 'не проведено', 0x613a333a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a32393a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b5d182d181d0bad0b0d18f223b693a323b733a31303a22d0bad0bed0bcd0bed0b4223b7d, 'cтол компьютерный, пистолет', 0x613a323a7b693a303b733a32373a22d181d182d0bed0bb20d0bed0b1d0b5d0b4d0b5d0bdd0bdd18bd0b9223b693a313b733a33323a22d181d182d183d0bbd18cd18f2c20d182d0b0d0b1d183d180d0b5d182d0bad0b8223b7d, 'портмоне', 0x613a323a7b693a303b733a32323a22d185d0bed0bbd0bed0b4d0b8d0bbd18cd0bdd0b8d0ba223b693a313b733a31383a22d182d0b5d0bbd0b5d0b2d0b8d0b7d0bed180223b7d, '', 'мужчина_женщина', 'один человек_семья_пара', 'с детьми старше 4-х лет', 'не имеет значения', '9221431645', '9:00', '13:00', 'Постоянно (проживает на этой же площади)', 'Все решаю сам', '', 1348679739, 1348679739, 'опубликовано'),
(7, 1, 'таунхаус', '2012-09-25', 'длительный срок', '0000-00-00', '6', 'да', '3', 'совмещенный', '2 лоджии и более', 'нет', 0, 68.5, 56, 12, 0, 0, 2, '0', 'нет', 'отсутствует', 'Екатеринбург', 'Юго-запад', '56.849817', '60.620489', 'улица Шевченко, 29', '2', 'Ботаническая', 23, 'руб.', 18000, 18000, 'да', 1500, 2500, 'да', 'есть', 5000, '2 месяца', 0, 0, 'больше года назад', 'евростандарт', 'деревянные', 'проведен', 'не проведен', 'проведено', 0x613a343a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a33373a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b2d183d185d0bcd0b5d181d182d0bdd0b0d18f223b693a323b733a33333a22d0bad180d0b5d181d0bbd0be20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b5223b693a333b733a31303a22d0bad0bed0bcd0bed0b4223b7d, 'пуффик', 0x613a303a7b7d, '', 0x613a313a7b693a303b733a34313a22d0bed185d180d0b0d0bdd0bdd0b0d18f20d181d0b8d0b3d0bdd0b0d0bbd0b8d0b7d0b0d186d0b8d18f223b7d, '', 'женщина', 'семья_пара', 'не имеет значения', 'не имеет значения', '9221431645', '8:00', '16:00', 'Никогда (проживает в другом городе)', 'Никакой ответственности', '', 1348683998, 1348683998, 'опубликовано'),
(8, 1, 'комната', '2012-09-26', 'длительный срок', '0000-00-00', '2', 'да', '0', 'раздельный', 'нет', '0', 12.3, 0, 0, 15, 40, 40, 0, 'есть', 'есть', 'отсутствует', 'Екатеринбург', 'Эльмаш', '56.849905', '60.621433', 'улица Шевченко, 31', '25', 'Уралмаш', 10, 'евро', 120, 4800, 'нет', 0, 0, 'нет', 'есть', 250, '3 месяца', 30, 25, 'сделан только что', 'бабушкин вариант', 'деревянные', 'проведен', 'не проведен', 'не проведено', 0x613a333a7b693a303b733a33313a22d0b4d0b8d0b2d0b0d0bd20d180d0b0d181d0bad0bbd0b0d0b4d0bdd0bed0b9223b693a313b733a32393a22d0bad180d0bed0b2d0b0d182d18c20d0b4d0b5d182d181d0bad0b0d18f223b693a323b733a31303a22d0bad0bed0bcd0bed0b4223b7d, '', 0x613a323a7b693a303b733a32373a22d181d182d0bed0bb20d0bed0b1d0b5d0b4d0b5d0bdd0bdd18bd0b9223b693a313b733a33323a22d181d182d183d0bbd18cd18f2c20d182d0b0d0b1d183d180d0b5d182d0bad0b8223b7d, '', 0x613a323a7b693a303b733a32323a22d185d0bed0bbd0bed0b4d0b8d0bbd18cd0bdd0b8d0ba223b693a313b733a31383a22d182d0b5d0bbd0b5d0b2d0b8d0b7d0bed180223b7d, '', 'мужчина_женщина', 'один человек_семья_пара', 'с детьми старше 4-х лет', 'не имеет значения', '9221431645', '10:00', '20:00', 'Периодически (чаще 1 раза в месяц)', 'никакой ответственности', '', 1348686239, 1348686239, 'опубликовано')
");

echo "Статус регистрации объектов недвижимости: " . $rez . "\n";

// Создаем таблицу для постоянного хранения информации о ФОТОГРАФИЯХ объектов недвижимости
$rez = mysql_query("INSERT INTO propertyfotos (id, filename, extension, filesizeMb, propertyId) VALUES
('03b141bbb26e42040b96fb9176ae28c4', 'Квартира4.jpg', 'jpg', 0.1, 1),
('08c1da5a4849d70e2e109219382c9dfc', '291.JPG', 'JPG', 0.1, 1),
('11d92a3d33d510ecb02f9ad89ba842df', 'Квартира2.jpg', 'jpg', 0, 4),
('155d3a797c42f233afc305954ce9bc3b', 'Квартира2.jpg', 'jpg', 0, 2),
('1e01eb19077606729cd208ef8f1813c4', 'Квартира5.jpg', 'jpg', 0, 5),
('4cbb15f69dee3c789e5cc421da294d39', 'Квартира1.jpg', 'jpg', 0, 2),
('6ec697030d123ac8903e7b579f827fd1', 'Квартира2.jpg', 'jpg', 0, 7),
('a2ae107cba37fa06d99c31e67e028421', 'Квартира4.jpg', 'jpg', 0.1, 6),
('b3185f04cd6cbd1b832e16be6162b234', 'Квартира5.jpg', 'jpg', 0, 4),
('b6ac5310bb479949a37940ef97d2d50d', 'Квартира3.jpg', 'jpg', 0.1, 3),
('c130dad1e123737c7325f797cbeda477', 'Квартира4.jpg', 'jpg', 0.1, 7),
('c3f0e2ec40c21d7ea821e20995389137', 'Квартира3.jpg', 'jpg', 0.1, 2),
('d0541fa5096a2b58cc6dc99118409ec3', '291.JPG', 'JPG', 0.1, 8),
('e3a4fba78730181de8a9037e13a5b848', 'Квартира5.jpg', 'jpg', 0, 6),
('fb7caff3b35748fbf629fca0f6f679b7', 'Квартира1.jpg', 'jpg', 0, 7),
('fbd5e8e558fb7e78c4d689f34ca82b55', 'Квартира5.jpg', 'jpg', 0, 7)
");

echo "Статус регистрации фотографий недвижимости: " . $rez . " \n ";

?>