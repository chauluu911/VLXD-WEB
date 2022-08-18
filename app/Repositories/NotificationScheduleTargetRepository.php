<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Enums\EStatus;
use App\Models\NotificationScheduleTarget;

class NotificationScheduleTargetRepository extends BaseRepository {
	public function __construct(NotificationScheduleTarget $notificationScheduleTarget) {
		$this->model = $notificationScheduleTarget;
	}


	public function getUserInNotificationScheduleTarget($notificationId) {
		return $this->model
			->select('users.name', 'users.phone', 'users.registered_as_parent', 'users.registered_as_teacher')
			->where('notification_schedule_target.notification_schedule_id', $notificationId)
			->join('users', 'users.id', '=', 'notification_schedule_target.user_id')
			->get();
	}

	public function getNotificationScheduleTargets(int $notificationScheduleId) {
	    return $this->model
			->from('notification_schedule_target as nst')
			->where('nst.notification_schedule_id', $notificationScheduleId)
			->whereExists(function ($query) {
				$query->select(DB::raw(1))
					->from('users as u')
					->whereRaw('nst.user_id = u.id')
					->where('u.status', EStatus::ACTIVE);
			})
			->cursor();
    }
}
