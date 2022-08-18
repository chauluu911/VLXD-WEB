<?php

namespace App\Enums;

abstract class EOtpType {
	const VERIFY_EMAIL_WHEN_REGISTER = 0;
	const VERIFY_EMAIL_WHEN_FORGOT_PASSWORD = 1;
}