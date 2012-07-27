/**
 * @author dimau
 */

/* Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера */
window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')

// Активируем кнопки через jQuery UI
$(function() {
	$("button, a.button").button();
});

/* Инициализируем отображение вкладок при помощи jQuery UI */
$(function() {
	$("#tabs").tabs();
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

