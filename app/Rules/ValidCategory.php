<?php

namespace App\Rules;

use App\Enums\EStatus;
use App\Services\AreaService;
use App\Services\CategoryService;
use Illuminate\Contracts\Validation\Rule;

class ValidCategory implements Rule {
	private CategoryService $categoryService;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct() {
		$this->categoryService = resolve('\App\Services\CategoryService');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {
    	$category = $this->categoryService->getById((int)$value);
    	return !empty($category) && $category->status === EStatus::ACTIVE;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.custom.not_valid', [
        	'attribute' => __('front/post.attributes.category')
		]);
    }
}
