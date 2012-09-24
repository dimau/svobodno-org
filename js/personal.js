/**
 * @author dimau
 */

// Отображение результатов обработки формы на PHP - найденных ошибок при заполнении форм на этой странице
if ($('#userMistakesBlock ol').html() != "") {
    $('#userMistakesBlock').on('click', function() {
        $(this).slideUp(800);
    });
    $('#userMistakesBlock').css('display', 'block');
}

// Выбор вкладки для отображения в качестве текущей после загрузки страницы
var index = "tabs-1"; // По умолчанию открываем первую вкладку - Профайл
if ($(".tabsId").attr('tabsId')) index = $(".tabsId").attr('tabsId');
$(function() {
    $("#tabs").tabs("select" , index);
});

/***********************************************************
 * Вкладка Профиль
 ***********************************************************/

/* Переключение на вкладке Профиль из режима просмотра в режим редактирования и обратно */
$('#tabs-1 #notEditingProfileParametersBlock .setOfInstructions a').on('click', function() {
    $("#notEditingProfileParametersBlock").css('display', 'none');
    $("#editingProfileParametersBlock").css('display', '');
});

$('#editingProfileParametersBlock').on('submit', function() {
    $("#notEditingProfileParametersBlock").css('display', '');
    $("#editingProfileParametersBlock").css('display', 'none');
});

// Вставляем календарь для выбора дня рождения
$(function() {
    $( "#datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        minDate: new Date(1900, 0, 1),
        maxDate: new Date(2004, 11, 31),
        defaultDate: new Date(1987, 0, 27),
        yearRange: "1900:2004",
    });
    $( "#datepicker" ).datepicker($.datepicker.regional["ru"]);

});

// Подготовим возможность загрузки фотографий
function createUploader(){
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: '../lib/uploader.php',
        allowedExtensions: ["jpeg", "jpg", "img", "bmp", "png", "gif"], //Также расширения нужно менять в файле uploader.php
        sizeLimit: 10 * 1024 * 1024,
        debug: false,
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
$(document).ready(createUploader);


/* Если в форме Работа указан чекбокс - не работаю, то блокировать заполнение остальных инпутов */
$("#notWorkCheckbox").on('change', notWorkCheckbox);
$(document).ready(notWorkCheckbox);
function notWorkCheckbox() {
    var userTypeTenant = $(".userType").attr('typeTenant') == "true";
    if ($("#notWorkCheckbox").is(':checked')) {
        $("input.ifWorked").attr('disabled', 'disabled').css('color', 'grey');
        $("div.searchItem.ifWorked div.required").text("");
    } else {
        $("input.ifWorked").removeAttr('disabled').css('color', '');
        // Отметим звездочкой обязательность заполнения полей для арендаторов
        if (userTypeTenant) {
            $("div.searchItem.ifWorked div.required").text("*");
        } else {
            $("div.searchItem.ifWorked div.required").text("");
        }
    }
}

/* Если в форме Образование выбран селект - не учился, то блокировать заполнение остальных инпутов */
$("#currentStatusEducation").change(currentStatusEducation);
$(document).ready(currentStatusEducation);
function currentStatusEducation() {
    var userTypeTenant = $(".userType").attr('typeTenant') == "true";
    var currentValue = $("#currentStatusEducation option:selected").attr('value');
    if (currentValue == "0") {
        $("input.ifLearned, select.ifLearned").removeAttr('disabled').css('color', '');
        // Отметим звездочкой обязательность заполнения полей только для арендаторов
        if (userTypeTenant) {
            $("div.searchItem.ifLearned div.required").text("*");
        } else {
            $("div.searchItem.ifLearned div.required").text("");
        }
    }
    if (currentValue == "нет") {
        $("input.ifLearned, select.ifLearned").attr('disabled', 'disabled').css('color', 'grey');
        $("div.searchItem.ifLearned div.required").text("");
    }
    if (currentValue == "сейчас учусь") {
        $("input.ifLearned, select.ifLearned").removeAttr('disabled').css('color', '');
        $('#kurs').css('display', '');
        $('#yearOfEnd').css('display', 'none');
        // Отметим звездочкой обязательность заполнения полей только для арендаторов
        if (userTypeTenant) {
            $("div.searchItem.ifLearned div.required").text("*");
        } else {
            $("div.searchItem.ifLearned div.required").text("");
        }
    }
    if (currentValue == "закончил") {
        $("input.ifLearned, select.ifLearned").removeAttr('disabled').css('color', '');
        $('#kurs').css('display', 'none');
        $('#yearOfEnd').css('display', '');
        // Отметим звездочкой обязательность заполнения полей для арендаторов
        if (userTypeTenant) {
            $("div.searchItem.ifLearned div.required").text("*");
        } else {
            $("div.searchItem.ifLearned div.required").text("");
        }
    }
}

/***********************************************************
 * Вкладка Мои объявления
 ***********************************************************/

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
    window.open('newadvert.php');
	return false;
}

/***********************************************************
 * Вкладка Поиск
 ***********************************************************/

// Активируем кнопку Нового поискового запроса, если она есть на странице
$(function() {
    $("button#createSearchRequestButton").button({
        icons : {
            primary : "ui-icon-circle-plus"
        }
    });
});

// Подгонка размера правого блока параметров (районы) вкладки Поиск под размер левого блока параметров. 19 пикселей - на padding у fieldset
if (document.getElementById('rightBlockOfSearchParameters')) {
    document.getElementById('rightBlockOfSearchParameters').style.height = document.getElementById('leftBlockOfSearchParameters').offsetHeight - 22 + 'px';
}

/* Сценарии для появления блока с подробным описанием сожителей */
$("#withWho").on('change', function(event) {
    if ($("#withWho").attr('value') != "один") {
        $("#withWhoDescription").css('display', '');
    } else {
        $("#withWhoDescription").css('display', 'none');
    }
});

/* Сценарии для появления блока с подробным описанием детей */
$("#children").on('change', function(event) {
    if ($("#children").attr('value') != "без детей") {
        $("#childrenDescription").css('display', '');
    } else {
        $("#childrenDescription").css('display', 'none');
    }
});

/* Сценарии для появления блока с подробным описанием животных */
$("#animals").on('change', function(event) {
    if ($("#animals").attr('value') != "без животных") {
        $("#animalsDescription").css('display', '');
    } else {
        $("#animalsDescription").css('display', 'none');
    }
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

/***********************************************************
 * Вкладка Избранное
 ***********************************************************/

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
