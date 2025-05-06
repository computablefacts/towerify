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
            $requirements[] = __('validation.requirement.min', ['min' => $this->min()]);
        }
        if ($this->hasMax()) {
            $requirements[] = __('validation.requirement.max', ['max' => $this->max()]);
        }
        if ($this->mixedCase()) {
            $requirements[] = __('validation.requirement.mixed');
        }
        if ($this->letters()) {
            $requirements[] = __('validation.requirement.letters');
        }
        if ($this->numbers()) {
            $requirements[] = __('validation.requirement.numbers');
        }
        if ($this->symbols()) {
            $requirements[] = __('validation.requirement.symbols');
        }
        if ($this->hasCustomRule(OnlyLettersAndDigits::class)) {
            $requirements[] = __('validation.requirement.only_letters_and_digits');
        }
        if ($this->hasCustomRule(AtLeastOneLetter::class)) {
            $requirements[] = __('validation.requirement.letters');
        }
        if ($this->hasCustomRule(AtLeastOneDigit::class)) {
            $requirements[] = __('validation.requirement.numbers');
        }
        if ($this->hasCustomRule(AtLeastOneUppercaseLetter::class)) {
            $requirements[] = __('validation.requirement.at_least_one_uppercase_letter');
        }
        if ($this->hasCustomRule(AtLeastOneLowercaseLetter::class)) {
            $requirements[] = __('validation.requirement.at_least_one_lowercase_letter');
        }

        return array_unique($requirements);
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