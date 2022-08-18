<?php

namespace App\Jobs;

use App\Enums\EErrorCode;
use App\Enums\ESendStatus;
use App\Enums\ESmsType;
use App\Enums\Sms\ESandbox;
use App\Enums\Sms\ESmsType as ESMSSmsType;
use App\Enums\EStatus;
use App\Services\Sms\SmsLogService;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\Response;


class SendSms implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

	private SmsLogService $smsLogService;
	private UserService $userService;

	private $phoneNumber;
	private $message;
	private $type;
	private $now;

    private $result;

    public function __construct($phoneNumber, $message, $type, $now, $retry = 3) {
    	$this->phoneNumber = $phoneNumber;
    	$this->message = $message;
    	$this->type = $type;
        $this->now = $now;
        $this->retry = $retry;

        $this->queue = 'smsNotification';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SmsLogService $smsLogService,
						   UserService $userService) {
    	$this->smsLogService = $smsLogService;
    	$this->userService = $userService;
        $this->sendSms($this->phoneNumber, $this->message, $this->type, $this->now);
    }

    public function sendSms($phoneNumber, $message, $type, $now) {
        try {
        	// get app_config
            $apiKey = config('app.vihat_sms.api_key');
            $secretKey = config('app.vihat_sms.secret_key');
            $brandName = config('app.vihat_sms.brand_name');

            // request url
			$requestUrl = 'http://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json/';

            // init request client
			$client = new \GuzzleHttp\Client();

			// request data
			$requestData = [
				// (Bắt buộc) Số điện thoại người nhận
				'Phone' => $phoneNumber,

				// (Bắt buộc) Nội dung gửi đến người nhận
				'Content' => $message,

				// (Bắt buộc) Thông tin APIKey được cấp khi đăng ký trong phần Quản
				// lý API sau khi đăng nhập
				'ApiKey' => $apiKey,

				// (Bắt buộc) Thông tin SecretKey được cấp khi đăng ký trong phần
				// Quản lý API sau khi đăng nhập
				'SecretKey' => $secretKey,

				// (Bắt buộc) Là loại tin nhắn muốn sử dụng, mỗi loại sẽ có đầu số hiển
				// thị khác nhau và chi phí khác nhau.
				// Vui long liên hệ hotline 0902435340 để được tư vấn cụ
				// thể hơn
				'SmsType' => ESMSSmsType::BRANDNAME_CSKH,

				// (Bắt buộc khi SmsType = BRANDNAME_CSKH)
				// Tên Brandname (tên công ty hay tổ chức khi gửi tin
				// sẽ hiển thị trên tin nhắn đó).
				// Chú ý: sẽ phải đăng ký trước khi sử dụng.
				'Brandname' => $brandName,

				// Gửi đi thật hay chỉ đang test
				'Sandbox' => ESandbox::ACTUAL_SEND,

				// ID Tin nhắn của đối tác, dùng để kiểm tra ID này đã được
				// hệ thống esms tiếp nhận trước đó hay chưa.
				// 'RequestId' => 123456,

				// Đặt lịch gửi tin
				// 'SendDate' => null,
			];

			// send request
			$response = $client->post($requestUrl, [
				'json' => $requestData
			]);

			// Get response content as object
			$responseContent = json_decode($response->getBody());

			logger('send sms nahaaaaaaaaaaaaa', compact('responseContent', 'requestData'));

			// get user from phone number to save log
            switch ($type) {
                case ESmsType::SEND_OTP_LOGIN:
				case ESmsType::SEND_OTP_FORGOT_PASSWORD:
					$user = $this->userService->getByOptions([
						'phone' => $phoneNumber,
						'status' => EStatus::ACTIVE,
						'first' => true,
					]);
                    break;
            }

            // log data
            $logData = [
            	'created_at' => $now,
				'to_phone_number' => $phoneNumber,
				'type' => $type,
				'content' => $message,
				'send_status' => null,
				'tried_time' => $this->attempts(),
				'error_messages' => null,
			];
            if ($responseContent->CodeResult != 100) {
				$logData['send_status'] = ESendStatus::FAILED;
				$logData['error_messages'] = $responseContent->ErrorMessage;
				$this->result = ['error' => EErrorCode::ERROR, 'msg' => 'Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại'];
            } else {
				$logData['send_status'] = ESendStatus::SENT;
				$this->result = ['error' => EErrorCode::NO_ERROR];
            }
            $this->smsLogService->createSmsLog($logData, $user->id ?? null);
        } catch (\Exception $e) {
            logger()->error('Error when sending sms messages', compact('e'));
			$this->result = ['error' => EErrorCode::ERROR, 'msg' => __('common/error.system-error')];
        }
    }

    public function getResult() {
    	return $this->result;
	}
}