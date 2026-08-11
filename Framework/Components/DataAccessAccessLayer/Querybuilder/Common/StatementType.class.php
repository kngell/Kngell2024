<?php

declare(strict_types=1);

enum StatementType: string
{
    public function toSqlStatement(): SqlStatement
    {
        return match ($this) {
            self::SIMPLE_UPDATE => SqlStatement::UPDATE,
            self::SIMPLE_INSERT => SqlStatement::INSERT,
            self::BULK_UPDATE_MARIADB => SqlStatement::UPDATE,
            self::BULK_UPDATE => SqlStatement::UPDATE,
            self::BULK_UPDATE_CASE => SqlStatement::UPDATE,
            self::BULK_INSERT => SqlStatement::INSERT,
            self::SIMPLE_DELETE => SqlStatement::DELETE,
            self::CTE, self::SIMPLE_SELECT => SqlStatement::SELECT
        };
    }

    public function getBuildOrder(): array
    {
        return match($this) {
            self::SIMPLE_SELECT => [
                SqlMethodCategory::WITH,
                SqlMethodCategory::SELECT,
                SqlMethodCategory::FROM,
                SqlMethodCategory::WHERE,
                SqlMethodCategory::GROUP_BY,
                SqlMethodCategory::HAVING,
                SqlMethodCategory::ORDER_BY,
                SqlMethodCategory::LIMIT,
                SqlMethodCategory::OFFSET,
                SqlMethodCategory::POST_SELECT,
            ],
            self::SIMPLE_INSERT => [
                SqlMethodCategory::INSERT,
                SqlMethodCategory::INTO,
                SqlMethodCategory::VALUES,
            ],
            self::SIMPLE_UPDATE => [
                SqlMethodCategory::UPDATE,
                SqlMethodCategory::SET,
                SqlMethodCategory::WHERE,
            ],
            self::BULK_UPDATE => [
                SqlMethodCategory::UPDATE,
                SqlMethodCategory::FROM,
                SqlMethodCategory::SET,
                SqlMethodCategory::WHERE,
            ],
            self::BULK_UPDATE_MARIADB => [
                SqlMethodCategory::UPDATE,
                SqlMethodCategory::FROM,
                SqlMethodCategory::SET,
                SqlMethodCategory::WHERE,
            ],
            self::SIMPLE_DELETE => [
                SqlMethodCategory::DELETE,
                SqlMethodCategory::FROM,
                SqlMethodCategory::WHERE,
            ],
            default => []
        };
    }

    public function validate(array $userFlow, array $map = []): void
    {
        $this->ensureMinimalFlow($userFlow);
        $this->validateAllowedMethods($userFlow);
        $this->validateCategoryOrder($userFlow);

        if ($this->supportsJoins()) {
            $this->validateJoinOnPairs($userFlow, $map);
        }
    }

    private function ensureMinimalFlow(array $userFlow): void
    {
        $required = match($this) {
            self::SIMPLE_UPDATE => ['update', 'set'],
            self::BULK_UPDATE => ['bulkUpdate', 'innerJoin', 'on', 'set'],
            self::BULK_UPDATE_MARIADB => ['bulkUpdate', 'innerJoin', 'on', 'set'],
            self::BULK_UPDATE_CASE => ['bulkUpdateCase', 'when', 'then', 'else', 'end', 'set'],
            self::BULK_INSERT => ['bulkInsert', 'into', 'values'],
            self::SIMPLE_DELETE => ['delete', 'from'],
            self::SIMPLE_INSERT => ['insert', 'into'],
            default => []
        };

        foreach ($required as $method) {
            if (!in_array($method, $userFlow)) {
                throw new QueryFlowException("Method '{$method}' is required for {$this->value} strategy.");
            }
        }
    }

    private function validateAllowedMethods(array $userFlow): void
    {
        $sqlStatement = $this->toSqlStatement();
        foreach ($userFlow as $method) {
            if (!$sqlStatement->isMethodAllowed($method)) {
                $allowedCategories = $sqlStatement->getAllowedCategories();
                $allowedMethods = [];
                foreach ($allowedCategories as $category) {
                    $allowedMethods = array_merge($allowedMethods, $category->getMethods());
                }

                throw new QueryFlowException(
                    "Method '{$method}' is not allowed for {$sqlStatement->value} statements. " .
                    'Allowed methods: ' . implode(', ', array_unique($allowedMethods)),
                );
            }
        }
    }

    private function validateCategoryOrder(array $userFlow): void
    {
        $buildOrder = $this->getBuildOrder();
        $lastIndex = -1;
        $validatedCategory = [];

        foreach ($userFlow as $method) {
            $category = SqlMethodCategory::getCategoryForMethod($method);
            if (!$category) {
                continue;
            }

            $currentIndex = array_search($category, $buildOrder);

            if ($currentIndex === false) {
                throw new QueryFlowException("Category '{$category->value}' is not supported by {$this->value}");
            }

            if ($currentIndex < $lastIndex) {
                throw new QueryFlowException("Method '{$method}' called out of order for {$this->value} strategy.");
            }
            $lastIndex = $currentIndex;
            $validatedCategory[] = $category;
        }
    }

