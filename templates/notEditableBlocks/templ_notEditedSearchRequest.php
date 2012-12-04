<div id="notEditingSearchParametersBlock" class="objectDescription">
    <div id="notEditedDistricts" class="notEdited left">
        <div class="legend">
            Район
        </div>
        <table>
            <tbody>
                <?php
                if (isset($userSearchRequest['district']) && count($userSearchRequest['district']) != 0) { // Если район указан пользователем
                    echo "<tr><td>";
                    for ($i = 0, $s = count($userSearchRequest['district']); $i < $s; $i++) { // Выводим названия всех районов, в которых ищет недвижимость пользователь
                        echo $userSearchRequest['district'][$i];
                        if ($i < count($userSearchRequest['district']) - 1) echo ", ";
                    }
                    echo  "</td></tr>";
                } else {
                    echo "<tr><td>" . "любой" . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="notEdited left">
        <div class="legend">
            Характеристика объекта
        </div>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Тип:</td>
                    <td class="objectDescriptionBody">
            <span>
            <?php
                if (isset($userSearchRequest['typeOfObject']) && $userSearchRequest['typeOfObject'] != "0") echo $userSearchRequest['typeOfObject']; else echo "любой";
                ?>
            </span>
                    </td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Количество комнат:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['amountOfRooms']) && count($userSearchRequest['amountOfRooms']) != "0") for ($i = 0, $s = count($userSearchRequest['amountOfRooms']); $i < $s; $i++) {
                            echo $userSearchRequest['amountOfRooms'][$i];
                            if ($i < count($userSearchRequest['amountOfRooms']) - 1) echo ", ";
                        } else echo "любое";
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Комнаты смежные:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['adjacentRooms']) && $userSearchRequest['adjacentRooms'] != "0") echo $userSearchRequest['adjacentRooms']; else echo "любые";
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Этаж:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['floor']) && $userSearchRequest['floor'] != "0") echo $userSearchRequest['floor']; else echo "любой";
                        ?></span></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="notEdited right">
        <div class="legend">
            Стоимость
        </div>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Арендная плата в месяц от:</td>
                    <td class="objectDescriptionBody"><?php
                        if (isset($userSearchRequest['minCost']) && $userSearchRequest['minCost'] != 0) echo "<span>" . $userSearchRequest['minCost'] . "</span> руб."; else echo "любая";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
                    <td class="objectDescriptionBody"><?php
                        if (isset($userSearchRequest['maxCost']) && $userSearchRequest['maxCost'] != 0) echo "<span>" . $userSearchRequest['maxCost'] . "</span> руб."; else echo "любая";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Залог до:</td>
                    <td class="objectDescriptionBody"><?php
                        if (isset($userSearchRequest['pledge']) && $userSearchRequest['pledge'] != 0) echo "<span>" . $userSearchRequest['pledge'] . "</span> руб."; else echo "любой";
                        ?></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Максимальная предоплата:</td>
                    <td class="objectDescriptionBody"><?php
                        if (isset($userSearchRequest['prepayment']) && $userSearchRequest['prepayment'] != 0) echo "<span>" . $userSearchRequest['prepayment'] . "</span>"; else echo "любая";
                        ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="notEditedSpecialParams" class="notEdited left" style="width: 100%;">
        <div class="legend">
            Особые параметры поиска
        </div>
        <table>
            <tbody>
                <tr>
                    <td class="objectDescriptionItemLabel">Как собираетесь проживать:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['withWho']) && $userSearchRequest['withWho'] != "0") echo $userSearchRequest['withWho']; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
                if ($userSearchRequest['withWho'] != "самостоятельно" && $userSearchRequest['withWho'] != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Информация о сожителях:</td><td class='objectDescriptionBody''><span>";
                    if (isset($userSearchRequest['linksToFriends'])) echo $userSearchRequest['linksToFriends'];
                    echo "</span></td></tr>";
                }
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Дети:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['children']) && $userSearchRequest['children'] != "0") echo $userSearchRequest['children']; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
                if ($userSearchRequest['children'] != "без детей" && $userSearchRequest['children'] != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Количество детей и их возраст:</td><td class='objectDescriptionBody''><span>";
                    if (isset($userSearchRequest['howManyChildren'])) echo $userSearchRequest['howManyChildren'];
                    echo "</span></td></tr>";
                }
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Животные:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['animals']) && $userSearchRequest['animals'] != "0") echo $userSearchRequest['animals']; else echo "не указано";
                        ?></span></td>
                </tr>
                <?php
                if ($userSearchRequest['animals'] != "без животных" && $userSearchRequest['animals'] != "0") {
                    echo "<tr><td class='objectDescriptionItemLabel'>Количество животных и их вид:</td><td class='objectDescriptionBody''><span>";
                    if (isset($userSearchRequest['howManyAnimals'])) echo $userSearchRequest['howManyAnimals'];
                    echo "</span></td></tr>";
                }
                ?>
                <tr>
                    <td class="objectDescriptionItemLabel">Срок аренды:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['termOfLease']) && $userSearchRequest['termOfLease'] != "0") echo $userSearchRequest['termOfLease']; else echo "не указан";
                        ?></span></td>
                </tr>
                <tr>
                    <td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
                    <td class="objectDescriptionBody"><span><?php
                        if (isset($userSearchRequest['additionalDescriptionOfSearch'])) echo $userSearchRequest['additionalDescriptionOfSearch'];
                        ?></span></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="clearBoth"></div>
</div>