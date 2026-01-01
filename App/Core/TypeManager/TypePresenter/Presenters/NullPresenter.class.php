<?php

declare(strict_types=1);

class NullPresenter implements TypePresenterInterface
{
    private string $defaultPlaceholder;

    public function __construct(string $defaultPlaceholder = '')
    {
        $this->defaultPlaceholder = $defaultPlaceholder;
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value === null;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        // Try to get placeholder from property attribute first
        if ($property !== null) {
            $placeholder = $this->getPlaceholderFromAttribute($property);
            if ($placeholder !== null) {
                return $placeholder;
            }
        }

        // Use default placeholder
        return $this->defaultPlaceholder;
    }

    private function getPlaceholderFromAttribute(ReflectionProperty $property): ?string
    {
        $attributes = $property->getAttributes(DisplayFormat::class);
        if (!empty($attributes)) {
            $format = $attributes[0]->newInstance();
            if (isset($format->nullPlaceholder)) {
                return $format->nullPlaceholder;
            }
        }

        return null;
    }
}