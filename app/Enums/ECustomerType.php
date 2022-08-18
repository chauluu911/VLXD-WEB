<?php
namespace App\Enums;


abstract class ECustomerType {
    const SELLER = 1;
    const BUYER = 2;
    const ADVERTISER = 3;

	public static function valueToLocalizedName($status) {
		switch ($status) {
			case self::SELLER:
				return __('common/constant.customer_type.seller');
			case self::BUYER:
				return __('common/constant.customer_type.buyer');
			case self::ADVERTISER:
				return __('common/constant.customer_type.advertiser');
		}
		return null;
	}
}
