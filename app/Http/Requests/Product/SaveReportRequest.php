<?php

namespace App\Http\Requests\Product;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class SaveReportRequest extends FormRequest {
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
            'content' => 'required',
            'reportChoosed' => 'required',
            'parentId' => 'required'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'content' => 'Nội dung',
            'reportChoosed' => 'Danh mục',
            'parentId' => 'Danh mục con',
        ];
    }
}
