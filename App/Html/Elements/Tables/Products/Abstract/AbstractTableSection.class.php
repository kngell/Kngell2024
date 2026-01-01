<?php

declare(strict_types=1);

abstract class AbstractTableSection
{
    public function __construct(
        protected HtmlBuilder $builder,
        protected IconBuilder $icon,
        protected TypePresenterFactory $presenter,
    ) {
    }

    protected function show(Entity $entity, mixed $value, string $propertyName): mixed
    {
        try {
            $property = $entity->getProperty($propertyName);
            return $this->presenter->displayValue($value, $property);
        } catch (ReflectionException $e) {
            return $this->presenter->displayValue($value);
        }
    }

    protected function showField(Entity $entity, string $fieldName): mixed
    {
        // Try getter
        $getter = 'get' . ucfirst(StringUtils::camelCase($fieldName));
        if (!method_exists($entity, $getter)) {
            // Try without prefix
            $getter = $fieldName;
        }

        $value = method_exists($entity, $getter) ? $entity->$getter() : null;
        return $this->show($entity, $value, $fieldName);
    }

    protected function showRelated(Entity $entity, string $relationPath, string $propertyName): mixed
    {
        $parts = explode('.', $relationPath);
        $currentEntity = $entity;

        // Navigate relationships
        foreach ($parts as $part) {
            $getter = 'get' . ucfirst($part);
            if (!method_exists($currentEntity, $getter)) {
                return null;
            }
            $currentEntity = $currentEntity->$getter();

            // If we get a non-entity, that's our value
            if (!$currentEntity instanceof Entity) {
                return $this->presenter->displayValue($currentEntity);
            }
        }

        // Display the final property
        if ($currentEntity instanceof Entity) {
            return $this->showField($currentEntity, $propertyName);
        }

        return null;
    }

    protected function showValue(mixed $value, ?string $typeHint = null): mixed
    {
        return $this->presenter->displayValue($value);
    }

    /**
     * Format media/image for display.
     */
    protected function media(?string $media = null, string $alt = ''): AbstractHtmlComponent
    {
        $html = $this->builder;

        if ($media) {
            return $html->tag('span')->class('img-container')->add(
                $html->tag('img')->src($media)->alt($alt)->class('image'),
            );
        }

        return $html->tag('span')->class('img-container');
    }

    /**
     * Format product variations for display.
     *
     * @param ProductVariationShow[] $variations
     */
    protected function variation(array $variations): AbstractHtmlComponent
    {
        $html = $this->builder;
        $count = count($variations);

        if ($count === 0) {
            return $html->tag('li')->class('text-container__variant')->content('No variants');
        }

        $text = $count === 1 ? '1 Variant' : $count . ' Variants';
        return $html->tag('li')->class('text-container__variant')->content($text);
    }
}