<?php

declare(strict_types=1);

class TabbedPageLayout extends AbstractTabbedLayout implements PageLayoutInterface
{
    public function buildLayout(HtmlRegularSectionManager $sectionManager, HtmlBuilder $builder, AbstractHtml $htmlInstance, PageConfig $config, array $entities, array $pagination = []): array
    {
        $activeTabConfig = $this->getActiveTabConfig($config->getTabConfig());

        if (!$activeTabConfig->hasTabs()) {
            return [];
        }

        if ($activeTabConfig->getContentContainerClass()) {
            $this->contentContainerClass = $activeTabConfig->getContentContainerClass();
        }
        if (!empty($pagination)) {
            $sectionManager->setPagination($pagination);
        }
        $sectionsConfig = $sectionManager->getSections($entities);
        $pageSections = $this->buildPageSections(
            $sectionsConfig,
            $config,
            $builder,
            $sectionManager,
            $htmlInstance,
        );

        return $this->buildTabsLayout($activeTabConfig, $pageSections, $builder);
    }

    public function getFieldSectionLayout(
        array $fields,
        string|int $sectionKey,
        HtmlBuilder $form,
    ): ?AbstractHtmlComponent {
        return $form->add(...$fields);
    }

    private function buildPageSections(
        array $sectionsConfig,
        PageConfig $config,
        HtmlBuilder $builder,
        HtmlRegularSectionManager $sectionManager,
        AbstractHtml $htmlInstance,
    ): array {
        $components = [];
        $pageSections = [];
        $enumClass = $config->getEnumClass();

        if (!$enumClass || !enum_exists($enumClass)) {
            return [];
        }

        foreach ($enumClass::cases() as $case) {
            $sectionKey = $case->value;

            if (!isset($sectionsConfig[$sectionKey])) {
                continue;
            }
            $section = $sectionsConfig[$sectionKey];

            $sectionObj = $sectionManager->getSection($sectionKey);
            if ($sectionObj->hasForm()) {
                $action = $sectionObj->getAction();
                if ($action) {
                    $section = $sectionObj->buildForm();
                }
            }
            // Render section if needed
            if (is_array($section)) {
                $section = $this->renderPageSection(
                    $section,
                    $sectionKey,
                    $builder,
                    $sectionsConfig,
                    $sectionManager,
                    $config,
                    $htmlInstance,
                );
            }

            // Store component
            if ($section instanceof AbstractHtmlComponent) {
                $components[$sectionKey] = [$section];
            } elseif (is_array($section)) {
                $components[$sectionKey] = array_values($section);
            }

            // Add to section groups
            $pageSections = $this->getSectionElement(
                $sectionKey,
                $components[$sectionKey] ?? [],
                $pageSections,
            );
        }

        return $pageSections;
    }

    private function renderPageSection(
        array $section,
        int|string $sectionKey,
        HtmlBuilder $builder,
        array $sectionsConfig,
        HtmlRegularSectionManager $sectionManager,
        PageConfig|FormConfig $config,
        AbstractHtml $htmlInstance,
    ): array {
        if (empty($section)) {
            return [];
        }

        // Check if already rendered
        $isRendered = true;
        foreach ($section as $item) {
            if ($item === null) {
                continue;
            }
            if (!$item instanceof AbstractHtmlComponent) {
                $isRendered = false;
                break;
            }
        }

        if ($isRendered) {
            return $section;
        }

        $sectionRenderer = $config->getSectionRenderer();
        $component = $sectionRenderer->render(
            sectionKey: $sectionKey,
            form: $builder,
            sectionsConfig: $sectionsConfig,
            formInstance: $htmlInstance,
            sectionManager: $sectionManager,
            formLayout: $this,
            config: $config,
        );

        return is_array($component) ? $component : [$component];
    }
}