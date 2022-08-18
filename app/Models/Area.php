<?php
namespace App\Models;

use App\Enums\EAreaType;
use App\Enums\EStatus;

class Area extends BaseModel {
	protected $table = 'area';

	public function childAreas() {
		return $this->hasMany(Area::class, 'parent_area_id', 'id')
			->orderBy('name');
	}

	public function allChildAreas() {
		return $this->childAreas()->with('allChildAreas');
	}

	public function parentArea() {
		return $this->hasOne(Area::class, 'id', 'parent_area_id')
            ->where('type', '!=',EAreaType::STATE);
	}

	public function allParentAreas() {
		return $this->parentArea()->with('allParentAreas');
	}
}
