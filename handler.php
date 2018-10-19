<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Error;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Main\Entity\EntityError;


class PlatronHandler extends PaySystem\ServiceHandler
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{

		require_once 'PG_Signature.php';

		$params = $this->getParamsBusValue($payment);

        $paymentShouldPay = round($this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY'), 2);

		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $payment->getCollection();

		/** @var \Bitrix\Sale\Order $order */
		$order = $paymentCollection->getOrder();

		$context = \Bitrix\Main\Application::getInstance()->getContext();

		$salt = rand(101,45300);
		$strDescription = 'Order ID: ' . $order->getId();

		$extraParams = array(
			'PLATRON_DESCRIPTION' => $strDescription,
			'PLATRON_METHOD' => 'POST',
			'PLATRON_ORDER_LIFETIME' => ($params['PLATRON_ORDER_LIFETIME']) ? $params['PLATRON_ORDER_LIFETIME'] : 6000,
			'PLATRON_SALT' => $salt,
			'BX_PAYSYSTEM_CODE' => $payment->getPaymentSystemId(),
			'PAYMENT_SHOULD_PAY' => $paymentShouldPay,
			'PLATRON_LANGUAGE' => $context->getLanguage(),
			'PLATRON_CHECK_URL' => $params['PLATRON_CHECK_URL'] . '?PAY_SYSTEM_ID_NEW=' . $payment->getPaymentSystemId() . '&type=check',
			'PLATRON_RESULT_URL' => $params['PLATRON_RESULT_URL'] . '?PAY_SYSTEM_ID_NEW=' . $payment->getPaymentSystemId() . '&type=result',
			'PLATRON_SUCCESS_URL' => $params['PLATRON_SUCCESS_URL'],
			'PLATRON_FAILURE_URL' => $params['PLATRON_FAILURE_URL'],
			'PLATRON_SALT' => $salt
		);

		$arrFields = array(
			'pg_merchant_id'		=> $params['PLATRON_MERCHANT_ID'],
			'pg_order_id'			=> $params['PAYMENT_ID'],
			'pg_currency'			=> $params['PAYMENT_CURRENCY'],
			'pg_amount'				=> $paymentShouldPay,
			'pg_lifetime'			=> $extraParams['PLATRON_ORDER_LIFETIME'],
			'pg_testing_mode'		=> ($params['PLATRON_TESTING_MODE'] === 'Y') ? 1 : 0, // IS_TEST - ?
			'pg_description'		=> $strDescription,
			'pg_language'			=> $extraParams['PLATRON_LANGUAGE'],
			'pg_check_url'			=> $extraParams['PLATRON_CHECK_URL'],
			'pg_result_url'			=> $extraParams['PLATRON_RESULT_URL'],
			'pg_success_url'		=> $extraParams['PLATRON_SUCCESS_URL'],
			'pg_failure_url'		=> $extraParams['PLATRON_FAILURE_URL'],
			'pg_request_method'		=> $extraParams['PLATRON_METHOD'],
			'cms_payment_module'	=> 'BITRIX7',
		);

		if ($params['PLATRON_PAYMENT_SYSTEM']) {
			$arrFields['pg_payment_system'] = $params['PLATRON_PAYMENT_SYSTEM'];
		}

		$arrFields['pg_salt'] = $extraParams['PLATRON_SALT'];

		$phone = $this->getPhone($order);
		$email = $this->getEmail($order);

		if ($phone) {
			$extraParams['BUYER_PERSON_PHONE'] = $phone;
			$arrFields['pg_user_phone'] = $extraParams['BUYER_PERSON_PHONE'];
		}

		if ($email) {
			$extraParams['BUYER_PERSON_EMAIL'] = $email;
			$arrFields['pg_user_email'] = $extraParams['BUYER_PERSON_EMAIL'];
		}

		$signatrue = PG_Signature::make('init_payment.php', $arrFields, $params['PLATRON_SECRET_KEY']);

		$arrFields['pg_sig'] = $signatrue;
		$extraParams['PLATRON_SIGNATURE'] = $signatrue;

	 	$response = file_get_contents('https://www.platron.ru/init_payment.php?' . http_build_query($arrFields));
 		$responseElement = new \SimpleXMLElement($response);

	 	$checkResponse = PG_Signature::checkXML('init_payment.php', $responseElement, $params['PLATRON_SECRET_KEY']);

		$result = new PaySystem\ServiceResult();

    	if ($checkResponse && (string)$responseElement->pg_status == 'ok') {

    		if ($params['PLATRON_OFD_SEND_RECEIPT'] == 'Y') {

    			$paymentId = (string)$responseElement->pg_payment_id;

    	        $ofdReceiptItems = array();

				$basket = $order->getBasket();

				/** @var \Bitrix\Sale\BasketItem $basketItem */
    			foreach ($basket->getBasketItems() as $basketItem) {
					if ($basketItem->getPrice() > 0) {
	    	            $ofdReceiptItem = new OfdReceiptItem();
						$name = $basketItem->getField("NAME");
						$ofdReceiptItem->label = (toUpper(LANG_CHARSET) == "WINDOWS-1251") ? iconv('cp1251', 'utf-8', $name) : $name;
    		            $ofdReceiptItem->amount = round($basketItem->getPrice() * $basketItem->getQuantity(), 2);
    	    	        $ofdReceiptItem->price = round($basketItem->getPrice(), 2);
    	        	    $ofdReceiptItem->quantity = $basketItem->getQuantity();
    	            	$ofdReceiptItem->vat = $params['PLATRON_OFD_VAT'];
	    	            $ofdReceiptItems[] = $ofdReceiptItem;
					}
        		}

				/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
				$shipmentCollection = $order->getShipmentCollection();

				foreach ($shipmentCollection as $shipment) {
					if (!$shipment->isSystem()) {
	    				$ofdReceiptItem = new OfdReceiptItem();
						$name = $shipment->getDeliveryName();
						$ofdReceiptItem->label = (toUpper(LANG_CHARSET) == "WINDOWS-1251") ? iconv('cp1251', 'utf-8', $name) : $name;
    					$ofdReceiptItem->amount = round($shipment->getPrice(), 2);
    					$ofdReceiptItem->price = round($shipment->getPrice(), 2);
    					$ofdReceiptItem->quantity = 1;
    					$ofdReceiptItem->vat = $params['PLATRON_OFD_VAT'] === 'none' ? 'none': 18;
	    				$ofdReceiptItems[] = $ofdReceiptItem;
    	   			}
				}

    			$ofdReceiptRequest = new OfdReceiptRequest($params['PLATRON_MERCHANT_ID'], $paymentId);
    			$ofdReceiptRequest->items = $ofdReceiptItems;
    			$ofdReceiptRequest->sign($params['PLATRON_SECRET_KEY']);

    			$responseOfd = file_get_contents('https://www.platron.ru/receipt.php?' . http_build_query($ofdReceiptRequest->requestArray()));
    			$responseElementOfd = new \SimpleXMLElement($responseOfd);

    			if ((string)$responseElementOfd->pg_status != 'ok') {

					$error = 'Platron create OFD check error. ' . $responseElementOfd->pg_error_description;
					$error = (toUpper(LANG_CHARSET) == "WINDOWS-1251") ? iconv('utf-8', 'cp1251', $error) : $error;
					echo $error;
					PaySystem\ErrorLog::add(array(
						'ACTION' => 'initiatePay',
						'MESSAGE' => $error
					));
					$result->addError(new Error($error));
					return $result;
    			}

    		}

		} else {

			$error = 'Platron init payment error. ' . $responseElement->pg_error_description;
			$error = (toUpper(LANG_CHARSET) == "WINDOWS-1251") ? iconv('utf-8', 'cp1251', $error) : $error;
			echo $error;
			PaySystem\ErrorLog::add(array(
				'ACTION' => 'initiatePay',
				'MESSAGE' => $error
			));
			$result->addError(new Error($error));

			return $result;

	 	}

		$extraParams['PLATRON_REDIRECT_URL'] = (string)$responseElement->pg_redirect_url;

		$this->setExtraParams($extraParams);

		return $this->showTemplate($payment, "template");
	}

	/**
	 * @return string
	 */
	protected function getPhone($order)
	{
		return $this->getFromPropertyCollection('IS_PHONE', $order);
	}

	/**
	 * @return string
	 */
	protected function getEmail($order)
	{
		return $this->getFromPropertyCollection('IS_EMAIL', $order);
	}

	/**
	 * @return string
	 */
	protected function getFromPropertyCollection($what, $order)
	{
		$userProfiles = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($order->getUserId());
		$userPersonTypeId = $order->getPersonTypeId();
		$userProfile = current($userProfiles[$userPersonTypeId]);
		$propertyCollection = $order->getPropertyCollection();

		foreach ($propertyCollection as $property)
		{
			if ($property->isUtil()) {
				continue;
			}

			$arProperty = $property->getProperty();

			if ($arProperty[$what] === 'Y') {
				return $userProfile[$arProperty['ID']];
			}
		}

		return '';
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return array('BX_HANDLER' => 'PLATRON');
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('pg_order_id');
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{

		$result = new PaySystem\ServiceResult();

		require_once 'PG_Signature.php';

		$arrRequest = $_REQUEST;

		$paymentCollection = $payment->getCollection();
		$order = $paymentCollection->getOrder();

		$thisScriptName = PG_Signature::getOurScriptName();

		$params = $this->getParamsBusValue($payment);

		if (empty($arrRequest['pg_sig']) || !PG_Signature::check($arrRequest['pg_sig'], $thisScriptName, $_POST, $params['PLATRON_SECRET_KEY'])) {
			die("Wrong signature");
		}

		if ($arrRequest['type'] == 'check') {

			if (sprintf('%0.2f',$arrRequest['pg_amount']) != sprintf('%0.2f',$this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY'))) {
				$strResponseStatus = 'error';
				$strResponseDescription = 'Неверная сумма';
			} elseif ($arrRequest['pg_can_reject'] == 1 and $order->getField('CANCELED') === 'Y') {
				$strResponseStatus = 'rejected';
				$strResponseDescription = 'Заказ отменён';
			} else { 
				$strResponseStatus = 'ok';
				$strResponseDescription = '';
			}

			if (toUpper(LANG_CHARSET) == "WINDOWS-1251") {
				$strResponseStatus = iconv('cp1251', 'utf-8', $strResponseStatus);
				$strResponseDescription = iconv('cp1251', 'utf-8', $strResponseDescription);
			}

			$arrResponse = array();
			$arrResponse['pg_salt']              = $arrRequest['pg_salt'];
			$arrResponse['pg_status']            = $strResponseStatus;
			$arrResponse['pg_error_description'] = $strResponseDescription;
			$arrResponse['pg_sig']				 = PG_Signature::make($thisScriptName, $arrResponse, $params['PLATRON_SECRET_KEY']);

			$objResponse = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
			$objResponse->addChild('pg_salt', $arrResponse['pg_salt']);
			$objResponse->addChild('pg_status', $arrResponse['pg_status']);
			$objResponse->addChild('pg_error_description', $arrResponse['pg_error_description']);
			$objResponse->addChild('pg_sig', $arrResponse['pg_sig']);

			header("Content-type: text/xml");
			echo $objResponse->asXML();
			die();

		}
		elseif ($arrRequest['type'] == 'result') {

			if (sprintf('%0.2f',$arrRequest['pg_amount']) != sprintf('%0.2f',$this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY'))) {
				$strResponseStatus = 'error';
				$strResponseDescription = "Неверная сумма"; 
			} elseif ($arrRequest['pg_can_reject'] == 1 and $order->getField('CANCELED') === 'Y') {
				$strResponseStatus = 'rejected';
				$strResponseDescription = 'Заказ отменён';
			} else {
				$strResponseStatus = 'ok';
				$strResponseDescription = "Оплата принята";
			
				if ($arrRequest['pg_result'] == 1) {

					$psFields = array(
						"PS_STATUS" => "Y",
						"PS_STATUS_CODE" => "-",
						"PS_STATUS_DESCRIPTION" => 'Order Id: ' . $arrRequest['pg_order_id'],
						"PS_STATUS_MESSAGE" => 'User phone: ' . $arrRequest['pg_user_phone'] . ' User email: ' . $arrRequest['pg_user_contact_email'],
						"PS_SUM" => $arrRequest['pg_amount'],
						"PS_CURRENCY" => $payment->getField('CURRENCY'),
						"PS_RESPONSE_DATE" => new DateTime()
					);

					if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') == 'Y' && !$payment->isPaid())
					{
						$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
						$result->setPsData($psFields);
					}	
				} else {
					$error = $arrRequest['pg_error_description'];
					$error = (toUpper(LANG_CHARSET) == "WINDOWS-1251") ? iconv('utf-8', 'cp1251', $error) : $error;
					PaySystem\ErrorLog::add(array(
						'ACTION' => 'processRequest',
						'MESSAGE' => $error
						));
					$result->addError(new EntityError($error));
				}
			}

			if (toUpper(LANG_CHARSET) == "WINDOWS-1251") {
				$strResponseStatus = iconv('cp1251', 'utf-8', $strResponseStatus);
				$strResponseDescription = iconv('cp1251', 'utf-8', $strResponseDescription);
			}
		
			$objResponse = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
			$objResponse->addChild('pg_salt', $arrRequest['pg_salt']);
			$objResponse->addChild('pg_status', $strResponseStatus);
			$objResponse->addChild('pg_description', $strResponseDescription);
			$objResponse->addChild('pg_sig', PG_Signature::makeXML($thisScriptName, $objResponse, $params['PLATRON_SECRET_KEY']));
	
			header("Content-type: text/xml");
			echo $objResponse->asXML();
			return $result;

		}
		elseif($arrRequest['type'] == 'success') {
			LocalRedirect($params['SALE_HPS_PLATRON_SUCCESS_URL']);
		}
		else {
			LocalRedirect($params['SALE_HPS_PLATRON_FAILURE_URL']);
		}

	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}

	public static function isMyResponse(Request $request, $paySystemId)
	{
		$id = $request->get('PAY_SYSTEM_ID_NEW');
		if ((int)$id == (int)$paySystemId) {
			return true;
		}
		else {
			return false;
		}
	}


}
