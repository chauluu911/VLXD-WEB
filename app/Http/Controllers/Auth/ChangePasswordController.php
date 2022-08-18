<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Enums\EErrorCode;

class ChangePasswordController extends Controller {
	protected UserService $userService;

    public function __construct(UserService $userService) {
		$this->userService = $userService;
    }

    public function changePassword(ChangePasswordRequest $request) {
    	$validated = $request->validated();
    	$userId = auth()->id();
    	try {
            $result = $this->userService->changePassword($userId, $validated);
            if ($result['error'] === EErrorCode::NO_ERROR) {
                $result['msg'] = __('common/common.change-password-success');
            }
            return response()->json($result);
        } catch (\Exception $e) {
            logger()->error('change password failed', compact('e'));
            return response()->json(['error' => EErrorCode::ERROR, 'msg' => __('common/error.system-error')]);
        }
    }
}
