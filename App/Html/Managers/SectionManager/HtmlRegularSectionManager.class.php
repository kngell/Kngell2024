<?php

declare(strict_types=1);
final class HtmlRegularSectionManager extends AbstractHtmlSectionManager
{
    public function getSections(array $context = []): array
    {
        return array_map(
            fn ($section) => $section->getConfig($context),
            $this->sections,
        );
    }
}