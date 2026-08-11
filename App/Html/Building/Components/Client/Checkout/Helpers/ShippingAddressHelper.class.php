<?php

declare(strict_types=1);

final class ShippingAddressHelper
{
    public function __construct(private readonly CountryService $countryService, private array $addresses)
    {
    }

    public function getFormFields(): array
    {
        $addresses = $this->addresses;
        return [
            FormFieldConfig::create(
                name: 'shippingAddress',
                type: 'radio',
            )
                ->setOptions($this->getAddressOptions($addresses))
                ->setDefaultValue($this->getDefaultAddressId($addresses))
                ->setAttributes([
                    'aria-label' => 'Select shipping address',
                ]),

            // 1: Billing Same as Shipping Checkbox
            FormFieldConfig::create(
                name: 'billingSameAsShipping',
                type: 'checkbox',
            )
                ->setDefaultValue(true)
                ->setLabel('Same as shipping address')
                ->setAttributes([
                    'aria-label' => 'Billing same as shipping',
                    'data-toggle' => 'billing-fields',
                ]),

            // 2: Billing First Name
            FormFieldConfig::create(
                name: 'billingFirstName',
                type: 'text',
            )
                ->setLabel('First Name')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'first_name'))
                ->setAttributes([
                    'autocomplete' => 'given-name',
                    'placeholder' => ' ',
                ]),

            // 3: Billing Last Name
            FormFieldConfig::create(
                name: 'billingLastName',
                type: 'text',
            )
                ->setLabel('Last Name')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'last_name'))
                ->setAttributes([
                    'autocomplete' => 'family-name',
                    'placeholder' => ' ',
                ]),

            // 4: Billing Company
            FormFieldConfig::create(
                name: 'billingCompany',
                type: 'text',
            )
                ->setLabel('Company')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'company'))
                ->setAttributes([
                    'autocomplete' => 'organization',
                    'placeholder' => ' ',
                ]),

            // 5: Billing Address Line 1
            FormFieldConfig::create(
                name: 'billingAddressLine1',
                type: 'text',
            )
                ->setLabel('Address Line 1')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'address1'))
                ->setAttributes([
                    'autocomplete' => 'address-line1',
                    'placeholder' => ' ',
                ]),

            // 6: Billing Address Line 2
            FormFieldConfig::create(
                name: 'billingAddressLine2',
                type: 'text',
            )
                ->setLabel('Address Line 2')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'address2'))
                ->setAttributes([
                    'autocomplete' => 'address-line2',
                    'placeholder' => ' ',
                ]),

            // 7: Billing City
            FormFieldConfig::create(
                name: 'billingCity',
                type: 'text',
            )
                ->setLabel('City')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'city'))
                ->setAttributes([
                    'autocomplete' => 'address-level2',
                    'placeholder' => ' ',
                ]),

            // 8: Billing State/Province
            FormFieldConfig::create(
                name: 'billingState',
                type: 'select',
            )
                ->setLabel('State/Province')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'state'))
                ->setOptions($this->getStateOptions())
                ->setAttributes([
                    'autocomplete' => 'address-level1',
                    'placeholder' => ' ',
                ])
                ->withRightIcon(),

            // 9: Billing Postal Code
            FormFieldConfig::create(
                name: 'billingPostalCode',
                type: 'text',
            )
                ->setLabel('Postal Code')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'postal_code'))
                ->setAttributes([
                    'autocomplete' => 'postal-code',
                    'placeholder' => ' ',
                ]),

            // 10: Billing Country
            FormFieldConfig::create(
                name: 'billingCountry',
                type: 'select',
            )
                ->setLabel('Country')
                ->setDefaultValue($this->getDefaultBillingCountryCode($addresses))
                ->setOptions($this->getCountryOptions())
                ->setAttributes([
                    'autocomplete' => 'country',
                    'placeholder' => ' ',
                ])
                ->withRightIcon(),

            // 11: Billing Phone
            FormFieldConfig::create(
                name: 'billingPhone',
                type: 'text',
            )
                ->setLabel('Phone')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'phone'))
                ->setAttributes([
                    'autocomplete' => 'tel',
                    'placeholder' => ' ',
                ]),

            // 12: Billing Email
            FormFieldConfig::create(
                name: 'billingEmail',
                type: 'text',
            )
                ->setLabel('Email')
                ->setDefaultValue($this->getDefaultBillingValue($addresses, 'email'))
                ->setAttributes([
                    'autocomplete' => 'email',
                    'placeholder' => ' ',
                ]),
        ];
    }

    private function getAddressOptions(array $addresses): array
    {
        $options = [];
        foreach ($addresses as $address) {
            $options[] = [
                'value' => (string) $address->getId(),
                'label' => $this->buildAddressName($address),
                'selected' => $address->isDefaultShipping(),
            ];
        }
        return $options;
    }

    private function buildAddressName(Address $address): string
    {
        $firstName = $address->getFirstName();
        $lastName = $address->getLastName();
        return trim($firstName . ' ' . $lastName) ?: 'Home';
    }

    private function getDefaultAddressId(array $addresses): ?string
    {
        foreach ($addresses as $address) {
            if ($address->isDefaultShipping()) {
                return (string) $address->getId();
            }
        }
        return !empty($addresses) ? (string) $addresses[0]->getId() : null;
    }

    private function getDefaultBillingValue(array $addresses, string $field): ?string
    {
        $address = $this->getDefaultAddress($addresses);
        if (!$address) {
            return null;
        }

        return match ($field) {
            'first_name' => $address->getFirstName(),
            'last_name' => $address->getLastName(),
            'company' => $address->getCompany(),
            'address1' => $address->getAddress1(),
            'address2' => $address->getAddress2(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'postal_code' => $address->getPostalCode(),
            'phone' => $address->getPhone(),
            'email' => $address->getEmail(),
            default => null,
        };
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

    private function getDefaultBillingCountryCode(array $addresses): ?string
    {
        $address = $this->getDefaultAddress($addresses);
        if (!$address) {
            return null;
        }

        $country = $address->getCountry();
        return $country instanceof Country ? $country->getIsoCode() : null;
    }

    /**
     * @param Address[] $addresses
     */
    private function getDefaultAddress(array $addresses): ?Address
    {
        foreach ($addresses as $address) {
            if ($address->isDefaultShipping()) {
                return $address;
            }
        }
        return $addresses[0] ?? null;
    }

    private function getCountryOptions(): array
    {
        return $this->countryService->getCountryOptions();
    }
}