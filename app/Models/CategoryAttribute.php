<?php
namespace App\Models;

use App\Enums\EStatus;

class CategoryAttribute extends BaseModel {
	protected $table = 'category_attribute';
	const UPDATED_AT = null;
	const CREATED_AT = null;

	public function setValueAttribute($value) {
		$this->setJsonValue('value', $value);
	}
}
