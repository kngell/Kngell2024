<?php

declare(strict_types=1);

final class ButtonBuilder implements StandAloneComponentInterface
{
    private ?ButtonConfig $buttonConfig = null;

    public function __construct(
        private HtmlBuilder $htmlBuilder,
        private IconBuilder $iconBuilder,
    ) {
    }

    public function add(
        string $type,
        string $buttonSize,
        ?string $label = null,
        ?string $buttonStyle = null,
        ?string $icon = null,
        ?string $ariaLabel = null,
        string $iconPosition = 'left',
        array $buttonClass = [],
        array $attributes = [],
    ): self {
        $this->buttonConfig = new ButtonConfig(
            type: $type,
            label: $label ?? '',
            size: $buttonSize,
            ariaLabel: $ariaLabel ?? $label ?? '',
            style: $buttonStyle ?? 'primary',
            icon: $icon,
            iconPosition: $iconPosition,
            attributes: $attributes,
            classes: $buttonClass,
        );

        return $this;
    }

    public function addConfig(ButtonConfig|array $buttonConfig): self
    {
        $this->buttonConfig = $buttonConfig instanceof ButtonConfig
            ? $buttonConfig
            : ButtonConfig::fromArray($buttonConfig);

        return $this;
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        $config = $this->resolveConfig($params);

        if ($config === null) {
            return null;
        }

        $button = $this->htmlBuilder->button()
            ->type($config->type)
            ->class(...$this->getButtonClasses($config));

        $this->addButtonContent($button, $config);

        if (!empty($config->attributes)) {
            $button->custom($config->attributes);
        }

        $this->buttonConfig = null;

        return $button;
    }

    private function resolveConfig(mixed $params): ?ButtonConfig
    {
        if ($params instanceof ButtonConfig) {
            return $params;
        }

        if (is_array($params)) {
            return ButtonConfig::fromArray($params);
        }

        return $this->buttonConfig;
    }

    private function getButtonClasses(ButtonConfig $config): array
    {
        $classes = ['btn'];

        if ($config->size !== '') {
            $classes[] = 'btn--' . $config->size;
        }

        if ($config->style !== '') {
            $classes[] = 'btn--' . $config->style;
        }

        return array_merge($classes, $config->classes);
    }

    private function addButtonContent(AbstractHtmlComponent $button, ButtonConfig $config): void
    {
        $html = $this->htmlBuilder;
        $iconBuilder = $this->iconBuilder;

        $hasIcon = $config->icon !== null;
        $hasLabel = $config->label !== '';

        if ($hasIcon && $hasLabel) {
            $iconSpan = $html->tag('span')->class('btn__icon')->add(
                $iconBuilder->createIcon($config->icon, $config->ariaLabel ?: 'Button'),
            );
            $labelSpan = $html->tag('span')->class('btn__label')->content($config->label);

            if ($config->iconPosition === 'right') {
                $button->add($labelSpan, $iconSpan);
            } else {
                $button->add($iconSpan, $labelSpan);
            }
        } elseif ($hasIcon) {
            $button->add(
                $html->tag('span')->class('btn__icon')->add(
                    $iconBuilder->createIcon($config->icon, $config->ariaLabel ?: 'Button'),
                ),
            );
        } elseif ($hasLabel) {
            $button->add(
                $html->tag('span')->class('btn__label')->content($config->label),
            );
        }
    }
}