<?php

declare(strict_types=1);

class User extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

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
    private bool $isActive = true;
    private ?int $loginAttempts;
    private ?array $preferences = null;

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
     * @return User
     */
    public function setUserId(int $userId): User
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
     * @return User
     */
    public function setFirstName(?string $firstName): User
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
     * @return User
     */
    public function setLastName(?string $lastName): User
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
     * @return User
     */
    public function setUserName(?string $userName): User
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
     * @return User
     */
    public function setDisplayName(?string $displayName): User
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
     * @return User
     */
    public function setEmail(?string $email): User
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
     * @return User
     */
    public function setPassword(?string $password): User
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
     * @return User
     */
    public function setTokenExpiry(?string $tokenExpiry): User
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
     * @return User
     */
    public function setPasswordResetHash(?string $passwordResetHash): User
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
     * @return User
     */
    public function setPasswordResetExpiry(?string $passwordResetExpiry): User
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
     * @return User
     */
    public function setActive(?bool $active): User
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
     * @return User
     */
    public function setActivationHash(?string $activationHash): User
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
     * @return User
     */
    public function setGender(?string $gender): User
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
     * @return User
     */
    public function setGroupId(?int $groupId): User
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
     * @return User
     */
    public function setMedia(?string $media): User
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
     * @return User
     */
    public function setPhone(?string $phone): User
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
     * @return User
     */
    public function setDeleted(?bool $deleted): User
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
     * @return User
     */
    public function setAcl(?string $acl): User
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    /**
     * @param null|bool $verified
     *
     * @return User
     */
    public function setVerified(?bool $verified): User
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
     * @return User
     */
    public function setLoginAttempts(?int $loginAttempts): User
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
     * @return User
     */
    public function setPreferences(?array $preferences): User
    {
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return User
     */
    public function setIsActive(bool $isActive): User
    {
        $this->isActive = $isActive;

        return $this;
    }
}