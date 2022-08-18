<?php

namespace App\Services;

use App\Constant\DefaultConfig;
use App\Enums\EDateFormat;
use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Enums\EStatus;
use App\Enums\EUserType;
use App\Repositories\OtpCodeRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserRolePermissionRepository;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\ECustomerType;
use App\Repositories\AclObjectRepository;
use App\Models\UserRolePermission;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Wallet;
use App\Models\WalletTransactionLog;
use App\Enums\EWalletTransactionLogType;
use App\Enums\EWalletType;
use App\Enums\ECreatedFrom;
use Illuminate\Support\Str;

class UserService {
	private UserRepository $userRepository;
	private UserRolePermissionRepository $userRolePermissionRepository;
	private AclObjectRepository $aclObjectRepository;
	private OtpCodeRepository $otpCodeRepository;
    private WalletService $walletService;
    private ShopService $shopService;
    private UserAffiliateCodeService $userAffiliateCodeService;

    public function __construct(UserRepository $userRepository,
								UserRolePermissionRepository $userRolePermissionRepository,
								AclObjectRepository $aclObjectRepository,
								OtpCodeRepository $otpCodeRepository,
                                WalletService $walletService,
                                ShopService $shopService,
                                UserAffiliateCodeService $userAffiliateCodeService) {
        $this->userRepository = $userRepository;
        $this->userRolePermissionRepository = $userRolePermissionRepository;
        $this->aclObjectRepository = $aclObjectRepository;
        $this->otpCodeRepository = $otpCodeRepository;
        $this->walletService = $walletService;
        $this->shopService = $shopService;
        $this->userAffiliateCodeService = $userAffiliateCodeService;
    }

