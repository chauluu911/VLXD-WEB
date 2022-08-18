<?php

namespace App\Http\Controllers\Auth;

use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Enums\EStatus;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Services\OtpCodeService;
use App\Services\UserService;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

	private OtpCodeService $otpCodeService;
	private UserService $userService;

	public function __construct(OtpCodeService $otpCodeService,
								UserService $userService) {
		$this->otpCodeService = $otpCodeService;
		$this->userService = $userService;
	}

	public function reset() {
		request()->validate([
			'otp' => 'required',
			'phone' => ValidatorHelper::phoneRule(['required' => true]),
			'password' => ValidatorHelper::passwordRule(['required' => true, 'confirmed' => true]),
		]);

		// Here we will attempt to reset the user's password. If it is successful we
		// will update the password on an actual user model and persist it to the
		// database. Otherwise we will parse the error and return the response.

		$user = $this->userService->getByOptions([
			'phone' => request('phone'),
			'not_status' => EStatus::DELETED,
			'first' => true,
		]);
		$result = $this->otpCodeService->verifyOtpCodeAndProcessByOtpType(
			$user,
			(string)request('otp'),
			EOtpType::VERIFY_EMAIL_WHEN_FORGOT_PASSWORD,
			[
				'password' => Hash::make(request('password')),
			]
		);
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => __('front/auth.auth-area.reset-success-msg'),
			'redirectTo' => route('login', [], false),
		]);
	}
}
