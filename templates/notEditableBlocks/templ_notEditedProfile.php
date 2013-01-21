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
                if ($userCharacteristic['almamater'] != "") echo $userCharacteristic['almamater'] . ", ";
                if ($userCharacteristic['speciality'] != "") echo $userCharacteristic['speciality'] . ", ";
                if ($userCharacteristic['ochnoZaochno'] != "0") echo $userCharacteristic['ochnoZaochno'] . ", ";
                if ($userCharacteristic['kurs'] != "") echo "курс: " . $userCharacteristic['kurs'];
                if ($userCharacteristic['almamater'] == "" && $userCharacteristic['speciality'] == "" && $userCharacteristic['ochnoZaochno'] == "0" && $userCharacteristic['kurs'] == "") {
                    echo "студент";
                }
            }
            if ($userCharacteristic['currentStatusEducation'] == "закончил") {
                if ($userCharacteristic['almamater'] != "") echo $userCharacteristic['almamater'] . ", ";
                if ($userCharacteristic['speciality'] != "") echo $userCharacteristic['speciality'] . ", ";
                if ($userCharacteristic['ochnoZaochno'] != "0") echo $userCharacteristic['ochnoZaochno'] . ", ";
                if ($userCharacteristic['yearOfEnd'] != "") echo "<span style='white-space: nowrap;'>закончил в " . $userCharacteristic['yearOfEnd'] . " году</span>";
                if ($userCharacteristic['almamater'] == "" && $userCharacteristic['speciality'] == "" && $userCharacteristic['ochnoZaochno'] == "0" && $userCharacteristic['yearOfEnd'] == "") {
                    echo "закончил";
                }
            }
            ?>
        </li>
        <li>
            <span class="headOfString">Работа:</span> <?php
            if ($userCharacteristic['statusWork'] == "0") {
                echo "";
            }
            if ($userCharacteristic['statusWork'] == "не работаю") {
                echo "нет";
            }
            if ($userCharacteristic['statusWork'] == "работаю") {
                if ($userCharacteristic['placeOfWork'] != "") {
                    echo $userCharacteristic['placeOfWork'] . ", ";
                }
                if ($userCharacteristic['workPosition'] != "") {
                    echo $userCharacteristic['workPosition'];
                }
                if ($userCharacteristic['placeOfWork'] == "" && $userCharacteristic['workPosition'] == "") {
                    echo "есть";
                }
            }
            ?>
        </li>
        <li>
            <span class="headOfString">Внешность:</span> <?php
            if ($userCharacteristic['nationality'] != "0") echo "<span style='white-space: nowrap;'>" . $userCharacteristic['nationality'] . "</span>";
            ?>
        </li>
        <li>
            <span class="headOfString">Пол:</span> <?php
            if ($userCharacteristic['sex'] != "0") echo $userCharacteristic['sex'];
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
                echo GlobFunc::calculate_age($userCharacteristic['birthday']);
            ?>
        </li>
        <li>
            <br>
        </li>

    <?php if ($mode == "personal" || $isAdmin['searchUser']): // Контакты показываются в личном кабинете, а также при просмотре анкеты админом, но не при просмотре анкеты арендатора собственником ?>
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
            <span style="font-weight: bold;">О себе и своих интересах:</span>
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