<?php

namespace App\Jobs;

use App\Constant\Firebase\FirebaseTopic;
use App\Enums\ELanguage;
use App\Enums\ENotificationScheduleTargetType;
use App\Services\NotificationScheduleService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class FeedNotifyUserJobQueue implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Execute the job.
     *
     * @param NotificationScheduleService $notificationScheduleService
     * @return void
     */
    public function handle(NotificationScheduleService $notificationScheduleService) {
        $notificationSchedules = $notificationScheduleService->getWaitingToSendNotificationSchedule();
        foreach ($notificationSchedules as $notificationSchedule) {
            if ($notificationSchedule->schedule_at->gt(now())) {
                logger()->info("the schedule is not due yet", [
                    'id' => $notificationSchedule->id,
                    'scheduledAt' => $notificationSchedule->schedule_at
                ]);
                continue;
            }

            $notifyData = [
                'title' => [
                    ELanguage::VI => $notificationSchedule->title,
                ],
                'content' => [
                    ELanguage::VI => $notificationSchedule->content,
                ],
                'type' => $notificationSchedule->type,
                'meta' => [
                    'notificationScheduleId' => $notificationSchedule->id
                ],
                'notUserId' => $notificationSchedule->created_by
            ];

            // $translations = $notificationSchedule->translations;
            // if (!empty($translations)) {
            //     $engTranslation = $translations->filter(function ($item) {
            //         return $item->language_code === ELanguage::EN;
            //     });
            //     if (!empty($engTranslation)) {
            //         $notifyData['title'][ELanguage::EN] = $engTranslation->first()->title;
            //         $notifyData['content'][ELanguage::EN] = $engTranslation->first()->content;
            //     }
            // }

            switch ($notificationSchedule->target_type) {
                case ENotificationScheduleTargetType::ALL_CUSTOMER:
                    logger()->info(__METHOD__ . ": feeding jobs sending notification to all users", ['notificationScheduleId' => $notificationSchedule->id]);
					NotifyTopicJob::dispatch(FirebaseTopic::ALL, $notifyData)->onQueue('pushToAllDevice');
                    break;
                case ENotificationScheduleTargetType::SPECIFIC_CUSTOMER:
                    logger()->info(__METHOD__ . ": feeding jobs sending notification to specifically users", ['notificationScheduleId' => $notificationSchedule->id]);
                    $userIdList = $notificationSchedule->targets->map(function ($item) {
                    	return $item->user_id;
					});
					NotifyUserJob::dispatch($userIdList, $notifyData)->onQueue('pushToDevice');
                    break;
            }
            $notificationScheduleService->markNotificationScheduleAsSent($notificationSchedule->id);
        }
    }
}
