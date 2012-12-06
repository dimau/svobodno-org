<div>
	<div>
		<span class='content'>
			<a target="_self" href="personal.php?compId=<?php echo GlobFunc::idToCompId($userCharacteristic['id']); ?>"><?php echo $userCharacteristic['surname']." ".$userCharacteristic['name']." ".$userCharacteristic['secondName']; ?></a>
		</span>
		<?php if ($userCharacteristic['typeTenant'] === "TRUE") echo "[арендатор]"; ?>
		<?php if ($userCharacteristic['typeOwner'] === "TRUE") echo "[собственник]";?>
	</div>
	<div>
        Телефон: <span class='content'><?php echo $userCharacteristic['telephon']; ?></span>
		E-mail: <span class='content'><?php echo $userCharacteristic['email']; ?></span>
	</div>
    <div>
        Логин: <span class='content'><?php echo $userCharacteristic['login']; ?></span>
		Пароль: <span class='content'><?php echo $userCharacteristic['password']; ?></span>
    </div>
</div>