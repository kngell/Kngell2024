<?php

declare(strict_types=1);

final class FormOptions implements StandAloneComponentInterface
{
    private array $options = [];
    private string $defaultOption = Theme::LIGHT->value;

    public function __construct(
        private HtmlBuilder $htmlBuilder,
    ) {
    }

    public function addOption(
        string $title,
        ?string $description = null,
        array $attribute = [],
    ): self {
        $this->options[] = [
            'title' => $title,
            'description' => $description,
            'attribute' => $attribute,
        ];
        return $this;
    }

    public function addOptions(array $options): self
    {
        foreach ($options as $option) {
            $this->addOption(
                $option['title'],
                $option['description'] ?? null,
            );
        }

        return $this;
    }

    public function build(mixed $params = null): AbstractHtmlComponent
    {
        $container = $this->htmlBuilder->tag('div')
            ->class('options');

        foreach ($this->options as $option) {
            $container->add($this->buildOption($option, $params));
        }
        return $container->add(
            $this->htmlBuilder->input('hidden')
            ->name('small_banner_theme')->value($this->defaultOption),
        );
    }

    /**
     * @param string $defaultOption
     *
     * @return FormOptions
     */
    public function setDefaultOption(string $defaultOption): FormOptions
    {
        $this->defaultOption = $defaultOption;

        return $this;
    }

    private function buildOption(array $option, mixed $params): ?AbstractHtmlComponent
    {
        if (!is_string($params)) {
            return null;
        }

        $html = $this->htmlBuilder;
        $optionBox = $html->tag('div')->class('options-box');

        if (array_key_exists('attribute', $option)) {
            $optionBox->custom($option['attribute']);
        }

        // Get the option value from data-option attribute
        $optionValue = $option['attribute']['data-option'] ?? null;

        // Create the radio input
        $radioInput = $html->input('radio')
            ->name($params)
            ->value($optionValue)
            ->style(['display' => 'none;']);
        if ($this->defaultOption === $optionValue) {
            $radioInput->defaultValue($optionValue);
        }

        return $optionBox->role('button')->add(
            $radioInput,
            $html->tag('span')->class('options-box__title')->content($option['title']),
            $html->tag('span')->class('options-box__description')->content($option['description']),
        );
    }
}