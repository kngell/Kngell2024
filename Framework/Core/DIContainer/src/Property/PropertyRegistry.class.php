<?php

declare(strict_types=1);

readonly class PropertyRegistry
{
    private const NAME_SEPARATOR = '.';
    private array $properties;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public function getPropertiesByName(string $name) : string|int|float|array
    {
        $results = $this->tryToGetPropertiesByName($name);
        if ($results === null) {
            throw new PropertyNotFoundException("Could not find property with name : $name");
        }
        return $results;
    }

    public function getPropertiesByNameOrDefault(string $name, string|int|float|array|null $defaultValues) : string|int|float|array
    {
        $results = $this->tryToGetPropertiesByName($name);
        if ($results === null) {
            return $defaultValues;
        }
        return $results;
    }

    private function tryToGetPropertiesByName(string $name) : string|int|float|array|null
    {
        $keys = explode(self::NAME_SEPARATOR, $name);
        $currentValue = $this->properties;
        foreach ($keys as $key) {
            if (! array_key_exists($key, $currentValue)) {
                return null;
            }

            $currentValue = $currentValue[$key];
        }

        return $currentValue;
    }
}