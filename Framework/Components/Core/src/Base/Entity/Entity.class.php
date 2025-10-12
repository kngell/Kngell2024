<?php

declare(strict_types=1);

use Brick\Money\Money;

abstract class Entity
{
    protected const string DATE_FORMAT = 'Y-m-d H:i:s';

    public function __set($name, $value)
    {
        $name = lcfirst(StringUtils::studlyCaps($name));
        if (property_exists($this, $name)) {
            $reflection = CustomReflection::getInstance($this)->getObject();
            $methodName = 'set' . ucfirst($name);

            if ($reflection->hasMethod($methodName)) {
                $method = $reflection->getMethod($methodName);
                $parameters = $method->getParameters();

                if (!empty($parameters)) {
                    $firstParam = $parameters[0];
                    $paramType = $firstParam->getType();

                    // Handle type conversions for PDO
                    if ($paramType && !$paramType->isBuiltin()) {
                        $typeName = $paramType->getName();

                        if ($typeName === DateTimeImmutable::class && is_string($value)) {
                            $value = new DateTimeImmutable($value);
                        }
                        // Add other type conversions as needed
                    }
                }

                $method->invoke($this, $value);
            }
        }
    }

    // public function __set($name, $value)
    // {
    //     $this->pdoHydrate([$name => $value]);
    // }

    /**
     * Special hydration method for PDO FETCH_CLASS that handles type conversions.
     */
    public function pdoHydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            $propertyName = lcfirst(StringUtils::studlyCaps($key));

            if (property_exists($this, $propertyName)) {
                $setter = 'set' . ucfirst($propertyName);

                if (method_exists($this, $setter)) {
                    // Handle type conversions for known types
                    if (in_array($propertyName, ['created_at', 'updated_at', 'deleted_at']) && is_string($value)) {
                        $value = new DateTimeImmutable($value);
                    }

                    $this->$setter($value);
                } else {
                    // Fallback to direct property assignment for PDO
                    $reflection = new ReflectionProperty($this, $propertyName);
                    $reflection->setAccessible(true);

                    // Convert date strings for DateTime properties
                    $type = $reflection->getType();
                    if ($type && $type->getName() === DateTimeImmutable::class && is_string($value)) {
                        $value = new DateTimeImmutable($value);
                    }

                    $reflection->setValue($this, $value);
                }
            }
        }
    }

    /**
     * Optionally set createdAt and updatedAt.
     */
    public function touchTimestamps(): void
    {
        if ($this instanceof TimestampableInterface) {
            $now = new DateTimeImmutable();

            if (method_exists($this, 'getCreatedAt') && $this->getCreatedAt() === null) {
                $this->setCreatedAt($now);
            }

            if (method_exists($this, 'setUpdatedAt')) {
                $this->setUpdatedAt($now);
            }
        }
    }

    /**
     * Optionally soft-delete entity.
     */
    public function touchDeleted(): void
    {
        if ($this instanceof SoftDeletableInterface && method_exists($this, 'softDelete')) {
            $this->softDelete();
        }
    }

    public function table(): string
    {
        return StringUtils::StudlyCapsToUnderscore($this::class);
    }

    public function toOriginalArray(): array
    {
        $array = [];
        $reflection = CustomReflection::getInstance($this)->getObject();
        $props = $reflection->getProperties();
        foreach ($props as $prop) {
            $name = StringUtils::StudlyCapsToUnderscore($prop->getName());
            $array[$name] = $prop->getValue($this);
        }
        return $array;
    }

    // In Entity.php - add this method

    public function toArray(): array
    {
        $array = [];
        $reflection = CustomReflection::getInstance($this)->getObject();
        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            $value = $property->getValue($this);

            // Handle different value types
            if ($value instanceof self) {
                $array[$propertyName] = $value->toArray();
            } elseif ($value instanceof DateTimeInterface) {
                $array[$propertyName] = $value->format(self::DATE_FORMAT);
            } elseif ($value instanceof Money) {
                $array[$propertyName] = $value->getAmount(); // or whatever method Money has
            } elseif (is_array($value)) {
                $array[$propertyName] = $this->convertArrayValues($value);
            } elseif (is_object($value) && method_exists($value, '__toString')) {
                $array[$propertyName] = (string) $value;
            } else {
                $array[$propertyName] = $value;
            }
        }

        return $array;
    }

    protected function convertArrayValues(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($value instanceof self) {
                $result[$key] = $value->toArray();
            } elseif ($value instanceof DateTimeInterface) {
                $result[$key] = $value->format(self::DATE_FORMAT);
            } elseif (is_array($value)) {
                $result[$key] = $this->convertArrayValues($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function getEntityKeyField(): string|bool
    {
        $reflector = CustomReflection::getInstance($this)->getObject();
        $properties = $reflector->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $identifier = $property->getAttributes();
            if (! empty($identifier)) {
                /** @var ReflectionAttribute */
                $attribute = ArrayUtils::first($identifier);
                $attrArguments = $attribute->getArguments();
                if (! empty($attrArguments['name'])) {
                    $entityFieldId = $attrArguments['name'];
                } else {
                    $entityFieldId = StringUtils::StudlyCapsToUnderscore($property->getName());
                }
                return $entityFieldId;
            }
        }
        return false;
    }

    public function assign(array $data): self
    {
        $attrs = CustomReflection::getInstance($this)->getObject()->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($data as $key => $prop) {
            $ok = array_filter($attrs, function ($attr) use ($key) {
                return StringUtils::camelCase($key) === $attr->getName();
            });
            if ($ok) {
                /** @var ReflectionProperty */
                $property = ArrayUtils::first($ok);
                $attr = ArrayUtils::first($property->getAttributes());
                if (! empty($attr) && $attr->getName() === 'DateField') {
                    $prop = $this->prop($prop);
                }
                $property->setAccessible(true);
                $property->setValue($this, $prop);
            }
        }
        return $this;
    }

    public function isInitialized(string $field): bool
    {
        $reflector = CustomReflection::getInstance($this)->getObject();
        $property = $reflector->getProperty(StringUtils::studlyCaps($field));
        return $property->isInitialized($this);
    }

    public function getFieldValue(string $field): mixed
    {
        $reflector = CustomReflection::getInstance($this)->getObject();
        $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (strtolower($method->getName()) === 'get' . strtolower($field)) {
                return $method->invoke($this);
            }
        }

        return null;
    }

    private function prop(string $property): string
    {
        $date = new DateTimeImmutable($property);
        return $date->format(self::DATE_FORMAT);
    }
}