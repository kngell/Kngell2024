<?php

declare(strict_types=1);
/**
 * SQL STATEMENT TYPES - The complete classification of SQL command types
 * Based on SQL standard categories with no overlaps or ambiguities.
 */
enum SqlStatementType: string
{
    private const COMMAND_FLOW = [
        'select' => ['select' => true, 'from' => true,  'where' => false, 'having' => false, 'groupBy' => false, 'orderBy' => false, 'limit' => false, 'offset' => false,
        ],
        'insert' => [
            'insert' => true, 'into' => false, 'fields' => true, 'values' => true,
        ],
        'update' => ['update' => true, 'set' => true, 'where' => true],
        'updateCte' => ['update', 'fields', 'join', 'values', 'where'],
        'delete' => ['delete' => true, 'from' => true, 'where' => false],
        'create' => [],
        'raw' => ['raw' => true],
        'show' => ['show'],
        'withCte' => ['with', 'fields', 'values', 'where'],
    ];

    public function getRequiredClauses(): array
    {
        return match($this) {
            self::SELECT => ['select', 'from'],
            self::INSERT => ['insert', 'values'],
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
            self::UPDATE => ['where'],
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
                SqlClauseCategory::WITH,
                SqlClauseCategory::SELECT,
                SqlClauseCategory::FROM,    // Includes JOINs
                SqlClauseCategory::WHERE,
                SqlClauseCategory::GROUP_BY,
                SqlClauseCategory::HAVING,
                SqlClauseCategory::ORDER_BY,
                SqlClauseCategory::LIMIT,
                SqlClauseCategory::OFFSET,
            ],
            self::INSERT => [
                SqlClauseCategory::INTO,
                SqlClauseCategory::VALUES,
            ],
            self::UPDATE => [
                SqlClauseCategory::SET,
                SqlClauseCategory::WHERE,
                // Note: UPDATE can also have JOINs in some databases
            ],
            self::DELETE => [
                SqlClauseCategory::FROM,
                SqlClauseCategory::WHERE,
            ],
            default => []
        };
    }

    public function getAllowedCategories(): array
    {
        return match($this) {
            self::SELECT => [
                SqlClauseCategory::WITH,
                SqlClauseCategory::SELECT, SqlClauseCategory::FROM,
                SqlClauseCategory::WHERE, SqlClauseCategory::GROUP_BY, SqlClauseCategory::HAVING,
                SqlClauseCategory::ORDER_BY, SqlClauseCategory::LIMIT, SqlClauseCategory::OFFSET,
            ],
            self::INSERT => [SqlClauseCategory::INTO, SqlClauseCategory::VALUES],
            self::UPDATE => [
                SqlClauseCategory::SET,
                SqlClauseCategory::WHERE,
                SqlClauseCategory::FROM,
            ],
            self::DELETE => [
                SqlClauseCategory::FROM,
                SqlClauseCategory::WHERE,
                SqlClauseCategory::FROM,
            ],
            default => []
        };
    }

    public function isMethodAllowed(string $method): bool
    {
        $category = SqlClauseCategory::getCategoryForMethod($method);
        return $category && in_array($category, $this->getAllowedCategories());
    }

    public function getBuildOrder(): array
    {
        // For backward compatibility, return category values
        return array_map(
            fn (SqlClauseCategory $category) => $category->value,
            $this->getCategoryBuildOrder(),
        );
    }

    public function getFlow(): array
    {
        return self::COMMAND_FLOW[$this->value] ?? [];
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