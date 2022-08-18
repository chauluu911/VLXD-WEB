<?php

namespace App\Repositories;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use App\Enums\EStatus;
use App\Models\CategoryAttribute;
use Illuminate\Support\Facades\DB;

class CategoryAttributeRepository extends BaseRepository {
	public function __construct(CategoryAttribute $categoryAttribute) {
		$this->model = $categoryAttribute;
	}

    public function getByOptions(array $options = []) {
	    $result = $this->model
			->from('category_attribute as ca')
            ->select('ca.*');

	    if (!Arr::has($options, ['status', 'not_status'])) {
            $result->where('ca.status', EStatus::ACTIVE);
        }

	    foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                    $result->where("ca.$key", $val);
                    break;
				case 'category_id':
					$result->where('category_id', $val);
					break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'seq');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("ca.$orderBy", $orderDirection);
                break;
        }

        return $this->getByOption($options, $result);
    }
}
