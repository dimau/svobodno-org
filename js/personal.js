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

// Подготовим возможность загрузки и редактирования фотографий
$(document).ready(createUploader);

/* Активируем механизм скрытия ненужных полей в зависимости от заполнения формы */
// При изменении перечисленных здесь полей функция notavailability пробегает форму с целью показать нужные элементы и скрыть ненужные
$(document).ready(notavailability);
$("#currentStatusEducation").change(notavailability);
$("#statusWork").change(notavailability);
$("#typeOfObject").change(notavailability);

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

/* Сценарии для появления блока с подробным описанием сожителей */
$("#withWho").on('change', withWho);
$(document).ready(withWho);
function withWho() {
    if ($("#withWho").attr('value') != "самостоятельно" && $("#withWho").attr('value') != "0") {
        $(".withWhoDescription").css('display', '');
    } else {
        $(".withWhoDescription").css('display', 'none');
    }
}

/* Сценарии для появления блока с подробным описанием детей */
$("#children").on('change', children);
$(document).ready(children);
function children() {
    if ($("#children").attr('value') != "без детей" && $("#children").attr('value') != "0") {
        $(".childrenDescription").css('display', '');
    } else {
        $(".childrenDescription").css('display', 'none');
    }
}

/* Сценарии для появления блока с подробным описанием животных */
$("#animals").on('change', animals);
$(document).ready(animals);
function animals() {
    if ($("#animals").attr('value') != "без животных" && $("#animals").attr('value') != "0") {
        $(".animalsDescription").css('display', '');
    } else {
        $(".animalsDescription").css('display', 'none');
    }
}

/* Переключение на вкладке поиск из режима просмотра в режим редактирования и обратно */
$('#tabs-4 #notEditingSearchParametersBlock .setOfInstructions a').on('click', function () {
    $("#notEditingSearchParametersBlock").css('display', 'none');
    $("#extendedSearchParametersBlock").css('display', '');
});

/***********************************************************
 * Вкладка Избранное
 ***********************************************************/