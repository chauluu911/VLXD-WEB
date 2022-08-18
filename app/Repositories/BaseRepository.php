<?php

namespace App\Repositories;

use App\Constant\DefaultConfig;
use App\Models\BaseModel;
use App\Enums\EStatus;
use App\Traits\Repository\ServerDateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

abstract class BaseRepository {

	use ServerDateTime;

    /**
     * The Model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Destroy a model.
     *
     * @param int $id
     * @return void
     *
     * @throws \Exception
     */
    public function destroy($id) {
        $this->getById($id)->delete();
    }

    /**
     * Get Model by id.
     *
     * @param  int $id
     * @return \App\Models\BaseModel
     */
    public function getById(int $id) {
        return $this->model->where('id', $id)->first();
    }

    public function getByCode($code) {
        return $this->model->where('code', $code)->first();
    }

	/**
	 * Get Collection by id list.
	 *
	 * @param array $idList
	 * @return Collection<\App\Models\BaseModel>
	 */
	public function getByIds(array $idList) {
		return $this->model->whereIn('id', $idList)->get();
	}

    /**
     * Update Model with data array
     *
     * @param int $id
     * @param array $data data to update
     * @return \App\Models\BaseModel
     */
    public function update($id, $data) {
        $model = $this->getById($id);
        foreach ($data as $key => $val) {
            $model->$key = $val;
        }
        $model->save();
        return $model;
    }

    /**
     * @param array $options
     * @param null $queryBuilder
     * @return \App\Models\BaseModel|Collection<\App\Models\BaseModel>|LengthAwarePaginator<BaseModel>|null
     */
    public function getByOption($options = [], $queryBuilder = null) {
        if (empty($queryBuilder)) {
            $queryBuilder = $this->model->select('*');
        }

		if (!empty($options['distinct'])) {
			if (is_array($options['distinct']) || is_string($options['distinct'])) {
				return $queryBuilder->distinct($options['distinct']);
			}
			$queryBuilder->distinct();
		}
        if (!empty($options['exists'])) {
            return $queryBuilder->exists();
        }
        if (!empty($options['first'])) {
            return $queryBuilder->first();
        }
        if (!empty($options['sum'])) {
            return $queryBuilder->sum($options['sum']);
        }
		if (!empty($options['count'])) {
			if (is_array($options['count']) || is_string($options['count'])) {
				return $queryBuilder->count($options['count']);
			}
			return $queryBuilder->count();
		}
        if (Arr::has($options, 'pageSize')) {
            return $queryBuilder->paginate(empty($options['pageSize']) ? DefaultConfig::DEFAULT_PAGE_SIZE : $options['pageSize']);
        }
        return $queryBuilder->get();
    }
}
