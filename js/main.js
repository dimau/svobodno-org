/* Author:

*/

/* Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера */
window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')

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

/* Функция кроссбраузерно возвращает текущее значение прокрутки */
function getPageScroll() {
	if (window.pageXOffset != undefined) {
		return {
			left : pageXOffset,
			top : pageYOffset
		};
	}
	var html = document.documentElement;
	var body = document.body;
	var top = html.scrollTop || body && body.scrollTop || 0;
	top -= html.clientTop;
	var left = html.scrollLeft || body && body.scrollLeft || 0;
	left -= html.clientLeft;
	return {
		top : top,
		left : left
	};
}

/* Функция кроссбраузерно возвращает координаты левого верхнего угла элемента */
function getCoords(elem) {
	var box = elem.getBoundingClientRect();
	var body = document.body;
	var docEl = document.documentElement;
	var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
	var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;
	var clientTop = docEl.clientTop || body.clientTop || 0;
	var clientLeft = docEl.clientLeft || body.clientLeft || 0;
	var top = box.top + scrollTop - clientTop;
	var left = box.left + scrollLeft - clientLeft;
	return {
		top : Math.round(top),
		left : Math.round(left)
	};
}