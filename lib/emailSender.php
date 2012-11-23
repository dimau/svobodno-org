<?php

    // Получаем сообщения о появлении новых объектов недвижимости
   /* $res = $this->DBlink->query("SELECT * FROM messagesNewProperty");
    if (($this->DBlink->errno)
        OR (($messagesNewProperty = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
    ) {
        //TODO: сделать логирование ошибки
        $messagesNewProperty = array();
    }*/

    // Рассылаем сообщения
    //foreach ($messagesNewProperty as $value) {
        mail('dimau777@gmail.com', 'Новое предложение по Вашему поиску', 'тестовое письмо', "From: Тестовая компания <support@test.org>");
    //}

    //TODO: тест
    echo "рассылка проведена";