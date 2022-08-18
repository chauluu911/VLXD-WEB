<?php

namespace App\Http\Controllers\Auth;

use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Enums\EStatus;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Services\OtpCodeService;
use App\Services\UserService;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset sms
    |
    */

	private OtpCodeService $otpCodeService;
	private UserService $userService;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(UserService $userService,
								OtpCodeService $otpCodeService) {
		$this->middleware('guest');
		$this->otpCodeService = $otpCodeService;
		$this->userService = $userService;
	}

	private function getRequestUser() {
		return $this->userService->getByOptions([
			'phone' => request('phone'),
			'not_status' => EStatus::DELETED,
			'first' => true,
		]);
	}
	public function forgotView() {
		return view('front.login.forgot-password');
	}
	public function validatePhoneNumber(Request $request) {
		$request->validate([
			'phone' => array_merge(ValidatorHelper::phoneRule(['required']), [
				function($attribute, $value, $fail) {
					$userExists = !empty($this->getRequestUser());
					if (!$userExists) {
						$fail(__('front/auth.failed.deleted'));
					}
				}
			]),
		]);
	}

	/**
	 * Send a reset OTP to the given user.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	public function sendResetLinkEmail(Request $request) {
		$this->validatePhoneNumber($request);

		$user = $this->getRequestUser();

		$result = $this->otpCodeService->sendOtp(
			$user,
			EOtpType::VERIFY_EMAIL_WHEN_FORGOT_PASSWORD,
			/*[
				'sendNow' => true,
			]*/
		);

		return $result['error'] == EErrorCode::NO_ERROR ?
			$this->sendResetLinkResponse($request, PasswordBroker::RESET_LINK_SENT) :
			$this->sendResetLinkFailedResponse($request, __('common/error.system-error'));
	}

	/**
	 * Get the response for a successful password reset link.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string  $response
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	protected function sendResetLinkResponse(Request $request, $response) {
		if ($request->ajax()) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'message' => __($response),
			]);
		}
		return back()->with('status', trans($response));
	}

	/**
	 * Get the response for a failed password reset link.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string  $response
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	protected function sendResetLinkFailedResponse(Request $request, $response) {
		if ($request->ajax()) {
			return response()->json([
				'error' => EErrorCode::ERROR,
				'message' => __($response),
			]);
		}
		return back()
			->withInput($request->only('email'))
			->withErrors(['email' => trans($response)]);
	}

	public function verify() {
		request()->validate([
			'phone' => ValidatorHelper::phoneRule(['required' => true]),
			'otp' => [
				'required',
				function($attribute, $value, $fail) {
					$user = $this->userService->getByOptions([
						'phone' => (string)request('phone'),
						'not_status' => EStatus::DELETED,
						'first' => true
					]);
					if (empty($user)) {
						$fail(__('validation.custom.not_valid', [
							'attribute' => __('validation.attributes.phone')
						]));
					}
					$isValid = $this->otpCodeService->checkOTPCode($value, $user->id ?? null);
					if (!$isValid) {
						$fail(__('validation.custom.not_valid', [
							'attribute' => __('validation.attributes.otp')
						]));
					}
				}
			],
		]);

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
		]);
	}
}
