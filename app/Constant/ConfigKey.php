<?php

namespace App\Constant;

abstract class ConfigKey {

	const CUMULATIVE_POINTS = 'cumulative_points';
	const EXCHANGE_COINS = 'exchange_coins';
	const WALLET_DEPOSIT_GUIDE_AMOUNTS = 'wallet.deposit.guide-amounts';

	const CONTACT_ADDRESS = 'contact.address';
	const CONTACT_PHONE = 'contact.phone';
	const CONTACT_EMAIL = 'contact.email';
    const SHOP_LEVEL_COMMISSION = 'shop.level.commission';
    const BANNER_IMAGE_SPECS_WEB = 'banner.image.specs.web';
    const BANNER_IMAGE_SPECS_MOBILE = 'banner.image.specs.mobile';
    const QUESTIONS_ASK = 'questions.ask.shop';
    const BANK_TRANSFER_INFO = 'bank_transfer_info';

    const TERMS_AND_CONDITIONS = 'terms.and.conditions';
    const USER_GUIDE = 'guide_user';
    const PAYMENT_POLICY = 'payment_policy';
    const TRANSPORTATION_POLICY = 'transportation_policy';
    const PRIVACY_POLICY = 'privacy_policy';
    const BUY_POLICY = 'buy_policy';
    const ABOUT_US = 'about_us';
    const USER_ADMIN_ID = 'user_admin_id';
    
    public static function getNotToBeCacheKey() {
        return [
            self::CUMULATIVE_POINTS,
            self::EXCHANGE_COINS,
            self::WALLET_DEPOSIT_GUIDE_AMOUNTS,
            self::CONTACT_ADDRESS,
            self::CONTACT_PHONE,
            self::CONTACT_EMAIL,
            self::TERMS_AND_CONDITIONS,
            self::USER_GUIDE,
            self::PAYMENT_POLICY,
            self::TRANSPORTATION_POLICY,
            self::PRIVACY_POLICY,
            self::BUY_POLICY,
            self::ABOUT_US,
        ];
    }
}
