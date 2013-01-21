<?php
/*
 *
 */

$user = "dimau777@gmail.com";
$password = md5("udvudvudv5H");
$to = "7"."9221431615";
$from = "Svobodno";
$text = "Привет,+Дима,+из+смсАэро";

$url = "http://gate.smsaero.ru/send/?user=".$user."&password=".$password."&to=".$to."&from=".$from."&text=".$text;

// Инициализация библиотеки curl.
if (!($ch = curl_init())) {
    //Logger::getLogger(GlobFunc::$loggerName)->log("ParserBazaB2B.php->curlRequest():1 Не удалось получить страницу с сайта bazaB2B по адресу: ".$url);
    //return FALSE;
    //TODO: test
    exit("Не удалось инициализировать curl");
}
curl_setopt($ch, CURLOPT_URL, $url); // Устанавливаем URL запроса
//curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/logs/'.$cookieFileName); // Сохранять куки в указанный файл
//curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/logs/'.$cookieFileName); // При запросе передавать значения кук из указанного файла
curl_setopt($ch, CURLOPT_HEADER, false); // При значении true CURL включает в вывод результата заголовки, которые нам не нужны (мы их на сервере не обрабатываем).
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // При значении = true полученный код страницы возвращается как результат выполнения curl_exec.
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Следовать за редиректами
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Максимальное время ожидания ответа от сервера в секундах
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); // Установим значение поля User-agent для маскировки под обычного пользователя
//curl_setopt($ch, CURLOPT_POST, $post !== 0); // Если указаны POST параметры, то включаем их использование
//if ($post) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

// Выполнение запроса
$data = curl_exec($ch);
// Особождение ресурса
curl_close($ch);

// Меняем кодировку с windows-1251 на utf-8
//$data = iconv("windows-1251", "UTF-8", $data);

// Выдаем результат работы, в случае ошибки FALSE
//return $data;
//TODO: test
echo "Алгоритм полностью выполнен. Ответ сервера:".$data;