<?php

declare(strict_types=1);

class CaseWhenThen extends SqlExpression
{
    private array $conditionComponents = [];
    private SqlExpression $result;

    public function __construct(
        ConditionGroupBuilder $builder,
        mixed $result,
        ?string $resultMethod = null,
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct($em);
        $this->method = $resultMethod;

        // Extract condition components from builder
        $groupedElements = $builder->getGroupedElements();
        foreach ($groupedElements->all() as $element) {
            $this->conditionComponents[] = $element;
        }

        $this->result = new SqlExpression($result, $resultMethod, $em);
    }

    #[Override]
    public function build(): string
    {
        $parts = ['WHEN'];

        $conditionSql = $this->buildConditions();
        $parts[] = '(' . $conditionSql . ')';

        $parts[] = 'THEN';

        // Build result
        $this->prepareChild($this->result);
        $parts[] = $this->result->build();
        $this->mergeChildState($this->result);

        return implode(' ', $parts);
    }

    private function buildConditions(): string
    {
        $sqlParts = [];
        $previousComponent = null;

        foreach ($this->conditionComponents as $component) {
            $this->prepareChild($component);

            if ($previousComponent !== null && method_exists($component, 'getLogicalLink')) {
                $link = $component->getLogicalLink();
                if ($link) {
                    $sqlParts[] = $link;
                }
            }

            $sqlParts[] = $component->build();

            $this->mergeChildState($component);
            $previousComponent = $component;
        }

        return implode(' ', $sqlParts);
    }
}