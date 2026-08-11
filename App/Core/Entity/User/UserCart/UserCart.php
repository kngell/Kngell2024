<?php

declare(strict_types=1);

class UserCart extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'uc_id', type: FieldType::INT)]
    private int $id;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    private ?int $userId;

    private string $sessionId;
    private ?DateTimeImmutable $expiresAt = null;

    /**
     * @return null|int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param null|int $userId
     *
     * @return UserCart
     */
    public function setUserId(?int $userId): UserCart
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     *
     * @return UserCart
     */
    public function setSessionId(string $sessionId): UserCart
    {
        $this->sessionId = $sessionId;

        return $this;
    }

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
     * @return UserCart
     */
    public function setId(int $id): UserCart
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @param null|DateTimeImmutable $expiresAt
     *
     * @return UserCart
     */
    public function setExpiresAt(?DateTimeImmutable $expiresAt): UserCart
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}