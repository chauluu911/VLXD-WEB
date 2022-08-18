<?php

namespace App\Repositories;
use App\Models\UserRolePermission;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\EStatus;

class UserRolePermissionRepository extends BaseRepository {
	public function __construct(UserRolePermission $model) {
		$this->model = $model;
	}

	public function getByUserId($userId) {
		$result = $this->model
			->from('user_role_permission')
			->where('status', EStatus::ACTIVE)
			->where('user_id', $userId)
			->get();
		return $result;
	}

    public function getByOptions(array $options) {
		$result = $this->model
			->from('user_role_permission');

		foreach ($options as $key => $val) {
			switch ($key) {
				case 'status':
					$result->where('status', $val);
					break;
				case 'user_id':
					$result->where('user_id', $val);
					break;
			}
		}
		return parent::getByOption($options, $result);
	}

	public function isUserHasPermissionGroup(int $userId, int $permissionGroupId) {
        return $this->model->where('status', EStatus::ACTIVE)
            ->where('user_id', $userId)
            ->where('permission_group_id', $permissionGroupId)
            ->exists();
	}
}
