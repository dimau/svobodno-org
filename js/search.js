/**
 * @author dimau
 */

/* Инициализируем отображение вкладок при помощи jQuery UI */
$(function() {
	$("#tabs").tabs();
});

// Активируем кнопки "Найти" через jQuery UI
$(function() {
	$("button").button({
	});
});


/* Считаем высоту видимой части экрана - чтобы задать ее высоте блока с картой */
$('#map').css('height', document.documentElement.clientHeight + 'px');

/* Навешиваем обработчик на прокрутку экрана с целью зафиксировать карту и заголовок таблицы в случае достижения ими верха страницы */
var map = document.getElementById("map");
var mapWrapper = document.getElementById("resultOnSearchPage");

window.onscroll = function() {
	// Если экран опустился ниже верхней границы карты, но карта не дошла до футера, то fixedTopBlock
	if (getPageScroll().top <= getCoords(mapWrapper).top) {
		$(map).removeClass('fixedTopBlock');
		$(map).removeClass('absoluteBottomBlock');
	} else {
		if (getPageScroll().top + map.offsetHeight >= getCoords(mapWrapper).top + mapWrapper.offsetHeight) {
			$(map).addClass('absoluteBottomBlock');
			$(map).removeClass('fixedTopBlock');
		} else {
			$(map).addClass('fixedTopBlock');
			$(map).removeClass('absoluteBottomBlock');
		}
	}
};

/* Как только будет загружен API и готов DOM, выполняем инициализацию карты*/
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

	/***** Рисуем на карте маркеры объектов недвижимости, соответствующих запросу *****/
	placeMarkers();

	$('#expandMap').bind('click', reDrawMap);
	$('#listPlusMap').bind('click', reDrawMap);

	/***** Функция перестроения карты - используется при изменении размеров блока *****/
	function reDrawMap() {
		//map.setCenter([56.829748, 60.617435]);
		map.container.fitToViewport();
	}

	function placeMarkers() {
		var addressArray = getElementsByClass('realtyObjectAddress', document.getElementById("fullParametersListOfRealtyObjects"));
		for (var i = 0; i < addressArray.length; i++) {
			// Получаем координаты очередного объекта недвижимости из атрибутов html объекта
			var realtyObjCoordX = $(addressArray[i]).attr('coordX');
			var realtyObjCoordY = $(addressArray[i]).attr('coordY');

			// Создаем метку на основе координат
			myPlacemark = new ymaps.Placemark([realtyObjCoordX, realtyObjCoordY], {
				// Свойства
				//iconContent: 'Щелкни по мне',
				balloonContentHeader : 'улица Сибирский тракт 50 летия 107',
				balloonContentBody : '<img class="miniImg"><img class="miniImg"><img class="miniImg"><br>Квартира<br>Стоимость в месяц: 15000 + к. у. от 1500 до 2500 руб.<br> + <a href="#">единовременная комиссия 3000 руб. (40%) собственнику</a><br>Количество комнат: 2, смежные<br>Площадь: 22.4/34<br>Этаж: 3 из 10<br>Срок сдачи: долгосрочно<br>Мебель: есть<br>Район: Центр<br>Телефон собственника: 89221431615, Алексей Иванович',
				balloonContentFooter : '<div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><img alt="Значок избранного или не избранного" style="border: 1px solid black; float:right; width:10px; height:10px;"></div>'
			});

			// Добавляем метку на карту
			map.geoObjects.add(myPlacemark);
		}
	}

}

// Подгонка размера правого блока параметров (районы) расширенного поиска под размер левого блока параметров. 19 пикселей - на padding у fieldset
document.getElementById('rightBlockOfSearchParameters').style.height = document.getElementById('leftBlockOfSearchParameters').offsetHeight - 22 + 'px';

/* Событие клика по ссылке развернуть список*/
$('#expandList').on('click', function() {
	$('#shortListOfRealtyObjects').css('display', 'none');
	$('#map').css('display', 'none');
	$('#fullParametersListOfRealtyObjects').css('display', '');
	//$('#listPlusMap').css('display', '');
	//$('#expandMap').css('display', '');
	//$('#expandList').css('display', 'none');
	return false;
});

/* Событие клика по ссылке список + карта*/
$('#listPlusMap').on('click', function() {
	$('#shortListOfRealtyObjects').css('display', 'block');
	$('#map').css('display', 'block');
	$('#map').css('width', '49%');
	$('#fullParametersListOfRealtyObjects').css('display', 'none');
	//$('#expandList').css('display', '');
	//$('#expandMap').css('display', '');
	//$('#listPlusMap').css('display', 'none');
	return false;
});

/* Событие клика по ссылке развернуть карту*/
$('#expandMap').on('click', function() {
	$('#shortListOfRealtyObjects').css('display', 'none');
	$('#map').css('display', '');
	$('#map').css('width', '100%');
	$('#fullParametersListOfRealtyObjects').css('display', 'none');
	//$('#expandList').css('display', '');
	//$('#listPlusMap').css('display', '');
	//$('#expandMap').css('display', 'none');
	return false;
});

