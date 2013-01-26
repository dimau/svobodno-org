<?php

/**********************************************************************************
 * БАЗА ДАННЫХ
 *********************************************************************************/

// Функция выполняет запросы к БД
function executeSQL($DBlink, $request, $paramsType, $paramsArr) {

    $stmt = mysqli_prepare($DBlink, $request);
    if ($stmt) {

        // Подготовим массив для передачи в mysqli_stmt_bind_param
        $arr = array($stmt, $paramsType);
        $arr = array_merge($arr, $paramsArr);

        call_user_func_array('mysqli_stmt_bind_param', $arr);
        mysqli_stmt_execute($stmt);
        $res = mysqli_affected_rows($DBlink);
        mysqli_stmt_close($stmt);
    }

    return $res;
}

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


/**********************************************************************************
 * man.php
 *********************************************************************************/

// Получаем список пользователей, чьей недвижимостью интересовался наш пользователь ($userId) в качестве арендатора, и чьи анкеты он имеет право смотреть
$tenantsWithSignUpToViewRequest = array();
if ($rez = mysql_query("SELECT interestingPropertysId FROM searchRequests WHERE userId = '" . $userId . "'")) {
    if ($row = mysql_fetch_assoc($rez)) {
        $interestingPropertysId = unserialize($row['interestingPropertysId']);

        // По каждому объекту недвижимости выясняем статус и собственника. Если статус = опубликовано, то собственника добавляем в массив ($visibleUsersIdOwners)
        if ($interestingPropertysId != FALSE && is_array($interestingPropertysId) && count($interestingPropertysId) != 0) {
            // Составляем условие запроса к БД, указывая интересующие нас id объявлений
            $selectValue = "";
            for ($i = 0; $i < count($interestingPropertysId); $i++) {
                $selectValue .= " id = '" . $interestingPropertysId[$i] . "'";
                if ($i < count($interestingPropertysId) - 1) $selectValue .= " OR";
            }
            // Перебираем полученные строки из таблицы, каждая из которых соответствует 1 объявлению
            if ($rez = mysql_query("SELECT userId, status FROM property WHERE " . $selectValue)) {
                for ($i = 0; $i < mysql_num_rows($rez); $i++) {
                    if ($row = mysql_fetch_assoc($rez)) {
                        if ($row['status'] == "опубликовано") {
                            $visibleUsersIdOwners[] = $row['userId'];
                        }
                    }
                }
            }
        }
    }
}

/**********************************************************************************
 * оповещение по email для уведомлений
 *********************************************************************************/

// Получим максимум 100 уведомлений для обработки - обработка уведомлений порционно позволяет снизить негативный эффект в случае повторной обработки (если вдруг запустится второй экземпляр скрипта пока работает первый)
$messages = DBconnect::selectMessagesForEmail(100);
$amountMessages = count($messages);

// Возвращает массив ассоциированных массивов, каждый из которых содержит данные по одному из уведомлений. Если ничего не найдено или произошла ошибка, вернет пустой массив
// Необязательный параметр на входе - максимальное кол-во уведомлений, которые мы хотим получить за одно обращение
public static function selectMessagesForEmail($limit = 100) {

    // Проверка входящих параметров
    if (isset($limit) && !is_int($limit)) return array();

    $stmt = DBconnect::get()->stmt_init();
    if (($stmt->prepare("SELECT * FROM messagesNewProperty WHERE needEmail = 1 LIMIT ?") === FALSE)
        OR ($stmt->bind_param("i", $limit) === FALSE)
        OR ($stmt->execute() === FALSE)
        OR (($res = $stmt->get_result()) === FALSE)
        OR (($res = $res->fetch_all(MYSQLI_ASSOC)) === FALSE)
        OR ($stmt->close() === FALSE)
    ) {
        Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка обращения к БД. Запрос: 'SELECT * FROM messagesNewProperty WHERE needEmail = 1 LIMIT " . $limit . "'. Местонахождение кода: DBconnect::selectMessagesNewPropertyForEmail():1. Выдаваемая ошибка: " . $stmt->errno . " " . $stmt->error . ". ID пользователя: не определено");
        return array();
    }

    return $res;
}

// Если отправлять нечего, то прекращает выполнение скрипта
if ($amountMessages == 0) exit();

// Инициализируем класс для отправки e-mail и указываем постоянные параметры (верные для любых уведомлений)
$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
try {
    $mail->SetFrom('support@svobodno.org', 'Svobodno.org');
    $mail->AddReplyTo('support@svobodno.org', 'support');
    //$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
} catch (phpmailerException $e) {
    echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
    echo $e->getMessage(); //Boring error messages from anything else!
}

// Обрабатываем каждое уведомление индивидуально
foreach ($messages as $message) {

    if ($message['messageType'] == "newProperty") {
        $MsgHTML = View::getHTMLforMessageNewProperty($message);
    }

    // Отправка очередного e-mail
    try {
        $mail->AddAddress('dimau777@gmail.com', 'Ushakov');
        $mail->Subject = 'Новое объявление: ';
        $mail->MsgHTML($MsgHTML);
        $mail->Send();
    } catch (phpmailerException $e) {
        echo $e->errorMessage(); //Pretty error messages from PHPMailer
    } catch (Exception $e) {
        echo $e->getMessage(); //Boring error messages from anything else!
    }
}