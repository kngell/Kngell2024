<?php

declare(strict_types=1);

trait EntityTimestampableTrait
{
    protected const string DATE_FORMAT = 'Y-m-d H:i:s';

    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * Touch updated_at with current time.
     */
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
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
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

    /**
     * Convert string to DateTimeImmutable with proper error handling.
     */
    private function convertToDateTimeImmutable(string $dateString): DateTimeImmutable
    {
        if ($dateString === '') {
            throw new InvalidArgumentException('Date string cannot be empty');
        }

        try {
            // First try the entity's date format
            $dateTime = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $dateString);
            if ($dateTime !== false) {
                $errors = DateTimeImmutable::getLastErrors();
                if ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0)) {
                    return $dateTime;
                }
            }

            // Fallback to natural parsing
            return new DateTimeImmutable($dateString);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid date format: "%s". Expected format: "%s"', $dateString, self::DATE_FORMAT),
                0,
                $e,
            );
        }
    }
}