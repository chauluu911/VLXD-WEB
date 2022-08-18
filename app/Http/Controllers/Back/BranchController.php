<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constant\SessionKey;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;
use App\Services\BranchService;
use Illuminate\Support\Arr;
use App\Enums\EErrorCode;
use App\Enums\EAreaType;
use Illuminate\Support\Str;
use App\Enums\EStatus;
use App\Services\AreaService;
use App\Helpers\FileUtility;
use App\Enums\EPaymentStatus;
use App\Services\ShopService;
use App\Constant\DefaultConfig;
use App\Http\Requests\Branch\CreateBranchRequest;
use Validator;
use App\Helpers\ValidatorHelper;

class BranchController extends Controller
{
    private AreaService $areaService;
    private BranchService $branchService;
    private ShopService $shopService;

    public function __construct(AreaService $areaService, BranchService $branchService, ShopService $shopService)
    {
        $this->branchService = $branchService;
        $this->areaService = $areaService;
        $this->shopService = $shopService;
    }

    public function getBranchList()
    {
        $timezone = session(SessionKey::TIMEZONE);
        $acceptFields = ['q', 'createdAtFrom', 'createdAtTo'];
        $filters = [
            'admin_branch_list' => true,
            'pageSize' => request('pageSize'),
            'orderBy' => 'created_at',
            'orderDirection' => 'desc',
        ];
        foreach ($acceptFields as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            if ($field === 'createdAtFrom' || $field === 'createdAtTo') {
                if (Arr::has(request('filter'), $field)) {
                    try {
                        $date = Carbon::createFromFormat(
                            EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ,
                            request("filter.$field") . " $timezone",
                        );
                        $filters[$field] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $filters[$field] = request("filter.$field");
            }
        }
        $branch = $this->branchService->getByOptions($filters);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $branch,
        ]);
    }

    public function getAreaAll()
    {
        $options = [
            'get_id_name' => true,
            'orderBy' => 'seq',
            'orderDirection' => 'asc',
        ];
        $acceptFields = ['q', 'countryId', 'type', 'parentAreaId'];

        foreach ($acceptFields as $field) {
            if (!request()->filled($field)) {
                continue;
            }
            $options[Str::snake($field)] = request($field);
        }
        if (request('pageSize')) {
            $options['pageSize'] = request('pageSize');
        }
        $areaList = $this->areaService->getByOptions($options);
        foreach ($areaList as $province) {
            $district = $province->childAreas->where('type', EAreaType::DISTRICT);
            if (count($district) > 0) {
                $province->district = $district;
                foreach ($province->district as $district) {
                    $district->child_areas = $district->childAreas->where('type', EAreaType::WARD);
                }
            }
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $areaList,
        ]);
    }

    public function getShopAll()
    {
        $tz = session(SessionKey::TIMEZONE);
        $acceptFields = ['q', 'userId', 'createdAtFrom', 'createdAtTo', 'status', 'payment_status'];
        $filters = [
            'admin_shop_list' => true,
            'pageSize' => request('pageSize'),
            'orderBy' => 'created_at',
            'orderDirection' => 'desc',
        ];

        foreach ($acceptFields as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            if ($field === 'createdAtFrom' || $field === 'createdAtTo') {
                if (Arr::has(request('filter'), $field)) {
                    try {
                        $date = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, request('filter')[$field] . " $tz");
                        $filters[$field] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $filters[$field] = request("filter.$field");
            }
        }
        $shops = $this->shopService->getByOptions($filters);
        foreach ($shops as $key => $value) {
            $value->avatar = !empty($value->avatar_path) ? FileUtility::getFileResourcePath($value->avatar_path) : DefaultConfig::FALLBACK_USER_AVATAR_PATH;
            $value->strStatus = EStatus::valueToLocalizedName($value->status, true);
            $value->strPaymentStatus = EPaymentStatus::valueToLocalizedName($value->payment_status);
            $value->createdAt =  Carbon::parse($value->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
            $value->identityDate =  Carbon::parse($value->identity_date)->format(EDateFormat::STANDARD_DATE_FORMAT);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $shops,
        ]);
    }

    public function getProvinceAll() {
        $tz = session(SessionKey::TIMEZONE);
        $province = $this->branchService->getBranch();
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $province,
        ]);
    }

    public function saveBranch(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shopId' => 'required|numeric',
                'name' => 'required|string',
                'phone1' => ValidatorHelper::phoneRule(['required' => true]),
                'address' => 'required',
                'areaProvince' => 'required|numeric',
                'areaDistrict' => 'required|numeric',
                'areaWard' => 'required|numeric',
            ],
            [
                'shopId.required' => 'Chưa chọn cửa hàng',
                'shopId.numeric' => 'Cửa hàng không hợp lệ',
                'name.required' => 'Tên chi nhánh không được bỏ trống',
                'phone1.required' => 'Số điện thoại không được bỏ trống',
                'areaProvince.required' => 'Tỉnh/TP không được bỏ trống',
                'areaProvince.numeric' => 'Tỉnh không không hợp lệ',
                'areaDistrict.required' => 'Quận/HUyện không được bỏ trống',
                'areaDistrict.numeric' => 'Quận/Huyên không hợp lệ',
                'areaWard.required' => 'Phường/Xã không được bỏ trống',
                'areaWard.numeric' => 'Phường/Xã không hợp lệ',
                'address.required' => 'Địa chỉ chi nhánh không được bỏ trống',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'EErrorCode' => EErrorCode::VALIDATION_ERROR,
                'mgs' => $validator->errors()
            ]);
        }

        $data = [
            'shopId' => $request->shopId,
            'name' => $request->name,
            'phone1' => $request->phone1,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'description' => $request->description,
            'areaProvince' => $request->areaProvince,
            'areaDistrict' => $request->areaDistrict,
            'areaWard' => $request->areaWard,
        ];

        $result = $this->branchService->savePost($data, auth()->id());

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.save-data-success', [
                'objectName' =>'chi nhánh'
            ])
        ]);
    }

    public function deleteBranch()
    {
        $id = request('id');
        $delete = $this->branchService->deleteBranch($id);
        if ($delete['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($delete);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.delete-data-success', [
                'objectName' => 'chi nhánh'
            ])
        ]);
    }

    public function getBranchInfo($id) {
        $option = [
			'id' => $id,
			'first' => true
		];
		$branch = $this->branchService->getByOptions($option);
		$data = [
            'shopId' => $branch->id,
            'name' => $branch->name,
            'address' => $branch->address,
            'phone1' => $branch->phone1,
            'description' => $branch->description,
            'longitude' => $branch->longitude,
            'latitude' => $branch->latitude,
            'areaProvince' => $branch->province_id,
            'areaDistrict' => $branch->district_id,
            'areaWard' => $branch->ward_id,
		];
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'branch' => $data,
		]);
    }
}
