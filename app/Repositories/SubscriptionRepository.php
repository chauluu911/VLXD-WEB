<?php

namespace App\Repositories;

use App\Constant\ConfigTableName;
use App\Enums\EDateFormat;
use App\Enums\EPaymentType;
use App\Enums\EStatus;
use App\Enums\ETableName;
use App\Models\Subscription;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionRepository extends BaseRepository {

	public function __construct(Subscription $subscription) {
		$this->model = $subscription;
	}

    public function getByOptions(array $options) {
		$result = $this->model
			->from('subscription as sb')
			->select('sb.*');

		//region join
        if (Arr::get($options, 'payment_type', null) == EPaymentType::BUY_COINS) {
            $result->join('users as usr', 'usr.id', 'sb.user_id');
        }
        if (Arr::get($options, 'payment_type', null) == EPaymentType::PUSH_PRODUCT) {
            $result->join('product as prd', 'prd.id', 'sb.table_id');
            $result->join('shop as shp', 'shp.id', 'prd.shop_id');
        }
        if (Arr::get($options, 'payment_type', null) == EPaymentType::UPGRADE_SHOP) {
            $result->join('shop as shp', 'shp.id', 'sb.table_id');
        }


		//region filter
		if (Arr::hasAny($options, ['status', 'not_status'])) {
			$result->where('sb.status', EStatus::ACTIVE);
		}
//        if (Arr::get($options, 'payment_type', null) === EPaymentType::PUSH_PRODUCT) {
//            $result->where('sb.status', EStatus::ACTIVE);
//        }
		foreach ($options as $key => $val) {
			if (!isset($val)) {
				continue;
			}
			switch ($key) {
				case 'id':
				case 'user_id':
				case 'table_id':
				case 'table_name':
				case 'status':
                case 'payment_method':
				case 'payment_status':
					$result->where("sb.$key", $val);
					break;
				case 'not_payment_status':
					$result->where("sb.payment_status", null);
					break;
				case 'not_status':
					$result->where('sb.' . Str::replaceFirst('not_', '', $key), $val);
					break;
				case 'created_at_lt':
					$result->whereRaw('coalesce(sb.updated_at, sb.created_at) < ?', [
						$val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT)
					]);
					break;
				case 'created_at_gt':
					$result->whereRaw('coalesce(sb.updated_at, sb.created_at) >= ?', [
						$val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT)
					]);
					break;
				case 'type_list':
					$result->select('sb.id', 'sb.user_id', 'sb.status', 'sb.payment_status',
                        'sb.payment_method', 'sp.price', 'sb.table_name', 'sb.table_id',
                        'sb.created_at', 'sp.type','sp.name')
						->join('subscription_price as sp', DB::raw('text(sp.id)'), 'sb.payment_meta->subscriptionPriceId')
						->whereIn('table_name', [ETableName::DEPOSIT, ETableName::PRODUCT, ETableName::SHOP]);
					break;
				case 'payment_type':
					$result->where('sp.type', $val);
					break;
				case 'q':
                    if (Arr::get($options, 'payment_type', null) == EPaymentType::BUY_COINS) {
                        $result->where(function($query) use ($val) {
                            $query->orWhere('usr.name_search', 'ilike', "%$val%")
                                ->orWhere('usr.phone', 'ilike', "%$val%");
                        });
                    } else {
                        if (Arr::get($options, 'payment_type', null) == EPaymentType::PUSH_PRODUCT) {
                            $result->where(function($query) use ($val) {
                                $query->orWhere('prd.name_search', 'ilike', "%$val%")
                                    ->orWhere('shp.name_search', 'ilike', "%$val%")
                                    ->orWhere('shp.phone', 'ilike', "%$val%");
                            });
                        }
                        if (Arr::get($options, 'payment_type', null) == EPaymentType::UPGRADE_SHOP) {
                            $result->where(function($query) use ($val) {
                                $query->orWhere('shp.name_search', 'ilike', "%$val%")
                                    ->orWhere('shp.phone', 'ilike', "%$val%");
                            });
                        }
                    }
                    break;

				case 'subscription_price_id':
					$result->where('sb.payment_meta->subscriptionPriceId', $val);
					break;
			}
		}

		if (Arr::hasAny($options, 'null_payment_status')) {
			$result->whereNull('sb.payment_status');
		}
		//endregion
        $orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("sb.$orderBy", "$orderDirection");
                break;
        }

		return parent::getByOption($options, $result);
	}
}
