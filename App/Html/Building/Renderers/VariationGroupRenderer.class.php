<?php

declare(strict_types=1);

final class VariationGroupRenderer
{
    public function __construct(private FieldGroupRenderer $fieldGroupRenderer)
    {
    }

    public function render(array $groupConfig, FormBuilder $builder, AbstractForm $formInstance, null|HtmlGroupLayoutInterface|FieldSectionInterface $section = null, null|PageConfig|FormConfig $config = null): array
    {
        $wrapperClass = $groupConfig['wrapperClass'] ?? 'field-group';
        $content = $groupConfig['content'] ?? [];
        $variationGroups = [];
        foreach ($content as $variationConfig) {
            $variationGroups[] = $this->fieldGroupRenderer->buildVariationGroup($variationConfig, $builder, $formInstance, $config);
        }
        return $section->renderGroupLayout($variationGroups, $wrapperClass);
    }
}