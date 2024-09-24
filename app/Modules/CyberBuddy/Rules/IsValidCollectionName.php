<?php

namespace App\Modules\CyberBuddy\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidCollectionName implements ValidationRule
{
    public static function test(?string $name): bool
    {
        $regex = '/^[a-z]+[-a-z0-9]*$/';
        return $name && preg_match($regex, $name) > 0;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!self::test($value)) {
            $fail('The :attribute is not a valid collection name.');
        }
    }
}
