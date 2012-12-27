<div id="extendedSearchParametersBlock">
<fieldset class="edited left">
    <legend>
        Характеристика объекта
    </legend>
    <table>
        <tbody>
        <tr>
            <td class="itemLabel">
                Тип
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <select name="typeOfObject" id="typeOfObject">
                    <option value="0" <?php if ($userSearchRequest['typeOfObject'] == "0") echo "selected";?>></option>
                    <option value="квартира" <?php if ($userSearchRequest['typeOfObject'] == "квартира") echo "selected";?>>
                        квартира
                    </option>
                    <option value="комната" <?php if ($userSearchRequest['typeOfObject'] == "комната") echo "selected";?>>
                        комната
                    </option>
                    <option value="дом" <?php if ($userSearchRequest['typeOfObject'] == "дом") echo "selected";?>>дом,
                        коттедж
                    </option>
                    <option value="таунхаус" <?php if ($userSearchRequest['typeOfObject'] == "таунхаус") echo "selected";?>>
                        таунхаус
                    </option>
                    <option value="дача" <?php if ($userSearchRequest['typeOfObject'] == "дача") echo "selected";?>>дача
                    </option>
                    <option value="гараж" <?php if ($userSearchRequest['typeOfObject'] == "гараж") echo "selected";?>>
                        гараж
                    </option>
                </select>
            </td>
        </tr>
        <tr notavailability="typeOfObject_гараж">
            <td class="itemLabel">
                Количество комнат
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <label><input type="checkbox" value="1" name="amountOfRooms[]"
					<?php
					foreach ($userSearchRequest['amountOfRooms'] as $value) {
						if ($value == "1") {
							echo "checked";
							break;
						}
					}
					?>>
                    1</label>
                <label><input type="checkbox" value="2"
                              name="amountOfRooms[]" <?php
					foreach ($userSearchRequest['amountOfRooms'] as $value) {
						if ($value == "2") {
							echo "checked";
							break;
						}
					}
					?>>
                    2</label>
                <label><input type="checkbox" value="3"
                              name="amountOfRooms[]" <?php
					foreach ($userSearchRequest['amountOfRooms'] as $value) {
						if ($value == "3") {
							echo "checked";
							break;
						}
					}
					?>>
                    3</label>
                <label><input type="checkbox" value="4"
                              name="amountOfRooms[]" <?php
					foreach ($userSearchRequest['amountOfRooms'] as $value) {
						if ($value == "4") {
							echo "checked";
							break;
						}
					}
					?>>
                    4</label>
                <label><input type="checkbox" value="5"
                              name="amountOfRooms[]" <?php
					foreach ($userSearchRequest['amountOfRooms'] as $value) {
						if ($value == "5") {
							echo "checked";
							break;
						}
					}
					?>>
                    5</label>
                <label><input type="checkbox" value="6"
                              name="amountOfRooms[]" <?php
					foreach ($userSearchRequest['amountOfRooms'] as $value) {
						if ($value == "6") {
							echo "checked";
							break;
						}
					}
					?>>
                    6...</label>
            </td>
        </tr>
        <tr notavailability="typeOfObject_гараж">
            <td class="itemLabel">
                Комнаты смежные
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <select name="adjacentRooms" id="adjacentRooms">
                    <option value="0" <?php if ($userSearchRequest['adjacentRooms'] == "0") echo "selected";?>></option>
                    <option
                            value="не имеет значения" <?php if ($userSearchRequest['adjacentRooms'] == "не имеет значения") echo "selected";?>>
                        не
                        имеет значения
                    </option>
                    <option
                            value="только изолированные" <?php if ($userSearchRequest['adjacentRooms'] == "только изолированные") echo "selected";?>>
                        только изолированные
                    </option>
                </select>
            </td>
        </tr>
        <tr notavailability="typeOfObject_дом&typeOfObject_таунхаус&typeOfObject_дача&typeOfObject_гараж">
            <td class="itemLabel">
                Этаж
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <select name="floor" id="floor">
                    <option value="0" <?php if ($userSearchRequest['floor'] == "0") echo "selected";?>></option>
                    <option value="любой" <?php if ($userSearchRequest['floor'] == "любой") echo "selected";?>>любой
                    </option>
                    <option value="не первый" <?php if ($userSearchRequest['floor'] == "не первый") echo "selected";?>>
                        не
                        первый
                    </option>
                    <option
                            value="не первый и не последний" <?php if ($userSearchRequest['floor'] == "не первый и не последний") echo "selected";?>>
                        не первый и не
                        последний
                    </option>
                </select>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>

