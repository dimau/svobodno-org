<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
         More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Админка</title>
    <meta name="description" content="Админка">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="initialscale=1.0, width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .simpleBlockForAnyContent {
            margin: 10px 0 10px 0;
            font-size: 0.9em;
            line-height: 2em;
        }

        .simpleBlockForAnyContent .content {
            font-size: 1.1em;
            color: #6A9D02;
            font-weight: bold;
        }

        .simpleBlockForAnyContent .setOfInstructions {
            float: left;
            margin-left: 15px;
            list-style: none;
        }

        .simpleBlockForAnyContent .setOfInstructions li {
            display: inline-block;
            margin-left: 10px;
            margin-right: 10px;
            font-size: 1em;
        }

		/* Используется для выделения описания той заявки на просмотр, что интересует админа */
		.highlightedBlock {
			padding: 5px;
			border: 2px solid red;
		}

    </style>

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <!-- Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера -->
    <script>
        if (typeof jQuery === 'undefined') document.write("<scr" + "ipt src='js/vendor/jquery-1.7.2.min.js'></scr" + "ipt>");
    </script>
    <!-- jQuery UI с моей темой оформления -->
    <script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>

</head>

<body>
<div class="page_without_footer">
    <div class="page_main_content">
        <div class="headerOfPage">
            Панель администратора -> Заявка на просмотр
        </div>

        <div class="simpleBlockForAnyContent">

            <div style="float: left; width: 49%;">
				<?php
				// Шаблон для сведений о собственнике
				include "templates/adminTemplates/templ_adminUserItem.php";
				?>
            </div>

            <div style="float: right; width: 49%;">
				<?php
				// Шаблон для сведений об объекте недвижимости
				include "templates/adminTemplates/templ_adminPropertyItem.php";
				?>
            </div>

            <div class="clearBoth"></div>
            <hr>

            <div style="margin-left: 40px;">
				<?php foreach ($allRequestsToView as $requestToView): ?>
					<div class="<?php if ($requestToView['id'] == $requestToViewId) echo "highlightedBlock";?>">
						<?php include "templates/adminTemplates/templ_adminRequestToViewDetailedItem.php";?>
					</div>
                	<hr>
				<?php endforeach; ?>
            </div>

        </div>

    </div>
    <!-- /end.page_main_content -->
    <!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
    <div class="page-buffer"></div>
</div>
<!-- /end.page_without_footer -->
<div class="footer">
    2012 г. Вопросы и пожелания по работе портала можно передавать по телефону: 8-922-143-16-15, e-mail:
    support@svobodno.org
</div>
<!-- /end.footer -->

