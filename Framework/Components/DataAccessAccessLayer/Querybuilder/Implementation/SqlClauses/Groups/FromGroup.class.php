<?php

declare(strict_types=1);

class FromGroup extends SqlComponent implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::FROM;

    public function __construct(private null|STatementType $context = null)
    {
        parent::__construct(null);
    }

    public function build(): string
    {
        if ($this->children->isEmpty()) {
            return '';
        }

        $parts = [];

        foreach ($this->children as $child) {
            if ($this->context && $this->context === StatementType::BULK_UPDATE && $child instanceof FromClause) {
                continue;
            }
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

        return $this->query = implode(' ', $parts);
    }

    public function getSqlClause(): ?SqlClause
    {
        if ($this->context && $this->context === StatementType::BULK_UPDATE) {
            return null;
        }
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
