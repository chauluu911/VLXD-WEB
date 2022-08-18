<?php


namespace App\Enums;


class EDisplayStatus {
    const HIDDEN = 0;
    const SHOWING = 1;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case self::SHOWING:
                return __('common/constant.display_status.showing');
			case self::HIDDEN:
			default:
				return __('common/constant.display_status.hidden');
        }
    }
}
