<?php


namespace App\Enums;


abstract class EPlatform {
    const WEB_AND_MOBILE = 0;
    const WEB = 1;
    const MOBILE = 2;

	const ANDROID = 2;
	const IOS = 3;

	public static function valueToName($value) {
		switch ($value) {
			case self::WEB:
				return 'Web';
			case self::MOBILE:
				return 'Mobile';
            case self::WEB_AND_MOBILE:
                return 'Cả 2';
		}
		return null;
	}
}
