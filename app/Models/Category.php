<?php
namespace App\Models;

use App\Enums\EStatus;

class Category extends BaseModel {
	protected $table = 'category';
	const UPDATED_AT = null;
	const CREATED_AT = null;

	public function childCategories() {
		return $this->hasMany(Category::class, 'parent_category_id', 'id')
			->where('status', EStatus::ACTIVE)
			->orderBy('id', 'desc');
	}

	public function parentCategories() {
		return $this->hasMany(Category::class, 'id', 'parent_category_id');
	}

	public function allParentCategories() {
		return $this->parentCategories()->with('allParentCategories');
	}

	public function allAttribute() {
		return $this->hasMany(CategoryAttribute::class, 'category_id', 'id')
			->orderBy('id', 'asc')
			->where('status', EStatus::ACTIVE);
	}

	public function attributeEnableFilter() {
		return $this->hasMany(CategoryAttribute::class, 'category_id', 'id')
			->orderBy('id', 'asc')
			->where('enable_filter', true)
			->where('status', EStatus::ACTIVE);
	}
}
