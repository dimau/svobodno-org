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

// Отображение результатов обработки формы на PHP
if ($('#userMistakesBlock ol').html() != "") {
    $('#userMistakesBlock').on('click', function () {
        $(this).slideUp(800);
    });
    $('#userMistakesBlock').css('display', 'block');
}

/*****************************************************************
 * Блок с валидациями и выдачей ошибок
 *****************************************************************/

// Общедостыпная переменная, содержащая флаг - нужна ли валидация полей ввода при показе новой вкладки (tabsshow)
var validationIsNeeded;

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

// Производит валидацию вкладки, номер которой передан в качестве параметра
// pageName - имя страницы, на которой производится валидация
// tabNumber - дополнительный параметр, указывающий на номер вкладки на странице (можно воспринимать как идентификатор блока параметров, которым требуется валидация на данной странице)
function executeValidation(pageName, tabNumber) {

    // Инициализируем переменную для хранения количества найденных ошибок
    var errors = 0;

    // Удаляем на странице все отображаемые блоки с ошибками
    $(".errorBlock").remove();

    switch (tabNumber) {
        case 0:
            errors = personalFIO_validation();
            break;
        case 1:
            errors = personalEducAndWork_validation();
            break;
        case 2:
            errors = personalSocial_validation();
            break;
        case 3:
            errors = searchRequest_validation();
            break;
    }

    // Возвращаем количество ошибок
    return errors;
}

// Функции валидации для каждой вкладки
function personalFIO_validation() {

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
    if ($('#email').val() == '' && typeTenant) {
        buildErrorMessageBlock ("email", "Укажите e-mail");
        err++;
    }
    if ($('#email').val() != '' && !/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/.test($('#email').val())) {
        buildErrorMessageBlock ("email", "Попробуйте ввести e-mail еще раз или указать другой электронный адрес (e-mail не прошел проверку формата)");
        err++;
    }

    return err;

}

function personalEducAndWork_validation() {
    var err = 0;

    if ($('#currentStatusEducation').val() == '0' && typeTenant) {
        buildErrorMessageBlock ("currentStatusEducation", "Укажите Ваше образование (текущий статус)");
        err++;
    } else {
        // Образование
        if ($('#almamater').val() == '' && typeTenant && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            buildErrorMessageBlock ("almamater", "Укажите учебное заведение");
            err++;
        }
        if ($('#almamater').val().length > 100) {
            buildErrorMessageBlock ("almamater", "Слишком длинное название учебного заведения (используйте не более 100 символов)");
            err++;
        }
        if ($('#speciality').val() == '' && typeTenant && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            buildErrorMessageBlock ("speciality", "Укажите специальность");
            err++;
        }
        if ($('#speciality').val().length > 100) {
            buildErrorMessageBlock ("speciality", "Слишком длинное название специальности (используйте не более 100 символов)");
            err++;
        }
        if ($('#kurs').val() == '' && typeTenant && $('#currentStatusEducation').val() == 'сейчас учусь') {
            buildErrorMessageBlock ("kurs", "Укажите курс обучения");
            err++;
        }
        if ($('#kurs').val().length > 30) {
            buildErrorMessageBlock ("kurs", "Указана слишком длинная строка (используйте не более 30 символов)");
            err++;
        }
        if ($('#ochnoZaochno').val() == '0' && typeTenant && $('#currentStatusEducation').val() == 'сейчас учусь') {
            buildErrorMessageBlock ("ochnoZaochno", "Укажите форму обучения (очная, заочная)");
            err++;
        }
        if ($('#yearOfEnd').val() == '' && typeTenant && $('#currentStatusEducation').val() == 'закончил') {
            buildErrorMessageBlock ("yearOfEnd", "Укажите год окончания учебного заведения");
            err++;
        }
        if ($('#yearOfEnd').val() != '' && !/^[12]{1}[0-9]{3}$/.test($('#yearOfEnd').val())) {
            buildErrorMessageBlock ("yearOfEnd", "Укажите год окончания учебного заведения в формате: \"гггг\". Например: 2007");
            err++;
        }
    }

    // Работа
    if ($('#statusWork').val() == '0' && typeTenant) {
        buildErrorMessageBlock ("statusWork", "Укажите статус занятости");
        err++;
    } else {
        if ($('#placeOfWork').val() == '' && $("#statusWork").val() == 'работаю' && typeTenant) {
            buildErrorMessageBlock ("placeOfWork", "Укажите Ваше место работы (название организации)");
            err++;
        }
        if ($('#placeOfWork').val().length > 100) {
            buildErrorMessageBlock ("placeOfWork", "Слишком длинное наименование места работы (используйте не более 100 символов)");
            err++;
        }
        if ($('#workPosition').val() == '' && $("#statusWork").val() == 'работаю' && typeTenant) {
            buildErrorMessageBlock ("workPosition", "Укажите Вашу должность");
            err++;
        }
        if ($('#workPosition').val().length > 100) {
            buildErrorMessageBlock ("workPosition", "Слишком длинное название должности (используйте не более 100 символов)");
            err++;
        }
    }

    // Коротко о себе
    if ($('#regionOfBorn').val().length > 50) {
        buildErrorMessageBlock ("regionOfBorn", "Слишком длинное наименование региона, в котором Вы родились (используйте не более 50 символов)");
        err++;
    }
    if ($('#cityOfBorn').val().length > 50) {
        buildErrorMessageBlock ("cityOfBorn", "Слишком длинное наименование города, в котором Вы родились (используйте не более 50 символов)");
        err++;
    }

    return err;
}

function personalSocial_validation() {
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

    if ($('#tabs-3 #lic').length && $('#tabs-3 #lic').attr('checked') != "checked") {
        buildErrorMessageBlock ("lic", "Регистрация возможна только при согласии с условиями лицензионного соглашения");
        err++;
    }

    return err;
}

function searchRequest_validation() {
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

     if ($('#tabs-4 #lic').length && $('#tabs-4 #lic').attr('checked') != "checked") {
        buildErrorMessageBlock ("lic", "Регистрация возможна только при согласии с условиями лицензионного соглашения");
        err++;
    }

    return err;
}