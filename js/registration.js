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
        yearRange:"1900:2004"
    });
    $("#birthday").datepicker($.datepicker.regional["ru"]);

});

// Подготовим возможность загрузки и редактирования фотографий
$(document).ready(createUploader);

/* Активируем механизм скрытия ненужных полей в зависимости от заполнения формы */
// При изменении перечисленных здесь полей функция notavailability пробегает форму с целью показать нужные элементы и скрыть ненужные
$(document).ready(notavailability);
$("#currentStatusEducation").change(notavailability);
$("#statusWork").change(notavailability);
$("#typeOfObject").change(notavailability);

// Функционал, который выполняется только при наличии вкладки 4 (Поиск)
if (document.getElementById("tabs-4")) {

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
}

/*****************************************************************
 * Валидация полей ввода в браузере
 *****************************************************************/

// Принимает решение - нужно ли выполнить валидацию вновь открываемой вкладки или нет
$('#tabs ul li a').click(function (event) {
    // Получаем номер кликнутой вкладки
    var currentTabId = $(this).attr("href").slice(-1) - 1;

    // Получаем список всех недоступных вкладок
    var disabled = $("#tabs").tabs("option", "disabled");

    // Если кликнутая вкладка недоступна - ничего не делаем
    if (disabled.indexOf(currentTabId) != -1) {
        // Значит кликнутая вкладка относится к недоступным - ничего не делаем
        validationIsNeeded = false;
        return false;
    }

    // Если кликнутая вкладка совпадает с текущей отображаемой, то ничего не делаем
    if (currentTabId == $("#tabs").tabs().tabs('option', 'selected')) {
        validationIsNeeded = false;
        return false;
    }

    // Если мы имеем дело с админом, который регистрирует чужого собственника, то ничего не делаем
    if (isAlienOwnerRegistration) {
        validationIsNeeded = false;
        return false;
    }

    // В иных случаях взводим флаг - требуется валидация при показе новой вкладки
    validationIsNeeded = true;

});

// Так как по событию клика вкладка еще не отображается, то проверка вкладки и отображение ошибок возможно только после наступления события tabsshow
$('#tabs').bind('tabsshow', function () {

    // Удаляем на странице все отображаемые блоки с ошибками
    $(".errorBlock").remove();

    if (validationIsNeeded) {
        // Получаем номер текущей вкладки
        currentTabId = $("#tabs").tabs().tabs('option', 'selected');

        // Проводим валидацию вновь открытой вкладки, чтобы отобразить имеющиеся на ней ошибки
        executeValidation("registration", currentTabId);

        // Снимаем флаг о том, что требуется валидация при показе новой вкладки
        validationIsNeeded = false;
    }
});

// Обработка клика по кнопке Назад
$(".backButton").click(function() {

    // Получаем номер текущей вкладки
    currentTabId = $("#tabs").tabs().tabs('option', 'selected');

    // Взводим флаг - требуется валидация при показе новой вкладки
    // Если мы имеем дело с админом, который регистрирует чужого собственника, то не проводим проверок
    if (isAlienOwnerRegistration) {
        validationIsNeeded = false;
    } else {
        validationIsNeeded = true;
    }

    // Меняем выбранную вкладку
    $("#tabs").tabs().tabs('select', currentTabId - 1);

    return false;
});

// Обработка клика по кнопке Далее
$(".forwardButton").click(function() {

    // Получаем номер текущей вкладки
    currentTabId = $("#tabs").tabs().tabs('option', 'selected');

    // Вызываем функцию валидации для этой вкладки
    // Если мы имеем дело с админом, который регистрирует чужого собственника, то не проводим проверок
    var errOnTab = 0;
    if (!isAlienOwnerRegistration) {
        errOnTab = executeValidation("registration", currentTabId);
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

    // Получаем номер текущей вкладки
    currentTabId = $("#tabs").tabs().tabs('option', 'selected');

    // Вызываем функцию валидации для всех вкладок по очереди, если на какой-то обнаружим ошибки, то останавливаем валидацию и оставляем пользователя на этой вкладке
    // Если мы имеем дело с админом, который регистрирует чужого собственника, то не проводим проверок
    var errOnTab = 0;
    if (!isAlienOwnerRegistration) {
    switch (currentTabId) {
        case 2:
            $("#tabs").tabs().tabs('select', 0);
            errOnTab = executeValidation("registration", 0);
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 1);
            errOnTab = executeValidation("registration", 1);
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 2);
            errOnTab = executeValidation("registration", 2);
            break;

        case 3:
            $("#tabs").tabs().tabs('select', 0);
            errOnTab = executeValidation("registration", 0);
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 1);
            errOnTab = executeValidation("registration", 1);
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 2);
            errOnTab = executeValidation("registration", 2);
            if (errOnTab != 0)  break;

            $("#tabs").tabs().tabs('select', 3);
            errOnTab = executeValidation("registration", 3);
            break;
    }
    }

    // Проверяем, есть ли ошибки на какой-либо вкладке
    // Если ошибок нет, то отправляем данные на сервер. Если ошибки есть хотя бы на одной из вкладок - открываем ее и отображаем ошибки
    if (errOnTab != 0) {
        return false;
    }

});