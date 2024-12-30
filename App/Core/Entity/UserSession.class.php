<?php

declare(strict_types=1);

class UserSession extends Entity
{
    #[EntityFieldId]
    private int $usID;
    private string $tokenHash;
    private int $userId;
    private string $expiresAt;
    private string $userAgent;

    /**
     * @return int
     */
    public function getUsID(): int
    {
        return $this->usID;
    }

    /**
     * @param int $usID
     * @return UserSession
     */
    public function setUsID(int $usID): self
    {
        $this->usID = $usID;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    /**
     * @param string $tokenHash
     * @return UserSession
     */
    public function setTokenHash(string $tokenHash): self
    {
        $this->tokenHash = $tokenHash;
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
     * @return UserSession
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }

    /**
     * @param string $expiresAt
     * @return UserSession
     */
    public function setExpiresAt(string $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     * @return UserSession
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }
}