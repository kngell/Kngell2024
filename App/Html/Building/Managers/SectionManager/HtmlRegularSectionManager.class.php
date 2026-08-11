<?php

declare(strict_types=1);
final class HtmlRegularSectionManager extends AbstractHtmlSectionManager
{
    public function getSections(array $context = []): array
    {
        return array_map(
            fn ($section) => $this->getSectionConfig($section, $context),
            $this->sections,
        );
    }

    public function getFieldMapping(array|Entity $formValues = []): array
    {
        return [];
    }

    private function getSectionConfig(HtmlSectionInterface $section, array $context): array|AbstractHtmlComponent
    {
        $pagination = $this->getPagination();
        if (!empty($pagination)) {
            $section->setPagination($pagination);
        }
        return $section->getConfig($context);
    }
}