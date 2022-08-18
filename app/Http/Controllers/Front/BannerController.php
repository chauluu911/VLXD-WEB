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
use App\Enums\EApprovalStatus;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ProductService;
use App\Services\SubscriptionPriceService;
use App\Http\Requests\Product\SaveInfoRequest;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Helpers\FileUtility;

class BannerController extends Controller {

	protected BannerService $bannerService;

    public function __construct(BannerService $bannerService) {
        $this->bannerService = $bannerService;
    }

    public function getBannerList() {
        $acceptFields = ['q', 'type', 'status', 'platform'];

        foreach ($acceptFields as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            $filters[Str::snake($field)] = request("filter.$field");
        }
        $filters['banner_home'] = true;
        $banners = $this->bannerService->getByOptions($filters);
        $banners = $banners->map(function(Banner $banner) {
            return [
                'image' => !empty($banner->path_to_resource) ?
                config('app.resource_url_path') . '/' .$banner->path_to_resource
                : DefaultConfig::FALLBACK_IMAGE_PATH,
                'type' => $banner->type,
                'valid' => $banner->valid_to,
                'action' => $banner->action_on_click_type,
                'actionDetail' => $banner->action_on_click_target ?
                    json_decode($banner->action_on_click_target) : null,
            ];
        });
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'banners' => $banners,
        ]);
    }

}
