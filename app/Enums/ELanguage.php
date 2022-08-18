<?php
namespace App\Enums;


abstract class ELanguage {
    const VI = 'vi';
    const EN = 'en';
    const FR = 'fr';

    public static function getSupportedLanguage()
    {
        return [
			self::EN,
            self::VI,
            self::FR,
        ];
    }

    public static function isSupportedLanguage($lang) {
        return in_array($lang, self::getSupportedLanguage());
    }
}