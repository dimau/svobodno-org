/**
 * @author dimau
 */

/* Инициализируем отображение вкладок при помощи jQuery UI, делаем вкладки вертикальными */
$(function() {
	$("#tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	$( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
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


/* Скрытие и отображение вариантов работы: классический, улучшенный и идеальный */

$("#tarif1").on('click', function() {
    if ($('#text1').css('display') == 'none') $('#text1').css('display', 'block'); else $('#text1').css('display', 'none');
    return false;
});

$("#tarif2").on('click', function() {
    if ($('#text2').css('display') == 'none') $('#text2').css('display', 'block'); else $('#text2').css('display', 'none');
    return false;
});

$("#tarif3").on('click', function() {
    if ($('#text3').css('display') == 'none') $('#text3').css('display', 'block'); else $('#text3').css('display', 'none');
    return false;
});