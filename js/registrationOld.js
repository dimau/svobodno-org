// Активируем и настраиваем слайдер, содержащий регистрационные поля
$(document).ready(function () {
    $('#slider').rhinoslider({
        controlsPlayPause:false,
        showControls:'always',
        showBullets:'always',
        controlsMousewheel:false,
        prevText:'Назад',
        nextText:'Далее',
        slidePrevDirection:'toRight',
        slideNextDirection:'toLeft'
    });

    $(".rhino-prev").hide();
    $('.rhino-next').after('<a class="form-submit" href="javascript:void(0);" >Далее</a>');
    $(".rhino-next").hide();

    var info = ["Шаг 1<br>Личные данные", "Шаг 2<br>Образование / Работа", "Шаг 3<br>Социальные сети", "Шаг 4<br>Что ищете?"];
    $('.rhino-bullet').each(function (index) {
        $(this).html(info[index]);
    });

});

$('.form-submit').live("click", function () {

    $('.form-error').html("");

    var current_tab = $('#slider').find('.rhino-active').attr("id");

    switch (current_tab) {
        case 'rhino-item0':
            step1_validation();
            break;
        case 'rhino-item1':
            step2_validation();
            break;
        case 'rhino-item2':
            step3_validation();
            break;
        case 'rhino-item3':
            step4_validation();
            break;
    }
});

