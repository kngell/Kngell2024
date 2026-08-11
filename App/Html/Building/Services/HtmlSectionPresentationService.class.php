<?php

declare(strict_types=1);

class HtmlSectionPresentationService
{
    public function __construct(
        private TypePresenterFactory $presenterFactory,
    ) {
    }

    public function show(Entity $entity, mixed $value, string $propertyName): mixed
    {
        try {
            $property = $entity->getProperty($propertyName);
            return $this->presenterFactory->displayValue($value, $property);
        } catch (ReflectionException $e) {
            return $this->presenterFactory->displayValue($value);
        }
    }

    public function showField(Entity $entity, string $fieldName): mixed
    {
        $value = $entity->getFieldValue($fieldName);
        return $this->show($entity, $value, $fieldName);
    }

    public function showRelated(Entity $entity, string $relationPath, string $propertyName): mixed
    {
        $parts = explode('.', $relationPath);
        $currentEntity = $entity;

        foreach ($parts as $part) {
            $part = StringUtils::snakeCaseToCamelCase($part);
            $getter = 'get' . ucfirst($part);
            if (!method_exists($currentEntity, $getter)) {
                return null;
            }
            $currentEntity = $currentEntity->$getter();

            if (!$currentEntity instanceof Entity) {
                return $this->presenterFactory->displayValue($currentEntity);
            }
        }

        if ($currentEntity instanceof Entity) {
            return $this->showField($currentEntity, $propertyName);
        }

        return null;
    }

    public function getNestedValue(object $entity, string $path, string $default = ''): string
    {
        $parts = explode('.', $path);
        $current = $entity;

        foreach ($parts as $index => $part) {
            if ($index === 0) {
                $current = $entity->getFieldValue($part);
            } elseif (is_array($current)) {
                $current = $current[$part] ?? null;
            } elseif (is_object($current)) {
                $getter = 'get' . ucfirst($part);
                $current = method_exists($current, $getter) ? $current->$getter() : ($current->$part ?? null);
            } else {
                $current = null;
            }

            if ($current === null) {
                return $default;
            }
        }

        return $this->show($entity, $current, $path);
    }
}