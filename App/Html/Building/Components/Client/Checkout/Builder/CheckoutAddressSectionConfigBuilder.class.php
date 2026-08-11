<?php

declare(strict_types=1);

class CheckoutAddressSectionConfigBuilder implements FormSectionConfigBuilderInterface
{
    private bool $isLoggedIn = false;

    public function __construct(
        private readonly UserContext $userContext,
    ) {
        $this->isLoggedIn = $userContext->isLoggedIn();
    }

    #[Override]
    public function buildMediaConfig(): array
    {
        return [];
    }

    #[Override]
    public function buildRegularConfig(): array
    {
        $configs = [];

        // ============================================
        // SECTION 1: RECIPIENT INFORMATION
        // ============================================
        $configs[] = RegularSectionConfig::create(
            key: CheckoutAddressSectionKeys::RECIPIENT->value,
            title: 'Recipient Information',
            sectionId: 'recipient_section',
        )->setSectionBodyId('recipient_fields_container')
        ->setSectionClassBody(['address-section', 'recipient-section'])
        ->addField(
            FormFieldConfig::create(
                name: 'first_name',
                type: 'text',
            )->setLabel('First Name')
            ->setPlaceholder(' ')
            ->setRequired()
            ->setFooter(['error' => 'First name is required']),
        )->addField(
            FormFieldConfig::create(
                name: 'last_name',
                type: 'text',
            )->setLabel('Last Name')
            ->setPlaceholder(' ')
            ->setRequired()
            ->setFooter(['error' => 'Last name is required']),
        )->addField(
            FormFieldConfig::create(
                name: 'company',
                type: 'text',
            )->setLabel('Company (Optional)')
            ->setPlaceholder(' ')
            ->setFooter(['hint' => 'Optional - leave blank if not applicable']),
        )->addField(
            FormFieldConfig::create(
                name: 'phone',
                type: 'tel',
            )->setLabel('Phone Number')
            ->setPlaceholder(' ')
            ->setRequired()
            ->setFooter(['error' => 'Valid phone number is required']),
        )->addField(
            FormFieldConfig::create(
                name: 'email',
                type: 'email',
            )->setLabel('Email Address')
            ->setPlaceholder(' ')
            ->setRequired()
            ->setFooter(['error' => 'Valid email address is required']),
        )->setShowRequired(true)
        ->setRowIndicesConfig([
            [
                'indices' => [0, 1],  // first_name, last_name
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [2],  // company
                'class' => ['form-row'],
            ],
            [
                'indices' => [3, 4],  // phone, email
                'class' => ['form-row', 'horizontal'],
            ],
        ]);

        // ============================================
        // SECTION 2: ADDRESS DETAILS
        // ============================================
        $configs[] = RegularSectionConfig::create(
            key: CheckoutAddressSectionKeys::ADDRESS->value,
            title: 'Address Details',
            sectionId: 'address_details_section',
        )->setSectionBodyId('address_fields_container')
        ->setSectionClassBody(['address-section', 'address-details-section'])
        ->addField(
            FormFieldConfig::create(
                name: 'address1',
                type: 'text',
            )->setLabel('Address Line 1')
            ->setPlaceholder(' ')
            ->setRequired()
            ->setFooter(['error' => 'Address is required']),
        )->addField(
            FormFieldConfig::create(
                name: 'address2',
                type: 'text',
            )->setLabel('Address Line 2 (Optional)')
            ->setPlaceholder(' ')
            ->setFooter(['hint' => 'Apartment, suite, unit, building, floor, etc.']),
        )->addField(
            FormFieldConfig::create(
                name: 'city',
                type: 'text',
            )->setLabel('City')
            ->setPlaceholder(' ')
            ->setRequired()
            ->setFooter(['error' => 'City is required']),
        )->addField(
            FormFieldConfig::create(
                name: 'state',
                type: 'select',
            )->setLabel('State / Province')
            ->setPlaceholder('Select State')
            ->setRequired()
            ->setOptions($this->getStateOptions())
            ->setFooter(['error' => 'Please select a state']),
        )->addField(
            FormFieldConfig::create(
                name: 'postal_code',
                type: 'text',
            )->setLabel('Postal Code')
            ->setPlaceholder(' ')
            ->setRequired()
            ->setFooter(['error' => 'Postal code is required']),
        )->addField(
            FormFieldConfig::create(
                name: 'country_id',
                type: 'select',
            )->setLabel('Country')
            ->setPlaceholder('Select Country')
            ->setRequired()
            ->setOptions($this->getCountryOptions())
            ->setFooter(['error' => 'Please select a country']),
        )->setShowRequired(true)
        ->setRowIndicesConfig([
            [
                'indices' => [0],  // address1
                'class' => ['form-row'],
            ],
            [
                'indices' => [1],  // address2
                'class' => ['form-row'],
            ],
            [
                'indices' => [2, 3],  // city, state
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [4, 5],  // postal_code, country_id
                'class' => ['form-row', 'horizontal'],
            ],
        ]);

        // ============================================
        // SECTION 3: ADDRESS PREFERENCES (Logged-in only)
        // ============================================
        if ($this->isLoggedIn) {
            $configs[] = RegularSectionConfig::create(
                key: CheckoutAddressSectionKeys::PREFERENCES->value,
                title: 'Address Preferences',
                sectionId: 'preferences_section',
            )->setSectionBodyId('preferences_fields_container')
            ->setSectionClassBody(['address-section', 'preferences-section'])
            ->addField(
                FormFieldConfig::create(
                    name: 'is_default_shipping',
                    type: 'checkbox',
                )->setLabel('Set as default shipping address')
                ->setFooter(['hint' => 'This address will be pre-selected for future orders']),
            )->addField(
                FormFieldConfig::create(
                    name: 'is_default_billing',
                    type: 'checkbox',
                )->setLabel('Set as default billing address')
                ->setFooter(['hint' => 'This address will be pre-selected for billing']),
            )->addField(
                FormFieldConfig::create(
                    name: 'save_address',
                    type: 'checkbox',
                )->setLabel('Save this address for future orders')
                ->setDefaultValue('1')
                ->setFooter(['hint' => 'We\'ll store this address in your account']),
            )->setShowRequired(false)
            ->setRowIndicesConfig([
                [
                    'indices' => [0],  // is_default_shipping
                    'class' => ['form-row', 'checkbox-row'],
                ],
                [
                    'indices' => [1],  // is_default_billing
                    'class' => ['form-row', 'checkbox-row'],
                ],
                [
                    'indices' => [2],  // save_address
                    'class' => ['form-row', 'checkbox-row', 'highlighted'],
                ],
            ]);
        }

        return $configs;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    private function getStateOptions(): array
    {
        return [
            '' => 'Select State/Province',
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];
    }

    private function getCountryOptions(): array
    {
        // Ideally fetch from Country repository
        return [
            '' => 'Select Country',
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'PT' => 'Portugal',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'AT' => 'Austria',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'IE' => 'Ireland',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'JP' => 'Japan',
            'CN' => 'China',
            'IN' => 'India',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
        ];
    }
}