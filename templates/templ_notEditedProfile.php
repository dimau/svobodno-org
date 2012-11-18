<?php
    // Инициализируем используемые в шаблоне переменные
    $userCharacteristic = $dataArr['userCharacteristic'];
    $mode = $dataArr['mode']; // Режим отображения данных: personal - все анкетные данные пользователя (для личного использования), tenantForOwner - все анкетные данные кроме контактов (чтобы собственник не мог связаться с арендатором напрямую без нас)
?>

<div class="profileInformation">
    <ul class="listDescriptionBig">
        <li>
            <span class="FIO"><?php echo $userCharacteristic['surname'] . " " . $userCharacteristic['name'] . " " . $userCharacteristic['secondName'] ?></span>
        </li>
        <li>
            <br>
        </li>
        <li>
            <span class="headOfString">Образование:</span> <?php
            if ($userCharacteristic['currentStatusEducation'] == "0") {
                echo "";
            }
            if ($userCharacteristic['currentStatusEducation'] == "нет") {
                echo "нет";
            }
            if ($userCharacteristic['currentStatusEducation'] == "сейчас учусь") {
                if (isset($userCharacteristic['almamater'])) echo $userCharacteristic['almamater'] . ", ";
                if (isset($userCharacteristic['speciality'])) echo $userCharacteristic['speciality'] . ", ";
                if (isset($userCharacteristic['ochnoZaochno'])) echo $userCharacteristic['ochnoZaochno'] . ", ";
                if (isset($userCharacteristic['kurs'])) echo "курс: " . $userCharacteristic['kurs'];
            }
            if ($userCharacteristic['currentStatusEducation'] == "закончил") {
                if (isset($userCharacteristic['almamater'])) echo $userCharacteristic['almamater'] . ", ";
                if (isset($userCharacteristic['speciality'])) echo $userCharacteristic['speciality'] . ", ";
                if (isset($userCharacteristic['ochnoZaochno'])) echo $userCharacteristic['ochnoZaochno'] . ", ";
                if (isset($userCharacteristic['yearOfEnd'])) echo "<span style='white-space: nowrap;'>закончил в " . $userCharacteristic['yearOfEnd'] . " году</span>";
            }
            ?>
        </li>
        <li>
            <span class="headOfString">Работа:</span> <?php
            if ($userCharacteristic['statusWork'] == "не работаю") {
                echo "не работаю";
            } else {
                if (isset($userCharacteristic['placeOfWork']) && $userCharacteristic['placeOfWork'] != "") {
                    echo $userCharacteristic['placeOfWork'] . ", ";
                }
                if (isset($userCharacteristic['workPosition'])) {
                    echo $userCharacteristic['workPosition'];
                }
            }
            ?>
        </li>
        <li>
            <span class="headOfString">Внешность:</span> <?php
            if (isset($userCharacteristic['nationality']) && $userCharacteristic['nationality'] != "0") echo "<span style='white-space: nowrap;'>" . $userCharacteristic['nationality'] . "</span>";
            ?>
        </li>
        <li>
            <span class="headOfString">Пол:</span> <?php
            if (isset($userCharacteristic['sex'])) echo $userCharacteristic['sex'];
            ?>
        </li>
        <li>
            <span class="headOfString">День рождения:</span> <?php
            if (isset($userCharacteristic['birthday'])) echo $userCharacteristic['birthday'];
            ?>
        </li>
        <li>
            <span class="headOfString">Возраст:</span>
            <?php
                echo $this->globFunc->calculate_age($userCharacteristic['birthday']);
            ?>
        </li>
        <li>
            <br>
        </li>

    <?php if ($mode == "personal"): // Контакты показываются в личном кабинете, но не при просмотре анкеты арендатора собственником ?>
        <li>
            <span style="font-weight: bold;">Контакты:</span>
        </li>
        <li>
            <span class="headOfString">E-mail:</span> <?php
            if (isset($userCharacteristic['email'])) echo $userCharacteristic['email'];
            ?>
        </li>
        <li>
            <span class="headOfString">Телефон:</span> <?php
            if (isset($userCharacteristic['telephon'])) echo $userCharacteristic['telephon'];
            ?>
        </li>
        <li>
            <br>
        </li>
    <?php endif; ?>

        <li>
            <span style="font-weight: bold;">Малая Родина:</span>
        </li>
        <li>
            <span class="headOfString">Город (населенный пункт):</span> <?php
            if (isset($userCharacteristic['cityOfBorn'])) echo $userCharacteristic['cityOfBorn'];
            ?>
        </li>
        <li>
            <span class="headOfString">Регион:</span> <?php
            if (isset($userCharacteristic['regionOfBorn'])) echo $userCharacteristic['regionOfBorn'];
            ?>
        </li>
        <li>
            <br>
        </li>
        <li>
            <span style="font-weight: bold;">Коротко о себе и своих интересах:</span>
        </li>
        <li>
            <?php
            if (isset($userCharacteristic['shortlyAboutMe'])) echo $userCharacteristic['shortlyAboutMe'];
            ?>
        </li>
        <li>
            <br>
        </li>
        <li>
            <span style="font-weight: bold;">Страницы в социальных сетях:</span>
        </li>
        <li>
            <ul class="linksToAccounts">
                <?php
                if (isset($userCharacteristic['vkontakte'])) echo "<li><a href='" . $userCharacteristic['vkontakte'] . "'>" . $userCharacteristic['vkontakte'] . "</a></li>";
                ?>
                <?php
                if (isset($userCharacteristic['odnoklassniki'])) echo "<li><a href='" . $userCharacteristic['odnoklassniki'] . "'>" . $userCharacteristic['odnoklassniki'] . "</a></li>";
                ?>
                <?php
                if (isset($userCharacteristic['facebook'])) echo "<li><a href='" . $userCharacteristic['facebook'] . "'>" . $userCharacteristic['facebook'] . "</a></li>";
                ?>
                <?php
                if (isset($userCharacteristic['twitter'])) echo "<li><a href='" . $userCharacteristic['twitter'] . "'>" . $userCharacteristic['twitter'] . "</a></li>";
                ?>
            </ul>
        </li>
    </ul>
</div>