<?php


namespace App\Enums;


class EOrderStatus {
    const DELETED = -3;
    const CANCEL_BY_USER = -2;
    const CANCEL_BY_SHOP = -1;
    const WAITING = 0;
    const CONFIRMED = 1;
    const SHOPPING_CART = 2;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case EOrderStatus::DELETED:
                return __('common/constant.order_status.deleted');
            case EOrderStatus::CANCEL_BY_USER:
                return __('common/constant.order_status.cancel_by_user');
            case EOrderStatus::CANCEL_BY_SHOP:
                return __('common/constant.order_status.cancel_by_shop');
            case EOrderStatus::WAITING:
                return __('common/constant.order_status.waiting');
            case EOrderStatus::CONFIRMED:
                return __('common/constant.order_status.confirmed');
        }
        return null;
    }
}
