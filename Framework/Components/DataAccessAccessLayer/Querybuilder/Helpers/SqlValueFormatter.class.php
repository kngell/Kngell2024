<?php

declare(strict_types=1);

final class SqlValueFormatter
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function formatForSql(mixed $normalizedValue): string
    {
        if ($normalizedValue === null) {
            return 'NULL';
        }

        // Hex literals (from BinaryType/HexLiteralType)
        if (is_string($normalizedValue) && $this->isHexLiteral($normalizedValue)) {
            return $normalizedValue;
        }

        // Numbers (including booleans as 1/0 from StandardType)
        if (is_int($normalizedValue) || is_float($normalizedValue)) {
            return (string) $normalizedValue;
        }

        // Strings need quoting
        if (is_string($normalizedValue)) {
            return $this->em->quote($normalizedValue);
        }

        // Everything else
        return $this->em->quote((string) $normalizedValue);
    }

    public function formatArrayForSql(array $normalizedValues): array
    {
        $formatted = [];
        foreach ($normalizedValues as $key => $value) {
            $formatted[$key] = $this->formatForSql($value);
        }
        return $formatted;
    }

    private function isHexLiteral(mixed $value): bool
    {
        return is_string($value) &&
               strlen($value) >= 3 &&
               str_starts_with($value, '0x') &&
               ctype_xdigit(substr($value, 2));
    }
}
