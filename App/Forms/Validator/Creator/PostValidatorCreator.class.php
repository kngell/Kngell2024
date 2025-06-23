<?php

declare(strict_types=1);

class PostValidatorCreator extends AbstractValidatorCreator
{
    public function __construct(private ?Model $model, private array $inputFields)
    {
    }

    public function create(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue) : ?AbstractValidator
    {
        return match ($ruleName) {
            'required' => new RequiredValidator($display, $inputValue, $ruleValue),
            'min' => new MinValidator($display, $inputValue, $ruleValue),
            'max' => new MaxValidator($display, $inputValue, $ruleValue),
            'stripTag' => new StriptagValidator($display, $inputValue, $ruleValue),
            default => null
        };
    }
}
