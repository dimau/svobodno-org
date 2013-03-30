<div class="propertyBlock" propertyId="<?php echo $propertyCharacteristic['id'];?>" style="margin: 10px 0 10px 0;">
    <div>
        <div style="float: left;">
            <span><?php echo $propertyCharacteristic['typeOfObject']; ?>:</span>
			<span class="content">
				<?php
                echo $propertyCharacteristic['address'];
                if (isset($propertyCharacteristic['apartmentNumber']) && $propertyCharacteristic['apartmentNumber'] != "") {
                    echo ", кв. № " . $propertyCharacteristic['apartmentNumber'];
                }
                ?>
			</span>
            <?php echo " [" . $propertyCharacteristic['status'] . "]"; ?>
        </div>

        <ul class="setOfInstructions">
            <li>
                <a href='editadvert.php?propertyId=<?php echo $propertyCharacteristic['id'];?>'>Редактировать</a>
            </li>
            <li>
                <a href='property.php?propertyId=<?php echo $propertyCharacteristic['id'];?>'>Подробнее</a>
            </li>
            <?php if (isset($propertyCharacteristic['status']) && $propertyCharacteristic['status'] == "опубликовано"): ?>
            <li class="unpublishAdvert">
                <a>Снять с публикации</a>
            </li>
            <?php endif; ?>
        </ul>
        <div class="clearBoth"></div>
    </div>

    <?php if (isset($propertyCharacteristic['adminComment']) && $propertyCharacteristic['adminComment'] != ""): ?>
    <div style="margin-left: 30px;;">
        Комментарий для сотрудников: <?php echo $propertyCharacteristic['adminComment']; ?>
    </div>
    <?php endif; ?>
</div>