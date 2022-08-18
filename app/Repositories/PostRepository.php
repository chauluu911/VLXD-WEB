<?php

namespace App\Repositories;

use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\Models\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;

class PostRepository extends BaseRepository {

	public function __construct(Post $post) {
		$this->model = $post;
	}
	/**
	 * @param array $options
	 * @return bool|LengthAwarePaginator|Collection|User
	 */
	public function getByOptions(array $options) {
		$result = $this->model
			->from('post as p')
			->select('p.*');
		foreach ($options as $key => $val) {
			switch ($key) {
				case 'status':
					$result->where('p.status', $val);
					break;
				case 'q':
					$result->where('p.title_search', 'ilike', "%$val%")
						->orWhere('p.content_search', 'ilike', "%$val%");
					break;
				case 'id':
					$result->where('p.id', $val);
					break;
                case 'ignore':
                    $result->whereNotIn('p.id', $val);
                    break;
				case 'createdAtFrom':
                    $result->where('p.created_at', '>=', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'createdAtTo':
                    $result->where('p.created_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                	break;
			}
		}

		$orderBy = Arr::get($options,'orderBy', 'id');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("p.$orderBy", "$orderDirection");
                break;
        }

		return parent::getByOption($options, $result);
	}
}
