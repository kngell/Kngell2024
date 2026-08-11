<?php

declare(strict_types=1);

class UserAddressResponse extends AbstractBaseEntityResponse
{
    use EntityDisplayTrait;

    public function __construct(
        array $image,
        ?UserAddress $userAddress,
        private HtmlSectionPresentationService $presenter,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $userAddress, $isDefault);
    }

    public function getEntity(): ?UserAddress
    {
        return parent::getEntity();
    }

    public function hasAddresses(): bool
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return false;
        }
        $addresses = $entity->getAddress();
        return !empty($addresses) && count($addresses) > 0;
    }

    public function getDefaultShippingAddress(): array
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return [];
        }

        $addresses = $entity->getAddress();
        foreach ($addresses as $address) {
            if ($address instanceof Address && $address->isDefaultShipping()) {
                return $this->getAddress($address);
            }
        }
        return $this->getAddress($addresses[0] ?? null);
    }

    public function getDefaultBillingAddress(): array
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return [];
        }

        $addresses = $entity->getAddress();
        foreach ($addresses as $address) {
            if ($address instanceof Address && $address->isDefaultBilling()) {
                return $this->getAddress($address);
            }
        }
        return $this->getAddress($addresses[0] ?? null);
    }

    /**
     * @return Address[]
     */
    public function getAddresses(): array
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return [];
        }

        $addresses = $entity->getAddress();
        $result = [];

        foreach ($addresses as $address) {
            if ($address instanceof Address) {
                $result[] = $address;
            }
        }

        return $result;
    }

    /**
     * Get addresses filtered by address type (uses the AddressType enum).
     */
    public function getAddressesByType(string $type): array
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return [];
        }

        $addresses = $entity->getAddress();
        $result = [];

        foreach ($addresses as $address) {
            if (!$address instanceof Address) {
                continue;
            }

            // Get the AddressType enum from the address
            $addressType = $address->getAddressType();

            if ($type === 'shipping' && !$addressType->isShippingAllowed()) {
                continue;
            }

            if ($type === 'billing' && !$addressType->isBillingAllowed()) {
                continue;
            }

            $result[] = $this->getAddress($address);
        }

        return $result;
    }

    public function getShippingAddresses(): array
    {
        return $this->getAddressesByType('shipping');
    }

    public function getBillingAddresses(): array
    {
        return $this->getAddressesByType('billing');
    }

    public function getAddress(?Address $address = null): array
    {
        if ($address === null) {
            return [];
        }
        $country = $address->getCountry();

        return [
            'user_id' => $this->show($address, 'user_id'),
            'address_id' => (string) $address->getId(),
            'first_name' => $this->show($address, 'first_name'),
            'last_name' => $this->show($address, 'last_name'),
            'company' => $this->show($address, 'company'),
            'phone' => $this->show($address, 'phone'),
            'email' => $this->show($address, 'email'),
            'address1' => $this->show($address, 'address1'),
            'address2' => $this->show($address, 'address2'),
            'address_type' => $this->show($address, 'address_type'),
            'city' => $this->show($address, 'city'),
            'state' => $this->show($address, 'state'),
            'postal_code' => $this->show($address, 'postal_code'),
            'country_code' => $this->show($address, 'country_code'),
            'country' => $country instanceof Country ? $this->show($country, 'official_name'() ?? '') : '',
            'label' => $this->show($address, 'label'),
            'is_default_shipping' => $address->isDefaultShipping(),
            'is_default_billing' => $address->isDefaultBilling(),
            'is_verified' => $this->show($address, 'is_verified'),
            'validation_status' => $this->show($address, 'validation_status'),
            'validation_response' => $this->show($address, 'validation_response'),
            'validated_at' => $this->show($address, 'validated_at'),
            'is_active' => $this->show($address, 'is_active'),
            'deleted_at' => $this->show($address, 'deleted_at'),
            'created_at' => $this->show($address, 'created_at'),
            'updated_at' => $this->show($address, 'updated_at'),
        ];
    }

    public function getAddressesAsArray(): array
    {
        $addresses = $this->getAddresses();
        $result = [];

        foreach ($addresses as $address) {
            $result[] = $this->getAddress($address);
        }
        return $result;
    }

    public function toArray(): array
    {
        return [
            'addresses' => $this->getAddressesAsArray(),
            'hasAddresses' => $this->hasAddresses(),
            'defaultShipping' => $this->getDefaultShippingAddress(),
            'defaultBilling' => $this->getDefaultBillingAddress(),
        ];
    }
}