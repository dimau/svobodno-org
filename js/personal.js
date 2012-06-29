/**
 * @author dimau
 */

/* Если jQuery с сервера Google недоступна, то загружаем с моего локального сервера */
window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')

/* Инициализируем отображение вкладок при помощи jQuery UI */
$(function() {
	$("#tabs").tabs();
});

// Активируем кнопки "Новое объявление" через jQuery UI - добавляем пиктограммку плюсика в кружочке
	$(function() {
		$("button").button({
            icons: {
                primary: "ui-icon-circle-plus"
            }
        });
	});

/* Как только будет загружен API и готов DOM, выполняем инициализацию карты от Яндекса*/
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
	$('#addressForm').submit(function() {
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
		});
	});

	// Чтобы карта отображалась при открытии вкладки, ее нужно перестраивать по событию - открытие вкладки
	$('#tabs').bind('tabsshow', function(event, ui) {
		map.setCenter([56.829748, 60.617435]);
		map.container.fitToViewport();
	});
}
