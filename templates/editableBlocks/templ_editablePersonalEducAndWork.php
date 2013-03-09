<fieldset class="edited left">
    <legend>
        Работа
    </legend>
    <table>
        <tbody>
        <tr>
            <td class="itemLabel">
                Вы работаете?
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <select name="statusWork" id="statusWork">
                    <option value="0" <?php if ($userCharacteristic['statusWork'] == "0") echo "selected";?>></option>
                    <option value="работаю" <?php if ($userCharacteristic['statusWork'] == "работаю") echo "selected";?>>
                        работаю
                    </option>
                    <option value="не работаю" <?php if ($userCharacteristic['statusWork'] == "не работаю") echo "selected";?>>
                        не
                        работаю
                    </option>
                </select>
            </td>
        </tr>
        <tr notavailability="statusWork_0&statusWork_не работаю">
            <td class="itemLabel">
                Место работы
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="placeOfWork"
                       id="placeOfWork" <?php echo "value='" . $userCharacteristic['placeOfWork'] . "'";?>>
            </td>
        </tr>
        <tr notavailability="statusWork_0&statusWork_не работаю">
            <td class="itemLabel">
                Должность
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="workPosition"
                       id="workPosition" <?php echo "value='" . $userCharacteristic['workPosition'] . "'";?>>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>

<fieldset class="edited right">
    <legend>
        Образование
    </legend>
    <table>
        <tbody>
        <tr>
            <td class="itemLabel">
                Вы учитесь?
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <select name="currentStatusEducation" id="currentStatusEducation">
                    <option value="0" <?php if ($userCharacteristic['currentStatusEducation'] == "0") echo "selected";?>></option>
                    <option
                            value="нет" <?php if ($userCharacteristic['currentStatusEducation'] == "нет") echo "selected";?>>
                        Нигде не учился
                    </option>
                    <option
                            value="сейчас учусь" <?php if ($userCharacteristic['currentStatusEducation'] == "сейчас учусь") echo "selected";?>>
                        Сейчас учусь
                    </option>
                    <option
                            value="закончил" <?php if ($userCharacteristic['currentStatusEducation'] == "закончил") echo "selected";?>>
                        Закончил
                    </option>
                </select>
            </td>
        </tr>
        <tr id="almamaterBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет"
            title="Укажите учебное заведение, в котором учитесь сейчас, либо последнее из тех, что заканчивали">
            <td class="itemLabel">
                Учебное заведение
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="almamater" id="almamater"
                       class="ifLearned" <?php echo "value='" . $userCharacteristic['almamater'] . "'";?>>
            </td>
        </tr>
        <tr id="specialityBlock" notavailability="currentStatusEducation_0&currentStatusEducation_нет">
            <td class="itemLabel">
                Специальность
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="speciality" id="speciality"
                       class="ifLearned" <?php echo "value='" . $userCharacteristic['speciality'] . "'";?>>
            </td>
        </tr>
        <tr id="kursBlock"
            notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_закончил"
            title="Укажите курс, на котором учитесь">
            <td class="itemLabel">
                Курс
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="kurs" id="kurs" class="ifLearned"
                       value='<?php echo $userCharacteristic['kurs'];?>'>
            </td>
        </tr>
        <tr id="formatEducation"
            notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_закончил"
            title="Укажите форму обучения">
            <td class="itemLabel">
                Очно / Заочно
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <select name="ochnoZaochno" id="ochnoZaochno" class="ifLearned">
                    <option value="0" <?php if ($userCharacteristic['ochnoZaochno'] == "0") echo "selected";?>></option>
                    <option value="очно" <?php if ($userCharacteristic['ochnoZaochno'] == "очно") echo "selected";?>>
                        Очно
                    </option>
                    <option value="заочно" <?php if ($userCharacteristic['ochnoZaochno'] == "заочно") echo "selected";?>>
                        Заочно
                    </option>
                </select>
            </td>
        </tr>
        <tr id="yearOfEndBlock"
            notavailability="currentStatusEducation_0&currentStatusEducation_нет&currentStatusEducation_сейчас учусь"
            title="Укажите год окончания учебного заведения">
            <td class="itemLabel">
                Год окончания
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="yearOfEnd" id="yearOfEnd"
                       class="ifLearned" value='<?php echo $userCharacteristic['yearOfEnd'];?>'>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>

<fieldset class="edited left">
    <legend>
        Коротко о себе
    </legend>
    <table>
        <tbody>
        <tr>
            <td class="itemLabel">
                В каком регионе родились
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="regionOfBorn" id="regionOfBorn"
                       value='<?php echo $userCharacteristic['regionOfBorn'];?>'>
            </td>
        </tr>
        <tr>
            <td class="itemLabel">
                Родной город, населенный пункт
            </td>
            <td class="itemRequired">
            </td>
            <td class="itemBody">
                <input type="text" name="cityOfBorn" id="cityOfBorn"
                       value='<?php echo $userCharacteristic['cityOfBorn'];?>'>
            </td>
        </tr>
        <tr>
            <td class="itemLabel">
                О себе и своих интересах:
            </td>
            <td class="itemRequired">
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <textarea name="shortlyAboutMe" id="shortlyAboutMe"
                          rows="4"><?php echo $userCharacteristic['shortlyAboutMe'];?></textarea>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>

<div class="clearBoth"></div>