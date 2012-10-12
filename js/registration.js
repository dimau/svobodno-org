// Блокируем вкладки, начиная со второй
$(function () {
    $("#tabs").tabs("option", "disabled", [1, 2, 3]);
});

// Вставляем календарь для выбора дня рождения
$(function () {
    $("#birthday").datepicker({
        changeMonth:true,
        changeYear:true,
        minDate:new Date(1900, 0, 1),
        maxDate:new Date(2004, 11, 31),
        defaultDate:new Date(1987, 0, 27),
        yearRange:"1900:2004",
    });
    $("#birthday").datepicker($.datepicker.regional["ru"]);

});

// Подготовим возможность загрузки фотографий
function createUploader() {
    var uploader = new qq.FileUploader({
        element:document.getElementById('file-uploader'),
        action:'../lib/uploader.php',
        allowedExtensions:["jpeg", "jpg", "img", "bmp", "png", "gif"], //Также расширения нужно менять в файле uploader.php
        sizeLimit:10 * 1024 * 1024,
        debug:false,
        // О каждом загруженном файле информацию передаем на сервер через переменные - для сохранения в БД
        onSubmit:function (id, fileName) {
            uploader.setParams({
                fileuploadid:$("#fileUploadId").val(),
                sourcefilename:fileName,
            });
        },
        //extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]]
    });

    // Важно, что в конце файла uploader.php располагается функция handleUpload, в которой есть и мой код, работающий на сервере при получении файла

    // Сформируем зеленые блоки для уже загруженных фотографий руками, чтобы пользователя не путать
    var rezult = {success:true};
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

/* Если в форме Работа указано, что пользователь не работает, то блокировать заполнение остальных инпутов */
$("#statusWork").on('change', statusWork);
$(document).ready(statusWork);
function statusWork() {
    var userTypeTenant = $(".userType").attr('typeTenant') == "true";
    var currentValue = $("#statusWork option:selected").attr('value');
    if (currentValue == "не работаю") {
        $("input.ifWorked").attr('disabled', 'disabled').css('color', 'grey');
        $("div.ifWorked div.required").text("");
    } else {
        $("input.ifWorked").removeAttr('disabled').css('color', '');
        // Отметим звездочкой обязательность заполнения полей для арендаторов
        if (userTypeTenant) {
            $("div.ifWorked div.required").text("*");
        } else {
            $("div.ifWorked div.required").text("");
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
        $('#kursBlock').css('display', '');
        $('#yearOfEndBlock').css('display', 'none');
        // Отметим звездочкой обязательность заполнения полей только для арендаторов
        if (userTypeTenant) {
            $("div.searchItem.ifLearned div.required").text("*");
        } else {
            $("div.searchItem.ifLearned div.required").text("");
        }
    }
    if (currentValue == "закончил") {
        $("input.ifLearned, select.ifLearned").removeAttr('disabled').css('color', '');
        $('#kursBlock').css('display', 'none');
        $('#yearOfEndBlock').css('display', '');
        // Отметим звездочкой обязательность заполнения полей для арендаторов
        if (userTypeTenant) {
            $("div.searchItem.ifLearned div.required").text("*");
        } else {
            $("div.searchItem.ifLearned div.required").text("");
        }
    }
}

if (document.getElementById("tabs-4")) {
    // Подгонка размера правого блока параметров (районы) расширенного поиска под размер левого блока параметров. 10 пикселей - на компенсацию margin у fieldset
    document.getElementById('rightBlockOfSearchParameters').style.height = document.getElementById('leftBlockOfSearchParameters').offsetHeight - 10 + 'px';
    $('#rightBlockOfSearchParameters .searchItem').css('height', parseFloat($('#rightBlockOfSearchParameters fieldset').css('height')) - parseFloat($('#rightBlockOfSearchParameters fieldset legend').css('height')));

    /* Активируем механизм скрытия ненужных полей в зависимости от заполнения формы */
    // При изменении перечисленных здесь полей алгоритм пробегает форму с целью показать нужные элементы и скрыть ненужные
    $(document).ready(notavailability);
    $("#typeOfObject").change(notavailability);
    // Пробегает все элементы и изменяет в соответствии с текущей ситуацией их доступность/недоступность для пользователя
    function notavailability() {
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
            $("div.required", currentElem).text("*");

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
                    $("div.required", currentElem).text("");
                    break; // Прерываем цикл, так как проверка остальных условий по данному элементу уже не нужна
                }
            }
        });
    }

    /* Сценарии для появления блока с подробным описанием сожителей */
    $("#withWho").on('change', withWho);
    $(document).ready(withWho);
    function withWho() {
        if ($("#withWho").attr('value') != "самостоятельно" && $("#withWho").attr('value') != "0") {
            $("#withWhoDescription").css('display', '');
        } else {
            $("#withWhoDescription").css('display', 'none');
        }
    }

    /* Сценарии для появления блока с подробным описанием детей */
    $("#children").on('change', children);
    $(document).ready(children);
    function children() {
        if ($("#children").attr('value') != "без детей" && $("#children").attr('value') != "0") {
            $("#childrenDescription").css('display', '');
        } else {
            $("#childrenDescription").css('display', 'none');
        }
    }

    /* Сценарии для появления блока с подробным описанием животных */
    $("#animals").on('change', animals);
    $(document).ready(animals);
    function animals() {
        if ($("#animals").attr('value') != "без животных" && $("#animals").attr('value') != "0") {
            $("#animalsDescription").css('display', '');
        } else {
            $("#animalsDescription").css('display', 'none');
        }
    }
}


