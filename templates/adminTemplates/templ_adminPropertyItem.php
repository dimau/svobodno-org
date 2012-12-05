<div style="margin: 10px 0 10px 0;">
    <div>
        <div style="float: left;">
            <span><?php echo $property['typeOfObject']; ?>:</span>
			<span class="content">
				<?php
				echo $property['address'];
				if (isset($property['apartmentNumber']) && $property['apartmentNumber'] != "") {
					echo ", кв. № " . $property['apartmentNumber'];
				}
				?>
			</span>
        </div>

        <ul class="setOfInstructions">
            <li>
                <a href='editadvert.php?propertyId=<?php echo $property['id'];?>'>редактировать</a>
            </li>
            <li>
                <a href='objdescription.php?propertyId=<?php echo $property['id'];?>'>подробнее</a>
            </li>
			<?php if (isset($property['completeness']) && $property['completeness'] == "0"): // Возможность удалить объявление есть только для объектов, полученных из чужих баз - а значит по определению некачественных объявлений ?>
            <li>
                <a class="removeAlienAdvert" propertyId='<?php echo $property['id'];?>'>удалить</a>
            </li>
			<?php endif; ?>
        </ul>
        <div class="clearBoth"></div>
    </div>

	<?php if (isset($property['adminComment']) && $property['adminComment'] != ""): ?>
    <div style="margin-left: 30px;;">
        Комментарий для сотрудников: <?php echo $property['adminComment']; ?>
    </div>
	<?php endif; ?>
</div>