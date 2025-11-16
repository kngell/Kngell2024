<?php

declare(strict_types=1);

class LoginAttempts extends Entity
{
    #[EntityFieldId]
    private int $laId;

    private ?int $userId;
    private ?string $timestamp;
    private ?string $ipAddress;

    /**
     * @return int
     */
    public function getLaId(): int
    {
        return $this->laId;
    }

    /**
     * @param int $laId
     * @return LoginAttempts
     */
    public function setLaId(int $laId): self
    {
        $this->laId = $laId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param null|int $userId
     * @return LoginAttempts
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    /**
     * @param null|string $timestamp
     * @return LoginAttempts
     */
    public function setTimestamp(?string $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param null|string $ipAddress
     * @return LoginAttempts
     */
    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }
}
