/**
 * @author dimau
 */

/* Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера */
window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')

/* Инициализируем отображение вкладок при помощи jQuery UI, делаем вкладки вертикальными */
$(function() {
	$("#tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	$( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
});

// Активируем кнопки "Оставить заявку" через jQuery UI
$(function() {
	$("button").button({
	});
});

/* Если пользователь кликнет не по тексту названия таба, а по самому табу, то также должно происходить событие выбора таба*/
$("#tabs").on('click', function(event) {
	var target = event.target;
	if (target.nodeName != 'LI') {
		return;
	}
	var idOfTab = $(target.children[0]).attr('href');
	$("#tabs").tabs("select", idOfTab);
})

/* Считаем и фиксируем ширину панели вывода справа от табов*/
//var widthOfUiTabs = $("#tabs ul").outerWidth(true);
//$(".ui-tabs-vertical .ui-tabs-panel").css('margin-left', widthOfUiTabs+20);
