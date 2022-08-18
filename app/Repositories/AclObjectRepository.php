<?php

namespace App\Repositories;
use App\Models\AclObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class AclObjectRepository extends BaseRepository {
	public function __construct(AclObject $model) {
		$this->model = $model;
	}

	/**
	 * @return Collection<Role>
	 */

    public function getByOptions(array $options) {
		$result = $this->model
			->from('acl_object');

		foreach ($options as $key => $val) {
			switch ($key) {
				case 'status':
					$result->where('status', $val);
					break;
			}
		}

		$orderBy = Arr::get($options,'orderBy', 'id');
        $orderDirection = Arr::get($options,'orderDirection', 'asc');
        switch ($orderBy) {
            default:
                $result->orderBy("$orderBy", "$orderDirection");
                break;
        }
		return parent::getByOption($options, $result);
	}
}
