/* Делаем красивые (равномерные) отступы внутри плашки меню */
function changeMenuSeparatorWidth() {
    // Выясняем ширину области меню
    var menuWidth = $(".menu").width();

    // Приводим ширину пунктов меню к естественному виду
    $(".menu .choice").each(function () {
        $(this).css("width", "");
    });

    // Считаем остаток ширины на сепараторы
    $(".menu .choice").each(function () {
        menuWidth = menuWidth - $(this).width();
    });

    var separatorWidth = 0;
    if ($(".menu .choice").length == 3) { // Отрабатываем в случае неавторизованного пользователя с 3 пунктами в меню
        separatorWidth = (menuWidth - 5) / 4;
    } else { // Отрабатываем в случае авторизованного пользователя, у которого больше 3 пунктов в меню
        separatorWidth = (menuWidth - 5) / 5;
    }

    // Применяем вычисленную ширину ко всем сепараторам
    $(".menu .separator").each(function () {
        $(this).width(separatorWidth);
    })
}
$(document).ready(changeMenuSeparatorWidth);
$(window).resize(changeMenuSeparatorWidth);

/* Инициализируем отображение вкладок при помощи jQuery UI */
$(function () {
    $("#tabs").tabs();
});

// Активиуем аккордеон, установим возможность сворачиваться одновременно всем вкладкам, установим параметр, который будет позволять высоте вкладки автоматически подстраиваться под размер содержимого. При запуске аккордеона закроем все вкладки
$(function () {
    $(".accordion").accordion({
        collapsible:true,
        autoHeight:false
    });
    $(".accordion").accordion("activate", false);
});

// Активируем кнопки через jQuery UI
$(function () {
    $("button, a.button, input.button").button();
});


