<?php

namespace App\Rules;

use App\Enums\ECategoryType;
use Illuminate\Contracts\Validation\Rule;

class ValidCategoryType implements Rule {
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {
        return ECategoryType::isValid($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return __('validation.custom.not_valid', [
            'attribute' => __("front/post.attributes.category")
        ]);
    }
}
