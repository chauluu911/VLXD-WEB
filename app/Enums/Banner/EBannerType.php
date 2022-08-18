<?php

namespace App\Enums\Banner;


abstract class EBannerType {
	const SHOW_AS_POP_UP_AFTER_LOG_IN = 1;
	const MAIN_BANNER_ON_HOME_SCREEN = 2;
	const PROMOTION = 3;
	const TRADEMARK = 4;

	public static function isValid($type) {
		if (!is_numeric($type)) {
			return false;
		}
		if (self::SHOW_AS_POP_UP_AFTER_LOG_IN != $type
			&& self::MAIN_BANNER_ON_HOME_SCREEN != $type
			&& self::PROMOTION != $type) {
			return false;
		}
		return true;
    }
    
    public static function valueToName($value) {
		switch ($value) {
			case self::SHOW_AS_POP_UP_AFTER_LOG_IN:
				return 'Flash screen';
			case self::MAIN_BANNER_ON_HOME_SCREEN:
				return 'Home';
			case self::PROMOTION:
				return 'Promotion';
			case self::TRADEMARK:
				return 'Thương hiệu';
		}
		return null;
	}
}