<?php


namespace App\Enums;


class EPaymentType {
    const PUSH_PRODUCT = 1;
    const BUY_COINS = 2;
    const UPGRADE_SHOP = 3;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case self::PUSH_PRODUCT:
                return 'Giá đẩy gói tin';
            case self::BUY_COINS:
                return 'Mua xu';
            case self::UPGRADE_SHOP:
                return 'Nâng cấp shop';
            
        }
    }
}
