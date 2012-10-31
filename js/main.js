
/**********************************************************************
 * ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ: инициализация
 **********************************************************************/

var currentFotoGalleryIndex = 1; // Инкрементный, уникальный. Используется для того, чтобы объединять на странице фотографии в отдельные галереи. Например, если на странице поиска много объектов недвижимости, то и фотки каждого отдельно взятого объекта должны быть проинициализированы как уникальная галерея через colorBox.

/**********************************************************************
 * ГЛАВНОЕ МЕНЮ: делаем красивые (равномерные) отступы внутри плашки меню
 **********************************************************************/

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

/**********************************************************************
 * jQUERY UI: инициализация элементов по умолчанию
 **********************************************************************/

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

/**********************************************************************
 * ИЗБРАННОЕ: добавление, удаление
 **********************************************************************/

// Навешиваем обработчик клика по добавлению в избранные
$(document).on('click', '.addToFavorites', addToFavorites);
function addToFavorites() {
    var self = this;
    var propertyId = 0;
    propertyId = $(self).attr('propertyId');

    jQuery.post("lib/changeFavorites.php", {"propertyId": propertyId, "action": "addToFavorites"}, function (data) {
        $(data).find("span[status='successful']").each(function () {
            // Изменяем соответствующим образом вид команды
            $("span.addToFavorites[propertyId='" + propertyId + "']").removeClass("addToFavorites").addClass("removeFromFavorites");
            $("span.removeFromFavorites[propertyId='" + propertyId + "'] img").attr('src', 'img/gold_star.png');
            $("span.removeFromFavorites[propertyId='" + propertyId + "'] a").html("убрать из избранного");
        });
        $(data).find("span[status='denied']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
        });
    }, "xml");

    return false;
}

// Навешиваем обработчик клика по удалению из избранного
$(document).on('click', '.removeFromFavorites', removeFromFavorites);
function removeFromFavorites() {
    var self = this;
    var propertyId = 0;
    propertyId = $(self).attr('propertyId');

    jQuery.post("lib/changeFavorites.php", {"propertyId": propertyId, "action": "removeFromFavorites"}, function (data) {
        $(data).find("span[status='successful']").each(function () {
            // Изменяем соответствующим образом вид команды
            $("span.removeFromFavorites[propertyId='" + propertyId + "']").removeClass("removeFromFavorites").addClass("addToFavorites");
            $("span.addToFavorites[propertyId='" + propertyId + "'] img").attr('src', 'img/blue_star.png');
            $("span.addToFavorites[propertyId='" + propertyId + "'] a").html("добавить в избранное");
        });
        $(data).find("span[status='denied']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении отказа в удалении из избранного, то закодить здесь */
        });
    }, "xml");

    return false;
}

/**********************************************************************
 * Выравнивание блока со списком районов и других блоков в параметрах поиска
 **********************************************************************/

// Подгонка размера правого блока параметров (районы) расширенного поиска под размер левого блока параметров. 10 пикселей - на компенсацию margin у fieldset
if ($('#rightBlockOfSearchParameters').length && $('#leftBlockOfSearchParameters').length) {
    $('#rightBlockOfSearchParameters').height($('#leftBlockOfSearchParameters').height() - 10);
    $('#rightBlockOfSearchParameters ul').height($('#rightBlockOfSearchParameters fieldset').height() - $('#rightBlockOfSearchParameters fieldset legend').height());
}

/**********************************************************************
 * Активируем ColorBox для просмотра в модальном окне галереи фотографий по клику на миниатюре
 **********************************************************************/

$(document).ready(function () {
    // Соберем на странице все блоки с фотографиями
    var allFotosWrappers = document.getElementsByClassName('fotosWrapper');

    // Для каждого блока с фотографиями создаем отдельную галерею colorBox
    for (var i = 0; i < allFotosWrappers.length; i++) {
        // Если данный блок с фотографией(ями) не должен быть интерактивным, то навешивать на него colorBox не нужно
        if ($(allFotosWrappers[i]).hasClass('fotoNonInteractive')) continue;

        // Навешиваем обработчик colorBox, преобразую набор картинок в галерею
        $(".gallery", allFotosWrappers[i]).colorbox({ opacity:0.7, rel:currentFotoGalleryIndex, current:'№ {current} из {total}' });
        currentFotoGalleryIndex++;
    }

});

