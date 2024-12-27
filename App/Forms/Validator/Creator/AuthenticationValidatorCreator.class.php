<?php

declare(strict_types=1);

class AuthenticationValidatorCreator extends AbstractValidatorCreator
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
            'valid_email' => new EmailValidator($display, $inputValue, $ruleValue),
            'unique' => new UniqueValidator($display, $inputValue, $ruleValue, $this->model),
            'letter_number' => new PasswordLetterValidator($display, $inputValue, $ruleValue),
            'capital_letter' => new PasswordCapitalLetterValidator($display, $inputValue, $ruleValue),
            'digit' => new PasswordDigitValidator($display, $inputValue, $ruleValue),
            'small_letter' => new PasswordSmallLetterValidator($display, $inputValue, $ruleValue),
            'specialchar' => new PasswordSpecialcharValidator($display, $inputValue, $ruleValue),
            'white_space' => new PasswordWhitespaceValidator($display, $inputValue, $ruleValue),
            'match' => new PasswordMatchValidator($display, $inputValue, $ruleValue, $this->model, $this->inputFields),
            default => null
        };
    }
}