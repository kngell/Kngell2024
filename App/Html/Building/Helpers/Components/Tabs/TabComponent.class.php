<?php

declare(strict_types=1);

class TabComponent implements StandAloneComponentInterface
{
    private TabComponentConfig $config;
    private array $sectionComponents = [];
    private array $hiddenFields = [];

    public function __construct(
        private HtmlBuilder $htmlBuilder,
        private ?TabConfig $tabConfig = null,
        private ?SectionGroupManager $sectionGroupManager = null,
        private ?AbstractHtmlComponent $customContent = null,
        ?TabComponentConfig $config = null,
    ) {
        $this->config = $config ?? TabComponentConfig::create();
    }

    // ─── Setters ───

    public function setTabConfig(TabConfig $tabConfig): self
    {
        $this->tabConfig = $tabConfig;
        return $this;
    }

    public function setSectionGroupManager(SectionGroupManager $manager): self
    {
        $this->sectionGroupManager = $manager;
        return $this;
    }

    public function setSectionComponents(array $components): self
    {
        $this->sectionComponents = $components;
        return $this;
    }

    public function addSectionComponent(string $key, AbstractHtmlComponent $component): self
    {
        $this->sectionComponents[$key] = $component;
        return $this;
    }

    public function setHiddenFields(array $hiddenFields): self
    {
        $this->hiddenFields = $hiddenFields;
        return $this;
    }

    public function setCustomContent(?AbstractHtmlComponent $content): self
    {
        $this->customContent = $content;
        return $this;
    }

    // ─── Config Delegation ───

    public function setContainerClass(array $class): self
    {
        $this->config->setContainerClass($class);
        return $this;
    }

    public function setContainerTag(string $tag): self
    {
        $this->config->setContainerTag($tag);
        return $this;
    }

    public function setContentContainerClass(array $class): self
    {
        $this->config->setContentContainerClass($class);
        return $this;
    }

    public function setContentContainerTag(string $tag): self
    {
        $this->config->setContentContainerTag($tag);
        return $this;
    }

    public function setPanelClass(array $class): self
    {
        $this->config->setPanelClass($class);
        return $this;
    }

    public function setRadioName(string $name): self
    {
        $this->config->setRadioName($name);
        return $this;
    }

    public function returnAsArray(bool $returnAsArray = true): self
    {
        $this->config->setReturnAsArray($returnAsArray);
        return $this;
    }

    public function setWrapContent(bool $wrap): self
    {
        $this->config->setWrapContent($wrap);
        return $this;
    }

    public function setConfig(TabComponentConfig $config): self
    {
        $this->config = $config;
        return $this;
    }

    // ─── Build ───

    public function build(mixed $params = null): null|array|AbstractHtmlComponent
    {
        if ($params instanceof TabConfig) {
            $this->tabConfig = $params;
        }

        if (!$this->tabConfig || !$this->tabConfig->hasTabs()) {
            return null;
        }

        // Build FormTabs with custom radio name
        $tabsComponent = new FormTabs($this->htmlBuilder);

        // Get tab label container classes from TabConfig
        $tabLabelClasses = $this->tabConfig->getTabLabelContainerClass();
        $tabsComponent->setTabClass($tabLabelClasses);
        $tabsComponent->setRadioName($this->config->getRadioName());

        $contentChildren = [];

        foreach ($this->tabConfig->getTabs() as $tab) {
            if ($tab->isDisabled()) {
                continue;
            }

            $tabsComponent->addTab(
                $tab->getTitle(),
                (string) $tab->getId(),
                $tab->getState(),
                $tab->getAttributes()['class'] ?? [],
            );

            // Merge default panel class with tab-specific content class
            $panelClasses = $this->config->getPanelClass();
            if (!empty($tab->getContentClass())) {
                $panelClasses = array_merge($panelClasses, $tab->getContentClass());
            }
            $panelClasses = array_values(array_unique(array_filter($panelClasses)));

            $tabContent = $this->htmlBuilder->tag('div')
                ->id((string) $tab->getId() . '-content')
                ->class(...$panelClasses);

            if ($tab->isActive() || $tab->getId() === $this->tabConfig->getActiveTab()) {
                $tabContent->class('active');
            }

            // Build content from section groups
            foreach ($tab->getSectionGroups() as $groupKey) {
                if ($this->sectionGroupManager) {
                    $group = $this->sectionGroupManager->getGroup($groupKey);
                    if ($group) {
                        $wrapper = $this->buildGroupWrapper($group);
                        if ($wrapper) {
                            $tabContent->add($wrapper);
                        }
                    }
                }
            }

            $contentChildren[] = $tabContent;
        }

        // Add hidden fields
        if (!empty($this->hiddenFields)) {
            $contentChildren = array_merge($contentChildren, $this->hiddenFields);
        }

        // Build content container
        $contentLayout = $this->buildContentContainer($contentChildren);

        $components = $tabsComponent->getComponents(
            $this->customContent ?? $contentLayout,
        );

        // ALWAYS wrap in container if we have container classes
        // Even when returnAsArray is true, we need the container for styling
        if (!empty($this->config->getContainerClass())) {
            $wrappedComponent = $this->htmlBuilder->tag($this->config->getContainerTag())
                ->class(...$this->config->getContainerClass())
                ->add(...$components);

            if ($this->config->isReturnAsArray()) {
                return [$wrappedComponent];
            }

            return $wrappedComponent;
        }

        if ($this->config->isReturnAsArray()) {
            return $components;
        }

        return $components;
    }

