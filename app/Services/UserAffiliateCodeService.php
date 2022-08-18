<?php

namespace App\Services;

use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Repositories\UserAffiliateCodeRepository;
use Illuminate\Support\Arr;
use App\Models\UserAffiliateCode;
use Illuminate\Support\Str;

class UserAffiliateCodeService {
	private UserAffiliateCodeRepository $userAffiliateCodeRepository;

    public function __construct(UserAffiliateCodeRepository $userAffiliateCodeRepository) {
        $this->userAffiliateCodeRepository = $userAffiliateCodeRepository;
    }

    /**
     * @param $userId
     * @return \App\User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getById($id) {
	    return $this->userAffiliateCodeRepository->getById($id);
    }

    public function getByOptions($options) {
        return $this->userAffiliateCodeRepository->getByOptions($options);
    }

	/**
	 * Lưu user_affiliate_code
	 * Case 1: Không điền code -> gen random 5 kí tự A-Z0-9
	 * Case 2: Điền code -> check xem code đã tồn tại chưa
	 *
	 * @param $data
	 * @param $loggedInUserId
	 * @return array
	 */
	public function saveAffiliateCode($data, $loggedInUserId) {
        $userId = Arr::get($data, 'user_id');
        $affiliateCode = mb_strtoupper((string)Arr::get($data, 'code'));

        if (!empty($affiliateCode)) {
			$isCodeUsed = $this->userAffiliateCodeRepository->getByOptions([
				'code' => $affiliateCode,
				'not_user_id' => $userId,
				'status' => EStatus::ACTIVE,
				'exists' => true,
			]);
			if ($isCodeUsed) {
				$errMsg = ['affiliateCode' => ['Mã giới thiệu đã tồn tại']];
				return ['error' => EErrorCode::ERROR, 'msg' => $errMsg];
			}
		}

        $userAffiliateCode = $this->userAffiliateCodeRepository->getByOptions([
            'user_id' => $userId,
            'first' => true,
            'status' => EStatus::ACTIVE,
        ]);
        if (!empty($userAffiliateCode)) {
			$userAffiliateCode->updated_by = $loggedInUserId;
        } else {
			$userAffiliateCode = new UserAffiliateCode();
			$userAffiliateCode->created_by = $loggedInUserId;
        }

        if (empty($affiliateCode)) {
			do {
				$affiliateCode = mb_strtoupper('GT' . Str::random(5));
				$isCodeUsed = $this->userAffiliateCodeRepository->getByOptions([
					'code' => $affiliateCode,
					'not_user_id' => $userId,
					'status' => EStatus::ACTIVE,
					'exists' => true,
				]);
			} while ($isCodeUsed);
		}
		$userAffiliateCode->code = $affiliateCode;
		$userAffiliateCode->user_id = $userId;
		$userAffiliateCode->status = EStatus::ACTIVE;
		$userAffiliateCode->save();
        return ['error' => EErrorCode::NO_ERROR];
    }
}
