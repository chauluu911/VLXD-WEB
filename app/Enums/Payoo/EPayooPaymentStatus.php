<?php

namespace App\Enums\Payoo;

abstract class EPayooPaymentStatus {
	const SUCCESS = 1;
	const FAILED = 0;
	const CANCELED = -1;

	public static function isValid($status) {
		if (!is_numeric($status)) {
			return false;
		}
		if (self::SUCCESS != $status
			&& self::FAILED != $status
			&& self::CANCELED != $status) {
			return false;
		}
		return true;
	}

	public static function nameToValue($name) {
		switch (mb_strtolower($name)) {
			case 'success':
				return self::SUCCESS;
			case 'failed':
				return self::FAILED;
			case 'canceled':
				return self::CANCELED;
		}
		return null;
	}

	public static function valueToName($val) {
		switch ($val) {
			case self::SUCCESS:
				return 'success';
			case self::FAILED:
				return 'failed';
			case self::CANCELED:
				return 'canceled';
		}
		return null;
	}
}