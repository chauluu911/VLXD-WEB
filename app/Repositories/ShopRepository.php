<?php

namespace App\Repositories;

use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\Models\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;

class ShopRepository extends BaseRepository {

	public function __construct(Shop $shop) {
		$this->model = $shop;
	}
	/**
	 * @param array $options
	 * @return bool|LengthAwarePaginator|Collection|User
	 */
	public function getByOptions(array $options) {
		$result = $this->model
			->from('shop as s')
			->select('s.*');
		foreach ($options as $key => $val) {
			switch ($key) {
				case 'status':
                    if ($val == EStatus::EXCEPT_DELETED) {
                        $result->where('s.status','!=', EStatus::DELETED);
                    }
                    else {
                        $result->where('s.status', $val);
                    }
                    break;
                case 'payment_status':
                    $result->where('s.payment_status', $val);
                    break;
				case 'q':
				    $result->where(function ($query) use ($val) {
				        $query->orWhere('s.name_search', 'ilike', "%$val%")
                            ->orWhere('s.code', 'ilike',"%$val%")
                            ->orWhere('s.phone', 'ilike',"%$val%")
                            ->orWhere('s.email', 'ilike',"%$val%");
                    });
				    break;
				case 'id':
					$result->where('s.id', $val);
					break;
				case 'user_id':
					$result->where('s.user_id', $val);
					break;
				case 'createdAtFrom':
                    $result->where('s.created_at', '>=', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'createdAtTo':
                    $result->where('s.created_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                case 'user_get_info':
                	$result->select('s.id', 's.user_id', 's.name', 's.phone', 's.address', 's.latitude', 's.longitude', 's.status', 's.avatar_path as avatar', 's.payment_status', 's.area_id', 's.code', 's.email', 's.created_at', 'fb_page as fb', 's.level', 'avatar_type as avatarType', 'zalo_page as zalo', 'identity_code as identityCode', 'identity_date', 'identity_place as identityPlace');
                	break;
			}
		}

        if (Arr::get($options, 'status', null) == EStatus::EXCEPT_DELETED){
            $result->orderBy('s.status', 'asc');
        }

		$orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("s.$orderBy", "$orderDirection");
                break;
        }

		return parent::getByOption($options, $result);
	}

    public function getByOwnerUserId($ownerUserId) {
        return $this->model->where('user_id', $ownerUserId)->where('status', EStatus::ACTIVE)->first();
    }

	public function didEmailExist(string $email, int $exceptShopId = null) {
		return $this->model
			->where(function ($query) use ($exceptShopId) {
				if ($exceptShopId != null) {
					$query->where('id', '!=', $exceptShopId);
				}
			})
			->where('email', $email)
            ->where('status', EStatus::ACTIVE)
			->exists();
	}

	public function didPhoneExist(string $phone, int $exceptShopId = null) {
        return $this->model
            ->where(function ($query) use ($exceptShopId) {
                if ($exceptShopId != null) {
                    $query->where('id', '!=', $exceptShopId);
                }
            })
            ->where('phone', $phone)
            ->where('status', EStatus::ACTIVE)
            ->exists();
    }
}
