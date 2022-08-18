<?php

namespace App\Jobs;

use App\Constant\Firebase\FirebaseTopic;
use App\Enums\ELanguage;
use App\Enums\ENotificationType;
use App\Services\NotificationService;
use App\Services\SearchHistoryService;
use App\Services\PostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SendWhenHaveSuitableBusiness implements ShouldQueue {
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
     * @param NotificationService $notificationService
     * @return void
     */
    public function handle(NotificationService $notificationService, SearchHistoryService $searchHistoryService, PostService $postService) {
        DB::transaction(function() use ($notificationService, $searchHistoryService, $postService) {
            $searHistory = $searchHistoryService->getByOptions([
                'is_save' => false,
                'first' => true,
            ]);
            if (!empty($searHistory)) {
                $data = (array)$searHistory->content;
                if (!empty($data['country_id'])) {
                    $data['country_id'] = $data['country_id']->id;
                }
                $data['first'] = true;
                $post = $postService->getByOptions($data);
                if (!empty($post)) {
                    $saveData = [
                        'title' => __('front/notification.post_available.title', [], ELanguage::VI),
                        'content' => __('front/notification.post_available.content', [], ELanguage::VI),
                        'type' => ENotificationType::POST_AVAILABLE,
                        'meta' => (array)$searHistory->content,
                        'translations' => [
                            ELanguage::VI => [
                                'title' =>  __('front/notification.post_available.title', [], ELanguage::VI),
                                'content' => __('front/notification.post_available.content', [], ELanguage::VI),
                            ],
                        ],
                        'targetList' => [$searHistory->user_id],
                    ];
                    $searHistory->is_save = true;
                    $searHistory->save();
                    $notificationService->saveNotification($saveData);
                }
            }
        });
    }
}
