<?php
namespace App\Enums;


abstract class EConnectStatus {
    const WAITING = 0;
    const CONNECTED = 1;

    public static function getValueToName($value) {
    	switch ($value) {
			case self::WAITING:
				return __('back/user.contact.connect.status.waiting');
			case self::CONNECTED:
				return __('back/user.contact.connect.status.connected');
		}
		return null;
	}
}
