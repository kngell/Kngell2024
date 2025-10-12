<?php

declare(strict_types=1);

trait EntityTimestampableTrait
{
    private ?string $created_at = null;
    private ?string $updated_at = null;

    /**
     * Get created_at as DateTimeImmutable instance.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        if ($this->created_at === null || $this->created_at === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($this->created_at);
        } catch (Throwable) {
            // Gracefully handle invalid or unexpected date format
            return null;
        }
    }

    /**
     * Set created_at (accepts string, DateTimeImmutable, or null).
     */
    public function setCreatedAt(DateTimeImmutable|string|null $createdAt): self
    {
        if ($createdAt instanceof DateTimeImmutable) {
            $this->created_at = $createdAt->format('Y-m-d H:i:s');
        } else {
            $this->created_at = $createdAt ?: null;
        }

        return $this;
    }

    /**
     * Get updated_at as DateTimeImmutable instance.
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        if ($this->updated_at === null || $this->updated_at === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($this->updated_at);
        } catch (Throwable) {
            // Gracefully handle invalid or unexpected date format
            return null;
        }
    }

    /**
     * Set updated_at (accepts string, DateTimeImmutable, or null).
     */
    public function setUpdatedAt(DateTimeImmutable|string|null $updatedAt): self
    {
        if ($updatedAt instanceof DateTimeImmutable) {
            $this->updated_at = $updatedAt->format('Y-m-d H:i:s');
        } else {
            $this->updated_at = $updatedAt ?: null;
        }

        return $this;
    }

    /**
     * Touch updated_at with current time.
     */
    public function touchUpdatedAt(): self
    {
        $this->setUpdatedAt(new DateTimeImmutable());
        return $this;
    }

    /**
     * Touch created_at with current time (if not already set).
     */
    public function touchCreatedAt(): self
    {
        if ($this->created_at === null) {
            $this->setCreatedAt(new DateTimeImmutable());
        }

        return $this;
    }

    /**
     * Get raw database string values (useful for persistence layer).
     */
    public function getCreatedAtRaw(): ?string
    {
        return $this->created_at;
    }

    public function getUpdatedAtRaw(): ?string
    {
        return $this->updated_at;
    }
}