<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
<script src="js/main.js"></script>
<script>
    $(document).ready(function () {

		// Обработчик клика по статусу запроса на просмотр
        $(".statusAnchor").on('click', function () {
			// Получим головной элемент описания данного запроса на просмотр класса requestToViewBlock
			var requestToViewBlock = $(this).closest(".requestToViewBlock");
			// Сделаем видимым селект для выбора статуса
            $(".statusSelect", requestToViewBlock).css('display', '');
			// Сделаем невидимой ссылку с названием текущего статуса
			$(this).css('display', 'none');
        });

		// Обработчик выбора нового статуса в селекте
		$(".statusSelect").on('change', function() {

            // Получим головной элемент описания данного запроса на просмотр (класса requestToViewBlock)
            var requestToViewBlock = $(this).closest(".requestToViewBlock");

            // Получим id запроса на просмотр
            var requestToViewId = requestToViewBlock.attr('requestToViewId');

            // Получим новое значение статуса для запроса на просмотр
            var newValue = $('.statusSelect option:selected', requestToViewBlock).val();

            jQuery.post("AJAXChangeRequestToView.php", {"requestToViewId": requestToViewId, "action": "changeStatus", "newValue": newValue}, function (data) {
                $(data).find("span[status='successful']").each(function () {
                    // Изменяем соответствующим образом текст статуса и его видимость
                    $(".statusAnchor", requestToViewBlock).html(newValue).css('display', '');
                    $(".statusSelect", requestToViewBlock).css('display', 'none');
                    $(".statusSelect [value='" + newValue + "']", requestToViewBlock).attr("selected", "selected");
                });
                $(data).find("span[status='denied']").each(function () {
                    /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
                });
            }, "xml");
		});


		// Обработчик клика по ссылке для изменения удобного времени просмотра арендатора
        $(".tenantTimeAnchor").on('click', function () {
            // Получим головной элемент описания данного запроса на просмотр класса requestToViewBlock
            var requestToViewBlock = $(this).closest(".requestToViewBlock");
            // Сделаем видимым блок редактирования удобного времени для арендатора
            $(".tenantTimeEditBlock", requestToViewBlock).css('display', '');
            // Сделаем невидимым первоначальный текст
            $(".tenantTimeText", requestToViewBlock).css('display', 'none');
        });

        // Обработчик клика по кнопка сохранения удобного времени просмотра арендатора
        $(".tenantTimeSaveButton").on('click', function() {

            // Получим головной элемент описания данного запроса на просмотр (класса requestToViewBlock)
            var requestToViewBlock = $(this).closest(".requestToViewBlock");

            // Получим id запроса на просмотр
            var requestToViewId = requestToViewBlock.attr('requestToViewId');

            // Получим новое значение удобного времени просмотра арендатора
            var newValue = $('.tenantTimeTextArea', requestToViewBlock).val();

            jQuery.post("AJAXChangeRequestToView.php", {"requestToViewId": requestToViewId, "action": "changeTenantTime", "newValue": newValue}, function (data) {
                $(data).find("span[status='successful']").each(function () {
                    // Изменяем соответствующим образом текст статуса и его видимость
                    $(".tenantTimeText", requestToViewBlock).html(newValue).css('display', '');
                    $(".tenantTimeEditBlock", requestToViewBlock).css('display', 'none');
                    $(".tenantTimeTextArea", requestToViewBlock).val(newValue);
                });
                $(data).find("span[status='denied']").each(function () {
                    /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
                });
            }, "xml");
        });

		// Обработчик клика по кнопка отмены изменения удобного времени просмотра арендатора
        $(".tenantTimeCancelButton").on('click', function() {

            // Получим головной элемент описания данного запроса на просмотр (класса requestToViewBlock)
            var requestToViewBlock = $(this).closest(".requestToViewBlock");

            // Получим предыдущее значение удобного времени просмотра арендатора
            var oldValue = $('.tenantTimeText', requestToViewBlock).html();

			// Делаем видимым предыдущий текст удобного времени просмотра арендатора
            $(".tenantTimeText", requestToViewBlock).css('display', '');

			// Скрываем блок для редактирования удобного времени просмотра арендатора
            $(".tenantTimeEditBlock", requestToViewBlock).css('display', 'none');
            $(".tenantTimeTextArea", requestToViewBlock).val(oldValue);
        });

        // Обработчик клика по ссылке для изменения комментария арендатора
        $(".tenantCommentAnchor").on('click', function () {
            // Получим головной элемент описания данного запроса на просмотр класса requestToViewBlock
            var requestToViewBlock = $(this).closest(".requestToViewBlock");
            // Сделаем видимым блок редактирования комментария арендатора
            $(".tenantCommentEditBlock", requestToViewBlock).css('display', '');
            // Сделаем невидимым первоначальный текст
            $(".tenantCommentText", requestToViewBlock).css('display', 'none');
        });

        // Обработчик клика по кнопка сохранения комментария арендатора
        $(".tenantCommentSaveButton").on('click', function() {

            // Получим головной элемент описания данного запроса на просмотр (класса requestToViewBlock)
            var requestToViewBlock = $(this).closest(".requestToViewBlock");

            // Получим id запроса на просмотр
            var requestToViewId = requestToViewBlock.attr('requestToViewId');

            // Получим новое значение комментария арендатора
            var newValue = $('.tenantCommentTextArea', requestToViewBlock).val();

            jQuery.post("AJAXChangeRequestToView.php", {"requestToViewId": requestToViewId, "action": "changeTenantComment", "newValue": newValue}, function (data) {
                $(data).find("span[status='successful']").each(function () {
                    // Изменяем соответствующим образом текст статуса и его видимость
                    $(".tenantCommentText", requestToViewBlock).html(newValue).css('display', '');
                    $(".tenantCommentEditBlock", requestToViewBlock).css('display', 'none');
                    $(".tenantCommentTextArea", requestToViewBlock).val(newValue);
                });
                $(data).find("span[status='denied']").each(function () {
                    /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
                });
            }, "xml");
        });

        // Обработчик клика по кнопка отмены изменения комментария арендатора
        $(".tenantCommentCancelButton").on('click', function() {

            // Получим головной элемент описания данного запроса на просмотр (класса requestToViewBlock)
            var requestToViewBlock = $(this).closest(".requestToViewBlock");

            // Получим предыдущее значение комментария арендатора
            var oldValue = $('.tenantCommentText', requestToViewBlock).html();

            // Делаем видимым предыдущий текст комментария арендатора
            $(".tenantCommentText", requestToViewBlock).css('display', '');

            // Скрываем блок для редактирования комментария арендатора
            $(".tenantCommentEditBlock", requestToViewBlock).css('display', 'none');
            $(".tenantCommentTextArea", requestToViewBlock).val(oldValue);
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