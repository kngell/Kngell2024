<?php

declare(strict_types=1);

abstract class AbstractBaseHtmlSection implements HtmlSectionInterface
{
    public function __construct(
        protected readonly HtmlBuilder $htmlBuilder,
        protected readonly IconBuilder $iconBuilder,
    ) {
    }

    abstract public function getConfig(array $formValues = []): array|AbstractHtmlComponent;

    public function shouldRender(array $formValues = []): bool
    {
        return true;
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        return null;
    }

    protected function media(): string
    {
        return '';
    }

    protected function escape(?string $value, array $options = []): ?string
    {
        if ($value === null) {
            return '';
        }

        // Default options
        $options = array_merge([
            'trim' => true,
            'preserveNbsp' => true,
            'preserveEntities' => [], // Additional entities to preserve
            'encoding' => 'UTF-8',
        ], $options);

        $result = $value;

        // Trim whitespace
        if ($options['trim']) {
            $result = trim($result);
        }

        // Preserve specific HTML entities
        if ($options['preserveNbsp'] || !empty($options['preserveEntities'])) {
            $entitiesToPreserve = $options['preserveNbsp'] ? ['&nbsp;'] : [];
            $entitiesToPreserve = array_merge($entitiesToPreserve, $options['preserveEntities']);

            // Replace entities with placeholders
            $placeholders = [];
            foreach ($entitiesToPreserve as $i => $entity) {
                $placeholder = "___ENT_{$i}___";
                $placeholders[$placeholder] = $entity;
                $result = str_replace($entity, $placeholder, $result);
            }

            // Decode any remaining HTML entities
            $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, $options['encoding']);
            // Escape everything
            $result = htmlspecialchars($result, ENT_QUOTES, $options['encoding']);

            // Restore preserved entities
            foreach ($placeholders as $placeholder => $entity) {
                $result = str_replace($placeholder, $entity, $result);
            }

            return $result;
        }

        // Simple escape without preserving entities
        return htmlspecialchars($result, ENT_QUOTES, $options['encoding']);
    }

    protected function copyFromsource(array $organized): void
    {
    }
}