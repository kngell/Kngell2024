<?php

declare(strict_types=1);

class StepNavigationComponent implements StandAloneComponentInterface
{
    private array $buttons = [];
    private array $class = ['step-navigation'];
    private string $tag = 'div';

    public function __construct(
        private HtmlBuilder $htmlBuilder,
        private ButtonBuilder $buttonBuilder,
    ) {
    }

    public function addBackButton(string $targetStep, string $label = '← Back'): self
    {
        $this->buttons[] = [
            'type' => 'back',
            'target' => $targetStep,
            'label' => $label,
            'style' => 'checkout-back',
            'size' => 'lg',
        ];
        return $this;
    }

    public function addNextButton(string $targetStep, string $label = 'Next'): self
    {
        $this->buttons[] = [
            'type' => 'next',
            'target' => $targetStep,
            'label' => $label,
            'style' => 'checkout-next',
            'size' => 'md',
        ];
        return $this;
    }

    public function addSubmitButton(string $label = 'Place Order'): self
    {
        $this->buttons[] = [
            'type' => 'submit',
            'target' => null,
            'label' => $label,
            'style' => 'checkout-submit',
            'size' => 'lg',
        ];
        return $this;
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        if (empty($this->buttons)) {
            return null;
        }

        $html = $this->htmlBuilder;
        $components = [];

        foreach ($this->buttons as $button) {
            if ($button['type'] === 'submit') {
                $this->buttonBuilder->add(
                    type: 'submit',
                    buttonSize: $button['size'],
                    label: $button['label'],
                    buttonStyle: $button['style'],
                );
                $components[] = $this->buttonBuilder->build();
                $this->buttonBuilder->reset();
            } else {
                $components[] = $html->label()
                    ->for($button['target'])
                    ->class('btn', 'btn--' . $button['style'], 'btn--' . $button['size'])
                    ->role('button')
                    ->attr('aria-controls', 'checkout-step-' . $button['target'])
                    ->content($button['label']);
            }
        }

        return $html->tag($this->tag)
            ->class(...$this->class)
            ->add(...$components);
    }

    public function setClass(array $class): self
    {
        $this->class = array_merge($this->class, $class);
        return $this;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    public function reset(): self
    {
        $this->buttons = [];
        $this->class = ['step-navigation'];
        return $this;
    }
}