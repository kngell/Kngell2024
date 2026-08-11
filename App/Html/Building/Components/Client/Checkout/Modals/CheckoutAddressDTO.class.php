<?php

declare(strict_types=1);

final class CheckoutAddressDTO extends BaseFooterDTO
{
    private ?string $firstName;
    private ?string $lastName;
    private ?string $company;
    private ?string $phone;
    private ?string $email;
    private ?string $address1;
    private ?string $address2;
    private ?string $city;
    private ?string $state;
    private ?string $postalCode;
    private ?int $countryId;
    private bool $isDefaultShipping;
    private bool $isDefaultBilling;
    private bool $isVerified;
    private string $validationStatus;
    private array $validationResponse;
    private ?string $validatedAt;
    private string $addressType; // 'shipping' or 'billing'
    private bool $isLoggedIn;

    public function __construct(
        string $cancelRoute,
        string $deleteRoute,
        ?int $id = null,
        bool $isVisible = false,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $company = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $address1 = null,
        ?string $address2 = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postalCode = null,
        ?int $countryId = null,
        bool $isDefaultShipping = false,
        bool $isDefaultBilling = false,
        bool $isVerified = false,
        string $validationStatus = 'PENDING',
        array $validationResponse = [],
        ?string $validatedAt = null,
        string $addressType = 'shipping',
        bool $isLoggedIn = false,
        int $sortOrder = 0,
        bool $isActive = true,
        ?string $validFrom = null,
        ?string $validTo = null,
    ) {
        parent::__construct(
            cancelRoute: $cancelRoute,
            deleteRoute: $deleteRoute,
            isVisible: $isVisible,
            id: $id,
            sortOrder: $sortOrder,
            isActive: $isActive,
            validFrom: $validFrom,
            validTo: $validTo,
        );

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->company = $company;
        $this->phone = $phone;
        $this->email = $email;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->countryId = $countryId;
        $this->isDefaultShipping = $isDefaultShipping;
        $this->isDefaultBilling = $isDefaultBilling;
        $this->isVerified = $isVerified;
        $this->validationStatus = $validationStatus;
        $this->validationResponse = $validationResponse;
        $this->validatedAt = $validatedAt;
        $this->addressType = $addressType;
        $this->isLoggedIn = $isLoggedIn;
    }

    // ─── Getters ──────────────────────────────────────────────

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    public function isDefaultShipping(): bool
    {
        return $this->isDefaultShipping;
    }

    public function isDefaultBilling(): bool
    {
        return $this->isDefaultBilling;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getValidationStatus(): string
    {
        return $this->validationStatus;
    }

    public function getValidationResponse(): array
    {
        return $this->validationResponse;
    }

    public function getValidatedAt(): ?string
    {
        return $this->validatedAt;
    }

    public function getAddressType(): string
    {
        return $this->addressType;
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    // ─── Setters with static return type ──────────────────────

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function setAddress1(?string $address1): static
    {
        $this->address1 = $address1;
        return $this;
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;
        return $this;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function setCountryId(?int $countryId): static
    {
        $this->countryId = $countryId;
        return $this;
    }

    public function setIsDefaultShipping(bool $isDefaultShipping): static
    {
        $this->isDefaultShipping = $isDefaultShipping;
        return $this;
    }

    public function setIsDefaultBilling(bool $isDefaultBilling): static
    {
        $this->isDefaultBilling = $isDefaultBilling;
        return $this;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function setValidationStatus(string $validationStatus): static
    {
        $this->validationStatus = $validationStatus;
        return $this;
    }

    public function setValidationResponse(array $validationResponse): static
    {
        $this->validationResponse = $validationResponse;
        return $this;
    }

    public function setValidatedAt(?string $validatedAt): static
    {
        $this->validatedAt = $validatedAt;
        return $this;
    }

    public function setAddressType(string $addressType): static
    {
        $this->addressType = $addressType;
        return $this;
    }

    public function setIsLoggedIn(bool $isLoggedIn): static
    {
        $this->isLoggedIn = $isLoggedIn;
        return $this;
    }

    // ─── toFormValues ─────────────────────────────────────────

    public function toFormValues(): array
    {
        return array_merge(parent::toFormValues(), [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'company' => $this->company,
            'phone' => $this->phone,
            'email' => $this->email,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country_id' => $this->countryId,
            'is_default_shipping' => $this->isDefaultShipping,
            'is_default_billing' => $this->isDefaultBilling,
            'is_verified' => $this->isVerified,
            'validation_status' => $this->validationStatus,
            'validation_response' => $this->validationResponse,
            'validated_at' => $this->validatedAt,
            'address_type' => $this->addressType,
            'is_logged_in' => $this->isLoggedIn,
            'label' => ucfirst($this->addressType) . ' Address',
        ]);
    }

    /**
     * Convert to array for API/JSON responses.
     */
    public function toArray(): array
    {
        return $this->toFormValues();
    }

    // ─── Factory Methods ──────────────────────────────────────

    /**
     * Create DTO from Address entity.
     */
    public static function fromEntity(
        Address $address,
        string $cancelRoute,
        string $deleteRoute,
        bool $isVisible = false,
        string $addressType = 'shipping',
        bool $isLoggedIn = true,
    ): self {
        return new self(
            cancelRoute: $cancelRoute,
            deleteRoute: $deleteRoute,
            id: $address->getId(),
            isVisible: $isVisible,
            firstName: $address->getFirstName(),
            lastName: $address->getLastName(),
            company: $address->getCompany(),
            phone: $address->getPhone(),
            email: $address->getEmail(),
            address1: $address->getAddress1(),
            address2: $address->getAddress2(),
            city: $address->getCity(),
            state: $address->getState(),
            postalCode: $address->getPostalCode(),
            countryId: $address->getCountry()?->getId(),
            isDefaultShipping: $address->isDefaultShipping(),
            isDefaultBilling: $address->isDefaultBilling(),
            isVerified: $address->isVerified(),
            validationStatus: $address->getValidationStatus()->value,
            validationResponse: $address->getValidationResponse(),
            validatedAt: $address->getValidatedAt()?->format('Y-m-d H:i:s'),
            addressType: $addressType,
            isLoggedIn: $isLoggedIn,
            sortOrder: 0,
            isActive: $address->isActive(),
        );
    }

    /**
     * Create DTO for new address (guest or logged-in).
     */
    public static function forNewAddress(
        string $cancelRoute,
        string $deleteRoute,
        string $addressType = 'shipping',
        bool $isLoggedIn = false,
        bool $isVisible = false,
    ): self {
        return new self(
            cancelRoute: $cancelRoute,
            deleteRoute: $deleteRoute,
            isVisible: $isVisible,
            addressType: $addressType,
            isLoggedIn: $isLoggedIn,
            isDefaultShipping: $addressType === 'shipping',
            isDefaultBilling: $addressType === 'billing',
        );
    }

    /**
     * Create DTO for guest address with pre-filled data.
     */
    public static function fromGuestData(
        array $data,
        string $cancelRoute,
        string $deleteRoute,
        string $addressType = 'shipping',
        bool $isVisible = false,
    ): self {
        return new self(
            cancelRoute: $cancelRoute,
            deleteRoute: $deleteRoute,
            isVisible: $isVisible,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            company: $data['company'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address1: $data['address1'] ?? null,
            address2: $data['address2'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            countryId: $data['country_id'] ?? null,
            addressType: $addressType,
            isLoggedIn: false,
        );
    }
}