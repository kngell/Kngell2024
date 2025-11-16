<?php

declare(strict_types=1);

class InsertRuleValues extends AbstractConditionRules
{
    /**
     * @param EntityManagerInterface $em
     * @param QueryBuilder $builder
     * @param array $bind_arr
     * @param array $tableAlias
     * @param array $aliasCheck
     * @param array $parameters
     * @param array $tables
     */
    public function __construct(
        EntityManagerInterface $em,
        QueryBuilder $builder,
        array $bind_arr,
        array $tableAlias,
        array $aliasCheck,
        array $parameters,
        array $tables,
        string $method,
        private TypeNormalizerInterface $normalizer,
    ) {
        $this->em = $em;
        $this->builder = $builder;
        $this->bind_arr = $bind_arr;
        $this->tableAlias = $tableAlias;
        $this->aliasCheck = $aliasCheck;
        $this->parameters = $parameters;
        $this->tables = $tables;
        $this->method = $method;
    }

    public function getRule(array|null $values): string
    {
        $rule = '';
        $values = $this->normalize($values);
        foreach ($values as $field => $value) {
            $prefix = $this->paramPrefix($field);
            $end = $field !== array_key_last($values) ? ', ' : '';
            $rule .= ':' . $prefix . '_' . $field . $end;
            $this->parameters[$prefix . '_' . $field] = $value;
        }
        return $rule;
    }

    protected function normalize(array|null $arrayInput): array
    {
        $values = empty($arrayInput) ? $this->em->getEntityProperties() : $arrayInput;
        /** @var Entity */
        $entity = $this->em->getEntity();

        return $this->normalizer->normalizeValuesForDatabase($values, $entity);
    }

    private function end(): string
    {
        if ($this->method === 'fields') {
            return '';
        }
        if ($this->method === 'values') {
            return ',';
        }
        return '';
    }
}