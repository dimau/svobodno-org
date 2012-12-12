<div class="requestFromOwnerBlock" style="margin: 10px 0 10px 0;">
        <div style="float: left;">
            <div>
				Дата регистрации: <?php echo GlobFunc::timestampFromDBToView($requestFromOwner['regDate']); ?>
			</div>
            <div>
				Имя собственника: <?php echo $requestFromOwner['name']; ?>
            </div>
            <div>
				Телефон: <?php echo $requestFromOwner['telephon']; ?>
            </div>
            <div>
				Адрес: <?php echo $requestFromOwner['address']; ?>
            </div>
            <div>
				Комментарий: <?php echo $requestFromOwner['commentOwner']; ?>
            </div>
        </div>

        <ul class="setOfInstructions">
            <li>
                <a class="remove" href="adminAllRequestsFromOwners.php?action=remove&requestFromOwnerId=<?php echo $requestFromOwner['id'];?>">удалить</a>
            </li>
        </ul>
        <div class="clearBoth"></div>
</div>