<?php

declare(strict_types=1);

class SimplePageLayout implements PageLayoutInterface
{
    public function buildLayout(HtmlRegularSectionManager $sectionManager, HtmlBuilder $builder, AbstractHtml $htmlInstance, PageConfig $config, array $entities, array $pagination = []): array
    {
        $sections = $sectionManager->getSections();
        $allSections = [];
        $enumclass = $config->getEnumClass();

        foreach ($enumclass::cases() as $case) {
            $sectionKey = $case->value;

            if (!isset($sections[$sectionKey])) {
                continue;
            }

            $section = $sections[$sectionKey];

            if ($section instanceof AbstractHtmlComponent) {
                $allSections[$sectionKey] = $section;
            } elseif (is_array($section)) {
                $allSections[$sectionKey] = array_values($section);
            }
        }

        return $allSections;
    }
}