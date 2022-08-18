<?php

namespace App\Enums;


abstract class ENotificationScheduleTargetType {
	const ALL_CUSTOMER = 1;
	const SPECIFIC_CUSTOMER = 2;

	public static function valueToLocalizedName($type) {
		switch ($type) {
			case self::ALL_CUSTOMER:
				return __('common/constant.target_type.all_customer');
			case self::SPECIFIC_CUSTOMER:
			default:
				return __('common/constant.target_type.specific_customer');
		}
	}
}