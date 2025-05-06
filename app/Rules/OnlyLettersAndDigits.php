<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OnlyLettersAndDigits implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (1 !== preg_match('/^[0-9a-z]+$/i', $value)) {
            $fail(__('validation.password.only_letters_and_digits'));
        }
    }
}
