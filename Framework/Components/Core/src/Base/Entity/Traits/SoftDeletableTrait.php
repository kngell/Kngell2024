<?php

declare(strict_types=1);

trait SoftDeletableTrait
{
    protected const string SOFT_DELETE_DATE_FORMAT = 'Y-m-d H:i:s';

    private ?DateTimeImmutable $deletedAt = null;

    /**
     * Check if entity is soft deleted.
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * Check if entity is not soft deleted.
     */
    public function isNotDeleted(): bool
    {
        return $this->deletedAt === null;
    }

    /**
     * Soft delete the entity by setting deleted_at to current time.
     */
    public function softDelete(?DateTimeImmutable $at = null): self
    {
        $this->deletedAt = $at ?? new DateTimeImmutable();
        return $this;
    }

    /**
     * Restore the entity by setting deleted_at to null.
     */
    public function restore(): self
    {
        $this->deletedAt = null;
        return $this;
    }

    /**
     * Get raw database string value for deleted_at.
     */
    public function getDeletedAtRaw(): ?string
    {
        return $this->deletedAt?->format(static::SOFT_DELETE_DATE_FORMAT);
    }

    /**
     * Get deleted_at in specified format.
     */
    public function getDeletedAtFormatted(?string $format = null): ?string
    {
        return $this->deletedAt?->format($format ?? static::SOFT_DELETE_DATE_FORMAT);
    }

    public function getDateFormat(): string
    {
        return self::SOFT_DELETE_DATE_FORMAT;
    }

    /**
     * Get the time since deletion in seconds.
     */
    public function getSecondsSinceDeletion(): ?int
    {
        if ($this->deletedAt === null) {
            return null;
        }

        $now = new DateTimeImmutable();
        return $now->getTimestamp() - $this->deletedAt->getTimestamp();
    }

    /**
     * Check if entity was deleted within a certain time period.
     */
    public function wasDeletedWithin(string $interval): bool
    {
        if ($this->deletedAt === null) {
            return false;
        }

        $cutoff = (new DateTimeImmutable())->sub(DateInterval::createFromDateString($interval));
        return $this->deletedAt > $cutoff;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param null|DateTimeImmutable $deletedAt
     *
     * @return SoftDeletableInterface
     */
    public function setDeletedAt(?DateTimeImmutable $deletedAt): SoftDeletableInterface
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function touchDeleted(?DateTimeImmutable $at = null): void
    {
        $this->softDelete($at);
    }

    /**
     * Convert string to DateTimeImmutable with proper error handling.
     */
    private function parseDateString(string $dateString): DateTimeImmutable
    {
        if ($dateString === '') {
            throw new InvalidArgumentException('Date string cannot be empty');
        }

        try {
            // First try the entity's date format
            $dateTime = DateTimeImmutable::createFromFormat(static::SOFT_DELETE_DATE_FORMAT, $dateString);
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
                sprintf('Invalid date format: "%s". Expected format: "%s"', $dateString, static::SOFT_DELETE_DATE_FORMAT),
                0,
                $e,
            );
        }
    }
}