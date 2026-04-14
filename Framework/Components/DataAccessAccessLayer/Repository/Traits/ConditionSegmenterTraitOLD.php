<?php

declare(strict_types=1);

trait ConditionSegmenterTraitOLD
{
    protected const string DIVIDER = '---';

    protected function applyMixedConditions(SqlSelectQueryBuilderInterface $qb, array $conditions): void
    {
        if (empty($conditions)) {
            return;
        }

        $chunks = $this->splitByNewBlock($conditions);
        // dump($chunks);
        foreach ($chunks as $index => $chunk) {
            $segment = $this->parseAndCleanChunk($chunk, $index);

            if (empty($segment['data'])) {
                continue; // Skip empty segments
            }

            $method = ($segment['operator'] === 'OR') ? 'orWhere' : 'where';
            $qb->$method(...$segment['data']);
        }
    }

    private function splitByNewBlock(array $conditions): array
    {
        $chunks = [];
        $currentChunk = [];

        foreach ($conditions as $key => $value) {
            if ($this->isSeparator($value)) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                    $currentChunk = [];
                }
                continue;
            }

            if (is_string($key)) {
                $currentChunk[$key] = $value;
            } else {
                $currentChunk[] = $value;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    private function parseAndCleanChunk(array $chunk, ?int $index = null): array
    {
        $operator = 'AND';
        $cleanData = [];

        foreach ($chunk as $key => $value) {
            if (is_numeric($key) && $this->isLogical($value)) {
                $operator = strtoupper(trim($value));
                continue;
            }

            if (is_string($key)) {
                $cleanData[] = $key;
                $cleanData[] = $value;
            } else {
                $cleanData[] = $value;
            }
        }

        // Optional validation
        if (empty($cleanData)) {
            throw new InvalidArgumentException(
                sprintf('Chunk %d resulted in empty conditions', $index ?? 0),
            );
        }

        return [
            'operator' => $operator,
            'data' => $cleanData,
        ];
    }

    private function isLogical(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $op = SqlOperator::tryFrom(strtoupper(trim($value)));
        return $op && ($op === SqlOperator::AND || $op === SqlOperator::OR);
    }

    private function isSeparator(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        // Support '---', '===', or a dedicated constant
        return in_array($value, [
            '---', '===', 'new_block', 'BLOCK_BREAK', static::DIVIDER,
        ], true);
    }
}