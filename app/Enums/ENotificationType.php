<?php

namespace App\Enums;


abstract class ENotificationType {
	const SYSTEM = 1;
    const APPROVED_PRODUCT = 2;
    const FOLLOW = 3;
    const APPROVED_PAYMENT_COINS = 4;
    const APPROVED_SHOP = 5;
    const APPROVED_NOTIFICATION_CREATED_BY_SHOP = 6;
    const APPROVED_BANNER = 7;
    const RECEIVED_COMMISSION = 8;
    const RECEIVED_ACCUMULATE_POINT = 9;
    const RECEIVED_NOTIFICATION_PRODUCT_CREATED_BY_SHOP = 10;
    const APPROVED_PUSH_PRODUCT = 11;
    const APPROVED_UPGRADE_SHOP = 12;
    const RECEIVED_NOTIFICATION_ORDER_CREATED_BY_USER = 13;
    const APPROVED_ORDER = 14;
    const MESSAGE_NOTIFICATION = 20;

	public static function valueToString($value) {
        switch ($value) {
            case ENotificationType::SYSTEM:
                return 'Hệ thống';
        }
        return null;
    }
}