<?php

namespace App\Services;

use App\Constant\ConfigTableName;
use App\Enums\EErrorCode;
use App\Enums\EPaymentStatus;
use App\Models\SubscriptionPrice;
use App\Enums\EStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Repositories\ForbiddenSearchRepository;
use App\Enums\ESubscriptionPriceType;
use App\Models\ForbiddenSearch;

class ForbiddenSearchService {

    private ForbiddenSearchRepository $forbiddenSearchRepository;

    public function __construct(ForbiddenSearchRepository $forbiddenSearchRepository) {
		$this->forbiddenSearchRepository = $forbiddenSearchRepository;
    }

    public function getById($id) {
        return $this->forbiddenSearchRepository->getById($id);
    }

    public function getByOptions($options) {
        return $this->forbiddenSearchRepository->getByOptions($options);
    }

    public function delete($id, $loggedInUserId) {
        return DB::transaction(function() use ($id, $loggedInUserId) {
            $forbiddenSearch = $this->getById($id);
            if (empty($forbiddenSearch)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            if ($forbiddenSearch->status == EStatus::DELETED) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.data-was-deleted')];
            }
            $forbiddenSearch->deleted_at = now();
            $forbiddenSearch->status = EStatus::DELETED;
            $forbiddenSearch->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function save($data, $loggedInUserId) {
        try {
            return DB::transaction(function() use ($data, $loggedInUserId) {
                $id = Arr::get($data, 'id');
                if ($id) {
                    $forbiddenSearch = $this->getById($id);
                    if (empty($forbiddenSearch)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                    }
                    //$category->updated_by = $loggedInUserId;
                } else {
                    $forbiddenSearch = new ForbiddenSearch();
                }
                $forbiddenSearch->name = Arr::get($data, 'name', $forbiddenSearch->name);
                $forbiddenSearch->status = EStatus::ACTIVE;
                $forbiddenSearch->save();
                return [
                    'error' => EErrorCode::NO_ERROR,
                    'forbiddenSearch' => $forbiddenSearch
                ];
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
