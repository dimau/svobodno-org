/**********************************************************************************
 * Высота карты == высоте окошка браузера
 **********************************************************************************/

$(document).ready(changeMapSize);
$(window).resize(changeMapSize);
$('#tabs').bind('tabsshow', changeMapSize);

function changeMapSize() {

    // Подстраиваем высоту карты под высоту окна браузера document.documentElement.clientHeight + 'px'
    $('#map').height($(window).height());
    $('#resultOnSearchPage').css('min-height', $(window).height() + 'px');

    // Значения этих переменных пригодятся, когда карта получит положение fixed и ее размеры будут определятся уже относительно окна браузера. При этом размеры и положение карты должны остаться теми же самыми.
    // Исходим из того, что положение и ширина карты соотносятся с таблицей с краткими сведениями об объектах
    mapWidth = $("#shortListOfRealtyObjects").width();
    mapLeftCoord = $("#shortListOfRealtyObjects").offset().left + mapWidth;
}

/**********************************************************************************
 * Зафиксируем карту на всю высоту окошка браузера при прокрутке экрана
 **********************************************************************************/

/* Навешиваем обработчик на прокрутку экрана с целью зафиксировать карту и заголовок таблицы в случае достижения ими верха страницы */

var map = document.getElementById("map");
var mapWrapper = document.getElementById("resultOnSearchPage");
var mapWidth = 0;
var mapLeftCoord = 0;

$(window).scroll(function() {

    if (!$("#listPlusMap a").hasClass("inUse")) return true;

// Если экран опустился ниже верхней границы карты, но карта не дошла до футера, то fixedTopBlock

if (getPageScroll().top <= getCoords(mapWrapper).top) { // Если мы смотрим заголовок страницы

    $(map).removeClass('fixedTopBlock');
    $(map).removeClass('absoluteBottomBlock');

    // Возвращаем исходные значения
    $(map).css({'width': '', 'left': ''});

} else { // Если мы проматали ниже заголовка страницы и верх карты достиг верха экрана

    if (getPageScroll().top + map.offsetHeight >= getCoords(mapWrapper).top + mapWrapper.offsetHeight) { // Если мы дошли до подвала страницы

    $(map).addClass('absoluteBottomBlock');
    $(map).removeClass('fixedTopBlock');

    // Возвращаем исходные значения
    $(map).css({'width': '', 'left': ''});

    } else { // Если мы просматриваем середину списка - фиксируем карту на экране

        $(map).addClass('fixedTopBlock');
        $(map).removeClass('absoluteBottomBlock');

        // Важно оставить карту на экране в том же местоположении и той же ширины, что она была до прокрутки
        $(map).css({'width': mapWidth, 'left': mapLeftCoord});

    }

    }

    return true;
});


/*var map = document.getElementById("map");
var mapWrapper = document.getElementById("resultOnSearchPage");
$(window).scroll(function () {
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
});*/

/**********************************************************************************
 * Подгрузка новых 20-ти объектов при прокрутке экрана со списком
 **********************************************************************************/

// Инициализируем переменную, которая при выполнении загрузки новых объявлений не дает повторно срабатывать событию прокрутки. В противном случае одни и те же объекты могут подгрузиться 2 раза.
var blockOfScrollHandler = false;

function getNextRealtyObjects(lastRealtyObjectsId, lastNumber) {

    // Блокируем обработку прокрутки при загрузке данных с сервера
    blockOfScrollHandler = true;

    // Инициализируем массив для сохранения id объектов, по которым нам нужно получить с сервера данные
    var propertyIdArr = new Array();

    // Формируем массив id объектов, по которым запросим данные с сервера
    $("#allBalloons .balloonBlock[propertyId='" + lastRealtyObjectsId + "']").nextAll().slice(0, 20).each(function () {
        propertyIdArr.push($(this).attr('propertyId'));
    });

    // Запускаем вертушку, чтобы показать пользователю, что новые данные подгружаются TODO: вертушка загрузки
    //$("#upBlock").css('display', 'block');

    jQuery.post("../lib/getSearchResultHTML.php", {"propertyId":propertyIdArr, "typeOperation":"FullData", "number":lastNumber}, function (data) {

        // Дополняем таблицы, содержащие списки с краткими и подробными объявлениями
        $("#shortListOfRealtyObjects tbody").append(data.matterOfShortList);
        $("#fullParametersListOfRealtyObjects tbody").append(data.matterOfFullParametersList);

        // Присваиваем полученный HTML соответствующим баллунам
        for (i = 0; i < propertyIdArr.length; i++) {
            $("#allBalloons .balloonBlock[propertyId='" + propertyIdArr[i] + "']").html(data.arrayOfBalloonList[propertyIdArr[i]]);
        }

        //$("#upBlock").css('display', 'none'); TODO: вертушка загрузки Снимаем вертушку - данные успешно подгружены

        /************* Активируем ColorBox для просмотра в модальном окне галереи фотографий по клику на миниатюре *************/
        // Это необходимо сделать для вновь загруженных объектов
        for (i = 0; i < propertyIdArr.length; i++) {

            /* Для представления результатов поиска в виде карты с баллунами */
            $("#allBalloons .balloonBlock[propertyId='" + propertyIdArr[i] + "'] .gallery").colorbox({ opacity: 0.7 , rel: currentFotoGalleryIndex, current: '№ {current} из {total}' });
            currentFotoGalleryIndex++;

            /* Для представления результатов поиска список + карта */
            $("#shortListOfRealtyObjects .realtyObject[propertyId='" + propertyIdArr[i] + "'] .gallery").colorbox({ opacity: 0.7 , rel: currentFotoGalleryIndex, current: '№ {current} из {total}' });
            currentFotoGalleryIndex++;

            /* Для представления результатов поиска в виде списка */
            $("#fullParametersListOfRealtyObjects .realtyObject[propertyId='" + propertyIdArr[i] + "'] .gallery").colorbox({ opacity: 0.7 , rel: currentFotoGalleryIndex, current: '№ {current} из {total}' });
            currentFotoGalleryIndex++;

        }

        // Разблокируем обработку прокрутки страницы для новых загрузок с сервера
        blockOfScrollHandler = false;

    }, 'json');

}

