<?php

namespace App\Helpers;

use Html2Text\Html2Text;
use Illuminate\Support\Str;
use App\Enums\ELanguage;

class StringUtility {
    public static function convertViToEn($str, $additionRegexMap = []) {
        if (empty($str)) {
            return '';
        }

        $langMap = array_merge([
            'a' => 'àáạảãâầấậẩẫăằắặẳẵä',
            'c' => 'ç',
            'e' => 'èéẹẻẽêềếệểễë',
            'i' => 'ìíịỉĩïî',
			'n' => 'ñ',
            'o' => 'òóọỏõôồốộổỗơờớợởỡö',
            'u' => 'ùúụủũưừứựửữüû',
            'y' => 'ỳýỵỷỹ',
            'd' => 'đ',
            'A' => 'ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÄ',
			'C' => 'Ç',
            'E' => 'ÈÉẸẺẼÊỀẾỆỂỄË',
            'I' => 'ÌÍỊỈĨÏÎ',
			'N' => 'Ñ',
            'O' => 'ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÖ',
            'U' => 'ÙÚỤỦŨƯỪỨỰỬỮÜÛ',
            'Y' => 'ỲÝỴỶỸ',
            'D' => 'Đ',
        ], $additionRegexMap);

        foreach ($langMap as $replacement => $val) {
            $str = preg_replace("/([$val])/u", $replacement, $str);
        }
        return $str;
    }

	public static function shorten_text($text, $limit = 150, $keepOldCase = false) {
		if (empty($text)) {
			return "";
		}

		$length = mb_strlen($text);
		if ($length > $limit) {
			$text = Str::limit((new Html2Text($text))->getText(), $limit);
			if (!$keepOldCase) {
				return self::uc_sentence($text);
			}
		}
		return $text;
	}

	public static function html2text($text) {
    	if (empty($text)) {
    		return "";
		}
    	return (new Html2Text($text))->getText();
	}

	public static function uc_sentence($str) {
		if ($str) { // input
			$str = preg_replace('/' . chr(32) . chr(32) . '+/', chr(32), $str); // recursively replaces all double spaces with a space
			if (($x = mb_substr($str, 0, 10)) && ($x == mb_strtoupper($x))) $str = mb_strtolower($str); // sample of first 10 chars is ALLCAPS so convert $str to lowercase; if always done then any proper capitals would be lost
			$na = array('. ', '! ', '? '); // punctuation needles
			foreach ($na as $n) { // each punctuation needle
				if (mb_strpos($str, $n) !== false) { // punctuation needle found
					$sa = explode($n, $str); // split
					foreach ($sa as $s) $ca[] = mb_ucfirst($s); // capitalize
					$str = implode($n, $ca); // replace $str with rebuilt version
					unset($ca); //  clear for next loop
				}
			}
			return mb_ucfirst(trim($str)); // capitalize first letter in case no punctuation needles found
		}
	}

	public static function getCurrencyFormat($value, $languageCode) {
		return number_format($value, $languageCode === ELanguage::VI ? 0 : 2);
	}

	public static function getLinkTikTok($url) {
        $UserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($curl);
        curl_close($curl);

        $tagStartLink = '<link rel="canonical" href="';
        $startLink = strpos($data, $tagStartLink) + strlen($tagStartLink);
        $endLink = strpos($data, '"', $startLink);

        return [
            'src' => substr($data, $startLink, $endLink - $startLink),
        ];
    }
}
