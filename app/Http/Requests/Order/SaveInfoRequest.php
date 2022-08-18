<?php

namespace App\Http\Requests\Order;

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
        return [
            'name' => 'required|max:60',
            'phone' => ValidatorHelper::phoneRule(['required' => true]),
            'address' => ValidatorHelper::address(['required' => true, 'max' => 150]),
            'paymentMethod' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'name' => __('back/user.name'),
            'phone' => __('back/user.phone'),
            'paymentMethod' => 'Vui lòng chọn hình thức thanh toán',
            'address' => __('back/user.address'),
        ];
    }
}
