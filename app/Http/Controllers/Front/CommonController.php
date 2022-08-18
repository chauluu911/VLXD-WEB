<?php

namespace App\Http\Controllers\Front;


use App\Constant\CacheKey;
use App\Enums\EDisplayStatus;
use App\Constant\DefaultConfig;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\UserSaveFeedbackRequest;
use App\Models\Country;
use App\Models\Area;
use App\Models\Category;
use App\Services\AreaService;
use App\Services\CategoryService;
use App\Services\CountryService;
use App\Services\FeedbackService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Enums\EAreaType;
use App\Helpers\FileUtility;
use App\Helpers\StringUtility;

class CommonController extends Controller {
	private AreaService $areaService;
	private CategoryService $categoryService;

	public function __construct(AreaService $areaService, CategoryService $categoryService) {
		$this->areaService = $areaService;
		$this->categoryService = $categoryService;
	}

	public function getAreaList() {
		$options = [
			'get_id_name' => true,
			'orderBy' => 'seq',
			'orderDirection' => 'asc',
		];
		$acceptFields = ['q', 'countryId', 'type', 'parentAreaId'];

		foreach ($acceptFields as $field) {
			if (!request()->filled($field)) {
				continue;
			}
			$options[Str::snake($field)] = request($field);
		}
		if (request('pageSize')) {
			$options['pageSize'] = request('pageSize');
		}
		//dd($options);
		$areaList = $this->areaService->getByOptions($options);
		foreach ($areaList as $province) {
			$district = $province->childAreas->where('type', EAreaType::DISTRICT);
			if (count($district) > 0) {
				$province->district = $district;
				foreach ($province->district as $district) {
					$district->child_areas = $district->childAreas->where('type', EAreaType::WARD);
				}
			}
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $areaList,
		]);
	}

	public function getAllCategory() {
		$options = [
			'get' => true,
			'orderBy' => 'seq',
			'orderDirection' => 'asc',
			'get_data_for_user' => true,
		];

		$acceptFields = ['parentCategoryId', 'notType'];

		foreach ($acceptFields as $field) {
			if (!request()->filled($field)) {
				continue;
			}
			$options[Str::snake($field)] = request($field);
		}
		if (request('pageSize')) {
			$options['pageSize'] = request('pageSize');
		}
		$categoryList = $this->categoryService->getByOptions($options);
		foreach ($categoryList as $category) {
			$attribute = $category->attributeEnableFilter;
			foreach ($attribute as $key) {
				$key->data = json_decode($key->value);
			}
			$category->attribute = $attribute;
			$category->childCategories;
            $category->logo_path = !empty($category->logo_path) ? get_image_url([
                'path' => $category->logo_path,
                'op' => 'thumbnail',
                'w' => 130,
                'h' => 130,
            ]) : DefaultConfig::FALLBACK_IMAGE_PATH;
            foreach ($category->childCategories as $item) {
            	$item->logo_full_path = FileUtility::getFileResourcePath($item->logo_path, DefaultConfig::FALLBACK_IMAGE_PATH);
            	$item->childCategories;
            }
		}

		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $categoryList,
		]);
	}

	public function getThumbnailTiktok() {
		return response()->json([
			'data' => StringUtility::getLinkTikTok(request('url')),
		]);
	}
}
