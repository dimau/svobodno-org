<!DOCTYPE html>
<html>
<head>

    <!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-language" content="ru">
    <meta name="description" content="Аренда недвижимости в Екатеринбурге">
    <!-- Если у пользователя IE: использовать последний доступный стандартный режим отображения независимо от <!DOCTYPE> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- Информация для поисковых систем об индексации страницы -->
    <meta name="document-state" content="Dynamic">
    <meta name="keywords" content="Недвижимость, в Екатеринбурге, аренда, жилье, собственник, свободно">
    <meta name="robots" content="index,follow">
    <!-- Оптимизация отображения на мобильных устройствах -->
    <!--<meta name="viewport" content="initialscale=1.0, width=device-width">-->
    <!-- end meta -->

    <title>Svobodno.org</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/feature-carousel.css">
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
    <!-- Карусель -->
    <script src="js/vendor/jquery.featureCarousel.js"></script>
    <!-- end JS -->

</head>

<body>

<div class="pageWithoutFooter">

    <?php
        require $websiteRoot . "/templates/templ_header.php";
    ?>

    <div class="carousel-container">
        <div id="carousel">
            <div class="carousel-feature">
                <a href="#"><img class="carousel-image" alt="Image Caption"
                                 src="uploaded_files/7/big/70bcdb9e7b9648f145d097ed72c33a28.jpeg"></a>

                <div class="carousel-caption">
                    <p>
                        Телефоны собственников жилья БЕСПЛАТНО<br>
                        Всем зарегистрированным пользователям
                    </p>
                </div>
            </div>
            <div class="carousel-feature">
                <a href="#"><img class="carousel-image" alt="Image Caption"
                                 src="uploaded_files/8/big/8191e133c5de9ac88f6e2044f9fa1492.jpeg"></a>

                <div class="carousel-caption">
                    <p>
                        Экономьте свое время<br>
                        Мы отбираем только реальные квартиры и комнаты!
                    </p>
                </div>
            </div>
            <div class="carousel-feature">
                <a href="#"><img class="carousel-image" alt="Image Caption"
                                 src="uploaded_files/a/big/abc59f61571d0be307c5a055edb26d4b.jpeg"></a>

                <div class="carousel-caption">
                    <p>
                        Общайтесь напрямую с собственниками<br>
                        Мы обеспечим Вас контактами!
                    </p>
                </div>
            </div>
            <div class="carousel-feature">
                <a href="#"><img class="carousel-image" alt="Image Caption"
                                 src="uploaded_files/b/big/b5a2f24d0ed2160c95701a94e2d6a72c.jpeg"></a>

                <div class="carousel-caption">
                    <p>
                        Примерно 30% исходных объявлений имеют фотографии<br>
                        Просматривайте их на премиум доступе!
                    </p>
                </div>
            </div>
            <div class="carousel-feature">
                <a href="#"><img class="carousel-image" alt="Image Caption"
                                 src="uploaded_files/c/big/ccb661a47f3f25dc25ca154234728f62.jpeg"></a>

                <div class="carousel-caption">
                    <p>
                        Вы не пропустите подходящий вариант<br>
                        Мы сообщим о нем по e-mail
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="tabs" class="mainContentBlock">

        <ul class="tabsMenu">
            <li class="tabsMenuItem">
                <a href="#tabs-1">Арендатору</a>
            </li>
            <!--<li class="tabsMenuItem">
                <a href="#tabs-2">Собственнику</a>
            </li>-->
        </ul>

        <div id="tabs-1">
            Приветствуем Вас на сайте Svobodno.org! В настоящий момент мы переводим наш ресурс на новую бизнес-модель. Уже в начале следующего месяца Вы сможете пользоваться им бесплатно - получая всю информацию по каждому объявлению, кроме фотографий, сразу после регистрации. Платный доступ нужен будет только тем, кто пожелает получить дополнительные преимущества: просмотр фотографий, исходных объявлений и e-mail оповещения.
            Будем рады, если Вы воспользуетесь нашим сайтом в начале апреля!

            <!--<div style="width: 49%; float: left;">
                <p style="margin-top: 0;">
                    Приветствуем Вас на сайте Svobodno.org! Тем, кто ищет жилье, мы предлагаем пройти 2 простых шага.
                </p>

                <p>

                <div style="font-weight: bold;">
                    1 шаг - Регистрация
                </div>
                <div>
                    Регистрация позволит не только использовать поиск по интересующим Вас параметрам, но и добавлять
                    объявления в избранные. Кроме того, Вы сможете получать e-mail уведомления о новых объектах, которые
                    подходят под Ваши условия поиска.
                </div>
                </p>
                <p>

                <div style="font-weight: bold;">
                    2 шаг - Премиум доступ
                </div>
                <div>
                    Получив премиум доступ, Вы сможете видеть контакты собственников заинтересовавших Вас объектов. Если
                    объявление первоначально было опубликовано собственником на другом ресурсе, Вы увидите также
                    исходную
                    ссылку. Остальные преимущества премиум доступа перечислены в списке справа.
                </div>
                </p>
            </div>

            <div style="width: 49%; float: right;">
                <div>
                    <span style="font-weight: bold;">Преимущества премиум доступа:</span>
                    <ul class="benefits">
                        <li>
                            Автоматический подбор вариантов недвижимости
                        </li>
                        <li>
                            Email оповещение о новых подходящих под условия поиска объектах
                        </li>
                        <li>
                            Избранные объявления (добавление/удаление/просмотр)
                        </li>
                        <li>
                            Автоматическое применение условий поиска при авторизации на сайте
                        </li>
                        <!--<li>
                            Бланки необходимых документов и разъяснения по ним (договор аренды, лист расчетов с
                            собственником, расписка о получении оплаты...)
                        </li>-->
            <!--<li>
                            Техническая поддержка и консультирование по работе сайта
                        </li>
                    </ul>
                </div>

                <div style="margin: 1em 0 0 1em;">
                    <?php
                    //require $websiteRoot . "/templates/templ_paymentButtons.php";
                    ?>
                </div>
            </div>-->

            <div class="clearBoth"></div>

        </div>
        <!-- /end.tabs-1 -->

        <!--<div id="tabs-2"></div><!-- /end.tabs-2 -->

    </div>
    <!-- /end.tabs -->

    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.pageWithoutFooter -->
<div class="footer">
    2013 г. Мы будем рады ответить на Ваши вопросы, отзывы, предложения по телефону: 8-922-160-95-14, или e-mail: support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
<script src="js/main.js"></script>
<script>
    $(document).ready(function () {
        $('#carousel').featureCarousel({
            trackerIndividual:false,
            trackerSummation:false,
            largeFeatureWidth:400,
            largeFeatureHeight:300,
            smallFeatureWidth:200,
            smallFeatureHeight:150,
            smallFeatureOffset:110
        });
    });
</script>
<!-- end scripts -->

</body>
</html>
