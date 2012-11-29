<div class="simpleBlockForAnyContent">
	<div>
		<span class='content'>
			<a target="_self" href="personal.php?compId=<?php echo GlobFunc::idToCompId($user['id']); ?>"><?php echo $user['surname']." ".$user['name']." ".$user['secondName']; ?></a>
		</span>
		<?php if ($user['typeTenant'] === "TRUE") echo "[арендатор]"; ?>
		<?php if ($user['typeOwner'] === "TRUE") echo "[собственник]";?>
	</div>
	<div>
        Телефон: <span class='content'><?php echo $user['telephon']; ?></span>
		E-mail: <span class='content'><?php echo $user['email']; ?></span>
	</div>
    <div>
        Логин: <span class='content'><?php echo $user['login']; ?></span>
		Пароль: <span class='content'><?php echo $user['password']; ?></span>
    </div>

	<div style="margin-left: 40px;">
		<?php
		foreach ($allProperties as $value) {
			if ($value['userId'] == $user['id']) {
				View::getHTMLforAdminFindedUsersProperty($value);
			}
		}
		?>
	</div>
</div>