// Отображение результатов обработки формы на PHP
if ($('#userMistakesBlock ol').html() != "") {
    $('#userMistakesBlock').on('click', function () {
        $(this).slideUp(800);
    });
    $('#userMistakesBlock').css('display', 'block');
}


/*// Подключение и настройка динамической проверки формы на JS
$('#tabs').bind('tabsshow', function (event, ui) {
    newTabId = ui.panel.id; // Определяем идентификатор вновь открытой вкладки
    $(".formError." + newTabId).css("display", ""); // Показываем все ошибки к полям на этой вкладке
    $(".formError").not("." + newTabId).css('display', "none"); // Скрываем все ошибки полей на других вкладках

    // Перепозиционируем подсказки по валидации при открытии вкладки - это важно при проверке формы перед отправкой, когда появляются все подсказки на всех вкладках (даже невидимых)
    $(".formError." + newTabId).each(function () {
        var validatedElemName = $(this).attr("class").split(" ")[1];
        var validatedElem = document.body.querySelector("[name=" + validatedElemName + "]");
        rePosition(validatedElem, this);
    });
});
// В качестве входных параметров получает caller - валидируемый элемент и divFormError - элемент всплывающей подсказки с текстом сообщения об ошибке валидации
function rePosition(caller, divFormError) { // Соответствует действиям по позиционированию функции buildPrompt из jquery.validationEngine.js - строчка 84
    callerTopPosition = $(caller).offset().top;
    callerleftPosition = $(caller).offset().left;
    callerWidth = $(caller).width();
    callerHeight = $(caller).height();
    inputHeight = $(divFormError).height();

    callerleftPosition = callerleftPosition + callerWidth - 30;
    callerTopPosition = callerTopPosition - inputHeight - 10;

    $(divFormError).css({
        top:callerTopPosition,
        left:callerleftPosition,
    });
}
*/

/*****************************************************************
 * Блок с валидациями и выдачей ошибок
 *****************************************************************/

