// Отображение результатов обработки формы на PHP - найденных ошибок при заполнении форм на этой странице
if ($('#userMistakesBlock ol').html() != "") {
    $('#userMistakesBlock').on('click', function () {
        $(this).slideUp(800);
    });
    $('#userMistakesBlock').css('display', 'block');
}

// Выбор вкладки для отображения в качестве текущей после загрузки страницы
var index = "tabs-1"; // По умолчанию открываем первую вкладку - Профайл
if ($(".tabsId").attr('tabsId')) index = $(".tabsId").attr('tabsId');
$(function () {
    $("#tabs").tabs("select", index);
});

/***********************************************************
 * Вкладка Профиль
 ***********************************************************/

/* Переключение на вкладке Профиль из режима просмотра в режим редактирования и обратно */
$('#tabs-1 #notEditingProfileParametersBlock .setOfInstructions a').on('click', function () {
    $("#notEditingProfileParametersBlock").css('display', 'none');
    $("#editingProfileParametersBlock").css('display', '');
});

$('#editingProfileParametersBlock').on('submit', function () {
    $("#notEditingProfileParametersBlock").css('display', '');
    $("#editingProfileParametersBlock").css('display', 'none');
});

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


/* Если в форме Работа указано, что пользователь не работает или ничего не выбрано, то блокировать заполнение остальных инпутов */
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
$(function () {
    $("button#newAdvertButton").button({
        icons:{
            primary:"ui-icon-circle-plus"
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
$(function () {
    $("button#createSearchRequestButton").button({
        icons:{
            primary:"ui-icon-circle-plus"
        }
    });
});

// Подгонка размера правого блока параметров (районы) расширенного поиска под размер левого блока параметров. 10 пикселей - на компенсацию margin у fieldset
if (document.getElementById('rightBlockOfSearchParameters')) {
    document.getElementById('rightBlockOfSearchParameters').style.height = document.getElementById('leftBlockOfSearchParameters').offsetHeight - 10 + 'px';
    $('#rightBlockOfSearchParameters .searchItem').css('height', parseFloat($('#rightBlockOfSearchParameters fieldset').css('height')) - parseFloat($('#rightBlockOfSearchParameters fieldset legend').css('height')));
    // Блок редактируемых параметров поиска невидим в случае если пользователь уже является арендатором (у него есть поисковый запрос, данные которого и отображаются в нередактируемом виде (блок id="notEditingSearchParametersBlock"))
    // Важно, что сначала в видимом состоянии вычисляется нужная высота блока со списком районов, а только затем он вместе со всем блоком параметров поиска становится невидимым
    if ($(".userType").attr('typeTenant') == "true" && $(".userType").attr('correctNewSearchRequest') != "false") $('#extendedSearchParametersBlock').css('display', 'none');
}

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
$("#withWho").on('change', function (event) {
    if ($("#withWho").attr('value') != "самостоятельно" && $("#withWho").attr('value') != "0") {
        $("#withWhoDescription").css('display', '');
    } else {
        $("#withWhoDescription").css('display', 'none');
    }
});

/* Сценарии для появления блока с подробным описанием детей */
$("#children").on('change', function (event) {
    if ($("#children").attr('value') != "без детей" && $("#children").attr('value') != "0") {
        $("#childrenDescription").css('display', '');
    } else {
        $("#childrenDescription").css('display', 'none');
    }
});

/* Сценарии для появления блока с подробным описанием животных */
$("#animals").on('change', function (event) {
    if ($("#animals").attr('value') != "без животных" && $("#animals").attr('value') != "0") {
        $("#animalsDescription").css('display', '');
    } else {
        $("#animalsDescription").css('display', 'none');
    }
});

/* Переключение на вкладке поиск из режима просмотра в режим редактирования и обратно */
$('#tabs-4 #notEditingSearchParametersBlock .setOfInstructions a').on('click', function () {
    $("#notEditingSearchParametersBlock").css('display', 'none');
    $("#extendedSearchParametersBlock").css('display', '');
});

/***********************************************************
 * Вкладка Избранное
 ***********************************************************/