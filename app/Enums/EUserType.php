<?php
namespace App\Enums;


abstract class EUserType {
    const ADMIN = 0;
    const INTERNAL_USER = 2;
    const NORMAL_USER = 1;

    public static function getLocalizedName($value) {
    	switch ($value) {
			case self::ADMIN:
				return __('common/constant.user_type.admin');
			case self::INTERNAL_USER:
				return __('common/constant.user_type.staff');
			case self::NORMAL_USER:
				return __('common/constant.user_type.user');
		}
		return null;
	}
}
