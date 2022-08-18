<?php

namespace App\Models;
use App\Enums\EResourceType;
use App\Enums\EStatus;
use App\Enums\EPaymentStatus;
use App\Enums\ETableName;

class Product extends BaseModel {
	protected $table = 'product';

	public function image() {
		return $this->hasMany(ProductResource::class, 'product_id', 'id')
			->where('type', EResourceType::IMAGE)
			->where('status', EStatus::ACTIVE);
	}

	public function video() {
		return $this->hasMany(ProductResource::class, 'product_id', 'id')
			->where('type', EResourceType::VIDEO)
			->where('status', EStatus::ACTIVE);
	}

	public function productCategories() {
		return $this->hasMany(ProductCategory::class, 'product_id', 'id')
			->where('status', EStatus::ACTIVE);
	}

	public function productCategoryAttributes() {
		return $this->hasMany(ProductCategoryAttribute::class, 'product_id', 'id')
			->orderBy('id', 'asc')
			->where('status', EStatus::ACTIVE);
	}

	public function getSubscription() {
		return $this->hasOne(Subscription::class, 'table_id', 'id')
			->where('status', EStatus::ACTIVE)
            ->where('valid_to', '>', now())
            ->where('valid_from','<',now())
			->where('table_name', ETableName::PRODUCT);
	}

	public function packagePushProductWaiting() {
		return $this->hasOne(Subscription::class, 'table_id', 'id')
			->where('status', EStatus::ACTIVE)
			->where('payment_status', EPaymentStatus::WAITING)
			->where('table_name', ETableName::PRODUCT);
	}

	public function getShop() {
		return $this->belongsTo(Shop::class, 'shop_id', 'id')
			->where('status', EStatus::ACTIVE);
	}
}
