<?php

namespace App\Repositories;

use App\Enums\EPlatform;
use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\Models\Banner;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;

class BannerRepository extends BaseRepository {

	public function __construct(Banner $banner) {
		$this->model = $banner;
	}
	/**
	 * @param array $options
	 * @return bool|LengthAwarePaginator|Collection|User
	 */
	public function getByOptions(array $options) {
		$result = $this->model
			->from('banner as b')
			->select('b.*');
		if (!Arr::get($options, 'status')) {
			if (Arr::get($options, 'banner_home')) {
				$result->where('b.status', EStatus::ACTIVE);
			} else {
				$result->where('b.status', '!=', EStatus::DELETED);
			}
		}
		foreach ($options as $key => $val) {
			switch ($key) {
				case 'status':
					$result->where('b.status', $val);
					break;
				case 'type':
					$result->where('b.type', $val);
					break;
				case 'shop_id':
					$result
						->join('shop_banner as sb', 'sb.banner_id', 'b.id')
						->where('sb.shop_id', $val);
					break;
				case 'platform':
					$result->whereIn('b.platform', [$val, EPlatform::WEB_AND_MOBILE]);
				 	break;
			}
		}
		if (!Arr::get($options, 'banner_shop')) {
			$result->where(function($query) {
	            $query->orWhere('b.valid_to', '>', now()->timezone(config('app.timezone'))
	                ->format(EDateFormat::MODEL_DATE_FORMAT))
	                ->orWhereNull('b.valid_to');
	        });
		}
		$orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("b.$orderBy", "$orderDirection");
                break;
        }

		return parent::getByOption($options, $result);
	}
}
