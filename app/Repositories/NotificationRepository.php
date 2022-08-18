<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Enums\EStatus;

class NotificationRepository extends BaseRepository {

	public function __construct(Notification $notification) {
		$this->model = $notification;
	}

	public function getNotificationForUser($userId, $pageSize) {
		return $this->model
			->from('notification as n')
			->join('notification_target as nt', 'nt.notification_id', 'n.id')
			->where('n.status', EStatus::ACTIVE)
			->where('user_id', $userId)
            ->orderBy('n.created_at', 'desc')
            ->orderBy('n.id', 'desc')
            ->limit($pageSize)
            ->select('n.*', 'nt.is_seen', 'meta')
            ->get();
	}

	public function getNotificationNumberNotSeen($userId) {
		return $this->model
			->from('notification as n')
			->join('notification_target as nt', 'nt.notification_id', 'n.id')
			->where('user_id', $userId)
			->where('n.status', EStatus::ACTIVE)
			->where('is_seen', false)
			->count();
	}

	public function getNumberOfNotification($userId) {
		return $this->model
			->from('notification as n')
			->join('notification_target as nt', 'nt.notification_id', 'n.id')
			->where('user_id', $userId)
			->count();
	}

	public function getNotificationNotSeen($userId) {
		return $this->model
			->from('notification as n')
			->join('notification_target as nt', 'nt.notification_id', 'n.id')
			->where('user_id', $userId)
			->where('is_seen', false)
			->get();
	}
}