/*****************************************************************
 * ФОТОГРАФИИ: загрузка и редактирование
 *****************************************************************/

// Схема работы следующая:
// При формировании страницы на сервере на нее (в конец страницы) добавляется информация об уже ранее загруженных фотографиях по данному пользователю (или объекту недвижимости). Если таковых нет, то передается пустой массив. Информация помещается в переменную temp в виде JSON-строки
// На клиенте подставленная сервером JSON-строка temp декодируется и мы получаем uploadedFoto не в виде строки, а в виде массива объектов. Каждый объект содержит информацию по 1 фотографии
// На странице JS выполняет функцию createUploader (находится в main.js) для инициализации загрузки и редактирования фотографий
// createUploader вызывает функцию createUploadedFilesBlocks (находится в main.js), которая формирует на основе массива uploadedFoto соответствующие li элементы для списка фотографий (миниатюры фоток, команды для работы с ними...)
// Если пользователь загружает новую фотографию, то скрипт _onComplete в файле fileuploader.js сформирует для нее соответствующий объект с данными фотографии и запишет его в массив uploadedFoto
// При удалении фотографии на клиенте скрипт removeFoto (находится в main.js) удалит соответствующий ей объект из массива uploadedFoto
// При отправке заполненной формы на сервер registration.js (или другая страница) вызывает скрипт attrInputHiddenToValue в main.js, который заполняет поле value у специального INPUT hidden JSON-строкой, в которую преобразуется массив uploadedFoto.
// На сервере registration.php (или другая страница) разбирает эту строку и превращает ее снова в JSON-строку для передачи в браузер для JS (если возвращает некорректную форму клиенту), либо, получив дополнительные данные из таблицы tempFotos, сохраняет данные по фотографиям для постоянного хранения в таблицу userFotos в Базу данных на сервере (если валидация данных формы прошла успешно)
// Требования к HTML на странице: 1. формы, в которых предусмотрена возможность загрузки фотографий должны быть отмечены классом formWithFotos (для того, чтобы createUploader навешал на них обработчик события submit при инициализации). 2. в форме должен содержаться элемент с id = fotoWrapperBlock, внутри которого и помещается все, что связанос загрузчиком фотографий. 3. В том числе, внутри fotoWrapperBlock должен содержаться элемент с id = file-uploader - именно в него fileuploader.js поместить загрузчик фоток 4. в конце страницы должен быть блок script внутри которого php сможет сохранить в переменную uploadedFoto данные по ранее загруженных фоткам

// Функция активирует блок загрузки и редактирования фотографий на странице
function createUploader() {
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: '../lib/uploader.php',
        allowedExtensions: ["jpeg", "JPEG", "jpg", "JPG", "png", "PNG", "gif", "GIF"], // Также расширения нужно менять в файле uploader.php
        sizeLimit: 10 * 1024 * 1024,
        debug: false,
        // О каждом загруженном файле информацию передаем на сервер через переменные - для сохранения в БД
        onSubmit:function (id, fileName) {
            uploader.setParams({
                fileuploadid: $("#fileUploadId").val(),
                sourcefilename: fileName
            });
        }
        //extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]]
    });

    // Важно, что в конце файла uploader.php располагается функция handleUpload, в которой есть и мой код, работающий на сервере при получении файла

    // Сформируем зеленые блоки для уже загруженных фотографий руками, чтобы пользователя не путать
    createUploadedFilesBlocks(uploader);

    // Навесим обработчик клика по ссылке удалить фотографию
    $(document).on('click', '.qq-upload-remove', removeFoto);

    // Навесим обработчик submit на формы, содержащие класс formWithFotos
    $("form.formWithFotos").submit(attrInputHiddenToValue);
}

