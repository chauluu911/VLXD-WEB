<?php

namespace App\Models;
use App\Enums\EStatus;

class ProductCategory extends BaseModel {
	protected $table = 'product_category';

	public function categories() {
		return $this->hasOne(Category::class, 'id', 'category_id');
	}
}
