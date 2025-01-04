<?php

declare(strict_types=1);
abstract class Entity
{
    public function __set($name, $value)
    {
        $name = lcfirst(StringUtils::studlyCaps($name));
        if (property_exists($this, $name)) {
            $reflection = CustomReflection::getInstance($this)->getObject();
            $reflection->getMethod('set' . $name)->invoke($this, $value);
        }
    }

    public function table() : string
    {
        return StringUtils::StudlyCapsToUnderscore($this::class);
    }

    public function toOriginalArray() : array
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

    public function getEntityKeyField() : string|bool
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

    public function assign(array $data) : self
    {
        $attrs = CustomReflection::getInstance($this)->getObject()->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($data as $key => $prop) {
            $ok = array_filter($attrs, function ($attr) use ($key) {
                return StringUtils::camelCase($key) === $attr->getName();
            });
            if ($ok) {
                /** @var ReflectionProperty */
                $property = ArrayUtils::first($ok);
                $property->setAccessible(true);
                $property->setValue($this, $prop);
            }
        }
        return $this;
    }
}