// Делаем свой движок для отображения ошибок
function buildErrorMessageBlock (inputId, errorText) {
    var divErrorBlock = document.createElement('div');
    var divErrorContent = document.createElement('div');
    var errorArrow = document.createElement('div');

    $(divErrorBlock).addClass("errorBlock");
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

var validationIsNeeded = false;

// Отображение ошибок при клике на вкладку
$('#tabs ul li a').click(function (event) {
    // Получаем номер кликнутой вкладки
    currentTabId = $(this).attr("href").slice(-1) - 1;

    // Получаем список всех недоступных вкладок
    var disabled = $("#tabs").tabs("option", "disabled");

    // Если кликнутая вкладка недоступна - ничего не делаем
    if (disabled.indexOf(currentTabId) != -1) {
        // Значит кликнутая вкладка относится к недоступным - ничего не делаем
        return false;
    }

    // Если кликнутая вкладка совпадает с текущей отображаемой, то ничего не делаем
    if (currentTabId == $("#tabs").tabs().tabs('option', 'selected')) {
        return false;
    }

    // Удаляем все блоки с ошибками
    $(".errorBlock").remove();

    // Взводим флаг - требуется валидация при показе новой вкладки
    validationIsNeeded = true;

});

// Так как
$('#tabs').bind('tabsshow', function () {
    if (validationIsNeeded) {
        // Получаем номер текущей вкладки
        currentTabId = $("#tabs").tabs().tabs('option', 'selected');

        // Проводим валидацию вновь открытой вкладки, чтобы отобразить имеющиеся на ней ошибки
        var errOnTab = 0;
        switch (currentTabId) {
            case 0:
                errOnTab = step1_validation();
                break;
            case 1:
                errOnTab = step2_validation();
                break;
            case 2:
                errOnTab = step3_validation();
                break;
            case 3:
                errOnTab = step4_validation();
                break;
        }

        // Снимаем флаг о том, что требуется валидация при показе новой вкладки
        validationIsNeeded = false;
    }
});

// Обработка клика по кнопке Назад
$(".backButton").click(function() {
    // Удаляем все блоки с ошибками
    $(".errorBlock").remove();

    // Получаем номер текущей вкладки
    currentTabId = $("#tabs").tabs().tabs('option', 'selected');

    // Взводим флаг - требуется валидация при показе новой вкладки
    validationIsNeeded = true;

    // Меняем выбранную вкладку
    $("#tabs").tabs().tabs('select', currentTabId - 1);

    return false;

});

// Обработка клика по кнопке Далее
$(".forwardButton").click(function() {
    // Удаляем все блоки с ошибками
    $(".errorBlock").remove();

    // Получаем номер текущей вкладки
    currentTabId = $("#tabs").tabs().tabs('option', 'selected');

   // Вызываем функцию валидации для этой вкладки
    var errOnTab = 0;
    switch (currentTabId) {
        case 0:
            errOnTab = step1_validation();
            break;
        case 1:
            errOnTab = step2_validation();
            break;
        case 2:
            errOnTab = step3_validation();
            break;
    }

    // Проверяем, есть ли ошибки на этой вкладке
    // Если ошибок нет, то открываем следующую вкладку
    if (errOnTab == 0) {
        $("#tabs").tabs().tabs('enable', currentTabId + 1);
        $("#tabs").tabs().tabs('select', currentTabId + 1);
    }

    return false;

});

// Обработка клика по кнопке Отправить (submitButton)
$(".submitButton").click(function() {
    // Удаляем все блоки с ошибками
    $(".errorBlock").remove();

    // Получаем номер текущей вкладки
    currentTabId = $("#tabs").tabs().tabs('option', 'selected');

    // Вызываем функцию валидации для всех вкладок по очереди, если на какой-то обнаружим ошибки, то останавливаем валидацию и оставляем пользователя на этой вкладке
    var errOnTab = 0;
    switch (currentTabId) {
        case 2:
            $("#tabs").tabs().tabs('select', 0);
            errOnTab = step1_validation();
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 1);
            errOnTab = step2_validation();
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 2);
            errOnTab = step3_validation();

            break;
        case 3:
            $("#tabs").tabs().tabs('select', 0);
            errOnTab = step1_validation();
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 1);
            errOnTab = step2_validation();
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 2);
            errOnTab = step3_validation();
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 3);
            errOnTab = step4_validation();
            break;
    }

    // Проверяем, есть ли ошибки на какой-либо вкладке
    // Если ошибок нет, то отправляем данные на сервер. Если ошибки есть хотя бы на одной из вкладок - открываем ее и отображаем ошибки
    if (errOnTab != 0) {
        return false;
    }

});

