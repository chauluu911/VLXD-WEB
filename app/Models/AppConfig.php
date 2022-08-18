<?php

namespace App\Models;


class AppConfig extends BaseModel {
    protected $table = 'app_config';

	public function getValue() {
		if (!empty($this->date_time_wotz_value)) {
			return Carbon::parse($this->date_time_wotz_value);
		}
		if (!empty($this->date_time_value)) {
			return Carbon::parse($this->date_time_value);
		}
		if (!empty($this->text_arr_value)) {
			return $this->getArrayValue($this->text_arr_value);
		}
		if (isset($this->numeric_value)) {
			return floatval($this->numeric_value);
		}
		$textVal = $this->getJsonValue($this->text_value);
		return empty($textVal) ? $this->text_value : $textVal;
	}
}
