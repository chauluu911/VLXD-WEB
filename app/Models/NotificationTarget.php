<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class NotificationTarget extends BaseModel {
	protected $table = 'notification_target';
	const UPDATED_AT = null;

	public function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
