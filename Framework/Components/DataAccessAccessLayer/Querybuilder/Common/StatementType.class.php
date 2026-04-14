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
            self::CTE => SqlStatement::SELECT
        };
    }

    public function getBuildOrder(): array
    {
        return match($this) {
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

    public function validate(array $userFlow): void
    {
        $this->ensureMinimalFlow($userFlow);
        $this->validateAllowedMethods($userFlow);
        $this->validateCategoryOrder($userFlow);
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
        }
    }

    case SIMPLE_UPDATE = 'simple_update';
    case SIMPLE_INSERT = 'simple_insert';
    case BULK_UPDATE_MARIADB = 'bulk_update_mariadb';
    case BULK_UPDATE = 'bulk_update';
    case BULK_UPDATE_CASE = 'bulk_update_case';
    case BULK_INSERT = 'bulk_insert';
    case UPSERT = 'upsert';
    case SIMPLE_DELETE = 'simple_delete';
    case CTE = 'cte';
}