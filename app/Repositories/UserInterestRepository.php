<?php

namespace App\Repositories;

use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use App\Models\UserInterest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Enums\ECustomerType;
use App\Enums\EUserType;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;
use App\Models\SmsLog;

class UserInterestRepository extends BaseRepository {

    public function __construct(UserInterest $userInterest) {
        $this->model = $userInterest;
    }

    public function didInterestExist($userId, $tableName, $tableId) {
        return $this->model
            ->where('user_id', $userId)
            ->where('table_name', $tableName)
            ->where('table_id', $tableId)
            ->exists();
    }

    public function deleteInterest($userId,$tableName, $tableId) {
        return $this->model
            ->where('user_id', $userId)
            ->where('table_name', $tableName)
            ->where('table_id', $tableId)
            ->delete();
    }

}
