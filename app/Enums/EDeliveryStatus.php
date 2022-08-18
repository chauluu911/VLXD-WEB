<?php


namespace App\Enums;


class EDeliveryStatus {
    const WAITING_FOR_APPROVAL = 0;
    const ON_THE_WAY = 1;
    const DELIVERED_SUCCESS = 2;
    const CUSTOMER_REFUSED = 3;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case self::WAITING_FOR_APPROVAL:
                return 'Chờ xác nhận từ người bán';
			case self::ON_THE_WAY:
                return 'Đang giao';
            case self::DELIVERED_SUCCESS:
                return 'Giao thành công';
            case self::CUSTOMER_REFUSED:
                return 'Khách hàng từ chối';
			default:
				return __('common/constant.payment_status.unpaid');
        }
    }
}
