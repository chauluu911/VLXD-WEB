<?php

namespace App\Models;
use App\Enums\ETableName;
use App\Enums\EStatus;
use App\Enums\EResourceType;

class Shop extends BaseModel {
	protected $table = 'shop';

	public function getArea() {
		return $this->hasOne(Area::class, 'id', 'area_id')->where('type', '!=', 0);
	}

	public function getEvaluate() {
		return $this->hasMany(Review::class, 'table_id', 'id')->where('table_name', ETableName::SHOP);
	}

	public function getProduct() {
		return $this->hasMany(Product::class, 'shop_id', 'id')->where('status', EStatus::ACTIVE);
	}

	public function image() {
		return $this->hasMany(ShopResource::class, 'shop_id', 'id')
			->select('path_to_resource')
			->where('type', EResourceType::IMAGE)
			->where('status', EStatus::ACTIVE)
            ->orderBy('id', 'desc');
	}

	public function video() {
		return $this->hasMany(ShopResource::class, 'shop_id', 'id')
			->select('path_to_resource')
			->where('type', EResourceType::VIDEO)
			->where('status', EStatus::ACTIVE)
            ->orderBy('id', 'desc');
	}
}
