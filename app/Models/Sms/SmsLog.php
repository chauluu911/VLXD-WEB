<?php

namespace App\Models\Sms;

use App\Models\BaseModel;

class SmsLog extends BaseModel {
    protected $table = 'sms_log';
    const UPDATED_AT = null;

	public function getErrorMessagesAttribute($value) {
		$value = str_replace(['{', '}'], '', $value);
		$value = explode(',', $value);
		return $value;
	}

	public function setErrorMessagesAttribute($value) {
		if (!empty($value)) {
			$this->attributes['error_messages'] = '{' . implode(',', $value) . '}';
		}
	}
}
