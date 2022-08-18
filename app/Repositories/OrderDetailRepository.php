<?php

namespace App\Repositories;

use App\Models\OrderDetail;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderDetailRepository extends BaseRepository {

	public function __construct(OrderDetail $orderDetail) {
		$this->model = $orderDetail;
	}

    public function getByOptions(array $options) {
		$result = $this->model
			->from('order_detail as od')
			->select('od.*');
		foreach ($options as $key => $val) {
			if (!isset($val)) {
				continue;
			}
			switch ($key) {
				case 'id':
				case 'order_id':
				case 'status':
					$result->where("od.$key", $val);
					break;

			}
		}

		$orderBy = Arr::get($options,'orderBy', 'id');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("od.$orderBy", "$orderDirection");
                break;
        }
		return parent::getByOption($options, $result);
	}
}
