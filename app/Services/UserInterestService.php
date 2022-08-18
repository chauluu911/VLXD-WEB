<?php

namespace App\Services;

use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Repositories\PostRepository;
use App\Repositories\UserInterestRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Post;


class UserInterestService {
    private UserInterestRepository $userInterestRepository;

    public function __construct(UserInterestRepository $userInterestRepository) {
        $this->userInterestRepository = $userInterestRepository;
    }

    /**
     * @param $userId
     * @return \App\User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getById($shopId) {
        return $this->userInterestRepository->getById($shopId);
    }

    public function getByOptions(array $options) {
        return $this->userInterestRepository->getByOptions($options);
    }

    public function didInterestExist($userId, $tableName, $tableId) {
        return $this->userInterestRepository->didInterestExist($userId,$tableName,$tableId);
    }

}
