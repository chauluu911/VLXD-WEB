<?php

namespace App\Enums\Payoo;

use App\Enums\EValueToNameType;
use Illuminate\Support\Str;

abstract class EPayooPaymentMethod {
	const E_WALLET = 1;
	const INTERNAL_CARD = 2;
	// const INTERNATIONAL_CARD_VN = 3;
	// const INTERNATIONAL_CARD_OVERSEA = 4;
	// const STORE = 5;
	// const QR = 6;

	public static function getAll() {
		return [
			self::E_WALLET,
			// self::INTERNATIONAL_CARD_VN,
			// self::INTERNATIONAL_CARD_OVERSEA, // this enum to check payment fee, but ERA is fee free, so not use this enum
			self::INTERNAL_CARD,
			// self::STORE,
			// self::QR,
		];
	}

	public static function valueToName($method, int $valueToNameType = EValueToNameType::DEFAULT) {
		$name = null;
		switch ($method) {
			case self::E_WALLET:
				$name = 'Payoo Account';
				break;
			case self::INTERNAL_CARD:
				$name = 'Bank Payment';
				break;
			// case self::INTERNATIONAL_CARD_VN:
			// case self::INTERNATIONAL_CARD_OVERSEA:
			// 	$name = 'Cc Payment';
			// 	break;
			// case self::STORE:
			// 	$name = 'Pay At Store';
			// 	break;
   //          case self::QR:
			// 	$name = 'Qr';
			// 	break;
		}
		if (!empty($name)) {
			switch ($valueToNameType) {
				case EValueToNameType::DEFAULT;
					return $name;
				case EValueToNameType::LOWER_CASE:
					return mb_strtolower($name);
				case EValueToNameType::UPPER_CASE:
					return mb_strtoupper($name);
				case EValueToNameType::KEBAB_CASE:
					return Str::kebab($name);
			}
		}
		return $name;
	}

	public static function nameToValue($name) {
	    foreach (self::getAll() as $method) {
	        if (in_array($name, [
                self::valueToName($method, EValueToNameType::DEFAULT),
                self::valueToName($method, EValueToNameType::LOWER_CASE),
                self::valueToName($method, EValueToNameType::UPPER_CASE),
                self::valueToName($method, EValueToNameType::KEBAB_CASE),
                strtolower(self::valueToName($method, EValueToNameType::KEBAB_CASE)),
            ])) {
	            return $method;
            }
        }
	    return null;
    }
}
