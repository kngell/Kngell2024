<?php

declare(strict_types=1);

trait EntityTimestampableTrait
{
    protected const TIMESTAMP_TIMEZONE = 'UTC';
    /**
     * Use untyped constants unless your project is guaranteed to run on PHP 8.3+.
     */
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    #[DisplayFormat(
        dateStyle: 'Y-m-d',
        suffix: ' UTC',
    )]
    private ?DateTimeImmutable $createdAt = null;

    #[DisplayFormat(
        dateStyle: 'Y-m-d',
        suffix: ' UTC',
    )]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * Set updated_at to the current UTC time.
     */
    public function touchUpdatedAt(?DateTimeImmutable $at = null): static
    {
        $this->updatedAt = self::normalizeTimestamp($at ?? self::now());

        return $this;
    }

    /**
     * Set created_at to the current UTC time only if it is not already set.
     */
    public function touchCreatedAt(?DateTimeImmutable $at = null): static
    {
        if ($this->createdAt === null) {
            $this->createdAt = self::normalizeTimestamp($at ?? self::now());
        }

        return $this;
    }

    /**
     * Set created_at if missing and always update updated_at.
     */
    public function touchTimestamps(?DateTimeImmutable $at = null): TimestampableInterface
    {
        $timestamp = self::normalizeTimestamp($at ?? self::now());

        if ($this->createdAt === null) {
            $this->createdAt = $timestamp;
        }

        $this->updatedAt = $timestamp;

        return $this;
    }

    /**
     * Get created_at as raw database string.
     */
    public function getCreatedAtRaw(): ?string
    {
        return $this->createdAt?->format(self::DATE_FORMAT);
    }

    /**
     * Get updated_at as raw database string.
     */
    public function getUpdatedAtRaw(): ?string
    {
        return $this->updatedAt?->format(self::DATE_FORMAT);
    }

    /**
     * Check whether created_at is set.
     */
    public function hasCreatedAt(): bool
    {
        return $this->createdAt !== null;
    }

    /**
     * Check whether updated_at is set.
     */
    public function hasUpdatedAt(): bool
    {
        return $this->updatedAt !== null;
    }

    /**
     * Get created_at in the specified format.
     */
    public function getCreatedAtFormatted(?string $format = null): ?string
    {
        return $this->createdAt?->format($format ?? self::DATE_FORMAT);
    }

    /**
     * Get updated_at in the specified format.
     */
    public function getUpdatedAtFormatted(?string $format = null): ?string
    {
        return $this->updatedAt?->format($format ?? self::DATE_FORMAT);
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): TimestampableInterface
    {
        $this->createdAt = self::normalizeTimestamp($createdAt);

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): TimestampableInterface
    {
        $this->updatedAt = $updatedAt !== null
            ? self::normalizeTimestamp($updatedAt)
            : null;

        return $this;
    }

    private static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            'now',
            new DateTimeZone(self::TIMESTAMP_TIMEZONE),
        );
    }

    private static function normalizeTimestamp(DateTimeImmutable $timestamp): DateTimeImmutable
    {
        return $timestamp->setTimezone(
            new DateTimeZone(self::TIMESTAMP_TIMEZONE),
        );
    }
}