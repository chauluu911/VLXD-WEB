<?php

namespace App\Services;

use App\Enums\ErrorCode;
use App\Enums\EStatus;
use App\Enums\EDateFormat;
use App\Helper\Utility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserActivitiesLogRepository;

class UserActivitiesLogService {
	protected $userActivitiesLogRepository;

	public function __construct(UserActivitiesLogRepository $userActivitiesLogRepository) {
		$this->userActivitiesLogRepository = $userActivitiesLogRepository;
	}

	public function getLastTime($userId) {
		return $this->userActivitiesLogRepository->getLastTime($userId);
	}
}