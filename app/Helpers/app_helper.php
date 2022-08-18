<?php

use App\Constant\ConfigKey;
use App\Enums\ELanguage;
use App\Helpers\ConfigHelper;
use Illuminate\Support\Str;

if (!function_exists('mb_ucfirst') && function_exists('mb_substr')) {
    function mb_ucfirst($string) {
        $string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
        return $string;
    }
}

if (!function_exists('encode_uri_component')) {
    function encode_uri_component($str) {
        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
        return strtr(rawurlencode($str), $revert);
    }
}

/*if (!function_exists('get_display_date_on_view')) {
    function get_display_date_on_view($date, $tz = null, string $to_formal = EDateFormat::STANDARD_DATE_FORMAT_2, $from_format = null) {
    	$tmp = \App\Helpers\DateUtility::tryParsedDateFromFormat($date, $tz, $from_format);
    	if (is_null($tmp)) {
    		return '';
    	}

		if ($to_formal === EDateFormat::LOCALIZE_DATE_FORMAT || $to_formal === EDateFormat::LOCALIZE_DATE_TIME_FORMAT) {
			return $tmp->formatLocalized($to_formal);
		}
		return $tmp->format($to_formal);
    }
}*/

if (!function_exists('get_display_date_for_ajax')) {
	function get_display_date_for_ajax($date, $tz = null, $from_format = null) {
	    if (is_null($date)) {
	        return null;
        }

		$tmp = \App\Helpers\DateUtility::tryParsedDateFromFormat($date, $tz, $from_format);
		if (is_null($tmp)) {
			return null;
		}
		return $tmp->toIso8601String();
	}
}

if (!function_exists('get_previous_route_name')) {
	function get_previous_route_name() {
		return app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();
	}
}

if (!function_exists('get_phone_number')) {
	function get_phone_number($phone_number, $dial_code = '84') {
		if (empty($phone_number)) {
			return '';
		}
		$phone = str_replace([' ', '-', '(', ')', '+'], '', $phone_number);
		$dial_code = str_replace('+', '', $dial_code);

		$phone = ltrim($phone, '0');
		if (Str::startsWith($phone, $dial_code)) {
			$phone = substr($phone, strlen($dial_code));
		}

		return $phone;
	}
}

if (!function_exists('normalize_phone_number')) {
	function normalize_phone_number(string $phone_number) {
		$phone = str_replace([' ', '-', '(', ')', '+'], '', $phone_number);

		$phone = ltrim($phone, '0');

		return $phone;
	}
}

if (!function_exists('normalize_affiliate_code')) {
	function normalize_affiliate_code(string $ref_code) {
		return str_replace([' ', '-', '(', ')', '+', '/', '\\'], '', $ref_code);
	}
}

if (!function_exists('add_css_class_for_email')) {
	function add_css_class_for_email(string $cssName) {
		$tmp = null;
		switch ($cssName) {
			case 'body':
				$tmp = [
					'font-family' => '"Open Sans", sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
					'font-size' => '14px',
					'background-color' => '#f3f3f3',
					'margin' => 0,
					'padding' => 0
				];
				break;
			case 'btn':
				$tmp = [
					'display' => 'inline-block',
					'font-weight' => '400',
					'text-align' => 'center',
					'white-space' => 'nowrap',
					'vertical-align' => 'middle',
					'-webkit-user-select' => 'none',
					'-moz-user-select' => 'none',
					'-ms-user-select' => 'none',
					'user-select' => 'none',
					'border' => '1px solid transparent',
					'padding' => '10px 20px',
					'line-height' => '1.5',
					'border-radius' => '5px',
					'-webkit-transition' => 'color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out',
					'transition' => 'color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out',
					'transition' => 'color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out',
					'transition' => 'color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out',
					'color' => 'white',
					'background-color' => '#c5001e',
				];
				break;
			case 'container':
				$tmp = [
					'background-color' => 'white',
					'margin-left' => 'auto',
					'margin-right' => 'auto',
					'box-shadow' => '0px 25px 50px 5px #c8c8c8 !important',
					'max-width' => '576px',
					'margin-top' => '50px',
					'margin-bottom' => '50px'
				];
				break;
			case 'header':
				$tmp = [
					'padding' => '40px 40px 0px'
				];
				break;
			case 'divider':
				$tmp = [
					'margin' => '10px 40px',
					'border-bottom' => '1px solid #ededed'
				];
				break;
			case 'content':
				$tmp = [
					'padding' => '0 40px 40px'
				];
				break;
			case 'footer':
				$tmp = [
					'padding' => '40px',
					'background-color' => 'black',
					'color' => 'white'
				];
				break;
			case 'footer.a':
				$tmp = [
					'color' => '#198a7b'
				];
				break;
		}

		if (empty($tmp)) {
			return '';
		}

		return array_reduce(array_keys($tmp), function ($carry, $item) use ($tmp) {
			return "$carry$item: $tmp[$item]; ";
		}, '');
	}
}

