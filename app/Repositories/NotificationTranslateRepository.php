<?php

namespace App\Repositories;

use App\Constant\ConfigKey;
use App\Helpers\ConfigHelper;
use App\Models\NotificationTranslate;

class NotificationTranslateRepository extends BaseRepository {

    public function __construct(NotificationTranslate $model) {
        $this->model = $model;
    }

    public function getNotificationTrans($languageId, $notificationId) {
        return $this->model
            ->where('language_id', $languageId)
            ->where('notification_id', $notificationId)
            ->first();
    }
}