    private function supportsJoins(): bool
    {
        return in_array($this, [
            self::SIMPLE_SELECT,
            self::SIMPLE_UPDATE,
            self::BULK_UPDATE,
            self::BULK_UPDATE_MARIADB,
        ]);
    }

    private function validateJoinOnPairs(array $userFlow, array $map): void
    {
        $hasJoin = $this->hasJoinMethods($userFlow);
        $hasOn = $this->hasOnMethods($userFlow);

        if ($hasJoin && !$hasOn) {
            throw new QueryFlowException(
                'JOIN clauses require corresponding ON conditions. ' .
                'Use ->on() after each ->join() method.',
            );
        }

        if ($hasOn && !$hasJoin) {
            throw new QueryFlowException(
                'ON conditions require corresponding JOIN clauses. ' .
                'Use ->join() before ->on() method.',
            );
        }

        // Validate JOIN/ON table pairs if we have both and the map is provided
        if ($hasJoin && $hasOn && !empty($map)) {
            $this->validateJoinTableMatching($map);
            $this->validateJoinOnOrder($userFlow);
        }
    }

    private function hasJoinMethods(array $userFlow): bool
    {
        foreach ($userFlow as $method) {
            if (SqlBuilderMethodRegistry::isJoinMethod($method)) {
                return true;
            }
        }
        return false;
    }

    private function hasOnMethods(array $userFlow): bool
    {
        foreach ($userFlow as $method) {
            if (SqlBuilderMethodRegistry::isOnMethod($method)) {
                return true;
            }
        }
        return false;
    }

    private function isJoinMethod(string $method): bool
    {
        return SqlBuilderMethodRegistry::isJoinMethod($method);
    }

    private function isOnMethod(string $method): bool
    {
        return SqlBuilderMethodRegistry::isOnMethod($method);
    }

    private function validateJoinTableMatching(array $map): void
    {
        $joinMap = $map['join'] ?? [];
        $onMap = $map['on'] ?? [];

        // Check each JOIN has a corresponding ON
        foreach ($joinMap as $joinKey => $joinConfig) {
            $tableName = $this->extractTableNameFromJoin($joinKey, $joinConfig);

            if (!isset($onMap[$tableName])) {
                $joinType = explode('|', $joinKey)[0] ?? 'join';
                throw new QueryFlowException(
                    "{$joinType} clause for table '{$tableName}' requires a corresponding ON condition. " .
                    "Use ->on() method after ->{$joinType}() method.",
                );
            }
        }

        // Check each ON has a corresponding JOIN
        foreach ($onMap as $tableName => $onCondition) {
            $hasMatchingJoin = false;
            foreach ($joinMap as $joinKey => $joinConfig) {
                $joinTableName = $this->extractTableNameFromJoin($joinKey, $joinConfig);
                if ($joinTableName === $tableName || str_contains($joinKey, $tableName)) {
                    $hasMatchingJoin = true;
                    break;
                }
            }

            if (!$hasMatchingJoin) {
                throw new QueryFlowException(
                    "ON condition for table '{$tableName}' has no corresponding JOIN clause. " .
                    'Use ->join() method before ->on() method.',
                );
            }
        }
    }

    private function extractTableNameFromJoin(string $joinKey, array $joinConfig): string
    {
        return is_string($joinConfig['table'])
            ? $joinConfig['table']
            : $joinKey;
    }

    private function validateJoinOnOrder(array $userFlow): void
    {
        $normalizedFlow = array_map(fn ($method) => $this->normalizeMethod($method), $userFlow);

        $lastJoinIndex = -1;
        $lastOnIndex = -1;

        foreach ($normalizedFlow as $index => $normalizedMethod) {
            if ($normalizedMethod === 'join') {
                $lastJoinIndex = $index;
            }
            if ($normalizedMethod === 'on') {
                $lastOnIndex = $index;
            }
        }

        // Last ON should come after last JOIN
        if ($lastJoinIndex !== -1 && $lastOnIndex !== -1 && $lastOnIndex < $lastJoinIndex) {
            throw new QueryFlowException(
                'ON clause cannot appear before JOIN clause. ' .
                'Correct order: ->join() then ->on().',
            );
        }
    }

    private function normalizeMethod(string $method): string
    {
        if ($this->isJoinMethod($method)) {
            return 'join';
        }

        if ($this->isOnMethod($method)) {
            return 'on';
        }

        return $method;
    }
    case SIMPLE_SELECT = 'simple_select';
    case SIMPLE_UPDATE = 'simple_update';
    case SIMPLE_INSERT = 'simple_insert';
    case BULK_UPDATE_MARIADB = 'bulk_update_mariadb';
    case BULK_UPDATE = 'bulk_update';
    case BULK_UPDATE_CASE = 'bulk_update_case';
    case BULK_INSERT = 'bulk_insert';
    case UPSERT = 'upsert';
    case SIMPLE_DELETE = 'simple_delete';
    case CTE = 'cte';
    case CASE_BLOCK = 'case_block';
}