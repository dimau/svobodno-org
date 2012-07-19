/**
 * @author dimau
 */

/* Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера */
window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')

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

/* Как только будет загружен API и готов DOM, выполняем инициализацию */
ymaps.ready(init);

function init() {
	// Создание экземпляра карты и его привязка к контейнеру с
	// заданным id ("map")
	var map = new ymaps.Map('map', {
		// При инициализации карты, обязательно нужно указать
		// ее центр и коэффициент масштабирования
		center : [56.829748, 60.617435], // Екатеринбург
		zoom : 10,
		// Включим поведения по умолчанию (default) и,
		// дополнительно, масштабирование колесом мыши.
		// дополнительно включаем измеритель расстояний по клику левой кнопки мыши
		behaviors : ['default', 'scrollZoom', 'ruler']
	});

	/***** Добавляем элементы управления на карту *****/
	// Для добавления элемента управления на карту используется поле controls, ссылающееся на
	// коллекцию элементов управления картой. Добавление элемента в коллекцию производится с помощью метода add().
	// В метод add можно передать строковый идентификатор элемента управления и его параметры.
	// Список типов карты
	map.controls.add('typeSelector');
	// Кнопка изменения масштаба - компактный вариант
	// Расположим её ниже и левее левого верхнего угла
	map.controls.add('smallZoomControl', {
		left : 5,
		top : 55
	});
	// Стандартный набор кнопок
	map.controls.add('mapTools');

	/***** Рисуем на карте маркер объекта недвижимости, соответствующего запросу *****/
	placeMarkers();

	$('#tabs').bind('tabsshow', reDrawMap);

	/***** Функция перестроения карты - используется при изменении размеров блока *****/
	function reDrawMap() {
		//map.setCenter([56.829748, 60.617435]);
		map.container.fitToViewport();
	}

	function placeMarkers() {
			
			// Создаем метку на основе координат - координаты вставляются на сервере с помощью php
			myPlacemark = new ymaps.Placemark([56.825483, 60.57357], {
                    // Свойства
                    //iconContent: 'Щелкни по мне',
                    balloonContentHeader: 'улица Гурзуфская 38',
                    balloonContentBody: '<img class="miniImg"><img class="miniImg"><img class="miniImg"><br>Квартира<br>Стоимость в месяц: 15000 + к. у. от 1500 до 2500 руб.<br> + <a href="#">единовременная комиссия 3000 руб. (40%) собственнику</a><br>Количество комнат: 2, смежные<br>Площадь: 22.4/34<br>Этаж: 3 из 10<br>Срок сдачи: долгосрочно<br>Мебель: есть<br>Район: Центр<br>Телефон собственника: 89221431615, Алексей Иванович',
                    balloonContentFooter: '<div style="width:100%;"><a>Подробнее</a><img alt="Значок избранного или не избранного" style="border: 1px solid black; float:right; width:10px; height:10px;"></div>'
                });
			
			// Добавляем метку на карту
			map.geoObjects.add(myPlacemark);
	}

}

