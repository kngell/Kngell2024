<?php

declare(strict_types=1);
/**
 * SQL STATEMENT TYPES - The complete classification of SQL command types
 * Based on SQL standard categories with no overlaps or ambiguities.
 */
enum SqlStatement: string
{
    public function getRequiredClauses(): array
    {
        return match($this) {
            self::SELECT => ['select', 'from'],
            self::INSERT => ['into', 'values'],
            self::UPDATE => ['set', 'where'],
            self::DELETE => ['delete', 'from'],
            default => []
        };
    }

    public function getOptionalClauses(): array
    {
        return match($this) {
            self::SELECT => ['where', 'having', 'groupBy', 'orderBy', 'limit', 'offset'],
            self::INSERT => ['into', 'fields'],
            self::UPDATE => ['from', 'where'],
            self::DELETE => ['where'],
            default => []
        };
    }

    public function validateFlow(array $userFlow): bool
    {
        $required = $this->getRequiredClauses();

        foreach ($required as $clause) {
            if (!isset($userFlow[$clause])) {
                throw new QueryFlowException("Missing required clause: {$clause} for {$this->value} command");
            }
        }

        return true;
    }

    public function getCategoryBuildOrder(): array
    {
        return match($this) {
            self::SELECT => [
                SqlMethodCategory::WITH,
                SqlMethodCategory::SELECT,
                SqlMethodCategory::FROM,
                SqlMethodCategory::WHERE,
                SqlMethodCategory::GROUP_BY,
                SqlMethodCategory::HAVING,
                SqlMethodCategory::ORDER_BY,
                SqlMethodCategory::LIMIT,
                SqlMethodCategory::OFFSET,
            ],
            default => []
        };
    }

    public function getAllowedCategories(): array
    {
        return match($this) {
            self::SELECT => [
                SqlMethodCategory::WITH,
                SqlMethodCategory::SELECT, SqlMethodCategory::FROM,
                SqlMethodCategory::WHERE, SqlMethodCategory::GROUP_BY, SqlMethodCategory::HAVING,
                SqlMethodCategory::ORDER_BY, SqlMethodCategory::LIMIT, SqlMethodCategory::OFFSET,
                SqlMethodCategory::POST_SELECT,
            ],
            self::INSERT => [
                SqlMethodCategory::INSERT,
                SqlMethodCategory::INTO,
                SqlMethodCategory::VALUES,
            ],
            self::UPDATE => [
                SqlMethodCategory::UPDATE,
                SqlMethodCategory::SET,
                SqlMethodCategory::WHERE,
                SqlMethodCategory::FROM,
            ],
            self::DELETE => [
                SqlMethodCategory::DELETE,
                SqlMethodCategory::FROM,
                SqlMethodCategory::WHERE,
            ],
            default => []
        };
    }

    public function isMethodAllowed(string $method): bool
    {
        $category = SqlMethodCategory::getCategoryForMethod($method);
        return $category && in_array($category, $this->getAllowedCategories());
    }

    public function getBuildOrder(): array
    {
        return array_map(
            fn (SqlMethodCategory $category) => $category,
            $this->getCategoryBuildOrder(),
        );
    }

    public static function getCommand(string $type): ?self
    {
        $type = strtoupper($type);
        foreach (self::cases() as $case) {
            if ($case->name === $type) {
                return $case;
            }
        }
        return null;
    }
    // ============================================
    // DATA QUERY LANGUAGE (DQL) - Read operations
    // ============================================
    case SELECT = 'SELECT';      // Data retrieval only

    // ============================================
    // DATA MANIPULATION LANGUAGE (DML) - Write operations on data
    // ============================================
    case INSERT = 'INSERT';      // Add new rows
    case UPDATE = 'UPDATE';      // Modify existing rows
    case DELETE = 'DELETE';      // Remove rows
    case MERGE = 'MERGE';        // Upsert operations (INSERT + UPDATE)

    // =============================================
    // DATA DEFINITION LANGUAGE (DDL) - Schema operations
    // =============================================
    case CREATE = 'CREATE';      // Create database objects
    case DROP = 'DROP';          // Remove database objects
    case ALTER = 'ALTER';        // Modify database objects
    case TRUNCATE = 'TRUNCATE';  // Remove all data from table
    case RENAME = 'RENAME';      // Rename database objects

    // =============================================
    // DATA CONTROL LANGUAGE (DCL) - Security operations
    // =============================================
    case GRANT = 'GRANT';        // Assign privileges
    case REVOKE = 'REVOKE';      // Remove privileges

    // =============================================
    // TRANSACTION CONTROL LANGUAGE (TCL) - Transaction operations
    // =============================================
    case COMMIT = 'COMMIT';      // Save transaction
    case ROLLBACK = 'ROLLBACK';  // Undo transaction
    case SAVEPOINT = 'SAVEPOINT'; // Create transaction savepoint
}