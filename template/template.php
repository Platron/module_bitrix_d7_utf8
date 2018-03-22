<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

?>

<?=Loc::getMessage("PAYMENT_DESCRIPTION_PS")?> <b>PLATRON</b>.<br /><br />
<?=Loc::getMessage("PAYMENT_DESCRIPTION_SUM")?>: <b><?=CurrencyFormat($params["PAYMENT_SHOULD_PAY"], $params['PAYMENT_CURRENCY'])?></b><br /><br />

<form name="paymentform" action="<?=$params['PLATRON_REDIRECT_URL']?>" method="POST">
	<input type="hidden" name="pg_merchant_id" value="<?=$params['PLATRON_MERCHANT_ID']?>" />
	<input type="hidden" name="pg_order_id" value="<?=$params['PAYMENT_ID']?>" />
	<input type="hidden" name="pg_currency" value="<?=$params['PAYMENT_CURRENCY']?>" />
	<input type="hidden" name="pg_amount" value="<?=$params['PAYMENT_SHOULD_PAY']?>" />
	<input type="hidden" name="pg_lifetime" value="<?=$params['PLATRON_ORDER_LIFETIME']?>" />
	<input type="hidden" name="pg_testing_mode" value="<?=($params['PLATRON_TESTING_MODE'] == 'Y') ? 1 : 0 ?>" />
	<input type="hidden" name="pg_description" value="<?=$params['PLATRON_DESCRIPTION']?>" /> 
	<input type="hidden" name="pg_language" value="<?=$params['PLATRON_LANGUAGE']?>" />
	<input type="hidden" name="pg_check_url" value="<?=$params['PLATRON_CHECK_URL']?>" />
	<input type="hidden" name="pg_result_url" value="<?=$params['PLATRON_RESULT_URL']?>" />
	<input type="hidden" name="pg_success_url" value="<?=$params['PLATRON_SUCCESS_URL']?>" />
	<input type="hidden" name="pg_failure_url" value="<?=$params['PLATRON_FAILURE_URL']?>" />
	<input type="hidden" name="pg_request_method" value="<?=$params['PLATRON_METHOD']?>" />
	<? if ($params['BUYER_PERSON_PHONE']) {	?>
	<input type="hidden" name="pg_user_phone" value="<?=$params['BUYER_PERSON_PHONE']?>" />
	<? } ?>
	<? if ($params['BUYER_PERSON_EMAIL']) {	?>
	<input type="hidden" name="pg_user_email" value="<?=$params['BUYER_PERSON_EMAIL']?>" />
	<? } ?>
	<? if ($params['PLATRON_PAYMENT_SYSTEM']) {	?>
	<input type="hidden" name="pg_payment_system" value="<?=$params['PLATRON_PAYMENT_SYSTEM']?>" />
	<? } ?>
	<input type="hidden" name="pg_salt" value="<?=$params['PLATRON_SALT']?>" />
	<input type="hidden" name="pg_sig" value="<?=$params['PLATRON_SIGNATURE']?>" />
	<input type="submit" value="<?= GetMessage("PAYMENT_PAY")?>" />
</form>

