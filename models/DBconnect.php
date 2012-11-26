<?php
/* Статический класс для работы с БД (практически синглтон, содержащий единственный на весь скрипт объект соединения с Базой данных) */

class DBconnect
{
    private static $connect; // Cодержит объект соединения с базой данных класса mysqli (единственный на весь скрипт)

    public static function get()
    {
        if (self::$connect === NULL) { // Если соединение с БД еще не устанавливалось
            self::$connect = self::connectToDB(); // Создаем объект соединения с БД
        }

        return self::$connect; // Возвращаем объект соединения с БД. Либо FALSE, если установить соединение не удалось
    }

    // Метод отрабатывает один раз при вызове DBconnect::get();
    // Метод возвращает объект соединения с БД (mysqli), лиюо FALSE
    private static function connectToDB()
    {
        // Устанавливаем соединение с базой данных
        $mysqli = new mysqli("localhost", "dimau1_dimau", "udvudv", "dimau1_homes");

        // Проверим - удалось ли установить соединение
        if (mysqli_connect_error()) {
            // TODO: сохранить в лог ошибку подключения к БД: ('Ошибка подключения к базе данных (' . mysqli_connect_errno() . ') ' . mysqli_connect_error())
            // TODO: сделать красивую страницу тех поддержки, на которую перенаправлять пользователя, если с БД связи нет
            return FALSE;
        }

        // Устанавливаем кодировку
        if (!$mysqli->set_charset("utf8")) {
            // TODO: сохранить в лог ошибку изменения кодировки БД
        }

        // Если объект соединения с БД получен - вернем его в качестве результата работы конструктора
        return $mysqli;
    }

    // Функция закрывает соединение с БД
    public static function closeConnectToDB()
    {

        // Если соединения не было, то и закрывать нечего
        if (self::$connect === FALSE || self::$connect === NULL) return TRUE;

        if (self::$connect->close()) {

            return TRUE;

        } else {

            // TODO: сохранить в лог ошибку закрытия соединения с БД
            return FALSE;

        }

    }

    // Функция возвращает подробные сведения по объектам недвижимости из БД
    // В случае ошибки возвращает FALSE, елси данные получить не удалось, то пустой массив
    // На входе - отсортированный массив id объектов недвижимости
    // На выходе - отсортированный в том же порядке массив ассоциативных массивов, каждый из которых содержит все параметры одного объекта, в том числе его фотографии
    public static function getFullDataAboutProperties($propertiesId)
    {

        // Проверка входного массива
        if (!isset($propertiesId) || !is_array($propertiesId)) return FALSE;

        // Сколько всего объектов интересует
        $limit = count($propertiesId);
        // Если 0, возвращаем пустой массив
        if ($limit == 0) return array();

        // Собираем строку WHERE для поискового запроса к БД по полным данным для не более чем 20-ти первых объектов
        $strWHERE = " (";
        for ($i = 0; $i < $limit; $i++) {
            $strWHERE .= " id = '" . $propertiesId[$i] . "'";
            if ($i < $limit - 1) $strWHERE .= " OR";
        }
        $strWHERE .= ")";

        // Узнаем анкетные данные о наших объектах
        $res = DBconnect::get()->query("SELECT * FROM property WHERE" . $strWHERE);
        if ((DBconnect::get()->errno)
            OR (($propertyFullArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
        ) {
            // Логируем ошибку
            //TODO: сделать логирование ошибки
            $propertyFullArr = array();
        }

        // Упорядочим полученные результаты из БД в том порядке, в котором во входящем массиве $propertiesId были указаны соответствующие id объектов недвижимости
        $tempArr = array();
        for ($i = 0; $i < $limit; $i++) {
            foreach ($propertyFullArr as $value) {
                if ($propertiesId[$i] == $value['id']) {
                    $tempArr[] = $value;
                    break;
                }
            }
        }
        $propertyFullArr = $tempArr;

        // Получим данные о фотографиях для каждого объекта из $propertyFullArr
        for ($i = 0, $s = count($propertyFullArr); $i < $s; $i++) {
            // Получим данные о фотографиях по id объекта недвижимости
            $propertyFotos = DBconnect::getPropertyFotosDataArr($propertyFullArr[$i]['id']);
            // Записываем полученный массив массивов с данными о фотографиях в специальный новый параметр массива $propertyFullArr
            $propertyFullArr[$i]['propertyFotos'] = $propertyFotos;
        }

        return $propertyFullArr;
    }

    // Функция возвращает массив ассоциированных массивов с данными о фотографиях объекта недвижимости
    // На входе - идентификатор объекта недвижимости, по которому нужно получить фотографии
    public static function getPropertyFotosDataArr($propertyId)
    {

        // Проверка входящих параметров
        if (!isset($propertyId)) return FALSE;

        $res = DBconnect::get()->query("SELECT * FROM propertyFotos WHERE propertyId = '" . $propertyId . "'");
        if ((DBconnect::get()->errno)
            OR (($propertyFotosArr = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
        ) {
            // Логируем ошибку
            //TODO: сделать логирование ошибки
            $propertyFotosArr = array();
        }

        return $propertyFotosArr;
    }

    // Конструктор не используется (но чтобы его нельзя было вызвать снаружи защищен модификатором private), так как он возвращает объект класса DBconnect, а мне в переменной $connect нужен объект класса mysqli
    private function __construct()
    {
    }

}
