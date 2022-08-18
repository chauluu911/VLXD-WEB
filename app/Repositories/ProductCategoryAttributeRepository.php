<?php

namespace App\Repositories;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use App\Enums\EStatus;
use App\Models\ProductCategoryAttribute;
use Illuminate\Support\Facades\DB;

class ProductCategoryAttributeRepository extends BaseRepository {
	public function __construct(ProductCategoryAttribute $productCategoryAttribute) {
		$this->model = $productCategoryAttribute;
	}

    public function getByOptions(array $options = []) {
	    $result = $this->model
			->from('product_category_attribute as pca');

	    if (!Arr::has($options, ['status', 'not_status'])) {
            $result->where('pca.status', EStatus::ACTIVE);
        }

	    foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                    $result->where("pca.$key", $val);
                    break;
				case 'category_id':
					$result->where('pca.category_id', $val);
					break;
                case 'product_id':
                    $result->where('pca.product_id', $val);
                    break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'id');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("pca.$orderBy", $orderDirection);
                break;
        }

        return $this->getByOption($options, $result);
    }
}
