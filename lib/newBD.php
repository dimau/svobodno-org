<?php
/**
 * Формирует необходимую для работы сайта базу данных со всей структурой и таблицами
 */

include_once 'connect.php'; //подключаемся к БД

// Создаем таблицу для временного хранения информации о загруженных при регистрации фотографиях пользователей
$rez = mysql_query("CREATE TABLE tempregfotos (id VARCHAR(32) NOT NULL PRIMARY KEY, fileUploadId VARCHAR(7) NOT NULL, filename VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, filesizeMb FLOAT(1) NOT NULL)");

echo $rez;

?>