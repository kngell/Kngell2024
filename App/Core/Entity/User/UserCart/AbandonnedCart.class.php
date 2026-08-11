<?php

declare(strict_types=1);

class AbandonnedCart extends Entity
{
    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'cart_id', type: FieldType::INT)]
    private int $id;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    private int $userId;

    private DateTimeImmutable $abandonedAt;
    private ?DateTimeImmutable $reminderSentAt = null;
    private ?DateTimeImmutable $recoveredAt = null;

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
     * @return AbandonnedCart
     */
    public function setId(int $id): AbandonnedCart
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
     * @return AbandonnedCart
     */
    public function setUserId(int $userId): AbandonnedCart
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getAbandonedAt(): DateTimeImmutable
    {
        return $this->abandonedAt;
    }

    /**
     * @param DateTimeImmutable $abandonedAt
     *
     * @return AbandonnedCart
     */
    public function setAbandonedAt(DateTimeImmutable $abandonedAt): AbandonnedCart
    {
        $this->abandonedAt = $abandonedAt;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getReminderSentAt(): ?DateTimeImmutable
    {
        return $this->reminderSentAt;
    }

    /**
     * @param null|DateTimeImmutable $reminderSentAt
     *
     * @return AbandonnedCart
     */
    public function setReminderSentAt(?DateTimeImmutable $reminderSentAt): AbandonnedCart
    {
        $this->reminderSentAt = $reminderSentAt;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getRecoveredAt(): ?DateTimeImmutable
    {
        return $this->recoveredAt;
    }

    /**
     * @param null|DateTimeImmutable $recoveredAt
     *
     * @return AbandonnedCart
     */
    public function setRecoveredAt(?DateTimeImmutable $recoveredAt): AbandonnedCart
    {
        $this->recoveredAt = $recoveredAt;

        return $this;
    }
}