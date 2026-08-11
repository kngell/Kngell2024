<?php

declare(strict_types=1);

class SqlExpressionParser
{
    /** @var ExpressionToken[] */
    private array $tokens = [];

    private int $parenDepth = 0;

    public function __construct(
        private readonly string $rawExpression,
    ) {
        $this->tokenize($rawExpression);
    }

    public function parseAndBuild(string $fallbackLogicalKey, bool $forceAutoAlias, array $tableAliasMap): string
    {
        $alias = '';
        $expressionTokens = $this->tokens;
        $count = count($this->tokens);
        $aliasIdx = -1;
        $asIdx = -1;

        for ($i = $count - 1; $i >= 0; $i--) {
            $t = $this->tokens[$i];
            if (trim($t->value) === '') {
                continue;
            }
            if ($aliasIdx === -1 && $t->parenDepth === 0 && $t->type === TokenType::IDENTIFIER) {
                $aliasIdx = $i;
                continue;
            }
            if ($aliasIdx !== -1 && $asIdx === -1 && $t->parenDepth === 0 && $t->type === TokenType::KEYWORD) {
                $upperWord = strtoupper(trim($t->value));
                if ($upperWord === 'AS') {
                    $asIdx = $i;
                    break;
                }
            }
            break;
        }

        if ($asIdx !== -1 && $aliasIdx !== -1) {
            $alias = trim($this->tokens[$aliasIdx]->value);
            $expressionTokens = array_slice($this->tokens, 0, $asIdx);
        }

        $reconstructedBody = '';
        $tokenCount = count($expressionTokens);
        $isAfterAsKeyword = false;

        $reservedFunctions = ['CAST', 'COUNT', 'CONCAT', 'LPAD', 'SUM', 'AVG', 'MIN', 'MAX', 'COALESCE', 'IFNULL', 'NOW'];
        $flowControlKeywords = ['CASE', 'WHEN', 'THEN', 'ELSE', 'END', 'AND', 'OR', 'NOT', 'IN', 'IS', 'NULL', 'LIKE', 'BETWEEN'];

        for ($i = 0; $i < $tokenCount; $i++) {
            $token = $expressionTokens[$i];
            $trimmedValue = trim($token->value);

            if ($trimmedValue === '') {
                $reconstructedBody .= $token->value;
                continue;
            }

            if ($trimmedValue === '.') {
                $nextRealIdx = $i + 1;
                while ($nextRealIdx < $tokenCount && trim($expressionTokens[$nextRealIdx]->value) === '') {
                    $nextRealIdx++;
                }
                if ($nextRealIdx < $tokenCount && is_numeric(trim($expressionTokens[$nextRealIdx]->value))) {
                    continue;
                }
            }

            if ($token->type === TokenType::KEYWORD) {
                $upperWord = strtoupper($trimmedValue);
                if ($upperWord === 'AS') {
                    $isAfterAsKeyword = true;
                    $reconstructedBody .= $token->value;
                    continue;
                }
            }

            if ($token->type === TokenType::PARAMETER || $token->type === TokenType::OPERATOR || $token->type === TokenType::PUNCTUATION || $token->type === TokenType::LITERAL) {
                $reconstructedBody .= $token->value;
                continue;
            }

            $isQualified = false;
            if ($i < $tokenCount - 2) {
                $nextIdx = $i + 1;
                while ($nextIdx < $tokenCount && trim($expressionTokens[$nextIdx]->value) === '') {
                    $nextIdx++;
                }
                if ($nextIdx < $tokenCount && $expressionTokens[$nextIdx]->value === '.') {
                    $isQualified = true;
                }
            }

            $isPropertyOfAlias = false;
            if ($i > 0) {
                $prevIdx = $i - 1;
                while ($prevIdx >= 0 && trim($expressionTokens[$prevIdx]->value) === '') {
                    $prevIdx--;
                }
                if ($prevIdx >= 0 && $expressionTokens[$prevIdx]->value === '.') {
                    $isPropertyOfAlias = true;
                }
            }

            if ($token->type === TokenType::IDENTIFIER && !$isAfterAsKeyword) {
                $datatypeKeywords = ['CHAR', 'BINARY', 'SIGNED', 'UNSIGNED', 'VARCHAR', 'INTEGER', 'DIGIT', 'NUMERIC'];
                $upperValue = strtoupper($trimmedValue);

                if (!in_array($upperValue, $datatypeKeywords, true) && !in_array($upperValue, $flowControlKeywords, true) && SqlKeyword::tryFrom($upperValue) === null) {
                    if ($isQualified) {
                        $dotIdx = $i + 1;
                        while ($dotIdx < $tokenCount && trim($expressionTokens[$dotIdx]->value) === '') {
                            $dotIdx++;
                        }
                        $numIdx = $dotIdx + 1;
                        while ($numIdx < $tokenCount && trim($expressionTokens[$numIdx]->value) === '') {
                            $numIdx++;
                        }
                        if ($numIdx < $tokenCount && is_numeric(trim($expressionTokens[$numIdx]->value))) {
                            continue;
                        }
                    }

                    if (str_contains($trimmedValue, '.')) {
                        $parts = explode('.', $trimmedValue);
                        $actualColumnName = array_pop($parts);
                        $fullLogicalPrefix = implode('.', $parts);
                        $upperColumnName = strtoupper($actualColumnName);

                        if (in_array($upperColumnName, $reservedFunctions, true) || SqlKeyword::tryFrom($upperColumnName) !== null) {
                            $reconstructedBody .= $actualColumnName;
                            continue;
                        }

                        if (is_numeric($actualColumnName)) {
                            $reconstructedBody .= $trimmedValue;
                            continue;
                        }

                        if (isset($tableAliasMap[$fullLogicalPrefix])) {
                            $reconstructedBody .= $tableAliasMap[$fullLogicalPrefix] . '.' . $actualColumnName;
                            if ($actualColumnName === '*') {
                                $forceAutoAlias = false;
                            }
                            continue;
                        } else {
                            $reconstructedBody .= $trimmedValue;
                            if ($actualColumnName === '*') {
                                $forceAutoAlias = false;
                            }
                            continue;
                        }
                    }

                    if (in_array($upperValue, $reservedFunctions, true)) {
                        $reconstructedBody .= $token->value;
                        continue;
                    }

                    if (!$isQualified && !$isPropertyOfAlias) {
                        $nextReal = $i + 1;
                        while ($nextReal < $tokenCount && trim($expressionTokens[$nextReal]->value) === '') {
                            $nextReal++;
                        }

                        $isFunctionalOrOperator = false;
                        if ($nextReal < $tokenCount) {
                            $nextVal = trim($expressionTokens[$nextReal]->value);
                            if ($nextVal === '(' || $nextVal === '+' || $nextVal === '-' || $nextVal === '*' || $nextVal === '/') {
                                $isFunctionalOrOperator = true;
                            }
                        }

                        if ($isFunctionalOrOperator) {
                            $reconstructedBody .= $token->value;
                            continue;
                        }

                        $activeContextAlias = $tableAliasMap[$fallbackLogicalKey] ?? $fallbackLogicalKey;
                        $reconstructedBody .= $activeContextAlias . '.' . $trimmedValue;
                        continue;
                    }
                }
            }

            if ($isAfterAsKeyword && $token->type === TokenType::IDENTIFIER) {
                $isAfterAsKeyword = false;
            }

            $reconstructedBody .= $token->value;
        }

        if (empty($alias) && $forceAutoAlias) {
            $currentAlias = $tableAliasMap[$fallbackLogicalKey] ?? $fallbackLogicalKey;
            $cleanTerm = preg_replace('/[^a-z0-9_]/i', '_', str_replace($currentAlias . '.', '', trim($reconstructedBody)));
            $alias = $currentAlias . '_' . trim(preg_replace('/_+/', '_', $cleanTerm), '_');
        }

        return trim($reconstructedBody) . (!empty($alias) ? ' AS ' . $alias : '');
    }

