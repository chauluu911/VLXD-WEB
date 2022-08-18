<?php

namespace App\Rules;

use App\Enums\EStatus;
use App\Services\CountryService;
use Illuminate\Contracts\Validation\Rule;

class ValidCountry implements Rule {
	private CountryService $countryService;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct() {
		$this->countryService = resolve('\App\Services\CountryService');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {
    	$country = $this->countryService->getById((int)$value);
    	return !empty($country);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.custom.not_valid', [
        	'attribute' => __('front/post.attributes.country')
		]);
    }
}
