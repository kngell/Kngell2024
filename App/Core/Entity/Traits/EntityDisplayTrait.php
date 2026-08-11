<?php

declare(strict_types=1);

trait EntityDisplayTrait
{
    private function getEntityId(Entity $entity): string
    {
        if (method_exists($entity, 'getPublicId')) {
            return $entity->getPublicId()->toString();
        }

        $presenterFactory = $entity->getPresenterFactory();
        $keyProp = $entity->getEntityKeyProperty();
        $rawId = $entity->getEntityPrimarykeyValue();
        $property = $entity->getProperty($keyProp);

        return $presenterFactory->displayValue($rawId, $property);
    }

    private function show(Entity $entity, string $propertyName): string
    {
        $presenterFactory = $entity->getPresenterFactory();
        $value = $entity->getFieldValue($propertyName);
        $property = $entity->getProperty($propertyName);
        return $presenterFactory->displayValue($value, $property);
    }
}