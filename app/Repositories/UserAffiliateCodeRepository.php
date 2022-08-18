<?php

namespace App\Repositories;

use App\Enums\EStatus;
use App\Models\UserAffiliateCode;

class UserAffiliateCodeRepository extends BaseRepository {

    public function __construct(UserAffiliateCode $userAffiliateCode) {
        $this->model = $userAffiliateCode;
    }

    public function getByOptions(array $options) {
        $result = $this->model
			->newQuery()
            ->from('user_affiliate_code');

        foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                case 'user_id':
                    $result->where($key, $val);
                    break;
				case 'code':
					$result->whereRaw("upper($key) = '$val'");
					break;
				case 'not_user_id':
					$key = str_replace('not_', '', $key);
					$result->where($key, '!=', $val);
					break;
            }
        }
        return parent::getByOption($options, $result);
    }
}
