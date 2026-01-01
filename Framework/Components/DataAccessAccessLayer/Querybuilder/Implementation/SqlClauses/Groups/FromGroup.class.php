<?php

declare(strict_types=1);

/**
 * FromGroup - Simple composite that groups FROM and JOIN clauses together
 * Minimal refactoring required - just wraps existing components.
 */
class FromGroup extends SqlComponent implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::FROM;

    public function __construct()
    {
        parent::__construct(self::CLAUSE);
    }

    public function build(): string
    {
        if ($this->children->isEmpty()) {
            return '';
        }

        $parts = [];

        foreach ($this->children as $child) {
            if ($child instanceof JoinClause) {
                $parts[] = $child->getLink();
            }
            $this->prepareChild($child);

            $childSql = $child->build();
            if (!empty($childSql)) {
                $parts[] = $childSql;
            }
            $this->mergeChildState($child);
        }

        return implode(' ', $parts);
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    /**
     * Add any FROM-related component (FromClause, JoinClause, etc.).
     */
    public function add(SqlComponent $component): void
    {
        $this->children->add($component);
        $component->setParent($this);
    }

    public function isComposite(): bool
    {
        return true;
    }
}
