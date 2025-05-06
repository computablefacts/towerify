<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AtLeastOneLowercaseLetter implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (1 !== preg_match('/[a-z]/', $value)) {
            $fail(__('validation.password.at_least_one_lowercase_letter'));
        }
    }
}