// Обработчик клика по кнопке Удалить фотографию
// this - инициализируется элементом-ссылкой 'Удалить фото'
function removeFoto() {

    // Получим id фотографии, которую пользователь желает удалить
    li = $(this).closest("li.uploadedFotoVisualItem")[0];
    fotoid = li.fotoid;

    // Удалим соответствующий фотографии элемент списка и запишем изменения в массив uploadedFoto (а фактически удалим из массива соответствующий фотографии объект)
    $(this).closest("li.uploadedFotoVisualItem").remove();
    for (var i = 0; i < uploadedFoto.length; i++) {
        if (uploadedFoto[i]['fotoid'] == fotoid) {
            uploadedFoto.splice(i, 1);
            break;
        }
    }

}

// Подготовка данных о фотографиях для передачи на сервер
// this - форма, на которой произошло событие submit
function attrInputHiddenToValue() {
    // Актуализируем статус у объектов массива uploadedFoto
    var primaryFotoId = getRadioValue('primaryFotoRadioButton');
    for (var i = 0; i < uploadedFoto.length; i++) {
        if (uploadedFoto[i]['id'] == primaryFotoId) {
            uploadedFoto[i]['status'] = 'основная';
        } else {
            uploadedFoto[i]['status'] = '';
        }
    }

    // Записываем JSON строку массива uploadedFoto в value параметр INPUT hidden для передачи на сервер
    $("#uploadedFoto").attr('value', JSON.stringify(uploadedFoto));
    return true;

    // Вспомогательная функция для определения значения (value) выбранной радиокнопки в группе с именем radioboxGroupName
    function getRadioValue(radioboxGroupName)
    {
        group = document.getElementsByName(radioboxGroupName);
        for (var i = 0; i < group.length; i++)
        {
            if (group[i].checked)
            {
                return (group[i].value);
            }
        }
        return (false);
    }
}

// ЗЕЛЕНЫЕ БЛОКИ ДЛЯ РАНЕЕ ЗАГРУЖЕННЫХ ФОТО ПРИ РЕНДЕРИНГЕ СТРАНИЦЫ
// Функция для создания зеленых блоков, соответствующих ранее загруженным фотографиям
function createUploadedFilesBlocks(uploader) {

    // Перебираем массив uploadedFoto, содержащий объекты, каждый из которых представляет данные по 1 ранее загруженной фотографии
    for (var i = 0; i < uploadedFoto.length; i++) {
        var folder = uploadedFoto[i]['folder'];
        var fotoid = uploadedFoto[i]['id'];
        var extension = uploadedFoto[i]['extension'];
        var filename = uploadedFoto[i]['filename'];
        var status = uploadedFoto[i]['status'];

        // Собираем объект с параметрами вызова функции _onComplete
        var result = {
            success: true,
            folder: folder,
            name: fotoid,
            ext: extension,
            status: status,
            uploadedFotoObjExists: 'true' // Этот признак скажет функции uploader._onComplete, что не нужно записывать в массив uploadedFoto новый объект с информацией по этой фотографии, так как он уже там есть
        };

        // Формируем зеленый блок в списке загруженных файлов в разделе Фотографии. Шаблон для блока хранится в fileTemplate в fileuploader.js (примерно 571 строка)
        uploader._addToList(100 + i, filename);
        uploader._onComplete(100 + i, filename, result);
    }

    // Чтобы обмануть загрузчик файлов и он не выдавал при отправке страницы сообщение о том, что мол есть еще не загруженные фотографии, ориентируясь на сформированные вручную зеленые блоки
    uploader._filesInProgress = 0;
}

/**********************************************************************
 * ПОЛЕЗНЫЕ ФУНКЦИИ
 **********************************************************************/

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
                    result.push(list[i]);
                    break;
                }
            }
        }
        return result;
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
 *********************************************************************************/

 // Пробегает все элементы и изменяет в соответствии с текущей ситуацией их доступность/недоступность для пользователя
 // Для использования необходимо подключить запуск функции к соответствующему селекту:
 // $("#typeOfObject").change(notavailability);

function notavailability() {

    // Понимаем роль пользователя, так как некоторые поля обязательны для арендатора, но необязательны для собственника
    var userTypeTenant = "";
    if ($(".userType").length) userTypeTenant = $(".userType").attr('typeTenant') == "true";

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
 *********************************************************************************/

// Используется при валидации формы регистрации пользователя
// inputId - идентификатор элемента управления (select, input..)
// errorText - сообщение об ошибке, которое нужно отобразить

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