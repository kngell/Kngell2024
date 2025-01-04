<?php

declare(strict_types=1);

class User extends Entity
{
    #[EntityFieldId]
    private int $userId;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $userName;
    private ?string $email;
    private ?string $password;
    private ?string $tokenExpire;
    private ?string $passwordResetHash;
    private ?string $paswwordResetExpiry;
    private ?bool $active;
    private ?string $activationHash;
    private ?string $gender;
    private ?int $groupId;
    private ?string $createdAt;
    private ?string $media;
    private ?string $phone;
    private ?bool $deleted;
    private ?string $acl;
    private ?bool $verified;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return User
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     * @return User
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     * @return User
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * @param string|null $userName
     * @return User
     */
    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return User
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return User
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTokenExpire(): ?string
    {
        return $this->tokenExpire;
    }

    /**
     * @param string|null $tokenExpire
     * @return User
     */
    public function setTokenExpire(?string $tokenExpire): self
    {
        $this->tokenExpire = $tokenExpire;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     * @return User
     */
    public function setGender(?string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    /**
     * @param int|null $groupId
     * @return User
     */
    public function setGroupId(?int $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * @param string|null $createdAt
     * @return User
     */
    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMedia(): ?string
    {
        return $this->media;
    }

    /**
     * @param string|null $media
     * @return User
     */
    public function setMedia(?string $media): self
    {
        $this->media = $media;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return User
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * @param bool|null $deleted
     * @return User
     */
    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcl(): ?string
    {
        return $this->acl;
    }

    /**
     * @param string|null $acl
     * @return User
     */
    public function setAcl(?string $acl): self
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool|null
    {
        return $this->verified;
    }

    /**
     * @param bool|null $verified
     * @return User
     */
    public function setVerified(?bool $verified): self
    {
        $this->verified = $verified;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool|null
    {
        return $this->active;
    }

    /**
     * @param null|bool $active
     * @return User
     */
    public function setActive(?bool $active): self
    {
        $this->active = $active;
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
     * @return User
     */
    public function setPasswordResetHash(?string $passwordResetHash): self
    {
        $this->passwordResetHash = $passwordResetHash;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPaswwordResetExpiry(): ?string
    {
        return $this->paswwordResetExpiry;
    }

    /**
     * @param null|string $paswwordResetExpiry
     * @return User
     */
    public function setPaswwordResetExpiry(?string $paswwordResetExpiry): self
    {
        $this->paswwordResetExpiry = $paswwordResetExpiry;
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
     * @return User
     */
    public function setActivationHash(?string $activationHash): self
    {
        $this->activationHash = $activationHash;
        return $this;
    }
}
