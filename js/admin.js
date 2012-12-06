/* JS сценарии, используемые на страницах админки */
$(document).ready(function () {

    // Обработчик клика по статусу запроса на просмотр
    $(".statusAnchor").on('click', startEditStatusOfRequestToView);

    // Обработчик клика по ссылке для изменения удобного времени просмотра арендатора
    $(".tenantTimeAnchor").on('click', startEditTenantTimeOfRequestToView);

    // Обработчик клика по ссылке для изменения комментария арендатора
    $(".tenantCommentAnchor").on('click', startEditTenantCommentOfRequestToView);

    // Обработчик клика по ссылке для изменения ближайшей даты и времени просмотра
    $(".earliestDateAnchor").on('click', startEditEarliestDate);

    // Обработчик выбора нового статуса в селекте
    $(".statusSelect").on('change', changeStatusOfRequestToView);

    // Обработчик клика по кнопка сохранения удобного времени просмотра арендатора
    $(".tenantTimeSaveButton").on('click', changeTenantTimeOfRequestToView);

    // Обработчик клика по кнопка сохранения комментария арендатора
    $(".tenantCommentSaveButton").on('click', changeTenantCommentOfRequestToView);

    // Обработчик клика по кнопка сохранения измененной даты и времени ближайшего просмотра
    $(".earliestDateSaveButton").on('click', changeEarliestDate);

    // Обработчик клика по кнопка отмены изменения удобного времени просмотра арендатора
    $(".tenantTimeCancelButton").on('click', cancelEditTenantTimeOfRequestToView);

    // Обработчик клика по кнопка отмены изменения комментария арендатора
    $(".tenantCommentCancelButton").on('click', cancelEditTenantCommentOfRequestToView);

    // Обработчик клика по кнопка отмены изменения ближайшей даты и времени просмотра
    $(".earliestDateCancelButton").on('click', cancelEditEarliestDate);

    // Показывает форму редактирования для статуса запроса на просмотр
    // В качестве this - элемент управления активирующий форму редактирования
    function startEditStatusOfRequestToView() {
        // Получим головной элемент описания данного запроса на просмотр класса requestToViewBlock
        var requestToViewBlock = $(this).closest(".requestToViewBlock");
        // Сделаем видимым селект для выбора статуса
        $(".statusSelect", requestToViewBlock).css('display', '');
        // Сделаем невидимой ссылку с названием текущего статуса
        $(this).css('display', 'none');
    }

    // Показывает форму редактирования для удобного времени просмотра арендатора
    // В качестве this - элемент управления активирующий форму редактирования
    function startEditTenantTimeOfRequestToView() {
        // Получим головной элемент описания данного запроса на просмотр класса requestToViewBlock
        var requestToViewBlock = $(this).closest(".requestToViewBlock");
        // Сделаем видимым блок редактирования удобного времени для арендатора
        $(".tenantTimeEditBlock", requestToViewBlock).css('display', '');
        // Сделаем невидимым первоначальный текст
        $(".tenantTimeText", requestToViewBlock).css('display', 'none');
    }

    // Показывает форму редактирования для комментария арендатора
    // В качестве this - элемент управления активирующий форму редактирования
    function startEditTenantCommentOfRequestToView() {
        // Получим головной элемент описания данного запроса на просмотр класса requestToViewBlock
        var requestToViewBlock = $(this).closest(".requestToViewBlock");
        // Сделаем видимым блок редактирования комментария арендатора
        $(".tenantCommentEditBlock", requestToViewBlock).css('display', '');
        // Сделаем невидимым первоначальный текст
        $(".tenantCommentText", requestToViewBlock).css('display', 'none');
    }

    // Отправляет и обрабатывает ответ AJAX запроса для изменения статуса запроса на просмотр
    // В качестве this - элемент управления по событию которого и выполняется действие
    function changeStatusOfRequestToView() {

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
    }

    // Отправляет и обрабатывает ответ AJAX запроса для изменения удобного времени просмотра арендатора
    // В качестве this - элемент управления по событию которого и выполняется действие
    function changeTenantTimeOfRequestToView() {

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
    }

    // Отправляет и обрабатывает ответ AJAX запроса для изменения комментария арендатора
    // В качестве this - элемент управления по событию которого и выполняется действие
    function changeTenantCommentOfRequestToView() {

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
    }

    // Отменяет редактирование (закрывает форму) для изменения удобного времени просмотра арендатора
    // В качестве this - элемент управления по событию которого и выполняется действие
    function cancelEditTenantTimeOfRequestToView() {

        // Получим головной элемент описания данного запроса на просмотр (класса requestToViewBlock)
        var requestToViewBlock = $(this).closest(".requestToViewBlock");

        // Получим предыдущее значение удобного времени просмотра арендатора
        var oldValue = $('.tenantTimeText', requestToViewBlock).html();

        // Делаем видимым предыдущий текст удобного времени просмотра арендатора
        $(".tenantTimeText", requestToViewBlock).css('display', '');

        // Скрываем блок для редактирования удобного времени просмотра арендатора
        $(".tenantTimeEditBlock", requestToViewBlock).css('display', 'none');
        $(".tenantTimeTextArea", requestToViewBlock).val(oldValue);
    }

    // Отменяет редактирование (закрывает форму) для изменения комментария арендатора
    // В качестве this - элемент управления по событию которого и выполняется действие
    function cancelEditTenantCommentOfRequestToView() {

        // Получим головной элемент описания данного запроса на просмотр (класса requestToViewBlock)
        var requestToViewBlock = $(this).closest(".requestToViewBlock");

        // Получим предыдущее значение комментария арендатора
        var oldValue = $('.tenantCommentText', requestToViewBlock).html();

        // Делаем видимым предыдущий текст комментария арендатора
        $(".tenantCommentText", requestToViewBlock).css('display', '');

        // Скрываем блок для редактирования комментария арендатора
        $(".tenantCommentEditBlock", requestToViewBlock).css('display', 'none');
        $(".tenantCommentTextArea", requestToViewBlock).val(oldValue);
    }

    // Показывает форму редактирования для ближайшей даты и времени просмотра
    // В качестве this - элемент управления активирующий форму редактирования
    function startEditEarliestDate() {
        // Получим головной элемент описания данного объекта недвижимости (класса propertyBlock)
        var propertyBlock = $(this).closest(".propertyBlock");
        // Сделаем видимым блок редактирования даты просмотра
        $(".earliestDateEditBlock", propertyBlock).css('display', '');
        // Сделаем невидимым первоначальный текст даты просмотра
        $(".earliestDateFullText", propertyBlock).css('display', 'none');
    }

    // Отправляет и обрабатывает ответ AJAX запроса для изменения ближайшей даты и времени просмотра
    // В качестве this - элемент управления по событию которого и выполняется действие
    function changeEarliestDate() {
        // Получим головной элемент описания данного объекта недвижимости (класса propertyBlock)
        var propertyBlock = $(this).closest(".propertyBlock");

        // Получим id объекта недвижимости
        var propertyId = propertyBlock.attr('propertyId');

        // Получим новое значение даты и времени просмотра
        var earliestDate = $('.earliestDateInput', propertyBlock).val();
        var earliestTimeHours = $('.earliestTimeHoursInput', propertyBlock).val();
        var earliestTimeMinutes = $('.earliestTimeMinutes', propertyBlock).val();
        var newValue = {
            "earliestDate": earliestDate,
            "earliestTimeHours": earliestTimeHours,
            "earliestTimeMinutes": earliestTimeMinutes
        };

        jQuery.post("AJAXChangePropertyData.php", {"propertyId": propertyId, "action": "changeEarliestDate", "newValueArr": JSON.stringify(newValue)}, function (data) {
            $(data).find("span[status='successful']").each(function () {
                // Изменяем соответствующим образом текст даты ближайшего просмотра и его видимость
                $(".earliestDateText", propertyBlock).html(earliestDate);
                $(".earliestTimeHoursText", propertyBlock).html(earliestTimeHours);
                $(".earliestTimeMinutesText", propertyBlock).html(earliestTimeMinutes);
                $(".earliestDateFullText", propertyBlock).css('display', '');
                $(".earliestDateEditBlock", propertyBlock).css('display', 'none');
            });
            $(data).find("span[status='denied']").each(function () {
                /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
            });
        }, "xml");
    }

    // Отменяет редактирование (закрывает форму) для изменения ближайшего даты и времени просмотра
    // В качестве this - элемент управления по событию которого и выполняется действие
    function cancelEditEarliestDate() {
        // Получим головной элемент описания данного объекта недвижимости (класса propertyBlock)
        var propertyBlock = $(this).closest(".propertyBlock");

        // Получим предыдущее значение даты и времени просмотра
        var oldEarliestDate = $('.earliestDateText', propertyBlock).html();
        var oldEarliestTimeHours = $('.earliestTimeHoursText', propertyBlock).html();
        var oldEarliestTimeMinutes = $('.earliestTimeMinutesText', propertyBlock).html();

        // Вернем исходные значения полям ввода
        $('.earliestDateInput', propertyBlock).val(oldEarliestDate);
        $('.earliestTimeHoursInput', propertyBlock).val(oldEarliestTimeHours);
        $('.earliestTimeMinutes', propertyBlock).val(oldEarliestTimeMinutes);

        // Делаем видимым предыдущий текст комментария арендатора
        $(".earliestDateFullText", propertyBlock).css('display', '');
        // Скрываем блок для редактирования комментария арендатора
        $(".earliestDateEditBlock", propertyBlock).css('display', 'none');
    }
});