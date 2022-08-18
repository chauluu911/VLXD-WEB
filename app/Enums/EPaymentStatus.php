<?php


namespace App\Enums;


class EPaymentStatus {
    const UNPAID = 0;
    const PAID = 1;
    const WAITING = 0;
    const PAYMENT_RECEIVED = 1;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case self::PAID:
                return __('common/constant.payment_status.paid');
            case self::UNPAID:
            default:
                return __('common/constant.payment_status.unpaid');
            case self::WAITING:
                return 'Chưa thanh toán';
            case self::PAYMENT_RECEIVED:
                return 'Đã thanh toán';
        }
    }
}
