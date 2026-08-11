<?php

declare(strict_types=1);

class TabbedFormLayout implements FormLayoutInterface
{
    public function __construct(
        private SectionGroupManager $sectionGroupManager,
    ) {
    }

    public function buildLayout(
        array $formValues,
        HtmlFormSectionManager $sectionManager,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $builder,
        AbstractForm $formInstance,
        FormConfig $config,
    ): array {
        $tabConfig = $config->getTabConfig();

        if (!$tabConfig || !$tabConfig->hasTabs()) {
            return [];
        }

        // Build sections from the form
        $sectionsConfig = $this->buildSectionsConfig($formValues, $sectionManager, $config);
        $sectionComponents = $this->buildSectionComponents(
            $sectionsConfig,
            $sectionRenderer,
            $builder,
            $formInstance,
            $sectionManager,
            $config,
        );

        // Get hidden fields
        $hiddenFields = $this->extractHiddenFields($sectionsConfig);

        return TabComponent::create(
            htmlBuilder: $builder,
            tabConfig: $tabConfig,
            sectionGroupManager: $this->sectionGroupManager,
            config: $config->getTabComponentConfig(),
        )
            ->setSectionComponents($sectionComponents)
            ->setHiddenFields($hiddenFields)
            ->returnAsArray(true)
            ->build();
    }

    public function getFieldSectionLayout(
        array $fields,
        string|int $sectionKey,
        HtmlBuilder $form,
    ): ?AbstractHtmlComponent {
        return $form->add(...$fields);
    }

    private function buildSectionsConfig(
        array $formValues,
        HtmlFormSectionManager $sectionManager,
        FormConfig $config,
    ): array {
        $sectionsConfig = $sectionManager->getSections($formValues);
        return array_merge($sectionsConfig, $config->getFields());
    }

    private function buildSectionComponents(
        array $sectionsConfig,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $form,
        AbstractForm $formInstance,
        HtmlFormSectionManager $sectionManager,
        FormConfig $config,
    ): array {
        $sectionComponents = [];

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

            // Skip hidden fields - they'll be handled separately
            if (is_array($section) && isset($section['type']) && $section['type'] === 'hidden') {
                continue;
            }

            // Find which group this section belongs to
            $groupKey = $this->findGroupForSection($sectionKey);
            if ($groupKey) {
                // Store components by section key
                $flatComponents = ArrayUtils::flatten($components);
                $validComponents = array_filter(
                    $flatComponents,
                    fn ($comp) => $comp instanceof AbstractHtmlComponent,
                );

                if (!empty($validComponents)) {
                    if (isset($sectionComponents[$sectionKey])) {
                        $sectionComponents[$sectionKey] = array_merge(
                            $sectionComponents[$sectionKey],
                            $validComponents,
                        );
                    } else {
                        $sectionComponents[$sectionKey] = $validComponents;
                    }
                }
            }
        }

        return $sectionComponents;
    }

    private function findGroupForSection(string|int $sectionKey): ?string
    {
        foreach ($this->sectionGroupManager->getAllGroups() as $group) {
            if (in_array($sectionKey, $group->getSectionKeys(), true)) {
                return $group->getKey();
            }
        }
        return null;
    }

    private function extractHiddenFields(array $sectionsConfig): array
    {
        $hiddenFields = [];

        foreach ($sectionsConfig as $key => $section) {
            if (is_array($section) && isset($section['type']) && $section['type'] === 'hidden') {
                // Hidden fields are handled during section building
            }
        }

        return $hiddenFields;
    }
}