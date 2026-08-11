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
        $html = $this->htmlBuilder;
        $summary = $formValues['entity_summary'] ?? [];

        $name = $summary['name'] ?? 'Unknown Entity';

        $props = $this->extractOtherProperties($summary);

        return $html->tag('div')->class('entity-summary')->add(
            $html->tag('h4')->class('title')->content($formValues['label'] . ' Summary'),
            $html->tag('div')->class('details')->add(
                $this->imageThumbnail($summary),
                $html->tag('div')->class('details__text')->add(
                    $html->tag('span')->class('entity-name')->content($name),
                    !empty($props) ? $html->tag('span')->class('other-properties')->content(implode(', ', $props)) : null,
                ),
            ),
        );
    }

    private function imageThumbnail(array $summary): AbstractHtmlComponent
    {
        $image = $summary['image'] ?? null;
        if (is_array($image)) {
            $image = $image[0];
        }
        return parent::thumbnail($image);
    }

    private function extractOtherProperties(array $summary): array
    {
        $props = [];
        foreach ($summary as $key => $value) {
            if (in_array($key, ['name', 'image'])) {
                continue;
            }
            $props[] = (string) $value;
        }
        return $props;
    }
}