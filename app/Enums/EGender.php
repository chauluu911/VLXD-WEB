<?php
namespace App\Enums;

abstract class EGender {
    const MALE = 1;
    const FEMALE = 2;

    public static function getNameByValue($status) {
        switch ($status) {
            case self::MALE:
                return __('common/constant.gender.male');
            case self::FEMALE:
                return __('common/constant.gender.female');
        }
        return null;
    }
}
