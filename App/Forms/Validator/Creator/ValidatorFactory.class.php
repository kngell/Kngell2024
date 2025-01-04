<?php

declare(strict_types=1);

class ValidatorCreatorFactory
{
    public static function create(string $ruleName, ?Model $model, array $inputFields = []) : AbstractValidatorCreator
    {
        return match (true) {
            in_array($ruleName, ['register', 'login', 'forgot-pw', 'reset-pw', 'profile']) => new AuthenticationValidatorCreator($model, $inputFields),
            $ruleName === 'postRules' => new PostValidatorCreator($model, $inputFields)
        };
    }
}