/**********************************************************************
 * ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ: инициализация
 **********************************************************************/

// Инкрементный, уникальный. Используется для того, чтобы объединять на странице фотографии в отдельные галереи. Например, если на странице поиска много объектов недвижимости, то и фотки каждого отдельно взятого объекта должны быть проинициализированы как уникальная галерея через colorBox.
var currentFotoGalleryIndex = 1;

// Инициализируем флаг - нужна ли валидация вкладки при ее появлении
var validationIsNeeded = false;

/**********************************************************************
 * ВЕШАЕМ ОБРАБОТЧИКИ СОБЫТИЙ
 **********************************************************************/

// jQUERY UI: инициализация элементов по умолчанию
$(document).ready(initJQueryUI);

// Добавление в Избранное по клику на звезде
$(document).on('click', '.addToFavorites', addToFavorites);

// Удаление из избранного по клику на звезде
$(document).on('click', '.removeFromFavorites', removeFromFavorites);

// Активируем ColorBox для просмотра в модальном окне галереи фотографий по клику на миниатюре
$(document).ready(initColorBox);

/**********************************************************************
 * ФУНКЦИИ ОБРАБОТЧИКИ СОБЫТИЙ
 **********************************************************************/

// Обработчик события клика по добавлению в избранное
function addToFavorites() {
    // Если пользователь незарегистрирован (а значит скрипт на сервере поместил в документ блок #addToFavoritesDialog), то выдаем модальное окно с информацией о необходимости регистрации
    if ($("#addToFavoritesDialog").length) {
        $("#addToFavoritesDialog").dialog("open");
        return false;
    }

    // Получим идентификатор объекта, который добавляем в избранное
    var self = this;
    var propertyId = 0;
    propertyId = $(self).attr('propertyId');

    // Меняем внешний вид всех команд добавления в Избранное данного объекта на этой странице сразу - не дожидаясь ответа от сервера
    $(".addToFavorites[propertyId='" + propertyId + "']").removeClass("addToFavorites").addClass("removeFromFavorites");
    $(".removeFromFavorites[propertyId='" + propertyId + "'] img").attr('src', 'img/gold_star.png');
    $(".removeFromFavorites[propertyId='" + propertyId + "'] a").html("убрать из избранного");

    jQuery.post("AJAXChangeFavorites.php", {"propertyId":propertyId, "action":"addToFavorites"}, function (data) {
        $(data).find("span[status='successful']").each(function () {
            /* Действия при положительном ответе от сервера */
        });
        $(data).find("span[status='denied']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
        });
    }, "xml");

    return false;
}

// Обработчик события клика по удалению из избранного
function removeFromFavorites() {

    // Получим идентификатор объекта, который добавляем в избранное
    var self = this;
    var propertyId = 0;
    propertyId = $(self).attr('propertyId');

    // Меняем внешний вид всех команд добавления в Избранное данного объекта на этой странице сразу - не дожидаясь ответа от сервера
    $(".removeFromFavorites[propertyId='" + propertyId + "']").removeClass("removeFromFavorites").addClass("addToFavorites");
    $(".addToFavorites[propertyId='" + propertyId + "'] img").attr('src', 'img/blue_star.png');
    $(".addToFavorites[propertyId='" + propertyId + "'] a").html("добавить в избранное");

    jQuery.post("AJAXChangeFavorites.php", {"propertyId":propertyId, "action":"removeFromFavorites"}, function (data) {
        $(data).find("span[status='successful']").each(function () {
            /* Действия при положительном ответе от сервера */
        });
        $(data).find("span[status='denied']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении отказа в удалении из избранного, то закодить здесь */
        });
    }, "xml");

    return false;
}

// Функция для инициализации colorBox блоков на странице
function initColorBox() {
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

}

// Функция для инициализации оформления элементов с помощью jQuery UI
function initJQueryUI() {

    /* Инициализируем отображение вкладок при помощи jQuery UI */
    $("#tabs").tabs();

    // Если на странице есть модальнео окно для незарегистрированного пользователя, который нажал на кнопку Добавить в избранное, то активируем его
    $("#addToFavoritesDialog").dialog({
        autoOpen:false,
        modal:true,
        width:600,
        dialogClass:"edited",
        draggable:true
    });
}

/*****************************************************************
 * Отображение найденных ошибок при валидации данных на сервере (PHP)
 *****************************************************************/

