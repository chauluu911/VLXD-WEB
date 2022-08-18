<?php

namespace App\Services;

use App\Constant\DefaultConfig;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Enums\EStatus;
use App\Repositories\OtpCodeRepository;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Shop;
use App\Enums\EPaymentStatus;
use App\Enums\ELevelName;
use App\Enums\ELanguage;
use App\Jobs\NotifyUserJob;
use App\Enums\ENotificationType;
use App\Models\ShopLevel;
use App\Models\ShopBanner;
use App\Models\ShopLevelConfig;
use App\Services\BannerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ShopService {
	private ShopRepository $shopRepository;
    private UserRepository $userRepository;
    private BannerService $bannerService;

    public function __construct(ShopRepository $shopRepository,
            UserRepository $userRepository,
            BannerService $bannerService) {
        $this->shopRepository = $shopRepository;
        $this->userRepository = $userRepository;
        $this->bannerService = $bannerService;
    }

    /**
     * @param $userId
     * @return \App\User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getById($shopId) {
	    return $this->shopRepository->getById($shopId);
    }

    public function getByOwnerUserId($ownerUserId) {
        return $this->shopRepository->getByOwnerUserId($ownerUserId);
    }

    public function getByOptions(array $options) {
        return $this->shopRepository->getByOptions($options);
    }

    public function generateNewCode() {
        do {
            $code = 'CH' . mb_strtoupper(Str::random(5));
        } while (Shop::where('code', $code)->exists());
        return $code;
    }

    public function saveShop($data, $loggedInUserId) {
        $fileToDeleteIfError = [];
        try {
            return DB::transaction(function() use ($data, $loggedInUserId, &$fileToDeleteIfError) {
                $id = Arr::get($data, 'id');
                if ($id) {
                    $shop = $this->getById($id);
                    if (empty($shop)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                    }

                    if (!empty(Arr::get($data, 'email')) && $this->shopRepository->didEmailExist(Arr::get($data, 'email'), $shop->id)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => ['email' => [__('common/error.email-exist')]]];
                    }

                    if (!empty(Arr::get($data, 'phone')) && $this->shopRepository->didPhoneExist(Arr::get($data, 'phone'), $shop->id)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => ['phone' => [__('common/error.phone-exist')]]];
                    }

                    $shop->updated_by = $loggedInUserId;
                } else {
                    $shop = new Shop();
                    $shop->code = $this->generateNewCode();
                    $shop->created_by = $loggedInUserId;
                    $shop->status = EStatus::WAITING;
                    $shop->level = ELevelName::LEVEL_1;
                    $shop->user_id = $loggedInUserId;
                }
                if (!empty(Arr::get($data, 'email')) && $this->shopRepository->didEmailExist(Arr::get($data, 'email'), $id)) {
                    return ['error' => EErrorCode::ERROR, 'msg' => ['email' => [__('common/error.email-exist')]]];
                }

                if (!empty(Arr::get($data, 'phone')) && $this->shopRepository->didPhoneExist(Arr::get($data, 'phone'), $id)) {
                    return ['error' => EErrorCode::ERROR, 'msg' => ['phone' => [__('common/error.phone-exist')]]];
                }
                $shop->name = Arr::get($data, 'name', $shop->name);
                $shop->phone = Arr::get($data, 'phone', $shop->phone);
                $shop->email = Arr::get($data, 'email', null);
                $shop->address = Arr::get($data, 'address', $shop->address);
                $shop->description = Arr::get($data, 'description', $shop->description);
                $shop->latitude = Arr::get($data, 'latitude', null);
                $shop->longitude = Arr::get($data, 'longitude', null);
                $shop->fb_page = Arr::get($data, 'fb', null);
                $shop->zalo_page = Arr::get($data, 'zalo', null);
                $shop->avatar_type = Arr::get($data, 'avatarType');
                $shop->identity_code = Arr::get($data, 'identityCode');
                $shop->identity_date = Arr::get($data, 'identityDate');
                $shop->identity_place = Arr::get($data, 'identityPlace');
                $avatarFile = Arr::get($data, 'avatar');
                if (!empty($avatarFile) && $avatarFile instanceof UploadedFile) {
                    $relativePath = FileUtility::storeFile(EStoreFileType::SHOP_AVATAR, $avatarFile);
                    FileUtility::removeFiles([$shop->avatar_path]);
                    $shop->avatar_path = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                }
                if(!empty(Arr::get($data,'areaWard'))) {
                    $shop->area_id = Arr::get($data, 'areaWard');
                } elseif (!empty(Arr::get($data,'areaDistrict'))) {
                    $shop->area_id = Arr::get($data, 'areaDistrict');
                } else {
                    $shop->area_id = Arr::get($data, 'areaId', $shop->area_id);
                }

                $shop->save();
                if (!$id) {
                    $shopConfig = ShopLevelConfig::where('level', $shop->level)->first();
                    $shopLevel = new ShopLevel();
                    $shopLevel->shop_id = $shop->id;
                    $shopLevel->num_image_introduce_remain = json_decode($shopConfig->image_introduce)->num_image;
                    // $shopLevel->num_push_product_in_month_remain = $shopConfig->num_push_product_in_month;
                    $shopLevel->status = EStatus::ACTIVE;
                    $shopLevel->created_at = now();
                    $shopLevel->created_by = $loggedInUserId;
                    $shopLevel->num_product_remain = $shopConfig->num_product;
                    $shopLevel->shop_level_config_id = $shopConfig->id;
                    $shopLevel->num_video_introduce_remain = $shopConfig->video_inproduct;
                    $shopLevel->num_image_in_product_remain = $shopConfig->num_image_in_product;
                    $shopLevel->num_push_product_in_month = str_replace('-', '/', json_encode([
                        'month_in_year' => now()->format(EDateFormat::STANDARD_MONTH_FORMAT),
                        'num_push_product_in_month_remain' => $shopConfig->num_push_product_in_month
                    ]));
                    $shopLevel->save();
                }

                return [
                	'error' => EErrorCode::NO_ERROR,
					'shop' => $shop,
				];
            });
        } catch (\Exception $e) {
            FileUtility::removeFiles($fileToDeleteIfError);
            throw $e;
        }
    }

    public function deleteShop($id, $loggedInUserId) {
        return DB::transaction(function() use ($id, $loggedInUserId) {
            $shop = $this->getById($id);
            if (empty($shop)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $shop->deleted_by = $loggedInUserId;
            $shop->deleted_at = Carbon::now();
            $shop->status = EStatus::DELETED;
            $shop->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function approveShop($id, $loggedInUserId) {
        return DB::transaction(function() use ($id, $loggedInUserId) {
            $shop = $this->getById($id);
            if (empty($shop)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $shop->updated_by = $loggedInUserId;
            $shop->status = EStatus::ACTIVE;
            $shop->save();

            NotifyUserJob::dispatch([$shop->user_id], [
                'type' => ENotificationType::APPROVED_SHOP,
                'title' => [
                    ELanguage::VI => 'Thông báo',
                ],
                'content' => [
                    ELanguage::VI => 'Shop ' . "'" . $shop->name . "'" . ' của bạn đã được duyệt',
                ],
                'meta' => [
                    'shopId' => (int)$shop->id
                ],
                'data' => [
                    'shopId' => $shop->id
                ],
            ])->onQueue('pushToDevice');
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function saveBanner($data, $loggedInUserId, $shopId) {
        return DB::transaction(function() use ($data, $loggedInUserId, $shopId) {
            $flatForm = ['web', 'mobile'];
            foreach ($flatForm as $item) {
                if (empty($data['isEdit']) || !empty($data['isEdit']) && !empty($data[$item]['edit'])) {
                    if (!empty($data[$item]['file'])) {
                        $saveBanner = $this->bannerService->saveBanner($data[$item], $loggedInUserId);
                    }
                    if (empty($data[$item]['edit']) && !empty($data[$item]['file'])) {
                        $shopBanner = new ShopBanner();
                        $shopBanner->shop_id = $shopId;
                        $shopBanner->banner_id = $saveBanner['bannerId'];
                        $shopBanner->save();
                    }
                }
            }
            return [
                'error' => EErrorCode::NO_ERROR,
            ];
        });
    }
}
