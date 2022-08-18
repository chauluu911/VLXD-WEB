<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Enums\EPaymentStatus;
use App\Enums\EUserType;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Config\SaveBannerRequest;
use App\Services\BannerService;
use App\Services\ConfigService;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\Banner\EBannerActionType;
use App\Enums\Banner\EBannerType;
use App\Enums\EPlatform;
use App\Constant\ConfigKey;

class BannerController extends Controller {
	private BannerService $bannerService;
	private ConfigService $configService;
	private UserService $userService;

	public function __construct(BannerService $bannerService,UserService $userService,
                                ConfigService $configService) {
		$this->bannerService = $bannerService;
		$this->configService = $configService;
		$this->userService = $userService;
	}

	public function getBannerList() {
        $tz = session(SessionKey::TIMEZONE);
		$acceptFields = ['type', 'platform', 'status'];
		$filters = [
			'pageSize' => request('pageSize'),
		];

		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
				continue;
			}
			$filters[$field] = request("filter.$field");
		}
		// if (!empty($filters['platform']) && $filters['platform'] == 'all') {
		// 	$filters['platform'] = null;
		// }
		$banners = $this->bannerService->getByOptions($filters);
		foreach ($banners as $key => $value) {
			$value->path_to_resource = !empty($value->path_to_resource) ? get_image_url([
				'path' => $value->path_to_resource,
				'op' => 'thumbnail',
				'w' => 100,
				'h' => 100,
			]) : DefaultConfig::FALLBACK_IMAGE_PATH;
			$value->original_resource_path = !empty($value->original_resource_path) ? config('app.resource_url_path') . '/' . $value->original_resource_path : DefaultConfig::FALLBACK_IMAGE_PATH;
			$value->actionOnClick = EBannerActionType::valueToName($value->action_on_click_type);
			$value->typeString = EBannerType::valueToName($value->type);
			$value->platformString = EPlatform::valueToName($value->platform);
			$link = json_decode($value->action_on_click_target, false);
			if ($value->action_on_click_type == 1) {
				$value->action_on_click_target = null;
			}
			$value->action_on_click_target = !empty($value->action_on_click_target) ? $link->url : null;
			$value->statusStr = EStatus::valueToLocalizedName($value->status);
			$creator = $this->userService->getById($value->created_by);
			if(!empty($creator)) {
                if($creator->type === EUserType::ADMIN) {
                    $value->creator = [
                        'type' => EUserType::ADMIN,
                        'name' => EUserType::getLocalizedName(EUserType::ADMIN),
                    ];
                } else if($creator->type === EUserType::INTERNAL_USER) {
                    $value->creator = [
                        'type' => EUserType::INTERNAL_USER,
                        'id' => $creator->code,
                        'name' => $creator->name,
                        'gender' => $creator->gender,
                        'phone' =>$creator->phone,
                        'dob' => $creator->date_of_birth,
                        'email' => $creator->email,
                        'address' => $creator->address,
                        'strStatus' => EStatus::valueToLocalizedName($creator->status),
                        'avatar' => !empty($creator->avatar_path) ? get_image_url([
                            'path' => $creator->avatar_path,
                            'op' => 'thumbnail',
                            'w' => 100,
                            'h' => 100,
                        ]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH
                    ];
                } else {
                    $shopInfo = $creator->getShop;
                    if(!empty($shopInfo)) {
                        $value->creator = [
                            'type' => EUserType::NORMAL_USER,
                            'id' => $shopInfo->code,
                            'name'=> $shopInfo->name,
                            'phone' => $shopInfo->phone,
                            'email' => $shopInfo->email,
                            'address' => $shopInfo->address,
                            'description' => $shopInfo->description,
                            'created_at' => $shopInfo->created_at,
                            'strPaymentStatus' => EPaymentStatus::valueToLocalizedName($shopInfo->payment_status),
                            'strStatus' => EStatus::valueToLocalizedName($shopInfo->status),
                            'avatar' => !empty($creator->avatar_path) ? get_image_url([
                                'path' => $creator->avatar_path,
                                'op' => 'thumbnail',
                                'w' => 100,
                                'h' => 100,
                            ]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH
                        ];
                    }
                }
            }
		}
		$dataValidateWeb = $this->configService->getByName(ConfigKey::BANNER_IMAGE_SPECS_WEB);
		$dataValidateMobile = $this->configService->getByName(ConfigKey::BANNER_IMAGE_SPECS_MOBILE);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => [
				'banner' => $banners,
				'validate' => [
					'web' => json_decode($dataValidateWeb->text_value),
					'mobile' => json_decode($dataValidateMobile->text_value),
				]
			]
		]);
	}

	public function deleteBanner() {
		try {
			$id = request('id');
			$delete = $this->bannerService->deleteBanner($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/banner.object_name')
				])
			]);

		} catch (\Exception $e) {
			logger()->error('Fail to delete banner', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}

	public function saveBanner(SaveBannerRequest $request) {
		$data = $request->validated();
		$data['actionType'] = request('actionType');
		$data['type'] = request('type');
		$data['id'] = request('id');
		$data['blob'] = request('blob');
		$data['ratio'] = str_replace('/', ':', request('ratio'));
		$result = $this->bannerService->saveBanner($data, auth()->id());
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => __('common/common.save-data-success', [
				'objectName' => __('back/banner.object_name'),
			])
		]);
	}

	public function approveBanner() {
		$data = request()->all();
		$result = $this->bannerService->approveBanner($data, now(), auth()->id());
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => 'Duyệt thành công',
		]);
	}

    public function rejectBanner() {
        $data = request()->all();
        $result = $this->bannerService->rejectBanner($data, now(), auth()->id());
        if ($result['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($result);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => 'Từ chối thành công',
        ]);
    }
}
