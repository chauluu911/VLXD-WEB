<?php

namespace App\Services\Sms;

use App\Constant\ConfigTableName;
use App\Models\Sms\SmsLog;
use App\Repositories\IssueReportRepository;
use App\Enums\EStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IssueReport;
use App\Enums\EErrorCode;

class SmsLogService {
    public function createSmsLog($data, $loggedInUserId) {
        return DB::transaction(function() use ($data, $loggedInUserId) {
        	$log = new SmsLog();
			$log->created_by = $loggedInUserId;
			$log->created_at = Arr::get($data, 'created_at', now());
			$log->to_phone_number = Arr::get($data, 'to_phone_number');
			$log->type = Arr::get($data, 'type');
			$log->content = Arr::get($data, 'content');
			$log->send_status = Arr::get($data, 'send_status');
			$log->tried_time = Arr::get($data, 'tried_time', 0);
			$log->error_messages = (array)Arr::get($data, 'error_messages');
			$log->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
}
