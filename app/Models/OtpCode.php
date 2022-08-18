<?php

namespace App\Models;

use App\Helpers\DateUtility;

class OtpCode extends BaseModel {
	protected $table = 'otp_code';
	const UPDATED_AT = null;

	protected $casts = [
		'type' => 'int',
		'expired_at' => 'date',
	];
}