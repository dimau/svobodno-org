// Отображение результатов обработки формы на PHP - найденных ошибок при заполнении форм на этой странице
if ($('#userMistakesBlock ol').html() != "") {
    $('#userMistakesBlock').on('click', function() {
        $(this).slideUp(800);
    });
    $('#userMistakesBlock').css('display', 'block');
}

// Вставляем календарь для выбора даты для начала аренды объекта
$(function() {
    var now = new Date();
    var twoYearsAfterNow = new Date(now.getFullYear()+2, now.getMonth(), now.getDate());
    $( "#datepicker1, #datepicker2" ).datepicker({
        changeMonth: true,
        changeYear: true,
        minDate: now,
        maxDate: twoYearsAfterNow,
        defaultDate: new Date(),
    });
    $( "#datepicker" ).datepicker($.datepicker.regional["ru"]);

});

/* Активируем механизм скрытия ненужных полей в зависимости от заполнения формы */
// Первоначально скроем все элементы, которые могут быть скрыты. Это нужно для того, чтобы при работе пользователя поля без редактирования поля появлялись, но не пропадали - так привычнее и более ожидаемо

// При изменении перечисленных здесь селектов алгоритм пробегает форму с целью показать нужные элементы и скрыть ненужные
$("#typeOfObject, #termOfLease, #amountOfRooms, #adjacentRooms, #typeOfBalcony, #subwayStation, #utilities, #bail").change(function currentStatusEducation(event) {
    var currentSelectId = $(this).attr('id');
    var currentSelectValue = $(this).val();
    // Показываем те элементы формы, которые стали нужны
    $("option", this).each(function() {
        if ($(this).val() != currentSelectValue) {
            searchingClass = "not" + currentSelectId + $(this).val();
            $("." + searchingClass).show();
        }
    });
    // Прячем блоки с ненужными элементами
    var searchingClass = "not" + currentSelectId + currentSelectValue;
    $("." + searchingClass).hide();
});
/*$(document).ready(currentStatusEducation);*/


/*
// Подготовим возможность загрузки фотографий
function createUploader(){
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: '../lib/uploader.php',
        allowedExtensions: ["jpeg", "jpg", "img", "bmp", "png", "gif"], //Также расширения нужно менять в файле uploader.php
        sizeLimit: 10 * 1024 * 1024,
        debug: true,
        // О каждом загруженном файле информацию передаем на сервер через переменные - для сохранения в БД
        onSubmit: function(id, fileName){
            uploader.setParams({
                fileuploadid: $("#fileUploadId").val(),
                sourcefilename: fileName,
            });
        },
        //extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]]
    });

    // Важно, что в конце файла uploader.php располагается функция handleUpload, в которой есть и мой код, работающий на сервере при получении файла

    // Сформируем зеленые блоки для уже загруженных фотографий руками, чтобы пользователя не путать
    var rezult = {success: true};
    var uploadedFoto = document.getElementsByClassName('uploadedFoto');
    for (var i = 0; i < uploadedFoto.length; i++) {
        var uploadedFotoName = $(uploadedFoto[i]).attr('filename');

        // Формируем зеленый блок в списке загруженных файлов в разделе Фотографии
        uploader._addToList(i + 100, uploadedFotoName);
        uploader._onComplete(i + 100, uploadedFotoName, rezult);
    }

    // Чтобы обмануть загрузчик файлов и он не выдавал при отправке страницы сообщение о том, что мол есть еще не загруженные фотографии, ориентируясь на сформированные вручную зеленые блоки
    uploader._filesInProgress = 0;
}
$(document).ready(createUploader); */

// Активируем кнопку сохранения параметров нового объявления
$(function() {
	$("#saveAdvertButton").button({
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