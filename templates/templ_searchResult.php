<?php
// Для работы шаблона требуются переменные
//$typeOfRequest
//$matterOfBalloonList
//$matterOfShortList
//$matterOfFullParametersList
?>

<!-- Элементы управления для выбора формы представления результатов выдачи (карта, список, карта + список) -->
<div class='choiceViewSearchResult'>
    <span id='expandList'>
        <a href='#'>Список</a>&nbsp;&nbsp;&nbsp;
    </span>
    <span id='listPlusMap'>
        <a href='#'>Список + карта</a>&nbsp;&nbsp;&nbsp;
    </span>
    <span id='expandMap'>
        <a href='#'>Карта</a>
    </span>
</div>

<div id='resultOnSearchPage' style='height: 100%;'>

    <!-- Блоки с баллунами для Яндекс карты -->
    <div id='allBalloons' style='display: none;'>
        <?php
        if ($matterOfBalloonList != "") {
            echo $matterOfBalloonList; // Вставляем HTML-текст баллунов для Яндекс карты объявлений по недвижимости с короткими данными и данными для баллунов на Яндекс карте
        } else {
            // Если ничего не нашли то блок allBalloons будет пустым
        }
        ?>
    </div>

    <!-- Блок для списка объектов с кратким описанием (представление: Список + Карта) -->
    <div class='listOfRealtyObjects' id='shortListOfRealtyObjects'>
        <?php
        if ($matterOfShortList != "") {
            echo $matterOfShortList; // Вставляем HTML-текст объявлений по недвижимости с короткими данными и данными для баллунов на Яндекс карте
        } else {
            // Если ничего не нашли, выдаем вместо пустого результата:
            echo $this->searchResultIsEmptyHTML($typeOfRequest);
        }
        ?>
    </div>

    <!-- Область показа карты -->
    <div id='map'></div>

    <!-- Раздел с подробными сведения по каждому объявлению -->
    <div class='clearBoth'></div>
    <div class='listOfRealtyObjects' id='fullParametersListOfRealtyObjects'
         style='display: none; width: 100%; float:none;'>
        <div id='headOfFullParametersList'>
            <div class='serviceMarks top left'></div>
            <div class="overFotosWrapper">Фото</div>
            <div class="mainContent"><div class="address">Адрес</div><div class="amountOfRooms">Комнаты</div><div class="areaValues">Площадь</div><div class="floor">Этаж</div><div class="furniture">Мебель</div><div class='costOfRenting top right'>Цена</div><div class="clearBoth"></div></div>
        </div>
        <?php
        if ($matterOfFullParametersList != "") {
            echo $matterOfFullParametersList; // Формируем содержимое таблицы со списком объявлений и расширенными данными по ним
        } else {
            // Если ничего не нашли, выдаем вместо пустого результата:
            echo $this->searchResultIsEmptyHTML($typeOfRequest);
        }
        ?>
    </div>

</div>