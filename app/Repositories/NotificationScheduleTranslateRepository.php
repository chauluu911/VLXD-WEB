<?php

namespace App\Repositories;

use App\Constant\ConfigKey;
use App\Helpers\ConfigHelper;
use App\Models\NotificationScheduleTranslate;

class NotificationScheduleTranslateRepository extends BaseRepository {

    public function __construct(NotificationScheduleTranslate $model) {
        $this->model = $model;
    }

    public function get($languageId, $notificationScheduleId) {
    	return $this->model
            ->where('language_id', $languageId)
            ->where('notification_schedule_id', $notificationScheduleId)
            ->first();
    }

    public function updateByLanguageAndSchedule(int $languageId, int $notificationScheduleId, $data) {
        NotificationScheduleTranslate::where('language_id', $languageId)
            ->where('notification_schedule_id', $notificationScheduleId)
            ->update($data);
    }
}
