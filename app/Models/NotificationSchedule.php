<?php
namespace App\Models;


use App\Helpers\DateUtility;

class NotificationSchedule extends BaseModel {
	protected $table = 'notification_schedule';
	protected $translationClass = NotificationScheduleTranslate::class;

	public function getTypeAttribute($value) {
		return $this->getNumericValue($value);
	}

	public function getTargetTypeAttribute($value) {
		return $this->getNumericValue($value);
	}

	public function getScheduleAtAttribute($value) {
		return DateUtility::tryParsedDateFromFormat($value);
	}

	public function targets() {
		return $this->hasMany(NotificationScheduleTarget::class, 'notification_schedule_id', 'id');
	}
}
