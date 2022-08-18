<?php

namespace App\Enums\Payoo;

abstract class EPayooErrorMessage {
	const PAYMENT_FAIL = 500;
	const DECLINED = 501;
	const MERCHANT_NOT_EXIST = 503;
	const INVALID_AMOUNT = 505;
	const UNSPECIFIED_FAILURE = 507;
	const INVALID_CARD_NUMBER = 508;
	const INVALID_CARD_NAME = 509;
	const EXPIRY_CARD = 510;
	const NOT_REGISTERED = 511;
	const INVALID_CARD_DATE = 512;
	const EXIST_AMOUNT = 513;
	const INSUFFICIENT_FUND = 521;
	const INVALID_ACCOUNT = 522;
	const ACCOUNT_LOCK = 523;
	const INVALID_CARD_INFO = 524;
	const INVALID_OTP = 525;
	const USER_CANCEL = 599;
	const INVALID_PARAMETERS = 755;
	const BANK_PENDING = 800;
}