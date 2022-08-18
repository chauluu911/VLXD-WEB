<?php

namespace App\Models;
use App\Enums\EStatus;

class ProductCategoryAttribute extends BaseModel {
	protected $table = 'product_category_attribute';
	const UPDATED_AT = null;
	const CREATED_AT = null;

	public function setValueAttribute($value) {
		$this->setJsonValue('value', $value);
	}

	public function getCategoryAttribute() {
		return $this->belongsTo(CategoryAttribute::class, 'category_attribute_id', 'id')
			->where('status', EStatus::ACTIVE);
	}
}