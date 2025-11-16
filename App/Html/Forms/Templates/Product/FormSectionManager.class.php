<?php

declare(strict_types=1);

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
        $isEdit = !empty($formValues['id']) || ($formValues instanceof Product && $formValues->getId());

        return [
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-group span-all',
                'content' => $this->variationBuilder->buildVariationGroups($isEdit, $formValues),
            ],
        ];
    }
}