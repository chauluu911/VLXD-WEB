<?php

namespace App\Http\Controllers\Auth;

use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Http\Controllers\Controller;
use App\Services\OtpCodeService;
use App\Services\UserService;
use App\Traits\EmailSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;

class VerificationController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/';

    private UserService $userService;
    private OtpCodeService $otpCodeService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService,
								OtpCodeService $otpCodeService) {
    	if(!request('phone')) {
    		$this->middleware('auth');
    	}
        $this->middleware('throttle:60,1')->only('verify', 'resend');

        $this->userService = $userService;
        $this->otpCodeService = $otpCodeService;
    }

	public function verify() {
		$result = $this->otpCodeService->verifyOtpCodeAndProcessByOtpType(
			auth()->user(),
			(string)request('otp'),
			EOtpType::VERIFY_EMAIL_WHEN_REGISTER,
		);
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
		]);
	}

	public function verified() {
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
		]);
	}

	public function resend() {
		if (request('phone')) {
			$user = $this->userService->getByOptions([
				'phone' => request('phone'),
				'first' => true,
			]);
		} else if (request()->user()->hasVerifiedEmail()) {
			return request()->wantsJson()
				? response()->json([], 204)
				: redirect($this->redirectPath());
		}

		$this->otpCodeService->sendOtp(!empty(request('phone')) ? $user : auth()->user(), EOtpType::VERIFY_EMAIL_WHEN_REGISTER);

		return request()->wantsJson()
			? response()->json([], 202)
			: back()->with('resent', true);
	}
}
