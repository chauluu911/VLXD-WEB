<?php

namespace App\Http\Controllers;

use App\Constant\SessionKey;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function updateUserTimezone($request = null, $parameters = null) {
		$oldTz = Session::get(SessionKey::TIMEZONE);
		if (empty($oldTz)) {
			Session::put(SessionKey::TIMEZONE, config('app.timezone'));
		}
		if (isset($request)) {
			$tz = $request->get('_timezone', null);
		}
		if (isset($parameters[0]->headers)) {
			$tz = $parameters[0]->headers->get('timezone', null);
		}
		if (request()->header('timezone')) {
			$tz = request()->header('timezone');
		}
		if (request()->header('x_timezone')) {
			$tz = request()->header('x_timezone');
		}
		if (empty($tz) &&  array_key_exists('_timezone', $_POST)) {
			$tz = $_POST['_timezone'];
		}
		if (!empty($tz) && null !== $tz && $oldTz !== $tz) {
			Session::put(SessionKey::TIMEZONE, $tz);
		}
		if (auth()->check()) {
			$user = auth()->user();
			$tz = Session::get(SessionKey::TIMEZONE);
			if (isset($tz) && $user->tz !== $tz) {
				$user->tz = $tz;
				$user->save(['timestamps' => false]);
			}
		}

		$oldTzOffset = Session::get(SessionKey::TIMEZONE_OFFSET);
		if (!isset($oldTzOffset)) {
			Session::put(SessionKey::TIMEZONE_OFFSET, Carbon::now()->offsetHours);
		} elseif (request()->header('timezone')) {
			$tzOffset = request()->header('timezone_offset');
		}
		if (isset($tzOffset) && $oldTzOffset !== $tzOffset) {
			Session::put(SessionKey::TIMEZONE_OFFSET, $tzOffset);
		}
	}

	public function callAction($method, $parameters) {
		$this->updateUserTimezone(null, $parameters);
		return parent::callAction($method, $parameters);
	}
}
