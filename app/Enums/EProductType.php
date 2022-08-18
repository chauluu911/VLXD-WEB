<?php

namespace App\Enums;

class EProductType {
    const PRODUCT = 1;
    const POST = 2;

    public static function valueToName($type) {
        switch ($type) {
            case EProductType::PRODUCT:
                return 'Sản phẩm đăng bán';
            case EProductType::POST:
                return 'Tin đăng';
        }
        return null;
    }
}
