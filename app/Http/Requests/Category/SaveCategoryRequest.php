<?php

namespace App\Http\Requests\Category;

use App\Helpers\ValidatorHelper;
use App\Rules\ValidCategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class SaveCategoryRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        //dd(request()->all());
        return [
            'name' => ValidatorHelper::nameRule(['required' => true, 'max' => 60]),
            // 'numberOfAttribute' => function($attribute, $value, $fail) {
            //     // $error = null;
            //     // for ($i = 0; $i < $value; $i++) {
            //     //     $data = request('attribute' . $i);
            //     //     if (empty($data->attributeName)) {
            //     //         // $error['attributeName' . $i] = 'Vui lòng nhập tên thuộc tính';
            //     //         $error = 'attribute' . $i . "-attributeName;Vui lòng nhập tên thuộc tính.";
            //     //     }
            //     //     if (empty($data->valueName)) {
            //     //         // $error['valueName' . $i] = 'Vui lòng nhập giá trị';
            //     //         $error .= 'attribute' . $i . "-valueName;Vui lòng nhập giá trị.";
            //     //     }
            //     // }
            //     // if (!empty($error)) {
            //     //     //dd($error);
            //     //     $fail($error);
            //     // }

            //     for ($i = 0; $i < $value; $i++) {
            //         $error = [];
            //         $data = request('attribute' . $i);
            //         if (empty($data->attributeName)) {
            //             $error['attributeName' . $i] = 'Vui lòng nhập tên thuộc tính';
            //         }
            //         if (empty($data->valueName)) {
            //             $error['valueName' . $i] = 'Vui lòng nhập giá trị';
            //         }
                   
            //         if (!empty($error)) {
            //             $fail($error);
            //         }
            //     }
            // },
        ];
    }

    public function attributes() {
        return [
            'name' => __('back/category.attributes.name'),
        ];
    }
}
