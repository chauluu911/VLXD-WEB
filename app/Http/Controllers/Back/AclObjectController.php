<?php

namespace App\Http\Controllers\Back;

use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Socialite\Facades\Socialite;
use App\Services\AclObjectService;
use App\Enums\EErrorCode;
use App\Enums\EStatus;

class AclObjectController extends Controller {
	private $aclObjectService;

	public function __construct(AclObjectService $aclObjectService) {
		$this->aclObjectService = $aclObjectService;
	}

	public function getPermissionList() {
		$roleList = $this->aclObjectService->getByOptions([
			'status' => EStatus::ACTIVE,
			'get' => true,
		]);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'roleList' => $roleList,
		]);
	}
}
