<?php

namespace App\Services;

use App\Enums\EErrorCode;
use App\Models\Order;
use App\Enums\EStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OrderDetail;
use App\Repositories\OrderDetailRepository;
use App\Services\OrderService;

class OrderDetailService {
	private OrderDetailRepository $orderDetailRepository;

    public function __construct(OrderDetailRepository $orderDetailRepository) {
		$this->orderDetailRepository = $orderDetailRepository;
    }

    public function getById($id) {
    	return $this->orderDetailRepository->getById($id);
    }

    public function getByOptions(array $options) {
        return $this->orderDetailRepository->getByOptions($options);
    }

    public function deleteOrderDetail($id, $loggedInUserId, $now) {
        return DB::transaction(function() use ($id, $loggedInUserId, $now) {
            $detail = $this->getById($id);
            $detail->status = EStatus::DELETED;
            $detail->deleted_at = $now;
            $detail->deleted_by = $loggedInUserId;
            $detail->save();
            return ['error' => EErrorCode::NO_ERROR, 'orderId' => $detail->order_id];
        });
    }

    public function saveOrderDetail($data, $loggedInUserId) {
    	return DB::transaction(function() use ($data, $loggedInUserId) {
    		if (!empty(Arr::get($data, 'orderDetailId'))) {
                $quantity = Arr::get($data, 'quantity');
    			$detail = $this->getById(Arr::get($data, 'orderDetailId'));
    			$detail->quantity = !empty($quantity) ? $detail->quantity + $quantity : $detail->quantity + 1;
                $detail->total = $detail->quantity * $detail->price;
    		} else {
    			$detail = new OrderDetail();
		    	$detail->product_id = Arr::get($data, 'productId');
		    	$detail->order_id = Arr::get($data, 'orderId');
		    	$detail->price = Arr::get($data, 'price');
		    	$detail->total = Arr::get($data, 'price') * Arr::get($data, 'quantity');
		    	$detail->quantity = Arr::get($data, 'quantity');
		    	$detail->status = EStatus::ACTIVE;
		    	$detail->created_by = $loggedInUserId;
		    	$detail->unit = !empty(Arr::get($data, 'unit')) ? Arr::get($data, 'unit') : $detail->unit;
    		}
    		$detail->save();
    		return ['error' => EErrorCode::NO_ERROR, 'detail' => $detail];
	    });  
    }
}
