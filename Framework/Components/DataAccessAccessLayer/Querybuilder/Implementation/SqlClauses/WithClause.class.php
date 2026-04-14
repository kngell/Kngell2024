<?php

declare(strict_types=1);

class WithClause extends SqlComponent implements CteClauseComponentInterface
{
    private bool $isRecursive = false;

    public function __construct(bool $isRecursive)
    {
        parent::__construct();
        $this->isRecursive = $isRecursive;
    }

    public function build(): string
    {
        if ($this->children->isEmpty()) {
            return '';
        }

        $cteParts = [];
        foreach ($this->children as $child) {
            $this->prepareChild($child);
            $cteParts[] = $child->build();
            $this->mergeChildState($child);
        }

        $this->state->joinContext = null;
        $this->query = implode(', ', $cteParts);
        return $this->query;
    }

    public function getSqlClause(): SqlCteClause
    {
        return $this->isRecursive
            ? SqlCteClause::WITH_RECURSIVE
            : SqlCteClause::WITH;
    }

    public function isRecursive(): bool
    {
        return $this->isRecursive;
    }

    public function add(SqlComponent $component): void
    {
        if (!$component instanceof CteQuery) {
            throw new InvalidArgumentException(
                'WithClause can only contain Cte components',
            );
        }

        $this->children->add($component);
        $component->setParent($this);
    }

    public function isComposite(): bool
    {
        return true;
    }
}