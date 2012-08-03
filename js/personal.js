/**
 * @author dimau
 */

// Активируем кнопки "Новое объявление" через jQuery UI - добавляем пиктограммку плюсика в кружочке
$(function() {
	$("button#newAdvertButton").button({
		icons : {
			primary : "ui-icon-circle-plus"
		}
	});
});

// Навешиваем обработчик на клик на кнопке нового объявления
$("button#newAdvertButton").on('click', clickNewAdvertButton);

function clickNewAdvertButton() {
	$("#modalWindowNewAdvert").dialog("open");
	return false;
}

// Готовим форму для модального окна формирования нового объявления
$("#modalWindowNewAdvert").dialog({
	autoOpen : false,
	width : 850, //ширина
	minWidth : 200,
	//height: 300,            //высота
	title : "Новое объявление", //тайтл, заголовок окна
	position : 'center', //месторасположение окна [отступ слева,отступ сверху]
	modal : true //булева переменная если она равно true -  то окно модальное, false -  то нет
});

// Активируем кнопку сохранения параметров нового объявления
$(function() {
	$("button.saveAdvertButton").button({
		icons : {
			primary : "ui-icon-disk"
		}
	});
});

/* Как только будет загружен API и готов DOM, выполняем инициализацию карты от Яндекса*/
ymaps.ready(init);

