<?php

namespace App\Http\Controllers\Auth;

use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\EUserType;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Services\OtpCodeService;
use App\Services\UserService;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller {

	private UserService $userService;
	private OtpCodeService $otpCodeService;

	public function __construct(UserService $userService,
								OtpCodeService $otpCodeService) {
		$this->userService = $userService;
		$this->otpCodeService = $otpCodeService;
	}

	public function redirectToProvider($provider) {
		return Socialite::driver($provider)->redirect();
	}

	/**
	 * Xử lý sau khi redirect lại từ trang login của facebook hoặc google
	 * Step 1: Lấy hoặc tạo user
	 *     Case 1: Nếu user đã tạo thì trả về
	 *     Case 2: Nếu user chưa tạo thì lưu tạm vào session để user verify OTP
	 *         sau đó lấy thông tin từ session để lưu User mới ở hàm verifyOTPAndCreateUser
	 *
	 * Step 2:
	 *     Case 1: Nếu user đang bị khóa thì báo lỗi và đăng xuất
	 *     Case 2: Đăng nhập
	 *
	 * @param $provider
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function handleProviderCallback($provider) {
		try {
			$userInfo = Socialite::driver($provider)->user();
			logger('new user register or login with facebook or google', ['u' => $userInfo, 'provider' => $provider]);
		} catch (\Exception $e) {
			logger('Error when get user with socialite', ['e' => $e]);
			return redirect()->route('auth.provider', [
				'provider' => $provider
			]);
		}

		$user = $this->userService->findUserWithSocialite($provider, $userInfo);
		if (empty($user)) {
			$column_id_fb_gg = ($provider === 'google') ? 'gg_id' : 'fb_id';
			session()->put(SessionKey::OAUTH_USER_INFO, [
				'name' => $userInfo->name,
				'email' => $userInfo->email,
				$column_id_fb_gg => $userInfo->id,
				'avatar_path' => $userInfo->avatar,
				'status' => EStatus::WAITING,
				'type' => EUserType::NORMAL_USER,
				'customer_type' => session(SessionKey::REGISTER_CUSTOMER_TYPE),
				'password' => null,
			]);

			return redirect()->route('login', ['oauth_verify' => true]);
		}


		if($user && $user->status == EStatus::ACTIVE && $user->email != null) {
			auth()->login($user, true);
			return redirect()->to('/');
		}
		if($user && $user->status == EStatus::SUSPENDED) {
			return redirect()->to('/')->with([
				'msg' => __('front/auth.failed.lock')
			]);
		}
		return redirect()->to('/');
	}

	public function updateOAuthPhoneNumber() {
		$userInfo = session(SessionKey::OAUTH_USER_INFO);
		if (empty($userInfo)) {
			return response()->json([
				'redirect' => true,
				'redirectTo' => route('login', [], false),
			]);
		}

		request()->validate([
			'phone' => ValidatorHelper::phoneRule(['required' => true]),
		]);

		$userInfo['phone'] = request('phone');
		$result = $this->userService->createUserWithSocialite($userInfo, auth()->id());
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			$msg = $result['msg'];
			while (is_array($msg)) {
				$msg = end($msg);
			}
			$result['msg'] = $msg;
			return response()->json($result);
		}

		auth()->login($result['user']);

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'redirect' => true,
			'redirectTo' => route('home', [], false),
		]);
	}
}