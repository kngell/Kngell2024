<?php

declare(strict_types=1);

class OriginalArrayTransformer implements ToArrayTransformerInterface
{
    public function __construct()
    {
    }

    public function supports(string $format): bool
    {
        return $format === 'original';
    }

    public function transform(Entity $entity, array $options = []): array
    {
        $array = [];
        $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();

        foreach ($reflection->getProperties() as $prop) {
            $name = StringUtils::studlyCapsToUnderscore($prop->getName());
            $array[$name] = $prop->getValue($entity);
        }

        return $array;
    }
}
