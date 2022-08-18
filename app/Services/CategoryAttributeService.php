<?php


namespace App\Services;


use App\Enums\EErrorCode;
use App\Enums\ECategoryDataType;
use App\Enums\EStatus;
use App\Helpers\StringUtility;
use App\Models\CategoryAttribute;
use App\Repositories\CategoryAttributeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class CategoryAttributeService {
    public function __construct(CategoryAttributeRepository $categoryAttributeRepository) {
        $this->categoryAttributeRepository = $categoryAttributeRepository;
    }

    public function getById($id) {
        return $this->categoryAttributeRepository->getById($id);
    }

    public function getByOptions($options) {
        return $this->categoryAttributeRepository->getByOptions($options);
    }

    public function saveData($data, $loggedInUserId) {
        return DB::transaction(function() use ($data, $loggedInUserId) {
            if (!empty(Arr::get($data, 'numberOfAttribute'))) {
                for ($i = 0; $i < Arr::get($data, 'numberOfAttribute'); $i++) { 
                    $attribute = Arr::get($data, 'attribute' . $i);
                    if (!empty(Arr::get($attribute, 'id'))) {
                        $categoryAttribute = $this->getById(Arr::get($attribute, 'id'));
                    }else {
                        $categoryAttribute = new CategoryAttribute();
                    }
                    $categoryAttribute->category_id = Arr::get($data, 'categoryId');
                    $categoryAttribute->status = Arr::get($attribute, 'status');
                    $categoryAttribute->attribute_name = Arr::get($attribute, 'attributeName');
                    $categoryAttribute->value_type = Arr::get($attribute, 'valueType');
                    $categoryAttribute->enable_filter = Arr::get($attribute, 'enableFilter');
                    $categoryAttribute->data_type = Arr::get($attribute, 'dataType');
                    if (Arr::get($attribute, 'dataType') == ECategoryDataType::NUMBER) {
                        for ($j = 0; $j < count($arr); $j++) { 
                            $arr[$j] = (int)$arr[$j];
                        }  
                    }
                    if (!empty(Arr::get($attribute, 'valueName'))) {
                        $arr = explode(',', Arr::get($attribute, 'valueName'));
                        $categoryAttribute->value = [
                            'value' => $arr
                        ];
                    }
                    $categoryAttribute->save();
                }
            }
            return [
                'error' => EErrorCode::NO_ERROR,
            ];
        });
    }
}
