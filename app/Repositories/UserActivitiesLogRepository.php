<?php

namespace App\Repositories;

use App\Enums\EStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserActivitiesLogRepository extends BaseRepository {

    public function getLastTime($userId) {
        $result = DB::table('user_activities_log')
            ->select('created_at')
            ->orderBy('created_at', 'desc')
            ->where('action_type', 3)
            ->where('user_id', $userId)
            ->first();
        return $result;
    }
}