$(window).scroll(function () {

    // Если мы дожидаемся окончания прошлого запроса к серверу, то новые не отправляются
    if (blockOfScrollHandler) return true;

    // Если ни один из списков объектов недвижимости не является видимым (например, при просмотре пользователем результатов поиска в режиме карты), то и обрабатывать прокрутку экрана не нужно
    if ($("#shortListOfRealtyObjects").is(":hidden") && $("#fullParametersListOfRealtyObjects").is(":hidden")) return true;

    var screenHeight = $(window).height(); // Высота экрана пользователя
    var currentTopScroll = $(this).scrollTop(); // Текущая промотка экрана (количество пикселей, скрытое вверху страницы)
    // Текущая координата низа экрана относительно всего документа
    var currentScreenBottom = 0;
    currentScreenBottom = screenHeight + currentTopScroll;

    // Вычисляем координату низа списка с объявлениями относительно всего документа. Либо подробного, либо с минимумом сведений - в зависимости от того, какой режим отображения результатов поиска выбран
    if ($("#shortListOfRealtyObjects").is(":visible")) {
        var listOfRealtyObjectsBottom = $("#shortListOfRealtyObjects").height() + $("#shortListOfRealtyObjects").offset().top;
        var lastRealtyObjectsId = $("#shortListOfRealtyObjects tr:last").attr('propertyId');
        var lastNumber = $("#shortListOfRealtyObjects tr:last .numberOfRealtyObject").html();
    }
    if ($("#fullParametersListOfRealtyObjects").is(":visible")) {
        listOfRealtyObjectsBottom = $("#fullParametersListOfRealtyObjects").height() + $("#fullParametersListOfRealtyObjects").offset().top;
        lastRealtyObjectsId = $("#fullParametersListOfRealtyObjects tr:last").attr('propertyId');
        lastNumber = $("#fullParametersListOfRealtyObjects tr:last .numberOfRealtyObject").html();
    }

    // Сколько пикселей осталось промотать пользователю, чтобы достигнуть низа списка объектов
    var leftHeight = listOfRealtyObjectsBottom - currentScreenBottom;

    if (leftHeight < 400) { // Если до низа осталось меньше 400 пикселей - пытаемся подгрузить продолжение списка с сервера

        // Дошли до конца списка? Если последний в проматываемом списке элемент совпадает с последним в списке allBalloons (а значит мы не получим от сервера продолжения), то запрос к серверу не отправляется - весь список на экране!
        if ($("#allBalloons balloonBlock:last").attr('propertyId') == lastRealtyObjectsId) return true;

        getNextRealtyObjects(lastRealtyObjectsId, lastNumber);
    }

    return true;
});

