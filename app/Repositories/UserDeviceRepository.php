<?php

namespace App\Repositories;

use App\Models\UserDevice;

class UserDeviceRepository extends BaseRepository {

    public function __construct(UserDevice $userDevice) {
        $this->model = $userDevice;
    }

    public function getByDeviceToken($token) {
    	return $this->model
			->where('device_token', $token)
			->first();
	}
}
