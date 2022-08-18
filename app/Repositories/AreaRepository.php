<?php

namespace App\Repositories;
use App\Models\Area;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use App\Enums\EStatus;
use Illuminate\Support\Facades\DB;

class AreaRepository extends BaseRepository {

	public function __construct(Area $area) {
		$this->model = $area;
	}

    public function getByOptions(array $options = []) {
	    $result = $this->model->from('area as a');
	    foreach ($options as $key => $val) {
            if (!isset($val)) {
                continue;
            }
            switch ($key) {
                case 'id':
                    $result->where('a.id', '=', $val);
                    break;
                case 'not_status':
                    $result->where('a.status', '!=', $val);
                    break;
				case 'q':
					$result->where('a.name_search', 'ilike', "%$val%");
					break;
				case 'parent_area_id':
					$result->where('parent_area_id', $val);
					break;
				case 'country_id':
					$result->where('country_id', $val);
					break;
                case 'with':
                    foreach ((array)Arr::get($options, $key, []) as $with) {
                        $result->with($with);
                    }
                    break;
                case 'type':
                	if (is_array($val)) {
                		$result->whereIn('type', $val);
                	} else {
                		$result->where('type', $val);
                	}
                	break;
                case 'get_id_name':
                    $result->select('id', 'name', 'type');
                    break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'seq');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            case 'name':
				$result->orderBy('a.name', $orderDirection);
                break;
            default:
                $result->orderBy("a.$orderBy", $orderDirection)
                    ->orderBy('a.name', 'asc');
                break;
        }

        return $this->getByOption($options, $result);
    }
}
