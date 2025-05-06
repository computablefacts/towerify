<?php

namespace App\Helpers;

use App\Rules\AtLeastOneDigit;
use App\Rules\AtLeastOneLetter;
use App\Rules\AtLeastOneLowercaseLetter;
use App\Rules\AtLeastOneUppercaseLetter;
use App\Rules\OnlyLettersAndDigits;
use Illuminate\Validation\Rules\Password;
use ReflectionClass;

class PasswordRequirements
{
    private Password $password;

    public function __construct(Password $password)
    {
        $this->password = $password;
    }

    public function getRequirements(): array
    {
        $requirements = [];

        if ($this->hasMin()) {
            $requirements['min'] = [];
            $requirements['min']['text'] = __('validation.requirement.min', ['min' => $this->min()]);
            $requirements['min']['condition'] = 'passwordField.value.length >= ' . $this->min();;
        }
        if ($this->hasMax()) {
            $requirements['max'] = [];
            $requirements['max']['text'] = __('validation.requirement.max', ['max' => $this->max()]);
            $requirements['max']['condition'] = 'passwordField.value.length <= ' . $this->max();
        }
        if ($this->mixedCase()) {
            $requirements['mixed'] = [];
            $requirements['mixed']['text'] = __('validation.requirement.mixed');
            $requirements['mixed']['condition'] = 'passwordField.value.match(/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u)';
        }
        if ($this->letters()) {
            $requirements['letters'] = [];
            $requirements['letters']['text'] = __('validation.requirement.letters');
            $requirements['letters']['condition'] = 'passwordField.value.match(/\p{L}/u)';
        }
        if ($this->numbers()) {
            $requirements['numbers'] = [];
            $requirements['numbers']['text'] = __('validation.requirement.numbers');
            $requirements['numbers']['condition'] = 'passwordField.value.match(/\p{N}/u)';
        }
        if ($this->symbols()) {
            $requirements['symbols'] = [];
            $requirements['symbols']['text'] = __('validation.requirement.symbols');
            $requirements['symbols']['condition'] = 'passwordField.value.match(/\p{Z}|\p{S}|\p{P}/u)';
        }
        if ($this->hasCustomRule(OnlyLettersAndDigits::class)) {
            $requirements['only_letters_and_digits'] = [];
            $requirements['only_letters_and_digits']['text'] = __('validation.requirement.only_letters_and_digits');
            $requirements['only_letters_and_digits']['condition'] = 'passwordField.value.match(/^[0-9a-z]+$/i)';
        }
        if ($this->hasCustomRule(AtLeastOneLetter::class)) {
            $requirements['at_least_one_letter'] = [];
            $requirements['at_least_one_letter']['text'] = __('validation.requirement.letters');
            $requirements['at_least_one_letter']['condition'] = 'passwordField.value.match(/[a-z]/i)';
        }
        if ($this->hasCustomRule(AtLeastOneDigit::class)) {
            $requirements['at_least_one_digit'] = [];
            $requirements['at_least_one_digit']['text'] = __('validation.requirement.numbers');
            $requirements['at_least_one_digit']['condition'] = 'passwordField.value.match(/[0-9]/)';
        }
        if ($this->hasCustomRule(AtLeastOneUppercaseLetter::class)) {
            $requirements['at_least_one_uppercase_letter'] = [];
            $requirements['at_least_one_uppercase_letter']['text'] = __('validation.requirement.at_least_one_uppercase_letter');
            $requirements['at_least_one_uppercase_letter']['condition'] = 'passwordField.value.match(/[A-Z]/)';
        }
        if ($this->hasCustomRule(AtLeastOneLowercaseLetter::class)) {
            $requirements['at_least_one_lowercase_letter'] = [];
            $requirements['at_least_one_lowercase_letter']['text'] = __('validation.requirement.at_least_one_lowercase_letter');
            $requirements['at_least_one_lowercase_letter']['condition'] = 'passwordField.value.match(/[a-z]/)';
        }

        return $requirements;
    }

    private function hasMin(): bool
    {
        return $this->min() !== null;
    }

    private function min()
    {
        return $this->getPasswordValue('min');
    }

    private function hasMax(): bool
    {
        return $this->max() !== null;
    }

    private function max()
    {
        return $this->getPasswordValue('max');
    }

    private function mixedCase(): bool
    {
        return (bool)$this->getPasswordValue('mixedCase');
    }

    private function letters(): bool
    {
        return (bool)$this->getPasswordValue('letters');
    }

    private function numbers(): bool
    {
        return (bool)$this->getPasswordValue('numbers');
    }

    private function symbols(): bool
    {
        return (bool)$this->getPasswordValue('symbols');
    }

    private function customRules(): array
    {
        return (array)$this->getPasswordValue('customRules');
    }

    private function hasCustomRule(string $className): bool
    {
        foreach ($this->customRules() as $rule) {
            if ($rule instanceof $className) {
                return true;
            }
        }
        return false;
    }

    private function getPasswordValue(string $propertyName)
    {
        $reflection = new ReflectionClass($this->password);
        try {
            $property = $reflection->getProperty($propertyName);
        } catch (\ReflectionException $e) {
            return null;
        }

        return $property->getValue($this->password);
    }
}