<?php

namespace App\Http\Requests\Config;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;

class SaveConfigWalletRequest extends FormRequest {
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
            'cost' => ValidatorHelper::numberRule(['required' => true, 'min' => 1000]),
            'point' => ValidatorHelper::numberRule(['required' => true, 'min' => 0]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'cost' => __('back/config.score.cost'),
            'scoreExchangeValue' => __('back/config.score.exchangeValue'),
        ];
    }
}
