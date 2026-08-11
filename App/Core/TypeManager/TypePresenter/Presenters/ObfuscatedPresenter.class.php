<?php

declare(strict_types=1);

final class ObfuscatedPresenter implements TypePresenterInterface
{
    public function __construct(
        private ObfuscatorManager $obfuscatorManager,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if ($property === null) {
            return false;
        }

        $format = $this->getDisplayFormat($property);
        return $format?->obfuscate === true;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        $format = $this->getDisplayFormat($property);

        if ($value === null) {
            return $format?->nullPlaceholder ?? '';
        }

        if ($format?->obfuscate !== true) {
            return (string) $value;
        }

        $strategyName = $format->obfuscationStrategy ?? ObfuscatorConfig::getStrategyForModel($property?->getDeclaringClass()->getShortName() ?? '');

        if (is_int($value) || is_numeric($value)) {
            $obfuscated = $this->obfuscatorManager->strategy($strategyName)->obfuscate((int) $value);
            return ObfuscationUtils::addPrefix($obfuscated, $strategyName);
        }

        if (is_string($value) && ObfuscationUtils::isObfuscated($value)) {
            return $value;
        }

        return (string) $value;
    }

    private function getDisplayFormat(?ReflectionProperty $property): ?DisplayFormat
    {
        if ($property === null) {
            return null;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}