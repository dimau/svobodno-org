<?php

$cost = "130.00";
$invoiceNumber = uniqid("", TRUE); // уникальный идентификатор заказа - строка длиной в 23 символа
$userId = 1;
$purchase = "reviewRooms14d";
?>

<form method="post" action="https://merchant.w1.ru/checkout/default.aspx" accept-charset="UTF-8">
    <input type="hidden" name="WMI_MERCHANT_ID" value="185864873196"/>
    <input type="hidden" name="WMI_PAYMENT_AMOUNT" value="<?php echo $cost;?>"/>
    <input type="hidden" name="WMI_CURRENCY_ID" value="643"/>
    <input type="hidden" name="WMI_DESCRIPTION"
           value="Оплата доступа к порталу Svobodno.org: все данные по комнатам на 14 дней"/>
    <input type="hidden" name="WMI_SUCCESS_URL" value="http://svobodno.org/paymentSuccess.php"/>
    <input type="hidden" name="WMI_FAIL_URL" value="http://svobodno.org/paymentFail.php"/>
    <input type="hidden" name="WMI_PAYMENT_NO" value="<?php echo $invoiceNumber;?>"/>
    <input type="hidden" name="userId" value="<?php echo $userId;?>"/>
    <input type="hidden" name="purchase" value="<?php echo $purchase;?>"/>
    <!--<input type="hidden" name="WMI_SIGNATURE" value="<?php //echo $signature;?>"/> -->
    <button type="submit">Покупаю!</button>
</form>