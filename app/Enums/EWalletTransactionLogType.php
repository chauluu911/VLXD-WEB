<?php
namespace App\Enums;


abstract class EWalletTransactionLogType {
    const BUY_COIN = 1;
    const PUSH_PRODUCT = 2;
    const POINT_REWARD_WHEN_BUY_ORDER = 3;
    const CHANGE_POINT_BY_ADMIN = 4;
    const CHANGE_COIN_BY_ADMIN = 5;
    const RECEIVED_COIN_COMMISSION_WHEN_UPDATE_LEVEL_SHOP = 6;
    const UPDATE_LEVEL_SHOP = 7;
}
