<?php

namespace App\Http\Controllers\Back;

use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\EDateFormat;
use App\Enums\ESubscriptionPriceType;
use App\Constant\ConfigKey;
use App\Helpers\ConfigHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Config\SavePackageRequest;
use App\Http\Requests\Config\SavePackageUpgradeShopRequest;
use App\Services\SubscriptionPriceService;
use Illuminate\Support\Carbon;

class SubscriptionPriceController extends Controller {
    protected SubscriptionPriceService $subscriptionPriceService;

    public function __construct(SubscriptionPriceService $subscriptionPriceService) {
        $this->subscriptionPriceService = $subscriptionPriceService;
    }

    public function getPackagePushProductList() {
        $options = [
            'pageSize' => request('pageSize'),
            'type' => ESubscriptionPriceType::PACKAGE_PUSH_PRODUCT,
            'status' => EStatus::ACTIVE,
        ];

        $acceptFields = ['q'];
        foreach ($acceptFields as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            $options[Str::snake($field)] = request("filter.$field");
        }
        $list = $this->subscriptionPriceService->getByOptions($options);
        $tmp = $list->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'status' => $item->status,
                'numDay' => $item->num_day,
                'price' => number_format($item->price),
                'createdAt' =>Carbon::parse($item->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT)
            ];
        });
        $list->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data'  => $list,
        ]);
    }

    public function getPackageUpgradeShopList() {
        $options = [
            'pageSize' => request('pageSize'),
            'type' => ESubscriptionPriceType::UPGRADE_SHOP,
            'orderBy' => 'id',
            'orderDirection' => 'asc',
            'status' => EStatus::ACTIVE,
        ];

        $acceptFields = ['q'];
        foreach ($acceptFields as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            $options[Str::snake($field)] = request("filter.$field");
        }
        $list = $this->subscriptionPriceService->getByOptions($options);
        $tmp = $list->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'status' => $item->status,
                'numDay' => $item->num_day,
                'price' => number_format($item->price),
            ];
        });
        $list->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data'  => $list,
        ]);
    }

    public function deletePackage() {
        $result = $this->subscriptionPriceService->deletePackage(request('id'), auth()->id());
        if ($result['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($result);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.delete-data-success', [
                'objectName' => __('back/package.object_name'),
            ]),
        ]);
    }

    public function savePackage(SavePackageRequest $request) {
        //try {
            $data = $request->validated();
            $result = $this->subscriptionPriceService->savePackage($data, auth()->id());
            if ($result['error'] !== EErrorCode::NO_ERROR) {
                return response()->json($result);
            }
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'msg' => __('common/common.save-data-success', [
                    'objectName' => __('back/package.object_name')
                ]),
            ]);
        // } catch (\Exception $e) {
        //     logger()->error('Fail to save package', [
        //         'error' =>  $e
        //     ]);
        //     return response()->json([
        //         'error' => EErrorCode::ERROR,
        //         'msg' => __('common/common.there_was_an_error_in_the_processing'),
        //     ]);
        // }
    }
}
