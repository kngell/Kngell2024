<?php

declare(strict_types=1);

class TabbedFormLayoutOld extends AbstractTabbedLayout implements FormLayoutInterface
{
    public function buildLayout(
        array $formValues,
        HtmlFormSectionManager $sectionManager,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $form,
        AbstractForm $formInstance,
        FormConfig $config,
    ): array {
        $activeTabConfig = $this->getActiveTabConfig($config->getTabConfig());

        if (!$activeTabConfig->hasTabs()) {
            return [];
        }

        // Update container class from active config
        if ($activeTabConfig->getContentContainerClass()) {
            $this->contentContainerClass = $activeTabConfig->getContentContainerClass();
        }

        $fields = $config->getFields();
        $sectionsConfig = $sectionManager->getSections($formValues);
        $sectionsConfig = array_merge($sectionsConfig, $fields);

        [$hiddenFields, $formSections] = $this->buildFormSections(
            $sectionsConfig,
            $sectionRenderer,
            $form,
            $formInstance,
            $sectionManager,
            $config,
        );

        return $this->buildTabsLayout($activeTabConfig, $formSections, $form, $hiddenFields);
    }

    public function getFieldSectionLayout(
        array $fields,
        string|int $sectionKey,
        HtmlBuilder $form,
    ): ?AbstractHtmlComponent {
        return $form->add(...$fields);
    }

    private function buildFormSections(
        array $sectionsConfig,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $form,
        Abstractform $formInstance,
        HtmlFormSectionManager $sectionManager,
        PageConfig|FormConfig $config,
    ): array {
        $formSections = [];
        $hiddenFields = [];

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $result = $sectionRenderer->render(
                $sectionKey,
                $form,
                $sectionsConfig,
                $formInstance,
                $sectionManager,
                $this,
                $config,
            );

            $components = is_array($result) ? $result : [$result];
            $section = $sectionsConfig[$sectionKey];

            // Handle hidden fields
            if (is_array($section) && isset($section['type']) && $section['type'] === 'hidden') {
                $hiddenFields = array_merge($hiddenFields, $components);
            } else {
                $formSections = $this->getSectionElement($sectionKey, $components, $formSections);
            }
        }

        return [$hiddenFields, $formSections];
    }
}