<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidVietnamPhoneNumber implements Rule {
    private $checkPbxPhoneNumber;

    /**
     * Create a new rule instance.
     *
     * @param bool $checkPbxPhoneNumber
     */
    public function __construct($checkPbxPhoneNumber = false) {
        $this->checkPbxPhoneNumber = $checkPbxPhoneNumber;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {
//        $parsedNumber = preg_replace('/\D/', '', $value);
        $regex = '/^0(3[23456789]|5[2689]|7[06789]|8[123456789]|9[012346789])\d{7}$/';
        $regex2 =  '/^0(2[0123456789])\d{8}$/'; //regex cho số điện thoại bàn
        $pbxRegex = '/^1[89]00\d{4}$/';
        return (bool)preg_match($regex, $value)
            || $this->checkPbxPhoneNumber && (bool)preg_match($pbxRegex, $value)
            || (bool)preg_match($regex2, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return 'Số điện thoại không hợp lệ';
    }
}
