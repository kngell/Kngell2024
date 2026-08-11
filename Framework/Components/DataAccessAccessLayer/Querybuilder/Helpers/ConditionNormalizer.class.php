<?php

declare(strict_types=1);

class ConditionNormalizer
{
    public function normalize(array $conditions): array
    {
        if (empty($conditions)) {
            return [];
        }

        return $this->process($conditions);
    }

    private function process(array $conditions): array
    {
        $result = [];
        $keys = array_keys($conditions);
        $count = count($keys);
        $i = 0;

        while ($i < $count) {
            $key = $keys[$i];
            $value = $conditions[$key];

            // 1. Associative Pair: ['field' => 'value']
            if (is_string($key)) {
                $result[] = $key;
                if (is_string($value) && ($op = $this->findOperator($value))) {
                    $result[] = $op->value;
                } else {
                    $result[] = $value;
                }
                $i++;
                continue;
            }

            // 2. Logical Junction: ['OR']
            if (is_string($value) && ($op = $this->findOperator($value)) && $op->isLogical()) {
                $result[] = $op->value;
                $i++;
                continue;
            }

            // 3. Raw SQL String: "field IS NOT NULL"
            if (is_string($value) && ($parsed = $this->parseRawCondition($value))) {
                $result = array_merge($result, $parsed);
                $i++;
                continue;
            }

            // 4. Sequential: ['field', '<=', 10]
            if ($i < $count) {
                $this->handleSequential($result, $conditions, $keys, $i);
                $i = $this->getNextIndex($conditions, $keys, $i);
                continue;
            }

            $i++;
        }

        return $result;
    }

    private function findOperator(string $value): ?SqlOperator
    {
        $upperValue = strtoupper(trim($value));

        // Direct match first
        $operator = SqlOperator::tryFrom($upperValue);
        if ($operator) {
            return $operator;
        }

        // Try to find by matching value (for multi-word operators)
        foreach (SqlOperator::cases() as $operator) {
            if (strtoupper($operator->value) === $upperValue) {
                return $operator;
            }
        }

        return null;
    }

    /**
     * Parse a raw SQL condition like "icon IS NOT NULL" into [field, operator, value?].
     */
    private function parseRawCondition(string $condition): ?array
    {
        // Split into parts
        $parts = explode(' ', $condition);
        if (count($parts) < 2) {
            return null;
        }

        $field = array_shift($parts);

        // Try to find the longest matching operator
        $matchedOperator = null;
        $operatorLength = 0;

        // Try to match multi-word operators (e.g., "IS NOT NULL" is 3 words)
        for ($i = count($parts); $i >= 1; $i--) {
            $testOperator = implode(' ', array_slice($parts, 0, $i));
            $operator = $this->findOperator($testOperator);
            if ($operator) {
                $matchedOperator = $operator;
                $operatorLength = $i;
                break;
            }
        }

        if (!$matchedOperator) {
            return null;
        }

        // Remove the matched operator parts
        array_splice($parts, 0, $operatorLength);

        $result = [$field, $matchedOperator->value];

        // For binary operators, add the remaining parts as value
        if ($matchedOperator->isBinary() && !empty($parts)) {
            $value = implode(' ', $parts);
            // Try to convert numeric values
            if (is_numeric($value)) {
                $value = $value + 0; // Convert to int/float
            }
            // Remove quotes if present
            if (preg_match('/^[\'"](.+)[\'"]$/', $value, $matches)) {
                $value = $matches[1];
            }
            $result[] = $value;
        }

        return $result;
    }

    private function handleSequential(array &$result, array $conditions, array $keys, int $i): void
    {
        $field = $conditions[$keys[$i]];
        $next = $conditions[$keys[$i + 1] ?? null] ?? null;
        $afterNext = $conditions[$keys[$i + 2] ?? null] ?? null;

        $result[] = $field;

        // Check if next token is an operator
        $op = is_string($next) ? $this->findOperator($next) : null;

        if ($op) {
            $result[] = $op->value;
            // Only take the third element if it's a binary operator (like =)
            // and the element actually exists.
            if ($op->isBinary() && $afterNext !== null) {
                $result[] = $this->resolveValue($afterNext);
            }
        } elseif ($next !== null) {
            // No operator found? Assume equality: [field, value]
            $result[] = $this->resolveValue($next);
        }
    }

    private function resolveValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $cleanValue = rtrim(strtoupper($value), '()');
            $func = SqlFunction::tryFrom($cleanValue);

            if ($func) {
                return $func->value . '()';
            }

            // Try to convert numeric strings
            if (is_numeric($value)) {
                return $value + 0;
            }
        }
        return $value;
    }

    private function getNextIndex(array $conditions, array $keys, int $i): int
    {
        $next = $conditions[$keys[$i + 1] ?? null] ?? null;
        $op = is_string($next) ? $this->findOperator($next) : null;

        if ($op) {
            return $op->isUnary() ? $i + 2 : $i + 3;
        }
        return $i + 2;
    }
}