<?php

namespace App\Console\Commands;

use App\Constant\CacheKey;
use App\Constant\ConfigKey;
use App\Enums\Affiliate\EAffiliateTrackingActionType;
use App\Enums\EExamQuestionFormal;
use App\Enums\ELanguage;
use App\Enums\EMarkStatus;
use App\Enums\EPaymentStatus;
use App\Enums\EStatus;
use App\Enums\ETeacherAvailableScheduleType;
use App\Enums\EVideoCallProvider;
use App\Enums\EWalletTransactionLogDetailType;
use App\Enums\EWalletTransactionLogType;
use App\Enums\EWalletType;
use App\Events\Affiliate\MeetAffiliateTrackingCode;
use App\Helpers\ConfigHelper;
use App\Models\Affiliate\UserAffiliateCommission;
use App\Models\ExamQuestionSectionAnswer;
use App\Models\ExamQuestionUserAnswer;
use App\Models\Coupon;
use App\Models\ExamRegistration;
use App\Models\MarkWritingSectionRegistration;
use App\Models\SpeakingScheduleTeacher;
use App\Models\TeacherAvailableSchedule;
use App\Models\TeacherAvailableScheduleTimeDetail;
use App\Models\UserBuyExamPackage;
use App\Services\ExamPackageService;
use App\Services\NotificationService;
use App\Services\UserService;
use App\Services\WalletService;
use App\Services\WalletTransactionLogService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Twilio\Rest\Client;

class HackCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hack:command
                                {--reset-cache : Reset app_config cache}
                                
                                {--notify : Send test notification}
                                {--phone=* : Create test for particular user with phone number}
                                {--user=* : Custom user id}
                                {--topic=* : Custom firebase topic}
                                {--message= : Custom message in vietnamese}
                                {--message_en= : Custom message in english}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle() {
	    if ($this->option('reset-cache')) {
		    $this->resetCacheAppConfig();
	    } elseif ($this->option('notify')) {
			$this->sendNotification();
		}
    }

    private function resetCacheAppConfig() {
    	ConfigHelper::clear();
    }
    
    private function sendNotification() {
    	$userIdList = $this->option('user');
    	$userPhoneList = $this->option('phone');
    	$topicList = $this->option('topic');

    	if (!empty($userIdList) || !empty($userPhoneList)) {
    		$userList = \App\User::whereIn('id', $userIdList)->orWhereIn('phone', $userPhoneList)->get();
    		foreach ($userList as $user) {
    			NotificationService::sendNotificationToOneUser($user->id, [
    				'title' => 'Kiểm tra',
					'content' => $this->option('message') ?? 'Tin nhắn test',
					'type' => 99,
				]);
			}
		} elseif (!empty($topicList)) {
    		foreach ($topicList as $topic) {
				NotificationService::sendNotificationToTopic($topic, [
					'title' => 'Kiểm tra',
					'content' => $this->option('message') ?? 'Tin nhắn test',
					'type' => 99,
				]);
			}
		}
	}
}
