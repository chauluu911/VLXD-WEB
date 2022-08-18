<?php

namespace App\Http\Requests\Post;

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
            'title' => ValidatorHelper::commissionContentRule(['required' => true, 'max' => 150]),
            'content' => ValidatorHelper::commissionContentRule(['required' => true, 'max' => 999999]),
            'blob' => empty(request('url')) ? 'required' : '',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'title' => __('back/post.title'),
            'content' => __('back/post.content'),
            'blob' => __('back/post.blob'),
        ];
    }
}
