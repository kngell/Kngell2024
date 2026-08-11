<?php

declare(strict_types=1);

class FieldGroupRenderer
{
    public function __construct(private ?FieldRenderer $fieldRenderer = null)
    {
    }

    public function setFieldRenderer(FieldRenderer $fieldRenderer): void
    {
        $this->fieldRenderer = $fieldRenderer;
    }

    public function renderFieldGroup(array $groupConfig, FormBuilder $builder, AbstractForm $formInstance): array|AbstractHtmlComponent
    {
        $wrapperClass = $groupConfig['wrapperClass'] ?? 'field-group';
        $content = $groupConfig['content'] ?? [];

        $groupElements = [];
        $form = $builder->form();

        foreach ($content as $config) {
            $groupElements[] = $this->buildItem($config, $form, $formInstance, $config);
        }

        return $form->tag('div')
                  ->class($wrapperClass)
                  ->add(...$groupElements);
    }

    public function buildVariationGroup(array $groupConfig, FormBuilder $form, AbstractForm $formInstance, FormConfig|PageConfig $config): array
    {
        $variationGroups = [];
        $attributesGroup = [];
        foreach ($groupConfig as $item) {
            if (!is_array($item)) {
                continue;
            }
            if ($item['type'] === 'variation-attribute-group') {
                $attributeWrapperClass = $item['wrapperClass'] ?? null;
                $content = $item['content'];

                foreach ($content as $attrItem) {
                    $attributesGroup[] = $this->buildItem($attrItem, $form, $formInstance, $config);
                }
            } else {
                $variationGroups[] = $this->buildItem($item, $form, $formInstance, $config);
            }
        }
        return [
            'variation' => $variationGroups,
            'attributes' => $attributesGroup, 'attributeWrapperClass' => $attributeWrapperClass ?? null,
        ];
    }

    private function buildItem(array $item, FormBuilder $form, AbstractForm $formInstance, FormConfig|PageConfig $config): array|AbstractHtmlComponent
    {
        $component = null;
        if (isset($item['type'])) {
            switch ($item['type']) {
                case 'field-group':
                    $component = $this->renderFieldGroup($item, $form, $formInstance);
                    break;
                case 'variation-group':
                    $component = $this->buildVariationGroup($item, $form, $formInstance, $config);
                    break;
                case 'button':
                    $component = $formInstance->renderButton($item, $form);
                    break;
                case 'button-group':
                    $component = $formInstance->renderButtonGroup($item, $form);
                    break;
                case 'html':
                    $component = $formInstance->renderHtml($item, $form);
                    break;
                default:
                    $component = $this->fieldRenderer->render($item, $form, $formInstance, $config);
            }
        } else {
            $component = $this->fieldRenderer->render($item, $form, $formInstance, $config);
        }
        return $component;
    }
}