function init() {
	// Создание экземпляра карты для Нового объявления и его привязка к контейнеру с
	// заданным id ("mapForNewAdvert")
	var map = new ymaps.Map('mapForNewAdvert', {
		// При инициализации карты, обязательно нужно указать
		// ее центр и коэффициент масштабирования
		center : [56.829748, 60.617435], // Екатеринбург
		zoom : 10,
		// Включим поведения по умолчанию (default) и,
		// дополнительно, масштабирование колесом мыши.
		// дополнительно включаем измеритель расстояний по клику левой кнопки мыши
		behaviors : ['default', 'scrollZoom']
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
	// Стандартный набор кнопок, кроме линейки
	var myToolbar = new ymaps.control.MapTools(['drag', 'magnifier']);
	map.controls.add(myToolbar);

	/***** Настраиваем возможность указания адреса в форме регистрации *****/

	// Создаем пустой массив маркеров - в него будет класть маркер, соответствующий адресу, введеному пользователем
	searchObjectCollection = new ymaps.GeoObjectCollection();

	// При вводе адреса в строку и нажатии энтера ставим метку на карте города
	$('#checkAddressButton').on('click', function() {
		// Записываем в переменную что конкретно ввел пользователь. Поле для ввода адреса располагается первым в форме!
		var search_query = $('input:first').val();

		// Получаем набор координат объектов, соответствующих строке пользователя на карте -
		// Ограничиваем набор только первым объектом и поиск объекта ограничиваем только пригородом Екатеринбурга (параметр boundedBy [юго-западный угол, северов-восточный угол границы поиска])
		var geoObjectsOfsearch_query = ymaps.geocode(search_query, {
			results : 1,
			boundedBy : [[55, 59], [58, 62]],
			strictBounds : true
		});

		geoObjectsOfsearch_query.then(function(res) {
			searchObjectCollection.removeAll();
			searchObjectCollection = res.geoObjects;
			map.geoObjects.add(searchObjectCollection);

			// В центр карты поместим полученный объект
			var point = res.geoObjects.get(0);
			map.setCenter(point.geometry.getCoordinates(), 16);

			// Указанный пользователем адрес в строке ввода сформулируем в соответствии с базой Яндекса
			// Поле для ввода адреса располагается первым в форме!
			document.getElementById('addressTextBox').value = point.properties.get('name');
		},
		// Обработка ошибки
		function(error) {
			alert("Возникла ошибка при работе с картой: " + error.message);
		});

		// Указанные координату не отправляются на сервер
		return false;
	});

	// Если пользователь кликнит левой кнопкой по дому - то адресная строка заполнится автоматически
	map.events.add('click', function(e) {
		var coords = e.get('coordPosition');

		// Отправим запрос на геокодирование, берем только 1 результат - это будетт название улицы и номер дома (так у них в Яндексе настроено).
		ymaps.geocode(coords, {
			results : 1
		}).then(function(res) {
			var names = [];

			// Переберём все найденные результаты и
			// запишем имена найденный объектов в массив names.
			// Этот код остался от того момента, когда geocode был ограничен не одним результатом, а несколькими, возможно, для повышения эффективности его можно сократить
			res.geoObjects.each(function(obj) {
				names.push(obj.properties.get('name'));
			});

			// Если на карте уже есть метки - удаляем, записываем новую метку в точку, по координатам которой запрашивали обратное геокодирование
			searchObjectCollection.removeAll();
			searchObjectCollection = res.geoObjects;
			map.geoObjects.add(searchObjectCollection);

			// В центр карты поместим полученный объект
			var point = res.geoObjects.get(0);
			map.setCenter(point.geometry.getCoordinates(), 16);

			// Укажем адрес данного объекта в строке ввода
			// Поле для ввода адреса располагается первым в форме!
			document.getElementById('addressTextBox').value = point.properties.get('name');

			// Координаты объекта для запоминания на сервер - для дальнейшего однозначного отображения метки на картах поиска
			var coordX = point.geometry.getCoordinates()[0];
			var coordY = point.geometry.getCoordinates()[1];
		});
	});

	// Чтобы карта отображалась при открытии вкладки, ее нужно перестраивать по событию - открытие вкладки
	// Чтобы карта отображалась при открытии вкладки - нужно $('#tabs').bind('tabsshow', function(event, ui) {
	$('#newAdvertButton').bind('click', function(event, ui) {
		map.setCenter([56.829748, 60.617435]);
		map.container.fitToViewport();
	});
}

// Подгонка размера правого блока параметров (районы) вкладки Поиск под размер левого блока параметров. 19 пикселей - на padding у fieldset
document.getElementById('rightBlockOfSearchParameters').style.height = document.getElementById('leftBlockOfSearchParameters').offsetHeight - 22 + 'px';

/* Сценарии для появления блока с подробным описанием сожителей */
$("#withWho").on('change', function(event) {
	if ($("#withWho").attr('value') != 1) {
		$("#withWhoDescription").css('display', '');
	} else {
		$("#withWhoDescription").css('display', 'none');
	}
});

/* Сценарии для появления блока с подробным описанием детей */
$("#children").on('change', function(event) {
	if ($("#children").attr('value') != 0) {
		$("#childrenDescription").css('display', '');
	} else {
		$("#childrenDescription").css('display', 'none');
	}
});

/* Сценарии для появления блока с подробным описанием животных */
$("#animals").on('change', function(event) {
	if ($("#animals").attr('value') != 0) {
		$("#animalsDescription").css('display', '');
	} else {
		$("#animalsDescription").css('display', 'none');
	}
});

/* Переключение на вкладке Профиль из режима просмотра в режим редактирования и обратно */
$('#tabs-1 #notEditingProfileParametersBlock .setOfInstructions a').on('click', function() {
	$("#notEditingProfileParametersBlock").css('display', 'none');
	$("#editingProfileParametersBlock").css('display', '');
});

$('#editingProfileParametersBlock').on('submit', function() {
	$("#notEditingProfileParametersBlock").css('display', '');
	$("#editingProfileParametersBlock").css('display', 'none');
});

/* Переключение на вкладке поиск из режима просмотра в режим редактирования и обратно */
$('#tabs-4 #notEditingSearchParametersBlock .setOfInstructions a').on('click', function() {
	$("#notEditingSearchParametersBlock").css('display', 'none');
	$("#extendedSearchParametersBlock").css('display', '');
});

$('#extendedSearchParametersBlock').on('submit', function() {
	$("#notEditingSearchParametersBlock").css('display', '');
	$("#extendedSearchParametersBlock").css('display', 'none');
});

/* =============================================================================
   Вкладка Избранное
   ========================================================================== */

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
ymaps.ready(init2);

function init2() {
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
	
	// Чтобы карта отображалась при открытии вкладки, ее нужно перестраивать по событию - открытие вкладки
	// Чтобы карта отображалась при открытии вкладки - нужно $('#tabs').bind('tabsshow', function(event, ui) {
	$('#tabs').bind('tabsshow', function(event, ui) {
		map.setCenter([56.829748, 60.617435]);
		map.container.fitToViewport();
	});
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
