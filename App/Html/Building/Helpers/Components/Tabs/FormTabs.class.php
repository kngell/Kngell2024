<?php

declare(strict_types=1);

class FormTabs implements StandAloneComponentInterface
{
    private array $tabs = [];

    // Classes for the tab label container
    private array $tabLabelContainerClass = ['tabs__label'];
    private string $tag = 'div';
    private string $radioName = 'tab-name';

    public function __construct(
        private HtmlBuilder $htmlBuilder,
    ) {
    }

    // ─── Setters ───

    /**
     * Merge classes with defaults.
     */
    public function setTabClass(array $class): self
    {
        $this->tabLabelContainerClass = $this->mergeClasses($this->tabLabelContainerClass, $class);
        return $this;
    }

    public function setRadioName(string $name): self
    {
        $this->radioName = $name;
        return $this;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    // ─── Tab Management ───

    public function addTab(
        string $title,
        ?string $tabId = null,
        ?string $state = null,
        array $class = [],
    ): self {
        $this->tabs[] = [
            'title' => $title,
            'id' => $tabId,
            'state' => $state,
            'class' => $class,
        ];
        return $this;
    }

    // ─── Build ───

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $tabLabels = [];

        foreach ($this->tabs as $tab) {
            $label = $html->label();

            if (!empty($tab['class'])) {
                $label->class(...$tab['class']);
            } else {
                $label->class('tab');
            }

            $label->for($tab['id'])
                ->add(
                    $html->tag('h6')->class('tab__text')->content($tab['title']),
                );

            if (($tab['state'] ?? null) === 'disabled') {
                $label->class('tab__disabled');
            }

            $tabLabels[] = $label;
        }

        return $html->tag($this->tag)
            ->class(...$this->tabLabelContainerClass)
            ->add(...$tabLabels);
    }

    // ─── Components ───

    public function getRadioElements(): array
    {
        $radioElements = [];
        $html = $this->htmlBuilder;
        $checked = false;
        $hasDefaultTab = false;

        foreach ($this->tabs as $tab) {
            if (($tab['state'] ?? null) === 'default') {
                $hasDefaultTab = true;
                break;
            }
        }

        foreach ($this->tabs as $index => $tab) {
            if (empty($tab['id'])) {
                throw new FieldLayoutException('Radio id is required');
            }

            $radioElement = $html->input('radio')
                ->name($this->radioName)
                ->class('radio-tab')
                ->id($tab['id']);

            if (!$checked) {
                if (($tab['state'] ?? null) === 'default') {
                    $radioElement->checked();
                    $checked = true;
                } elseif (!$hasDefaultTab && $index === 0) {
                    $radioElement->checked();
                    $checked = true;
                }
            }

            if (($tab['state'] ?? null) === 'disabled') {
                $radioElement->disabled();
            }

            $radioElements[] = $radioElement;
        }

        return $radioElements;
    }

    public function getComponents(?AbstractHtmlComponent $content = null): array
    {
        $components = $this->getRadioElements();
        $components[] = $this->build();

        if ($content) {
            $components[] = $content;
        }

        return $components;
    }

    // ─── Helper ───

    private function mergeClasses(array $default, array $additional): array
    {
        return array_values(array_unique(array_filter(array_merge($default, $additional))));
    }
}