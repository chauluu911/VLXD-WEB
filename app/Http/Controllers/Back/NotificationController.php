<?php

namespace App\Http\Controllers\Back;
use App\Constant\SessionKey;
use App\Enums\ELanguage;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use App\Http\Requests\Notification\SaveNotificationScheduleRequest;
use App\Models\NotificationSchedule;
use App\Services\NotificationScheduleService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\NotificationService;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EApprovalStatus;
use App\Enums\ENotificationScheduleTargetType;
use App\Enums\ENotificationType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NotificationController extends Controller {
	protected NotificationService $notificationService;
	protected UserService $userService;
	protected NotificationScheduleService $notificationScheduleService;

	public function __construct(NotificationService $notificationService,
								UserService $userService,
								NotificationScheduleService $notificationScheduleService) {
		$this->notificationService = $notificationService;
		$this->userService = $userService;
		$this->notificationScheduleService = $notificationScheduleService;
	}

	public function getForNotificationScheduleList() {
		$tz = session(SessionKey::TIMEZONE);
		$locale = app()->getLocale();
		$options = [
			'pageSize' => request('pageSize'),
			'distinct' => true,
			'status_in' => [
				EStatus::ACTIVE,
				EStatus::WAITING,
			]
		];

		$acceptFields = ['q', 'targetType', 'scheduledAtGt', 'scheduledAtLt', 'status'];
		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
				continue;
			}

			$fieldValue = request("filter.$field");
			switch ($field) {
				case 'scheduledAtGt':
				case 'scheduledAtLt':
					try {
						$date = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, "$fieldValue $tz");
						$options[Str::snake($field)] = $date;
					} catch (\Exception $e) {}
					continue 2;
			}
			$options[Str::snake($field)] = $fieldValue;
		}

		$scheduleList = $this->notificationScheduleService->getByOptions($options);
		$tmp = $scheduleList->map(function(NotificationSchedule $schedule) use ($locale) {
			return [
				'id' => $schedule->id,
				'title' => $schedule->title,
				'content' => $schedule->content,
				'scheduled_at' => get_display_date_for_ajax($schedule->schedule_at),
				'target_type' => $schedule->target_type,
				'targetTypeStr' => ENotificationScheduleTargetType::valueToLocalizedName($schedule->target_type),
				'status' => $schedule->status,
				'statusStr' => $schedule->status === EStatus::ACTIVE
					? __('common/constant.notification_status.sent')
					: EStatus::valueToLocalizedName($schedule->status),
				'approvalStatus' => $schedule->approval_status,
				'approvalStatusStr' => EApprovalStatus::valueToLocalizedName($schedule->approval_status),
			];
		});
		$scheduleList->setCollection($tmp);

		$sentCount = $this->notificationScheduleService->getByOptions([
			'status' => EStatus::ACTIVE,
			'distinct' => true,
			'count' => true,
		]);

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => [
				'scheduleList' => $scheduleList,
				'sentCount' => $sentCount,
			],
		]);
	}

	public function getNotificationScheduleInfo(NotificationSchedule $notificationSchedule) {
		$result = [
			'multiLanguage' => false,
			'titleVi' => $notificationSchedule->title,
			'contentVi' => $notificationSchedule->content,
			'targetType' => $notificationSchedule->target_type,
			'targetList' => [],
			'scheduled_at' => $notificationSchedule->schedule_at,
			'status' => $notificationSchedule->status,
		];
		$result['multi_language'] = $result['titleVi']
			|| $result['contentVi'];
		if (!$result['multi_language']) {
			$result['titleFr'] = $result['titleVi'] = null;
			$result['contentFr'] = $result['contentVi'] = null;
		}

		if ($result['targetType'] === ENotificationScheduleTargetType::SPECIFIC_CUSTOMER) {
			foreach ($notificationSchedule->targets as $target) {
				$user = $target->user;
				$result['targetList'][] = [
					'code' => $user->code,
					'name' => $user->name,
                    'phone' => $user->phone,
                    'id' => $user->id,
				];
			}
		}

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $result,
		]);
	}

	public function saveNotificationSchedule(SaveNotificationScheduleRequest $request) {
		$request->validated();

		$sendDate = request('date');
		$sendTime = request('time');
		$tz = session(SessionKey::TIMEZONE);
		$scheduleAt = Carbon::createFromFormat('d/m/Y H:i:s e', "$sendDate $sendTime $tz");
		if ($scheduleAt->lt(Carbon::now()->addMinutes(5))) {
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('back/notification_schedule.errors.schedule_at_after_now'),
			]);
		}

		$data = [
			'status' => EStatus::PENDING,
			'approval_status' => EApprovalStatus::APPROVED,
			'type' => ENotificationType::SYSTEM,
			'target_type' => (int)request('targetType'),
			'title' => request('titleVi'),
			'content' => request('contentVi'),
			'scheduled_at' => $scheduleAt,
			'translations' => [
				ELanguage::VI => [
					'title' => request('titleVi'),
					'content' => request('contentVi'),
				],
			],
			'targetList' => request('targetList'),
		];
		if (request('id')) {
			$data['id'] = request('id');
		}

		$result = $this->notificationScheduleService->saveNotificationSchedule($data, auth()->id());
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => __('common/common.save-data-success2'),
		]);
	}

	public function deleteNotificationSchedule() {
		$result = $this->notificationScheduleService->deleteNotificationSchedule((int)request('id'), auth()->id());
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => __('common/common.delete-data-success', [
				'objectName' => __('back/notification_schedule.object_name'),
			])
		]);
	}

	public function approveNotify() {
		$data = request()->all();
		$result = $this->notificationScheduleService->approveNotify($data, now(), auth()->id());
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => 'Duyệt thành công',
		]);
	}
}
