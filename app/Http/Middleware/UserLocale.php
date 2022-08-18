<?php

namespace App\Http\Middleware;

use App\Constant\SessionKey;
use App\Enums\ELanguage;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserLocale {

	public function __construct() {

	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		/*
		 * - testuru.com/vi -> vi
		 * - testuru.com -> detected locale
		 * - testuru.com/vi?lang=jp -> jp
		 * - testuru.com?lang=vi -> vi
		 * - testuru.com/vi/about-us -> vi
		 * - testuru.com/vi/about-us?lang=jp -> jp
		 * - testuru.com/about-us -> detected locale
		 * - testuru.com/about-us?lang=jp -> jp
		 */

		$detectedLocale = $request->header('X-COUNTRY-CODE');
		if (!empty($detectedLocale)) {
			$detectedLocale = strtolower($detectedLocale);
		}
		if ($detectedLocale === 'vn') {
			$detectedLocale = ELanguage::VI;
		}

		$defaultLocale = config('app.locale');
		$requestUri = trim($request->getRequestUri(), '/');
		$queryLang = trim(strtolower($request->query('lang', '')));

		if (!empty($queryLang) && ELanguage::isSupportedLanguage($queryLang)) {
			$locale = $queryLang;
		} else {
			$locale = $this->getLocaleFromRequestPath($request);
		}

		if (auth()->check()) {
			// $locale = auth()->user()->language_code;
			$locale = app()->getLocale();
		}

		if (empty($locale)) {
			$locale = session(SessionKey::LANG);
		}

		if (empty($locale)) {
			$locale = $detectedLocale;
		}

		// fallback
		if (empty($locale) || !ELanguage::isSupportedLanguage($locale)) {
			$locale = $defaultLocale;
		}

		$redirectUri = remove_query_string($requestUri, 'lang');
		$redirectUri = $this->fixLocatePartInRequestUri($redirectUri, $locale);
		$this->setLocale($locale);

		// if (auth()->check() && $locale != auth()->user()->language_code) {
		// 	$user = auth()->user();
		// 	$user->language_code = $locale;
		// 	$user->save(['timestamps' => false]);
		// }

		if ($request->ajax()) {
			return $next($request);
		}

		if ($locale !== $defaultLocale) {
			try {
				$route = app('router')->getRoutes()->match(app('request')->create($redirectUri));
			} catch (\Throwable $e) {
				// logger()->error('create route failed', ['exception' => $e]);
				// remove locale path
				$redirectUri = str_replace("/$locale/", "/", $redirectUri);
			}
		}

		if ($requestUri !== $redirectUri && "/$requestUri" !== $redirectUri) {
			$parseUrls = parse_url($requestUri);
			if (Arr::has($parseUrls, 'path')
				&& Str::startsWith(Arr::get($parseUrls, 'path'), 'blog/')) {
				return redirect($redirectUri, Response::HTTP_MOVED_PERMANENTLY);
			}
			return redirect($redirectUri);
		}
		return $next($request);
	}

	private function setLocale(string $locale) {
		app()->setLocale($locale);
		session([SessionKey::LANG => $locale]);
		URL::defaults(['locale' => $locale]);
	}

	private function fixLocatePartInRequestUri(string $requestUri, string $targetLocale) {
		$defaultLocale = config('app.locale');

		$requestUri = trim($requestUri, '/');
		$parseUrls = parse_url($requestUri);

		$matchedLocaleInRequestUri = Arr::first(ELanguage::getSupportedLanguage(), function($item) use ($requestUri, $parseUrls) {
			return $requestUri === $item
				|| Str::startsWith($requestUri, "$item/")
				|| Arr::get($parseUrls, 'path') === $item;
		});

		if ($defaultLocale === $targetLocale) {
			// remove locate prefix in the uri, if any
			if (!empty($matchedLocaleInRequestUri)) {
				if (ELanguage::isSupportedLanguage(Arr::get($parseUrls, 'path'))) {
					$parseUrls['path'] = '/';
					return unparse_url($parseUrls);
				}
				return str_replace("$matchedLocaleInRequestUri/", '/', $requestUri);
			}
			return $requestUri;
		}

		if (empty($matchedLocaleInRequestUri)) {
			// append locale prefix to the request uri
			return "/$targetLocale/$requestUri";
		}

		if ($matchedLocaleInRequestUri === $targetLocale) {
			return "/$requestUri";
		}

		// change locale prefix if switch to a locale other than the one currently in uri
		if (ELanguage::isSupportedLanguage(Arr::get($parseUrls, 'path'))) {
			$parseUrls['path'] = $targetLocale;
			return unparse_url($parseUrls);
		}
		return str_replace("$matchedLocaleInRequestUri/", "/$targetLocale/", $requestUri);
	}

	/**
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getLocaleFromRequestPath($request) {
		$pathInfo = trim($request->getPathInfo(), '/');
		if (empty($pathInfo)) {
			return null;
		}

		$locale = Arr::first(ELanguage::getSupportedLanguage(), function($item) use ($pathInfo) {
			return $pathInfo === $item || Str::startsWith($pathInfo, "$item/");
		});
		return $locale;
	}
}
