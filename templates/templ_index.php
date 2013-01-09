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

    <title>Главная</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/feature-carousel.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .blockHeader {
            font-family: Georgia, "Times New Roman", Times, serif;
            font-size: 1.3em;
            margin-bottom: 8px;
            text-align: center;
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
    <!-- Карусель -->
    <script src="js/vendor/jquery.featureCarousel.js"></script>
    <!-- end JS -->

</head>

<body>
<div class="page_without_footer">

    <?php
        // Сформируем и вставим заголовок страницы
		require $_SERVER['DOCUMENT_ROOT'] . "/templates/templ_header.php";
    ?>

    <div class="page_main_content">

        <div class="carousel-container">
            <div id="carousel">
                <div class="carousel-feature">
                    <a href="#"><img class="carousel-image" alt="Image Caption"
                                     src="uploaded_files/8/big/8191e133c5de9ac88f6e2044f9fa1492.jpeg"></a>

                    <div class="carousel-caption">
                        <p>
                            Бесплатно для собственников<br>
                            Низкая комиссия для арендаторов - 30%
                        </p>
                    </div>
                </div>
                <div class="carousel-feature">
                    <a href="#"><img class="carousel-image" alt="Image Caption"
                                     src="uploaded_files/a/big/abc59f61571d0be307c5a055edb26d4b.jpeg"></a>

                    <div class="carousel-caption">
                        <p>
                            Находим порядочных арендаторов<br>
                            Подробные анкеты по каждому претенденту на Вашу недвижимость
                        </p>
                    </div>
                </div>
                <div class="carousel-feature">
                    <a href="#"><img class="carousel-image" alt="Image Caption"
                                     src="uploaded_files/b/big/b5a2f24d0ed2160c95701a94e2d6a72c.jpeg"></a>

                    <div class="carousel-caption">
                        <p>
                            Сдать жилье с нами - просто<br>
                            Мы составим подробное описание Вашей недвижимости<br>
                            Мы его опубликуем на всех городских ресурсах<br>
                            Вам останется только выбрать арендатора
                        </p>
                    </div>
                </div>
                <div class="carousel-feature">
                    <a href="#"><img class="carousel-image" alt="Image Caption"
                                     src="uploaded_files/c/big/ccb661a47f3f25dc25ca154234728f62.jpeg"></a>

                    <div class="carousel-caption">
                        <p>
                            Ежедневно от 70 до 150 новых объявлений с реальными данными
                        </p>
                    </div>
                </div>
                <div class="carousel-feature">
                    <a href="#"><img class="carousel-image" alt="Image Caption"
                                     src="uploaded_files/7/big/70bcdb9e7b9648f145d097ed72c33a28.jpeg"></a>

                    <div class="carousel-caption">
                        <p>
                            Арендатор оплачивает комиссию только по факту заселения<br>
                            Работаем с выездом
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tabs">

            <ul>
                <li>
                    <a href="#tabs-1">Собственнику</a>
                </li>
                <li>
                    <a href="#tabs-2">Арендатору</a>
                </li>
            </ul>

            <div id="tabs-1">
                <div class="benefitsBlock"
                     style="width: 49.5%; display: inline-block; vertical-align: top; text-align: center;">
                    <div class="blockHeader">Почему мы - лучший выбор</div>
                    <div class="accordion">
                        <h3><a href="#">Бесплатность услуг</a></h3>

                        <div class="accordionContentUnit">
                            <p>
                                Мы помогаем сдавать недвижимость бесплатно для собственников.
                            </p>
                        </div>
                        <h3><a href="#">Минимум действий – наш сотрудник приедет и все сделает</a></h3>

                        <div class="accordionContentUnit">
                            <p>
                                Мы составим подробное объявление для Вашей недвижимости.
                            </p>

                            <p>
                                Мы опубликуем объявление на всех интернет-ресурсах города для привлечения к нему
                                максимального внимания потенциальных нанимателей (в том числе, на e1.ru, на 66.ru, на
                                avito.ru и других ресурсах). Более того, мы ежедневно будем поднимать Ваше объявление в
                                рейтинге для того, чтобы оно оставалось заметным для наибольшего числа потенциальных
                                арендаторов. Также подробные данные о Вашей недвижимости будут размещены на нашем
                                портале.
                            </p>
                        </div>
                        <h3><a href="#">Легко найти порядочных нанимателей</a></h3>

                        <div class="accordionContentUnit">
                            <p>
                                Все арендаторы, желающие посмотреть Ваш объект недвижимости, заполняют на нашем сайте
                                подробную анкету, которая сразу же передается Вам. Мы просим арендаторов указывать, в
                                том числе, свои фотографии и ссылки на страницы в социальных сетях (одноклассники, в
                                контакте, facebook, twitter).
                            </p>

                            <p>
                                Таким образом, Вы сможете заранее составить свое мнение о потенциальном арендаторе и
                                решить еще до показа: хотите Вы ему сдавать свою недвижимость или нет.
                            </p>
                        </div>
                        <h3><a href="#">Просто сдать недвижимость с первого показа</a></h3>

                        <div class="accordionContentUnit">
                            <p>
                                Так как арендатор заранее подробно ознакомлен с Вашим объектом недвижимости, а Вы
                                ознакомлены с его данными из анкеты, то с большой долей вероятности первый же показ
                                закончится заключением договора аренды. Таким образом, Вы экономите свое время и силы
                                при поиске порядочных нанимателей.
                            </p>
                        </div>
                        <h3><a href="#">Профессиональный договор аренды</a></h3>

                        <div class="accordionContentUnit">
                            <p>
                                Договор аренды будет составлен нашим специалистом с учетом Ваших потребностей и
                                особенностей Вашей недвижимости на основе шаблона, учитывающего лучшие практики в этой
                                области.
                            </p>
                        </div>
                        <h3><a href="#">Полный контроль над Вашим объявлением </a></h3>

                        <div class="accordionContentUnit">
                            <p>
                                Начав работать с нами, Вы получите личный кабинет на сайте, который позволит Вам в любой
                                момент времени самостоятельно редактировать объявление, снимать его с публикации и
                                наоборот – заново публиковать, а также просматривать анкеты всех потенциальных
                                арендаторов, которые им заинтересовались.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="nextActionBlock"
                     style="width: 49.5%; display: inline-block; vertical-align: top; text-align: center;">
                    <div class="blockHeader">Что дальше?</div>
                    <a class="button mainButton" href="forowner.php"><span style="font-size: 1.1em;">Подайте заявку</span><br><span
                        style="font-size: 0.9em;">наш специалист свяжется с Вами</span></a>
                </div>

                <div class="clearBoth"></div>
            </div>
            <!-- /end.tabs-1 -->

            <div id="tabs-2">
                <div class="benefitsBlock" style="width: 49.5%; display: inline-block; vertical-align: top;">
                    <div class="blockHeader">Почему мы - лучший выбор</div>
                    <div class="accordion">
                        <h3><a href="#">Оплата только по факту заселения</a></h3>
                        <div class="accordionContentUnit">
                            <p>
                                Мы не берем никаких авансов за свои услуги. Только после того, как Вы выберете
                                подходящий объект недвижимости, посмотрите его вживую и при нашей поддержке заключите
                                договор аренды с собственником, мы попросим выплатить соответствующую комиссию.
                            </p>
                        </div>

                        <h3><a href="#">Низкий размер комиссии</a></h3>
                        <div class="accordionContentUnit">
                            <p>
                                Размер нашей комиссии составляет 30% от месячной стоимости аренды. Это в 2 раза меньше,
                                чем средняя цена на такие услуги в городе (от 50% до 100%).
                            </p>
                        </div>

                        <h3><a href="#">Подробная и достоверная информация</a></h3>
                        <div class="accordionContentUnit">
                            <p>
                                Мы стараемся постоянно увеличивать количество подробных качественных объявлений с фотографиями на нашем портале для того, чтобы арендаторы, не выходя из дома, могли получить полное представление о той недвижимости, которая сейчас сдается в городе, а также сознательно выбрать лучшее предложение.
                            </p>
                            <p>
                                Для этого мы сами выезжаем на объекты к собственникам, согласившимся на такую схему работы, и сами формируем объявления.
                            </p>
                            <p>
                                Кроме того, мы публикуем объявления, полученные из баз собственников наших проверенных партнеров. Это гарантирует, что, в отличие от большинства других ресурсов, на нашем портале не будут публиковаться рекламные пустышки.
                            </p>
                        </div>

                        <h3><a href="#">Максимальный выбор жилья для аренды в городе</a></h3>
                        <div class="accordionContentUnit">
                            <p>
                                Кроме непосредственной работы с собственниками мы также работаем с базами собствеников от наших партнеров для того, чтобы Вы могли получить максимальный выбор арендного жилья в одном месте.
                            </p>
                            <p>
                                К сожалению, объявления, которые мы получаем от партнеров, далеко не всегда содержат полную информацию, фотографии объекта. Но мы верим, что ради максимального выбора недвижимости эти объявления также должны быть доступны нашим пользователям.
                            </p>
                        </div>
                        <h3><a href="#">Гарантия юридической безопасности сделки</a></h3>

                        <div class="accordionContentUnit">
                            <p>
                                При оформлении сделки наши специалисты проверят документы собственника, подтверждающие его право сдавать данную недвижимость, а также предложат договор, четко разграничивающий ответственность сторон (Наймодатель и Наниматель), их права и обязанности.
                            </p>
                            <p>
                                Это позволит Вам обезопасить себя от неправомерных требований собственника, а также, в случае возникновения той или иной неприятной ситуации, связанной с арендуемой недвижимостью, знать что нужно делать и кто несет ответственность.
                            </p>
                        </div>
                    </div>
                </div>
                <!-- /end.benefitsBlock -->

                <div class="nextActionBlock"
                     style="width: 49.5%; display: inline-block; vertical-align: top; text-align: center;">
                    <div class="blockHeader">Что дальше?</div>
                    <a class="button mainButton" href="registration.php"><span
                        style="font-size: 1.1em;">Зарегистрируйтесь</span><br><span style="font-size: 0.9em;">и получите новые возможности</span></a>
                    <ul class="benefits" style="text-align: left;">
                        <li>
                            Записаться на просмотр любой недвижимости
                        </li>
                        <li>
                            Получать уведомления о появлении подходящих вариантов недвижимости
                        </li>
                        <li>
                            Добавлять объявления в избранные и в любой момент просматривать их
                        </li>
                        <li>
                            Не указывать повторно условия поиска - портал все запомнит
                        </li>
                    </ul>
                    или воспользуйтесь <a href="search.php">Поиском недвижимости</a>
                </div>

                <div class="clearBoth"></div>
            </div>
            <!-- /end.tabs-2 -->
        </div>
        <!-- /end.tabs -->
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
    $(document).ready(function() {
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

<!-- Asynchronous Google Analytics snippet. Change UA-XXXXX-X to be your site's ID.
        mathiasbynens.be/notes/async-analytics-snippet -->
<!-- <script>
        var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
        </script> -->
</body>
</html>
