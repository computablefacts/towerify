<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidAsset implements ValidationRule
{
    public static function test(?string $asset): bool
    {
        return IsValidDomain::test($asset)
            || IsValidIpAddress::test($asset)
            || IsValidRange::test($asset);
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!self::test($value)) {
            $fail('The :attribute is not a valid asset.');
        }
    }
}
