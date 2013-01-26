<div style="margin: 10px 0 10px 0;">
    <div>
        <div style="float: left;">
            <span>Заявка на просмотр</span>
            <span>[<?php echo $requestToView['status'];?>]:</span>
			<span class="content">
				<?php
                echo $requestToView['address'];
                if (isset($requestToView['apartmentNumber']) && $requestToView['apartmentNumber'] != "") {
                    echo ", кв. № " . $requestToView['apartmentNumber'];
                }
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
    </div>
</div>