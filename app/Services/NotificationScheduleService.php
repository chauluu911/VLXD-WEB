<?php

namespace App\Services;
use App\Constant\CacheKey;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\ELanguage;
use App\Helpers\StringUtility;
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
use App\Enums\EApprovalStatus;
use App\Jobs\NotifyUserJob;

class NotificationScheduleService {

	protected NotificationScheduleRepository $notificationScheduleRepository;
	protected NotificationScheduleTargetRepository $notificationScheduleTargetRepository;
	protected UserRepository $userRepository;
	protected UserDeviceRepository $userDeviceRepository;

	public function __construct(NotificationScheduleRepository $notificationScheduleRepository,
                                NotificationScheduleTargetRepository $notificationScheduleTargetRepository,
                                UserRepository $userRepository,
								UserDeviceRepository $userDeviceRepository) {
		$this->notificationScheduleRepository = $notificationScheduleRepository;
		$this->notificationScheduleTargetRepository = $notificationScheduleTargetRepository;
		$this->userRepository = $userRepository;
		$this->userDeviceRepository = $userDeviceRepository;
	}

	/**
	 * @param int $id
	 * @return NotificationSchedule
	 */
	public function getById(int $id) {
		return $this->notificationScheduleRepository->getById($id);
	}

	public function getByOptions(array $options) {
		return $this->notificationScheduleRepository->getByOptions($options);
	}

	public function saveNotificationSchedule(array $data, int $currentUserId) {
		return DB::transaction(function() use ($data, $currentUserId) {
			//region save notification_schedule
			if (Arr::get($data, 'id')) {
				$schedule = $this->getById((int)Arr::get($data, 'id'));
				if (empty($schedule)) {
					return [
						'error' => EErrorCode::ERROR,
						'msg' => __('common/error.entity-not-found', [
							'entity' => __('back/notification_schedule.object_name')
						]),
					];
				}
				$schedule->updated_by = $currentUserId;
			} else {
				$schedule = new NotificationSchedule();
				$schedule->created_by = $currentUserId;
			}
			$fields = ['status', 'title', 'content', 'type', 'target_type', 'meta', 'approval_status'];
			foreach ($fields as $field) {
				$schedule->{$field} = Arr::get($data, $field);
			}
			$schedule->schedule_at = $data['scheduled_at']->copy()->timezone(config('app.timezone'))->format(EDateFormat::MODEL_DATE_FORMAT);
			$schedule->content_search = StringUtility::convertViToEn(StringUtility::html2text($schedule->content));
			$schedule->save();
			//endregion

			//region save notification_schedule_target
			$newTargetList = (array)Arr::get($data, 'targetList', []);
			foreach ($schedule->targets as $target) {
				$user = $target->user;
				if (in_array($user->code, $newTargetList)) {
					$newTargetList = array_diff($newTargetList, [$user->code]);
				} else {
					$target->delete();
				}
			}
			if ($schedule->target_type === ENotificationScheduleTargetType::SPECIFIC_CUSTOMER) {
				foreach ($newTargetList as $userCode) {
					$user = $this->userRepository->getByCode($userCode);
					if (isset($user)) {
						$target = new NotificationScheduleTarget();
						$target->notification_schedule_id = $schedule->id;
						$target->customer_id = $user->id;
						$target->created_by = $currentUserId;
						$target->save();
					}
				}
			}
			//endregion

			return [
				'error' => EErrorCode::NO_ERROR,
				'notificationSchedule' => $schedule,
			];
		});
	}

	public function deleteNotificationSchedule(int $notificationScheduleId, int $currentUserId) {
		$notificationSchedule = $this->getById($notificationScheduleId);
		if (empty($notificationSchedule)) {
			return [
				'error' => EErrorCode::ERROR,
				'msg' => __('common/error.entity-not-found', [
					'entity' => __('back/notification_schedule.object_name'),
				]),
			];
		}
		$notificationSchedule->deleted_by = $currentUserId;
		$notificationSchedule->deleted_at = Carbon::now();
		$notificationSchedule->status = EStatus::DELETED;
		$notificationSchedule->save();
		return [
			'error' => EErrorCode::NO_ERROR,
			'notificationSchedule' => $notificationSchedule
		];
	}

	/**
	 * @param array $data token => result
	 * @param Carbon $sentTime
	 */
	private function updateGCMSendResult(array $data, Carbon $sentTime) {
		$maxSendingTryTime = (int)Cache::get(CacheKey::NOTIFICATION_MAX_SENDING_TRY, 10);
		foreach ($data as $token => $result) {
			$userDevice = $this->userDeviceRepository->getByDeviceToken($token);
			if (empty($userDevice) || $userDevice->status == EStatus::DELETED) {
				continue;
			}

			$userDevice->last_notification_sent_at = $sentTime;

			if (Arr::has($result, 'error')) {
				$userDevice->no_times_sending_notificaiton_fail = ($userDevice->no_times_sending_notificaiton_fail ?? 0) + 1;

				$error = Arr::get($result, 'error');
				$userDevice->fail_reason_in_last_try = is_string($error) || is_numeric($error) ? $error : json_encode($error);
			} elseif (Arr::has($result, 'message_id')) {
				$userDevice->no_times_sending_notificaiton_fail = 0;
				$userDevice->fail_reason_in_last_try = null;
			}

			if ($userDevice->no_times_sending_notificaiton_fail >= $maxSendingTryTime) {
				$userDevice->status = EStatus::DELETED;
			}

			$userDevice->save();
		}
	}

    public function markNotificationScheduleAsSent($notifyId) {
    	try {
    		$notification = $this->notificationScheduleRepository->getById($notifyId);
			$notification->status = EStatus::ACTIVE;
			$notification->updated_at = Carbon::now();
			$notification->updated_by = auth()->id();
			$notification->save();
			return $notification;
    	} catch (\Exception $e) {
    		logger()->error('Failed to update status notification', ['e' => $e]);
    		return [
    			'error' => EErrorCode::ERROR,
    			'message' => __('common.there_was_an_error_in_the_processing'),
    		];
    	}
    }

    public function getWaitingToSendNotificationSchedule() {
        return $this->notificationScheduleRepository->getWaitingToSendNotificationSchedule();
    }

    public function approveNotify($data, $now, $currentUserId) {
    	return DB::transaction(function() use ($data, $now, $currentUserId) {
    		foreach ($data as $key) {
    			foreach ($key as $val) {
    				$schedule = $this->getById($val['id']);
    				$schedule->approval_status = EApprovalStatus::APPROVED;
    				$schedule->approved_at = $now;
    				$schedule->approved_by = $currentUserId;
    				$schedule->save();

    				NotifyUserJob::dispatch([$schedule->created_by], [
		                'type' => ENotificationType::APPROVED_NOTIFICATION_CREATED_BY_SHOP,
		                'title' => [
		                    ELanguage::VI => 'Thông báo',
		                ],
		                'content' => [
		                    ELanguage::VI => 'Thông báo ' . "'" . $schedule->title . "'". ' của bạn đã được duyệt',
		                ],
		            ])->onQueue('pushToDevice');
    			}
    		}
			return [
				'error' => EErrorCode::NO_ERROR,
			];
		});
    }
}
