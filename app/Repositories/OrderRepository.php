<?php

namespace App\Repositories;

use App\Constant\ConfigTableName;
use App\Enums\ECustomOrderStatusForUser;
use App\Enums\EDateFormat;
use App\Enums\EDeliveryStatus;
use App\Enums\EOrderStatus;
use App\Enums\EOrderType;
use App\Enums\EStatus;
use App\Enums\ETableName;
use App\Models\Order;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderRepository extends BaseRepository {

	public function __construct(Order $order) {
		$this->model = $order;
	}

    public function getByOptions(array $options) {
		$result = $this->model
			->from('orders as o')
			->select('o.*');

        if(Arr::has($options,['get_for_order_list_of_user'])) {
            $result->Join('shop as s','s.id','o.shop_id');
            $result->select('o.id', 'o.user_id','o.code', 'o.status','o.canceled_by',
                'o.delivery_status', 'o.receiver_name', 'o.total',
                'o.created_at','o.shop_id', 'o.shipping_fee','rated', 's.avatar_type as shop_avatar_type',
                's.avatar_path as shop_avatar','s.name as shop_name','s.phone as shop_phone'
            );
        }
        if(Arr::has($options,['get_for_order_list_of_shop']) || Arr::has($options,['admin_get_for_order_list'])) {
            $result->Join('users as u','u.id','o.user_id');
            $result->select('o.id', 'o.user_id','o.code', 'o.status','o.canceled_by',
                'o.delivery_status', 'o.receiver_name', 'o.total', 'o.receiver_phone',
                'o.created_at','o.shop_id', 'o.shipping_fee','rated',
                'u.avatar_path as user_avatar' ,'u.name as user_name','u.phone as user_phone'
            );
        }

        if(Arr::has($options,['for_admin_payment_list'])) {
            $result->Join('shop as s','s.id','o.shop_id');
            $result->Join('users as u','u.id','o.user_id');
            $result->select('o.id', 'o.user_id','o.code', 'o.status','o.canceled_by',
                'o.delivery_status', 'o.payment_status' , 'o.receiver_name', 'o.total as price', 'o.receiver_phone',
                'o.created_at','o.shop_id', 'o.shipping_fee','rated',
                'u.avatar_path as user_avatar' ,'u.name as user_name','u.phone as user_phone',
                's.avatar_path as shp_avatar', 's.code as shp_code', 's.name as shp_name', 's.level as shp_level',
                's.phone as shp_phone', 's.email as shp_email', 's.address as shp_address', 's.status as shp_status',
                's.created_at as shp_created_at', 's.fb_page as shp_fb_page', 's.zalo_page as shp_zalo_page',
                's.payment_status as shp_payment_status', 's.id as shp_id'
            );
        }
        // if (Arr::get($options, 'get_for_order_list', false)) {
		// 	$result
		// 		->leftJoin('users as usr', 'usr.id', 'o.user_id');
		// }

		//region filter
		// if (!Arr::hasAny($options, ['status', 'not_status'])) {
		// 	$result->where('o.status', EOrderStatus::CONFIRMED);
		// }
		if (Arr::get($options, 'get_detail')) {
			$result
				->join('order_detail as od', 'od.order_id', 'o.id')
				->where('o.status', '!=', EOrderStatus::DELETED)
				->where('od.status', EStatus::ACTIVE);
		}
		if(Arr::has($options,['custom_order_status_for_user'])) {
		    $customStatus = $options['custom_order_status_for_user'];
		    switch($customStatus) {
                case ECustomOrderStatusForUser::WAITING:
                    $result->where('o.status',EOrderStatus::WAITING);
                    break;
                case ECustomOrderStatusForUser::WAITING_FOR_DELIVERY:
                    $result->where('o.status',EOrderStatus::CONFIRMED)
                        ->where('o.delivery_status', EDeliveryStatus::WAITING_FOR_APPROVAL);
                    break;
                case ECustomOrderStatusForUser::ON_THE_WAY :
                    $result->where('o.delivery_status', EDeliveryStatus::ON_THE_WAY);
                    break;
                case ECustomOrderStatusForUser::DELIVERED_SUCCESS:
                    $result->where('o.delivery_status', EDeliveryStatus::DELIVERED_SUCCESS);
                    break;
                case ECustomOrderStatusForUser::CANCELED:
                    $result->where(function ($query) {
                        $query->where('o.status',EOrderStatus::CANCEL_BY_SHOP)
                            ->orWhere('o.status',EOrderStatus::CANCEL_BY_USER);
                    });
            }
        }

		//if get order for shop, delete option get order by user_id
        if(Arr::has($options,['get_for_order_list_of_shop'])) {
            unset($options['user_id']);
        }

        if(Arr::has($options,['for_admin_payment_list']) ||
            Arr::has($options,['admin_get_for_order_list']) ||
            Arr::has($options,['get_for_order_list_of_shop'])||
            Arr::has($options,['get_for_order_list_of_user'])) {
            $result->where('o.type', '!=', EOrderType::SHOPPING_CART);
        }

		foreach ($options as $key => $val) {
			if (!isset($val)) {
				continue;
			}
			switch ($key) {
				case 'id':
				case 'user_id':
				case 'status':
                case 'delivery_status':
				case 'shop_id':
				case 'type':
				case 'payment_status':
					$result->where("o.$key", $val);
					break;
				case 'not_status':
					$result->where('o.' . Str::replaceFirst('not_', '', $key), $val);
					break;
				case 'post_id':
					$result->where('o.meta->postId', $val);
					break;
                case 'created_at_lt':
				case 'created_at_to':
                    $result->where('o.created_at', '<',
                        $val->copy()->timezone(config('app.timezone'))
                            ->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'created_at_gt':
				case 'created_at_from':
                    $result->where('o.created_at', '>=',
                        $val->copy()->timezone(config('app.timezone'))
                            ->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
					break;
//				case 'type_list':
//					$result->select('o.id', 'o.user_id', 'o.status', 'o.payment_status',
//                        'o.payment_method', 'o.total as price', 'o.created_at','rated');
//					break;
				case 'q':
				    if(Arr::has($options,['get_for_order_list_of_shop']))
                    $result->where(function($query) use ($val) {
                        $query->orWhere('o.code','ilike', "%$val%")
                            ->orWhere('u.phone', 'ilike', "%$val%");
                    });
                    if(Arr::has($options,['get_for_order_list_of_user']))
                        $result->where(function($query) use ($val) {
                            $query->orWhere('o.code','ilike', "%$val%")
								->orWhere('s.phone', 'ilike', "%$val%");
                        });
                    if(Arr::has($options,['admin_get_for_order_list']))
                        $result->where(function($query) use ($val) {
                            $query->orWhere('o.code','ilike', "%$val%")
                                ->orWhere('u.phone', 'ilike', "%$val%")
                                ->orWhere('u.name_search', 'ilike', "%$val%");
                        });
                    if(Arr::has($options,['for_admin_payment_list']))
                        $result->where(function($query) use ($val) {
                            $query->orWhere('o.code','ilike', "%$val%")
                                ->orWhere('s.name_search', 'ilike', "%$val%")
                                ->orWhere('u.name_search', 'ilike', "%$val%")
                                ->orWhere('s.phone', 'ilike', "%$val%")
                                ->orWhere('u.phone', 'ilike', "%$val%");
                        });
                    break;
				case 'product_id':
					$result->where("od.product_id", $val);
					break;
				case 'order_detail_id':
					$result->where("od.id", $val);
					break;
			}
		}

		if (Arr::get($options, 'get_detail')) {
			$result->select('od.id as orderDetailId', 'od.quantity', 'od.total', 'od.product_id',
                'od.price', 'od.order_id');
		}

        if (Arr::get($options, 'total_price')) {
            $result->select('o.total as total');
        }

		if (Arr::get($options, 'get_cart')) {
			$result->select('o.id', 'o.shop_id', 'o.total');
		}
		//endregion

		$orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("o.$orderBy", "$orderDirection");
                break;
        }
		return parent::getByOption($options, $result);
	}
}
