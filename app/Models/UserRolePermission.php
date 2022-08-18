<?php

namespace App\Models;

use App\Enums\EAclObjectType;


class UserRolePermission extends BaseModel {
	protected $table = 'user_role_permission';
	const UPDATED_AT = null;

	public function permissionGroup() {
        return $this->belongsTo(AclObject::class, 'permission_group_id', 'id')
            ->where('object_type', EAclObjectType::PERMISSION_GROUP);
    }
}