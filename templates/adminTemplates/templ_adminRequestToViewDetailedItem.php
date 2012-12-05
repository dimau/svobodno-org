<div class="requestToViewBlock" requestToViewId='<?php echo $requestToView['id'];?>' style="margin: 10px 0 10px 0;">

    <div style="float: left;">
        <span>Заявка на просмотр</span>
            <span>[
				<a class="statusAnchor" style="cursor: pointer;"><?php echo $requestToView['status'];?></a>
				<select class="statusSelect" style="display: none;">
                    <option value="Новая" <?php if ($requestToView['status'] == "Новая") echo "selected";?>>Новая
                    </option>
                    <option value="Назначен просмотр" <?php if ($requestToView['status'] == "Назначен просмотр") echo "selected";?>>
                        Назначен просмотр
                    </option>
                    <option value="Отложена" <?php if ($requestToView['status'] == "Отложена") echo "selected";?>>
                        Отложена
                    </option>
                    <option value="Объект уже сдан" <?php if ($requestToView['status'] == "Объект уже сдан") echo "selected";?>>
                        Объект уже сдан
                    </option>
                    <option value="Отказ собственника" <?php if ($requestToView['status'] == "Отказ собственника") echo "selected";?>>
                        Отказ собственника
                    </option>
                    <option value="Отменена" <?php if ($requestToView['status'] == "Отменена") echo "selected";?>>
                        Отменена
                    </option>
                    <option value="Безуспешный просмотр" <?php if ($requestToView['status'] == "Безуспешный просмотр") echo "selected";?>>
                        Безуспешный просмотр
                    </option>
                    <option value="Успешный просмотр" <?php if ($requestToView['status'] == "Успешный просмотр") echo "selected";?>>
                        Успешный просмотр
                    </option>
                </select>
			]</span>
			<span class="content">
				от
				<?php
				echo $requestToView['surname'] . " " . $requestToView['name'] . " " . $requestToView['secondName']
				?>
			</span>
    </div>

    <ul class="setOfInstructions">
        <li>
            <a target="_blank"
               href='adminRequestToView.php?propertyId=<?php echo $requestToView['propertyId'];?>&requestToViewId=<?php echo $requestToView['id'];?>'>подробнее</a>
        </li>
    </ul>

    <div class="clearBoth"></div>

    <div>
        <a class="tenantTimeAnchor" style="cursor: pointer;">Удобное для арендатора время:</a>
        <span class="tenantTimeText"><?php echo $requestToView['tenantTime'];?></span>

        <div class="tenantTimeEditBlock" style="display: none; margin: 10px 0 10px 0;">
            <textarea class="tenantTimeTextArea"
                      style="width: 40em; max-width: 100%; height: 250px;"><?php echo $requestToView['tenantTime'];?></textarea>
            <div>
                <a class="tenantTimeSaveButton" style="cursor: pointer;">Сохранить</a>
                <a class="tenantTimeCancelButton" style="cursor: pointer; margin-left: 15px;;">Отменить</a>
            </div>
        </div>
    </div>

    <div>
        <a class="tenantCommentAnchor" style="cursor: pointer;">Комментарий арендатора:</a>
        <span class="tenantCommentText"><?php echo $requestToView['tenantComment'];?></span>

        <div class="tenantCommentEditBlock" style="display: none; margin: 10px 0 10px 0;">
            <textarea class="tenantCommentTextArea" style="width: 40em; max-width: 100%; height: 250px;"><?php echo $requestToView['tenantComment'];?></textarea>
            <div>
                <a class="tenantCommentSaveButton" style="cursor: pointer;">Сохранить</a>
                <a class="tenantCommentCancelButton" style="cursor: pointer; margin-left: 15px;;">Отменить</a>
            </div>
        </div>
    </div>

</div>