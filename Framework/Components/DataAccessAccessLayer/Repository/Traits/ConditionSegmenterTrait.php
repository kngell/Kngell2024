<?php

declare(strict_types=1);

trait ConditionSegmenterTrait
{
    protected function applyMixedConditions(SqlSelectQueryBuilderInterface $qb, array $conditions): void
    {
        $i = 0;
        $keys = array_keys($conditions);
        $count = count($conditions);

        while ($i < $count) {
            $value = $conditions[$keys[$i]];

            // 1. Scopes
            if ($value === '(') {
                $method = $this->determineMethod($conditions, $keys, $i - 1);
                $i++;
                $sub = $this->extractSubScope($conditions, $i, '(', ')');
                $qb->$method(fn ($subQb) => $this->applyMixedConditions($subQb, $sub));
                $i += count($sub) + 1;
                continue;
            }

            if ($this->isLogicalSeparator($value)) {
                $i++;
                continue;
            }

            // 3. Blob Processing
            $method = $this->determineMethod($conditions, $keys, $i - 1);
            $rawBlob = $this->grabUntilDelimiter($conditions, $keys, $i);

            if (!empty($rawBlob)) {
                $qb->$method($rawBlob);
                $i += count($rawBlob);
            } else {
                $i++;
            }
        }
    }

    private function grabUntilDelimiter(array $conditions, array $keys, int $index): array
    {
        $unit = [];
        $count = count($conditions);
        for ($j = $index; $j < $count; $j++) {
            $current = $conditions[$keys[$j]];

            // Stop ONLY if we hit a structural delimiter
            if ($this->isLogicalSeparator($current) || $current === '(' || $current === ')') {
                break;
            }

            if (is_string($keys[$j])) {
                $unit[$keys[$j]] = $current;
            } else {
                $unit[] = $current;
            }
        }
        return $unit;
    }

    private function determineMethod(array $conditions, array $keys, int $lookback): string
    {
        if ($lookback >= 0) {
            $prev = strtoupper(trim((string) ($conditions[$keys[$lookback]] ?? '')));
            if ($prev === 'OR') {
                return 'orWhere';
            }
            if ($prev === 'AND') {
                return 'andWhere';
            }
        }
        return 'where';
    }

    private function isLogicalSeparator(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $v = strtoupper(trim($value));
        return in_array($v, ['OR', 'XOR', 'AND'], true);
    }

    private function extractSubScope(array $conditions, int $startIndex, string $open, string $close): array
    {
        $subConditions = [];
        $depth = 1;
        $count = count($conditions);
        $keys = array_keys($conditions);

        for ($j = $startIndex; $j < $count; $j++) {
            $val = $conditions[$keys[$j]];
            if ($val === $open) {
                $depth++;
            }
            if ($val === $close) {
                $depth--;
            }
            if ($depth === 0) {
                return $subConditions;
            }
            $subConditions[$keys[$j]] = $val;
        }
        return $subConditions;
    }
}
