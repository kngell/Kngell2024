<?php

declare(strict_types=1);

class CategoryPriceRangeSection extends BaseRegularSection
{
    protected SectionLayout $layoutType = SectionLayout::LAYOUT_CUSTOM;

    public function getKey(): string
    {
        return CategorySection::PRICE_RANGE->value;
    }

    protected function getSectionConfig(array $formValues = []): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key: 'price-range',
            title:'Price Range',
        )
            ->setWrapperClass(['price-range'])
            ->setIcon('icon-edit2')
            ->setShowRequired(false);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        return [
            // Global fields
            [
                'key' => 'global-min',
                'name' => 'min_price',
                'type' => 'number',
                'label' => 'Min Price',
                'placeholder' => ' ',
                'step' => '0.01',
                'footer' => ['error' => ''],
            ],
            [
                'key' => 'global-max',
                'name' => 'max_price',
                'type' => 'number',
                'label' => 'Max Price',
                'placeholder' => ' ',
                'step' => '0.01',
                'footer' => ['error' => ''],
            ],
            [
                'key' => 'bracket-label',
                'name' => 'price_ranges[brackets][0][label]',
                'type' => 'text',
                'label' => 'Label',
                'placeholder' => ' ',
                'footer' => ['error' => ''],
            ],
            [
                'key' => 'bracket-min',
                'name' => 'price_ranges[brackets][0][min]',
                'type' => 'number',
                'label' => 'Min',
                'placeholder' => ' ',
                'step' => '0.01',
                'footer' => ['error' => ''],
            ],
            [
                'key' => 'bracket-max',
                'name' => 'price_ranges[brackets][0][max]',
                'type' => 'number',
                'label' => 'Max',
                'placeholder' => ' ',
                'step' => '0.01',
                'footer' => ['error' => ''],
            ],
        ];
    }

    protected function buildCustomLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig|MediaSectionConfig $config): void
    {
        // Global min/max row (fields 0, 1)
        $body->add(
            $form->tag('div')->class('form-row', 'horizontal')->add(
                $fields[0] ?? null,
                $fields[1] ?? null,
            ),
        );

        // Build bracket cards from remaining fields
        $remainingFields = array_slice($fields, 2);
        $fieldCount = count($remainingFields);
        $bracketCount = (int) ($fieldCount / 3);

        for ($i = 0; $i < $bracketCount; $i++) {
            $labelField = $remainingFields[$i * 3] ?? null;
            $minField = $remainingFields[($i * 3) + 1] ?? null;
            $maxField = $remainingFields[($i * 3) + 2] ?? null;

            $body->add(
                $form->tag('div')->class('bracket-range')->add(
                    $form->tag('div')
                        ->class('bracket-range__card')
                        ->attribute('data-bracket-index', (string) $i)
                        ->add(
                            $this->buildBracketCardHeader($form, $i, $bracketCount),
                            $this->buildBracketCardBody($form, $labelField, $minField, $maxField),
                        ),
                ),
            );
        }
    }

    private function buildBracketCardHeader(HtmlBuilder $form, int $index, int $total): AbstractHtmlComponent
    {
        $title = $total === 1 ? 'Bracket' : 'Bracket ' . ($index + 1);

        return $form->tag('div')->class('bracket-range__card-header')->add(
            $form->tag('span')->class('card-title')->content($title),
            $form->tag('div')->class('card-action')->add(
                $form->button('button')
                    ->class('card-action__add-btn')
                    ->attribute('data-add-bracket', 'true')
                    ->add(
                        $this->iconBuilder->createIcon('icon-plus', 'Add Range', ['add-range']),
                    ),
                $form->button('button')
                    ->class('card-action__remove-btn')
                    ->attribute('data-remove-card', 'true')
                    ->add(
                        $this->iconBuilder->createIcon('icon-minus', 'Remove Range', ['remove-range']),
                    ),
            ),
        );
    }

    private function buildBracketCardBody(HtmlBuilder $form, ?AbstractHtmlComponent $labelField, ?AbstractHtmlComponent $minField, ?AbstractHtmlComponent $maxField): AbstractHtmlComponent
    {
        return $form->tag('div')->class('bracket-range__card-body')->add(
            $form->tag('div')->class('form-row')->add($labelField),
            $form->tag('div')->class('form-row', 'horizontal')->add($minField, $maxField),
        );
    }
}