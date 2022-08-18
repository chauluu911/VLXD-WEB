<?php

namespace App\Rules;

use App\Enums\EStatus;
use App\Services\AreaService;
use Illuminate\Contracts\Validation\Rule;

class ValidArea implements Rule {
	private AreaService $areaService;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct() {
		$this->areaService = resolve('\App\Services\AreaService');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {
    	$area = $this->areaService->getById((int)$value);
    	return !empty($area) && $area->status === EStatus::ACTIVE;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.custom.not_valid', [
        	'attribute' => __('front/post.attributes.region')
		]);
    }
}
