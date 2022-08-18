<?php

namespace App\Jobs;

use App\Constant\Firebase\FirebaseTopic;
use App\Enums\ELanguage;
use App\Enums\EStatus;
use App\Enums\EUserType;
use App\Services\Firebase\FirebaseService;
use App\Services\NotificationService;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NotifyTopicJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $option;
    /**
     * @var int
     */
    private $topic;

    /**
     * Create a new job instance.
     *
     * @param string $topic
     * @param $option
     */
    public function __construct(string $topic, $option) {
        $this->topic = $topic;
        $this->option = $option;
    }

	/**
	 * Execute the job.
	 *
	 * @param NotificationService $notificationService
	 * @param UserService $userService
	 * @return void
	 */
    public function handle(NotificationService $notificationService,
						   UserService $userService) {
        DB::transaction(function() use ($notificationService, $userService) {
        	$userFilter = [
				'status' => EStatus::ACTIVE,
				'type' => EUserType::NORMAL_USER,
				'not_id' => Arr::get($this->option, 'notUserId', null),
			];
        	switch ($this->topic) {
				case FirebaseTopic::ALL:
					break;
				default:
					return;
			}

			$userList = $userService->getByOptions($userFilter);
			if (empty($userList)) {
				return;
			}

			$title = Arr::get($this->option, 'title');
			$content = Arr::get($this->option, 'content');
			$saveData = [
				'title' => $title[ELanguage::VI],
				'content' => $content[ELanguage::VI],
				'type' => Arr::get($this->option, 'type'),
				'meta' => Arr::get($this->option, 'meta'),
				// 'translations' => [
				// 	ELanguage::VI => [
				// 		'title' => $title[ELanguage::VI],
				// 		'content' => $content[ELanguage::VI],
				// 	],
				// 	ELanguage::EN => [
				// 		'title' => $title[ELanguage::EN],
				// 		'content' => $content[ELanguage::EN],
				// 	]
				// ],
				'targetList' => $userList->map(function($user) {
					return $user->id;
				}),
			];
			$notificationService->saveNotification($saveData);

			FirebaseService::sendNotificationToTopic($this->topic, [
				'title' => $title[ELanguage::VI],
				'content' => $content[ELanguage::VI],
				'type' => Arr::get($this->option, 'type'),
			]);
		});
    }
}
