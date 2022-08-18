<?php

namespace App\Models;

class AclObject extends BaseModel {
	protected $table = 'acl_object';

	public function getUriPathAttribute($value) {
        return explode(',', trim($value, '{}'));
    }
}