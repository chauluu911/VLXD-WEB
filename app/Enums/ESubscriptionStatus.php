<?php
namespace App\Enums;


abstract class ESubscriptionStatus {
    const NOT_PAID = 0;
    const PAID = 1;

    public static function getLocalizedName($value) {
    	switch ($value) {
			case self::NOT_PAID:
				return __('front/business.status.not_paid');
			case self::PAID:
				return __('front/business.status.paid');
		}
		return null;
	}
}
