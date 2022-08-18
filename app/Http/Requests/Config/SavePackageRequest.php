<?php

namespace App\Http\Requests\Config;

use App\Enums\ESubscriptionPriceType;
use App\Enums\EUserType;
use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;

class SavePackageRequest extends FormRequest {
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
        $data = request()->all();
        return [
            'id' => ['required' => false],
            'type' => ValidatorHelper::numberRule(['required' => true]),
            'name' => ValidatorHelper::nameRule(['required' => true]),
            'price' => ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'numDay' => ValidatorHelper::numberRule(['required' => false, 'min' => 1, 'max' => 999]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'name' => __('back/package.name'),
            'price' => __('back/package.price'),
            'numDay' => __('back/package.num_day'),
        ];
    }
}
