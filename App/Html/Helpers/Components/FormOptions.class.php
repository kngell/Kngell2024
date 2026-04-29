<?php

declare(strict_types=1);

final class FormOptions implements StandAloneComponentInterface
{
    private array $options = [];
    private ?string $inputName = null;
    private string $defaultOption = '';

    public function __construct(
        private HtmlBuilder $htmlBuilder,
    ) {
    }

    public function addOption(
        string $value,
        string $title,
        ?string $description = null,
        array $attributes = [],
    ): self {
        $this->options[] = [
            'value' => $value,
            'title' => $title,
            'description' => $description,
            'attributes' => $attributes,
        ];

        return $this;
    }

    public function addOptions(array $options): self
    {
        foreach ($options as $option) {
            $this->addOption(
                value: $option['value'],
                title: $option['title'],
                description: $option['description'] ?? null,
                attributes: $option['attributes'] ?? [],
            );
        }

        return $this;
    }

    public function setInputName(string $name): self
    {
        $this->inputName = $name;

        return $this;
    }

    public function build(mixed $params = null): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $inputName = $this->inputName
            ?? (is_string($params) ? $params : 'option');

        $container = $html->tag('div')->class('options');

        foreach ($this->options as $option) {
            $container->add(
                $this->buildOption($option, $inputName),
            );
        }

        return $container;
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

    private function buildOption(
        array $option,
        string $inputName,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;
        $value = $option['value'];

        $optionBox = $html->tag('div')
            ->class('options-box')
            ->custom(array_merge(
                [
                    'data-option' => $value,
                    'role' => 'button',
                    'tabindex' => '0',
                ],
                $option['attributes'],
            ));

        $radioInput = $html->input('radio')
            ->name($inputName)
            ->id($inputName . '-' . $value)
            ->value($value)
            ->style(['display' => 'none']);

        if ($this->defaultOption === $value) {
            $radioInput->defaultValue($value);
            $optionBox->class('selected');
        }

        $title = $html->tag('span')
            ->class('options-box__title')
            ->content($option['title']);

        $optionBox->add($radioInput, $title);

        if (!empty($option['description'])) {
            $optionBox->add(
                $html->tag('span')
                    ->class('options-box__description')
                    ->content($option['description']),
            );
        }

        return $optionBox;
    }
}