if ($('#userMistakesBlock ol').html() != "") {
    $('#userMistakesBlock').on('click', function () {
        $(this).slideUp(800);
    });
    $('#userMistakesBlock').css('display', 'block');
}

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
        element:document.getElementById('file-uploader'),
        action:'../AJAXUploader.php',
        allowedExtensions:["jpeg", "JPEG", "jpg", "JPG", "png", "PNG", "gif", "GIF"], // Также расширения нужно менять в файле AJAXUploader.php
        sizeLimit:25 * 1024 * 1024,
        debug:false,
        // О каждом загруженном файле информацию передаем на сервер через переменные - для сохранения в БД
        onSubmit:function (id, fileName) {
            uploader.setParams({
                fileuploadid:$("#fileUploadId").val(),
                sourcefilename:fileName
            });
        }
        //extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]]
    });

    // Важно, что в конце файла AJAXUploader.php располагается функция handleUpload, в которой есть и мой код, работающий на сервере при получении файла

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
    $(li).remove();
    for (var i = 0; i < uploadedFoto.length; i++) {
        if (uploadedFoto[i]['id'] == fotoid) {
            uploadedFoto.splice(i, 1);
            break;
        }
    }

}

// Подготовка данных о фотографиях для передачи на сервер
// this - форма, на которой произошло событие submit
// Функция выполняется при событии submit на форме с class = "formWithFotos" (обработчик навешивается в функции createUploader)
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
    function getRadioValue(radioboxGroupName) {
        group = document.getElementsByName(radioboxGroupName);
        for (var i = 0; i < group.length; i++) {
            if (group[i].checked) {
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
            success:true,
            folder:folder,
            name:fotoid,
            ext:extension,
            status:status,
            uploadedFotoObjExists:'true' // Этот признак скажет функции uploader._onComplete, что не нужно записывать в массив uploadedFoto новый объект с информацией по этой фотографии, так как он уже там есть
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
    if (typeTenant === undefined) typeTenant = false;

    // Перебираем все элементы, доступность которых зависит от каких-либо условий
    $("[notavailability]").each(function () {
        // Получаем текущий элемент из перебираемых и набор условий его недоступности
        var currentElem = this;
        var notSelectorsOfElem = $(this).attr("notavailability");

        // Получаем массив, каждый элемент которого = условию недоступности
        var arrNotSelectorsOfElem = notSelectorsOfElem.split('&');

        // Презумпция доступности элемента, если одно из его условий недоступности выполнится ниже, то он станет недоступным
        $("select, input, textarea", currentElem).removeAttr("disabled");
        $(currentElem).css('color', '');
        if (typeTenant) $(".itemRequired.typeTenantRequired", currentElem).text("*");

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
                $("select, input, textarea", currentElem).attr("disabled", "disabled");
                $(currentElem).css('color', '#e6e6e6');
                $(".itemRequired", currentElem).text("");
                break; // Прерываем цикл, так как проверка остальных условий по данному элементу уже не нужна
            }
        }
    });
}

/**********************************************************************************
 * ВАЛИДАЦИЯ ПОЛЕЙ ВВОДА В БРАУЗЕРЕ: функции, используемые при валидации введенных пользователем данных и отображения ошибок
 *********************************************************************************/

// inputId - идентификатор элемента управления (select, input..)
// errorText - сообщение об ошибке, которое нужно отобразить
function buildErrorMessageBlock(inputId, errorText) {
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
        top:inputTopPosition,
        left:inputleftPosition,
        opacity:0
    });
    $(divErrorBlock).fadeTo("fast", 0.8);
}

// При фокусировке на некотором элементе управления нам нужно удалить сообщение об ошибке, чтобы оно не мешало вводу данных
$("input[type=text], input[type=password], select, textarea").on('focus', function () {
    // Ищем блок с отображением ошибки, который помечен классом, совпадающим с именем элемента управления - то есть относящимся к этому элементу управления и удаляем его.
    $(".errorBlock." + $(this).attr("name")).each(function () {
        $(this).remove();
    });
});

// Производит валидацию вкладки, номер которой передан в качестве параметра
// pageName - имя страницы, на которой производится валидация
// tabNumber - дополнительный параметр, указывающий на номер вкладки на странице (можно воспринимать как идентификатор блока параметров, которым требуется валидация на данной странице)
function executeValidation(pageName, tabNumber) {

    // Инициализируем переменную для хранения количества найденных ошибок
    var errors = 0;

    // Удаляем на странице все отображаемые блоки с ошибками
    $(".errorBlock").remove();

    if (pageName == "registration") {
        switch (tabNumber) {
            case 0:
                errors = personalForRegistration_validation();
                break;
            case 1:
                errors = searchRequest_validation();
                break;
        }
    }

    if (pageName == "forowner") {
        errors = requestFromOwner_validation();
    }

    // Возвращаем количество ошибок
    return errors;
}

