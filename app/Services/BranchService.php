<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Enums\EErrorCode;
use Illuminate\Support\Carbon;
use App\Enums\EStatus;
use Illuminate\Support\Arr;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Branch;

use App\Repositories\BranchRepository;

class BranchService {
	private BranchRepository $branchRepository;

    public function __construct(BranchRepository $branchRepository) {
        $this->branchRepository = $branchRepository;
    }

    public function getById($branchId) {
	    return $this->branchRepository->getById($branchId);
    }

    public function getByOptions(array $options) {
        return $this->branchRepository->getByOptions($options);
    }

    public function getListShop() {
        return $this->branchRepository->getListShop();
    }

    public function savePost($data, $loggedInUserId) {
        $fileToDeleteIfError = [];
        try {
            return DB::transaction(function() use ($data, $loggedInUserId, &$fileToDeleteIfError) {
                $id = Arr::get($data, 'id');
                if ($id) {
                    $branch = $this->getById($id);
                    if (empty($branch)) {
                        return ['error' => EErrorCode::ERROR,
                        'msg' => __('common/error.invalid-request-data')];
                    }
                    $branch->updated_by = $loggedInUserId;
                } else {
                    $branch = new Branch();
                    $branch->status = EStatus::ACTIVE;
                }

                $branch->name = Arr::get($data, 'name');
                $branch->longitude = Arr::get($data, 'longitude');
                $branch->latitude = Arr::get($data, 'latitude');
                $branch->phone1 = Arr::get($data, 'phone1');
                $branch->address = Arr::get($data, 'address');
                $branch->description = Arr::get($data, 'description');
                $branch->shop_id = Arr::get($data, 'shopId');
                $branch->province_id = Arr::get($data, 'areaProvince');
                $branch->district_id = Arr::get($data, 'areaDistrict');
                $branch->ward_id = Arr::get($data, 'areaWard');

                $branch->save();

                return [
                	'error' => EErrorCode::NO_ERROR,
					'branch' => $branch,
				];
            });
        } catch (\Exception $e) {
            FileUtility::removeFiles($fileToDeleteIfError);
            throw $e;
        }
    }

    public function getBranch() {
        return $this->branchRepository->getBranch();
    }

    public function deleteBranch($id) {
        return DB::transaction(function() use ($id) {
            $branch = $this->getById($id);
            if (empty($branch)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $branch->status = EStatus::DELETED;
            $branch->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
}
