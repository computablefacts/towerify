<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AtLeastOneUppercaseLetter implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtolower($value) === $value) {
            $fail('The :attribute must contain at least one uppercase letter.');
        }
    }
}
