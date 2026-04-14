<?php

declare(strict_types=1);

class SimpleArrayTransformer implements ToArrayTransformerInterface
{
    public function __construct(
        private TypeNormalizerInterface $normalizer,
    ) {
    }

    public function supports(string $format): bool
    {
        return $format === 'simple';
    }

    public function transform(Entity $entity, array $options = []): array
    {
        $array = [];
        $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $propertyName = $property->getName();

            if ($property->isInitialized($entity)) {
                $dbFieldName = StringUtils::camelCaseToSnakeCase($propertyName);
                $value = $property->getValue($entity);
                if (!$value instanceof Entity ||
                    !(is_array($value) && (ArrayUtils::isObjectList($value) || ArrayUtils::isArrayList($value)))) {
                    $value = $this->normalizer->normalizeFromEntityToDatabase($value, $property);
                }

                $array[$dbFieldName] = $value;
            }
        }

        return $array;
    }
}
