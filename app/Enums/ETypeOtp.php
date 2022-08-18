<?php
namespace App\Enums;

abstract class ETypeOtp {
	const VERIFY_PHONE_AT_LOGIN = 1;
	const VERIFY_PHONE_WHEN_RESET_PASSWORD = 2;
	const VERIFY_LOGIN_FACEBOOK_AND_GOOGLE = 3;
}