<fieldset class="edited right cost">
    <legend>
        Стоимость
    </legend>
    <table>
        <tbody>
        <tr title="В месяц за аренду недвижимости с учетом стоимости коммунальных услуг (если они оплачиваются дополнительно)">
            <td class="itemLabel">
                Арендная плата от
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="minCost" id="minCost"
                       maxlength="8" value='<?php echo $userSearchRequest['minCost'];?>'>
                руб.
            </td>
        </tr>
        <tr title="В месяц за аренду недвижимости с учетом стоимости коммунальных услуг (если они оплачиваются дополнительно)">
            <td class="itemLabel">
                Арендная плата до
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="maxCost" id="maxCost"
                       maxlength="8" value='<?php echo $userSearchRequest['maxCost'];?>'>
                руб.
            </td>
        </tr>
        <tr title="Какую сумму Вы готовы передать собственнику в качестве возвращаемого гарантийного депозита">
            <td class="itemLabel">
                Залог до
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="pledge" id="pledge"
                       maxlength="8" value='<?php echo $userSearchRequest['pledge'];?>'>
                руб.
            </td>
        </tr>
        <tr title="Какую предоплату за проживание Вы готовы внести">
            <td class="itemLabel">
                Макс. предоплата
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <select name="prepayment" id="prepayment">
                    <option value="0" <?php if ($userSearchRequest['prepayment'] == "0") echo "selected";?>></option>
                    <option value="нет" <?php if ($userSearchRequest['prepayment'] == "нет") echo "selected";?>>нет
                    </option>
                    <option value="1 месяц" <?php if ($userSearchRequest['prepayment'] == "1 месяц") echo "selected";?>>
                        1
                        месяц
                    </option>
                    <option value="2 месяца" <?php if ($userSearchRequest['prepayment'] == "2 месяца") echo "selected";?>>
                        2
                        месяца
                    </option>
                    <option value="3 месяца" <?php if ($userSearchRequest['prepayment'] == "3 месяца") echo "selected";?>>
                        3
                        месяца
                    </option>
                    <option value="4 месяца" <?php if ($userSearchRequest['prepayment'] == "4 месяца") echo "selected";?>>
                        4
                        месяца
                    </option>
                    <option value="5 месяцев" <?php if ($userSearchRequest['prepayment'] == "5 месяцев") echo "selected";?>>
                        5
                        месяцев
                    </option>
                    <option value="6 месяцев" <?php if ($userSearchRequest['prepayment'] == "6 месяцев") echo "selected";?>>
                        6
                        месяцев
                    </option>
                </select>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>

<fieldset class="edited districts left">
    <legend>
        Район
    </legend>
    <ul>
		<?php
		if (isset($allDistrictsInCity)) {
			foreach ($allDistrictsInCity as $value) { // Для каждого идентификатора района и названия формируем чекбокс
				echo "<li><label><input type='checkbox' name='district[]' value='" . $value['name'] . "'";
				foreach ($userSearchRequest['district'] as $valueDistrict) {
					if ($valueDistrict == $value['name']) {
						echo "checked";
						break;
					}
				}
				echo "> " . $value['name'] . "</label></li>";
			}
		}
		?>
    </ul>
    <div class="clearBoth"></div>
</fieldset>

