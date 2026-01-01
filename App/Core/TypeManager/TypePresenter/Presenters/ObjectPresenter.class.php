<?php

declare(strict_types=1);

class ObjectPresenter implements TypePresenterInterface
{
    public function __construct(
        private ?TypePresenterFactory $presenterFactory = null,
        private bool $deepInspect = false,
        private int $maxDepth = 2,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_object($value) && !$value instanceof DateTimeInterface;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!is_object($value)) {
            return (string) $value;
        }

        // Check for special object types that might have string representations
        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        // Check for display format preferences
        $format = 'type';
        $showProperties = false;
        $maxProperties = 3;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $formatAttr = $attributes[0]->newInstance();
                $format = $formatAttr->style ?? $format;
                $showProperties = $formatAttr->showProperties ?? $showProperties;
                $maxProperties = $formatAttr->maxProperties ?? $maxProperties;
            }
        }

        // Choose display format
        return match($format) {
            'class' => $this->formatClassOnly($value),
            'properties' => $this->formatWithProperties($value, $maxProperties),
            'json' => $this->formatAsJson($value),
            'debug' => $this->formatDebug($value),
            default => $this->formatType($value, $showProperties, $maxProperties),
        };
    }

    /**
     * Format as just the class name.
     */
    private function formatClassOnly(object $object): string
    {
        return get_class($object);
    }

    /**
     * Format as class name with type hint.
     */
    private function formatType(object $object, bool $showProperties = false, int $maxProperties = 3): string
    {
        $className = get_class($object);

        if (!$showProperties) {
            return $className;
        }

        // Get a few public properties to show
        $properties = $this->getSimpleProperties($object, $maxProperties);
        if (empty($properties)) {
            return $className;
        }

        $propsString = implode(', ', array_map(
            fn ($k, $v) => $k . ': ' . $this->formatPropertyValue($v),
            array_keys($properties),
            array_values($properties),
        ));

        return $className . ' {' . $propsString . '}';
    }

    /**
     * Format with main properties.
     */
    private function formatWithProperties(object $object, int $maxProperties): string
    {
        $properties = $this->getSimpleProperties($object, $maxProperties);

        if (empty($properties)) {
            return get_class($object);
        }

        $lines = [];
        foreach ($properties as $key => $value) {
            $lines[] = $key . ': ' . $this->formatPropertyValue($value);
        }

        return implode(', ', $lines);
    }

    /**
     * Format as JSON (for simple objects).
     */
    private function formatAsJson(object $object): string
    {
        try {
            if (method_exists($object, 'toArray')) {
                $data = $object->toArray();
            } elseif (method_exists($object, 'jsonSerialize')) {
                $data = $object->jsonSerialize();
            } else {
                $data = get_object_vars($object);
            }

            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json ?: '{}';
        } catch (Throwable $e) {
            return get_class($object);
        }
    }

    /**
     * Debug format with more details.
     */
    private function formatDebug(object $object): string
    {
        $className = get_class($object);
        $properties = [];

        try {
            $reflection = new ReflectionClass($object);
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                if ($property->isInitialized($object)) {
                    $value = $property->getValue($object);
                    $properties[$property->getName()] = $this->formatPropertyValue($value);
                }
            }
        } catch (ReflectionException $e) {
            // If reflection fails, just show class name
            return $className;
        }

        if (empty($properties)) {
            return $className;
        }

        $lines = [];
        foreach ($properties as $key => $value) {
            $lines[] = '  ' . $key . ': ' . $value;
        }

        return $className . " {\n" . implode("\n", $lines) . "\n}";
    }

    /**
     * Get simple properties from object.
     */
    private function getSimpleProperties(object $object, int $max = 3): array
    {
        $properties = [];

        try {
            $reflection = new ReflectionClass($object);

            // First, try public properties
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                if ($property->isInitialized($object)) {
                    $value = $property->getValue($object);
                    $properties[$property->getName()] = $this->simplifyValue($value);

                    if (count($properties) >= $max) {
                        break;
                    }
                }
            }

            // If not enough public properties, try getter methods
            if (count($properties) < $max) {
                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    $methodName = $method->getName();

                    if (str_starts_with($methodName, 'get') && strlen($methodName) > 3 && $method->getNumberOfParameters() === 0) {
                        $propertyName = lcfirst(substr($methodName, 3));

                        if (!isset($properties[$propertyName])) {
                            try {
                                $value = $method->invoke($object);
                                $properties[$propertyName] = $this->simplifyValue($value);

                                if (count($properties) >= $max) {
                                    break;
                                }
                            } catch (Throwable $e) {
                                // Skip methods that fail
                                continue;
                            }
                        }
                    }
                }
            }
        } catch (ReflectionException $e) {
            // If reflection fails, try get_object_vars
            $vars = get_object_vars($object);
            foreach ($vars as $key => $value) {
                $properties[$key] = $this->simplifyValue($value);
                if (count($properties) >= $max) {
                    break;
                }
            }
        }

        return $properties;
    }

    /**
     * Simplify a value for display.
     */
    private function simplifyValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            if (strlen($value) > 20) {
                return substr($value, 0, 17) . '...';
            }
            return $value;
        }

        if (is_array($value)) {
            $count = count($value);
            return $count === 0 ? '[]' : 'array(' . $count . ')';
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $str = (string) $value;
                if (strlen($str) > 20) {
                    return substr($str, 0, 17) . '...';
                }
                return $str;
            }
            return get_class($value);
        }

        return gettype($value);
    }

    private function formatPropertyValue(mixed $value): string
    {
        if ($this->presenterFactory !== null) {
            try {
                return (string) $this->presenterFactory->displayValue($value);
            } catch (Throwable $e) {
                // Fall back to simple formatting
            }
        }

        return $this->simplifyValue($value);
    }
}