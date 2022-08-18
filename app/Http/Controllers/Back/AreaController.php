<?php

namespace App\Http\Controllers\Back;


use App\Constant\CacheKey;
use App\Enums\EDisplayStatus;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\UserSaveFeedbackRequest;
use App\Models\Country;
use App\Services\AreaService;
use App\Services\CategoryService;
use App\Services\CountryService;
use App\Services\FeedbackService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AreaController extends Controller {
	private AreaService $areaService;

	public function __construct(AreaService $areaService) {
		$this->areaService = $areaService;
	}

	public function getAreaList() {
		$options = [
			'pageSize' => request('pageSize'),
			'get_id_name' => true,
			'orderBy' => 'id',
			'orderDirection' => 'asc'
		];

		$acceptFields = ['q', 'countryId', 'type', 'parentAreaId'];

		foreach ($acceptFields as $field) {
			if (!request()->filled($field)) {
				continue;
			}
			$options[Str::snake($field)] = request($field);
		}
		$areaList = $this->areaService->getByOptions($options);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $areaList,
		]);
	}
}