<?php

namespace App\Repositories;

use App\Enums\EApprovalStatus;
use App\Enums\EProductStatus;
use App\Enums\ETableName;
use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;

class ProductRepository extends BaseRepository {

	public function __construct(Product $product) {
		$this->model = $product;
	}
	/**
	 * @param array $options
	 * @return bool|LengthAwarePaginator|Collection|User
	 */
	public function getByOptions(array $options) {
		$result = $this->model
			->from('product as p')
			->select('p.*');

		if (empty(Arr::get($options, 'status', null)) && empty(Arr::get($options, 'get_all_status', null))) {
			$result->where('p.status', EStatus::ACTIVE);
		}
		if (!empty(Arr::get($options, 'category_id', null)) || !empty(Arr::get($options, 'attribute', null)) || !empty(Arr::get($options,'get_for_user'))) {
			$result->join('product_category as pc', 'pc.product_id', 'p.id')
				->join('category as c', 'c.id', 'pc.category_id')
                ->where('c.status', EStatus::ACTIVE);
		}
		if (Arr::has($options, ['area_id'])) {
            $result->join('shop as s', 's.id', 'p.shop_id');
        }
        if(Arr::get($options,'get_subscription', null)) {
            $result->leftJoin('subscription as s', function ($leftJoin) {
                $leftJoin->on('s.table_id', '=', 'p.id')
                    ->where('s.status', EStatus::ACTIVE)
                    ->where('s.table_name', ETableName::PRODUCT)
                    ->where('valid_from', '<', now())
                    ->where('s.valid_to', '>', now());
            });
		}

        if (Arr::get($options,'attribute')) {
        	$attribute = Arr::get($options,'attribute');
        	foreach ($attribute as $index => $value) {
        		if (!empty($attribute[$index]['id'])) {
        			$result
		        		->select('p.*')
		        		->whereExists(function ($query) use ($attribute, $index) {
			               	$query
			                    ->from('product_category_attribute as pca')
			                    ->whereRaw('pca.product_id = p.id')
			                    ->where('pca.category_attribute_id', $attribute[$index]['id']);
			                if (!empty($attribute[$index]['value'])) {
			                	$query->whereIn('pca.value->value', $attribute[$index]['value']);
			                }
			           });
        		}
        	}
        }

        $userId = Arr::get($options,'user_id', null);

        if(Arr::get($options,'get_saved_product', null)
            && $userId) {
            $result->Join('user_interest as usri', function ($Join) use ($userId) {
                $Join->on('usri.table_id', '=', 'p.id')
                    ->where('usri.table_name', ETableName::PRODUCT)
                    ->where('usri.user_id',$userId);
            })->select('p.*', 'usri.id as savedId');
        } else if ($userId) {
            $result->leftJoin('user_interest as usri', function ($leftJoin) use ($userId) {
                $leftJoin->on('usri.table_id', '=', 'p.id')
                    ->where('usri.table_name', ETableName::PRODUCT)
                    ->where('usri.user_id',$userId);
            })->select('p.*', 'usri.id as savedId');
        }
		foreach ($options as $key => $val) {
			switch ($key) {
				case 'status':
					$result->where('p.status', $val);
					break;
				case 'approval_status':
					$result->where('p.approval_status', (int)$val);
					break;
                case 'get_product_waiting_and_active':
                    $result->where(function ($query)  {
                        $query->orWhere('p.approval_status', EApprovalStatus::WAITING)
                            ->orWhere('p.approval_status', EApprovalStatus::APPROVED);
                    });
                    break;
				case 'q':
					$result->where(function ($query) use ($val) {
				        $query->orWhere('p.name_search', 'ilike', "%$val%")
                            ->orWhere('p.code', 'ilike',"%$val%");
                    });
					break;
				case 'id':
					$result->where('p.id', $val);
					break;
				case 'code':
					$result->where('p.code', $val);
					break;
				case 'shop_id':
					$result->where('p.shop_id', $val);
					break;
				case 'createdAtFrom':
                    $result->where('p.created_at', '>=', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'createdAtTo':
                    $result->where('p.created_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                	break;
                case 'category_id':
                	$result->where(function ($query) use ($val) {
				        $query->orWhere('c.id', $val)
                            ->orWhere('c.parent_category_id', $val);
                    });
                	break;
                case 'not_id':
                	$result->where('p.id', '<>', $val);
                	break;
                case 'min_price':
                	$result->where('p.price', '>=', $val);
                	break;
                case 'max_price':
                	$result->where('p.price', '<', $val);
                	break;
                case 'area_id':
                	$result->whereIn('s.area_id', $val);
                	break;
                case 'approval_status':
                    $result->where('p.approval_status', $val);
                    break;

			}
		}

		//chỉ lấy những product của những shop còn hoạt động
        $result->join('shop as ps', 'ps.id', 'p.shop_id');
		$result->where('ps.status',EStatus::ACTIVE);


        if(Arr::get($options,'get_saved_product', null)){
            //order theo user_interest created_at
            $result->orderBy("usri.created_at","desc");
        }

        if(Arr::get($options,'get_for_homepage', null) || Arr::get($options,'get_subscription', null)){
            //order theo created_at của sub
            $result->orderByRaw("s.created_at desc NULLS LAST");
        }
        if(Arr::get($options, 'get_product_waiting_and_active', null)){
            $result->orderBy("p.approval_status","asc");
        }
        $orderBy = Arr::get($options,'orderBy', 'publish_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("p.$orderBy", "$orderDirection");
                break;
        }

		return parent::getByOption($options, $result);
	}

    public function getBranchShop($shopId) {
        return $this->model
        ->from('branch as br')
        ->leftJoin('shop as sh', 'sh.id', 'br.shop_id')
        ->where('br.shop_id', $shopId)
        ->select('br.*', 'sh.name as name_shop')
        ->get();
    }

}
