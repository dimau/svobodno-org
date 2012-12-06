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
        </div>

        <ul class="setOfInstructions">
            <li>
                <a href='editadvert.php?propertyId=<?php echo $propertyCharacteristic['id'];?>'>редактировать</a>
            </li>
            <li>
                <a href='objdescription.php?propertyId=<?php echo $propertyCharacteristic['id'];?>'>подробнее</a>
            </li>
            <li>
                <a target="_blank" href='adminRequestToView.php?propertyId=<?php echo $propertyCharacteristic['id'];?>'>заявки на просмотр</a>
            </li>
			<?php if (isset($propertyCharacteristic['completeness']) && $propertyCharacteristic['completeness'] == "0"): // Возможность удалить объявление есть только для объектов, полученных из чужих баз - а значит по определению некачественных объявлений ?>
            <li>
                <a class="removeAlienAdvert" propertyId='<?php echo $propertyCharacteristic['id'];?>'>удалить</a>
            </li>
			<?php endif; ?>
        </ul>
        <div class="clearBoth"></div>
    </div>

    <div style="margin-left: 30px;">
        <a class="earliestDateAnchor" style="cursor: pointer;">Дата просмотра:</a>
        <span class="earliestDateFullText">
			<?php if ($propertyCharacteristic['earliestDate'] != "0000-00-00" && $propertyCharacteristic['earliestTimeHours'] != "" && $propertyCharacteristic['earliestTimeMinutes'] != ""): ?>
				<span class="earliestDateText"><?php echo $propertyCharacteristic['earliestDate'];?></span> в <span class="earliestTimeHoursText"><?php echo $propertyCharacteristic['earliestTimeHours'];?></span>:<span class="earliestTimeMinutesText"><?php echo $propertyCharacteristic['earliestTimeMinutes'];?></span>
			<?php else: ?>
            <span class="earliestDateText">--.--.----</span> в <span class="earliestTimeHoursText">--</span>:<span class="earliestTimeMinutesText">--</span>
			<?php endif;?>
        </span>
        <div class="earliestDateEditBlock" style="display: none; margin: 10px 0 10px 0;">
            <input type="text" class="earliestDateInput" size="15" value="<?php if ($propertyCharacteristic['earliestDate'] != "0000-00-00") echo $propertyCharacteristic['earliestDate'];?>" style="margin-right: 7px;">
            <input type="text" class="earliestTimeHoursInput" size="3" value="<?php echo $propertyCharacteristic['earliestTimeHours'];?>">:
            <input type="text" class="earliestTimeMinutes" size="3" value="<?php echo $propertyCharacteristic['earliestTimeMinutes'];?>">
            <div style="display: inline; margin-left: 15px;">
                <a class="earliestDateSaveButton" style="cursor: pointer;">Сохранить</a>
                <a class="earliestDateCancelButton" style="cursor: pointer; margin-left: 15px;;">Отменить</a>
            </div>
        </div>
    </div>

	<?php if (isset($propertyCharacteristic['adminComment']) && $propertyCharacteristic['adminComment'] != ""): ?>
    <div style="margin-left: 30px;;">
        Комментарий для сотрудников: <?php echo $propertyCharacteristic['adminComment']; ?>
    </div>
	<?php endif; ?>
</div>