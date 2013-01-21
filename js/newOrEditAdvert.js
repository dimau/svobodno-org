/* Активируем механизм скрытия ненужных полей в зависимости от заполнения формы */

// При изменении перечисленных здесь селектов алгоритм пробегает форму с целью показать нужные элементы и скрыть ненужные
$(document).ready(notavailability);
$("#typeOfObject, #termOfLease, #amountOfRooms, #adjacentRooms, #typeOfBalcony, #subwayStation, #utilities, #bail").change(notavailability);

// Пробегает все элементы и изменяет в соответствии с текущей ситуацией их доступность/недоступность для пользователя
// ВНИМАНИЕ: данная функция отличается от той, что указана в main.js - она полностью прячет недоступные поля, а не просто делает их серыми
function notavailability() {
    // Перебираем все элементы, доступность которых зависит от каких-либо условий
    $("[notavailability]").each(function () {
        // Получаем текущий элемент из перебираемых и набор условий его недоступности
        var currentElem = this;
        var notSelectorsOfElem = $(this).attr("notavailability");

        // Получаем массив, каждый элемент которого = условию недоступности
        var arrNotSelectorsOfElem = notSelectorsOfElem.split('&');

        // Презумпция доступности элемента, если одно из его условий недоступности выполнится ниже, то он станет недоступным
        $(currentElem).show();
        $("select, input", currentElem).removeAttr("disabled");

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
                $(currentElem).hide();
                $("select, input", currentElem).attr("disabled", "disabled");
                break; // Прерываем цикл, так как проверка остальных условий по данному элементу уже не нужна
            }
        }
    });
}

// Вставляем календарь для выбора даты для начала аренды объекта
$(function () {
    var now = new Date();
    var twoYearsAfterNow = new Date(now.getFullYear() + 2, now.getMonth(), now.getDate());
    $("#datepicker1, #datepicker2").datepicker({
        changeMonth:true,
        changeYear:true,
        minDate:now,
        maxDate:twoYearsAfterNow,
        defaultDate:new Date()
    });
    $("#datepicker").datepicker($.datepicker.regional["ru"]);

});

// Подготовим возможность загрузки фотографий
$(document).ready(createUploader);

// Деактивируем кнопку проверки адреса, если доступность полей для редактирования ограничена
if (availability == "limited") {
    $(function () {
        $("#checkAddressButton").button({
            disabled:true
        });
    });
}

/* Как только будет загружен API и готов DOM, выполняем инициализацию карты от Яндекса*/
ymaps.ready(init);

