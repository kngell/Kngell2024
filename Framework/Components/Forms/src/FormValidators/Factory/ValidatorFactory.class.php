<?php

declare(strict_types=1);

final class ValidatorFactory
{
    public static function create(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue) : ValidatorInterface
    {
        return match ($ruleName) {
            'required' => new RequiredValidator($display, $inputValue, $ruleValue),
            'min' => new MinValidator($display, $inputValue, $ruleValue),
            'max' => new MaxValidator($display, $inputValue, $ruleValue),
        };
    }
}