// Функции валидации для каждой вкладки
function step1_validation() {

    var err = 0;

    // ФИО
    if ($('#surname').val() == '') {
        buildErrorMessageBlock ("surname", "Укажите фамилию");
        err++;
    }
    if ($('#surname').val().length > 50) {
        buildErrorMessageBlock ("surname", "Слишком длинная фамилия. Можно указать не более 50-ти символов");
        err++;
    }
    if ($('#name').val() == '') {
        buildErrorMessageBlock ("name", "Укажите имя");
        err++;
    }
    if ($('#name').val().length > 50) {
        buildErrorMessageBlock ("name", "Слишком длинное имя. Можно указать не более 50-ти символов");
        err++;
    }
    if ($('#secondName').val() == '') {
        buildErrorMessageBlock ("secondName", "Укажите отчество");
        err++;
    }
    if ($('#secondName').val().length > 50) {
        buildErrorMessageBlock ("secondName", "Слишком длинное отчество. Можно указать не более 50-ти символов");
        err++;
    }

    // Пол, внешность, ДР
    if ($('#sex').val() == '0') {
        buildErrorMessageBlock ("sex", "Укажите пол");
        err++;
    }
    if ($('#nationality').val() == '0') {
        buildErrorMessageBlock ("nationality", "Укажите внешность");
        err++;
    }
    if ($('#birthday').val() == '') {
        buildErrorMessageBlock ("birthday", "Укажите дату рождения");
        err++;
    } else {
        if (!/^\d\d.\d\d.\d\d\d\d$/.test($('#birthday').val())) {
            buildErrorMessageBlock ("birthday", "Неправильный формат даты рождения, должен быть: дд.мм.гггг, например: 01.01.1980");
            err++;
        } else {
            if ($('#birthday').val().slice(0, 2) < "01" || $('#birthday').val().slice(0, 2) > "31") {
                buildErrorMessageBlock ("birthday", "Проверьте дату Дня рождения (допустимо от 01 до 31)");
                err++;
            }
            if ($('#birthday').val().slice(3, 5) < "01" || $('#birthday').val().slice(3, 5) > "12") {
                buildErrorMessageBlock ("birthday", "Проверьте месяц Дня рождения (допустимо от 01 до 12)");
                err++;
            }
            if ($('#birthday').val().slice(6) < "1800" || $('#birthday').val().slice(6) > "2100") {
                buildErrorMessageBlock ("birthday", "Проверьте год Дня рождения (допустимо от 1800 до 2100)");
                err++;
            }
        }
    }

    // Логин и пароль
    if ($('#login').val() == '') {
        buildErrorMessageBlock ("login", "Укажите логин");
        err++;
    }
    if ($('#login').val().length > 50) {
        buildErrorMessageBlock ("login", "Слишком длинный логин. Можно указать не более 50-ти символов");
        err++;
    }
    if ($('#password').val() == '') {
        buildErrorMessageBlock ("password", "Укажите пароль");
        err++;
    }

    // Телефон и e-mail
    if ($('#telephon').val() == '') {
        buildErrorMessageBlock ("telephon", "Укажите контактный (мобильный) телефон");
        err++;
    } else {
        if (!/^[0-9]{10}$/.test($('#telephon').val())) {
            buildErrorMessageBlock ("telephon", "Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019");
            err++;
        }
    }
    if ($('#email').val() == '' && $(".userType").attr('typeTenant') == 'true') {
        buildErrorMessageBlock ("email", "Укажите e-mail");
        err++;
    }
    if ($('#email').val() != '' && !/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/.test($('#email').val())) {
        buildErrorMessageBlock ("email", "Попробуйте ввести e-mail еще раз или указать другой электронный адрес (e-mail не прошел проверку формата)");
        err++;
    }

    return err;

}

