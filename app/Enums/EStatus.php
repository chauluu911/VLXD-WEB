<?php


namespace App\Enums;


class EStatus {
    const DELETED = -1;
    const WAITING = 0;
    const DRAFT = 0;
    const PENDING = 0;
    const ACTIVE = 1;
    const SUSPENDED = 2;
    const APPROVED = 1;
    const EXCEPT_DELETED = 3;

    public static function valueToLocalizedName($status, $alternateLocalize = false) {
        switch ($status) {
            case EStatus::DELETED:
                return __('common/constant.status.deleted');
            case EStatus::WAITING:
                return __('common/constant.status.pending');
            case EStatus::SUSPENDED:
                return __('common/constant.status.suspend');
            case EStatus::ACTIVE:
                return 
                    // $alternateLocalize ? __('common/constant.status.approved') :
                     __('common/constant.status.active');
        }
        return null;
    }
}
