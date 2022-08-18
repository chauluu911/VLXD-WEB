<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Enums\ECategoryType;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\ECategoryDataType;
use App\Enums\ECategoryValueType;
use \App\Http\Controllers\Controller;
use App\Http\Requests\Category\SaveCategoryRequest;
use App\Services\CategoryService;
use App\Services\Language;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class CategoryController extends Controller {
    protected $categoryService;
    protected $languageService;

    public function __construct(CategoryService $categoryService) {
        $this->categoryService = $categoryService;
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

        $result = $this->categoryService->getByOptions($options);

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
            'data'  => $categoryList,
        ]);
    }

    public function getForCategoryList() {
        $options = [
            'status' => EStatus::ACTIVE,
            'orderBy' => 'seq',
            'orderDirection' => 'asc',
        ];

        $acceptFields = ['q', 'type', 'getAllCategory', 'parentCategoryId'];
        foreach ($acceptFields as $field) {
            if (!request()->filled("filter.$field")) {
                continue;
            }
            $options[Str::snake($field)] = request("filter.$field");
        }
        if (!empty(request('pageSize'))) {
            $options['pageSize'] = request('pageSize');
        }
        $type = Arr::get($options, 'type');
        if (empty($type)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg'   => __('back/category.category_type_not_found'),
            ]);
        }
        $categoryList = $this->categoryService->getByOptions($options);
        if (!empty($options['parent_category_id'])) {
            $currentCategory = $this->categoryService->getById($options['parent_category_id']);
        } else {
            $currentCategory = null;
        }
        if (!empty($currentCategory) && !empty($currentCategory->parent_category_id)) {
            $parentCategory = $this->categoryService->getById($currentCategory->parent_category_id);
        } else {
            $parentCategory = null;
        }
        if (empty(request('filter.getAllCategory'))) {
            $tmp = $categoryList->map(function(Category $category) use ($type) {
                $attribute = $category->allAttribute;
                if (count($attribute) > 0) {
                    foreach ($attribute as $key) {
                        if (!empty($key->value)) {
                            $key->valueName = json_decode($key->value)->value;
                        }
                    }
                }

                $childCategory = $this->categoryService->getChildCategory([
                    'parent_category_id' => $category->id,
                    'status' => EStatus::ACTIVE,
                    'type' => $type,
                ]);
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'status' => $category->status,
                    'attribute' => $attribute,
                    'image' => !empty($category->logo_path) ? get_image_url([
                        'path' => $category->logo_path,
                        'op' => 'thumbnail',
                        'w' => 50,
                        'h' => 50,
                    ]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH,
                    'seq' => $category->seq,
                    'childCategory' => $childCategory,
                ];
            });
            $categoryList->setCollection($tmp);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data'  => [
                'categoryList' =>  $categoryList,
                'parentCategory' => $parentCategory,
                'currentCategory' => $currentCategory
            ],
        ]);
    }

    // public function getCategoryInfo(\App\Models\Category $category) {
    //     $resultCol = ['id', 'type', 'code', 'name', 'description', 'meta', 'parentAndGrandparent'];
    //     $result = $this->extractCategory($category, $resultCol);
    //     $languageList = $this->categoryService->getLanguage();
    //     $names = [];
    //     foreach ($languageList as $language) {
    //         $nameT = $category->getTranslation($language->code, 'name');
    //         $names['category'.ucfirst($language->code)] = $nameT;
    //     }
    //     $result['name'] = $names;

    //     return response()->json([
    //         'error' => EErrorCode::NO_ERROR,
    //         'data'  => $result,
    //     ]);
    // }

    public function saveCategory() {
        //$requestData = $request->validated();
        // $requestData['img'] = request('image');
        // $requestData['categoryId'] = request('id');
        // $requestData['type'] = request('type');
        // $requestData['name'] = request('name');
        // $requestData['parentCategoryId'] = request('parentCategoryId');
        // $requestData['numberOfAttribute'] = request('numberOfAttribute');
        $requestData = [
            'image' => request('image'),
            'categoryId' => request('id'),
            'type' => request('type'),
            'name' => request('name'),
            'parentCategoryId' => request('parentCategoryId'),
            'numberOfAttribute' => request('numberOfAttribute'),
        ];
        $errorStack = [
            'category' => null,
            'attribute' => [],
        ];
        $attributeNames = array(
            'name' => __('back/category.name'),
            'image' => __('back/category.image'),
        );
        $rules = array(
            'name' => 'required|max:60',
            'image' => $requestData['type'] == ECategoryType::ISSUE_REPORT || !empty($requestData['parentCategoryId']) || $requestData['categoryId'] ? '' : 'required'
        );
        $validator = Validator::make($requestData, $rules);
        $validator->setAttributeNames($attributeNames);
        if (empty($requestData['parentCategoryId'])) {
            $errorStack['category'] = $validator->errors();
            for ($i = 0; $i < $requestData['numberOfAttribute']; $i++) {
                $data = request('attribute' . $i);
                $attributeNames = array(
                    'attributeName' => __('back/category.attr_name'),
                    'valueName' => 'Giá trị',
                );
                $rules = array(
                    'attributeName' => 'required',
                    'valueName' => [
                        $data['valueType'] == ECategoryValueType::MULTI ? 'required' : false,
                        function($attr, $value, $fail) use($data) {
                        if (!empty($value)) {
                            foreach (explode(',', $value) as $val) {
                                if ($data['dataType'] == ECategoryDataType::NUMBER
                                    && !is_numeric($value)) {
                                    return $fail(__('back/category.error.value_number'));
                                }
                            }
                        }
                    }],
                );
                $validator1 = Validator::make($data, $rules);
                $validator1->setAttributeNames($attributeNames);
                array_push($errorStack['attribute'], $validator1->errors());
            }
        }
        if ($validator->errors()->any() || count($errorStack['attribute']) > 0 && count($errorStack['attribute'][0]) > 0) {
            return \Response::json([
                'error' => EErrorCode::ERROR,
                'errors' => empty($requestData['parentCategoryId']) ? $errorStack : $validator->errors()
            ]);
        }
        for ($i = 0; $i < $requestData['numberOfAttribute']; $i++) {
            $requestData['attribute' . $i] = request('attribute' . $i);
        }
        $result = $this->categoryService->saveCategory($requestData, auth()->id());
        if ($result['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($result);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.save-data-success2'),
        ]);
    }

    public function deleteCategory() {
        $result = $this->categoryService->deleteCategory(request('id'), auth()->id());
        if ($result['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($result);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.delete-data-success', [
                'objectName' => __('back/category.object_name'),
            ]),
        ]);
    }

    public function changePosition() {
        $this->categoryService->changePosition(request()->all());
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => 'Thay đổi vị trí thành công'
        ]);
    }
}
