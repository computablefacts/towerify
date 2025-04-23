<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidTag implements ValidationRule
{
    public static function test(?string $tag): bool
    {
        return $tag && preg_match("#^[a-z0-9]+[-_/a-z0-9]*$#", $tag);
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!self::test($value)) {
            $fail('The :attribute is not a valid tag.');
        }
    }
}
