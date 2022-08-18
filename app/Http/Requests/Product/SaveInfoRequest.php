<?php

namespace App\Http\Requests\Product;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class SaveInfoRequest extends FormRequest {
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
        return [
            'id' => ValidatorHelper::nameRule(['required' => false]),
            'categoryId' => 'required',
            'type' => 'required',

            'unit' => 'required',
            'description' => 'required',
            'name' => 'required',
            'price' => ValidatorHelper::numberRule(['required' => true, 'min' => 1000]),
            'imageCount' => ValidatorHelper::numberRule(['required' => true, 'min' => 1]),
            //'videoCount' => ValidatorHelper::numberRule(['required' => true, 'min' => 1]),
            'videoCount' => '',
            'attribute.id' => ['required' => false],
            'attributeName' => function($attr, $value, $fail) {
                $errors = [];
                $haveError = false;
                foreach ($value as $index => $value) {
                    array_push($errors, $index);
                    if (empty($value)) {
                        $errors[$index] .= ' không có dữ liệu';
                        $haveError = true;
                    }
                }
                if ($haveError) {
                    return $fail($errors);
                }
            },
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'unit' => __('front/product.unit'),
            'description' => __('front/product.description'),
            'name' => __('front/product.name'),
            'price' => __('front/product.price'),
            'imageCount' => __('front/product.image'),
        ];
    }
}
