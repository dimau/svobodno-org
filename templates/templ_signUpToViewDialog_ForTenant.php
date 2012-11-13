<?php
    // Инициализируем используемые в шаблоне переменные
    $userCharacteristic = $dataArr['userCharacteristic'];
?>

<form method="post" name="signUpToViewDialog" id="signUpToViewDialog"
      title="Записаться на просмотр">

    <table>
        <tbody>
            <tr>
                <td class="itemLabel">
                    ФИО:
                </td>
                <td class="itemBody">
                    <?php
                    echo $userCharacteristic['surname'] . " " . $userCharacteristic['name'] . " " . $userCharacteristic['secondName'];
                    ?>
                </td>
            </tr>
            <tr>
                <td class="itemLabel">
                    Телефон:
                </td>
                <td class="itemBody">
                    <?php
                    if (isset($userCharacteristic['telephon']) && $userCharacteristic['telephon'] != "") echo "+7" . $userCharacteristic['telephon'];
                    ?>
                </td>
            </tr>
            <tr>
                <td class="itemLabel">
                    Удобные даты и время:
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <textarea name="convenientTime" rows="3"
                              placeholder="Например: 15 декабря 15.00 - 17.00" title="Рекомендуем указать несколько вариантов - это позволит нам быстрее договориться с собственником" autofocus></textarea>
                </td>
            </tr>
            <tr>
                <td class="itemLabel">
                    Комментарий:
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <textarea name="comment" rows="3"></textarea>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="bottomButton">
        <a id="signUpToViewDialogCancel" style="margin-right: 10px; cursor: pointer;">Отмена</a>
        <button type="submit" name="signUpToViewDialogButton" id="signUpToViewDialogButton"
                class="button">
            Отправить
        </button>
    </div>

</form>