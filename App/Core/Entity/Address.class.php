<?php

declare(strict_types=1);

class Address extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;
    protected const array RELATIONSHIPS = [
        'country' => [
            'class' => Country::class,
            'type' => 'one-to-one',
            'collection' => false,
        ],
    ];

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private int $userId;
    private string $firstName;
    private string $lastName;
    private ?string $company = null;
    private ?string $phone = null;
    private ?string $email = null;
    private string $address1;
    private ?string $address2 = null;
    private AddressType $addressType = AddressType::BOTH;
    private string $city;
    private ?string $state = null;
    private string $postalCode;
    private ?Country $country = null;
    private bool $isDefaultShipping = false;
    private bool $isDefaultBilling = false;
    private bool $isVerified = false;
    private AddressValidationStatus $validationStatus = AddressValidationStatus::PENDING;
    private array $validationResponse = [];
    private ?DateTimeImmutable $validatedAt = null;
    private bool $isActive = true;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Address
     */
    public function setId(int $id): Address
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return Address
     */
    public function setUserId(int $userId): Address
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return Address
     */
    public function setFirstName(string $firstName): Address
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return Address
     */
    public function setLastName(string $lastName): Address
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param null|string $company
     *
     * @return Address
     */
    public function setCompany(?string $company): Address
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     *
     * @return Address
     */
    public function setPhone(?string $phone): Address
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     *
     * @return Address
     */
    public function setEmail(?string $email): Address
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress1(): string
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     *
     * @return Address
     */
    public function setAddress1(string $address1): Address
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * @param null|string $address2
     *
     * @return Address
     */
    public function setAddress2(?string $address2): Address
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity(string $city): Address
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param null|string $state
     *
     * @return Address
     */
    public function setState(?string $state): Address
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return Address
     */
    public function setPostalCode(string $postalCode): Address
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefaultShipping(): bool
    {
        return $this->isDefaultShipping;
    }

    /**
     * @param bool $isDefaultShipping
     *
     * @return Address
     */
    public function setIsDefaultShipping(bool $isDefaultShipping): Address
    {
        $this->isDefaultShipping = $isDefaultShipping;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefaultBilling(): bool
    {
        return $this->isDefaultBilling;
    }

    /**
     * @param bool $isDefaultBilling
     *
     * @return Address
     */
    public function setIsDefaultBilling(bool $isDefaultBilling): Address
    {
        $this->isDefaultBilling = $isDefaultBilling;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     *
     * @return Address
     */
    public function setIsVerified(bool $isVerified): Address
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return AddressValidationStatus
     */
    public function getValidationStatus(): AddressValidationStatus
    {
        return $this->validationStatus;
    }

    /**
     * @param AddressValidationStatus $validationStatus
     *
     * @return Address
     */
    public function setValidationStatus(AddressValidationStatus $validationStatus): Address
    {
        $this->validationStatus = $validationStatus;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidationResponse(): array
    {
        return $this->validationResponse;
    }

    /**
     * @param array $validationResponse
     *
     * @return Address
     */
    public function setValidationResponse(array $validationResponse): Address
    {
        $this->validationResponse = $validationResponse;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getValidatedAt(): ?DateTimeImmutable
    {
        return $this->validatedAt;
    }

    /**
     * @param null|DateTimeImmutable $validatedAt
     *
     * @return Address
     */
    public function setValidatedAt(?DateTimeImmutable $validatedAt): Address
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return Address
     */
    public function setIsActive(bool $isActive): Address
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return null|Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param null|Country $country
     *
     * @return Address
     */
    public function setCountry(?Country $country): Address
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return AddressType
     */
    public function getAddressType(): AddressType
    {
        return $this->addressType;
    }

    /**
     * @param AddressType $addressType
     *
     * @return Address
     */
    public function setAddressType(AddressType $addressType): Address
    {
        $this->addressType = $addressType;

        return $this;
    }
}