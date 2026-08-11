<?php

declare(strict_types=1);

class CheckoutPaymentMethodSection extends BaseFieldSection
{
    private const string INPUT_NAME = 'payment_method';

    /** @var array<PaymentMethodDto> */
    private array $paymentMethods;

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly FieldIdGenerator $idGenerator,
        private readonly ButtonBuilder $buttonBuilder,
        ?array $paymentMethods = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);

        $this->paymentMethods = $paymentMethods ?? PaymentMethodFactory::createDefaultPaymentMethods();
    }

    #[Override]
    public function getConfig(array $formValues = []): array
    {
        return $this->buildFormFields();
    }

    #[Override]
    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        return $this->buildPaymentOptions($fields);
    }

    #[Override]
    public function getKey(): string
    {
        return CheckoutSection::PAYMENT->value;
    }

    public function withPaymentMethods(array $methods): self
    {
        $this->paymentMethods = $methods;
        return $this;
    }

    public function addPaymentMethod(PaymentMethodDto $method): self
    {
        $this->paymentMethods[] = $method;
        return $this;
    }

    // ─── Form Field Builder ──────────────────────────────────────

    private function buildFormFields(): array
    {
        foreach ($this->paymentMethods as $method) {
            if ($method->hasFields()) {
                return $method->fields;
            }
        }

        return [];
    }

    // ─── Payment Options Builder ──────────────────────────────────

    private function buildPaymentOptions(array $fields): AbstractHtmlComponent
    {
        $config = $this->createPaymentConfig($fields);

        return (new SelectableOptionComponent(
            $this->htmlBuilder,
            $this->iconBuilder,
            $this->idGenerator,
            $config,
            $this->buttonBuilder,
        ))->build();
    }

    private function createPaymentConfig(array $fields): SelectableOptionConfig
    {
        return new SelectableOptionConfig(
            // ─── Container - Use-case specific ───
            selectableOptionTag: 'section',
            selectableOptionClass: ['checkout-section', 'payment-method'],
            selectableOptionId: 'payment-method-section',

            // ─── Header - Use-case specific ───
            selectableOptionsTitle: '4. Payment Method',
            headerClass: ['checkout-section__header'],
            selectableOptionTitleClass: ['checkout-section__title'],

            // ─── Fieldset - Use-case specific ───
            fieldsetClass: ['payment-options'],
            legendClass: [], // 'sr-only' is in base
            legendTitle: 'Choose a payment method',

            // ─── Options - Use-case specific ───
            // These classes apply to ALL payment options
            optionWrapperClass: ['payment-option'],
            optionHeaderClass: ['payment-header'],
            optionContentClass: ['payment-content'],
            optionInfoClass: ['payment-info'],
            infoTitleClass: ['payment-title'],
            infoDescriptionClass: [], // No use-case specific description class
            infoIconsClass: ['payment-icons'],

            // ─── Card Layout - Use-case specific ───
            asCards: true,
            cardClass: ['payment-option'],
            selectedClass: ['active'],

            // ─── Security - Use-case specific ───
            includeSecurity: true,
            securityText: '🔒 Your payment is encrypted and processed securely.',
            securityClass: ['payment-security'],
            securityIconClass: [], // Use default
            securityTextClass: [], // Use default
            securityIconContainerClass: [], // Use default

            // ─── Icons - Use-case specific ───
            defaultIconConfig: new IconConfig(
                icon: '',
                ariaLabel: 'Payment option',
                iconClass: ['icon--options'],
            ),

            // ─── Expandable content ───
            expandableContent: true,

            // ─── Options ───
            options: $this->buildPaymentOptionsFromConfig($fields),
        );
    }

    // ─── Option Builders ─────────────────────────────────────────

    private function buildPaymentOptionsFromConfig(array $fields): array
    {
        $options = [];

        foreach ($this->paymentMethods as $method) {
            $options[] = $this->createPaymentOption($method, $fields);
        }

        return $options;
    }

    private function createPaymentOption(PaymentMethodDto $method, array $fields): SelectableOptionDto
    {
        return match(true) {
            $method->hasFields() => $this->createCardOption($method, $fields),
            $method->hasContent() => $this->createContentOption($method),
            default => $this->createSimpleOption($method),
        };
    }

    private function createCardOption(PaymentMethodDto $method, array $fields): SelectableOptionDto
    {
        $html = $this->htmlBuilder;

        $content = $html->div()
            ->class('payment-content')
            ->add(...$fields);

        $icons = $this->buildIcons($method->icons);

        // ─── DTO handles 'active' via isDefault ───
        // No need to manually add it here
        return new SelectableOptionDto(
            title: $method->label,
            radioName: self::INPUT_NAME,
            radioValue: $method->value,
            isDefault: $method->isDefault, // DTO will add 'active' automatically
            description: $method->description,
            content: $content,
            icons: $icons,
            optionClass: [], // Empty - use-case classes come from config
            optionHeaderClass: [],
            optionContentClass: [],
            optionInfoClass: [],
            optionTitleClass: [],
            optionIconClass: [],
            optionId: $method->id . '-option',
            isExpandable: $method->isExpandable,
            isWrappedRadio: true,
        );
    }

    private function createContentOption(PaymentMethodDto $method): SelectableOptionDto
    {
        $html = $this->htmlBuilder;

        $content = $html->div()
            ->class('payment-content')
            ->add(
                $html->tag('p')->htmlBlock($method->content ?? ''),
            );

        return new SelectableOptionDto(
            title: $method->label,
            radioName: self::INPUT_NAME,
            radioValue: $method->value,
            isDefault: $method->isDefault, // DTO handles 'active'
            description: $method->description,
            content: $content,
            icons: $this->buildIcons($method->icons),
            optionClass: [],
            optionHeaderClass: [],
            optionContentClass: [],
            optionInfoClass: [],
            optionTitleClass: [],
            optionIconClass: [],
            optionId: $method->id . '-option',
            isExpandable: $method->isExpandable,
            isWrappedRadio: true,
        );
    }

    private function createSimpleOption(PaymentMethodDto $method): SelectableOptionDto
    {
        return new SelectableOptionDto(
            title: $method->label,
            radioName: self::INPUT_NAME,
            radioValue: $method->value,
            isDefault: $method->isDefault, // DTO handles 'active'
            description: $method->description,
            icons: $this->buildIcons($method->icons),
            optionClass: [],
            optionHeaderClass: [],
            optionContentClass: [],
            optionInfoClass: [],
            optionTitleClass: [],
            optionIconClass: [],
            optionId: $method->id . '-option',
            isExpandable: false,
            isWrappedRadio: true,
        );
    }

    // ─── Helper Methods ──────────────────────────────────────────

    /**
     * @param array<string> $iconNames
     *
     * @return array<IconConfig>
     */
    private function buildIcons(array $iconNames): array
    {
        $icons = [];
        foreach ($iconNames as $iconName) {
            $icons[] = new IconConfig(
                icon: $iconName,
                ariaLabel: ucfirst(str_replace('-', ' ', $iconName)),
            );
        }
        return $icons;
    }
}