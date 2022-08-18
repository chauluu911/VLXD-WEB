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
use App\Services\ForbiddenSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ForbiddenSearchController extends Controller {
    protected ForbiddenSearchService $forbiddenSearchService;

    public function __construct(ForbiddenSearchService $forbiddenSearchService) {
        $this->forbiddenSearchService = $forbiddenSearchService;
    }

    public function getForbiddenSearchList() {
        $options = [
            'pageSize' => request('pageSize'),
        ];

        $acceptFields = ['q'];
        foreach ($acceptFields as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            $options[Str::snake($field)] = request("filter.$field");
        }
        $list = $this->forbiddenSearchService->getByOptions($options);
        $tmp = $list->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'createdAt' =>Carbon::parse($item->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT)
            ];
        });
        $list->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data'  => $list,
        ]);
    }


    public function delete() {
        $result = $this->forbiddenSearchService->delete(request('id'), auth()->id());
        if ($result['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($result);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.delete-data-success', [
                'objectName' => __('back/config.forbidden_search'),
            ]),
        ]);
    }

    public function save(Request $request) {
        //try {
            $data = $request->validate([
                'name' => 'required',
                'id' => 'nullable',
            ],['name.required' => 'Từ khóa là bắt buộc']);
            $result = $this->forbiddenSearchService->save($data, auth()->id());
            if ($result['error'] !== EErrorCode::NO_ERROR) {
                return response()->json($result);
            }
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'msg' => __('common/common.save-data-success', [
                    'objectName' => __('back/config.forbidden_search')
                ]),
            ]);
    }
}