function init() {
    // Создание экземпляра карты для Нового объявления и его привязка к контейнеру с
    // заданным id ("mapForNewAdvert")
    // Если пользователь уже указал месторасположение объекта - центрируем карту относительно него, иначе - относительно центра города
    var coordX = $("#coordX").val();
    var coordY = $("#coordY").val();
    if (coordX != "" && coordY != "") {
        var map = new ymaps.Map('mapForNewAdvert', {
            // При инициализации карты, обязательно нужно указать
            // ее центр и коэффициент масштабирования
            center:[$("#coordX").val(), $("#coordY").val()],
            zoom:16,
            // Включим поведения по умолчанию (default) и,
            // дополнительно, масштабирование колесом мыши.
            // дополнительно включаем измеритель расстояний по клику левой кнопки мыши
            behaviors:['default', 'scrollZoom']
        });

        // Добавляем на карту метку объекта недвижимости
        currentPlacemark = new ymaps.Placemark([coordX, coordY]);
        map.geoObjects.add(currentPlacemark);

    } else {
        var map = new ymaps.Map('mapForNewAdvert', {
            // При инициализации карты, обязательно нужно указать
            // ее центр и коэффициент масштабирования
            center:[56.829748, 60.617435], // Екатеринбург
            zoom:11,
            // Включим поведения по умолчанию (default) и,
            // дополнительно, масштабирование колесом мыши.
            // дополнительно включаем измеритель расстояний по клику левой кнопки мыши
            behaviors:['default', 'scrollZoom']
        });
    }

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
    // Стандартный набор кнопок, кроме линейки
    var myToolbar = new ymaps.control.MapTools(['drag', 'magnifier']);
    map.controls.add(myToolbar);

    // Создаем пустой массив маркеров - в него будет класть маркер, соответствующий адресу, введеному пользователем
    searchObjectCollection = new ymaps.GeoObjectCollection();

    // При вводе адреса в строку и нажатии энтера ставим метку на карте города
    $('#checkAddressButton').on('click', function () {
        // Записываем в переменную что конкретно ввел пользователь.
        var search_query = $('#addressTextBox').val();

        // Получаем набор координат объектов, соответствующих строке пользователя на карте -
        // Ограничиваем набор только первым объектом и поиск объекта ограничиваем только пригородом Екатеринбурга (параметр boundedBy [юго-западный угол, северов-восточный угол границы поиска])
        var geoObjectsOfsearch_query = ymaps.geocode(search_query, {
            results:1,
            boundedBy:[
                [56.727374, 60.465207],
                [56.921091, 60.838283]
            ],
            strictBounds:true
        });

        geoObjectsOfsearch_query.then(function (res) {
                searchObjectCollection.removeAll();
                searchObjectCollection = res.geoObjects;
                map.geoObjects.add(searchObjectCollection);

                // В центр карты поместим полученный объект
                var point = res.geoObjects.get(0);
                map.setCenter(point.geometry.getCoordinates(), 16);

                // Указанный пользователем адрес в строке ввода сформулируем в соответствии с базой Яндекса
                // Поле для ввода адреса располагается первым в форме!
                document.getElementById('addressTextBox').value = point.properties.get('name');

                // Сохраняем координаты в скрытые инпуты для передачи на сервер вместе с адресом объекта
                saveCoord(point);
            },
            // Обработка ошибки
            function (error) {
                if (!search_query) alert("Сначала укажите улицу и номер дома"); else alert("К сожалению, мы не смогли распознать указанную Вами улицу и номер дома. \nЕсли эта ошибка буде повторяться, пожалуйста, обратитесь в тех. поддержку. \nТекст ошибки для тех. поддержки: " + error.message);
            });

        return false;
    });

    // Если пользователь кликнит левой кнопкой по дому - то адресная строка заполнится автоматически
    // Работает только, если не производится ограниченное редактирование параметров объявления самим собственником
    if (availability != "limited") {
        map.events.add('click', function (e) {
            var coords = e.get('coordPosition');

            // Отправим запрос на геокодирование, берем только 1 результат - это будет название улицы и номер дома (так у них в Яндексе настроено).
            ymaps.geocode(coords, {
                results:1
            }).then(function (res) {
                    var names = [];

                    // Переберём все найденные результаты и
                    // запишем имена найденный объектов в массив names.
                    // Этот код остался от того момента, когда geocode был ограничен не одним результатом, а несколькими, возможно, для повышения эффективности его можно сократить
                    res.geoObjects.each(function (obj) {
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

                    // Сохраняем координаты в скрытые инпуты для передачи на сервер вместе с адресом объекта
                    saveCoord(point);
                });
        });
    }

    function saveCoord(point) {
        // Полученные координаты точки сохраним в input hidden для передачи на сервер
        var coordX = point.geometry.getCoordinates()[0];
        var coordY = point.geometry.getCoordinates()[1];
        $("#coordX").val(coordX);
        $("#coordY").val(coordY);
    }

    // Если карта будет помещена на скрытую первоначально при загрузке страницы вкладку, то ее нужно перестраивать вот так:
    // Чтобы карта отображалась при открытии вкладки, ее нужно перестраивать по событию - открытие вкладки
    // Чтобы карта отображалась при открытии вкладки - нужно $('#tabs').bind('tabsshow', function(event, ui) {
    /*$('#newAdvertButton').bind('click', function(event, ui) {
     map.setCenter([56.829748, 60.617435]);
     map.container.fitToViewport();
     });*/
}

// При изменении валюты пользователем, подставляем новое значение в блок с рассчетами
$(document).ready(currencyChanged);
$("#currency").change(currencyChanged);

function currencyChanged() {
    var value = $("#currency").val(); // текущее значение валюты
    $(".currency").html(value);
}

/********************************************************************
 * Единоразовая комиссия - взаимодополнение полей в валюте и %
 *******************************************************************/

$("#compensationMoney").change(compensationMoneyChanged);
$("#compensationPercent").change(compensationPercentChanged);
$("#costOfRenting").change(costOfRentingChanged);

function compensationMoneyChanged() {
    // Получаем текущее значение стоимости аренды в месяц и стоимости компенсации
    var costOfRenting = $("#costOfRenting").val();
    var compensationMoney = $("#compensationMoney").val();

    // Считаем процент компенсации от стоимости аренды
    if (costOfRenting == "" || compensationMoney == "") {
        var compensationPercent = 0;
    } else {
        compensationPercent = (compensationMoney / costOfRenting * 100).toFixed(2);
    }

    // Обновляем процент компенсации от стоимости аренды в браузере
    $("#compensationPercent").val(compensationPercent);
}

function compensationPercentChanged() {
    // Получаем текущее значение стоимости аренды в месяц и стоимости компенсации в %
    var costOfRenting = $("#costOfRenting").val();
    var compensationPercent = $("#compensationPercent").val();

    // Считаем сумму компенсации в соответствии с процентом компенсации от стоимости аренды
    if (costOfRenting == "" || compensationPercent == "") {
        var compensationMoney = 0;
    } else {
        compensationMoney = (compensationPercent / 100 * costOfRenting).toFixed(2);
    }

    // Обновляем сумму компенсации в браузере
    $("#compensationMoney").val(compensationMoney);
}

function costOfRentingChanged() {
    // Получаем текущее значение стоимости аренды в месяц и стоимости компенсации в % и в валюте
    var costOfRenting = $("#costOfRenting").val();
    var compensationPercent = $("#compensationPercent").val();
    var compensationMoney = $("#compensationMoney").val();

    // Считаем сумму компенсации в соответствии с процентом компенсации от стоимости аренды
    if (costOfRenting == "") {
        compensationMoney = "";
        compensationPercent = "";
    }
    if (costOfRenting != "" && compensationMoney != "" && compensationPercent == "") {
        compensationPercent = (compensationMoney / costOfRenting * 100).toFixed(2);
    }
    if (costOfRenting != "" && compensationPercent != "" && compensationMoney == "") {
        compensationMoney = (compensationPercent / 100 * costOfRenting).toFixed(2);
    }
    if (costOfRenting != "" && compensationMoney != "" && compensationPercent != "") {
        compensationMoney = "";
        compensationPercent = "";
    }

    // Обновляем сумму и % компенсации в браузере
    $("#compensationMoney").val(compensationMoney);
    $("#compensationPercent").val(compensationPercent);
}

// Активируем кнопку сохранения параметров объявления
$(function () {
    $("#saveAdvertButton").button({
        icons:{
            primary:"ui-icon-disk"
        }
    });
});