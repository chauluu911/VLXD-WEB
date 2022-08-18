<?php


namespace App\Enums;


class EShopPaymentStatus {
    const NOT_VERIFY = 0;
    const VERIFIED = 1;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case self::NOT_VERIFY:
                return __('front/shop.payment_status.not_verify');
            case self::VERIFIED:
                return __('front/shop.payment_status.verified');
        }
    }
}
