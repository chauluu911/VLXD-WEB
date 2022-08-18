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
use App\Repositories\SubscriptionPriceRepository;
use App\Enums\ESubscriptionPriceType;

class SubscriptionPriceService {

    private SubscriptionPriceRepository $subscriptionPriceRepository;

    public function __construct(SubscriptionPriceRepository $subscriptionPriceRepository) {
		$this->subscriptionPriceRepository = $subscriptionPriceRepository;
    }

    public function getById($id) {
        return $this->subscriptionPriceRepository->getById($id);
    }

    public function getByOptions($options) {
        return $this->subscriptionPriceRepository->getByOptions($options);
    }

    public function deletePackage($id, $loggedInUserId) {
        return DB::transaction(function() use ($id, $loggedInUserId) {
            $subscriptionPrice = $this->getById($id);
            if (empty($subscriptionPrice)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            if ($subscriptionPrice->status == EStatus::DELETED) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.data-was-deleted')];
            }
            $subscriptionPrice->deleted_by = $loggedInUserId;
            $subscriptionPrice->deleted_at = now();
            $subscriptionPrice->status = EStatus::DELETED;
            $subscriptionPrice->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function savePackage($data, $loggedInUserId) {
        try {
            return DB::transaction(function() use ($data, $loggedInUserId) {
                $id = Arr::get($data, 'id');
                if ($id) {
                    $subscriptionPrice = $this->getById($id);
                    if (empty($subscriptionPrice)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                    }
                    //$category->updated_by = $loggedInUserId;
                } else {
                    if (Arr::get($data, 'type') == ESubscriptionPriceType::DEPOSIT) {
                        $subscriptionPrice = $subscriptionPrice = $this->getByOptions([
                            'type' => Arr::get($data, 'type'),
                            'price' => Arr::get($data, 'price'),
                            'first' => true,
                        ]);
                        if (empty($subscriptionPrice)) {
                            $subscriptionPrice = new SubscriptionPrice();
                            $subscriptionPrice->created_by = $loggedInUserId;
                        }
                    } else {
                        $subscriptionPrice = new SubscriptionPrice();
                        $subscriptionPrice->created_by = $loggedInUserId;
                    }
                }
                $subscriptionPrice->name = Arr::get($data, 'name', $subscriptionPrice->name);
                $subscriptionPrice->num_day = Arr::get($data, 'numDay', null);
                $subscriptionPrice->price = Arr::get($data, 'price', $subscriptionPrice->price);
                $subscriptionPrice->original_price = Arr::get($data, 'price', $subscriptionPrice->original_price);
                $subscriptionPrice->status = EStatus::ACTIVE;
                $subscriptionPrice->type = Arr::get($data, 'type', $subscriptionPrice->type);
                if (Arr::get($data, 'type') == ESubscriptionPriceType::PACKAGE_PUSH_PRODUCT) {
                    $subscriptionPrice->description = 'Đẩy tin lên vị trí đầu tiên trong vòng '
                    . $subscriptionPrice->num_day . ' ngày';
                }
                $subscriptionPrice->save();
                return [
                    'error' => EErrorCode::NO_ERROR,
                    'subscriptionPrice' => $subscriptionPrice
                ];
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
