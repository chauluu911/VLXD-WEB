<?php

namespace App\Repositories;

use App\Enums\EDateFormat;
use App\Enums\ELanguage;
use Illuminate\Database\Query\JoinClause;
use App\Enums\EStatus;
use App\Enums\EApprovalStatus;
use App\Models\NotificationSchedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class NotificationScheduleRepository extends BaseRepository {
	public function __construct(NotificationSchedule $notificationSchedule) {
		$this->model = $notificationSchedule;
	}

	public function getByOptions(array $options) {
		$result = $this->model
			->from('notification_schedule as ns')
			->orderBy('ns.id', 'desc');
		if (Arr::get($options, 'distinct')) {
			$result->selectRaw('distinct on (ns.id) ns.*');
			unset($options['distinct']);
		} else {
			$result->selectRaw('ns.*');
		}

		//region filter
		if (Arr::hasAny($options, ['status', 'not_status'])) {
			$result->where('ns.status', EStatus::ACTIVE);
		}
		foreach ($options as $key => $val) {
			if (!isset($val)) {
				continue;
			}
			switch ($key) {
				case 'status':
					$result->where('ns.status', $val);
					break;
				case 'target_type':
					$result->where("ns.$key", $val);
					break;
				case 'not_status':
					$result->where('ns.' . Str::replaceFirst('not_', '', $key), $val);
					break;
				case 'status_in':
					$result->whereIn('ns.status', $val);
					break;
				case 'scheduled_at_lt':
					$result->where('schedule_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
					break;
				case 'scheduled_at_gt':
					$result->where('schedule_at', '>', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
					break;
				case 'q':
					$result->where(function($q) use ($val) {
						$q->orWhere('ns.title', 'ilike', "%$val%");
					});
					break;
			}
		}
		//endregion
		//region select
		//endregion

		return parent::getByOption($options, $result);
	}

	public function getNotification($content, $fromDate, $toDate, $quantity) {
		$result = $this->model
			->from('notification_schedule as ns')
			->select('ns.id', 'ns.content', 'ns.type', 'ns.target_type', 'ns.schedule_at', 'ns.status')
			->where('ns.status', '<>', EStatus::DELETED);
		if ($content != null && $content != '') {
			$result->where(function($where) use ($content) {
				$where->whereRaw('lower(ns.content) like ? ', ['%' . trim(mb_strtolower($content, 'UTF-8')) . '%']);
			});
		}
		if (!empty($fromDate)) {
			$result->whereDate('ns.schedule_at', '>=', $fromDate);
		}
		if (!empty($toDate)) {
			$result->whereDate('ns.schedule_at', '<=', $toDate);
		}
		$result = $result->orderBy('ns.id', 'desc')->paginate($quantity);
		return $result;
	}

    public function getWaitingToSendNotificationSchedule() {
	    $t = now()->floorMinute();
	    return $this->model
	    	->where('approval_status', EApprovalStatus::APPROVED)
			->where('status', EStatus::WAITING)
            ->where('schedule_at', '>=', $t->copy()->addHour(-12))
            ->where('schedule_at', '<', $t->copy()->addMinutes(5))
            ->orderBy('schedule_at', 'desc')
            ->get();
    }
}
