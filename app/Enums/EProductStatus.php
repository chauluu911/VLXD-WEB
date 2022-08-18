<?php


namespace App\Enums;


class EProductStatus {
    const REJECTED = -2;
    const DELETED = -1;
    const WAITING = 0;
    const ACTIVE = 1;
    const WAITING_AND_ACTIVE = 2;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case EProductStatus::REJECTED:
                return __('common/constant.product_status.rejected');
            case EProductStatus::DELETED:
                return __('common/constant.product_status.deleted');
            case EProductStatus::WAITING:
                return __('common/constant.product_status.waiting');
            case EProductStatus::ACTIVE:
                return __('common/constant.product_status.active');
            case EProductStatus::WAITING_AND_ACTIVE:
                return __('common/constant.product_status.waiting_and_active');
        }
        return null;
    }
}