    // ─── Build Helpers ───

    private function buildContentContainer(array $contentChildren): ?AbstractHtmlComponent
    {
        if (empty($contentChildren)) {
            return null;
        }

        if (!$this->config->shouldWrapContent()) {
            if (count($contentChildren) === 1) {
                return $contentChildren[0];
            }
            return $this->htmlBuilder->tag('div')->add(...$contentChildren);
        }

        // Merge content container classes from TabConfig and TabComponentConfig
        $contentContainerClasses = $this->tabConfig->getContentContainerClass();
        $configClasses = $this->config->getContentContainerClass();
        $mergedClasses = array_values(array_unique(array_filter(array_merge($contentContainerClasses, $configClasses))));

        return $this->htmlBuilder->tag($this->config->getContentContainerTag())
            ->class(...$mergedClasses)
            ->add(...$contentChildren);
    }

    private function buildGroupWrapper(SectionGroup $group): ?AbstractHtmlComponent
    {
        $sections = [];

        foreach ($group->getSectionKeys() as $sectionKey) {
            if (isset($this->sectionComponents[$sectionKey])) {
                $component = $this->sectionComponents[$sectionKey];

                if (is_array($component)) {
                    $flattened = ArrayUtils::flatten($component);
                    foreach ($flattened as $subComponent) {
                        if ($subComponent instanceof AbstractHtmlComponent) {
                            $sections[] = $subComponent;
                        }
                    }
                } elseif ($component instanceof AbstractHtmlComponent) {
                    $sections[] = $component;
                }
            }
        }

        if (empty($sections)) {
            return null;
        }

        $wrapperClass = $group->getWrapperClass();
        $wrapperTag = $group->getWrapperTag();

        // If no wrapper class and tag is 'div', return sections directly
        if (empty($wrapperClass) && $wrapperTag === 'div') {
            if (count($sections) === 1) {
                return $sections[0];
            }
            return $this->htmlBuilder->tag('div')->add(...$sections);
        }

        // Wrap with the specified tag and classes
        $wrapper = $this->htmlBuilder->tag($wrapperTag);

        if (!empty($wrapperClass)) {
            $wrapper->class(...$wrapperClass);
        }

        $wrapper->add(...$sections);

        foreach ($group->getAttributes() as $key => $value) {
            $wrapper->attr($key, $value);
        }

        return $wrapper;
    }

    // ─── Static Factory ───

    public static function create(
        HtmlBuilder $htmlBuilder,
        ?TabConfig $tabConfig = null,
        ?SectionGroupManager $sectionGroupManager = null,
        ?AbstractHtmlComponent $customContent = null,
        ?TabComponentConfig $config = null,
    ): self {
        return new self($htmlBuilder, $tabConfig, $sectionGroupManager, $customContent, $config);
    }
}