// Функция валидации для вкладки с личными параметрами страницы регистрации
function personalForRegistration_validation() {

    var err = 0;

    if ($('#name').val() == '') {
        buildErrorMessageBlock("name", "Укажите имя");
        err++;
    }
    if ($('#name').val().length > 50) {
        buildErrorMessageBlock("name", "Не более 50-ти символов");
        err++;
    }
    if ($('#telephon').val() == '') {
        buildErrorMessageBlock("telephon", "Укажите контактный (мобильный) телефон");
        err++;
    } else {
        if (!/^[0-9]{10}$/.test($('#telephon').val())) {
            buildErrorMessageBlock("telephon", "Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019");
            err++;
        }
    }
    if ($('#password').val() == '') {
        buildErrorMessageBlock("password", "Укажите пароль");
        err++;
    }
    if ($('#email').val() != '' && !/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/.test($('#email').val())) {
        buildErrorMessageBlock("email", "E-mail не соответствует формату: попробуйте ввести e-mail еще раз или указать другой электронный адрес");
        err++;
    }
    if ($('#lic').attr('checked') != "checked") {
        buildErrorMessageBlock("lic", "Регистрация возможна только при согласии с условиями лицензионного соглашения");
        err++;
    }

    return err;
}

// Функция валидации для основных данных пользователя
function personalFIO_validation() {

    var err = 0;

    // Понимаем роль пользователя, так как некоторые поля обязательны для арендатора, но необязательны для собственника
    if (typeTenant === undefined) typeTenant = false;

    // ФИО
    if ($('#surname').val().length > 50) {
        buildErrorMessageBlock("surname", "Не более 50-ти символов");
        err++;
    }
    if ($('#name').val() == '') {
        buildErrorMessageBlock("name", "Укажите имя");
        err++;
    }
    if ($('#name').val().length > 50) {
        buildErrorMessageBlock("name", "Не более 50-ти символов");
        err++;
    }
    if ($('#secondName').val().length > 50) {
        buildErrorMessageBlock("secondName", "Не более 50-ти символов");
        err++;
    }

    // Пол, внешность, ДР
    if ($('#birthday').val() != '') {
        if (!/^\d\d.\d\d.\d\d\d\d$/.test($('#birthday').val())) {
            buildErrorMessageBlock("birthday", "Неправильный формат даты рождения, должен быть: дд.мм.гггг, например: 01.01.1980");
            err++;
        } else {
            if ($('#birthday').val().slice(0, 2) < "01" || $('#birthday').val().slice(0, 2) > "31") {
                buildErrorMessageBlock("birthday", "Проверьте дату Дня рождения (допустимо от 01 до 31)");
                err++;
            }
            if ($('#birthday').val().slice(3, 5) < "01" || $('#birthday').val().slice(3, 5) > "12") {
                buildErrorMessageBlock("birthday", "Проверьте месяц Дня рождения (допустимо от 01 до 12)");
                err++;
            }
            if ($('#birthday').val().slice(6) < "1900" || $('#birthday').val().slice(6) > "2003") {
                buildErrorMessageBlock("birthday", "Проверьте год Дня рождения (допустимо от 1900 до 2003)");
                err++;
            }
        }
    }

    // Логин и пароль
    if ($('#password').val() == '') {
        buildErrorMessageBlock("password", "Укажите пароль");
        err++;
    }

    // Телефон и e-mail
    if ($('#telephon').val() == '') {
        buildErrorMessageBlock("telephon", "Укажите контактный (мобильный) телефон");
        err++;
    } else {
        if (!/^[0-9]{10}$/.test($('#telephon').val())) {
            buildErrorMessageBlock("telephon", "Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019");
            err++;
        }
    }
    if ($('#email').val() != '' && !/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/.test($('#email').val())) {
        buildErrorMessageBlock("email", "E-mail не соответствует формату: попробуйте ввести e-mail еще раз или указать другой электронный адрес");
        err++;
    }

    return err;
}

// Функция валидации для данных об образовании и работе
function personalEducAndWork_validation() {
    var err = 0;

    // Понимаем роль пользователя, так как некоторые поля обязательны для арендатора, но необязательны для собственника
    if (typeTenant === undefined) typeTenant = false;

        // Образование
        if ($('#almamater').val().length > 100) {
            buildErrorMessageBlock("almamater", "Не более 100 символов");
            err++;
        }
        if ($('#speciality').val().length > 100) {
            buildErrorMessageBlock("speciality", "Не более 100 символов");
            err++;
        }
        if ($('#kurs').val().length > 30) {
            buildErrorMessageBlock("kurs", "Не более 30-ти символов");
            err++;
        }
        if ($('#yearOfEnd').val() != '' && !/^[12]{1}[0-9]{3}$/.test($('#yearOfEnd').val())) {
            buildErrorMessageBlock("yearOfEnd", "Укажите год окончания учебного заведения в формате: \"гггг\". Например: 2007");
            err++;
        }

    // Работа
        if ($('#placeOfWork').val().length > 100) {
            buildErrorMessageBlock("placeOfWork", "Не более 100 символов");
            err++;
        }
        if ($('#workPosition').val().length > 100) {
            buildErrorMessageBlock("workPosition", "Не более 100 символов");
            err++;
        }

    // Коротко о себе
    if ($('#regionOfBorn').val().length > 50) {
        buildErrorMessageBlock("regionOfBorn", "Не более 50-ти символов");
        err++;
    }
    if ($('#cityOfBorn').val().length > 50) {
        buildErrorMessageBlock("cityOfBorn", "Не более 50-ти символов");
        err++;
    }

    return err;
}