/**********************************************************************************
 * Инициализация карты при загрузке страницы
 **********************************************************************************/

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
    if ($("#tabs #map").length) {
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
            gridSize:40
        });

        // Инициализируем массив меток, которые нужно добавить на карту
        placemarks = [];

        // Перебираем данные для всех баллунов и готовим соответствующие метки на карту
        for (var i = 0; i < realtyBalloons.length; i++) {

            // Получаем описание и координаты очередного объекта недвижимости из атрибутов html объекта
            //var balloonContentBodyVar = $(realtyBalloons[i]).html();
            var realtyObjCoordX = $(realtyBalloons[i]).attr('coordX');
            var realtyObjCoordY = $(realtyBalloons[i]).attr('coordY');
            var realtyObjId = $(realtyBalloons[i]).attr('propertyid');

            // Создаем метку на основе координат
            myPlacemark = new ymaps.Placemark([realtyObjCoordX, realtyObjCoordY], {
                propertyid:realtyObjId,
                balloonContentBody:'Загрузка данных...' // Текст для индикации процесса загрузки (будет заменен на контент когда данные загрузятся)
            });

            myPlacemark.events.add('click', onPlacemarkClick);

            // Добавляем полученную метку в коллекцию. Перед этим можно добавить проверку на удачность создания метки, чтобы всю страницу не запароть из-за одной косячной метки
            placemarks[i] = myPlacemark;

        }

        // Добавляем собранную коллекцию меток в кластер и на карту
        cluster.add(placemarks);
        map.geoObjects.add(cluster);

        // Обработчик клика по метке на Яндекс карте
        function onPlacemarkClick(event) {

            // Получаем параметры метки, в том числе id объекта недвижимости.
            var placemark = event.get('target'),
                map = placemark.getMap(), // Ссылка на карту.
                //bounds = map.getBounds(), // Область показа карты.
                propertyid = placemark.properties.get('propertyid'); // Получаем данные для запроса из свойств метки.

            // Пытаемся найти контент для баллуна на нашей текущей странице в разделе AllBalloons
            balloonHTML = $("#allBalloons .balloonBlock[propertyId='" + propertyid + "']").html();

            // Проверяем - есть ли на странице данные для формирования баллуна для этого объекта, если есть, формируем на основе их баллун
            if (balloonHTML) {

                // Обновляем поле "body" у properties метки
                placemark.properties.set('balloonContentBody', balloonHTML);

                // TODO: поправить: обработчик на клик по строчке краткого списка работает как надо - дает возможность открыть галерею фоток, а этот код дает возможность открыть только 1 фотку в галерее!
                // Берем только что сформированный HTML баллуна и навешиваем на фотографии галерею colorBox
                $("#map .fotosWrapper .gallery").removeClass('cboxElement').colorbox({ opacity: 0.7 , rel: currentFotoGalleryIndex, current: '№ {current} из {total}' });
                currentFotoGalleryIndex++;

            } else { // Если данные по этому объекту еще не были подгружены на страницу, то обращаемся к серверу

                // Обращаемся к серверу за HTML баллуна, передаем серверу propertyid - идентификатор объекта недвижимости
                jQuery.post("../lib/getSearchResultHTML.php", {"propertyId":new Array(propertyid), "typeOperation":"FullBalloons"}, function (data) {

                    balloonHTML = data.arrayOfBalloonList[propertyid];

                    // Обновляем поле "body" у properties метки
                    if (balloonHTML != "") placemark.properties.set('balloonContentBody', balloonHTML);

                    // Также в случае успеха, сохраняем данные по баллуну для данного объекта на странице с целью уменьшения количества запросов к серверу
                    $("#allBalloons .balloonBlock[propertyId='" + propertyid + "']").html(balloonHTML);

                    // TODO: поправить: обработчик на клик по строчке краткого списка работает как надо - дает возможность открыть галерею фоток, а этот код дает возможность открыть только 1 фотку в галерее!
                    // Берем только что сформированный HTML баллуна и навешиваем на фотографии галерею colorBox
                    $("#map .fotosWrapper .gallery").removeClass('cboxElement').colorbox({ opacity: 0.7 , rel: currentFotoGalleryIndex, current: '№ {current} из {total}' });
                    currentFotoGalleryIndex++;

                }, 'json');

            }

        }

    }


    /* Вешаем обработчик на клик по строчке краткого списка - чтобы отобразить инфу в виде баллуна на карте */
    $(document).on('click', "#shortListOfRealtyObjects tr.realtyObject", function (event) {
        var target = event.target;

        var propertyId = $(this).attr('propertyId');
        var balloonContentBodyVar = $("#allBalloons .balloonBlock[propertyId='" + propertyId + "']").html();
        var realtyObjCoordX = $("#allBalloons .balloonBlock[propertyId='" + propertyId + "']").attr('coordX');
        var realtyObjCoordY = $("#allBalloons .balloonBlock[propertyId='" + propertyId + "']").attr('coordY');

        map.balloon.open(
            // Позиция балуна
            [realtyObjCoordX, realtyObjCoordY], {
                // Свойства балуна
                contentBody:balloonContentBodyVar
            });

        // Берем только что сформированный HTML баллуна и навешиваем на фотографии галерею colorBox
        $("#map .fotosWrapper .gallery").removeClass('cboxElement').colorbox({ opacity: 0.7 , rel: currentFotoGalleryIndex, current: '№ {current} из {total}' });
        currentFotoGalleryIndex++;

        return true; // чтобы дать возможность отработать и другим обработчикам клика (например, для добавления/удаления в избранное, просмотра объявления подробнее)
    });

}

/**********************************************************************************
 * Переход на страницу с подробным описанием недвижимости по клику в режиме "только список"
 **********************************************************************************/

$(document).on('click', '#fullParametersListOfRealtyObjects .realtyObject', function (event) {

    // Открываем подробное описание объекта в новом окне
    var linkToDescription = $(this).attr('linkToDescription');
    window.open(linkToDescription);

    return true;

});

/**********************************************************************************
 * Устанавливаем режим просмотра результатов поиска по умолчанию, а также возможность его переключения
 **********************************************************************************/

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
    $('#map').css('width', '50%');
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