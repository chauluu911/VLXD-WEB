<?php

namespace App\Enums;

use App\Constant\ConfigKey;

abstract class EHomeConfigType {
    const HOME = 1;
    const HOME_BUYER = 2;
    const HOME_SELLER = 3;
    const HOME_ADS = 4;
    const ABOUT_US = 5;

    public static function valueToName($value) {
        $result = '';
        switch ((int)$value) {
            case self::HOME:
                $result = ConfigKey::HOME_INTRODUCE;
                break;
            case self::HOME_BUYER:
                $result = ConfigKey::HOME_INTRODUCE_BUYER;
                break;
            case self::HOME_SELLER:
                $result = ConfigKey::HOME_INTRODUCE_SELLER;
                break;
            case self::HOME_ADS:
                $result = ConfigKey::HOME_INTRODUCE_ADVERTISER;
                break;
            case self::ABOUT_US:
                $result = ConfigKey::ABOUT_US;
                break;
        }
        return $result;
    }
}
