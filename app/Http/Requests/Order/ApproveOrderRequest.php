<?php

namespace App\Http\Requests\Order;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;

class ApproveOrderRequest extends FormRequest {
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
            'code' => 'required',
            'shippingFee' => ValidatorHelper::numberRule(['required' => true, 'min' => 0]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'code' =>__('front/order.code'),
            'shippingFee'=> __('front/order.shipping_fee')
        ];
    }
}