    /**
     * @param $userId
     * @return \App\User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getById($userId) {
	    return $this->userRepository->getById($userId);
    }

    public function getByOptions(array $options) {
        return $this->userRepository->getByOptions($options);
    }

    public function generateNewCode() {
        do {
            $code = 'ID' . mb_strtoupper(Str::random(5));
        } while (User::where('code', $code)->exists());
        return $code;
    }

    public function saveUser($data, $loggedInUserId) {
        $fileToDeleteIfError = [];
        try {
            return DB::transaction(function() use ($data, $loggedInUserId, &$fileToDeleteIfError) {
                $id = Arr::get($data, 'id');
                $errMsg=[];
                if (!empty(Arr::get($data, 'email'))) {
                    $check = $this->userRepository->didEmailExist(Arr::get($data, 'email'), $id);
                    if ($check) {
                        $errMsg = ['email' => [__('common/error.email-exist')]];
                    }
                }

                if (!empty(Arr::get($data, 'phone'))) {
                    $checkPhone = $this->userRepository->didPhoneExist(Arr::get($data, 'phone'), $id);
                    if ($checkPhone) {
                        $errMsg['phone'] = [__('common/error.phone-exist')];
                    }
                }

                if ($id) {
                    $user = $this->getById($id);
                    if (empty($user)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                    }
                    if(!empty($errMsg)){
                        return ['error' => EErrorCode::ERROR, 'msg' => $errMsg];
                    }
                    $user->updated_by = $loggedInUserId;
                    //nếu là update user thì cho phép sửa mã giới thiệu
                    $saveAffiliateCode = $this->userAffiliateCodeService->saveAffiliateCode([
                        'user_id' => $id,
                        'code' => Arr::get($data, 'affiliateCode')
                    ], $loggedInUserId);
                    if ($saveAffiliateCode['error'] == EErrorCode::ERROR) {
                        return $saveAffiliateCode;
                    }
                } else {
                    if(!empty($errMsg)){
                        return ['error' => EErrorCode::ERROR, 'msg' => $errMsg];
                    }
                    $user = new User();
                    $user->code = $this->generateNewCode();
                    $user->created_by = $loggedInUserId;
                    $user->status = EStatus::WAITING;
                    $user->password = Hash::make(Arr::get($data, 'password', DefaultConfig::DEFAULT_USER_PASSWORD));
                }
                $user->type = Arr::get($data, 'userType', $user->type);
                $user->created_from = ECreatedFrom::WEB;
                // $user->customer_type = Arr::get($data, 'customerType');
                $user->date_of_birth = !empty(Arr::get($data, 'dob')) ? Carbon::parse(Arr::get($data, 'dob'))->format(EDateFormat::FORMAT_YEAR_MONTH_DATE) : null;
                // $user->country_id = (int)Arr::get($data, 'country');

				$fields = ['status', 'name', 'email', 'address', 'register_confirm_code', 'tz', 'language_code', 'phone', 'latitude', 'longitude', 'gender', 'type', 'gg_id', 'fb_id', 'avatar_path'];
				foreach ($fields as $field) {
				    if(Arr::has($data, $field)) {
                        $user->{$field} = Arr::get($data, $field);
                    }
                }

                $avatarFile = Arr::get($data, 'avatar');

                if (!empty($avatarFile)) {
                    $relativePath = FileUtility::storeFile(EStoreFileType::USER_AVATAR, $avatarFile);
                    FileUtility::removeFiles([$user->avatar_path]);
                    $user->avatar_path = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                }

                $user->save();

                if (Arr::get($data, 'userType') == EUserType::INTERNAL_USER) {
                    $roles = Arr::get($data, 'role', []);
                    $aclUserRolePermissions = $this->userRolePermissionRepository->getByUserId($user->id);
                    foreach ($aclUserRolePermissions as $permission) {
                        if (array_search($permission->permission_group_id, $roles) === false) {
                            $permission->status = EStatus::DELETED;
                            $permission->deleted_at = now();
                            $permission->deleted_by = $loggedInUserId;
                            $permission->save();
                        }
                    }

                    foreach ($roles as $role) {
                        $aclObject = $this->aclObjectRepository->getById($role);
                        if (empty($aclObject)) {
                        	DB::rollBack();
                            return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                        }
                        if (!$this->userRolePermissionRepository->isUserHasPermissionGroup($user->id, $aclObject->id)) {
                            $aclUserRolePermission = new UserRolePermission();
                            $aclUserRolePermission->status = EStatus::ACTIVE;
                            $aclUserRolePermission->user_id = $user->id;
                            $aclUserRolePermission->permission_group_id = $aclObject->id;
                            $aclUserRolePermission->created_by = $loggedInUserId;
                            $aclUserRolePermission->save();
                        }
                    }
                }

                if (Arr::get($data, 'sendVerifyOtp')) {
                	/** @var OtpCodeService $otpCodeService */
                	$otpCodeService = resolve('\App\Services\OtpCodeService');
					$otpCodeService->sendOtp($user, EOtpType::VERIFY_EMAIL_WHEN_REGISTER);
				}

                return [
                    'error' => EErrorCode::NO_ERROR,
                    'user' => $user,
                ];
            });
        } catch (\Exception $e) {
            FileUtility::removeFiles($fileToDeleteIfError);
            throw $e;
        }
    }

    public function deleteUser($id, $loggedInUserId) {
        return DB::transaction(function() use ($loggedInUserId, $id) {
            $user = $this->getById($id);
            if (empty($user)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            abort_if($user->status == EStatus::DELETED, Response::HTTP_NOT_FOUND);
            // if ($user->status == EStatus::DELETED) {
            //     return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.data-was-deleted')];
            // }
            $user->deleted_by = $loggedInUserId;
            $user->deleted_at = Carbon::now();
            $user->status = EStatus::DELETED;

            //delete user -> delete shop of user
            $shop = $user->getShop;
            if (!empty($shop)) {
                $shopDeleted = $this->shopService->deleteShop($shop->id, $loggedInUserId);
                if ($shopDeleted['error'] !== EErrorCode::NO_ERROR) {
                    return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                }
            }
            $user->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function resetUserPassword($id, int $loggedInUserId) {
        return DB::transaction(function() use ($loggedInUserId, $id) {
            $user = $this->getById($id);
            if (empty($user)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            abort_if($user->status == EStatus::DELETED, Response::HTTP_NOT_FOUND);
            // if ($user->status == EStatus::DELETED) {
            //     return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.data-was-deleted')];
            // }
            $user->updated_by = $loggedInUserId;
            $user->password = Hash::make(DefaultConfig::DEFAULT_USER_PASSWORD);
            $user->save();

            return ['error' => EErrorCode::NO_ERROR, 'name' => $user->name];
        });
    }

    public function toggleUserActiveStatus(int $userId, string $action, int $loggedInUserId) {
        return DB::transaction(function() use ($loggedInUserId, $userId, $action) {
            $user = $this->getById($userId);
            if (empty($user)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $user->updated_by = $loggedInUserId;
            if ($action === 'lock') {
                $user->status = EStatus::SUSPENDED;
            } else if ($action === 'unlock') {
                $user->status = EStatus::ACTIVE;
            }
            $this->generateUserStuffs($userId);
            $user->save();

            return ['error' => EErrorCode::NO_ERROR, 'name' => $user->name];
        });
    }

    public function changePassword($userId, $data) {
        $user = $this->getById($userId);
        if (empty($user)) {
            return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
        }
        $user->password = Hash::make($data['newPassword']);
        $user->save();
        return ['error' => EErrorCode::NO_ERROR];
    }

	/**
	 * Tìm user từ thông tin đăng nhập bằng facebook hoặc google
	 *
	 * @param string $provider facebook or google
	 * @param object $userInfo user info getting from socialite
	 * @return User
	 */
	public function findUserWithSocialite($provider, $userInfo) {
        return DB::transaction(function() use ($provider, $userInfo) {
            $column_id_fb_gg = ($provider === 'google') ? 'gg_id' : 'fb_id';

            // TODO: action when user is inactive or deleted
            if (!empty($userInfo->email)) {
                $checkExist = User::where('email', $userInfo->email)
                    ->where('status', '!=', EStatus::DELETED)
                    ->where('type', EUserType::INTERNAL_USER)
                    ->exists();
                if ($checkExist) {
                    return [
                        'error' => EErrorCode::ERROR,
                        'msg' => 'Tài khoản của bạn không có quyền truy cập, vui lòng đăng nhập bằng một tài khoản khác',
                    ];
                }
                $user_by_email = User::where('email', $userInfo->email)
                    ->where('status', '!=', EStatus::DELETED)
                    ->first();
                if ($user_by_email) {
                    $user_by_email->{$column_id_fb_gg} = $userInfo->id;
                    $user_by_email->save();


                    return $user_by_email;
                }
            }

            if (!empty($userInfo->phone)) {
                $checkExist = User::where('phone', $userInfo->phone)
                    ->where('status', '!=', EStatus::DELETED)
                    ->where('type', EUserType::INTERNAL_USER)
                    ->exists();
                if ($checkExist) {
                    return [
                        'error' => EErrorCode::ERROR,
                        'msg' => 'Tài khoản của bạn không có quyền truy cập, vui lòng đăng nhập bằng một tài khoản khác',
                    ];
                }
            }

            $user_by_oauth_id = User::where($column_id_fb_gg, $userInfo->id)
                ->where('status', '!=', EStatus::DELETED)
                ->first();
            if ($user_by_oauth_id) {
                return $user_by_oauth_id;
            }

			return null;
        });
    }

    public function createUserWithSocialite(array $userInfo, int $currentUserId = null) {
		return DB::transaction(function() use ($userInfo, $currentUserId) {
			// Add option to send an sms OTP to user after created
			$userInfo['sendVerifyOtp'] = true;

			$result = $this->saveUser($userInfo, $currentUserId);
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return $result;
			}

			$this->generateUserStuffs($result['user']->id);

			return [
				'error' => EErrorCode::NO_ERROR,
				'user' => $result['user'],
			];
		});
	}

	public function saveLanguageCode($languageCode) {
        if (auth()->user() != null) {
            $user = $this->getById(auth()->user()->id);
            if ($user != null) {
                $user->language_code = $languageCode;
                $user->save();
            }
        }
    }

    public function updateUserMeta(User $user) {
        return DB::transaction(function () use ($user) {
            $meta = $user->meta;
            if ($meta) {
                $meta = (object) ($meta);
                $meta->freeSubscriptionOnce = $meta->freeSubscriptionOnce ? !($meta->freeSubscriptionOnce) : true;
                $user->meta = (object) $meta;
            } else {
                $user->meta = [
                    "freeSubscriptionOnce" => true,
                ];
            }
            $user->save();

            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function getCoinAndPoint($userId) {
        $user = $this->getById($userId);
        if (empty($user)) {
            return ['error' => EErrorCode::ERROR,
                'msg' => __('common/error.invalid-request-data')];
        }
        $coin = number_format($user->getWallet->where('type', EWalletType::INTERNAL_MONEY )
            ->first()->balance);
        $point = number_format($user->getWallet->where('type', EWalletType::POINT )
            ->first()->balance);
        return [
            'error' => EErrorCode::NO_ERROR,
            'coin' => $coin,
            'point' => $point
        ];
    }

    public function editBalance($data, $loggedInUserId) {
        return DB::transaction(function () use ($data, $loggedInUserId) {
            $user = $this->getById(Arr::get($data, 'userId', null));
            if (!empty($user)) {
                $wallet = $user->getWallet->where('type', Arr::get($data, 'type'))->first();
                if (!empty($wallet)) {
                    $wallet->updated_at = now();
                    $wallet->updated_by = $loggedInUserId;
                } else {
                    $wallet = new Wallet();
                    $wallet->user_id = Arr::get($data, 'userId', null);
                    $wallet->type = Arr::get($data, 'type');
                    $wallet->status = EStatus::ACTIVE;
                    $wallet->created_at = now();
                    $wallet->created_by = $loggedInUserId;
                }
                $wallet->balance = Arr::get($data, 'balance', 0);
                $wallet->save();

                $log = new WalletTransactionLog();
                $log->user_id = $wallet->user_id;
                $log->wallet_id = $wallet->id;
                $log->type = Arr::get($data, 'type') == EWalletType::POINT ? EWalletTransactionLogType::CHANGE_POINT_BY_ADMIN : EWalletTransactionLogType::CHANGE_COIN_BY_ADMIN;
                $log->changed_amount = $wallet->balance;
                $log->created_at = now();
                $log->created_by = $loggedInUserId;
                $log->save();
            }
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

	/**
	 * Tạo mấy thứ liên quan tới user
	 * 1. Ví
	 * 2. Mã giới thiệu
	 *
	 * @param int $userId
	 * @param array $options
	 * Các options hỗ trợ:
	 * string affiliateCode (không bắt buộc)
	 */
	public function generateUserStuffs(int $userId, array $options = []) {
        try {
            DB::transaction(function() use ($userId, $options) {
				$user = $this->getById($userId);
				if (empty($user)) {
					return;
				}

				$this->walletService->generateUserWallet($userId, EWalletType::POINT);

				// Chỉ tạo 1 lần, có rồi không tạo nữa
				$isAffiliateCodeExisted = $this->userAffiliateCodeService->getByOptions([
					'user_id' => $userId,
					'status' => EStatus::ACTIVE,
					'exists' => true,
				]);
				if (!$isAffiliateCodeExisted) {
					$this->userAffiliateCodeService->saveAffiliateCode([
						'user_id' => $userId,
						'code' => Arr::get($options, 'affiliateCode')
					], $userId);
				}
            });
        } catch (\Exception $e) {
            logger()->error("[::generateUserStuffs] user_id = $userId: fail to generate stuffs", ['e' => $e]);
        }
    }
}
