<?php

declare(strict_types=1);

final class VariationSection extends BaseFieldSection implements HtmlGroupLayoutInterface
{
    use ProductFormSectionLayoutTrait;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        HtmlEscaper $escaper,
        private VariationConfig $variationConfig,
        private CardAction $cardAction,
    ) {
        parent::__construct($builder, $iconBuilder, $escaper);
    }

    public function getKey(): string
    {
        return ProductSection::VARIATION->value;
    }

    public function getConfig(array $formValues = []): array
    {
        return $this->variationConfig->getStaticConfig();
    }

    public function renderGroupLayout(array $groupElements, string $wrapperClass): null|array|AbstractHtmlComponent
    {
        $form = $this->htmlBuilder;
        $variationGroups = [];

        foreach ($groupElements as $index => $groupElement) {
            // Extract variation fields and attribute fields
            $variationFields = $groupElement['variation'] ?? [];
            $attributeFields = $groupElement['attributes'] ?? [];
            $attributeWrapperClass = $groupElement['attributeWrapperClass'] ?? 'variation-attributes';

            // Split variation fields into rows
            $spliter = new FlexibleArraySplitter($variationFields);
            $spliter->split(['firstRow' => 3, 'secondRow' => 2, 'thirdRow' => 2]);
            $remaining = $spliter->getRemaining();

            // Build attributes HTML with card actions
            $attributesComponents = $this->buildVariationAttributes($attributeFields, $attributeWrapperClass);

            // Build the complete variation group HTML
            $variationGroups[] = $form->tag('div')->class('variation-group span-all')
                ->add(
                    $form->div()->class('variation-content')->add(
                        $form->div()->class('variation-fields')->add(
                            $form->div()->class('form-row')->add(
                                ...$spliter->get('firstRow'),
                            ),
                            $form->div()->class('form-row')->add(
                                ...$spliter->get('secondRow'),
                            ),
                            $form->div()->class('form-row')->add(
                                ...$spliter->get('thirdRow'),
                            ),
                        ),
                        $form->div()->class('attributes-field')->add(
                            ...$attributesComponents,
                        ),
                    ),
                    $form->div()->class('button-container span-all')->add(
                        ...$remaining,
                    ),
                );
        }

        return $variationGroups;
    }

    private function buildVariationAttributes(array $attributeFields, string $wrapperClass): array
    {
        $html = $this->htmlBuilder;

        if (empty($attributeFields)) {
            return [];
        }

        // Group attributes in sets of 3 per row
        $attributeGroups = array_chunk($attributeFields, 3);
        $attributeRows = [];

        foreach ($attributeGroups as $group) {
            $attributeRows[] = $html->div()->class($wrapperClass)
                ->add(...$group);
        }

        // Add a single card action at the bottom of all attributes
        $cardAction = $this->cardAction
            ->target('.attributes-field')
            ->item('.variation-attributes')
            ->showAdd(true)
            ->showRemove(true)
            ->addAttributes(['data-card-context' => 'variation-attributes'])
            ->removeAttributes(['data-card-context' => 'variation-attributes'])
            ->build();

        $attributeRows[] = $cardAction;

        return $attributeRows;
    }
}