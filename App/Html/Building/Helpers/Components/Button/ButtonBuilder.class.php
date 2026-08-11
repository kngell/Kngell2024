<?php

declare(strict_types=1);

final class ButtonBuilder implements StandAloneComponentInterface
{
    /** @var array<ButtonConfig> */
    private array $buttonConfig = [];

    private bool $preserveAfterBuild = false;

    // ─── Icon-only state ──────────────────────────────────────
    private bool $iconOnly = false;
    private ?IconConfig $iconConfig = null;

    // ─── Form wrapping properties ────────────────────────────
    private ?string $formAction = null;
    private string $formMethod = 'POST';
    private bool $formIncludeCsrf = true;
    private ?string $formId = null;
    private array $formClasses = [];
    private array $formHiddenFields = [];
    private array $formAttributes = [];
    private bool $formWrapEnabled = false;

    /** @var AbstractHtmlComponent[] */
    private array $externalComponents = [];

    public function __construct(
        private HtmlBuilder $htmlBuilder,
        private IconBuilder $iconBuilder,
    ) {
    }

    public function iconOnly(?IconConfig $iconConfig = null): self
    {
        $this->iconOnly = true;
        if ($iconConfig !== null) {
            $this->iconConfig = $iconConfig;
        }

        return $this;
    }

    public function add(
        string $type = 'button',
        string $buttonSize = 'md',
        ?string $label = null,
        ?string $buttonStyle = 'primary',
        ?string $icon = null,
        ?string $ariaLabel = null,
        ?string $id = null,
        string $iconPosition = 'left',
        array $buttonClass = [],
        array $attributes = [],
        ?IconConfig $iconConfig = null,
    ): self {
        if ($this->iconOnly) {
            if ($iconConfig === null && $this->iconConfig !== null) {
                $iconConfig = $this->iconConfig;
            }
            if ($iconConfig === null && $icon !== null) {
                $iconConfig = new IconConfig(
                    icon: $icon,
                    ariaLabel: $ariaLabel ?? $label ?? 'Button',
                    iconClass: [],
                );
            }
            if ($iconConfig === null) {
                throw new LogicException(
                    'Icon-only button requires an IconConfig. '
                    . 'Provide it in iconOnly() or in add() via iconConfig parameter.',
                );
            }

            $label = '';
            $ariaLabel = $ariaLabel ?? $iconConfig->ariaLabel;
        }

        if ($iconConfig === null && $icon !== null) {
            $iconConfig = new IconConfig(
                icon: $icon,
                ariaLabel: $ariaLabel ?? $label ?? 'Button',
                iconClass: [],
            );
        }

        $this->buttonConfig[] = new ButtonConfig(
            type: $type,
            label: $label ?? '',
            size: $buttonSize,
            style: $buttonStyle,
            ariaLabel: $ariaLabel ?? $label ?? 'Button',
            iconConfig: $iconConfig,
            iconPosition: $iconPosition,
            id: $id,
            attributes: $attributes,
            buttonClass: $buttonClass,
            iconOnly: $this->iconOnly,
        );

        $this->iconOnly = false;
        $this->iconConfig = null;

        return $this;
    }

    public function addConfig(ButtonConfig|array $buttonConfig): self
    {
        // Reset icon-only state when adding config directly
        $this->iconOnly = false;
        $this->iconConfig = null;

        if ($buttonConfig instanceof ButtonConfig) {
            $this->buttonConfig[] = $buttonConfig;
            return $this;
        }

        if (ArrayUtils::isAssoc($buttonConfig)) {
            $this->buttonConfig[] = ButtonConfig::fromArray($buttonConfig);
            return $this;
        }

        if (ArrayUtils::isObjectList($buttonConfig) && $buttonConfig[0] instanceof ButtonConfig) {
            foreach ($buttonConfig as $config) {
                $this->buttonConfig[] = $config;
            }
            return $this;
        }

        if (ArrayUtils::isArrayList($buttonConfig)) {
            foreach ($buttonConfig as $config) {
                $this->buttonConfig[] = ButtonConfig::fromArray($config);
            }
        }

        return $this;
    }

    public function build(mixed $params = null): null|array|AbstractHtmlComponent
    {
        $configs = $this->resolveConfig($params);

        if (empty($configs)) {
            return null;
        }

        $buttons = [];
        foreach ($configs as $config) {
            if (!$config instanceof ButtonConfig) {
                continue;
            }

            $config = $this->applyGlobalSettings($config);

            $button = $this->buildButton($config);

            if ($this->formWrapEnabled && $this->formAction !== null) {
                $button = $this->wrapInForm($button, $config);
            }

            $buttons[] = $button;
        }

        $this->resetFormState();
        if (!$this->preserveAfterBuild) {
            $this->reset();
        }

        return count($buttons) === 1 ? $buttons[0] : $buttons;
    }

    // ─── Form Methods ──────────────────────────────────────────

    public function withForm(
        string $action,
        string $method = 'POST',
        bool $includeCsrf = true,
        ?string $id = null,
        array $classes = [],
        array $hiddenFields = [],
        array $attributes = [],
        array $externalComp = [],
    ): self {
        $this->formWrapEnabled = true;
        $this->formAction = $action;
        $this->formMethod = strtoupper($method);
        $this->formIncludeCsrf = $includeCsrf;
        $this->formId = $id;
        $this->formClasses = $classes;
        $this->formHiddenFields = $hiddenFields;
        $this->formAttributes = $attributes;
        $this->externalComponents = $externalComp;

        return $this;
    }