var step1_validation = function () {

    var err = 0;

    // ФИО
    if ($('#surname').val() == '') {
        $('#surname').parent().parent().find('.form-error').html("Укажите фамилию");
        err++;
    }
    if ($('#surname').val().length > 50) {
        $('#surname').parent().parent().find('.form-error').html("Слишком длинная фамилия. Можно указать не более 50-ти символов");
        err++;
    }
    if ($('#name').val() == '') {
        $('#name').parent().parent().find('.form-error').html("Укажите имя");
        err++;
    }
    if ($('#name').val().length > 50) {
        $('#name').parent().parent().find('.form-error').html("Слишком длинное имя. Можно указать не более 50-ти символов");
        err++;
    }
    if ($('#secondName').val() == '') {
        $('#secondName').parent().parent().find('.form-error').html("Укажите отчество");
        err++;
    }
    if ($('#secondName').val().length > 50) {
        $('#secondName').parent().parent().find('.form-error').html("Слишком длинное отчество. Можно указать не более 50-ти символов");
        err++;
    }

    // Пол, внешность, ДР
    if ($('#sex').val() == '0') {
        $('#sex').parent().parent().find('.form-error').html("Укажите пол");
        err++;
    }
    if ($('#nationality').val() == '0') {
        $('#nationality').parent().parent().find('.form-error').html("Укажите внешность");
        err++;
    }
    if ($('#birthday').val() == '') {
        $('#birthday').parent().parent().find('.form-error').html("Укажите дату рождения");
        err++;
    } else {
        if (!/^\d\d.\d\d.\d\d\d\d$/.test($('#birthday').val())) {
            $('#birthday').parent().parent().find('.form-error').html("Неправильный формат даты рождения, должен быть: дд.мм.гггг, например: 01.01.1980");
            err++;
        } else {
            if ($('#birthday').val().slice(0, 2) < "01" || $('#birthday').val().slice(0, 2) > "31") {
                $('#birthday').parent().parent().find('.form-error').html("Проверьте дату Дня рождения (допустимо от 01 до 31)");
                err++;
            }
            if ($('#birthday').val().slice(3, 5) < "01" || $('#birthday').val().slice(3, 5) > "12") {
                $('#birthday').parent().parent().find('.form-error').html("Проверьте месяц Дня рождения (допустимо от 01 до 12)");
                err++;
            }
            if ($('#birthday').val().slice(6) < "1800" || $('#birthday').val().slice(6) > "2100") {
                $('#birthday').parent().parent().find('.form-error').html("Проверьте год Дня рождения (допустимо от 1800 до 2100)");
                err++;
            }
        }
    }

    // Логин и пароль
    if ($('#login').val() == '') {
        $('#login').parent().parent().find('.form-error').html("Укажите логин");
        err++;
    }
    if ($('#login').val().length > 50) {
        $('#login').parent().parent().find('.form-error').html("Слишком длинный логин. Можно указать не более 50-ти символов");
        err++;
    }
    if ($('#password').val() == '') {
        $('#password').parent().parent().find('.form-error').html("Укажите пароль");
        err++;
    }

    // Телефон и e-mail
    if ($('#telephon').val() == '') {
        $('#telephon').parent().parent().find('.form-error').html("Укажите контактный (мобильный) телефон");
        err++;
    } else {
        if (!/^[0-9]{10}$/.test($('#telephon').val())) {
            $('#telephon').parent().parent().find('.form-error').html("Укажите, пожалуйста, Ваш мобильный номер без 8-ки, например: 9226470019");
            err++;
        }
    }
    if ($('#email').val() == '' && $(".userType").attr('typeTenant') == 'true') {
        $('#email').parent().parent().find('.form-error').html("Укажите e-mail");
        err++;
    }
    if ($('#email').val() != '' && !/^(([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+\.)*([a-zA-Z0-9_-]|[!#$%\*\/\?\|^\{\}`~&'\+=])+@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]{2,5}$/.test($('#email').val())) {
        $('#email').parent().parent().find('.form-error').html("Попробуйте ввести e-mail еще раз или указать другой электронный адрес (e-mail не прошел проверку формата)");
        err++;
    }

    if (err == 0) {
        $(".rhino-active-bullet").removeClass("step-error").addClass("step-success");
        $(".rhino-next").show();
        $('.form-submit').hide();
        $('.rhino-next').trigger('click');
    } else {
        $(".rhino-active-bullet").removeClass("step-success").addClass("step-error");
    }

};

var step2_validation = function () {
    var err = 0;

    if ($('#currentStatusEducation').val() == '0' && $(".userType").attr('typeTenant') == 'true') {
        $('#currentStatusEducation').parent().parent().find('.form-error').html("Укажите Ваше образование (текущий статус)");
        err++;
    } else {
        // Образование
        if ($('#almamater').val() == '' && $(".userType").attr('typeTenant') == 'true' && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            $('#almamater').parent().parent().find('.form-error').html("Укажите учебное заведение");
            err++;
        }
        if ($('#almamater').val().length > 100) {
            $('#almamater').parent().parent().find('.form-error').html("Слишком длинное название учебного заведения (используйте не более 100 символов)");
            err++;
        }
        if ($('#speciality').val() == '' && $(".userType").attr('typeTenant') == 'true' && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            $('#speciality').parent().parent().find('.form-error').html("Укажите специальность");
            err++;
        }
        if ($('#speciality').val().length > 100) {
            $('#speciality').parent().parent().find('.form-error').html("Слишком длинное название специальности (используйте не более 100 символов)");
            err++;
        }
        if ($('#kurs').val() == '' && $(".userType").attr('typeTenant') == 'true' && $('#currentStatusEducation').val() == 'сейчас учусь') {
            $('#kurs').parent().parent().find('.form-error').html("Укажите курс обучения");
            err++;
        }
        if ($('#kurs').val().length > 30) {
            $('#kurs').parent().parent().find('.form-error').html("Курс. Указана слишком длинная строка (используйте не более 30 символов)");
            err++;
        }
        if ($('#ochnoZaochno').val() == '0' && $(".userType").attr('typeTenant') == 'true' && ( $('#currentStatusEducation').val() == 'сейчас учусь' || $('#currentStatusEducation').val() == "закончил"  )) {
            $('#ochnoZaochno').parent().parent().find('.form-error').html("Укажите форму обучения (очная, заочная)");
            err++;
        }
        if ($('#yearOfEnd').val() == '' && $(".userType").attr('typeTenant') == 'true' && $('#currentStatusEducation').val() == 'закончил') {
            $('#yearOfEnd').parent().parent().find('.form-error').html("Укажите год окончания учебного заведения");
            err++;
        }
        if ($('#yearOfEnd').val() != '' && !/^[12]{1}[0-9]{3}$/.test($('#yearOfEnd').val())) {
            $('#yearOfEnd').parent().parent().find('.form-error').html("Укажите год окончания учебного заведения в формате: \"гггг\". Например: 2007");
            err++;
        }
    }

    // Работа
    if ($('#statusWork').val() == '0' && $(".userType").attr('typeTenant') == 'true') {
        $('#statusWork').parent().parent().find('.form-error').html("Укажите статус занятости");
        err++;
    } else {
        if ($('#placeOfWork').val() == '' && $("#statusWork").val() == 'работаю' && $(".userType").attr('typeTenant') == 'true') {
            $('#placeOfWork').parent().parent().find('.form-error').html("Укажите Ваше место работы (название организации)");
            err++;
        }
        if ($('#placeOfWork').val().length > 100) {
            $('#placeOfWork').parent().parent().find('.form-error').html("Слишком длинное наименование места работы (используйте не более 100 символов)");
            err++;
        }
        if ($('#workPosition').val() == '' && $("#statusWork").val() == 'работаю' && $(".userType").attr('typeTenant') == 'true') {
            $('#workPosition').parent().parent().find('.form-error').html("Укажите Вашу должность");
            err++;
        }
        if ($('#workPosition').val().length > 100) {
            $('#workPosition').parent().parent().find('.form-error').html("Слишком длинное название должности (используйте не более 100 символов)");
            err++;
        }
    }

    // Коротко о себе
    if ($('#regionOfBorn').val() == '' && $(".userType").attr('typeTenant') == 'true') {
        $('#regionOfBorn').parent().parent().find('.form-error').html("Укажите регион, в котором Вы родились");
        err++;
    }
    if ($('#regionOfBorn').val().length > 50) {
        $('#regionOfBorn').parent().parent().find('.form-error').html("Слишком длинное наименование региона, в котором Вы родились (используйте не более 50 символов)");
        err++;
    }
    if ($('#cityOfBorn').val() == '' && $(".userType").attr('typeTenant') == 'true') {
        $('#cityOfBorn').parent().parent().find('.form-error').html("Укажите город (населенный пункт), в котором Вы родились");
        err++;
    }
    if ($('#cityOfBorn').val().length > 50) {
        $('#cityOfBorn').parent().parent().find('.form-error').html("Слишком длинное наименование города, в котором Вы родились (используйте не более 50 символов)");
        err++;
    }

    if (err == 0) {
        $(".rhino-active-bullet").removeClass("step-error").addClass("step-success");
        $(".rhino-next").show();
        $('.form-submit').hide();
        $('.rhino-next').trigger('click');
    } else {
        $(".rhino-active-bullet").removeClass("step-success").addClass("step-error");
    }
};

var step3_validation = function () {
    var err = 0;

    if ($('#vkontakte').val().length > 100) {
        $('#vkontakte').parent().parent().find('.form-error').html("Указана слишком длинная ссылка на личную страницу Вконтакте (используйте не более 100 символов)");
        err++;
    }
    if ($('#vkontakte').val() != '' && !/vk\.com/.test($('#vkontakte').val())) {
        $('#vkontakte').parent().parent().find('.form-error').html("Укажите, пожалуйста, Вашу настоящую личную страницу Вконтакте, либо оставьте поле пустым (ссылка должна содержать строчку \"vk.com\")");
        err++;
    }
    if ($('#odnoklassniki').val().length > 100) {
        $('#odnoklassniki').parent().parent().find('.form-error').html("Указана слишком длинная ссылка на личную страницу в Одноклассниках (используйте не более 100 символов)");
        err++;
    }
    if ($('#odnoklassniki').val() != '' && !/www\.odnoklassniki\.ru\/profile\//.test($('#odnoklassniki').val())) {
        $('#odnoklassniki').parent().parent().find('.form-error').html("Укажите, пожалуйста, Вашу настоящую личную страницу в Одноклассниках, либо оставьте поле пустым (ссылка должна содержать строчку \"www.odnoklassniki.ru/profile/\")");
        err++;
    }
    if ($('#facebook').val().length > 100) {
        $('#facebook').parent().parent().find('.form-error').html("Указана слишком длинная ссылка на личную страницу на Facebook (используйте не более 100 символов)");
        err++;
    }
    if ($('#facebook').val() != '' && !/www\.facebook\.com\/profile\.php/.test($('#facebook').val())) {
        $('#facebook').parent().parent().find('.form-error').html("Укажите, пожалуйста, Вашу настоящую личную страницу на Facebook, либо оставьте поле пустым (ссылка должна содержать строчку с \"www.facebook.com/profile.php\")");
        err++;
    }
    if ($('#twitter').val().length > 100) {
        $('#twitter').parent().parent().find('.form-error').html("Указана слишком длинная ссылка на личную страницу в Twitter (используйте не более 100 символов)");
        err++;
    }
    if ($('#twitter').val() != '' && !/twitter\.com/.test($('#twitter').val())) {
        $('#twitter').parent().parent().find('.form-error').html("Укажите, пожалуйста, Вашу настоящую личную страницу в Twitter, либо оставьте поле пустым (ссылка должна содержать строчку \"twitter.com\")");
        err++;
    }

    if (err == 0) {
        $(".rhino-active-bullet").removeClass("step-error").addClass("step-success");
        $(".rhino-next").show();
        $('.form-submit').hide();
        $('.rhino-next').trigger('click');
    } else {
        $(".rhino-active-bullet").removeClass("step-success").addClass("step-error");
    }
};

var step4_validation = function () {
    var err = 0;


    if (err == 0) {
        $(".rhino-active-bullet").removeClass("step-error").addClass("step-success");
        $(".rhino-next").hide();
        $('.form-submit').show();
        $('.rhino-next').trigger('click');
    } else {
        $(".rhino-active-bullet").removeClass("step-success").addClass("step-error");
    }
};


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
