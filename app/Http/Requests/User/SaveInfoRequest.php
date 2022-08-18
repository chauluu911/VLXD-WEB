<?php

namespace App\Http\Requests\User;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use App\Enums\EUserType;

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
        // if (request()->has('avatar')) {
        //     return [];
        // }
        return [
            'affiliateCode' => request('userType') == EUserType::INTERNAL_USER
                ? '' : ValidatorHelper::affiliateCodeRule(),
            'name' => ValidatorHelper::nameRule(['required' => true]),
            'phone' => ValidatorHelper::phoneRule(['required' => true]),
            'email' => ValidatorHelper::emailRule(['required' => request('userType') == EUserType::INTERNAL_USER, 'max' => 150]),
            'address' => ValidatorHelper::address(['required' => true, 'max' => 999999999]),
            'role' => request('userType') == EUserType::INTERNAL_USER
                ? ValidatorHelper::arrayRule(['required' => true]) : ValidatorHelper::arrayRule(['required' => false]),
            'dob' => request('userType') == EUserType::NORMAL_USER
                ? ValidatorHelper::dateOfBirthRule(['required' => false]) :
                ValidatorHelper::dateOfBirthRule(['required' => true]),
            // 'image' => ValidatorHelper::nameRule(['required' => true]),
        ];
    }

    public function messages()
    {
        return [
            'dob.date_format' => ':attribute không đúng định dạng dd/mm/yyyy',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'affiliateCode' => __('back/user.affiliateCode'),
            'name' => __('back/user.name'),
            'phone' => __('back/user.phone'),
            'email' => __('back/user.email'),
            'dob' => __('back/user.dob'),
            'country' => __('back/user.country'),
            'address' => __('back/user.address'),
        ];
    }
}
