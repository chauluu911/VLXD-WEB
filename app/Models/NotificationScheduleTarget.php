<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class NotificationScheduleTarget extends BaseModel {
	protected $table = 'notification_schedule_target';
	const UPDATED_AT = null;

	public function user() {
		return $this->hasOne(User::class, 'id', 'customer_id');
	}
}
