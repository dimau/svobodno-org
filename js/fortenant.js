/* Инициализируем отображение вкладок при помощи jQuery UI, делаем вкладки вертикальными */
$(function () {
    $("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
    $("#tabs li").removeClass("ui-corner-top").addClass("ui-corner-left");
});

/* Если пользователь кликнет не по тексту названия таба, а по самому табу, то также должно происходить событие выбора таба*/
$("#tabs").on('click', function (event) {
    var target = event.target;
    if (target.nodeName != 'LI') {
        return;
    }
    var idOfTab = $(target.children[0]).attr('href');
    $("#tabs").tabs("select", idOfTab);
})

$("button, .yrt").on('click', function (event) {

});


