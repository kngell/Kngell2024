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
        $getter = 'get' . ucfirst(StringUtils::snakeCaseToCamelCase($fieldName));
        if (!method_exists($entity, $getter)) {
            $getter = $fieldName;
        }

        $value = method_exists($entity, $getter) ? $entity->$getter() : null;
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
}
