<?php

declare(strict_types=1);

class TwoColumnsPageLayout implements PageLayoutInterface
{
    public function __construct(
        private array $leftSections = [],
        private array $leftColumnClass = [],
        private array $rightColumnClass = [],
    ) {
        $this->leftSections = $leftSections;
    }

    public function buildLayout(HtmlRegularSectionManager $sectionManager, HtmlBuilder $builder, AbstractHtml $htmlInstance, PageConfig $config, array $entities, array $pagination = []): array
    {
        $sectionsConfig = $sectionManager->getSections($entities);
        $leftComponents = [];
        $rightComponents = [];

        foreach ($sectionsConfig as $key => $section) {
            $result = $this->renderPageSection(
                $section,
                $key,
                $builder,
                $sectionsConfig,
                $sectionManager,
                $config,
                $htmlInstance,
            );

            // Add to appropriate column
            if (in_array($key, $this->leftSections)) {
                $leftComponents = array_merge($leftComponents, $result);
            } else {
                $rightComponents = array_merge($rightComponents, $result);
            }
        }

        return [
            $builder->tag('div')->class(...$this->leftColumnClass)->add(...$leftComponents),
            $builder->tag('div')->class(...$this->rightColumnClass)->add(...$rightComponents),
        ];
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