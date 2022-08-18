<?php

namespace App\Repositories;


use App\Enums\EDateFormat;
use App\Models\OtpCode;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OtpCodeRepository extends BaseRepository {

	public function __construct(OtpCode $otpCode) {
		$this->model = $otpCode;
	}

	public function getByCode($code) {
		return $this->model->where('otp_code', $code)->first();
	}

	public function getByOptions(array $options) {
		$result = $this->model
			->newQuery()
			->from('otp_code as oc')
			->select('oc.*');

		foreach ($options as $key => $val) {
			switch ($key) {
				case 'id':
				case 'user_id':
				case 'type':
				case 'otp_code':
				case 'verified':
					if (is_array($val)) {
						$result->whereIn("usr.$key", $val);
					} else {
						$result->where($key, $val);
					}
					break;
				case 'expire_after':
					/** @var Carbon $val */
					$result->where('oc.created_at', '>=', $this->parseTimeString($val));
					break;
				case 'expire_before':
					/** @var Carbon $val */
					$result->where('oc.created_at', '<', $this->parseTimeString($val->copy()->addDay()));
					break;
			}
		}

		$orderBy = Arr::get($options,'orderBy', 'created_at');
		$orderDirection = Arr::get($options,'orderDirection', 'desc');
		switch ($orderBy) {
			default:
				$result->orderBy("oc.$orderBy", "$orderDirection");
				break;
		}

		return parent::getByOption($options, $result);
	}
}