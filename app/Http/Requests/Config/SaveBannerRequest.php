<?php

namespace App\Http\Requests\Config;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use App\Enums\Banner\EBannerActionType;

class SaveBannerRequest extends FormRequest {
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
            'file' => function($attribute, $value, $fail) use ($data) {
                if (empty($data['id'])
                    && (empty($value) || !($value instanceof UploadedFile))) {
                    $fail(__('validation.custom.pick_required', [
                        'attribute' => __('back/banner.msg.file')
                    ]));
                }
            },
            'link' => $data['actionType'] == EBannerActionType::OPEN_WEBSITE ? 'required|url' : '',
            'platform' => 'required',
            'type' => 'required',
            'ratio' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'link' => __('back/banner.msg.link'),
            'platform' => __('back/banner.msg.platform'),
            'type' => __('back/banner.msg.type'),
            'ratio' => __('back/banner.msg.ratio'),
        ];
    }
}
