<?php

declare(strict_types=1);

class CheckoutShippingMethodSection extends AbstractBaseHtmlSection
{
    private const string INPUT_NAME = 'shippingMethod';

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly ShippingMethodService $service,
        private readonly RegionContextInterface $regionContext,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly FieldIdGenerator $idGenerator,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $countryCode = $this->regionContext->getRegionCode() ?: 'FR';
        $responses = $this->service->getShippingMethodsForCurrentCart(
            isoCode: $countryCode,
            limit: 50,
            offset: 0,
        );

        return $html->div()
            ->class('shipping-section')
            ->add(
                $this->buildHeader(),
                $this->buildShippingOptions($responses),
                $this->buildGiftSection(),
            );
    }

    #[Override]
    public function getKey(): string
    {
        return CheckoutSection::SHIPPING->value;
    }

    // ─── Build Helpers ─────────────────────────────────────────────

    private function buildHeader(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('shipping-section__header')
            ->add(
                $html->tag('h3')
                    ->class('shipping-section__title')
                    ->content('Choose Shipping Method'),
                $html->tag('p')
                    ->class('shipping-section__subtitle')
                    ->content('Estimated delivery times based on your location'),
            );
    }

    /**
     * @param array<ShippingMethodResponse> $responses
     */
    private function buildShippingOptions(array $responses): AbstractHtmlComponent
    {
        $options = $this->createShippingOptions($responses);

        if (empty($options)) {
            return $this->htmlBuilder->tag('p')
                ->class('shipping-section__empty')
                ->content('No shipping methods available for your location.');
        }

        $config = new SelectableOptionConfig(
            selectableOptionTag: 'div',
            selectableOptionClass: ['shipping-methods'],
            selectableOptionId: 'shipping-methods-section',
            fieldsetClass: ['shipping-methods__fieldset'],
            legendClass: ['sr-only'],
            legendTitle: 'Select a shipping method',
            optionWrapperClass: ['shipping-method'],
            optionHeaderClass: ['shipping-method__label'],
            optionInfoClass: ['shipping-method__info'],
            infoTitleClass: ['shipping-method__name'],
            infoDescriptionClass: ['shipping-method__description'],
            infoIconsClass: ['shipping-method__icons'],
            optionContentClass: ['shipping-method__content'],
            asCards: true,
            cardClass: ['shipping-method'],
            selectedClass: ['shipping-method--selected'],
            options: $options,
            attributes: [
                'role' => 'radiogroup',
                'aria-label' => 'Shipping Methods',
            ],
            expandableContent: false, // Shipping content always visible
        );

        return (new SelectableOptionComponent(
            $this->htmlBuilder,
            $this->iconBuilder,
            $this->idGenerator,
            $config,
            $this->buttonBuilder,
        ))->build();
    }

    /**
     * @param array<ShippingMethodResponse> $responses
     *
     * @return array<SelectableOptionDto>
     */
    private function createShippingOptions(array $responses): array
    {
        $options = [];

        foreach ($responses as $response) {
            if (!$response->isAvailable()) {
                continue;
            }

            $options[] = $this->createShippingOption($response);
        }

        return $options;
    }

    private function createShippingOption(ShippingMethodResponse $response): SelectableOptionDto
    {
        $attributes = [
            'data-method-code' => $response->getCode(),
            'data-carrier' => $response->getCarrier(),
        ];

        $entity = $response->getEntity();
        if ($entity) {
            $attributes['data-method-id'] = $entity->getId();
        }

        return new SelectableOptionDto(
            title: $response->getName(),
            radioName: self::INPUT_NAME,
            radioValue: $response->getCode(),
            isDefault: $response->isSelected(),
            description: $response->getDescription(),
            content: $this->buildShippingContent($response),
            icons: $this->buildShippingIcons($response),
            optionClass: ['shipping-method'],
            optionTitleClass: ['shipping-method__name'],
            optionContentClass: ['shipping-method__content'],
            optionInfoClass: ['shipping-method__info'],
            optionIconClass: ['shipping-method__icon'],
            optionId: 'shipping-' . $response->getCode(),
            isWrappedRadio: true,
            attributes: $attributes,
            isExpandable: false, // Shipping content always visible
        );
    }

    private function buildShippingContent(ShippingMethodResponse $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->span()
            ->class('shipping-method__details')
            ->add(
                $html->span()
                    ->class('shipping-method__cost')
                    ->add(
                        $html->span()
                            ->class('shipping-method__cost-amount')
                            ->content($response->getRateDisplay()),
                        $this->buildFreeBadge($response),
                    ),
                $html->span()
                    ->class('shipping-method__delivery')
                    ->add(
                        $html->span()
                            ->class('shipping-method__delivery-icon')
                            ->content('📦'),
                        $html->span()
                            ->class('shipping-method__delivery-text')
                            ->content($response->getDeliveryEstimate()),
                    ),
                $this->buildZoneInfo($response),
            );
    }

    private function buildFreeBadge(ShippingMethodResponse $response): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $rateDisplay = $response->getRateDisplay();

        if (str_contains(strtolower($rateDisplay), 'free') || $rateDisplay === '€0.00' || $rateDisplay === '0.00€') {
            return $html->span()
                ->class('shipping-method__rate-badge', 'shipping-method__rate-badge--free')
                ->content('FREE');
        }

        return null;
    }

    private function buildZoneInfo(ShippingMethodResponse $response): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $zoneName = $response->getZoneName();
        $countries = $response->getZoneCountries();

        if (empty($zoneName) && empty($countries)) {
            return null;
        }

        $info = [];
        if (!empty($zoneName)) {
            $info[] = $zoneName;
        }
        if (!empty($countries)) {
            $info[] = $countries;
        }

        return $html->span()
            ->class('shipping-method__zone')
            ->add(
                $html->span()
                    ->class('shipping-method__zone-label')
                    ->content('Ships to:'),
                $html->span()
                    ->class('shipping-method__zone-list')
                    ->content(implode(' • ', $info)),
            );
    }

    /**
     * @return array<IconConfig>
     */
    private function buildShippingIcons(ShippingMethodResponse $response): array
    {
        $methodName = strtolower($response->getName());
        $carrier = strtolower($response->getCarrier());

        $iconName = match(true) {
            str_contains($carrier, 'dhl') => 'shipping-dhl',
            str_contains($carrier, 'fedex') => 'shipping-fedex',
            str_contains($carrier, 'ups') => 'shipping-ups',
            str_contains($carrier, 'usps'), str_contains($carrier, 'post') => 'shipping-post',
            str_contains($methodName, 'express') => 'shipping-express',
            str_contains($methodName, 'next day'), str_contains($methodName, '24h') => 'shipping-nextday',
            str_contains($methodName, 'pickup'), str_contains($methodName, 'store') => 'shipping-pickup',
            str_contains($methodName, 'free') => 'shipping-free',
            default => 'shipping-standard',
        };

        return [
            new IconConfig(
                icon: $iconName,
                ariaLabel: $response->getName(),
                iconClass: ['shipping-method__icon-svg'],
                role: 'img',
            ),
        ];
    }

    private function buildGiftSection(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $field = FormFieldConfig::create(
            name: 'isGift',
            type: 'checkbox',
            defaultValue: '1',
            label: 'This order is a gift',
            placeholder: ' ',
            attributes: ['autocomplete' => 'off'],
            id: 'isGift',
        );

        return $html->div()
            ->class('gift-section')
            ->add(
                $html->tag('h4')
                    ->class('gift-section__title')
                    ->content('Gift Options'),
                StandaloneInputHelper::create($html)->build([
                    'field' => $field,
                    'layoutType' => 'checkbox',
                ]),
            );
    }
}