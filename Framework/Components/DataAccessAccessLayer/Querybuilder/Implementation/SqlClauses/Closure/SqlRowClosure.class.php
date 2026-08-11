<?php

declare(strict_types=1);

class SqlRowClosure extends SqlComponent
{
    private Closure $closure;
    private QueryRulesInterface $rowValueRule;
    private null|string $columnList = null;

    public function __construct(
        Closure $closure,
        null|EntityManagerInterface $em = null,
        null|string $method = null,
        null|string $table = null,
        private null|BulkUpdateType $bulkUpdateType = null,
        private null|StatementType $statementContext = null,
    ) {
        parent::__construct(em: $em);
        $this->closure = $closure;
        $this->method = $method;
        $this->table = $table;
    }

    public function build(): string
    {
        $query = ($this->closure)($this);

        if ($query instanceof SqlComponent) {
            $this->prepareChild($query);
            $this->query = $query->build();
            $this->mergeChildState($query);
            return '(' . $this->query . ') AS subquery_alias';
        }

        $this->initializeRowValueRule($query);
        $data = $this->getData($query);

        $rowValuesSql = $this->rowValueRule->getRule($data);

        if ($this->rowValueRule instanceof AbstractBulkRow && method_exists($this->rowValueRule, 'getColumnList')) {
            $this->columnList = $this->rowValueRule->getColumnList();
        }

        if (empty($rowValuesSql)) {
            return '';
        }
        $this->query = $rowValuesSql;

        return $this->query;
    }

    /**
     * @return null|string
     */
    public function getColumnList(): ?string
    {
        return $this->columnList;
    }

    #[Override]
    public function getContext(): ?StatementType
    {
        return $this->statementContext;
    }

    public function getBulkUpdateType(): ?BulkUpdateType
    {
        return $this->bulkUpdateType;
    }

    private function getData(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }
        if ($data instanceof CollectionInterface) {
            return $data->all();
        }
        throw new QueryFlowException('inValid data type for bulkUpdate');
    }

    private function initializeRowValueRule(CollectionInterface|array $query): void
    {
        if (!isset($this->rowValueRule)) {
            if ($this->joinContext !== null) {
                $this->state->joinContext = $this->joinContext;
            }
            $registry = new SqlFactoryRegistry(
                $this,
                $this->em,
                $this->state,
            );

            $this->rowValueRule = $registry->getRule($this->method, $query);
        }
    }
}