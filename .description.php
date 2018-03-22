<?
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => 'Platron',
	'SORT' => 400,
	'CODES' => array(
		"PLATRON_MERCHANT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_MERCHANT_ID"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 100,
		),
		"PLATRON_SECRET_KEY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_SECRET_KEY"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 200,
		),
		// PS_IS_TEST - ?
		"PLATRON_TESTING_MODE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_TESTING_MODE"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 300,
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"PLATRON_ORDER_LIFETIME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_ORDER_LIFETIME"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 500,
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_ORDER_ID"),
			"SORT" => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			)
		),
		"PAYMENT_CURRENCY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_CURRENCY"),
			'SORT' => 750,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "CURRENCY",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_SHOULD_PAY"),
			"SORT" => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_CHANGE_STATUS_PAY"),
			'SORT' => 850,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
		),
		"PLATRON_PAYMENT_SYSTEM" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_PAYMENT_SYSTEM"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 880,
		),
		"PLATRON_CHECK_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_CHECK_URL"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 900,
			"DEFAULT" => array(
				"PROVIDER_VALUE" => 'http://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_result.php',
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"PLATRON_RESULT_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_RESULT_URL"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 1000,
			"DEFAULT" => array(
				"PROVIDER_VALUE" => 'http://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_result.php',
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"PLATRON_SUCCESS_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_SUCCESS_URL"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 1100,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 'http://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_success.php',
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"PLATRON_FAILURE_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_FAILURE_URL"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 1200,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 'http://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_fail.php',
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"PLATRON_OFD_SEND_RECEIPT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_OFD_SEND_RECEIPT"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 1300,
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"PLATRON_OFD_VAT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PLATRON_OFD_VAT"),
			'GROUP' => 'CONNECT_SETTINGS_PLATRON',
			'SORT' => 1400,
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					"0" => '0%',
					"10" => '10%',
					"18" => '18%',
					"110" => '10/110',
					"118" => '18/118',
					"none" => '-',
				)
			)
		)
	)
);

