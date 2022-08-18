<?php

namespace App\Http\Requests\Config;

use App\Helpers\ValidatorHelper;
use Illuminate\Foundation\Http\FormRequest;

class SaveShopLevelConfigRequest extends FormRequest {
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
            'id' => ['required' => false],
            'name' => 'required',
            'numProduct' => ValidatorHelper::numberRule(['required' => false, 'min' => 0, 'max' => 999999999]),
            'numImageInProduct' => ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'numPushProductInMonth' => ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'priorityShowSearchProduct' => 'required',
            'enableCreateNotification' => 'required',
            'videoIntroduceAllowUploadVideo' => 'required',
            'videoIntroduceUploadTime' =>  ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'videoIntroduceNumVideo' =>  ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'avatarAllowUploadVideo' => 'required',
            'avatarUploadTime' =>  ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'avatarType' =>  ValidatorHelper::numberRule(['required' => true, 'min' => 1, 'max' => 999999999]),
            'videoInProductAllowUploadVideo' => 'required',
            'videoInProductUploadTime' =>  ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'bannerInHomeAllowUploadBanner' => 'required',
            'bannerInHomeNumDayShow' => ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
            'imageIntroduceAllowUpdateImage' => 'required',
            'imageIntroduceNumImage' => ValidatorHelper::numberRule(['required' => true, 'min' => 0, 'max' => 999999999]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'name' => __('back/shop_level_config.name'),
            'id' => __('back/shop_level_config.id'),
            'numProduct' => __('back/shop_level_config.numProduct'),
            'numImageInProduct' => __('back/shop_level_config.numImageInProduct'),
            'numPushProductInMonth' => __('back/shop_level_config.numPushProductInMonth'),
            'priorityShowSearchProduct' => __('back/shop_level_config.priorityShowSearchProduct'),
            'enableCreateNotification' => __('back/shop_level_config.enableCreateNotification'),
            'videoIntroduceAllowUploadVideo' => __('back/shop_level_config.videoIntroduce.allow_upload_video'),
            'videoIntroduceUploadTime' => __('back/shop_level_config.videoIntroduce.upload_time'),
            'videoIntroduceNumVideo' => __('back/shop_level_config.videoIntroduce.num_video'),
            'avatarAllowUploadVideo' => __('back/shop_level_config.avatar.allow_upload_video'),
            'avatarUploadTime' => __('back/shop_level_config.avatar.upload_time'),
            'avatarType' => __('back/shop_level_config.avatar.type'),
            'videoInProductAllowUploadVideo' => __('back/shop_level_config.videoInProduct.allow_upload_video'),
            'videoInProductUploadTime' => __('back/shop_level_config.videoInProduct.upload_time'),
            'bannerInHomeAllowUploadBanner' => __('back/shop_level_config.bannerInHome.allow_upload_banner'),
            'bannerInHomeNumDayShow' => __('back/shop_level_config.bannerInHome.num_day_show'),
            'imageIntroduceAllowUpdateImage' => __('back/shop_level_config.imageIntroduce.allow_upload_image'),
            'imageIntroduceNumImage' => __('back/shop_level_config.imageIntroduce.num_image'),
        ];
    }
}
