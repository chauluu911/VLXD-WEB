<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Enums\EOrderStatus;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidatorHelper;
use Illuminate\Support\Arr;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Enums\EPaymentStatus;
use App\Http\Requests\Config\SaveShopLevelConfigRequest;
use App\Models\ShopLevelConfig;
use App\Services\ShopLevelConfigService;

class ShopLevelConfigController extends Controller {
    private ShopLevelConfigService $shopLevelConfigService;

    public function __construct(ShopLevelConfigService $shopLevelConfigService) {
        $this->shopLevelConfigService = $shopLevelConfigService;
    }

    public function getShopLevelConfigList() {
        $filters = [
            'pageSize' => request('pageSize') ?  request('pageSize') : 10 ,
            'orderBy' => 'level',
            'orderDirection' => 'asc',
        ];

        $shopLevelConfigList = $this->shopLevelConfigService->getByOptions($filters);
        $tmp = $shopLevelConfigList->map( function(ShopLevelConfig $value){
            return [
                'id' => $value->id,
                'status' =>$value->status,
                'name' => $value->name,
                'numProduct' => $value->num_product,
                'numImageInProduct' => $value->num_image_in_product,
                'videoIntroduce' => json_decode($value->video_introduce) ,
                'imageIntroduce' => json_decode($value->image_introduce) ,
                'numPushProductInMonth' => $value->num_push_product_in_month,
                'priorityShowSearchProduct' => $value->priority_show_search_product,
                'enableCreateNotification' => $value->enable_create_notification,
                'bannerInHome' => json_decode($value->banner_in_home),
                'avatar' => json_decode($value->avatar),
                'videoInProduct' => json_decode($value->video_in_product),
            ];
        }) ;
        $shopLevelConfigList->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $shopLevelConfigList,
        ]);
    }

    public function saveShopLevelConfig(SaveShopLevelConfigRequest $request) {
        $data = $request->validated();
        $data['id'] = request('id');
        $result = $this->shopLevelConfigService->saveShopLevelConfig($data, auth()->id());
        if ($result['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($result);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.save-data-success', [
                'objectName' => __('back/shop_level_config.object_name')
            ]),
        ]);
    }

}