    public function withIcon(
        IconConfig $iconConfig,
        string $label,
        string $size = 'md',
        string $style = 'primary',
        string $iconPosition = 'left',
        array $attributes = [],
    ): self {
        $this->add(
            type: 'button',
            buttonSize: $size,
            buttonStyle: $style,
            label: $label,
            attributes: $attributes,
            iconPosition: $iconPosition,
            iconConfig: $iconConfig,
        );

        return $this;
    }

    public function withHiddenField(string $name, string $value): self
    {
        $this->formHiddenFields[$name] = $value;
        return $this;
    }

    public function withHiddenFields(array $fields): self
    {
        $this->formHiddenFields = array_merge($this->formHiddenFields, $fields);
        return $this;
    }

    public function reset(): self
    {
        $this->buttonConfig = [];
        $this->resetFormState();
        $this->iconOnly = false;
        $this->iconConfig = null;
        return $this;
    }

    public function preserveAfterBuild(bool $preserve = true): self
    {
        $this->preserveAfterBuild = $preserve;
        return $this;
    }

    public function fresh(): self
    {
        $clone = clone $this;
        $clone->reset();
        return $clone;
    }

    // ─── Private Methods ───────────────────────────────────────────

    private function resolveConfig(mixed $params): array
    {
        if ($params instanceof ButtonConfig) {
            return [$params];
        }

        if (is_array($params) && !empty($params)) {
            if (ArrayUtils::isObjectList($params) && $params[0] instanceof ButtonConfig) {
                return $params;
            }
            if (ArrayUtils::isAssoc($params)) {
                return [ButtonConfig::fromArray($params)];
            }
            if (ArrayUtils::isArrayList($params)) {
                $configs = [];
                foreach ($params as $config) {
                    $configs[] = ButtonConfig::fromArray($config);
                }
                return $configs;
            }
        }
        return $this->buttonConfig;
    }

    private function applyGlobalSettings(ButtonConfig $config): ButtonConfig
    {
        // The iconOnly flag is already set on the config from add()
        // So we just return the config as-is
        return $config;
    }

    private function buildButton(ButtonConfig $config): AbstractHtmlComponent
    {
        $button = $this->htmlBuilder->button()
            ->type($config->type)
            ->class(...$this->getButtonClasses($config));

        if ($config->id !== null) {
            $button->id($config->id);
        }

        if (!empty($config->attributes)) {
            $button->custom($config->attributes);
        }

        $this->addButtonContent($button, $config);

        return $button;
    }

    private function getButtonClasses(ButtonConfig $config): array
    {
        $classes = ['btn'];

        if (!empty($config->size)) {
            $classes[] = 'btn--' . $config->size;
        }

        if (!empty($config->style)) {
            $classes[] = 'btn--' . $config->style;
        }

        if ($config->isIconOnly()) {
            $classes[] = 'btn--icon-only';
        }

        return array_merge($classes, $config->buttonClass);
    }

    private function addButtonContent(AbstractHtmlComponent $button, ButtonConfig $config): void
    {
        $hasIcon = $config->hasIcon();
        $hasLabel = $config->hasLabel();

        if ($config->isIconOnly() && $hasIcon) {
            $button->add($this->createIcon($config));
            return;
        }

        if ($hasIcon && $hasLabel) {
            $iconSpan = $this->htmlBuilder->tag('span')
                ->class('btn__icon')
                ->add($this->createIcon($config));

            $labelSpan = $this->htmlBuilder->tag('span')
                ->class('btn__label')
                ->content($config->label);

            if ($config->iconPosition === ButtonConfig::ICON_POSITION_RIGHT) {
                $button->add($labelSpan, $iconSpan);
            } else {
                $button->add($iconSpan, $labelSpan);
            }
            return;
        }

        if ($hasIcon) {
            $button->add($this->createIcon($config));
            return;
        }

        if ($hasLabel) {
            $button->add(
                $this->htmlBuilder->tag('span')
                    ->class('btn__label')
                    ->content($config->label),
            );
        }
    }

    private function createIcon(ButtonConfig $config): AbstractHtmlComponent
    {
        $iconConfig = $config->iconConfig ?? IconConfig::create('', $config->ariaLabel);
        return $this->iconBuilder->createFromConfig($iconConfig);
    }

    private function wrapInForm(AbstractHtmlComponent $button, ButtonConfig $config): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $form = $html->form($this->formIncludeCsrf)
            ->action($this->formAction)
            ->method($this->formMethod)
            ->class(...$this->formClasses);

        if ($this->formId !== null) {
            $form->id($this->formId);
        }

        if (!empty($this->formAttributes)) {
            $form->custom($this->formAttributes);
        }

        $form->custom(['data-ajax-form' => 'true']);

        $hiddenFields = $this->buildHiddenFields($html);

        if (isset($config->attributes['data-entity-id'])) {
            $hiddenFields[] = $html->input('hidden')
                ->name('id')
                ->value($config->attributes['data-entity-id']);
        }

        $fields = array_merge($hiddenFields, $this->externalComponents, [$button]);
        $form->add(...$fields);
        return $form;
    }

    private function buildHiddenFields(HtmlBuilder $html): array
    {
        $fields = [];

        foreach ($this->formHiddenFields as $name => $value) {
            $fields[] = $html->input('hidden')
                ->name($name)
                ->value((string) $value);
        }

        return $fields;
    }

    private function resetFormState(): void
    {
        $this->formWrapEnabled = false;
        $this->formAction = null;
        $this->formMethod = 'POST';
        $this->formIncludeCsrf = true;
        $this->formId = null;
        $this->formClasses = [];
        $this->formHiddenFields = [];
        $this->formAttributes = [];
        $this->externalComponents = [];
    }
}