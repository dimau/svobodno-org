<!DOCTYPE html>
<html>
<head>

    <!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-language" content="ru">
    <meta name="description" content="Регистрация нового пользователя">
    <!-- Если у пользователя IE: использовать последний доступный стандартный режим отображения независимо от <!DOCTYPE> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- Оптимизация отображения на мобильных устройствах -->
    <!--<meta name="viewport" content="initialscale=1.0, width=device-width">-->
    <!-- end meta -->

    <title>Регистрация</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/fileuploader.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .benefits {
            margin-top: 0;
        }

        .bottomControls {
            padding: 10px 0px 0px 0px;
        }

        .backButton {
            float: left;
        }

        .forwardButton, .submitButton {
            float: right;
        }
    </style>
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
    <!-- Русификатор виджета календарь -->
    <script src="js/vendor/jquery.ui.datepicker-ru.js"></script>
    <!-- Загрузчик фотографий на AJAX -->
    <script src="js/vendor/fileuploader.js" type="text/javascript"></script>
    <!-- end JS -->

</head>

<body>
<div class="pageWithoutFooter">

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
                    if (is_array($errors) && count($errors) != 0) {
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
    require $websiteRoot . "/templates/templ_header.php";
    ?>

    <div class="headerOfPage">
        Зарегистрируйтесь
    </div>

    <form name="personalInformation" id="personalInformationForm" method="post"
          action="registration.php?action=registration<?php if ($isAdmin['newAdvertAlien'] && $alienOwner == "true") echo "&alienOwner=true";?><?php if ($isOwner) echo "&typeOwner=true";?><?php if ($isTenant) echo "&typeTenant=true";?>">
        <div id="tabs" class="mainContentBlock">
            <ul>
                <li>
                    <a href="#tabs-1">Личные данные</a>
                </li>
                <?php if ($userCharacteristic['typeTenant']): ?>
                <li>
                    <a href="#tabs-2">Что ищете?</a>
                </li>
                <?php endif; ?>
            </ul>

            <div id="tabs-1">
                <div class="shadowText">
                    <span style="color: red">*</span> - обязательное поле
                </div>

                <fieldset class="edited left private">
                    <legend>
                        Личные данные
                    </legend>
                    <table>
                        <tbody>
                        <tr title="Как к Вам обращаться?">
                            <td class="itemLabel">
                                Имя
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input name="name" id="name" type="text" autofocus value='<?php echo $userCharacteristic['name']?>'>
                            </td>
                        </tr>
                        <tr title="Мобильный, например: 9224527541. Будет использоваться в качестве логина">
                            <td class="itemLabel">
                                Телефон
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input type="text" name="telephon" id="telephon"
                                       value='<?php echo $userCharacteristic['telephon'];?>'>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemLabel">
                                Пароль
                            </td>
                            <td class="itemRequired">
                                *
                            </td>
                            <td class="itemBody">
                                <input type="password" name="password" id="password"
                                       maxlength="50" value='<?php echo $userCharacteristic['password'];?>'>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemLabel">
                                E-mail
                            </td>
                            <td class="itemRequired">
                            </td>
                            <td class="itemBody">
                                <input type="text" name="email" id="email" value='<?php echo $userCharacteristic['email'];?>'>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </fieldset>

                <fieldset class="edited right private">
                    <legend>
                        Регистрация позволит бесплатно
                    </legend>
                    <ul class="benefits">
                        <li>
                            Получать контактные телефоны собственников
                        </li>
                        <li>
                            Добавлять объявления в избранные и в любой момент просматривать их
                        </li>
                        <li>
                            Не указывать повторно условия поиска - портал все запомнит
                        </li>
                    </ul>
                    <div style="margin-top: 1em;">
                        Кроме того, после регистрации Вы сможете получить премиум доступ к сервису Svobodno.org
                    </div>
                </fieldset>

                <div class="bottomControls">
                    <div style="float: right; margin-bottom: 10px; text-align: left;">
                        <label><input type="checkbox" name="lic" id="lic"
                                      value="yes" <?php if ($userCharacteristic['lic'] == "yes") echo "checked";?>> Я
                            принимаю условия <a href="useragreement.php" target="_blank">лицензионного
                                соглашения</a></label>
                    </div>
                    <div class="clearBoth"></div>
                    <?php if ($userCharacteristic['typeTenant']): ?>
                        <button class="forwardButton mainButton">Далее</button>
                    <?php else: ?>
                        <button type="submit" name="submitButton" class="submitButton mainButton">Отправить</button>
                    <?php endif; ?>
                    <div class="clearBoth"></div>
                </div>
            </div>
            <!-- /end.tabs-1 -->

            <?php if ($userCharacteristic['typeTenant']): ?>
            <div id="tabs-2">
                <div class="shadowText">
                    Заполните форму как можно подробнее, это позволит системе подобрать для Вас наиболее интересные
                    предложения
                    <br>
                </div>

                <?php
                // Подключим форму для ввода и редактирования данных о поисковом запросе пользователя
                require $websiteRoot . "/templates/editableBlocks/templ_editableSearchRequest.php";
                ?>

                <div class="bottomControls">
                    <a href="" class="backButton">Назад</a>
                    <button type="submit" name="submitButton" class="submitButton mainButton">Отправить</button>
                    <div class="clearBoth"></div>
                </div>

            </div>
            <!-- /end.tabs-2 -->
            <?php endif;?>

        </div>
        <!-- /end.tabs -->

    </form>

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.pageWithoutFooter -->
<div class="footer">
    2013 г. Мы будем рады ответить на Ваши вопросы, отзывы, предложения по телефону: 8-922-160-95-14, или e-mail: support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript -->
<script>
    var typeTenant = <?php if ($userCharacteristic['typeTenant']) echo "true"; else echo "false"; // Является ли регистрируемый пользователь арендатором ?>;
    var typeOwner = <?php if ($userCharacteristic['typeOwner']) echo "true"; else echo "false"; // Является ли регистрируемый пользователь собственником ?>;
    var isAlienOwnerRegistration = <?php if ($isAdmin['newAdvertAlien'] && $alienOwner == "true") echo "true"; else echo "false"; // Если регистрируется новый чужой собственник, то JS проверки на заполненность полей не проводятся ?>;
</script>
<script src="js/main.js"></script>
<script src="js/registration.js"></script>
<!-- end scripts -->

</body>
</html>