<?php

namespace App\Repositories;
use App\Enums\ECategoryType;
use Illuminate\Support\Arr;
use App\Enums\EStatus;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryRepository extends BaseRepository {
	public function __construct(Category $category) {
		$this->model = $category;
	}

    public function getByOptions(array $options = []) {
	    $result = $this->model
			->from('category as c')
            ->select('c.*');

	    if (!Arr::has($options, ['status', 'not_status'])) {
            $result->where('c.status', EStatus::ACTIVE);
        }

        if (empty(Arr::get($options, 'parent_category_id')) && empty(Arr::get($options, 'get_all_category'))) {
            $result->where('c.parent_category_id', null);
        }

	    foreach ($options as $key => $val) {
            switch ($key) {
                case 'type':
                case 'status':
                    $result->where("c.$key", $val);
                    break;
                case 'not_status':
                    $result->where('c.status', '!=', $val);
                    break;
				case 'q':
					$result->where('c.name_search', 'ilike', "%$val%");
					break;
				case 'parent_category_id':
					$result->where('parent_category_id', $val);
					break;
                case 'with':
                    foreach ((array)Arr::get($options, $key, []) as $with) {
                        $result->with($with);
                    }
                    break;
                case 'get_data_for_user':
                    $result->where('type', ECategoryType::PRODUCT_CATEGORY);
                    $result->select('id', 'name', 'parent_category_id','logo_path');
                    break;
                case 'not_type':
                    $result->where('c.type', '<>', $val);
                    break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'seq');
        $orderDirection = Arr::get($options,'orderDirection', 'asc');
        switch ($orderBy) {
            default:
                $result->orderBy("c.$orderBy", $orderDirection);
                break;
        }

        return parent::getByOption($options, $result);
    }
}
