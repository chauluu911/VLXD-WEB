<?php
namespace App\Models;

class Notification extends BaseModel {
	protected $table = 'notification';
	const UPDATED_AT = null;

    public function notificationTarget() {
    	return $this->hasMany(NotificationTarget::class, 'notification_id', 'id');
    }
}
