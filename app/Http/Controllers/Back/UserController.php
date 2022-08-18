<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Enums\ELanguage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\SaveInfoRequest;
use App\Services\UserService;
use App\Services\AclUserRolePermissionService;
use App\Services\UserActivitiesLogService;
use App\Enums\EUserType;
use App\Enums\ECustomerType;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\EWalletType;
use App\Enums\EGender;
use App\Helpers\FileUtility;

class UserController extends Controller {
	private $userService;
	private UserActivitiesLogService $userActivitiesLogService;

	public function __construct(UserService $userService, AclUserRolePermissionService
			$aclUserRolePermissionService, UserActivitiesLogService $userActivitiesLogService) {
		$this->userService = $userService;
		$this->aclUserRolePermissionService = $aclUserRolePermissionService;
		$this->userActivitiesLogService = $userActivitiesLogService;
	}

	public function changePassword(ChangePasswordRequest $request) {
		$validated = $request->validated();
		$userId = Arr::get($validated, 'userId', auth()->id());
		$user = $this->userService->getById($userId);
		if (!Hash::check(Arr::get($validated, 'oldPassword'), $user->password)) {
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => [
					'oldPassword' => [__('back/changePassword.message.password-incorect')]
				],
			]);
		}
		if (Arr::get($validated, 'newPassword') != Arr::get($validated, 'confirmPassword')) {
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => [
					'confirmPassword' => [__('validation.confirmed', ['attribute' => __('back/changePassword.re_enter_new_password')])]
				],
			]);
		}

		$changePassword = $this->userService->changePassword($userId, $validated);
		if (!empty($changePassword)) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.change-password-success'),
			]);
		}
	}

	public function getUserList() {
        $timezone = session(SessionKey::TIMEZONE);
		$acceptFields = ['q', 'customerType', 'type', 'createdAtFrom', 'createdAtTo', 'status'];
		$filters = [
			'admin_customer_list' => true,
			'pageSize' => request('pageSize'),
			'orderBy' => 'created_at',
            'orderDirection' => 'desc',
		];

		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
				continue;
			}
			if ($field === 'createdAtFrom' || $field === 'createdAtTo') {
                if (Arr::has(request('filter'), $field)) {
                    try {
                        $date = Carbon::createFromFormat(
                            EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ,
                            request("filter.$field"). " $timezone",
                        );
                        $filters[$field] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $filters[$field] = request("filter.$field");
            }
		}
		$users = $this->userService->getByOptions($filters);
		foreach ($users as $key) {
			if (isset($key->getShop)) {
				$key->shopId = $key->getShop->id;
				$key->shopName = $key->getShop->name;
			}
			$key->date_of_birth = isset($key->date_of_birth) ? Carbon::parse($key->date_of_birth)->format(EDateFormat::STANDARD_DATE_FORMAT) : null;
			$score = $key->getWallet->where('type', EWalletType::POINT)->first();
			$coins = $key->getWallet->where('type', EWalletType::INTERNAL_MONEY)->first();
			$key->avatar = FileUtility::getFileResourcePath($key->avatar_path, DefaultConfig::FALLBACK_IMAGE_PATH);
			$key->strStatus = EStatus::valueToLocalizedName($key->status);
			$key->score = isset($score) ? number_format($score->balance) : 0;
			$key->coins = isset($coins) ? number_format($coins->balance) : 0;
			$key->scoreType = EWalletType::POINT;
			$key->coinsType = EWalletType::INTERNAL_MONEY;
			$key->createdAt = Carbon::parse($key->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
			$key->genderStr = EGender::getNameByValue($key->gender);
			if($key->meta){
			    $meta = (object)$key->meta;
                $key->isFreeSubscriptionOnce = $meta->freeSubscriptionOnce;
            }else{
                $key->isFreeSubscriptionOnce = false;
            }

			if (isset($key->numberOfRole)) {
				$key->numberOfRole = $key->numberOfRole . '/' . $key->totalRole;
			}

			$activitiesTime = $this->userActivitiesLogService->getLastTime($key->id);
			$key->activities_time = !empty($activitiesTime->created_at) ? Carbon::parse($activitiesTime->created_at)->timezone($timezone)->format(EDateFormat::DATE_FORMAT_HOUR) : null;

			if ($key->type == EUserType::INTERNAL_USER) {
				$filters = [
					'status' => EStatus::ACTIVE,
					'user_id' => $key->id,
					'get' => true,
				];
				$permission = $this->aclUserRolePermissionService->getByOptions($filters);
				$arrRole = [];
				foreach ($permission as $value) {
					array_push($arrRole, $value->permissionGroup->name);
				}
				$key->role = $arrRole;
			}
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $users,
		]);
	}

	public function deleteUser() {
		try {
			$id = request('id');
			$delete = $this->userService->deleteUser($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/user.object_name')
				])
			]);
		} catch (\Exception $e) {
			logger()->error('Fail to delete user', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}

	public function resetUserPassword() {
			$id = request('id');
			$result = $this->userService->resetUserPassword($id, auth()->id());
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($result);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.reset-password-success'),
			]);
	}

	public function saveUser(SaveInfoRequest $request) {
		try {
			$data = $request->validated();
			$data['avatar'] = request('image');
			$data['customerType'] = request('customerType') == 'null' ? null : request('customerType');
			$data['userType'] = request('userType');
			$data['id'] = request('id');
			if(Arr::get($data, 'dob', null)) {
                $data['dob'] = str_replace('/', '-', $data['dob']);
            }
			$data['gender'] = request('gender');
			$data['latitude'] = request('latitude');
			$data['longitude'] = request('longitude');
			if (!$data['id']) {
				$data['status'] = EStatus::ACTIVE;
			}
            $result = $this->userService->saveUser($data, auth()->id());
            if ($result['error'] !== EErrorCode::NO_ERROR) {
                return response()->json($result);
            }
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'msg' => __('common/common.save-data-success', [
                    'objectName' => __('back/user.object_name')
                ]),
            ]);
        } catch (\Exception $e) {
            logger()->error('Fail to save user', [
                'error' =>  $e
            ]);
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => __('common/common.there_was_an_error_in_the_processing'),
            ]);
        }
    }

	public function getUserInfo($id) {
		$option = [
			'id' => $id,
			'type' => request('type'),
			'first' => true
		];
		if ($option['type'] == EUserType::NORMAL_USER) {
			$option['customer_type'] = request('customerType');
		}
		$user = $this->userService->getByOptions($option);
		//abort_if(empty($user) || $user->status == EStatus::DELETED, Response::HTTP_NOT_FOUND);
		if ($user->type == EUserType::INTERNAL_USER) {
			$filters = [
				'status' => EStatus::ACTIVE,
				'user_id' => $user->id,
				'get' => true,
			];
			$getRoleOfuser = $this->aclUserRolePermissionService->getByOptions($filters);
		}
		$data = [
			'image' => FileUtility::getFileResourcePath($user->avatar_path, DefaultConfig::FALLBACK_IMAGE_PATH),
			'name' => $user->name,
			'phone' => $user->phone,
			'email' => $user->email,
            'code' => $user->code,
			'dob' => $user->date_of_birth,
			'address' => $user->address,
			'roleOfUser' => isset($getRoleOfuser) ? $getRoleOfuser: null,
			'status' => $user->status,
			'gender' => $user->gender,
			'latitude' => $user->latitude,
			'longitude' => $user->longitude,
			'affiliateCode' => $user->affiliateCode,
			'statusStr' => EStatus::valueToLocalizedName($user->status),
		];
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'user' => $data,
		]);
	}


	public function searchCustomer() {

		$allowOptions = ['q', 'pageSize', 'code_not_in', 'createdAtFrom', 'createdAtTo', 'status'];

        $options = array_merge([
            'type' => EUserType::NORMAL_USER,
            'pageSize' => 15,
        ], request()->only($allowOptions));

        foreach ($allowOptions as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            $fieldValue = request("filter.$field");
            switch ($field) {

            }
            $options[Str::snake($field)] = $fieldValue;
        }

		$userList = $this->userService->getByOptions($options);
		$tmp = $userList->map(function($user) {
			return [
				'code' => $user->code,
				'id' => $user->id,
				'name' => $user->name,
				'typeStr' => ECustomerType::valueToLocalizedName($user->customer_type),
				'phone' => $user->phone,
			];
		});
		$userList->setCollection($tmp);

		return [
			'error' => EErrorCode::NO_ERROR,
			'data' => $userList,
		];
	}

	public function editBalance() {
		// try {
			$result = $this->userService->editBalance(request()->all(), auth()->id());
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($result);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.save-data-success', [
					'objectName' => __('back/user.balance.object_name')
				]),
			]);
		// } catch (\Exception $e) {
		// 	logger()->error('Fail to save balance', [
		// 		'error' =>  $e
		// 	]);
		// 	return response()->json([
		// 		'error' => EErrorCode::ERROR,
		// 		'msg' => __('common/common.there_was_an_error_in_the_processing'),
		// 	]);
		// }
	}

	public function approveUser() {
        $id = request('id');
        $approve = $this->userService->toggleUserActiveStatus($id, 'unlock', auth()->id());
        if ($approve['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($approve);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.approve-data-success2', [
                'objectName' => __('back/user.object_name')
            ])
        ]);
    }
}
