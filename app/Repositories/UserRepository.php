<?php

namespace App\Repositories;

use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Enums\ECustomerType;
use App\Enums\EUserType;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;
use App\Models\SmsLog;

class UserRepository extends BaseRepository {

	public function __construct(User $user) {
		$this->model = $user;
	}
	/**
	 * @param array $options
	 * @return bool|LengthAwarePaginator|Collection|User
	 */
	public function getByOptions(array $options) {
		$result = $this->model
			->from('users as usr')
            ->leftJoin('user_affiliate_code as uac', 'uac.user_id', 'usr.id')
			->select('usr.*', 'uac.code as affiliateCode');
		if (Arr::get($options, 'admin_customer_list', false)) {
			$result
            	->leftJoin('user_referral as ur', 'ur.user_id', 'usr.id')
            	->leftJoin('users as pr', 'ur.parent_user_id', 'pr.id')

            	->select('usr.id', 'usr.phone', 'usr.name', 'usr.avatar_path', 'usr.email', 'usr.created_at', 'usr.phone', 'usr.status', 'usr.date_of_birth', 'usr.address', 'pr.name as parent_user_name', 'pr.phone as parent_user_phone', 'usr.updated_at', 'usr.type', 'usr.gender', 'usr.code', 'uac.code as affiliateCode');
		}
		foreach ($options as $key => $val) {
			switch ($key) {
				case 'status':
				    if ($val == EStatus::EXCEPT_DELETED) {
                        $result->where('usr.status','!=', EStatus::DELETED);
                    }
					else {
					    $result->where('usr.status', $val);
                    }
					break;
				case 'q':
				    $result->where(function($query) use ($val) {
                        $query->orWhere('usr.name_search', 'ilike', "%$val%")
                            ->orWhere('usr.phone', 'ilike', "%$val%")
                            ->orWhere('usr.email', 'ilike', "%$val%");
                    });
					break;
				case 'id':
				case 'type':
				case 'phone':
					if (is_array($val)) {
						$result->whereIn("usr.$key", $val);
					} else {
						$result->where("usr.$key", $val);
					}
					break;
                case 'not_id':
                case 'not_status':
                	$key = 'usr.' . str_replace('not_', '', $key);
                    if (is_array($val)) {
                    	$result->whereNotIn($key, $val);
					} else {
                    	$result->where($key, '!=', $val);
					}
                    break;
				// case 'last_code':
				// 	$result->orderBy('usr.id', 'desc');
				// 	break;
				// case 'code':
				// 	$result->where('usr.code', $val);
				// 	break;
				// case 'code_not_in':
				// 	$result->whereNotIn('usr.code', $val);
				// 	break;
				case 'createdAtFrom':
                    $result->where('usr.created_at', '>=', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'createdAtTo':
                    $result->where('usr.created_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'countryId':
                    $result->where('usr.country_id', $val);
                    break;
                case 'refferalCode':
                    $result->where('uac.code', $val);
                    break;
    //                 break;
				// case 'admin_customer_list':
				// 	break;
			}
		}
		 if (Arr::get($options, 'status', null) == EStatus::EXCEPT_DELETED){
             $result->orderBy('usr.status', 'asc');
         }

		$orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("usr.$orderBy", "$orderDirection");
                break;
        }

		return parent::getByOption($options, $result);
	}

	public function didEmailExist(string $email, int $exceptUserId = null) {
		return $this->model
			->where(function ($query) use ($exceptUserId) {
				if ($exceptUserId != null) {
					$query->where('id', '!=', $exceptUserId);
				}
			})
			->where('email', $email)
			->where('status', '!=', EStatus::DELETED)
			->exists();
	}

    public function didPhoneExist(string $phone, int $exceptUserId = null) {
        return $this->model
            ->where(function ($query) use ($exceptUserId) {
                if ($exceptUserId != null) {
                    $query->where('id', '!=', $exceptUserId);
                }
            })
            ->where('phone', $phone)
			->where('status', '!=', EStatus::DELETED)
            ->exists();
    }
}
