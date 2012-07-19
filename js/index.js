/**
 * @author dimau
 */

/* Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера */
window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')

/* Рисуем аккордеон преимуществ */
// Активиуем аккордеон, установим возможность сворачиваться одновременно всем вкладкам, установим параметр, который будет позволять высоте вкладки автоматически подстраиваться под размер содержимого. При запуске аккордеона закроем все вкладки
$(function() {
	$(".accordion").accordion({
		collapsible : true,
		autoHeight : false
	});
	$(".accordion").accordion("activate", false);
});

// Активируем кнопки через jQuery UI
$(function() {
	$("button").button();
});

/* По кнопке Я - Собственник переходим на страницу описания порядка действий для собственников*/
$("#heIsOwner").on('click', function() {
	//window.location.assign("http://127.0.0.1:8020/HC/forowner.html");
	window.open('forowner.html');

});

/* Переинициализируем функцию getElementsByClassName для работы во всех браузерах*/
if (document.getElementsByClassName) {
	getElementsByClass = function(classList, node) {
		return (node || document).getElementsByClassName(classList)
	}
} else {
	getElementsByClass = function(classList, node) {
		var node = node || document, list = node.getElementsByTagName('*'), length = list.length, classArray = classList.split(/\s+/), classes = classArray.length, result = [], i, j
		for ( i = 0; i < length; i++) {
			for ( j = 0; j < classes; j++) {
				if (list[i].className.search('\\b' + classArray[j] + '\\b') != -1) {
					result.push(list[i])
					break
				}
			}
		}
		return result
	}
}

