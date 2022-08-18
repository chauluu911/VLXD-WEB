<?php

namespace App\Http\Requests\Config;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;

class SaveConfigRequest extends FormRequest {
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
            'textValue' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'textValue' => 'Nội dung',
        ];
    }
}
