/* JS сценарии, используемые на страницах админки */
$(document).ready(function () {

    /*** Снятие с просмотра объявления (перенос в архивную базу для чужих) ***/
    $(".unpublishAdvert").on('click', unpublishAdvert);

    // Отправляет и обрабатывает ответ AJAX запроса для снятия объекта с публикации (его переноса в архивную БД для чужих объявлений)
    // В качестве this - элемент управления по событию которого и выполняется действие
    function unpublishAdvert() {

        // Получим головной элемент описания данного объекта недвижимости (класса propertyBlock)
        var propertyBlock = $(this).closest(".propertyBlock");

        // Получим id объекта недвижимости
        var propertyId = propertyBlock.attr('propertyId');

        // Непосредственная работа с AJAX запросом
        jQuery.post("AJAXChangePropertyData.php", {"propertyId":propertyId, "action":"unpublishAdvert"}, function (data) {
            $(data).find("span[status='successful']").each(function () {
                $('.unpublishAdvert', propertyBlock).html("<span style='color: silver;'>снято с публикации</span>");
            });
            $(data).find("span[status='denied']").each(function () {
                /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
            });
        }, "xml");
    }
});