<?php

namespace App\Modules\AdversaryMeter\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidDomain implements ValidationRule
{
    public static function test(?string $asset): bool
    {
        $domainRegex = '/^(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}\.?|[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)$/';
        return $asset && preg_match($domainRegex, $asset) > 0;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!self::test($value)) {
            $fail('The :attribute is not a valid domain.');
        }
    }
}
