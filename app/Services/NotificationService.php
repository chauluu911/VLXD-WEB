<?php

namespace App\Services;
use App\Constant\CacheKey;
use App\Enums\EErrorCode;
use App\Enums\ELanguage;
use App\Models\NotificationTarget;
use App\Repositories\NotificationScheduleRepository;
use App\Repositories\NotificationScheduleTargetRepository;
use App\Repositories\UserDeviceRepository;
use App\Services\Firebase\FirebaseService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Repositories\NotificationRepository;
use App\Enums\EStatus;
use Illuminate\Support\Carbon;
use App\Models\NotificationSchedule;
use App\Enums\ENotificationScheduleTargetType;
use App\Models\Notification;
use App\Repositories\UserRepository;
use App\Models\NotificationScheduleTarget;
use App\Enums\ENotificationType;
use Illuminate\Support\Str;

class NotificationService {

	protected NotificationRepository $notificationRepository;
	protected NotificationScheduleRepository $notificationScheduleRepository;
	protected NotificationScheduleTargetRepository $notificationScheduleTargetRepository;
	protected UserRepository $userRepository;
	protected UserDeviceRepository $userDeviceRepository;

	public function __construct(NotificationRepository $notificationRepository,
                                NotificationScheduleRepository $notificationScheduleRepository,
                                NotificationScheduleTargetRepository $notificationScheduleTargetRepository,
                                UserRepository $userRepository,
								UserDeviceRepository $userDeviceRepository) {
		$this->notificationRepository = $notificationRepository;
		$this->notificationScheduleRepository = $notificationScheduleRepository;
		$this->notificationScheduleTargetRepository = $notificationScheduleTargetRepository;
		$this->userRepository = $userRepository;
		$this->userDeviceRepository = $userDeviceRepository;
	}

	public function getNotification($content, $fromDate, $toDate, $quantity = 15) {
		return $this->notificationScheduleRepository->getNotification($content, $fromDate, $toDate, $quantity);
	}

	public function getNotificationForUser($userId, $pageSize) {
		return $this->notificationRepository->getNotificationForUser($userId, $pageSize);
	}

	public function getNotificationNumberNotSeen($userId) {
		return $this->notificationRepository->getNotificationNumberNotSeen($userId);
	}

	public function getById($id) {
		return $this->notificationRepository->getById($id);
	}

	public function seenNotification($notifications, $userId) {
		try {
			return DB::transaction(function() use ($notifications, $userId) {
				if (!is_array($notifications)) {
					$notifications = ['id' => $notifications];
				}
				foreach ($notifications as $index => $item) {
					$notification = $this->notificationRepository->getById($item);
					if (!empty($notification)) {
						$notificationTarget = $notification->notificationTarget->where('user_id', $userId)->first();
						$notificationTarget->is_seen = true;
						$notificationTarget->save();
						return [
							'error' => EErrorCode::NO_ERROR,
						];
					}
				}
			});
		} catch (\Exception $e) {
			logger()->error('Fail to seen notification', ['e' => $e]);
			return [
				'error' => EErrorCode::ERROR,
				'message' => __('common.there_was_an_error_in_the_processing'),
			];
		}
	}

	//mark all read notification
	public function markAllNotification($userId) {
		try {
			return DB::transaction(function() use ($userId) {
				$listNotification = $this->notificationRepository->getNotificationNotSeen($userId);
				if(count($listNotification) > 0) {
					foreach ($listNotification as $key => $value) {
						$value->is_seen = true;
						$value->save();
					}
				}
				return [
					'error' => EErrorCode::NO_ERROR,
				];
			});
		} catch (\Exception $e) {
			logger()->error('Fail to read all notification', ['e' => $e]);
			return [
				'error' => EErrorCode::ERROR,
				'message' => __('common.there_was_an_error_in_the_processing'),
			];
		}
	}

    public function saveNotification(array $data, int $currentUserId = null) {
    	logger()->info(__METHOD__ . ": dataNotify", ['notificationScheduleId' => $data]);
		return DB::transaction(function() use ($data, $currentUserId) {
			$notification = new Notification();
			$notification->title = Arr::get($data, 'title');
			$notification->content = Arr::get($data, 'content');
			$notification->type = Arr::get($data, 'type');
			$notification->status = EStatus::ACTIVE;
			$notification->created_by = $currentUserId;
			$notification->meta = Arr::get($data, 'meta');
			$notification->save();

			$targetList = Arr::get($data, 'targetList', []);
			foreach ($targetList as $userId) {
				$target = new NotificationTarget();
				$target->notification_id = $notification->id;
				$target->user_id = $userId;
				$target->is_seen = false;
				$target->created_by = $currentUserId;
				$target->save();
			}

			return [
				'error' => EErrorCode::NO_ERROR,
				'notification' => $notification,
			];
		});
	}

	public function getNumberOfNotification($userId) {
		return $this->notificationRepository->getNumberOfNotification($userId);
	}
}
