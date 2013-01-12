<!DOCTYPE html>
<html>
<head>

    <!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-language" content="ru">
    <meta name="description" content="Вход на портал аренды недвижимости Свободно Svobodno.org">
    <!-- Если у пользователя IE: использовать последний доступный стандартный режим отображения независимо от <!DOCTYPE> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- Оптимизация отображения на мобильных устройствах -->
    <!--<meta name="viewport" content="initialscale=1.0, width=device-width">-->
    <!-- end meta -->

    <title>Вход</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <!-- end CSS -->

    <!-- JS -->
    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
    <!-- end JS -->

</head>

<body>
<div class="page_without_footer">

    <!-- Всплывающее поле для отображения списка ошибок, полученных при проверке данных на сервере (PHP)-->
    <div id="userMistakesBlock" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <div>
                <p>
                    <span class="icon-mistake ui-icon ui-icon-info"></span>
                    <span
                        id="userMistakesText">Для продолжения, пожалуйста, дополните или исправьте следующие данные:</span>
                </p>
                <ol><?php
                    if (isset($errors) && count($errors) != 0) {
                        foreach ($errors as $key => $value) {
                            echo "<li>$value</li>";
                        }
                    }
                    ?></ol>
            </div>
        </div>
    </div>

    <?php
        // Сформируем и вставим заголовок страницы
	require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_header.php";
    ?>

    <div class="page_main_content">


        <div class="miniBlock">
            <div class="miniBlockHeader">
                Введите логин и пароль
            </div>
            <div class="miniBlockContent">
                <form name="loginParol" method="post" action="login.php?action=signIn">
                    <table>
                        <tbody>
                            <tr>
                                <td><label>Логин: </label></td>
                                <td>
                                    <input type="text" name="login" size="23" tabindex="0"
                                           placeholder=" e-mail или телефон" autofocus>
                                </td>
                            </tr>
                            <tr>
                                <td><label>Пароль: </label></td>
                                <td>
                                    <input type="password" name="password" size="23" maxlength="50">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><a href="passwordRecovery.php">Забыли пароль?</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <div>
                        <button type="submit" id="buttonSubmit" name="buttonSubmit">Войти</button>
                        <a class="buttonRegistration" href="registration.php">Зарегистрироваться</a>
                        <div class="clearBoth"></div>
                    </div>
                </form>
            </div>
            <!-- /end.miniBlockContent -->
            <div class="clearBoth"></div>
        </div>

    </div>
    <!-- /end.page_main_content -->

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.page_without_footer -->
<div class="footer">
    2012 г. Вопросы и пожелания по работе портала можно передавать по телефону: 8-922-160-95-14, e-mail: support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
<script src="js/main.js"></script>
<script>
    // Отображение результатов обработки формы на PHP
    if ($('#userMistakesBlock ol').html() != "") {
        $('#userMistakesBlock').on('click', function () {
            $(this).slideUp(800);
        });
        $('#userMistakesBlock').css('display', 'block');
    }
</script>
<!-- end scripts -->

</body>
</html>