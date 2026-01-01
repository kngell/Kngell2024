<?php

declare(strict_types=1);

use Cassandra\UuidInterface;

final class FormSectionManager
{
    /** @var array<string, FormSectionInterface> */
    private array $sections = [];

    public function __construct(
        private readonly VariationBuilderInterface $variationBuilder,
    ) {
    }

    public function registerSection(FormSectionInterface $section): void
    {
        $this->sections[$section->getKey()] = $section;
    }

    public function getFieldMapping(array $formValues = []): array
    {
        $mapping = [];

        foreach ($this->sections as $section) {
            if ($section->shouldRender($formValues)) {
                $mapping = array_merge($mapping, $section->getFieldMapping());
            }
        }

        $variationMap = $this->variationBuilder->getFieldMapping($formValues);
        $mapping = array_merge($mapping, $variationMap);
        return array_filter($mapping, function ($sourcePath) {
            return !str_contains($sourcePath, '[');
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getFormSections(array $formValues = []): array
    {
        $sections = [];

        foreach ($this->sections as $section) {
            if (!$section->shouldRender($formValues)) {
                continue;
            }

            $sections[$section->getKey()] = $section->getConfig($formValues);
        }

        // Handle variation section separately
        $sections['variation'] = $this->buildVariationSection($formValues);

        return $sections;
    }

    private function buildVariationSection(array $formValues): array
    {
        $productId = $this->extractProductId($formValues);
        $isEdit = $productId !== null && $productId !== 0;

        return [
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-group span-all',
                'content' => $this->variationBuilder->buildVariationGroups($isEdit, $formValues),
            ],
        ];
    }

    private function extractProductId(array|Entity $formValues): ?int
    {
        if ($formValues instanceof Entity) {
            $field = $formValues->getEntityKeyProperty();
            $getter = 'get' . str_replace('_', '', ucwords($field, '_'));
            $id = method_exists($formValues, $getter) ? (int) $formValues->$getter() : null;
            if ($id instanceof UuidInterface) {
                return $id->toString();
            }
            return is_string($id) ? $id : null;
        }

        return  isset($formValues['public_id']) ? (int) $formValues['public_id'] : null;
    }
}