<?php

namespace App\Http\Controllers\Front;

use App\Constant\DefaultConfig;
use App\Enums\EErrorCode;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Http\Requests\User\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Enums\ELanguage;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserService;
use App\Http\Requests\User\SaveInfoRequest;

class UserController extends Controller {

	public function __construct(UserService $userService) {
		$this->userService = $userService;
	}

	public function showProfileView() {
		return redirect()->route('profile.edit');
	}

	public function showProfileEditView() {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		return view('front.profile.personal-info', [
			'isEdit' => true,
		]);
	}

    public function showProfileChangePasswordView() {
        if (!auth()->id()) {
            return redirect()->route('home');
        }
        return view('front.profile.change-password');
    }

    public function changePassword(ChangePasswordRequest $request) {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }
        $validated = $request->validated();
        $user = $this->userService->getById($userId);
        if (!Hash::check(Arr::get($validated, 'oldPassword'), $user->password)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'errors' => ['oldPassword' => [__('back/changePassword.message.password-incorect')]],
                'msg' => __('back/changePassword.message.password-incorect')
            ]);
        }
        if (Arr::get($validated, 'newPassword') != Arr::get($validated, 'confirmPassword')) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'errors' => ['confirmPassword' => [__('validation.confirmed',
                    ['attribute' => __('back/changePassword.re_enter_new_password')])]],
                'msg' => __('validation.confirmed',
                    ['attribute' => __('back/changePassword.re_enter_new_password')]),
            ]);
        }
        $changePassword = $this->userService->changePassword($userId, $validated);
        if (!empty($changePassword)) {
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'profile' => route(
                    'profile', [], false
                ),
                'msg' => __('common/common.change-password-success'),
            ]);
        }
    }

	public function saveProfile(SaveInfoRequest $request) {
		try {
			$data = $request->validated();
			$data['avatar'] = request('avatar');
			$data['id'] = request('id');
			$data['dob'] = request('dob');
			$data['latitude'] = request('latitude');
			$data['longitude'] = request('longitude');
			$data['gender'] = request('gender');
			if (!empty($data['dob'])) {
				$data['dob'] = str_replace('/', '-', $data['dob']);
			}
			$result = $this->userService->saveUser($data, auth()->id());
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($result);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'profile' => route(
					'profile', [], false
				),
				'msg' => __('common/common.save-data-success', [
					'objectName' => __('back/user.object_name')
				]),
			]);
		} catch (\Exception $e) {
			logger()->error('Fail to save user', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}

    public function getCoinAndPoint() {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }
        $coinAndPoint = $this->userService->getCoinAndPoint($userId);
        return response()->json($coinAndPoint);
    }
}
