/* Считаем высоту видимой части экрана - чтобы задать ее высоте блока с картой */
$('#map').css('height', document.documentElement.clientHeight + 'px');
$('#resultOnSearchPage').css('min-height', document.documentElement.clientHeight + 'px');

/* Навешиваем обработчик на прокрутку экрана с целью зафиксировать карту и заголовок таблицы в случае достижения ими верха страницы */
var map = document.getElementById("map");
var mapWrapper = document.getElementById("resultOnSearchPage");

window.onscroll = function () {
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
        center:[56.829748, 60.617435], // Екатеринбург
        zoom:11,
        // Включим поведения по умолчанию (default) и,
        // дополнительно, масштабирование колесом мыши.
        // дополнительно включаем измеритель расстояний по клику левой кнопки мыши
        behaviors:['default', 'scrollZoom', 'ruler']
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
        left:5,
        top:55
    });
    // Стандартный набор кнопок
    map.controls.add('mapTools');

    /***** Рисуем на карте маркеры объектов недвижимости, соответствующих запросу *****/
    placeMarkers();

    // Перестроение карты при различных событиях
    $('#expandMap').bind('click', reDrawMap);
    $('#listPlusMap').bind('click', reDrawMap);
    // Чтобы карта отображалась при открытии вкладки (Избранное в Личном кабинете), ее нужно перестраивать по событию - открытие вкладки
    if($("#tabs #map").length) {
        $('#tabs').bind('tabsshow', function (event, ui) {
            map.setCenter([56.829748, 60.617435]);
            map.container.fitToViewport();
        });
    }

    /***** Функция перестроения карты - используется при изменении размеров блока *****/
    function reDrawMap() {
        //map.setCenter([56.829748, 60.617435]);
        map.container.fitToViewport();
    }

    function placeMarkers() {
        // Получаем массив объектов, каждый из которых соответствует одному объявлению, в свою очередь соответствующему поисковому запросу пользователя. Массив включает в себя ВСЕ объекты из БД, которые соответствуют запросу пользователя
        var realtyBalloons = getElementsByClass('balloonBlock', document.getElementById("allBalloons"));

        // Создаем кластеризатор. Который будет объединять в 1 метку близко расположенные метки и будет масштабироваться по клику
        cluster = new ymaps.Clusterer();
        // Задаем размер ячейки кластера в пикселях. Чем больше, тем при большем расстояние между друг другом метки будут объединяться в одну
        cluster.options.set({
            gridSize: 40
        });

        // Инициализируем массив меток, которые нужно добавить на карту
        placemarks = [];

        for (var i = 0; i < realtyBalloons.length; i++) {
            // Получаем описание и координаты очередного объекта недвижимости из атрибутов html объекта
            //var balloonContentBodyVar = $(realtyBalloons[i]).html();
            var realtyObjCoordX = $(realtyBalloons[i]).attr('coordX');
            var realtyObjCoordY = $(realtyBalloons[i]).attr('coordY');

            // Создаем метку на основе координат
            myPlacemark = new ymaps.Placemark([realtyObjCoordX, realtyObjCoordY], {
                //iconContent: 'Щелкни по мне',
                //balloonContentHeader :
                //balloonContentBody: balloonContentBodyVar
                /*balloonContentFooter : */
            });
            // Добавляем полученную метку в коллекцию. Перед этим можно добавить проверку на удачность создания метки, чтобы всю страницу не запароть из-за одной косячной метки
            placemarks[i] = myPlacemark;

            // Добавляем метку на карту
            //map.geoObjects.add(myPlacemark);
        }

        // Добавляем собранную коллекцию меток в кластер и на карту
        cluster.add(placemarks);
        map.geoObjects.add(cluster);
    }

    /* Вешаем обработчик на клик по строчке краткого списка - чтобы отобразить инфу в виде баллуна на карте */
    $("tr.realtyObject", "#shortListOfRealtyObjects").on('click', function (event) {
        var target = event.target;

        var propertyId = $(this).attr('propertyId');
                var balloonContentBodyVar = $("#allBalloons .balloonBlock[propertyId='" + propertyId + "']").html();
                var realtyObjCoordX = $("#allBalloons .balloonBlock[propertyId='" + propertyId + "']").attr('coordX');
                var realtyObjCoordY = $("#allBalloons .balloonBlock[propertyId='" + propertyId + "']").attr('coordY');

                map.balloon.open(
                    // Позиция балуна
                    [realtyObjCoordX, realtyObjCoordY], {
                        // Свойства балуна
                        contentBody: balloonContentBodyVar
                    });

                return true; // чтобы дать возможность отработать и другим обработчикам клика (например, для добавления/удаления в избранное, просмотра объявления подробнее)
    });

}

/* Навешиваем обработчик клика на подробный список объектов недвижимости в результатах выполнения запроса */
$('#fullParametersListOfRealtyObjects').on('click', function (event) {
    var target = event.target;

    while (target != this) {// пока target не поднялся до уровня table #fullParametersListOfRealtyObjects ищем tr
        if (target.nodeName == 'TR' && $(target).hasClass('realtyObject')) {

            var linkToDescription = $(target).attr('linkToDescription');
            window.open(linkToDescription);

            return false;
        }

        target = target.parentNode;
    }
});

/* Устанавливаем режим просмотра объявлений по умолчанию */
$('#expandList a').removeClass('inUse');
$('#listPlusMap a').addClass('inUse');
$('#expandMap a').removeClass('inUse');

/* Событие клика по ссылке развернуть список */
$('#expandList').on('click', function () {
    $('#shortListOfRealtyObjects').css('display', 'none');
    $('#map').css('display', 'none');
    $('#fullParametersListOfRealtyObjects').css('display', '');
    $('#expandList a').addClass('inUse');
    $('#listPlusMap a').removeClass('inUse');
    $('#expandMap a').removeClass('inUse');
    return false;
});

/* Событие клика по ссылке список + карта*/
$('#listPlusMap').on('click', function () {
    $('#shortListOfRealtyObjects').css('display', '');
    $('#map').css('display', '');
    $('#map').css('width', '49%');
    $('#fullParametersListOfRealtyObjects').css('display', 'none');
    $('#expandList a').removeClass('inUse');
    $('#listPlusMap a').addClass('inUse');
    $('#expandMap a').removeClass('inUse');
    return false;
});

/* Событие клика по ссылке развернуть карту*/
$('#expandMap').on('click', function () {
    $('#shortListOfRealtyObjects').css('display', 'none');
    $('#map').css('display', '');
    $('#map').css('width', '100%');
    $('#fullParametersListOfRealtyObjects').css('display', 'none');
    $('#expandList a').removeClass('inUse');
    $('#listPlusMap a').removeClass('inUse');
    $('#expandMap a').addClass('inUse');
    return false;
});