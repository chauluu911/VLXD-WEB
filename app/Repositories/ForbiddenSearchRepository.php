<?php

namespace App\Repositories;

use App\Constant\ConfigTableName;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Enums\ETableName;
use App\Models\ForbiddenSearch;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForbiddenSearchRepository extends BaseRepository {

	public function __construct(ForbiddenSearch $forbiddenSearch) {
		$this->model = $forbiddenSearch;
	}

	public function getByOptions(array $options = []) {
	    $result = $this->model
			->from('forbidden_search as fs')
            ->select('fs.*');

	    if (!Arr::has($options, ['status', 'not_status'])) {
            $result->where('fs.status', EStatus::ACTIVE);
        }

	    foreach ($options as $key => $val) {
            switch ($key) {
                case 'type':
                case 'status':
                case 'price':
                case 'created_by':
				case 'id':
                    $result->where("fs.$key", $val);
                    break;
                case 'not_status':
                    $result->where('fs.status', '!=', $val);
                    break;
				case 'q':
					$result->where('fs.name_search', 'ilike', "%$val%");
					break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'id');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("fs.$orderBy", $orderDirection);
                break;
        }

        return $this->getByOption($options, $result);
    }
}
