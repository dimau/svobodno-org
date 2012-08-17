
// Подгонка размера правого блока параметров (районы) расширенного поиска под размер левого блока параметров. 19 пикселей - на padding у fieldset
document.getElementById('rightBlockOfSearchParameters').style.height = document.getElementById('leftBlockOfSearchParameters').offsetHeight - 22 + 'px';

/* Если в форме Работа указан чекбокс - не работаю, то блокировать заполнение остальных инпутов */
$("#notWorkCheckbox").on('change', function() {
    if ($("input.ifWorked").attr('disabled') == 'disabled') {
        $("input.ifWorked").removeAttr('disabled').css('color', '');
        $("div.searchItem.ifWorked div.required").text("*");

    } else {
        $("input.ifWorked").attr('disabled', 'disabled').css('color', 'grey');
        $("div.searchItem.ifWorked div.required").text("");
    }
});

/* Если в форме Образование указан чекбокс - не учился, то блокировать заполнение остальных инпутов */
$("#currentStatusEducation").change(function() {
    var currentValue = $("#currentStatusEducation option:selected").attr('value');
    if (currentValue == 0) {
        $("input.ifLearned, select.ifLearned").removeAttr('disabled').css('color', '');
        $("div.searchItem.ifLearned div.required").text("*");
    }
    if (currentValue == 1) {
        $("input.ifLearned, select.ifLearned").attr('disabled', 'disabled').css('color', 'grey');
        $("div.searchItem.ifLearned div.required").text("");
    }
    if (currentValue == 2) {
        $("input.ifLearned, select.ifLearned").removeAttr('disabled').css('color', '');
        $("div.searchItem.ifLearned div.required").text("*");
        $('#kurs').css('display', '');
        $('#yearOfEnd').css('display', 'none');
    }
    if (currentValue == 3) {
        $("input.ifLearned, select.ifLearned").removeAttr('disabled').css('color', '');
        $("div.searchItem.ifLearned div.required").text("*");
        $('#kurs').css('display', 'none');
        $('#yearOfEnd').css('display', '');
    }
});


/* Сценарии для появления блока с подробным описанием сожителей */
$("#withWho").on('change', function(event) {
    if ($("#withWho").attr('value') != 1) {
        $("#withWhoDescription").css('display', '');
    } else {
        $("#withWhoDescription").css('display', 'none');
    }
});

/* Сценарии для появления блока с подробным описанием детей */
$("#children").on('change', function(event) {
    if ($("#children").attr('value') != 0) {
        $("#childrenDescription").css('display', '');
    } else {
        $("#childrenDescription").css('display', 'none');
    }
});

/* Сценарии для появления блока с подробным описанием животных */
$("#animals").on('change', function(event) {
    if ($("#animals").attr('value') != 0) {
        $("#animalsDescription").css('display', '');
    } else {
        $("#animalsDescription").css('display', 'none');
    }
});

// Подготовим возможность загрузки фотографий
function createUploader(){
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: '../lib/uploader.php',
        allowedExtensions: ["jpeg", "jpg", "img", "bmp"], //Также расширения нужно менять в файле uploader.php
        sizeLimit: 10 * 1024 * 1024,
        //debug: true,
        //extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]]
    });
}
// in your app create uploader as soon as the DOM is ready
// don't wait for the window to load
$(document).ready(createUploader);