<fieldset class="edited right">
    <legend>
        Особые параметры поиска
    </legend>
    <table>
        <tbody>
        <tr notavailability="typeOfObject_гараж">
            <td class="itemLabel">
                Как собираетесь проживать
            </td>
			<?php
			if ($mode == "search") echo "<td class='itemRequired'></td>"; else echo "<td class='itemRequired typeTenantRequired'>*</td>";
			?>
            <td class="itemBody">
                <select name="withWho" id="withWho">
                    <option value="0" <?php if ($userSearchRequest['withWho'] == "0") echo "selected";?>></option>
                    <option
                            value="самостоятельно" <?php if ($userSearchRequest['withWho'] == "самостоятельно") echo "selected";?>>
                        самостоятельно
                    </option>
                    <option value="семья" <?php if ($userSearchRequest['withWho'] == "семья") echo "selected";?>>семьей
                    </option>
                    <option value="пара" <?php if ($userSearchRequest['withWho'] == "пара") echo "selected";?>>парой
                    </option>
                    <option value="2 мальчика" <?php if ($userSearchRequest['withWho'] == "2 мальчика") echo "selected";?>>
                        2
                        мальчика
                    </option>
                    <option value="2 девочки" <?php if ($userSearchRequest['withWho'] == "2 девочки") echo "selected";?>>
                        2
                        девочки
                    </option>
                    <option value="со знакомыми" <?php if ($userSearchRequest['withWho'] == "со знакомыми") echo "selected";?>>
                        со
                        знакомыми
                    </option>
                </select>
            </td>
        </tr>
        <tr class="withWhoDescription" style="display: none;">
            <td class="itemLabel" colspan="3">
                Что Вы можете сказать о сожителях:
            </td>
        </tr>
        <tr class="withWhoDescription" style="display: none;">
            <td colspan="3">
                <textarea name="linksToFriends" id="linksToFriends"
                          rows="3"><?php echo $userSearchRequest['linksToFriends'];?></textarea>
            </td>
        </tr>

        <tr notavailability="typeOfObject_гараж">
            <td class="itemLabel">
                Дети
            </td>
			<?php
			if ($mode == "search") echo "<td class='itemRequired'></td>"; else echo "<td class='itemRequired typeTenantRequired'>*</td>";
			?>
            <td class="itemBody">
                <select name="children" id="children">
                    <option value="0" <?php if ($userSearchRequest['children'] == "0") echo "selected";?>></option>
                    <option value="без детей" <?php if ($userSearchRequest['children'] == "без детей") echo "selected";?>>
                        без
                        детей
                    </option>
                    <option
                            value="с детьми младше 4-х лет" <?php if ($userSearchRequest['children'] == "с детьми младше 4-х лет") echo "selected";?>>
                        с детьми
                        младше 4-х лет
                    </option>
                    <option
                            value="с детьми старше 4-х лет" <?php if ($userSearchRequest['children'] == "с детьми старше 4-х лет") echo "selected";?>>
                        с детьми
                        старше 4-х лет
                    </option>
                </select>
            </td>
        </tr>
        <tr class="childrenDescription" style="display: none;">
            <td class="itemLabel" colspan="3">
                Сколько у Вас детей и какого возраста:
            </td>
        </tr>
        <tr class="childrenDescription" style="display: none;">
            <td colspan="3">
                <textarea name="howManyChildren" id="howManyChildren"
                          rows="3"><?php echo $userSearchRequest['howManyChildren'];?></textarea>
            </td>
        </tr>

        <tr notavailability="typeOfObject_гараж">
            <td class="itemLabel">
                Домашние животные
            </td>
			<?php
			if ($mode == "search") echo "<td class='itemRequired'></td>"; else echo "<td class='itemRequired typeTenantRequired'>*</td>";
			?>
            <td class="itemBody">
                <select name="animals" id="animals">
                    <option value="0" <?php if ($userSearchRequest['animals'] == "0") echo "selected";?>></option>
                    <option value="без животных" <?php if ($userSearchRequest['animals'] == "без животных") echo "selected";?>>
                        без
                        животных
                    </option>
                    <option
                            value="с животным(ми)" <?php if ($userSearchRequest['animals'] == "с животным(ми)") echo "selected";?>>
                        с
                        животным(ми)
                    </option>
                </select>
            </td>
        </tr>
        <tr class="animalsDescription" style="display: none;">
            <td class="itemLabel" colspan="3">
                Сколько у Вас животных и какого вида:
            </td>
        </tr>
        <tr class="animalsDescription" style="display: none;">
            <td colspan="3">
                <textarea name="howManyAnimals" id="howManyAnimals"
                          rows="3"><?php echo $userSearchRequest['howManyAnimals'];?></textarea>
            </td>
        </tr>

        <tr>
            <td class="itemLabel">
                Срок аренды
            </td>
			<?php
			if ($mode == "search") echo "<td class='itemRequired'></td>"; else echo "<td class='itemRequired typeTenantRequired'>*</td>";
			?>
            <td class="itemBody">
                <select name="termOfLease" id="termOfLease">
                    <option value="0" <?php if ($userSearchRequest['termOfLease'] == "0") echo "selected";?>></option>
                    <option
                            value="длительный срок" <?php if ($userSearchRequest['termOfLease'] == "длительный срок") echo "selected";?>>
                        длительный срок (от года)
                    </option>
                    <option
                            value="несколько месяцев" <?php if ($userSearchRequest['termOfLease'] == "несколько месяцев") echo "selected";?>>
                        несколько месяцев (до года)
                    </option>
                </select>
            </td>
        </tr>

		<?php if ($mode == "registration" || $mode == "personal"): ?>
        <tr class="additionalSearchConditions">
            <td class="itemLabel" colspan="3">
                Дополнительные условия поиска:
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <textarea name="additionalDescriptionOfSearch" id="additionalDescriptionOfSearch"
                          rows="4"><?php echo $userSearchRequest['additionalDescriptionOfSearch'];?></textarea>
            </td>
        </tr>
		<?php endif; ?>

        </tbody>
    </table>

</fieldset>

<?php if ($mode == "registration" || $mode == "personal"): ?>
<div class="edited right" style="margin: 0; padding: 0; border: none; text-align: right;">
	<label><input type="checkbox" value="1" name="needEmail" <?php if ($userSearchRequest['needEmail'] == 1) echo "checked";?>> Оповещать меня по e-mail о подходящих объявлениях</label>
</div>
<?php endif; ?>

<div class="clearBoth"></div>

</div>