if (!function_exists('get_item_path_from_id')) {
	/**
	 * @param int $itemId
	 * @param string $itemType
	 * @return mixed
	 * @throws Exception
	 */
	function get_item_path_from_id(int $itemId, string $itemType) {
		$itemUrlService = new \App\Services\ItemUrlService();
		return $itemUrlService->getUrlPath($itemId, $itemType);
	}
}

if (!function_exists('get_item_id_from_path')) {
	function get_item_id_from_path(string $path, string $itemType) {
		$itemUrlService = new \App\Services\ItemUrlService();
		$itemUrl = $itemUrlService->getLastItemPath($path, $itemType);
		if (!isset($itemUrl)) {
			return null;
		}
		return $itemUrl->item_id;
	}
}

if (!function_exists('get_money_by_locale')) {
	function get_money_by_locale($amount, string $locale = null, bool $withCurrency = true, string $customCurrency = null) {
		if (!is_numeric($amount)) {
			return null;
		}

		$amount = (float)$amount;
		if (empty($locale)) {
			$locale = app()->getLocale();
		}
		if ($amount > 0 && $locale !== ELanguage::VI) {
			$exchangeRate = ConfigHelper::get(ConfigKey::EXCHANGE_RATE);
			$exchangeRate = json_decode($exchangeRate);
			if (!empty($exchangeRate)) {
				try {
					switch ($locale) {
						case ELanguage::JP:
							$amount /= $exchangeRate->jpy->buy;
							break;
						case ELanguage::EN:
						default:
							$amount /= $exchangeRate->usd->buy;
							break;
					}
				} catch (Exception $e) {
					logger('failed to get_money_by_locale', ['e' => $e]);
				}
			}
		}

		switch ($locale) {
			case ELanguage::VI:
				$digits = -2;
				break;
			case ELanguage::EN:
			default:
				$digits = 2;
				break;
		}

		$amount = number_format($amount, $digits);
		if (!$withCurrency) {
			return $amount;
		}
		if (!empty($customCurrency)) {
			return "$amount $customCurrency";
		}
		if ($locale === ELanguage::VI) {
			return "$amount đ";
		}
		switch ($locale) {
			case ELanguage::VI;
				$customCurrency = 'Đ';
				break;
			case ELanguage::EN;
				$customCurrency = '$';
				break;
		}
		return "$customCurrency$amount";
	}
}

if (!function_exists('log_user_submission')) {
	function log_user_submission($content, $context = []) {
		if (empty($content)) {
			return;
		}
		try {
			$logger = new \Monolog\Logger('user-submission');
			$logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/user-submission.log')));
			$logger->info($content, $context);
		} catch (Exception $e) {
			logger()->warning('log submission failed', [
				'content' => $content,
				'context' => $context,
				'error' => $e
			]);
		}
	}
}

if (!function_exists('unparse_url')) {
	function unparse_url($parsed_url) {
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
		return "$scheme$user$pass$host$port$path$query$fragment";
	}
}

// https://wp-mix.com/php-add-remove-query-string-variables/
if (!function_exists('add_query_string')) {
	function add_query_string($uri, $key, $value) {
		$url = preg_replace('/(.*)(?|&)'. $key .'=[^&]+?(&)(.*)/i', '$1$2$4', $uri .'&');
		$url = substr($url, 0, -1);

		if (strpos($url, '?') === false) {
			return ($url .'?'. $key .'='. $value);
		}
		return ($url .'&'. $key .'='. $value);
	}
}

if (!function_exists('remove_query_string')) {
	function remove_query_string($uri, $key) {
		$url = preg_replace('/(.*)(\?|&)'. $key .'=[^&]+?(&)(.*)/i', '$1$2$4', $uri .'&');
		$url = substr($url, 0, -1);
		return ($url);
	}
}

if (!function_exists('get_image_url')) {
	function get_image_url($options) {
		$action = \Illuminate\Support\Arr::get($options, 'action', 'display');

		if (config('app.env') === 'production') {
            $uri = config('app.resource_url_path') . "/sr/$action";
            if (\Illuminate\Support\Arr::has($options, 'path') && Str::startsWith($options['path'], ['https://', 'http://'])) {
                $options['url'] = $options['path'];
                unset($options['path']);
            } elseif (\Illuminate\Support\Arr::has($options, 'url') && !Str::startsWith($options['url'], ['https://', 'http://'])) {
                $options['path'] = $options['url'];
                unset($options['url']);
            }

            // document: https://github.com/thoas/picfit#general-parameters
            foreach (['op', 'w', 'h', 'path', 'url', 'q'] as $key) {
                if (\Illuminate\Support\Arr::has($options, $key)) {
                    $uri = add_query_string($uri, $key, $key === 'url' ? encode_uri_component($options[$key]) : $options[$key]);
                }
            }
        } else {
		    $uri = \App\Helpers\FileUtility::getFileResourcePath(\Illuminate\Support\Arr::get($options, 'url', \Illuminate\Support\Arr::get($options, 'path')));
        }

		return $uri;
	}
}
