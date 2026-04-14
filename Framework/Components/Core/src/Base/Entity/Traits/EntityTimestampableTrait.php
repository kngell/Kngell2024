<?php

declare(strict_types=1);

trait EntityTimestampableTrait
{
    protected const string DATE_FORMAT = 'Y-m-d H:i:s';

    #[DisplayFormat(
        dateStyle: 'Y-m-d',
        suffix: ' UTC',
    )]
    private DateTimeImmutable $createdAt;

    #[DisplayFormat(
        dateStyle: 'Y-m-d',
        suffix: ' UTC',
    )]
    private ?DateTimeImmutable $updatedAt = null;

    public function touchUpdatedAt(): self
    {
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    /**
     * Touch created_at with current time (if not already set).
     */
    public function touchCreatedAt(): self
    {
        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }

        return $this;
    }

    /**
     * Get raw database string values.
     */
    public function getCreatedAtRaw(): ?string
    {
        return $this->createdAt?->format(self::DATE_FORMAT);
    }

    /**
     * Get raw database string values.
     */
    public function getUpdatedAtRaw(): ?string
    {
        return $this->updatedAt?->format(self::DATE_FORMAT);
    }

    /**
     * Check if created_at is set.
     */
    public function hasCreatedAt(): bool
    {
        return $this->createdAt !== null;
    }

    /**
     * Check if updated_at is set.
     */
    public function hasUpdatedAt(): bool
    {
        return $this->updatedAt !== null;
    }

    /**
     * Get created_at in specified format.
     */
    public function getCreatedAtFormatted(?string $format = null): ?string
    {
        return $this->createdAt?->format($format ?? self::DATE_FORMAT);
    }

    /**
     * Get updated_at in specified format.
     */
    public function getUpdatedAtFormatted(?string $format = null): ?string
    {
        return $this->updatedAt?->format($format ?? self::DATE_FORMAT);
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param null|DateTimeImmutable $updatedAt
     *
     * @return TimestampableInterface
     */
    public function setUpdatedAt(?DateTimeImmutable $updatedAt): TimestampableInterface
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return ?DateTimeImmutable
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return isset($this->createdAt) ? $this->createdAt : null;
    }

    /**
     * @param DateTimeImmutable $createdAt
     *
     * @return TimestampableInterface
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): TimestampableInterface
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function touchTimestamps(): void
    {
        if (!$this instanceof TimestampableInterface) {
            return;
        }

        $now = new DateTimeImmutable();

        if (method_exists($this, 'setUpdatedAt')) {
            $this->setUpdatedAt($now);
        }
        if (method_exists($this, 'setCreatedAt')) {
            if (method_exists($this, 'getCreatedAt')) {
                $existingCreatedAt = $this->getCreatedAt();
                if ($existingCreatedAt === null) {
                    $this->setCreatedAt($now);
                }
                // If created_at already exists, DON'T update it
            } else {
                // No getter - assume it's not set
                $this->setCreatedAt($now);
            }
        }
    }
}