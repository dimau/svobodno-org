<?php
$oldestActualTimeStamp = time() - (3 * 24 * 60 * 60);

echo "Текущее время: ".time()." Или: ".date("Ymd")."<br>";
echo "3 дня назад: ".$oldestActualTimeStamp." Или: ".date("Ymd", $oldestActualTimeStamp)."<br>";
echo "Дата объявлений: "."1348683429"." Или: ".date("Ymd", 1348683429)."<br>";
