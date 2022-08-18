<?php
namespace App\Enums;


abstract class EErrorCode {
    const NO_ERROR = 0;
    const ERROR = 1;
    const VALIDATION_ERROR = 2;
    const UNAUTHORIZED = 401;
	const API_USER_VERIFIED = 1001;
}
