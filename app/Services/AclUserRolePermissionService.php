<?php

namespace App\Services;

use App\Repositories\UserRolePermissionRepository;
use Illuminate\Database\Eloquent\Collection;

class AclUserRolePermissionService {

	private $userRolePermissionRepository;

    public function __construct(UserRolePermissionRepository $userRolePermissionRepository) {
        $this->userRolePermissionRepository = $userRolePermissionRepository;
    }

    public function getByOptions($options) {
	    return $this->userRolePermissionRepository->getByOptions($options);
    }
}
