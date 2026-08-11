<?php

declare(strict_types=1);

class CheckoutAddressSection extends BaseFieldSection
{
    private const string SAME_AS_SHIPPING_NAME = 'billingSameAsShipping';

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly FieldIdGenerator $idGenerator,
        private readonly ShippingAddressService $shippingAddress,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $cartItems = []): array|AbstractHtmlComponent
    {
        $response = $this->shippingAddress->getUserAddress();
        $shippingAddresses = $response?->getShippingAddresses() ?? [];

        if (empty($shippingAddresses)) {
            return [];
        }

        return [
            FormFieldConfig::create(
                name: self::SAME_AS_SHIPPING_NAME,
                type: 'checkbox',
                wrapperClass: ['same-as-shipping-toggle'],
                id: self::SAME_AS_SHIPPING_NAME,
                defaultValue: 1,
                label: 'Same as shipping address',
            ),
        ];
    }

    #[Override]
    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        return $this->buildAddressSection($fields);
    }

    #[Override]
    public function getKey(): string
    {
        return CheckoutSection::ADDRESS->value;
    }

    private function buildAddressSection(array $fields): AbstractHtmlComponent
    {
        $response = $this->shippingAddress->getUserAddress();
        $shippingAddresses = $response?->getShippingAddresses() ?? [];
        $billingAddresses = $response?->getBillingAddresses() ?? [];

        return $this->htmlBuilder->div()
            ->class('checkout__address-section')
            ->add(
                $this->buildAddressSelection($shippingAddresses, $billingAddresses, $fields),
            );
    }

    private function buildAddressSelection(array $shippingAddresses, array $billingAddresses, array $fields): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $container = $html->div()->class('address-selection');
        $container->add(
            $html->tag('h3')
                ->class('address-selection__title')
                ->content('Shipping & Billing Addresses'),
        );

        $grid = $html->div()->class('address-selection__grid');
        $grid->add($this->buildAddressSectionBlock('shipping', 'Shipping Address', $shippingAddresses, '📦'));
        $grid->add($this->buildBillingSection($billingAddresses, $fields));

        $container->add($grid);

        $container->add(
            $html->div()->class('address-selection__button')->add(
                $this->buttonBuilder->add(
                    type: 'link',
                    label: 'Add New Address',
                    buttonStyle: 'secondary',
                    buttonSize: 'md',
                    buttonClass: ['add-address-btn'],
                    attributes: ['href' => '#addressModal'],
                    icon: 'icon-plus',
                    iconPosition: 'left',
                    ariaLabel: 'Add New',
                )->build(),
            ),
        );

        return $container;
    }

    private function buildAddressSectionBlock(string $type, string $label, array $addresses, string $icon): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $section = $html->div()
            ->class('address-section', "address-section--{$type}");

        // Header
        $section->add(
            $html->div()
                ->class('address-section__header')
                ->add(
                    $html->tag('span')
                        ->class('title')
                        ->add(
                            $html->tag('span')->class('icon')->content($icon),
                            $html->text(' ' . $label),
                        ),
                    $html->tag('span')->class('badge')->content('Required'),
                ),
        );

        if (empty($addresses)) {
            $section->add(
                $html->div()
                    ->class('address-section__empty')
                    ->content('No ' . $type . ' address saved.'),
            );
        } else {
            $section->add(
                $this->buildAddressOptions($type, $addresses),
            );
        }

        return $section;
    }

    private function buildBillingSection(array $billingAddresses, array $fields): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $section = $html->div()
            ->class('address-section', 'address-section--billing')
            ->attribute('data-billing-section', '');

        $header = $html->div()->class('address-section__header');

        $title = $html->tag('span')
            ->class('title')
            ->add(
                $html->tag('span')->class('icon')->content('💳'),
                $html->text(' Billing Address'),
            );

        if (!empty($fields)) {
            $header->add($title, $fields[0]);
        } else {
            $header->add($title);
        }

        $section->add($header);

        if (empty($billingAddresses)) {
            $section->add(
                $html->div()
                    ->class('address-section__empty')
                    ->content('No billing address saved. Add one below.'),
            );
        } else {
            $section->add(
                $this->buildAddressOptions('billing', $billingAddresses),
            );
        }

        return $section;
    }

    private function buildAddressOptions(string $type, array $addresses): AbstractHtmlComponent
    {
        $selectedId = $this->getSelectedAddressId($type, $addresses);
        $options = [];

        foreach ($addresses as $address) {
            if (empty($address['address_id'])) {
                continue;
            }

            $addressId = (string) $address['address_id'];
            $isSelected = $addressId === $selectedId;

            // Build content with address details
            $content = $this->buildAddressContent($address);

            // Build actions (edit/delete buttons)
            $actions = $this->buildAddressActions($addressId, $type);

            // Combine content and actions in the info-icons section
            $icons = $actions ? [$actions] : [];

            $options[] = new SelectableOptionDto(
                radioName: $type . 'Address',
                radioValue: $addressId,
                isDefault: $isSelected,
                content: $content,
                icons: $icons ? [$icons] : [],

                // ─── ALL CLASSES IN ONE PLACE ──────────────────────────────────────
                // This is the SINGLE SOURCE OF TRUTH for option styling
                optionClass: ['address-item'],
                optionHeaderClass: ['address-item__header'],
                optionContentClass: ['address-item__content'],
                optionInfoClass: ['address-item__info'],
                optionTitleClass: ['address-item__title'],
                optionIconClass: ['address-item__actions'],
                optionDescriptionClass: ['address-item__details'],
                optionInfoIconsClass: ['address-item__actions'],

                // ─── Metadata ──────────────────────────────────────────────────────────
                optionId: "{$type}-address-{$addressId}",
                isWrappedRadio: true,
                attributes: [
                    'data-address-id' => $addressId,
                    'data-address-type' => $type,
                ],
                isExpandable: false,
            );
        }

        $config = new SelectableOptionConfig(
            // ─── Global/Structural ──────────────────────────────────────────
            selectableOptionTag: 'div',
            selectableOptionClass: ['address-options', "address-options--{$type}"],
            selectableOptionsTitle: null,
            legendTitle: 'Select ' . $type . ' address',
            asCards: true,
            cardClass: [],
            selectedClass: ['address-item--selected'],
            expandableContent: false,
            options: $options,
        );

        return (new SelectableOptionComponent(
            $this->htmlBuilder,
            $this->iconBuilder,
            $this->idGenerator,
            $config,
            $this->buttonBuilder,
        ))->build();
    }

    private function buildAddressContent(array $address): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->span()
            ->class('address-item__content')
            ->add(
                $html->span()
                    ->class('address-item-header')
                    ->add(
                        $html->tag('h4')->content($this->buildAddressName($address)),
                    ),
                $html->span()
                    ->class('address-item-details')
                    ->add(
                        $html->tag('span')
                            ->class('address-line')
                            ->content($this->formatAddressLine($address)),
                        $html->tag('span')
                            ->class('address-line')
                            ->content($this->formatCityStateZip($address)),
                    ),
            );
    }

    private function buildAddressDescription(array $address): string
    {
        $parts = [];
        if (!empty($address['address1'])) {
            $parts[] = $address['address1'];
        }
        if (!empty($address['address2'])) {
            $parts[] = $address['address2'];
        }
        $parts[] = $this->formatCityStateZip($address);
        return implode(', ', $parts);
    }

    private function buildAddressActions(string $addressId, string $type): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->span()
            ->class('address-item__actions')
            ->add(
                $this->buttonBuilder->add(
                    type: 'link',
                    label: '',
                    buttonStyle: 'outline',
                    buttonSize: 'sm',
                    buttonClass: ['icon-btn'],
                    attributes: [
                        'href' => '#addressModal',
                        'aria-label' => 'Edit address',
                        'data-address-id' => $addressId,
                        'data-address-type' => $type,
                    ],
                    iconConfig: new IconConfig(
                        icon: 'icon-edit',
                        ariaLabel: 'Edit address',
                        iconClass: ['icon--edit'],
                    ),
                )->build(),
                $this->buttonBuilder->add(
                    type: 'button',
                    label: '',
                    buttonStyle: 'outline',
                    buttonSize: 'sm',
                    buttonClass: ['icon-btn', 'delete'],
                    attributes: [
                        'aria-label' => 'Delete address',
                        'data-address-id' => $addressId,
                        'data-address-type' => $type,
                    ],
                    iconConfig: new IconConfig(
                        icon: 'icon-trash',
                        ariaLabel: 'Delete address',
                        iconClass: ['icon--delete'],
                    ),
                )->build(),
            );
    }

    // ─── Helper Methods ──────────────────────────────────────────────

    private function buildAddressName(array $address): string
    {
        $firstName = $address['first_name'] ?? '';
        $lastName = $address['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName) ?: 'Home';
    }

    private function formatAddressLine(array $address): string
    {
        $parts = [];
        if (!empty($address['address1'])) {
            $parts[] = $address['address1'];
        }
        if (!empty($address['address2'])) {
            $parts[] = $address['address2'];
        }
        return implode(', ', $parts);
    }

    private function formatCityStateZip(array $address): string
    {
        $parts = [];
        if (!empty($address['city'])) {
            $parts[] = $address['city'];
        }
        if (!empty($address['state'])) {
            $parts[] = $address['state'];
        }
        if (!empty($address['postal_code'])) {
            $parts[] = $address['postal_code'];
        }
        return implode(', ', $parts);
    }

    private function getSelectedAddressId(string $type, array $addresses): ?string
    {
        $defaultKey = $type === 'shipping' ? 'is_default_shipping' : 'is_default_billing';

        foreach ($addresses as $address) {
            if (!empty($address[$defaultKey]) && !empty($address['address_id'])) {
                return (string) $address['address_id'];
            }
        }

        foreach ($addresses as $address) {
            if (!empty($address['address_id'])) {
                return (string) $address['address_id'];
            }
        }

        return null;
    }
}