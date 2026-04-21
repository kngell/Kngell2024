<?php

declare(strict_types=1);

final class DeletionSummarySection extends AbstractBaseHtmlSection
{
    public function getKey(): string
    {
        return ConfirmDeletionSection::SUMMARY->value;
    }

    public function getConfig(array $formValues = []): AbstractHtmlComponent
    {
        $form = $this->htmlBuilder;
        $name = $formValues['product_name'] ?? null;
        $props = $this->otherProperties($formValues);
        return $form->tag('div')->class('product-summary')->add(
            $form->tag('h4')->class('title')->content('Product Summary'),
            $form->tag('div')->class('details')->add(
                $this->productThumbnail($formValues),
                $form->tag('div')->class('details__text')->add(
                    $form->tag('span')->class('product-name')->content($name),
                    $props !== null ? $form->tag('span')->class('other-properties')->content(implode(', ', $props)) : null,
                ),
            ),
        );
    }

    private function otherProperties(array $formValues): array
    {
        $sku = '';
        $stock = '';
        if (isset($formValues['product_name'])) {
            unset($formValues['product_name']);
        }
        if (isset($formValues['image'])) {
            unset($formValues['image']);
        }
        if (isset($formValues['sku'])) {
            $sku = $formValues['sku'];
        }
        if (isset($formValues['stockQuantity'])) {
            $stock = $formValues['stockQuantity'] . ' units';
        }
        return [$sku, $stock];
    }

    private function productThumbnail(array $formValues = []): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->tag('div')->class('details__image-container')->add(
            $this->productImage($formValues),
        );
    }

    private function productImage(array $formValues = []): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $mainImage = $formValues['image'] ?? null;
        $imageContainer = $html->tag('div')->class('image-container');

        if ($mainImage !== null) {
            return $imageContainer->add(
                $html->tag('img')
                    ->src($mainImage)
                    ->class('image')
                    ->alt('Product Image'),
            );
        }
        return $imageContainer->add(
            $this->iconBuilder->createIcon(
                $html,
                'icon-media-image',
                'Product thumbnail',
                ['image'],
            ),
        );
    }
}