function step2_validation() {
    var err = 0;

    if ($('#currentStatusEducation').val() == '0' && $(".userType").attr('typeTenant') == 'true') {
        buildErrorMessageBlock ("currentStatusEducation", "Укажите Ваше образование (текущий статус)");
        err++;
    } else {
        // Образование
        if ($('#almamater').val() == '' && $(".userType").attr('typeTenant') == 'true' && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            buildErrorMessageBlock ("almamater", "Укажите учебное заведение");
            err++;
        }
        if ($('#almamater').val().length > 100) {
            buildErrorMessageBlock ("almamater", "Слишком длинное название учебного заведения (используйте не более 100 символов)");
            err++;
        }
        if ($('#speciality').val() == '' && $(".userType").attr('typeTenant') == 'true' && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            buildErrorMessageBlock ("speciality", "Укажите специальность");
            err++;
        }
        if ($('#speciality').val().length > 100) {
            buildErrorMessageBlock ("speciality", "Слишком длинное название специальности (используйте не более 100 символов)");
            err++;
        }
        if ($('#kurs').val() == '' && $(".userType").attr('typeTenant') == 'true' && $('#currentStatusEducation').val() == 'сейчас учусь') {
            buildErrorMessageBlock ("kurs", "Укажите курс обучения");
            err++;
        }
        if ($('#kurs').val().length > 30) {
            buildErrorMessageBlock ("kurs", "Указана слишком длинная строка (используйте не более 30 символов)");
            err++;
        }
        if ($('#ochnoZaochno').val() == '0' && $(".userType").attr('typeTenant') == 'true' && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            buildErrorMessageBlock ("ochnoZaochno", "Укажите форму обучения (очная, заочная)");
            err++;
        }
        if ($('#yearOfEnd').val() == '' && $(".userType").attr('typeTenant') == 'true' && $('#currentStatusEducation').val() == 'закончил') {
            buildErrorMessageBlock ("yearOfEnd", "Укажите год окончания учебного заведения");
            err++;
        }
        if ($('#yearOfEnd').val() != '' && !/^[12]{1}[0-9]{3}$/.test($('#yearOfEnd').val())) {
            buildErrorMessageBlock ("yearOfEnd", "Укажите год окончания учебного заведения в формате: \"гггг\". Например: 2007");
            err++;
        }
    }

    // Работа
    if ($('#statusWork').val() == '0' && $(".userType").attr('typeTenant') == 'true') {
        buildErrorMessageBlock ("statusWork", "Укажите статус занятости");
        err++;
    } else {
        if ($('#placeOfWork').val() == '' && $("#statusWork").val() == 'работаю' && $(".userType").attr('typeTenant') == 'true') {
            buildErrorMessageBlock ("placeOfWork", "Укажите Ваше место работы (название организации)");
            err++;
        }
        if ($('#placeOfWork').val().length > 100) {
            buildErrorMessageBlock ("placeOfWork", "Слишком длинное наименование места работы (используйте не более 100 символов)");
            err++;
        }
        if ($('#workPosition').val() == '' && $("#statusWork").val() == 'работаю' && $(".userType").attr('typeTenant') == 'true') {
            buildErrorMessageBlock ("workPosition", "Укажите Вашу должность");
            err++;
        }
        if ($('#workPosition').val().length > 100) {
            buildErrorMessageBlock ("workPosition", "Слишком длинное название должности (используйте не более 100 символов)");
            err++;
        }
    }

    // Коротко о себе
    if ($('#regionOfBorn').val() == '' && $(".userType").attr('typeTenant') == 'true') {
        buildErrorMessageBlock ("regionOfBorn", "Укажите регион, в котором Вы родились");
        err++;
    }
    if ($('#regionOfBorn').val().length > 50) {
        buildErrorMessageBlock ("regionOfBorn", "Слишком длинное наименование региона, в котором Вы родились (используйте не более 50 символов)");
        err++;
    }
    if ($('#cityOfBorn').val() == '' && $(".userType").attr('typeTenant') == 'true') {
        buildErrorMessageBlock ("cityOfBorn", "Укажите город (населенный пункт), в котором Вы родились");
        err++;
    }
    if ($('#cityOfBorn').val().length > 50) {
        buildErrorMessageBlock ("cityOfBorn", "Слишком длинное наименование города, в котором Вы родились (используйте не более 50 символов)");
        err++;
    }

    return err;
}

