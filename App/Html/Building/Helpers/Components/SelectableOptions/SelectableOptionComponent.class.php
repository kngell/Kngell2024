<?php

declare(strict_types=1);

class SelectableOptionComponent implements StandAloneComponentInterface
{
    private array $benefitComponents = [];
    private readonly ButtonBuilder $buttonBuilder;

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly IconBuilder $iconBuilder,
        private readonly FieldIdGenerator $idGenerator,
        private readonly SelectableOptionConfig $config,
        ButtonBuilder $buttonBuilder,
    ) {
        $this->buttonBuilder = $buttonBuilder;
    }

    public function addBenefitsComponent(StandAloneComponentInterface $benefit): self
    {
        $this->benefitComponents[] = $benefit;
        return $this;
    }

    public function build(mixed $params = null): null|array|AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $options = $this->config->options;

        if (empty($options)) {
            return null;
        }

        // Main container
        $container = $html->tag($this->config->selectableOptionTag)
            ->class(...$this->config->getSelectableOptionClasses());

        if ($this->config->selectableOptionId) {
            $container->id($this->config->selectableOptionId);
        }

        if (!empty($this->config->attributes)) {
            $container->attr(...$this->config->attributes);
        }

        // Header (optional)
        $container->add($this->buildHeader());

        // Options group
        $group = $html->tag('fieldset')
            ->class(...$this->config->getFieldsetClasses())
            ->add(
                $html->tag('legend')
                    ->class(...$this->config->getLegendClasses())
                    ->content($this->config->legendTitle),
            );

        foreach ($options as $option) {
            $group->add($this->buildOption($option));
        }

        $container->add($group);

        // Benefits section
        $container->add($this->buildBenefitsContainer());

        // Security
        if ($this->config->includeSecurity && $this->config->securityText) {
            $container->add($this->buildSecurity());
        }

        return $container;
    }

    // ─── Private Build Helpers ─────────────────────────────────────────

    private function buildHeader(): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        if (!$this->config->selectableOptionsTitle && !$this->config->subtitle) {
            return null;
        }

        $header = $html->tag('div')
            ->class(...$this->config->getHeaderClasses());

        if ($this->config->selectableOptionsTitle) {
            $header->add(
                $html->tag('h3')
                    ->class(...$this->config->getSelectableOptionTitleClasses())
                    ->content($this->config->selectableOptionsTitle),
            );
        }

        if ($this->config->subtitle) {
            $header->add(
                $html->tag('p')
                    ->class(...$this->config->getSubtitleClasses())
                    ->content($this->config->subtitle),
            );
        }

        return $header;
    }

    private function buildOption(SelectableOptionDto $option): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $wrapperClasses = $this->config->getOptionWrapperClasses(
            array_merge($option->optionClass, $option->optionActiveClass),
        );

        $radioId = $this->idGenerator->getUniqueId($option->optionId);
        $isExpandable = $this->config->expandableContent && $option->isExpandable;

        $wrapper = $html->div()
            ->class(...$wrapperClasses)
            ->custom($option->attributes);

        // Build content (may be null)
        $content = $this->buildOptionContent($option);

        // Build header with or without content based on expandable
        $header = $isExpandable
            ? $this->buildOptionHeader($option, $radioId)
            : $this->buildOptionHeader($option, $radioId, $content);

        // Add header (handles both array and single component)
        if (is_array($header)) {
            $wrapper->add(...$header);
        } else {
            $wrapper->add($header);
        }

        // If expandable, add content outside label
        if ($content && $isExpandable) {
            $wrapper->add($content);
        }

        // Action button (if present)
        $wrapper->add($this->buildActionButton($option));

        return $wrapper;
    }

    private function buildOptionHeader(
        SelectableOptionDto $option,
        string $radioId,
        ?AbstractHtmlComponent $content = null,
    ): null|array|AbstractHtmlComponent {
        $html = $this->htmlBuilder;

        $headerClasses = $this->config->getOptionHeaderClasses($option->optionHeaderClass);

        // Radio input
        $inputRadio = $html->input('radio')
            ->id($radioId)
            ->name($option->radioName)
            ->value($option->radioValue ?? $option->title);

        if ($option->isDefault) {
            $inputRadio->checked();
        }

        // ─── Info Component ─────────────────────────────────────────────
        $infoComponent = $html->span()
            ->class(...$this->config->getOptionInfoClasses($option->optionInfoClass));

        // ─── Title (optional) ──────────────────────────────────────────
        if ($option->hasTitle()) {
            $infoComponent->add(
                $html->span()
                    ->class(...$this->config->getInfoTitleClasses($option->optionTitleClass))
                    ->content($option->title),
            );
        }

        // ─── Description (optional) ─────────────────────────────────────
        if ($option->description) {
            $infoComponent->add(
                $html->span()
                    ->class(...$this->config->getInfoDescriptionClasses($option->optionDescriptionClass))
                    ->content($option->description),
            );
        }

        // ─── Icons ──────────────────────────────────────────────────────
        $icons = $this->buildIcons($option);
        if ($icons) {
            $infoComponent->add($icons);
        }

        // Label
        $label = $html->label()
            ->for($radioId)
            ->class(...$headerClasses);

        if ($option->isWrappedRadio) {
            return $label->add($inputRadio, $infoComponent, $content);
        }

        return [$inputRadio, $label->add($infoComponent, $content)];
    }

    private function buildIcons(SelectableOptionDto $option): ?AbstractHtmlComponent
    {
        if (empty($option->icons)) {
            return null;
        }

        $html = $this->htmlBuilder;
        $iconComponents = [];

        foreach ($option->icons as $icon) {
            if (is_string($icon)) {
                $iconComponents[] = $html->span()
                    ->class('icon-text')
                    ->content($icon);
                continue;
            }

            if ($icon instanceof IconConfig) {
                if ($this->config->defaultIconConfig) {
                    $icon = $this->config->defaultIconConfig->merge($icon->toArray());
                }
                $iconComponents[] = $this->iconBuilder->createFromConfig($icon);
            }
        }

        if (empty($iconComponents)) {
            return null;
        }

        // FIX: Pass option->optionInfoIconsClass to getInfoIconsClasses
        return $html->span()
            ->class(...$this->config->getInfoIconsClasses($option->optionInfoIconsClass))
            ->add(...$iconComponents);
    }

    private function buildOptionContent(SelectableOptionDto $option): ?AbstractHtmlComponent
    {
        if (!$option->content) {
            return null;
        }

        $html = $this->htmlBuilder;
        $contentClasses = $this->config->getOptionContentClasses($option->optionContentClass);
        $isExpandable = $this->config->expandableContent && $option->isExpandable;

        // Use span for non-expandable (inside label), div for expandable (outside label)
        $container = $isExpandable
            ? $html->div()
            : $html->span();

        if (is_string($option->content)) {
            return $container
                ->class(...$contentClasses)
                ->htmlBlock($option->content);
        }

        return $container
            ->class(...$contentClasses)
            ->add($option->content);
    }

    private function buildActionButton(SelectableOptionDto $option): ?AbstractHtmlComponent
    {
        if (!$option->hasAction()) {
            return null;
        }

        $attributes = [
            'data-action' => $option->action,
        ];

        if ($option->getModalAttribute()) {
            $attributes['data-modal'] = $option->getModalAttribute();
        }

        return $this->buttonBuilder
            ->fresh()
            ->add(
                type: $option->actionUrl ? 'link' : 'button',
                label: $option->actionLabel,
                buttonStyle: $option->actionStyle ?? 'primary',
                buttonSize: 'md',
                buttonClass: ['selectable-options__action-btn'],
                attributes: $attributes,
            )
            ->build();
    }

    private function buildBenefitsContainer(): ?AbstractHtmlComponent
    {
        if (empty($this->benefitComponents)) {
            return null;
        }

        $html = $this->htmlBuilder;

        $container = $html->tag('div')
            ->class(...$this->config->getBenefitsClasses());

        foreach ($this->benefitComponents as $benefit) {
            $benefitHtml = $benefit->build();
            if ($benefitHtml) {
                $container->add($benefitHtml);
            }
        }

        return $container;
    }

    private function buildSecurity(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $iconConfig = new IconConfig(
            icon: 'icon-lock',
            ariaLabel: 'Security',
            iconClass: ['lock', ...$this->config->getSecurityIconClasses()],
            role: 'img',
        );

        $securityIcon = $this->iconBuilder->createFromConfig($iconConfig);

        $securityText = $html->tag('p')
            ->class(...$this->config->getSecurityTextClasses())
            ->content($this->config->securityText ?? 'Secure');

        return $html->div()
            ->class(...$this->config->getSecurityClasses())
            ->add(
                $html->span()
                    ->class(...$this->config->getSecurityIconContainerClasses())
                    ->add($securityIcon),
                $securityText,
            );
    }
}