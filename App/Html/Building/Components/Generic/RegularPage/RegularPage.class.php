<?php

declare(strict_types=1);

/**
 * @template T of BackedEnum
 */
class RegularPage extends AbstractHtml
{
    /**
     * @param RegularPageProvider $provider
     * @param HtmlRegularSectionManager $sectionManager
     * @param HtmlBuilder $builder
     * @param class-string<T> $sectionEnumClass
     * @param array|Entity $items
     *
     * @return void
     */
    public function __construct(
        private readonly RegularPageProvider $provider,
        private readonly HtmlRegularSectionManager $sectionManager,
        private HtmlBuilder $builder,
        private readonly string $sectionEnumClass,
        private array|Entity $items = [],
    ) {
    }

    public function getHtmlElements(): null|string|array
    {
        $this->provider->registerSections($this->builder, $this->sectionManager);
        return $this->render();
    }

    /**
     * Build the layout structure WITHOUT rendering
     * Returns components, not strings.
     *
     * @return array<string, AbstractHtmlComponent|list<AbstractHtmlComponent>>
     */
    public function buildLayout(?HtmlBuilder $html = null): array
    {
        $sections = $this->sectionManager->getSections($this->items);
        $allSections = [];

        /** @var T[] $cases */
        $cases = $this->sectionEnumClass::cases();

        foreach ($cases as $case) {
            $sectionKey = $case->value;

            if (!isset($sections[$sectionKey])) {
                continue;
            }

            if (empty($sections[$sectionKey])) {
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

    /**
     * Render the layout to HTML strings.
     *
     * @return array<string, string|string[]>
     */
    // public function render(): array
    // {
    //     $layout = $this->buildLayout();

    //     if (empty($layout)) {
    //         return [];
    //     }

    //     $html = [];

    //     foreach ($layout as $key => $section) {
    //         if ($section instanceof AbstractHtmlComponent) {
    //             $html[$key] = $section->generate();
    //             continue;
    //         }

    //         if (is_array($section)) {
    //             $html[$key] = $this->renderSectionWithCustomLayout($key, $section);
    //         }
    //     }

    //     return $html;
    // }
    public function render(): array
    {
        $layout = $this->buildLayout();

        if (empty($layout)) {
            return [];
        }

        $html = [];
        $timers = [];

        foreach ($layout as $key => $section) {
            $start = microtime(true);

            if ($section instanceof AbstractHtmlComponent) {
                $html[$key] = $section->generate();
            } elseif (is_array($section)) {
                $html[$key] = $this->renderSectionWithCustomLayout($key, $section);
            }

            $time = (microtime(true) - $start) * 1000;
            $timers[$key] = $time;
        }

        // Log expensive sections
        $expensiveSections = array_filter($timers, fn ($t) => $t > 50);
        if (!empty($expensiveSections)) {
            error_log('[PERFORMANCE] Expensive sections: ' . json_encode($expensiveSections));
        }

        return $html;
    }

    /**
     * Render a section with optional custom layout.
     *
     * @param string $key
     * @param array<AbstractHtmlComponent> $section
     *
     * @return string
     */
    private function renderSectionWithCustomLayout(string $key, array $section): string
    {
        $sectionObj = $this->sectionManager->getSection($key);

        // Try custom layout first (polymorphism via base class)
        if ($sectionObj !== null) {
            $customLayout = $sectionObj->getSectionsCustomLayout($section);
            if ($customLayout !== null) {
                return $customLayout->generate();
            }
        }

        // Fallback to default layout
        return $this->renderDefaultLayout($section);
    }

    /**
     * @param array<AbstractHtmlComponent> $components
     */
    private function renderDefaultLayout(array $components): string
    {
        return implode('', $this->renderComponents($components));
    }

    /**
     * Render an array of components to HTML strings.
     *
     * @param array<AbstractHtmlComponent> $components
     *
     * @return string[]
     */
    private function renderComponents(array $components): array
    {
        $htmlItems = [];
        foreach ($components as $component) {
            if ($component instanceof AbstractHtmlComponent) {
                $htmlItems[] = $component->generate();
            }
        }
        return $htmlItems;
    }
}