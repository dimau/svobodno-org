// Вставляем календарь для выбора дня рождения
$(function () {
    $("#datepicker").datepicker({
        changeMonth:true,
        changeYear:true,
        minDate:new Date(1900, 0, 1),
        maxDate:new Date(2004, 11, 31),
        defaultDate:new Date(1987, 0, 27),
        yearRange:"1900:2004",
    });
    $("#datepicker").datepicker($.datepicker.regional["ru"]);

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


// Подключение и настройка динамической проверки формы на JS
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
