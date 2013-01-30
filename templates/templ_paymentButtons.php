<!-- Заранее подгружаем картинки, которые нам понадобятся при наведении мышки на кнопки -->
<div style="display: none;">
    <img src="img/button-380-light.png">
    <img src="img/button-480-light.png">
</div>

<form method="post" action="<?php if($isLoggedIn) echo "https://merchant.w1.ru/checkout/default.aspx"; else echo "registration.php";?>" accept-charset="UTF-8" style="display: inline-block;">
    <?php if ($isLoggedIn):?>
    <input type="hidden" name="WMI_MERCHANT_ID" value="185864873196"/>
    <input type="hidden" name="WMI_PAYMENT_AMOUNT" value="380"/>
    <input type="hidden" name="WMI_CURRENCY_ID" value="643"/>
    <input type="hidden" name="WMI_DESCRIPTION" value="Оплата доступа к порталу Svobodno.org: все данные по комнатам на 14 дней"/>
    <input type="hidden" name="WMI_SUCCESS_URL" value="http://svobodno.org/paymentSuccess.php"/>
    <input type="hidden" name="WMI_FAIL_URL" value="http://svobodno.org/paymentFail.php"/>
    <input type="hidden" name="WMI_PAYMENT_NO" value="<?php $invoiceNumber = uniqid("", TRUE); echo $invoiceNumber;?>"/>
    <input type="hidden" name="userId" value="<?php echo $userCharacteristic['id'];?>"/>
    <input type="hidden" name="purchase" value="reviewRooms14d"/>
    <!--<input type="hidden" name="WMI_SIGNATURE" value="<?php //echo $signature;?>"/> -->
    <?php endif;?>
    <button type="submit" class="buyButton rooms"></button>
</form>

<form method="post" action="<?php if($isLoggedIn) echo "https://merchant.w1.ru/checkout/default.aspx"; else echo "registration.php";?>" accept-charset="UTF-8" style="display: inline-block;">
    <?php if ($isLoggedIn):?>
    <input type="hidden" name="WMI_MERCHANT_ID" value="185864873196"/>
    <input type="hidden" name="WMI_PAYMENT_AMOUNT" value="480"/>
    <input type="hidden" name="WMI_CURRENCY_ID" value="643"/>
    <input type="hidden" name="WMI_DESCRIPTION" value="Оплата доступа к порталу Svobodno.org: все данные по квартирам на 14 дней"/>
    <input type="hidden" name="WMI_SUCCESS_URL" value="http://svobodno.org/paymentSuccess.php"/>
    <input type="hidden" name="WMI_FAIL_URL" value="http://svobodno.org/paymentFail.php"/>
    <input type="hidden" name="WMI_PAYMENT_NO" value="<?php $invoiceNumber = uniqid("", TRUE); echo $invoiceNumber;?>"/>
    <input type="hidden" name="userId" value="<?php echo $userCharacteristic['id'];?>"/>
    <input type="hidden" name="purchase" value="reviewFlats14d"/>
    <!--<input type="hidden" name="WMI_SIGNATURE" value="<?php //echo $signature;?>"/> -->
    <?php endif;?>
    <button type="submit" class="buyButton flats"></button>
</form>