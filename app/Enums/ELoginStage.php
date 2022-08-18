<?php
namespace App\Enums;


abstract class ELoginStage {
    const NOT_REGISTERED = 1;
    const NOT_LOGGED_IN = 2;
    const VERIFY_EMAIL = 3;
    const LOGGED_IN = 4;
    const FORGOT_PASSWORD_EMAIL = 5;
    const VERIFY_OTP_FORGOT_PASSWORD = 6;
    const RESET_PASSWORD = 7;
    const VERIFY_SMS = 8;
    const OAUTH_ADDITION_INFO = 9;
}
