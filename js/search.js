/**
 * @author dimau
 */

function init() {
            // Создание экземпляра карты и его привязка к контейнеру с
            // заданным id ("map")
            var map = new ymaps.Map('map', {
                    // При инициализации карты, обязательно нужно указать
                    // ее центр и коэффициент масштабирования
                    center: [56.829748, 60.617435], // Екатеринбург
                    zoom: 10,
                    // Включим поведения по умолчанию (default) и,
                    // дополнительно, масштабирование колесом мыши.
                    // дополнительно включаем измеритель расстояний по клику левой кнопки мыши
                    behaviors: ['default', 'scrollZoom', 'ruler']
                });
            
        /***** Добавляем элементы управления на карту *****/        
            // Для добавления элемента управления на карту используется поле controls, ссылающееся на
            // коллекцию элементов управления картой. Добавление элемента в коллекцию производится с помощью метода add().
            // В метод add можно передать строковый идентификатор элемента управления и его параметры.
            // Список типов карты
            map.controls.add('typeSelector');
            // Кнопка изменения масштаба - компактный вариант
			// Расположим её ниже и левее левого верхнего угла
			map.controls.add('smallZoomControl', {left: 5, top: 55});
			// Стандартный набор кнопок
			map.controls.add('mapTools');
       
       /***** Рисуем на карте маркеры объектов недвижимости, соответствующих запросу *****/          
            placeMarkers();
}
        
function placeMarkers() {
 //var addressArray =	$(".realtyObjectAddress").text().split(' ');
 
// Поиск координат центра Нижнего Новгорода
            ymaps.geocode('улица Серафимы Дерябиной 43', { results: 1 }).then(function (res) {
                // Выбираем первый результат геокодирования
                var firstGeoObject = res.geoObjects.get(0);

                // Создаём карту.
                // Устанавливаем центр и коэффициент масштабирования.
                window.myMap = new ymaps.Map("map", {
                    center: firstGeoObject.geometry.getCoordinates(),
                    zoom: 11
                });

                // Поиск станций метро.
                // Делаем запрос на обратное геокодирование
                ymaps.geocode(myMap.getCenter(), {
                    // Ограничение типа искомых объектов - станции метро
                    kind: 'metro',
                    // Ищем в пределах области карты
                    boundedBy: myMap.getBounds(),
                    // Запрашиваем не более 20 результатов
                    results: 20
                }).then(function (res) {
                    // Задаем изображение для иконок меток
                    res.geoObjects.options.set('iconImageHref', 'http://upload.wikimedia.org/wikipedia/ru/4/4c/Metropoliten_NN.png');
                    // Добавляем полученную коллекцию на карту
                    myMap.geoObjects.add(res.geoObjects);
                });
            }, function (err) {
                // Если геокодирование не удалось,
                // сообщаем об ошибке
                alert(err.message);
            });

}

