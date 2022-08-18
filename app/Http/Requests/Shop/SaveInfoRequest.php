<?php

namespace App\Http\Requests\Shop;

use App\Helpers\ValidatorHelper;
use App\Helpers\BusinessValidatorHelper;
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
        //dd(request()->all());
        return [
            'name' => ValidatorHelper::nameRule(['required' => true]),
            'phone' => BusinessValidatorHelper::shopPhoneRule(['required' => true]),
            'email' => ValidatorHelper::emailRule(['required' => false, 'max' => 150]),
            'country' => ValidatorHelper::nameRule(['required' => false]),
            'address' => ValidatorHelper::address(['required' => true, 'max' => 999999]),
            'description' => ValidatorHelper::nameRule(['required' => false]),
            'fb' => 'url',
            'zalo' => 'url',
            'areaId' => 'required',
            'avatar' => 'required',
            'identityCode' => '',
            'identityDate' => '',
            'identityPlace' => '',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'name' => __('front/shop.name'),
            'phone' => __('front/shop.phone'),
            'email' => __('front/shop.email'),
            'country' => __('front/shop.country'),
            'address' => __('front/shop.address'),
            'areaId' => 'Tỉnh, thành phố',
        ];
    }
}
