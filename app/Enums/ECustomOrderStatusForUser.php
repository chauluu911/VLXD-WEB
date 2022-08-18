<?php


namespace App\Enums;


class ECustomOrderStatusForUser{
    const CANCELED = -1;
    const WAITING = 0;
    const WAITING_FOR_DELIVERY = 1;
    const ON_THE_WAY = 2;
    const DELIVERED_SUCCESS = 3;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case ECustomOrderStatusForUser::CANCELED:
                return __('common/constant.custom_order_status_for_user.canceled');
            case ECustomOrderStatusForUser::WAITING_FOR_DELIVERY:
                return __('common/constant.custom_order_status_for_user.waiting_for_delivery');
            case ECustomOrderStatusForUser::ON_THE_WAY:
                return __('common/constant.custom_order_status_for_user.on_the_way');
            case ECustomOrderStatusForUser::DELIVERED_SUCCESS:
                return __('common/constant.custom_order_status_for_user.delivery_success');
            case ECustomOrderStatusForUser::WAITING:
                return __('common/constant.custom_order_status_for_user.waiting');
        }
        return null;
    }
}
