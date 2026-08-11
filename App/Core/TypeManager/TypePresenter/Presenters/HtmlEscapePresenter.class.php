<?php

declare(strict_types=1);

final class HtmlEscapePresenter implements TypePresenterInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_string($value) || is_scalar($value);
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if ($value === null) {
            return '';
        }

        // Get options from DisplayFormat attribute if present
        $options = $this->getEscapeOptions($property);

        $result = (string) $value;

        if ($options['trim']) {
            $result = trim($result);
        }

        if ($options['preserveNbsp'] || !empty($options['preserveEntities'])) {
            $entitiesToPreserve = $options['preserveNbsp'] ? ['&nbsp;'] : [];
            $entitiesToPreserve = array_merge($entitiesToPreserve, $options['preserveEntities']);

            $placeholders = [];
            foreach ($entitiesToPreserve as $i => $entity) {
                $placeholder = "___ENT_{$i}___";
                $placeholders[$placeholder] = $entity;
                $result = str_replace($entity, $placeholder, $result);
            }

            $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, $options['encoding']);
            $result = htmlspecialchars($result, ENT_QUOTES, $options['encoding']);

            foreach ($placeholders as $placeholder => $entity) {
                $result = str_replace($placeholder, $entity, $result);
            }

            return $result;
        }

        return htmlspecialchars($result, ENT_QUOTES, $options['encoding']);
    }

    private function getEscapeOptions(?ReflectionProperty $property): array
    {
        $defaults = [
            'trim' => true,
            'preserveNbsp' => true,
            'preserveEntities' => [],
            'encoding' => 'UTF-8',
        ];

        if ($property === null) {
            return $defaults;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();

            // Extract escape-specific options from DisplayFormat
            return [
                'trim' => $format->escapeTrim ?? $defaults['trim'],
                'preserveNbsp' => $format->preserveNbsp ?? $defaults['preserveNbsp'],
                'preserveEntities' => $format->preserveEntities ?? $defaults['preserveEntities'],
                'encoding' => $format->encoding ?? $defaults['encoding'],
            ];
        }

        return $defaults;
    }
}