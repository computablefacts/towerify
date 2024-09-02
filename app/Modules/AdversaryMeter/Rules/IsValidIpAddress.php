<?php

namespace App\Modules\AdversaryMeter\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidIpAddress implements ValidationRule
{
    public static function test(string $asset): bool
    {
        return filter_var($asset, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!self::test($value)) {
            $fail('The :attribute is not a valid IP address.');
        }
    }
}
