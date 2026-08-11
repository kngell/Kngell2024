<?php

declare(strict_types=1);

class UserAddress extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;
    protected const array RELATIONSHIPS = [
        'address' => [
            'class' => Address::class,
            'type' => 'one-to-many',
            'collection' => true,
        ],
    ];

    #[EntityFieldId(name: 'user_id', type: FieldType::INT)]
    private int $userId;

    private ?string $firstName;
    private ?string $lastName;
    private ?string $userName;
    private ?string $displayName;
    private ?string $email;
    private ?string $password;
    private ?string $tokenExpiry;
    private ?string $passwordResetHash;
    private ?string $passwordResetExpiry;
    private ?bool $active;
    private ?string $activationHash;
    private ?string $gender;
    private ?int $groupId;
    private ?string $media;
    private ?string $phone;
    private ?bool $deleted;
    private ?string $acl;
    private ?bool $verified;
    private ?int $loginAttempts;
    private ?array $preferences = null;

    /** @var Address[] */
    private array $address = [];

    private ?string $addressType = null;

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
     * @return UserAddress
     */
    public function setUserId(int $userId): UserAddress
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param null|string $firstName
     *
     * @return UserAddress
     */
    public function setFirstName(?string $firstName): UserAddress
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param null|string $lastName
     *
     * @return UserAddress
     */
    public function setLastName(?string $lastName): UserAddress
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * @param null|string $userName
     *
     * @return UserAddress
     */
    public function setUserName(?string $userName): UserAddress
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param null|string $displayName
     *
     * @return UserAddress
     */
    public function setDisplayName(?string $displayName): UserAddress
    {
        $this->displayName = $displayName;

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
     * @return UserAddress
     */
    public function setEmail(?string $email): UserAddress
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     *
     * @return UserAddress
     */
    public function setPassword(?string $password): UserAddress
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTokenExpiry(): ?string
    {
        return $this->tokenExpiry;
    }

    /**
     * @param null|string $tokenExpiry
     *
     * @return UserAddress
     */
    public function setTokenExpiry(?string $tokenExpiry): UserAddress
    {
        $this->tokenExpiry = $tokenExpiry;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPasswordResetHash(): ?string
    {
        return $this->passwordResetHash;
    }

    /**
     * @param null|string $passwordResetHash
     *
     * @return UserAddress
     */
    public function setPasswordResetHash(?string $passwordResetHash): UserAddress
    {
        $this->passwordResetHash = $passwordResetHash;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPasswordResetExpiry(): ?string
    {
        return $this->passwordResetExpiry;
    }

    /**
     * @param null|string $passwordResetExpiry
     *
     * @return UserAddress
     */
    public function setPasswordResetExpiry(?string $passwordResetExpiry): UserAddress
    {
        $this->passwordResetExpiry = $passwordResetExpiry;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param null|bool $active
     *
     * @return UserAddress
     */
    public function setActive(?bool $active): UserAddress
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getActivationHash(): ?string
    {
        return $this->activationHash;
    }

    /**
     * @param null|string $activationHash
     *
     * @return UserAddress
     */
    public function setActivationHash(?string $activationHash): UserAddress
    {
        $this->activationHash = $activationHash;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param null|string $gender
     *
     * @return UserAddress
     */
    public function setGender(?string $gender): UserAddress
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    /**
     * @param null|int $groupId
     *
     * @return UserAddress
     */
    public function setGroupId(?int $groupId): UserAddress
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMedia(): ?string
    {
        return $this->media;
    }

    /**
     * @param null|string $media
     *
     * @return UserAddress
     */
    public function setMedia(?string $media): UserAddress
    {
        $this->media = $media;

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
     * @return UserAddress
     */
    public function setPhone(?string $phone): UserAddress
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * @param null|bool $deleted
     *
     * @return UserAddress
     */
    public function setDeleted(?bool $deleted): UserAddress
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAcl(): ?string
    {
        return $this->acl;
    }

    /**
     * @param null|string $acl
     *
     * @return UserAddress
     */
    public function setAcl(?string $acl): UserAddress
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function getVerified(): ?bool
    {
        return $this->verified;
    }

    /**
     * @param null|bool $verified
     *
     * @return UserAddress
     */
    public function setVerified(?bool $verified): UserAddress
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getLoginAttempts(): ?int
    {
        return $this->loginAttempts;
    }

    /**
     * @param null|int $loginAttempts
     *
     * @return UserAddress
     */
    public function setLoginAttempts(?int $loginAttempts): UserAddress
    {
        $this->loginAttempts = $loginAttempts;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getPreferences(): ?array
    {
        return $this->preferences;
    }

    /**
     * @param null|array $preferences
     *
     * @return UserAddress
     */
    public function setPreferences(?array $preferences): UserAddress
    {
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAddressType(): ?string
    {
        return $this->addressType;
    }

    /**
     * @param null|string $addressType
     *
     * @return UserAddress
     */
    public function setAddressType(?string $addressType): UserAddress
    {
        $this->addressType = $addressType;

        return $this;
    }

    /**
     * @return array
     */
    public function getAddress(): array
    {
        return $this->address;
    }

    /**
     * @param array $address
     *
     * @return UserAddress
     */
    public function setAddress(array $address): UserAddress
    {
        $this->address = $address;

        return $this;
    }
}