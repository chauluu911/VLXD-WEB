<?php


namespace App\Enums;

use App\Enums\EStatus;

class EAdvertiseStatus {
    const APPROVED = 1;
    const WAITING = 0;
    const DELETED = -1;

    public static function getStatusString($status) {
        switch ($status) {
            case EStatus::APPROVED:
                return __('back/user.advertise-list.status.approved');
            case EStatus::WAITING:
                return __('back/user.advertise-list.status.waiting');
            case EStatus::DELETED:
                return __('common/constant.status.deleted');
        }
        return null;
    }
}
