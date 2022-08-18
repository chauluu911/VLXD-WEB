<?php

namespace App\Enums;

abstract class ECreatedFrom {
    const WEB = 1;
    const ANDROID = 2;
    const IOS = 3;

    public static function valueToName($value) {
        switch ($value) {
            case self::WEB:
                return 'Web';
            case self::ANDROID:
                return 'Android';
            case self::IOS:
                return 'iOs';
        }
        return null;
    }
}