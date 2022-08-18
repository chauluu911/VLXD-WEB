<?php

namespace App\Http\Middleware;

use App\Helpers\StringUtility;
use Closure;

class LowerCaseFilter {
    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value) {
        if (request()->isMethod('get') && $key === 'q') {
            $value = StringUtility::convertViToEn(mb_strtolower($value));
        } elseif ($key === 'filter' && is_array($value)) {
            foreach ($value as $k => $val) {
                $value[$k] = is_string($val) && !is_numeric($val) ? StringUtility::convertViToEn(mb_strtolower($val)) : $val;
            }
        }
        return $value;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $modifyData = [];
        $backupData = [];
        if ($request->filled('q')) {
            $backupData['q'] = request('q');
            $modifyData['qOriginal'] = request('q');
            $modifyData['q'] = StringUtility::convertViToEn(mb_strtolower(request('q')));
        }
        if ($request->filled('filter')) {
            $backupData['filter'] = request('filter');
            $modifyData['filterOriginal'] = request('filter');
            $modifyData['filter'] = request('filter');
            foreach ($modifyData['filter'] as $k => $val) {
                $modifyData['filter'][$k] = is_string($val) && !is_numeric($val) ? StringUtility::convertViToEn(mb_strtolower($val)) : $val;
            }
        }
        $request->merge($modifyData);

        $response = $next($request);

        $request->merge($backupData);
        unset($request['qOriginal']);
        unset($request['filterOriginal']);

        return $response;
    }
}
