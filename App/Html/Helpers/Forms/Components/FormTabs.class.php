<?php

declare(strict_types=1);

class FormTabs implements StandAloneComponentInterface
{
    private array $tabs = [];
    private array $tabClass = ['form-tabs'];

    public function __construct(
        private HtmlBuilder $htmlBuilder,
    ) {
    }

    public function addTab(
        string $title,
        ?string $tabId = null,
        ?string $state = null,
    ): self {
        $this->tabs[] = [
            'title' => $title,
            'id' => $tabId,
            'state' => $state,
        ];
        return $this;
    }

    public function build(mixed $name = null): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $tabLabels = [];

        foreach ($this->tabs as $tab) {
            $label = $html->label()
                ->class('tab')
                ->for($tab['id'])
                ->add(
                    $html->tag('h6')->class('tab__text')->content($tab['title']),
                );

            if (($tab['state'] ?? null) === 'disabled') {
                $label->class('tab__disabled');
            }

            $tabLabels[] = $label;
        }

        return $html->tag('div')->class(...$this->tabClass)->add(...$tabLabels);
    }

    public function getRadioElements(): array
    {
        $radioElements = [];
        $html = $this->htmlBuilder;
        $checked = false;
        $hasDefaultTab = false;

        // Check if any tab has 'default' state
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
                ->name('form-tab')
                ->class('radio-tab')
                ->id($tab['id']);

            // Check ONLY ONE radio
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

    public function setTabClass(array $tabClass = []): FormTabs
    {
        $this->tabClass = array_merge($this->tabClass, $tabClass);
        return $this;
    }
}