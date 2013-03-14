<form method="post" action="<?php if($isLoggedIn) echo "https://merchant.w1.ru/checkout/default.aspx"; else echo "registration.php";?>" accept-charset="UTF-8" style="display: inline-block;">
    <?php if ($isLoggedIn):?>
    <input type="hidden" name="WMI_MERCHANT_ID" value="185864873196"/>
    <input type="hidden" name="WMI_PAYMENT_AMOUNT" value="50"/>
    <input type="hidden" name="WMI_CURRENCY_ID" value="643"/>
    <input type="hidden" name="WMI_DESCRIPTION" value="Оплата премиум доступа к порталу Svobodno.org на 1 сутки"/>
    <input type="hidden" name="WMI_SUCCESS_URL" value="http://svobodno.org/paymentSuccess.php"/>
    <input type="hidden" name="WMI_FAIL_URL" value="http://svobodno.org/paymentFail.php"/>
    <input type="hidden" name="WMI_PAYMENT_NO" value="<?php $invoiceNumber = uniqid("", TRUE); echo $invoiceNumber;?>"/>
    <input type="hidden" name="userId" value="<?php echo $userCharacteristic['id'];?>"/>
    <input type="hidden" name="purchase" value="reviewFull1d"/>
    <!--<input type="hidden" name="WMI_SIGNATURE" value="<?php //echo $signature;?>"/> -->
    <?php endif;?>
    <button type="submit" class="mainButton">
        50 руб. за 1 день
    </button>
</form>

<form method="post" action="<?php if($isLoggedIn) echo "https://merchant.w1.ru/checkout/default.aspx"; else echo "registration.php";?>" accept-charset="UTF-8" style="display: inline-block; margin-left: 10px;">
    <?php if ($isLoggedIn):?>
    <input type="hidden" name="WMI_MERCHANT_ID" value="185864873196"/>
    <input type="hidden" name="WMI_PAYMENT_AMOUNT" value="380"/>
    <input type="hidden" name="WMI_CURRENCY_ID" value="643"/>
    <input type="hidden" name="WMI_DESCRIPTION" value="Оплата премиум доступа к порталу Svobodno.org на 10 дней"/>
    <input type="hidden" name="WMI_SUCCESS_URL" value="http://svobodno.org/paymentSuccess.php"/>
    <input type="hidden" name="WMI_FAIL_URL" value="http://svobodno.org/paymentFail.php"/>
    <input type="hidden" name="WMI_PAYMENT_NO" value="<?php $invoiceNumber = uniqid("", TRUE); echo $invoiceNumber;?>"/>
    <input type="hidden" name="userId" value="<?php echo $userCharacteristic['id'];?>"/>
    <input type="hidden" name="purchase" value="reviewFull10d"/>
    <!--<input type="hidden" name="WMI_SIGNATURE" value="<?php //echo $signature;?>"/> -->
    <?php endif;?>
    <button type="submit" class="mainButton">
        380 руб. за 10 дней
    </button>
</form>

<form method="post" action="<?php if($isLoggedIn) echo "https://merchant.w1.ru/checkout/default.aspx"; else echo "registration.php";?>" accept-charset="UTF-8" style="display: inline-block; margin-left: 10px;">
    <?php if ($isLoggedIn):?>
    <input type="hidden" name="WMI_MERCHANT_ID" value="185864873196"/>
    <input type="hidden" name="WMI_PAYMENT_AMOUNT" value="1100"/>
    <input type="hidden" name="WMI_CURRENCY_ID" value="643"/>
    <input type="hidden" name="WMI_DESCRIPTION" value="Оплата премиум доступа к порталу Svobodno.org на 30 дней"/>
    <input type="hidden" name="WMI_SUCCESS_URL" value="http://svobodno.org/paymentSuccess.php"/>
    <input type="hidden" name="WMI_FAIL_URL" value="http://svobodno.org/paymentFail.php"/>
    <input type="hidden" name="WMI_PAYMENT_NO" value="<?php $invoiceNumber = uniqid("", TRUE); echo $invoiceNumber;?>"/>
    <input type="hidden" name="userId" value="<?php echo $userCharacteristic['id'];?>"/>
    <input type="hidden" name="purchase" value="reviewFull30d"/>
    <!--<input type="hidden" name="WMI_SIGNATURE" value="<?php //echo $signature;?>"/> -->
    <?php endif;?>
    <button type="submit" class="mainButton">
        1100 руб за 30 дней
    </button>
</form>