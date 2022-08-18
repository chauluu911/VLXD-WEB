<?php

namespace App\Http\Requests\Notification;

use App\Helpers\BusinessValidatorHelper;
use App\Helpers\ValidatorHelper;
use App\Enums\ENotificationScheduleTargetType;
use Illuminate\Foundation\Http\FormRequest;

class SaveNotificationScheduleRequest extends FormRequest {
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
        	'titleVi' => ValidatorHelper::nameRule(['required' => true, 'max' => 150]),
            'contentVi' => ValidatorHelper::descriptionRule(['required' => true]),
            'date' => ValidatorHelper::dateRule(['required' => true, 'format' => 'd/m/Y']),
            'time' => ValidatorHelper::timeRule(['required' => true, 'format' => 'H:i:s']),
			'targetType' => ValidatorHelper::numberRule(['required' => true]),
            'targetList' => ValidatorHelper::arrayRule(['required' => request('targetType') ==ENotificationScheduleTargetType::SPECIFIC_CUSTOMER ? true : false]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
        return [
            'titleVi' => __('back/notification_schedule.attributes.title'),
            'contentVi' => __('back/notification_schedule.attributes.content'),
            'date' => __('back/notification_schedule.attributes.date'),
            'time' => __('back/notification_schedule.attributes.time'),
			'targetType' => __('back/notification_schedule.attributes.target_type'),
            'targetList' => __('back/notification_schedule.attributes.target_type'),
        ];
    }

    public function messages() {
    	$attributes = $this->attributes();
    	return [
    		'targetList.required_if' => __('validation.custom.pick_required', [
    			'attribute' => __('back/notification_schedule.attributes.target_type')
			]),
		];
	}
}
