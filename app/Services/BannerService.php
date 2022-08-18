<?php

namespace App\Services;

use App\Constant\CacheKey;
use App\Constant\DefaultConfig;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\Banner\EBannerType;
use App\Enums\EPlatform;
use App\Repositories\BannerRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Banner;
use App\Models\ShopBanner;
use App\Models\ShopLevel;
use App\Models\ShopLevelConfig;
use App\Jobs\NotifyUserJob;
use App\Enums\ENotificationType;
use App\Enums\ELanguage;

class BannerService {
	private BannerRepository $bannerRepository;

    public function __construct(BannerRepository $bannerRepository) {
        $this->bannerRepository = $bannerRepository;
    }

    /**
     * @param $userId
     * @return \App\User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getById($bannerId) {
	    return $this->bannerRepository->getById($bannerId);
    }

    public function getByOptions(array $options) {
        return $this->bannerRepository->getByOptions($options);
    }

    public function processBannerForHome($banner, array $options = []) {
    	$imagePath = DefaultConfig::FALLBACK_IMAGE_PATH;
    	if (!empty($banner->path_to_resource)) {
    		if (Arr::get($options, 'original', false)) {
    			$imagePath = FileUtility::getFileResourcePath($banner->path_to_resource);
			} else {
				$imagePath = get_image_url([
					'path' => $banner->path_to_resource,
					'op' => 'resize',
					'w' => Arr::get($options, 'maxWidth', 1500),
				]);
			}
		}
    	return [
			'image' => $imagePath,
			'type' => $banner->type,
			'valid' => $banner->valid_to,
			'action' => $banner->action_on_click_type,
			'actionDetail' => $banner->action_on_click_target ?
				json_decode($banner->action_on_click_target) : null,
		];
	}

    public function getBannerListForHome($forceReset = false) {
    	//region không cache do mobile cũng tạo được banner loại này
		$desktopBannerList = $this->getByOptions([
			'status' => EStatus::ACTIVE,
			'type' => EBannerType::MAIN_BANNER_ON_HOME_SCREEN,
			'platform' => EPlatform::WEB
		])->map(fn($banner) => $this->processBannerForHome($banner, [
			'maxWidth' => 1500
		]))->toArray();

		$mobileBannerList = $this->getByOptions([
			'status' => EStatus::ACTIVE,
			'type' => EBannerType::MAIN_BANNER_ON_HOME_SCREEN,
			'platform' => EPlatform::MOBILE
		])->map(fn($banner) => $this->processBannerForHome($banner, [
			'maxWidth' => 1080
		]))->toArray();
		//endregion

		$webTrademarkBannerList = cache()->get(CacheKey::HOME_BANNER_TRADEMARK_DESKTOP_LIST);
		if (empty($webTrademarkBannerList) || $forceReset) {
			$webTrademarkBannerList = $this->getByOptions([
				'status' => EStatus::ACTIVE,
				'type' => EBannerType::TRADEMARK,
				'platform' => EPlatform::WEB
			])->map(fn($banner) => $this->processBannerForHome($banner, [
				'maxWidth' => 1500
			]))->toArray();
			cache()->put(CacheKey::HOME_BANNER_TRADEMARK_DESKTOP_LIST, $webTrademarkBannerList, now()->addDay());
		}

		$mobileTrademarkBannerList = cache()->get(CacheKey::HOME_BANNER_TRADEMARK_MOBILE_LIST);
		if (empty($mobileTrademarkBannerList) || $forceReset) {
			$mobileTrademarkBannerList = $this->getByOptions([
				'status' => EStatus::ACTIVE,
				'type' => EBannerType::TRADEMARK,
				'platform' => EPlatform::MOBILE
			])->map(fn($banner) => $this->processBannerForHome($banner, [
				'maxWidth' => 700
			]))->toArray();
			cache()->put(CacheKey::HOME_BANNER_TRADEMARK_MOBILE_LIST, $mobileTrademarkBannerList, now()->addDay());
		}

		$promotionBannerList = cache()->get(CacheKey::HOME_BANNER_PROMOTION_LIST);
		if (empty($promotionBannerList) || $forceReset) {
			$promotionBannerList = $this->getByOptions([
				'status' => EStatus::ACTIVE,
				'type' => EBannerType::PROMOTION,
				'platform' => EPlatform::WEB
			])->map(fn($banner) => $this->processBannerForHome($banner, [
                'maxWidth' => 450,
			]))->toArray();
			cache()->put(CacheKey::HOME_BANNER_PROMOTION_LIST, $promotionBannerList, now()->addDay());
		}

		return [
			'mainDesktop' => $desktopBannerList,
			'mainMobile' => $mobileBannerList,
			'trademarkWeb' => $webTrademarkBannerList,
            'trademarkMobile' => $mobileTrademarkBannerList,
			'promotion' => $promotionBannerList,
		];
	}

    public function saveBanner($data, $loggedInUserId) {
        $fileToDeleteIfError = [];
        try {
            $result = DB::transaction(function() use ($data, $loggedInUserId, &$fileToDeleteIfError) {
                $id = Arr::get($data, 'id');
                if ($id) {
                    $banner = $this->getById($id);
                    if (empty($banner)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                    }
                    if (Arr::get($data, 'type') == EBannerType::SHOW_AS_POP_UP_AFTER_LOG_IN || Arr::get($data, 'type') == EBannerType::PROMOTION) {
                        $getBanner = $this->getByOptions([
                            'type' => Arr::get($data, 'type'),
                        ]);
                        if (count($getBanner) > 1) {
                           return ['error' => EErrorCode::ERROR, 'msg' => __('back/banner.msg.exist')];
                        }
                    }
                    $banner->updated_by = $loggedInUserId;
                } else {
                    if (Arr::get($data, 'type') == EBannerType::SHOW_AS_POP_UP_AFTER_LOG_IN || Arr::get($data, 'type') == EBannerType::PROMOTION) {
                        $checkExistBanner = $this->getByOptions([
                            'type' => Arr::get($data, 'type'),
                            'exists' => true
                        ]);
                        if ($checkExistBanner) {
                           return ['error' => EErrorCode::ERROR, 'msg' => __('back/banner.msg.exist')];
                        }
                    }
                    $banner = new Banner();
                    $banner->created_by = $loggedInUserId;
                    $banner->status = Arr::get($data, 'status', EStatus::ACTIVE);
                }

                $pathToResource = Arr::get($data, 'file');
                if (!empty($pathToResource)) {
                    $relativePath = FileUtility::storeFile(EStoreFileType::BANNER_ORIGINAL_RESOURCE, $pathToResource);
                    FileUtility::removeFiles([$banner->original_resource_path]);
                    $banner->original_resource_path = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                }

                $originalPathToResource = Arr::get($data, 'blob');
                if (!empty($originalPathToResource)) {
                    $relativePath = FileUtility::storeFile(EStoreFileType::BANNER_RESOURCE, $originalPathToResource);
                    FileUtility::removeFiles([$banner->path_to_resource]);
                    $banner->path_to_resource = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                }

                $banner->action_on_click_type = Arr::get($data, 'actionType');
                $banner->type = Arr::get($data, 'type');
                $linkToResource = Arr::get($data, 'link');
                $banner->action_on_click_target = !empty($linkToResource) ? json_encode([
                    'url' =>filter_var($linkToResource, FILTER_SANITIZE_URL),
                    'uri' =>filter_var($linkToResource, FILTER_SANITIZE_URL)
                ]) : $banner->action_on_click_target;
                $banner->image_ratio = Arr::get($data, 'ratio');
                $banner->platform = Arr::get($data, 'platform', $banner->platform);
                $banner->save();
                return [
                	'error' => EErrorCode::NO_ERROR,
                    'bannerId' => $banner->id
				];
            });
            if ($result['error'] == EErrorCode::NO_ERROR) {
            	$this->getBannerListForHome(true);
			}
            return $result;
        } catch (\Exception $e) {
            FileUtility::removeFiles($fileToDeleteIfError);
            throw $e;
        }
    }

    public function deleteBanner($id, $loggedInUserId) {
        $result = DB::transaction(function() use ($id, $loggedInUserId) {
            $banner = $this->getById($id);
            if (empty($banner)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $banner->deleted_by = $loggedInUserId;
            $banner->deleted_at = Carbon::now();
            $banner->status = EStatus::DELETED;
            $banner->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
		if ($result['error'] == EErrorCode::NO_ERROR) {
			$this->getBannerListForHome(true);
		}
		return $result;
    }

    public function approveBanner($data, $now, $currentUserId) {
        $result = DB::transaction(function() use ($data, $now, $currentUserId) {
            foreach ($data as $key) {
                foreach ($key as $val) {
                    $banner = $this->getById($val['id']);

                    $shopBanner = ShopBanner::where('banner_id', $banner->id)->first();
                    $shopLevel = ShopLevel::where('shop_id', $shopBanner->shop_id)
                        ->where('status', EStatus::ACTIVE)->first();
                    $shopConfig = ShopLevelConfig::where('id', $shopLevel->shop_level_config_id)->first();
                    $numday = json_decode($shopConfig->banner_in_home)->num_day_show;
                    $banner->valid_from = $now->copy();
                    $banner->valid_to = $now->copy()->addDays($numday);
                    $banner->status = EStatus::ACTIVE;
                    $banner->updated_at = $now;
                    $banner->updated_by = $currentUserId;
                    $banner->save();

                    NotifyUserJob::dispatch([$banner->created_by], [
                        'type' => ENotificationType::APPROVED_BANNER,
                        'title' => [
                            ELanguage::VI => 'Thông báo',
                        ],
                        'content' => [
                            ELanguage::VI => 'Banner quảng cáo trên nền tảng ' . EPlatform::valueToName($banner->platform) . ' của bạn đã được duyệt',
                        ],
                        'meta' => [
                            'bannerId' => (int)$banner->id,
                            'shopId' => (int)$shopBanner->shop_id
                        ],
                        'data' => [
                            'bannerId' => $banner->id,
                            'sellerId' => $banner->created_by
                        ]
                    ])->onQueue('pushToDevice');
                }
            }
            return [
                'error' => EErrorCode::NO_ERROR,
            ];
        });
		if ($result['error'] == EErrorCode::NO_ERROR) {
			$this->getBannerListForHome(true);
		}
		return $result;
    }

    public function rejectBanner($data, $now, $currentUserId) {
        return DB::transaction(function() use ($data, $now, $currentUserId) {
            foreach ($data as $key) {
                foreach ($key as $val) {
                    $banner = $this->getById($val['id']);

                    $shopBanner = ShopBanner::where('banner_id', $banner->id)->first();
                    $banner->status = EStatus::SUSPENDED;
                    $banner->updated_at = $now;
                    $banner->updated_by = $currentUserId;
                    $banner->save();

                    NotifyUserJob::dispatch([$banner->created_by], [
                        'type' => ENotificationType::APPROVED_BANNER,
                        'title' => [
                            ELanguage::VI => 'Thông báo',
                        ],
                        'content' => [
                            ELanguage::VI => 'Banner quảng cáo trên nền tảng ' . EPlatform::valueToName($banner->platform) . ' của bạn đã bị từ chối',
                        ],
                        'meta' => [
                            'bannerId' => (int)$banner->id,
                            'shopId' => (int)$shopBanner->shop_id
                        ],
                        'data' => [
                            'bannerId' => $banner->id,
                            'sellerId' => $banner->created_by
                        ]
                    ])->onQueue('pushToDevice');
                }
            }
            return [
                'error' => EErrorCode::NO_ERROR,
            ];
        });
    }
}
