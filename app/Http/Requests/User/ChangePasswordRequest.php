<?php

namespace App\Http\Requests\User;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ChangePasswordRequest extends FormRequest {
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
            'oldPassword' => ValidatorHelper::nameRule(['required' => true]),
            'newPassword' => ValidatorHelper::passwordRule(['required' => true]),
            'confirmPassword' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'oldPassword' => __('back/changePassword.old_password'),
            'newPassword' => __('back/changePassword.new_password'),
            'confirmPassword' => __('back/changePassword.re_enter_new_password'),
        ];
    }
}
