<?php

namespace App\Models;

use App\Enums\EStatus;
use App\User;
use App\Enums\EOrderType;

class Order extends BaseModel {
	protected $table = 'orders';

	public function getPaymentStatusAttribute($value) {
		return $this->getNumericValue($value);
	}

	public function getAmountAttribute($value) {
		return $this->getNumericValue($value);
	}

	public function getDiscountAttribute($value) {
		return $this->getNumericValue($value);
	}

	public function getTotalAttribute($value) {
		return $this->getNumericValue($value);
	}

	public function user() {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

    public function shop() {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id')
            ->where('order_detail.status', EStatus::ACTIVE);
    }
}
