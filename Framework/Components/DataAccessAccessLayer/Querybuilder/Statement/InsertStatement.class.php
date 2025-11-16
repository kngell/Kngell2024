<?php

declare(strict_types=1);

class InsertStatement extends SqlQueryComponent
{
    private const SqlStatementType STATEMENT = SqlStatementType::INSERT;
    private const string MAIN_LINK = 'INSERT INTO';

    public function __construct(
        ?string $table,
        ?TablesAliasHelper $helper = null,
    ) {
        parent::__construct();
        $this->helper = $helper;
        $this->table = $table;
        $this->children = new Collection();
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }

        $table = $this->table;

        $columnStrings = [];
        foreach ($this->children as $child) {
            $this->prepareChild($child);
            $columnStrings[] = $child->build();
            $this->mergeChildState($child);
        }

        $this->query = implode(', ', array_filter($columnStrings));
        return $this->query;
    }

    public function isComposite(): bool
    {
        return true;
    }

    public function getStatementType(): SqlStatementType
    {
        return self::STATEMENT;
    }
}