function step3_validation() {
    var err = 0;

    if ($('#vkontakte').val().length > 100) {
        buildErrorMessageBlock ("vkontakte", "Указана слишком длинная ссылка на личную страницу Вконтакте (используйте не более 100 символов)");
        err++;
    }
    if ($('#vkontakte').val() != '' && !/vk\.com/.test($('#vkontakte').val())) {
        buildErrorMessageBlock ("vkontakte", "Укажите, пожалуйста, Вашу настоящую личную страницу Вконтакте, либо оставьте поле пустым (ссылка должна содержать строчку \"vk.com\")");
        err++;
    }
    if ($('#odnoklassniki').val().length > 100) {
        buildErrorMessageBlock ("odnoklassniki", "Указана слишком длинная ссылка на личную страницу в Одноклассниках (используйте не более 100 символов)");
        err++;
    }
    if ($('#odnoklassniki').val() != '' && !/www\.odnoklassniki\.ru\/profile\//.test($('#odnoklassniki').val())) {
        buildErrorMessageBlock ("odnoklassniki", "Укажите, пожалуйста, Вашу настоящую личную страницу в Одноклассниках, либо оставьте поле пустым (ссылка должна содержать строчку \"www.odnoklassniki.ru/profile/\")");
        err++;
    }
    if ($('#facebook').val().length > 100) {
        buildErrorMessageBlock ("facebook", "Указана слишком длинная ссылка на личную страницу на Facebook (используйте не более 100 символов)");
        err++;
    }
    if ($('#facebook').val() != '' && !/www\.facebook\.com\/profile\.php/.test($('#facebook').val())) {
        buildErrorMessageBlock ("facebook", "Укажите, пожалуйста, Вашу настоящую личную страницу на Facebook, либо оставьте поле пустым (ссылка должна содержать строчку с \"www.facebook.com/profile.php\")");
        err++;
    }
    if ($('#twitter').val().length > 100) {
        buildErrorMessageBlock ("twitter", "Указана слишком длинная ссылка на личную страницу в Twitter (используйте не более 100 символов)");
        err++;
    }
    if ($('#twitter').val() != '' && !/twitter\.com/.test($('#twitter').val())) {
        buildErrorMessageBlock ("twitter", "Укажите, пожалуйста, Вашу настоящую личную страницу в Twitter, либо оставьте поле пустым (ссылка должна содержать строчку \"twitter.com\")");
        err++;
    }

    return err;
}

function step4_validation() {
    var err = 0;

    if (!/^\d{0,8}$/.test($('#minCost').val())) {
        buildErrorMessageBlock ("minCost", "Неправильный формат числа в поле минимальной величины арендной платы (проверьте: только числа, не более 8 символов)");
        err++;
    }
    if (!/^\d{0,8}$/.test($('#maxCost').val())) {
        buildErrorMessageBlock ("maxCost", "Неправильный формат числа в поле максимальной величины арендной платы (проверьте: только числа, не более 8 символов)");
        err++;
    }
    if (!/^\d{0,8}$/.test($('#pledge').val())) {
        buildErrorMessageBlock ("pledge", "Неправильный формат числа в поле максимальной величины залога (проверьте: только числа, не более 8 символов)");
        err++;
    }

    if ($('#minCost').val() > $('#maxCost').val()) {
        buildErrorMessageBlock ("#minCost", "Минимальная стоимость аренды не может быть больше, чем максимальная. Исправьте поля, в которых указаны Ваши требования к диапазону стоимости аренды");
        err++;
    }

    if ($('#withWho').val() == "0" && $('#typeOfObject').val() != "гараж") {
        buildErrorMessageBlock ("withWho", "Укажите, как Вы собираетесь проживать в арендуемой недвижимости (с кем)");
        err++;
    }
    if ($('#children').val() == "0" && $('#typeOfObject').val() != "гараж") {
        buildErrorMessageBlock ("children", "Укажите, собираетесь ли Вы проживать вместе с детьми или без них");
        err++;
    }
    if ($('#animals').val() == "0" && $('#typeOfObject').val() != "гараж") {
        buildErrorMessageBlock ("animals", "Укажите, собираетесь ли Вы проживать вместе с животными или без них");
        err++;
    }
    if ($('#termOfLease').val() == "0") {
        buildErrorMessageBlock ("termOfLease", "Укажите предполагаемый срок аренды");
        err++;
    }

    /*if ($('#lic').val() != "yes") {
        buildErrorMessageBlock ("lic", "Регистрация возможна только при согласии с условиями лицензионного соглашения");
        err++;
    }*/

    return err;
}