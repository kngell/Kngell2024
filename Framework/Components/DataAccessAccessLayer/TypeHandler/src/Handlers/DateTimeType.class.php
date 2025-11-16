<?php

declare(strict_types=1);

class DateTimeType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        // Only support DateTime objects or valid date strings
        return $value instanceof DateTimeInterface ||
               (is_string($value) && $this->isValidDateString($value));
    }

    public function normalizeForDatabase(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value; // Already a string
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_string($value) && $this->isValidDateString($value)) {
            try {
                return new DateTimeImmutable($value);
            } catch (Exception $e) {
                throw new InvalidArgumentException(
                    "Invalid datetime string: {$value}",
                    $e->getCode(),
                    $e,
                );
            }
        }

        throw new InvalidArgumentException(
            'Cannot denormalize value to DateTime. Expected DateTimeInterface or valid date string, got: ' . gettype($value),
        );
    }

    private function isValidDateString(string $value): bool
    {
        // Basic date format validation
        if (trim($value) === '') {
            return false;
        }

        // Common date patterns
        $datePatterns = [
            '/^\d{4}-\d{2}-\d{2}$/', // YYYY-MM-DD
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', // YYYY-MM-DD HH:MM:SS
            '/^\d{2}\/\d{2}\/\d{4}$/', // MM/DD/YYYY
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return strtotime($value) !== false;
            }
        }

        return false;
    }
}