<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Enums\ECategoryType;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use App\Services\CategoryAttributeService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CategoryAttributeController extends Controller {
    protected CategoryAttributeService $categoryAttributeService;

    public function __construct(CategoryAttributeService $categoryAttributeService) {
        $this->categoryAttributeService = $categoryAttributeService;
    }

    public function getAttribute() {
		$options = array_merge([
			'pageSize' => request('pageSize'),
            'categoryId' => request('categoryId')
		]);
		$acceptFields = ['only', 'name', 'q'];
		foreach ($acceptFields as $field) {
			if (!request()->filled($field)) {
				continue;
			}
			$options[Str::snake($field)] = request($field);
		}
        $result = $this->categoryAttributeService->getByOptions($options);
        // $resultCol = request('get', ['id', 'type', 'code', 'name', 'meta', 'parentAndGrandparent']);
        // if ($categoryList instanceof LengthAwarePaginator) {
        //     $tmp = $categoryList->map(function ($item) use ($options, $resultCol) {
        //         return $this->extractCategory($item, $resultCol);
        //     });
        //     $categoryList->setCollection($tmp);
        // } else {
        //     $categoryList = $categoryList->map(function ($item) use ($options, $resultCol) {
        //         return $this->extractCategory($item, $resultCol);
        //     });
        // }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data'  => $result,
        ]);
    }
}
