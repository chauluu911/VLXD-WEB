<?php


namespace App\Enums;


class EPaymentMethod {
    const BANK_TRANSFER = 1;
    const PAYMENT_GATEWAY = 2;
    const COD = 3;
    const COIN = 4;
    const FREE = 100;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case self::BANK_TRANSFER:
                return 'Chuyển khoản';
            case self::PAYMENT_GATEWAY:
            default:
                return 'Thanh toán trực tuyến';
            case self::COD:
                return 'COD';
            case self::COIN:
                return 'Xu';
            case self::FREE:
                return 'Đẩy tin miễn phí';
        }
    }
}
