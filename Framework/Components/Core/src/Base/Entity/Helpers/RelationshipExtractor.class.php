<?php

declare(strict_types=1);
final readonly class RelationshipExtractor
{
    public function extractIds(Entity $entity, array $relationshipNames = []): array
    {
        $ids = [];

        $relationships = empty($relationshipNames)
            ? array_keys($entity->getRelationships())
            : $relationshipNames;

        foreach ($relationships as $relationName) {
            try {
                $getter = 'get' . ucfirst($relationName);
                if (method_exists($entity, $getter)) {
                    $relation = $entity->$getter();
                    if ($relation instanceof Entity) {
                        $ids[$relationName . '_id'] = $this->extractId($relation);
                    } elseif ($relation instanceof CollectionInterface || is_array($relation)) {
                        $ids[$relationName . '_ids'] = array_map(
                            fn ($item) => $this->extractId($item),
                            is_array($relation) ? $relation : $relation->all(),
                        );
                    }
                }
            } catch (Exception $e) {
                // Skip if relationship can't be loaded
            }
        }

        return $ids;
    }

    private function extractId(Entity $entity): mixed
    {
        $keyProperty = $entity->getEntityKeyProperty();
        if ($keyProperty !== false) {
            return $entity->getFieldValue($keyProperty);
        }

        // Fallback: look for any property ending with 'id'
        $reflection = CustomReflection::getInstance($entity)->getObject();
        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            if (str_ends_with(strtolower($propertyName), 'id')) {
                if ($property->isInitialized($entity)) {
                    return $property->getValue($entity);
                }
            }
        }

        return null;
    }
}