// Функция валидации для данных о социальных сетях пользователя
function personalSocial_validation() {
    var err = 0;

    if ($('#vkontakte').val().length > 100) {
        buildErrorMessageBlock("vkontakte", "Не более 100 символов");
        err++;
    }
    if ($('#vkontakte').val() != '' && !/vk\.com/.test($('#vkontakte').val())) {
        buildErrorMessageBlock("vkontakte", "Ошибка формата, строка должна содержать \"vk.com\")");
        err++;
    }
    if ($('#odnoklassniki').val().length > 100) {
        buildErrorMessageBlock("odnoklassniki", "Не более 100 символов");
        err++;
    }
    if ($('#odnoklassniki').val() != '' && !/www\.odnoklassniki\.ru\/profile\//.test($('#odnoklassniki').val())) {
        buildErrorMessageBlock("odnoklassniki", "Ошибка формата, строка должна содержать \"www.odnoklassniki.ru/profile/\")");
        err++;
    }
    if ($('#facebook').val().length > 100) {
        buildErrorMessageBlock("facebook", "Не более 100 символов");
        err++;
    }
    if ($('#facebook').val() != '' && !/www\.facebook\.com\/profile\.php/.test($('#facebook').val())) {
        buildErrorMessageBlock("facebook", "Ошибка формата, строка должна содержать \"www.facebook.com/profile.php\")");
        err++;
    }
    if ($('#twitter').val().length > 100) {
        buildErrorMessageBlock("twitter", "Не более 100 символов");
        err++;
    }
    if ($('#twitter').val() != '' && !/twitter\.com/.test($('#twitter').val())) {
        buildErrorMessageBlock("twitter", "Ошибка формата, строка должна содержать \"twitter.com\")");
        err++;
    }

    if ($('#tabs-3 #lic').length && $('#tabs-3 #lic').attr('checked') != "checked") {
        buildErrorMessageBlock("lic", "Регистрация возможна только при согласии с условиями лицензионного соглашения");
        err++;
    }

    return err;
}

// Функция валидации для параметров поискового запроса пользователя
function searchRequest_validation() {
    var err = 0;

    if (!/^\d{0,8}$/.test($('#minCost').val())) {
        buildErrorMessageBlock("minCost", "Неправильный формат числа (проверьте: только числа, не более 8 символов)");
        err++;
    }
    if (!/^\d{0,8}$/.test($('#maxCost').val())) {
        buildErrorMessageBlock("maxCost", "Неправильный формат числа (проверьте: только числа, не более 8 символов)");
        err++;
    }
    if (!/^\d{0,8}$/.test($('#pledge').val())) {
        buildErrorMessageBlock("pledge", "Неправильный формат числа (проверьте: только числа, не более 8 символов)");
        err++;
    }
    if ($('#minCost').val() > $('#maxCost').val()) {
        buildErrorMessageBlock("#minCost", "Минимальная стоимость аренды не может быть больше, чем максимальная");
        err++;
    }

    return err;
}

// Функция валидации для параметров заявки собственника
function requestFromOwner_validation() {
    var err = 0;

    if ($('#name').val() == '') {
        buildErrorMessageBlock("name", "Укажите Ваше имя");
        err++;
    }
    if ($('#name').val().length > 100) {
        buildErrorMessageBlock("name", "Используйте не более 100 символов");
        err++;
    }

    if ($('#telephon').val() == '') {
        buildErrorMessageBlock("telephon", "Укажите Ваш контактный номер телефона");
        err++;
    }
    if ($('#telephon').val().length > 20) {
        buildErrorMessageBlock("telephon", "Используйте не более 20 цифр, например: 9225468392");
        err++;
    }

    if ($('#address').val() == '') {
        buildErrorMessageBlock("address", "Укажите адрес недвижимости");
        err++;
    }
    if ($('#address').val().length > 60) {
        buildErrorMessageBlock("address", "Используйте не более 60-ти символов");
        err++;
    }

    return err;
}