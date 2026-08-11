<?php

declare(strict_types=1);

final class ToggleSwitch implements StandAloneComponentInterface
{
    private const string NAME = 'toggle-name';

    private ?string $id = null;
    private array $attributes = [];
    private array $wrapperClassPrev = [];
    private array $wrapperClassNext = [];
    private ?string $label = null;
    private bool $checked = false;
    private bool $disabled = false;

    public function __construct(
        private HtmlBuilder $htmlBuilder,
    ) {
    }

    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function label(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function checked(bool $checked = true): self
    {
        $this->checked = $checked;
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function attribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function build(mixed $name = null): ?AbstractHtmlComponent
    {
        $id = $this->id ?? $name . '-' . uniqid();
        $classes = array_merge($this->wrapperClassPrev, ['toggle-switch'], $this->wrapperClassNext);

        $wrapper = $this->htmlBuilder->tag('div')
            ->class(...$classes);

        foreach ($this->attributes as $attrName => $value) {
            $wrapper->attribute($attrName, $value);
        }

        if (!$name) {
            $name = self::NAME;
        }

        // Create label that contains everything
        $toggleLabel = $this->htmlBuilder->label()
            ->class('toggle');

        // Create checkbox
        $checkbox = $this->htmlBuilder->input('checkbox')
            ->id($id)
            ->name($name);

        if ($this->checked) {
            $checkbox->attribute('checked', 'checked');
        }

        if ($this->disabled) {
            $checkbox->attribute('disabled', 'disabled');
            $toggleLabel->attribute('style', 'opacity: 0.5; cursor: not-allowed;');
        }

        $toggleLabel->add($checkbox);
        $toggleLabel->add(
            $this->htmlBuilder->tag('span')->class('track'),
            $this->htmlBuilder->tag('span')->class('knob'),
        );

        $wrapper->add($toggleLabel);

        if ($this->label !== null) {
            $textLabel = $this->htmlBuilder->tag('span')
                ->class('toggle-label')
                ->content($this->label);
            $wrapper->add($textLabel);
        }

        return $wrapper;
    }

    public function wrapperClassPrev(array $wrapperClassPrev): ToggleSwitch
    {
        $this->wrapperClassPrev = $wrapperClassPrev;
        return $this;
    }

    public function wrapperClassNext(array $wrapperClassNext): ToggleSwitch
    {
        $this->wrapperClassNext = $wrapperClassNext;
        return $this;
    }
}