<?php

namespace App\Jobs;

use App\Enums\ELanguage;
use App\Services\Firebase\FirebaseService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Enums\ENotificationType;

class NotifyUserJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $option;
    /**
     * @var array
     */
    private $userIdList;

    /**
     * Create a new job instance.
     *
     * @param array $userIdList
     * @param $option
     */
    public function __construct(array $userIdList, $option) {
        $this->userIdList = $userIdList;
        $this->option = $option;
    }

	/**
	 * Execute the job.
	 *
	 * @param NotificationService $notificationService
	 * @return void
	 */
    public function handle(NotificationService $notificationService) {
        DB::transaction(function() use ($notificationService) {
			$title = Arr::get($this->option, 'title');
			$content = Arr::get($this->option, 'content');

			$saveData = [
				'title' => $title[ELanguage::VI],
				'content' => $content[ELanguage::VI],
				'type' => Arr::get($this->option, 'type'),
				'meta' => Arr::get($this->option, 'meta'),
				'translations' => [
					ELanguage::VI => [
						'title' => $title[ELanguage::VI],
						'content' => $content[ELanguage::VI],
					],
				],
				'targetList' => $this->userIdList,
			];
			if (Arr::get($this->option, 'type') != ENotificationType::MESSAGE_NOTIFICATION) {
				$notificationService->saveNotification($saveData);
			}
			for ($i = 0; $i < count($this->userIdList); $i++) { 
				FirebaseService::sendNotificationToOneUser($this->userIdList[$i], [
					'title' => $title[ELanguage::VI],
					'content' => $content[ELanguage::VI],
					'type' => Arr::get($this->option, 'type'),
					'data' => Arr::get($this->option, 'data'),
				]);
			}
		});
    }
}
