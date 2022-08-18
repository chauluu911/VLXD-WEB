<?php

namespace App\Repositories;

use App\Constant\ConfigTableName;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Enums\ETableName;
use App\Models\SubscriptionPrice;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionPriceRepository extends BaseRepository {

	public function __construct(SubscriptionPrice $subscriptionPrice) {
		$this->model = $subscriptionPrice;
	}

	public function getByOptions(array $options = []) {
	    $result = $this->model
			->from('subscription_price as sp')
            ->select('sp.*');

	    if (!Arr::has($options, ['status', 'not_status'])) {
            $result->where('sp.status', EStatus::ACTIVE);
        }

	    foreach ($options as $key => $val) {
            switch ($key) {
                case 'type':
                case 'status':
                case 'price':
                case 'created_by':
				case 'id':
                    $result->where("sp.$key", $val);
                    break;
                case 'not_status':
                    $result->where('sp.status', '!=', $val);
                    break;
				case 'q':
					$result->where('sp.name_search', 'ilike', "%$val%");
					break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'id');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("sp.$orderBy", $orderDirection);
                break;
        }

        return $this->getByOption($options, $result);
    }
}
