<?php


namespace App\Enums;


class EApprovalStatus {
    const WAITING = 0;
    const APPROVED = 1;
    const DENY = -1;
    const SUSPENDED = 2;

    public static function valueToLocalizedName($status) {
        switch ($status) {
            case self::WAITING:
                return 'Chờ duyệt';
            case self::APPROVED:
                return 'Hoạt động';
            case self::DENY:
                return 'Từ chối';
            case self::SUSPENDED:
                return 'Bị từ chối';
        }
        return null;
    }
}