/* Переинициализируем функцию getElementsByClassName для работы во всех браузерах*/
if (document.getElementsByClassName) {
    getElementsByClass = function (classList, node) {
        return (node || document).getElementsByClassName(classList)
    }
} else {
    getElementsByClass = function (classList, node) {
        var node = node || document, list = node.getElementsByTagName('*'), length = list.length, classArray = classList.split(/\s+/), classes = classArray.length, result = [], i, j
        for (i = 0; i < length; i++) {
            for (j = 0; j < classes; j++) {
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
            left:pageXOffset,
            top:pageYOffset
        };
    }
    var html = document.documentElement;
    var body = document.body;
    var top = html.scrollTop || body && body.scrollTop || 0;
    top -= html.clientTop;
    var left = html.scrollLeft || body && body.scrollLeft || 0;
    left -= html.clientLeft;
    return {
        top:top,
        left:left
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
        top:Math.round(top),
        left:Math.round(left)
    };
}

/**********************************************************************************
 * Функция для БЛОКИРОВКИ / РАЗБЛОКИРОВКИ ЭЛЕМЕНТОВ ВВОДА при изменении уже введенных значений.
 *
 * Пробегает все элементы и изменяет в соответствии с текущей ситуацией их доступность/недоступность для пользователя
 * Для использования необходимо подключить запуск функции к соответствующему селекту:
 * $("#typeOfObject").change(notavailability);
 *
 * Используется на странице регистрации пользователя
 **********************************************************************************/

function notavailability() {

    // Понимаем роль пользователя, так как некоторые поля обязательны для арендатора, но необязательны для собственника
    var userTypeTenant = $(".userType").attr('typeTenant') == "true";

    // Перебираем все элементы, доступность которых зависит от каких-либо условий
    $("[notavailability]").each(function () {
        // Получаем текущий элемент из перебираемых и набор условий его недоступности
        var currentElem = this;
        var notSelectorsOfElem = $(this).attr("notavailability");

        // Получаем массив, каждый элемент которого = условию недоступности
        var arrNotSelectorsOfElem = notSelectorsOfElem.split('&');

        // Презумпция доступности элемента, если одно из его условий недоступности выполнится ниже, то он станет недоступным
        $("select, input", currentElem).removeAttr("disabled");
        $(currentElem).css('color', '');
        $("select, input", currentElem).css("background-color", '');
        if (userTypeTenant) $(".itemRequired.typeTenantRequired", currentElem).text("*");

        // Проверяем верность каждого условия недоступности
        for (var i = 0; i < arrNotSelectorsOfElem.length; i++) {
            // Выделяем Селект условия недоступности и его значение, при котором условие выполняется и элемент должен стать недоступным
            var selectAndValue = arrNotSelectorsOfElem[i].split('_');
            var currentSelectId = selectAndValue[0];
            var currentNotSelectValue = selectAndValue[1];

            // Проверяем текущее значение селекта
            var currentSelectValue = $("#" + currentSelectId).val();
            var isCurrentSelectDisabled = $("#" + currentSelectId).attr("disabled");
            if (currentSelectValue == currentNotSelectValue || isCurrentSelectDisabled) { // Если текущее значение селекта совпало с тем значением, при котором данный элемент должен быть недоступен, либо селект, от значения которого зависит судьба данного недоступен, то выполняем скрытие элемента и его селектов
                $("select, input", currentElem).attr("disabled", "disabled");
                $(currentElem).css('color', '#e6e6e6');
                $("select, input", currentElem).css("background-color", '#e6e6e6');
                $(".itemRequired", currentElem).text("");
                break; // Прерываем цикл, так как проверка остальных условий по данному элементу уже не нужна
            }
        }
    });
}

/**********************************************************************************
 * Функция для ФОРМИРОВАНИЯ И ОТОБРАЖЕНИЯ СООБЩЕНИЯ ОБ ОШИБКЕ над элементом ввода.
 *
 * Используется при валидации формы регистрации пользователя
 * inputId - идентификатор элемента управления (select, input..)
 * errorText - сообщение об ошибке, которое нужно отобразить
 **********************************************************************************/

function buildErrorMessageBlock (inputId, errorText) {
    var divErrorBlock = document.createElement('div');
    var divErrorContent = document.createElement('div');
    var errorArrow = document.createElement('div');

    $(divErrorBlock).addClass("errorBlock");
    $(divErrorBlock).addClass($("#" + inputId).attr("name"));
    $(divErrorContent).addClass("errorContent");
    $(errorArrow).addClass("errorArrow");

    $("body").append(divErrorBlock);
    $(divErrorBlock).append(errorArrow);
    $(divErrorBlock).append(divErrorContent);
    $(errorArrow).html('<div class="line10"></div><div class="line9"></div><div class="line8"></div><div class="line7"></div><div class="line6"></div><div class="line5"></div><div class="line4"></div><div class="line3"></div><div class="line2"></div><div class="line1"></div>');
    $(divErrorContent).html(errorText);

    inputTopPosition = $("#" + inputId).offset().top;
    inputleftPosition = $("#" + inputId).offset().left;
    inputWidth = $("#" + inputId).width();
    inputHeight = $("#" + inputId).height();
    divErrorBlockHeight = $(divErrorBlock).height();

    inputleftPosition = inputleftPosition + inputWidth - 30;
    inputTopPosition = inputTopPosition - divErrorBlockHeight - 10;

    $(divErrorBlock).css({
        top: inputTopPosition,
        left: inputleftPosition,
        opacity: 0
    });
    $(divErrorBlock).fadeTo("fast", 0.8);
}

// При фокусировке на некотором элементе управления нам нужно удалить сообщение об ошибке, чтобы оно не мешало вводу данных
$("input[type=text], input[type=password], select, textarea").on('focus', function() {
    // Ищем блок с отображением ошибки, который помечен классом, совпадающим с именем элемента управления - то есть относящимся к этому элементу управления и удаляем его.
    $(".errorBlock." + $(this).attr("name")).each(function() {
        $(this).remove();
    });
});

// Инициализируем флаг - нужна ли валидация вкладки при ее появлении
var validationIsNeeded = false;

/* Как только будет загружен API и готов DOM, выполняем инициализацию карты от Яндекса*/
//ymaps.ready(init);
/* function init() {
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
 behaviors : ['default', 'scrollZoom']
 });
 */
/***** Добавляем элементы управления на карту *****/
/*	// Для добавления элемента управления на карту используется поле controls, ссылающееся на
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
 */
/***** Настраиваем возможность указания адреса в форме регистрации *****/
/*
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
 */