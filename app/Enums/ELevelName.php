<?php


namespace App\Enums;


class ELevelName {
    const LEVEL_1 = 1;
    const LEVEL_2 = 2;
    const LEVEL_3 = 3;
    const LEVEL_4 = 4;
    const LEVEL_5 = 5;

    public static function valueToName($type) {
        switch ($type) {
            case ELevelName::LEVEL_1:
                return 'Chưa xác thực';
            case ELevelName::LEVEL_2:
                return 'Xác thực';
            case ELevelName::LEVEL_3:
                return 'Đảm bảo';
            case ELevelName::LEVEL_4:
                return 'Chuyên nghiệp';
            case ELevelName::LEVEL_5:
                return 'VIP';
        }
        return null;
    }
}
