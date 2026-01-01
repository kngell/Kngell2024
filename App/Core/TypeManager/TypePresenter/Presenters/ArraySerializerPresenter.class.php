<?php

declare(strict_types=1);
class ArraySerializerPresenter implements TypePresenterInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_array($value);
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        // For serialization, we need to preserve the array structure
        $result = [];
        foreach ($value as $key => $item) {
            // Recursively process array items
            if (is_array($item)) {
                $result[$key] = $this->display($item, $property, $regionContext);
            } else {
                // Let the factory handle other types
                $result[$key] = $item;
            }
        }
        return $result;
    }
}