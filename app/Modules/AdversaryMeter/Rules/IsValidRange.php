<?php

namespace App\Modules\AdversaryMeter\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidRange implements ValidationRule
{
    public static function test(string $asset): bool
    {
        $parts = explode('/', $asset);

        if (count($parts) === 2) {

            $ip = $parts[0];
            $subnet = $parts[1];

            if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                return is_numeric($subnet) && $subnet >= 0 && $subnet <= 32;
            }
        }
        return false;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!self::test($value)) {
            $fail('The :attribute is not a valid range of IP.');
        }
    }
}
