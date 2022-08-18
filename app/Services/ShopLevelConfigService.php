<?php

namespace App\Services;

use App\Constant\DefaultConfig;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Enums\EStatus;
use App\Models\ShopLevelConfig;
use App\Repositories\OtpCodeRepository;
use App\Repositories\ShopLevelConfigRepository;
use App\Repositories\ShopRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Shop;
use App\Enums\EPaymentStatus;


class ShopLevelConfigService {
    private ShopLevelConfigRepository $shopLevelConfigRepository;

    public function __construct(ShopLevelConfigRepository $shopLevelConfigRepository) {
        $this->shopLevelConfigRepository = $shopLevelConfigRepository;
    }

    public function getById($shopId) {
        return $this->shopLevelConfigRepository->getById($shopId);
    }


    public function getByOptions(array $options) {
        return $this->shopLevelConfigRepository->getByOptions($options);
    }


    public function saveShopLevelConfig($data, $loggedInUserId) {
        try {
            return DB::transaction(function() use ($data, $loggedInUserId) {
                $id = Arr::get($data, 'id');
                if ($id) {
                    $shopLevelConfig = $this->getById($id);
                    if (empty($shopLevelConfig)) {
                        return ['error' => EErrorCode::ERROR, 'msg'
                        => __('common/error.invalid-request-data')];
                    }
                    $shopLevelConfig->updated_by = $loggedInUserId;
                } else {
                    $shopLevelConfig = new ShopLevelConfig();
                    $shopLevelConfig->created_by = $loggedInUserId;
                    $shopLevelConfig->status = EStatus::ACTIVE;
                }
                $shopLevelConfig->num_product = Arr::get($data, 'numProduct',
                    null);
                $shopLevelConfig->num_image_in_product = Arr::get($data, 'numImageInProduct',
                    $shopLevelConfig->num_image_in_product);
                $shopLevelConfig->num_push_product_in_month = Arr::get($data,
                    'numPushProductInMonth', $shopLevelConfig->num_push_product_in_month);
                $shopLevelConfig->priority_show_search_product = Arr::get($data,
                    'priorityShowSearchProduct', $shopLevelConfig->priority_show_search_product);
                $shopLevelConfig->enable_create_notification = Arr::get($data,
                    'enableCreateNotification', $shopLevelConfig->enable_create_notification);

                $shopLevelConfig->video_introduce = json_encode([
                    'allow_upload_video' => filter_var(Arr::get($data,
                        'videoIntroduceAllowUploadVideo'),FILTER_VALIDATE_BOOLEAN),
                    'upload_time'=> (int)Arr::get($data,'videoIntroduceUploadTime'),
                    'num_video' => (int)Arr::get($data, 'videoIntroduceNumVideo'),
                ]);
                $shopLevelConfig->avatar = json_encode([
                    'type' => (int)Arr::get($data, 'avatarType'),
                    'allow_upload_video' => filter_var(Arr::get($data,
                        'avatarAllowUploadVideo'),FILTER_VALIDATE_BOOLEAN),
                    'upload_time'=> (int)Arr::get($data,'avatarUploadTime'),
                ]);
                $shopLevelConfig->video_in_product = json_encode([
                    'allow_upload_video' => filter_var(Arr::get($data,
                        'videoInProductAllowUploadVideo'),FILTER_VALIDATE_BOOLEAN),
                    'upload_time'=> (int)Arr::get($data,'videoInProductUploadTime'),
                ]);

                $shopLevelConfig->banner_in_home = json_encode([
                    'allow_upload_banner' => filter_var(Arr::get($data,
                        'bannerInHomeAllowUploadBanner'),FILTER_VALIDATE_BOOLEAN),
                    'num_day_show'=> (int)Arr::get($data,'bannerInHomeNumDayShow'),
                ]);

                $shopLevelConfig->image_introduce = json_encode([
                    'allow_upload_image' => filter_var(Arr::get($data,
                        'imageIntroduceAllowUpdateImage'),FILTER_VALIDATE_BOOLEAN),
                    'num_image'=> (int)Arr::get($data,'imageIntroduceNumImage'),
                ]);

                $numProductText = is_null($shopLevelConfig->num_product) ? 'Không giới hạn'
                    : 'Tối đa ' . $shopLevelConfig->num_product . ' tin đăng';
                $description = '- Số tin đăng: ' . $numProductText .
                '\n- Số ảnh mỗi tin: ' . $shopLevelConfig->num_image_in_product . ' hình' .
                '\n- Thời gian hiển thị: không giới hạn';
                if (!empty(Arr::get($data,'bannerInHomeNumDayShow'))) {
                    $description .= '\n- Thời gian hiển thị banner: ' . (int)Arr::get($data,'bannerInHomeNumDayShow') . ' ngày';
                }
                $shopLevelConfig->description = $description;

                $shopLevelConfig->save();

                return [
                    'error' => EErrorCode::NO_ERROR,
                    'shopLevelConfig' => $shopLevelConfig,
                ];
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
