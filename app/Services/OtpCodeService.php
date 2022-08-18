<?php

namespace App\Services;

use App\Constant\DefaultConfig;
use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Enums\ESmsType;
use App\Jobs\SendSms;
use App\Models\OtpCode;
use App\Repositories\OtpCodeRepository;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OtpCodeService {
	private OtpCodeRepository $otpCodeRepository;
	private UserService $userService;

	public function __construct(OtpCodeRepository $otpCodeRepository, UserService $userService) {
		$this->otpCodeRepository = $otpCodeRepository;
		$this->userService = $userService;
	}

	/**
	 * Update ngày 30/12/2020 Nếu có một OTP còn hạn sử dụng và chưa verify thì return OTP đó
	 * Hoặc tạo một OTP mới
	 *
	 * @param int $userId
	 * @return mixed
	 */
	public function getOrCreateNewOtp(int $userId, int $otpType, int $currentUserId) {
		$availableOtp = $this->otpCodeRepository->getByOptions([
			'user_id' => $userId,
			'type' => $otpType,
			'verified' => false,
			'expire_after' => Carbon::now(),
			'first' => true,
		]);
		if (!empty($availableOtp)) {
			return [
				'error' => EErrorCode::NO_ERROR,
				'otpCode' => $availableOtp,
			];
		}

		$otp = new OtpCode();
		$otp->user_id = $userId;
		$otp->type = $otpType;
		$otp->otp_code = $this->randomOtp();
		$otp->verified = false;
		$otp->expired_at = now()->addMinutes(3);
		$otp->created_by = $currentUserId;
		$otp->save();
		return [
			'error' => EErrorCode::NO_ERROR,
			'otpCode' => $otp,
		];
	}

	private function randomOtp() {
		return sprintf("%'.03d", random_int(10, 990)) . sprintf("%'.03d", random_int(10, 990));
	}

	private function getDefaultOtpOptions() {
		return [
			'sendNow' => false,
			'retry' => 3,
		];
	}

	public function sendOtp(User $user, int $otpType, array $options = []) {
		$options = array_merge($this->getDefaultOtpOptions(), $options);

		return DB::transaction(function() use ($user, $otpType, $options) {
			$otpResult = $this->getOrCreateNewOtp($user->id, $otpType, $user->id);
			$otp = $otpResult['otpCode'];
			switch ($otpType) {
				case EOtpType::VERIFY_EMAIL_WHEN_REGISTER:
					$message = __('front/sms.register', [
						'otpCode' => $otp->otp_code,
						'durationInMinutes' => 3,
					]);
					$smsType = ESmsType::SEND_OTP_LOGIN;
					break;
				case EOtpType::VERIFY_EMAIL_WHEN_FORGOT_PASSWORD:
					$message = __('front/sms.forgot_password', [
						'otpCode' => $otp->otp_code,
						'durationInMinutes' => 3,
					]);
					$smsType = ESmsType::SEND_OTP_FORGOT_PASSWORD;
					break;
				default:
					throw new \Exception('otpType is not valid');
			}

			$retry = Arr::get($options, 'retry');
			if (Arr::get($options, 'sendNow')) {
				$i = 0;
				do {
					$i++;
					$job = SendSms::dispatchNow($user->phone, $message, $smsType, Carbon::now());
				} while ($i < $retry && $job->getResult()['error'] !== EErrorCode::NO_ERROR);

				return array_map($job->getResult(), [
					'otpCode' => $otp,
				]);
			}

			SendSms::dispatch($user->phone, $message, $smsType, Carbon::now(), $retry);
			return [
				'error' => EErrorCode::NO_ERROR,
				'otpCode' => $otp,
			];
		});
	}

	/**
	 * Kiểm tra OTP có hợp lệ không
	 * Nếu OTP = DefaultConfig::OTP_CODE thì mặc định đúng
	 *
	 * @param string $otpCode
	 * @param int $userId
	 * @return bool
	 */
	public function checkOTPCode(string $otpCode, int $userId) {
		if ($otpCode != DefaultConfig::OTP_CODE) {
			$otpCode = $this->otpCodeRepository->getByOptions([
				'otp_code' => $otpCode,
				'user_id' => $userId,
				'verified' => false,
				'expired_after' => now(),
				'first' => true,
			]);
			if (empty($otpCode)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Xác nhận OTP
	 * Nếu OTP = DefaultConfig::OTP_CODE thì mặc định đúng
	 *
	 * @param string $otpCode
	 * @param int $userId
	 * @return array
	 */
	public function verifyOtpCode(string $otpCode, int $userId) {
		if ($otpCode != DefaultConfig::OTP_CODE) {
			$otpCode = $this->otpCodeRepository->getByOptions([
				'otp_code' => $otpCode,
				'user_id' => $userId,
				'verified' => false,
				'expired_after' => now(),
				'first' => true,
			]);
			if (!empty($otpCode) && now()->diffInSeconds($otpCode->created_at) > 180) {
				return [
					'error' => EErrorCode::ERROR,
					'msg' => 'Mã xác minh không còn hiệu lực',
				];
			}
			if (empty($otpCode)) {
				return [
					'error' => EErrorCode::ERROR,
					'msg' => 'Mã xác minh không chính xác, vui lòng nhập lại',
				];
			}

			$otpCode->verified = true;
			$otpCode->save();
		}

		return [
			'error' => EErrorCode::NO_ERROR,
			'otpCode' => $otpCode,
		];
	}

	/**
	 * Xác nhận OTP sau đó xử lý tùy theo $otpType
	 * Ở đây không lấy $otpType từ DB trả về mà lấy từ controller truyền lên do có case dùng default Otp nữa
	 *
	 * @param User $user
	 * @param string $otpCode
	 * @param int $otpType
	 * @param array $processData (optional) dùng để truyền cho hàm xử lý
	 * @return array [error, user]
	 */
	public function verifyOtpCodeAndProcessByOtpType(User $user, string $otpCode, int $otpType, array $processData = []) {
		return DB::transaction(function() use ($user, $otpCode, $otpType, $processData) {
			$verifyResult = $this->verifyOtpCode($otpCode, $user->id);
			if ($verifyResult['error'] !== EErrorCode::NO_ERROR) {
				return $verifyResult;
			}

			switch ($otpType) {
				case EOtpType::VERIFY_EMAIL_WHEN_REGISTER:
					if (!$user->hasVerifiedEmail()) {
						$user->markEmailAsVerified();
					}
					$this->userService->generateUserStuffs($user->id);
					break;
				case EOtpType::VERIFY_EMAIL_WHEN_FORGOT_PASSWORD:
					$user->password = Arr::get($processData, 'password');
					$user->save();
					break;
			}

			return [
				'error' => EErrorCode::NO_ERROR,
				'user' => $user,
			];
		});
	}
}
