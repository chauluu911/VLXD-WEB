<?php

namespace App\Services;
use App\Constant\CacheKey;
use App\Constant\DefaultConfig;
use App\Enums\ECustomerType;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EUserType;
use App\Enums\EStoreFileType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Repositories\CategoryRepository;
use App\Enums\EStatus;
use Illuminate\Support\Carbon;
use App\Models\Category;
use App\Enums\ECategoryType;
use App\Helpers\FileUtility;
use App\Services\CategoryAttributeService;

class CategoryService {

	protected CategoryRepository $categoryRepository;
    protected CategoryAttributeService $categoryAttributeService;

	public function __construct(CategoryRepository $categoryRepository, CategoryAttributeService $categoryAttributeService) {
		$this->categoryRepository = $categoryRepository;
        $this->categoryAttributeService = $categoryAttributeService;
    }

	/**
	 * @param $id
	 * @return \App\Models\Category
	 */
	public function getById($id) {
    	return $this->categoryRepository->getById($id);
    }

    public function getByOptions($options) {
        return $this->categoryRepository->getByOptions($options);
    }

    public function getChildCategory($options) {
        $childCategory = null;
        $result = $this->categoryRepository->getByOptions($options);
        if (count($result) > 0) {
            foreach ($result as $index => $value) {
                if ($index != 0) {
                    $childCategory .=  ', ' . $value->name;
                } else {
                    $childCategory = $value->name;
                }
            }
        }
        return $childCategory;
    }

    public function getCategoryListForHome($forceReset = false) {
		$categoryList = cache()->get(CacheKey::HOME_CATEGORY_LIST);
		if (empty($categoryList) || $forceReset) {
			$categoryList = $this->getByOptions([
				'orderBy' => 'seq',
				'orderDirection' => 'asc',
				'type' => ECategoryType::PRODUCT_CATEGORY,
			])->map(fn($category) => [
				'id' => $category->id,
                'seq' => $category->seq,
				'name' => $category->name,
				'logo_path' => !empty($category->logo_path) ? get_image_url([
					'path' => $category->logo_path,
					'op' => 'thumbnail',
					'w' => 250,
					'h' => 250,
				]) : DefaultConfig::FALLBACK_IMAGE_PATH,
			])->toArray();
			cache()->put(CacheKey::HOME_CATEGORY_LIST, $categoryList, now()->addDay());
		}
		return $categoryList;
	}

    public function saveCategory($data, $loggedInUserId) {
        $fileToDeleteIfError = [];
        try {
            $result = DB::transaction(function() use ($data, $loggedInUserId, &$fileToDeleteIfError) {
                $id = Arr::get($data, 'categoryId');
                if ($id) {
                    $category = $this->getById($id);
                    if (empty($category)) {
                        return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                    }
                    //$category->updated_by = $loggedInUserId;
                } else {
                    $category = new Category();
                    //$category->created_by = $loggedInUserId;
                    $lastCategory = $this->categoryRepository->getByOptions([
                        'orderBy' => 'seq',
                        'orderDirection' => 'desc',
                        'type' => Arr::get($data, 'type'),
                        'parent_category_id' => Arr::get($data, 'parentCategoryId'),
                        'first' => true,
                    ]);
                    if (empty($lastCategory)) {
                        $category->seq = 1;
                    } else {
                        $category->seq = $lastCategory->seq + 1;
                    }
                }
                $category->name = Arr::get($data, 'name', $category->name);
                $category->status = EStatus::ACTIVE;
                $category->type = Arr::get($data, 'type', $category->type);
                $category->parent_category_id = Arr::get($data, 'parentCategoryId', $category->parent_category_id);

                $logo = Arr::get($data, 'image');

                if (!empty($logo)) {
                    $relativePath = FileUtility::storeFile(EStoreFileType::CATEGORY_LOGO, $logo);
                    FileUtility::removeFiles([$category->logo_path]);
                    $category->logo_path = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                }
                $category->save();
                $data['categoryId'] = $category->id;
                $saveAttribute = $this->categoryAttributeService->saveData($data, $loggedInUserId);

                return [
                    'error' => EErrorCode::NO_ERROR,
                    'category' => $category,
                ];
            });
            if ($result['error'] == EErrorCode::NO_ERROR && Arr::get($data, 'type') == ECategoryType::PRODUCT_CATEGORY) {
            	$this->getCategoryListForHome(true);
			}
            return [
                'error' => EErrorCode::NO_ERROR,
            ];
        } catch (\Exception $e) {
            FileUtility::removeFiles($fileToDeleteIfError);
            throw $e;
        }
    }

    public function changePosition($data) {
        $currentCategory = $this->getById(Arr::get($data, 'id'));
        if (empty($currentCategory)) {
            return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
        }
        $category = $this->getByOptions([
            'parent_category_id' => Arr::get($data, 'parentCategoryId'),
            'type' => Arr::get($data, 'type'),
            'orderBy' => 'seq',
            'orderDirection' => 'asc',
            'status' => EStatus::ACTIVE
        ]);
        if (count($category) > 0) {
            foreach ($category as $index => $value) {
                if(Arr::get($data, 'id') == $value->id) {
                    if (Arr::get($data, 'position') == 'up') {
                        $tmp = $value->seq;
                        $value->seq = $category[$index - 1]->seq;
                        $category[$index - 1]->seq = $tmp;
                        $category[$index - 1]->save();
                        $category[$index]->save();
                    } else {
                        $tmp = $value->seq;
                        $value->seq = $category[$index + 1]->seq;
                        $category[$index + 1]->seq = $tmp;
                        $category[$index + 1]->save();
                        $category[$index]->save();
                    }
                }
            }
        }
        $this->getCategoryListForHome(true);
        return ['error' => EErrorCode::ERROR];
    }

    public function deleteCategory($id, $loggedInUserId) {
        $result = DB::transaction(function() use ($id, $loggedInUserId) {
            $category = $this->getById($id);
            if (empty($category)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            if ($category->status == EStatus::DELETED) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.data-was-deleted')];
            }
            $category->deleted_by = $loggedInUserId;
            $category->deleted_at = now()->timezone(config('app.timezone'))
                ->format(EDateFormat::MODEL_DATE_FORMAT);
            $category->status = EStatus::DELETED;
            $childCategories = $category->childCategories;
            foreach ($childCategories as $childCategory) {
                $childCategory->deleted_by = $loggedInUserId;
                $childCategory->deleted_at = now()->timezone(config('app.timezone'))
                    ->format(EDateFormat::MODEL_DATE_FORMAT);
                $childCategory->status = EStatus::DELETED;
                $childCategory->save();
            }
            $category->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
        if ($result['error'] == EErrorCode::NO_ERROR) {
        	$this->getCategoryListForHome(true);
		}
        return $result;
    }
}
