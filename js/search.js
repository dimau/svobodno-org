/**
 * @author dimau
 */

/* Навешиваем обработчик на переключение вкладок с режимами поиска */
$('#tabs').bind('tabsshow', function(event, ui) {
    newTabId = ui.panel.id; // Определяем идентификатор вновь открытой вкладки
    if (newTabId == "tabs-1") {
        // Переносим тип объекта
        $("#typeOfObjectFast").val($("#typeOfObject").val());

        // Так как между районами при расширенном поиске и районом при быстром поиске невозможно построить взаимнооднозначную конвертацию, не будем этого делать, дабы не запутать пользователя

        // Переносим стоимости
        $("#minCostFast").val($("#minCost").val());
        $("#maxCostFast").val($("#maxCost").val());
    }
    if (newTabId == "tabs-2") {
        // Переносим тип объекта
        $("#typeOfObject").val($("#typeOfObjectFast").val());

        // Переносим стоимости
        $("#minCost").val($("#minCostFast").val());
        $("#maxCost").val($("#maxCostFast").val());
    }
});

// Подгонка размера правого блока параметров (районы) расширенного поиска под размер левого блока параметров. 19 пикселей - на padding у fieldset
document.getElementById('rightBlockOfSearchParameters').style.height = document.getElementById('leftBlockOfSearchParameters').offsetHeight - 22 + 'px';

/* Считаем высоту видимой части экрана - чтобы задать ее высоте блока с картой */
$('#map').css('height', document.documentElement.clientHeight + 'px');
$('#resultOnSearchPage').css('min-height', document.documentElement.clientHeight + 'px');

/* Навешиваем обработчик на прокрутку экрана с целью зафиксировать карту и заголовок таблицы в случае достижения ими верха страницы */
var map = document.getElementById("map");
var mapWrapper = document.getElementById("resultOnSearchPage");

window.onscroll = function() {
	// Если экран опустился ниже верхней границы карты, но карта не дошла до футера, то fixedTopBlock
	if (getPageScroll().top <= getCoords(mapWrapper).top) {
		$(map).css('top', 0 + 'px');
	} else {
		if (getPageScroll().top + map.offsetHeight >= getCoords(mapWrapper).top + mapWrapper.offsetHeight) {
			$(map).css('top', 'auto');
			$(map).css('bottom', 0 + 'px');
		} else {
			$(map).css('top', getPageScroll().top - getCoords(mapWrapper).top + 'px');
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
		zoom : 11,
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
		var realtyObjects = getElementsByClass('realtyObject', document.getElementById("shortListOfRealtyObjects"));

		for (var i = 0; i < realtyObjects.length; i++) {
			// Получаем описание и координаты очередного объекта недвижимости из атрибутов html объекта
			var balloonContentBodyVar = $(realtyObjects[i]).attr('balloonContentBody');
			var realtyObjCoordX = $(realtyObjects[i]).attr('coordX');
			var realtyObjCoordY = $(realtyObjects[i]).attr('coordY');

			// Создаем метку на основе координат
			myPlacemark = new ymaps.Placemark([realtyObjCoordX, realtyObjCoordY], {
				//iconContent: 'Щелкни по мне',
				//balloonContentHeader : 
				balloonContentBody : balloonContentBodyVar,
				/*balloonContentFooter : */
			});

			// Добавляем метку на карту
			map.geoObjects.add(myPlacemark);
		}
	}

	/* Вешаем обработчик на клик по строчке краткого списка - чтобы отобразить инфу в виде баллуна на карте */
	$('#shortListOfRealtyObjects').on('click', function(event) {
		var target = event.target;
		
		if (target.nodeName == 'A' && $(target).hasClass('linkToDescription')) {
			var linkToDescription = $(target).attr('href');
			window.open(linkToDescription);
			return false;
		}

		while (target != this) {// пока target не поднялся до уровня table #shortListOfRealtyObjects ищем tr
			if (target.nodeName == 'TR' && $(target).hasClass('realtyObject')) {

				var balloonContentBodyVar = $(target).attr('balloonContentBody');
				var realtyObjCoordX = $(target).attr('coordX');
				var realtyObjCoordY = $(target).attr('coordY');

				map.balloon.open(
				// Позиция балуна
				[realtyObjCoordX, realtyObjCoordY], {
					// Свойства балуна
					contentBody : balloonContentBodyVar,
				});

				return false;
			}

			target = target.parentNode;
		}
	})
}

/* Навешиваем обработчик клика на подробный список объектов недвижимости в результатах выполнения запроса */
$('#fullParametersListOfRealtyObjects').on('click', function(event) {
		var target = event.target;

		while (target != this) {// пока target не поднялся до уровня table #fullParametersListOfRealtyObjects ищем tr
			if (target.nodeName == 'TR' && $(target).hasClass('realtyObject')) {

				var linkToDescription = $(target).attr('linkToDescription');
				window.open(linkToDescription);

				return false;
			}

			target = target.parentNode;
		}
})

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
    $('#shortListOfRealtyObjects').css('display', '');
    $('#map').css('display', '');
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