    private function tokenize(string $rawExpression): void
    {
        $operatorPatternChunk = '';
        if (class_exists('SqlOperator')) {
            $operators = [];
            foreach (SqlOperator::cases() as $case) {
                $operators[] = preg_quote($case->toSql(), '/');
            }
            $operators = array_unique($operators);
            usort($operators, fn ($a, $b) => strlen($b) <=> strlen($a));
            $operatorPatternChunk = implode('|', $operators) . '|';
        }

        $multiWordKeywords = ['NULLS FIRST', 'NULLS LAST', 'CURRENT ROW', 'NOT MATERIALIZED', 'IN BOOLEAN MODE', 'IN NATURAL LANGUAGE', 'WITH EXPANSION'];
        usort($multiWordKeywords, fn ($a, $b) => strlen($b) <=> strlen($a));
        $multiWordPattern = implode('|', array_map(fn ($item) => preg_quote($item, '/'), $multiWordKeywords));

        $pattern = '/(\s+|"[^"]*"|\'[^\']*\'|:[a-z0-9_]+\b|' . $multiWordPattern . '|\b[a-z_][a-z0-9_]*(?:\.[a-z_][a-z0-9_]*)*(?:\.\*)?|' . $operatorPatternChunk . '<=>|>=|<=|==|!=|<>|>|<|=|\+|-|\*|\/|[\(\),]|\.)/i';
        $parts = preg_split($pattern, $rawExpression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            $trimmed = trim($part);

            if ($part !== '' && $trimmed === '') {
                $this->tokens[] = new ExpressionToken(TokenType::PUNCTUATION, $part, $this->parenDepth);
                continue;
            }
            if (str_starts_with($trimmed, ':')) {
                $this->tokens[] = new ExpressionToken(TokenType::PARAMETER, $part, $this->parenDepth);
                continue;
            }
            if ($trimmed === '(') {
                $this->parenDepth++;
                $this->tokens[] = new ExpressionToken(TokenType::PUNCTUATION, $part, $this->parenDepth);
                continue;
            }
            if ($trimmed === ')') {
                $this->tokens[] = new ExpressionToken(TokenType::PUNCTUATION, $part, $this->parenDepth);
                $this->parenDepth--;
                continue;
            }
            if (in_array($trimmed, [',', '.'], true)) {
                $this->tokens[] = new ExpressionToken(TokenType::PUNCTUATION, $part, $this->parenDepth);
                continue;
            }
            if (in_array($trimmed, ['=', '<=>', '>=', '<=', '==', '!=', '<>', '>', '<', '+', '-', '*', '/'], true)) {
                $this->tokens[] = new ExpressionToken(TokenType::OPERATOR, $part, $this->parenDepth);
                continue;
            }
            if ((str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"')) ||
                (str_starts_with($trimmed, "'") && str_ends_with($trimmed, "'"))) {
                $this->tokens[] = new ExpressionToken(TokenType::LITERAL, $part, $this->parenDepth);
                continue;
            }
            if (is_numeric($trimmed)) {
                $this->tokens[] = new ExpressionToken(TokenType::LITERAL, $part, $this->parenDepth);
                continue;
            }

            $matchedOperator = method_exists($this, 'lookupOperator') ? $this->lookupOperator($trimmed) : null;
            if ($matchedOperator !== null) {
                if ($matchedOperator->isLogical()) {
                    $this->tokens[] = new ExpressionToken(TokenType::KEYWORD, $part, $this->parenDepth);
                } else {
                    $this->tokens[] = new ExpressionToken(TokenType::OPERATOR, $part, $this->parenDepth);
                }
                continue;
            }

            if (class_exists('SqlFunction') && SqlFunction::tryFrom(strtoupper($trimmed)) !== null) {
                $this->tokens[] = new ExpressionToken(TokenType::FUNCTION, $part, $this->parenDepth);
                continue;
            }

            $upperTrimmed = strtoupper($trimmed);
            if ($upperTrimmed === 'AS' || SqlKeyword::tryFrom($upperTrimmed) !== null) {
                $this->tokens[] = new ExpressionToken(TokenType::KEYWORD, $part, $this->parenDepth);
                continue;
            }

            if (str_ends_with($trimmed, '.*')) {
                $this->tokens[] = new ExpressionToken(TokenType::IDENTIFIER, $part, $this->parenDepth);
                continue;
            }

            $this->tokens[] = new ExpressionToken(TokenType::IDENTIFIER, $part, $this->parenDepth);
        }
    }
}