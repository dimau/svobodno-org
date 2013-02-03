// Отображение результатов обработки формы на PHP - найденных ошибок при заполнении форм на этой странице
if ($('#userMistakesBlock ol').html() != "") {
    $('#userMistakesBlock').on('click', function () {
        $(this).slideUp(800);
    });
    $('#userMistakesBlock').css('display', 'block');
}

// Выбор вкладки для отображения в качестве текущей после загрузки страницы
if (tabsId === undefined || tabsId == '') tabsId = "tabs-1"; // По умолчанию открываем первую вкладку - Профайл
$(function () {
    $("#tabs").tabs("select", tabsId);
});

/***********************************************************
 * Вкладка Профиль
 ***********************************************************/

/* Переключение на вкладке Профиль из режима просмотра в режим редактирования и обратно */
$('#editProfileButton').on('click', function () {
    $("#notEditingProfileParametersBlock").css('display', 'none');
    $("#editingProfileParametersBlock").css('display', 'block');
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

/*****************************************************************
 * Вкладка Уведомления
 *****************************************************************/

$(document).ready(function () {
    // Вешаем обработчик на клик по ссылке "прочитано" на уведомлении
    $(".isReadedTrueMessage").click(isReadedTrueMessage);

    // Вешаем обработчик на клик по ссылке удалить уведомление
    $(".removeMessage").click(removeMessage);
});

// Обработчик события клика по ссылке прочитано - делает уведомление прочитанным
function isReadedTrueMessage() {
    // Получим головной элемент уведомления (класса news)
    var messageBlock = $(this).closest(".news");

    // Возможно понадобится изменить кол-во непрочитанных уведомлений
    if ($(messageBlock).hasClass("unread")) decUnreadMessagesAmount();

    // Проведем изменения в интерфейсе
    $(messageBlock).removeClass("unread");
    $(this).remove(); // удаляет ссылку "прочитано"

    // Получим id уведомления
    var messageId = messageBlock.attr('messageId');
    // Получим тип уведомления
    var messageType = messageBlock.attr('messageType');

    jQuery.post("AJAXChangeMessages.php", {"messageId":messageId, "messageType":messageType, "action":"isReadedTrue"}, function (data) {
        $(data).find("span[status='successful']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении положительного ответа, то закодить здесь */
        });
        $(data).find("span[status='denied']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
        });
    }, "xml");
}

// Обработчик события - удаляет уведомление
function removeMessage() {
    // Получим головной элемент уведомления (класса news)
    var messageBlock = $(this).closest(".news");

    // Возможно понадобится изменить кол-во непрочитанных уведомлений
    if ($(messageBlock).hasClass("unread")) decUnreadMessagesAmount();

    // Получим id уведомления
    var messageId = messageBlock.attr('messageId');
    // Получим тип уведомления
    var messageType = messageBlock.attr('messageType');

    // Проведем изменения в интерфейсе - удалим блок данного уведомления
    $(messageBlock).remove();

    jQuery.post("AJAXChangeMessages.php", {"messageId":messageId, "messageType":messageType, "action":"remove"}, function (data) {
        $(data).find("span[status='successful']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении положительного ответа, то закодить здесь */
        });
        $(data).find("span[status='denied']").each(function () {
            /* Если вдруг нужно будет что-то выдавать при получении отказа в добавлении в избранное, то закодить здесь */
        });
    }, "xml");
}

// Уменьшает количество непрочитанных уведомлений на 1 на клиенте (в браузере)
function decUnreadMessagesAmount() {

    // Получим строки для изменения
    var menuBlock = $(".menu .amountOfNewMessagesBlock")[0];
    var menuAmount = $(".menu .amountOfNewMessages").html();
    var tabsBlock = $(".ui-tabs-nav .amountOfNewMessagesBlock")[0];
    var tabsAmount = $(".ui-tabs-nav .amountOfNewMessages").html();

    // Если непрочитанных уведомлений у пользователя нет
    if (!menuBlock || !tabsBlock) return false;

    if (menuAmount != "1") {
        // Если кол-во непрочитанных уведомлений больше 1, то уменьшаем их число на 1
        menuAmount--;
        $(".amountOfNewMessages", menuBlock).html(menuAmount);
        $(".amountOfNewMessages", tabsBlock).html(menuAmount);
    } else {
        // Если было только 1 непрочитанное уведомление, то удаляем весь блок со скобками и числом непрочитанных уведомлений
        $(menuBlock).remove();
        $(tabsBlock).remove();
    }

    return true;
}

/***********************************************************
 * Вкладка Мои объявления
 ***********************************************************/


/***********************************************************
 * Вкладка Поиск
 ***********************************************************/

// Блок редактируемых параметров поиска невидим в случае если пользователь уже является арендатором (у него есть поисковый запрос, данные которого и отображаются в нередактируемом виде (блок id="notEditingSearchParametersBlock"))
if ($('#extendedSearchParametersBlock').length) {
    if (typeTenant == true && correctEditSearchRequest != "FALSE") $('#extendedSearchParametersBlock').css('display', 'none');
}

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
$('#editSearchRequestButton').on('click', function () {
    $("#notEditedSearchRequestBlock").css('display', 'none');
    $("#extendedSearchParametersBlock").css('display', 'block');
});

/***********************************************************
 * Вкладка Избранное
 ***********************************************************/