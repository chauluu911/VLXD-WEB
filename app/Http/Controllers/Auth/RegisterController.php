<?php

namespace App\Http\Controllers\Auth;

use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\EUserType;
use App\Enums\ELoginStage;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller {
	/*
	|--------------------------------------------------------------------------
	| Register Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users as well as their
	| validation and creation. By default this controller uses a trait to
	| provide this functionality without requiring any additional code.
	|
	*/

	use RegistersUsers;

	/**
	 * Where to redirect users after registration.
	 *
	 * @var string
	 */
	protected $redirectTo = '/';

	private UserService $userService;

	/**
	 * Create a new controller instance.
	 */
	public function __construct(UserService $userService) {
		$this->middleware('guest');
		$this->userService = $userService;
	}

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function validator(array $data) {
		return Validator::make($data, [
			'name' => ValidatorHelper::nameRule(['required' => true]),
			'phone' => ValidatorHelper::phoneRule(['required' => true]),
			'password' => ValidatorHelper::passwordRule(['required' => true, 'confirmed' => true]),
		]);
	}

	/**
	 * Show the application registration form.
	 *
	 * @return \Illuminate\View\View
	 */
	public function showRegistrationForm() {
		return view('front.register.register', [
			'login_stage' => ELoginStage::NOT_REGISTERED,
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return \App\User
	 */
	protected function create(array $data) {
		$data['status'] = EStatus::WAITING;
		$result = $this->userService->saveUser(
			array_merge([
				'tz' => session(SessionKey::TIMEZONE),
				'userType' => EUserType::NORMAL_USER,
				'sendVerifyOtp' => true,
			], $data),
			auth()->id()
		);
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			$rule = [];
			foreach ($result['msg'] as $key => $errorMessages) {
				$rule[$key] = [
					function($attribute, $value, $fail) use ($errorMessages) {
						if (is_array($errorMessages)) {
							$fail($errorMessages[0]);
						} else {
							$fail($errorMessages);
						}
					}
				];
			}
			request()->validate($rule);
			throw new \Exception($result['msg']);
		}
		return $result['user'];
	}

	/**
	 * The user has been registered.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  mixed  $user
	 * @return mixed
	 */
	protected function registered(Request $request, $user) {
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
		]);
	}
}