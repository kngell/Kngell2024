<?php

declare(strict_types=1);

final class ValidatorCreatorFactory
{
    private const CREATOR_MAP = [
        'register' => UserValidatorCreator::class,
        'login' => UserValidatorCreator::class,
        'forgot-pw' => UserValidatorCreator::class,
        'reset-pw' => UserValidatorCreator::class,
        'profile' => UserValidatorCreator::class,
        'postRules' => PostValidatorCreator::class,
        'productRules' => ProductValidatorCreator::class,
    ];

    public static function create(string $ruleName, ?Model $model, array $inputFields, ValidationConfig $config): AbstractValidatorCreator
    {
        if (!isset(self::CREATOR_MAP[$ruleName])) {
            return new GenericValidatorCreator($model, $inputFields, $config);
        }

        $creatorClass = self::CREATOR_MAP[$ruleName];
        return new $creatorClass($model, $inputFields, $config);
    }

    public static function get(string $ruleName, ?Model $model, array $inputFields = []): AbstractValidatorCreator
    {
        $className = FileSearchManager::get(APP . 'Forms' . DS . 'Validator', ucfirst($ruleName) . 'ValidatorCreator.class.php');

        if (!class_exists($className)) {
            return new GenericValidatorCreator($model, $inputFields, new ValidationConfig());
        }

        return new $className($model, $inputFields);
    }

    /**
     * Check if a specific creator exists for a rule set.
     */
    public static function hasSpecificCreator(string $ruleName): bool
    {
        return isset(self::CREATOR_MAP[$ruleName]);
    }

    public static function getCreatorMap(): array
    {
        return self::CREATOR_MAP;
    }
}
