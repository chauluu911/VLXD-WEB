<?php
namespace App\Traits\Repository;


use App\Enums\EDateFormat;

trait ServerDateTime {
	/**
	 * @param \Illuminate\Support\Carbon|\Carbon\Carbon $time
	 * @return string
	 */
	public function parseTimeString($time) {
		return $time->copy()->timezone(config('app.timezone'))->format(EDateFormat::MODEL_DATE_FORMAT);
	}

	/**
	 * @param \Illuminate\Support\Carbon|\Carbon\Carbon$time
	 * @return string
	 */
	public function parseTimeStringStartDate($time) {
		return $this->parseTimeString($time->copy()->startOfDay());
	}
}
