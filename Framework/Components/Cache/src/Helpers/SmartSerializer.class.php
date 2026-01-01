<?php

declare(strict_types=1);

final class SmartSerializer implements SmartSerializerInterface
{
    private const TYPE_MARKER = '__type__';
    private const TYPE_SERIALIZED = 'serialized';
    private const TYPE_JSON = 'json';
    private const TYPE_DATETIME = 'datetime';

    private bool $useCompression;
    private bool $useIgbinary;
    private bool $igbinaryAvailable;

    public function __construct(
        bool $useCompression = false,
        bool $useIgbinary = false,
    ) {
        $this->useCompression = $useCompression;
        $this->useIgbinary = $useIgbinary;
        $this->igbinaryAvailable = function_exists('igbinary_serialize') && function_exists('igbinary_unserialize');

        // Auto-disable if not available
        if ($this->useIgbinary && !$this->igbinaryAvailable) {
            $this->useIgbinary = false;
            trigger_error('igbinary extension not available, falling back to PHP serialization', E_USER_WARNING);
        }

        if ($this->useCompression && !function_exists('gzcompress')) {
            $this->useCompression = false;
            trigger_error('zlib extension not available, compression disabled', E_USER_WARNING);
        }
    }

    public function serialize(mixed $value): string
    {
        // Handle DateTime objects (always use JSON for DateTime)
        if ($value instanceof DateTimeInterface) {
            return json_encode([
                self::TYPE_MARKER => self::TYPE_DATETIME,
                'date' => $value->format('Y-m-d H:i:s.u'),
                'timezone' => $value->getTimezone()->getName(),
            ], JSON_THROW_ON_ERROR);
        }

        // Try JSON serialization for simple data types
        if ($this->shouldUseJson($value)) {
            try {
                return json_encode([
                    self::TYPE_MARKER => self::TYPE_JSON,
                    'data' => $value,
                ], JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // Fall through to other serialization methods
            }
        }

        // Use igbinary if available and enabled
        if ($this->useIgbinary && $this->igbinaryAvailable && $this->shouldUseIgbinary($value)) {
            try {
                // @phpstan-ignore-next-line - PHPStan/Intelephense doesn't know about igbinary
                return igbinary_serialize([
                    self::TYPE_MARKER => self::TYPE_SERIALIZED,
                    'data' => $value,
                ]);
            } catch (Throwable) {
                // Fall through to regular serialization
            }
        }

        // Default PHP serialization with type marker
        return serialize([
            self::TYPE_MARKER => self::TYPE_SERIALIZED,
            'data' => $value,
        ]);
    }

    public function unserialize(string $data): mixed
    {
        // Try JSON first (for DateTime and JSON-serialized data)
        if (str_starts_with($data, '{') || str_starts_with($data, '[')) {
            try {
                $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

                if (is_array($decoded) && isset($decoded[self::TYPE_MARKER])) {
                    return $this->decodeTypedData($decoded);
                }

                // If it's JSON but not our typed format, return as-is
                return $decoded;
            } catch (JsonException) {
                // Not valid JSON, continue to other methods
            }
        }

        // Try PHP unserialize
        try {
            $unserialized = unserialize($data);

            if (is_array($unserialized) && isset($unserialized[self::TYPE_MARKER])) {
                return $this->decodeTypedData($unserialized);
            }

            return $unserialized;
        } catch (Throwable) {
            // Might be igbinary
            if ($this->useIgbinary && $this->igbinaryAvailable) {
                try {
                    // @phpstan-ignore-next-line - PHPStan/Intelephense doesn't know about igbinary
                    $unserialized = igbinary_unserialize($data);

                    if (is_array($unserialized) && isset($unserialized[self::TYPE_MARKER])) {
                        return $this->decodeTypedData($unserialized);
                    }

                    return $unserialized;
                } catch (Throwable) {
                    // Not igbinary either
                }
            }

            // Return as-is (could be a plain string)
            return $data;
        }
    }

    public function compress(string $data): string
    {
        if (!$this->useCompression) {
            return $data;
        }

        $compressed = gzcompress($data);
        if ($compressed === false) {
            throw new SerializationException('Failed to compress data');
        }

        return $compressed;
    }

    public function decompress(string $data): string
    {
        if (!$this->useCompression) {
            return $data;
        }

        $decompressed = gzuncompress($data);
        if ($decompressed === false) {
            throw new SerializationException('Failed to decompress data');
        }

        return $decompressed;
    }

    public function supportsCompression(): bool
    {
        return $this->useCompression && function_exists('gzcompress');
    }

    public function supportsIgbinary(): bool
    {
        return $this->useIgbinary && $this->igbinaryAvailable;
    }

    private function decodeTypedData(array $data): mixed
    {
        return match ($data[self::TYPE_MARKER]) {
            self::TYPE_SERIALIZED => $data['data'] ?? null,
            self::TYPE_JSON => $data['data'] ?? null,
            self::TYPE_DATETIME => new DateTime(
                $data['date'],
                new DateTimeZone($data['timezone']),
            ),
            default => $data['data'] ?? null,
        };
    }

    private function shouldUseJson(mixed $value): bool
    {
        // JSON is good for simple, serializable data

        // Simple scalars
        if (is_scalar($value) || $value === null) {
            return true;
        }

        // Simple arrays (no objects, resources, or closures)
        if (is_array($value)) {
            foreach ($value as $item) {
                if (is_object($item) || is_resource($item) || $item instanceof Closure) {
                    return false;
                }
                // Check nested arrays
                if (is_array($item) && !$this->shouldUseJson($item)) {
                    return false;
                }
            }
            return true;
        }

        // JsonSerializable objects
        if ($value instanceof JsonSerializable) {
            return true;
        }

        return false;
    }

    private function shouldUseIgbinary(mixed $value): bool
    {
        // Use igbinary for complex data that can't use JSON

        // Complex arrays
        if (is_array($value) && count($value) > 10) {
            return true;
        }

        // Objects (except DateTime which uses JSON)
        if (is_object($value) && !$value instanceof DateTimeInterface) {
            return true;
        }

        // Resources or other non-JSON-serializable types
        if (is_resource($value) || $value instanceof Closure) {
            return true;
        }

        return false;
    }
}