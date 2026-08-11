<?php

declare(strict_types=1);

class ConditionBuilderHelper
{
    private ConditionGroupBuilder $builder;

    public function __construct(
        private EntityManagerInterface $em,
        private mixed $conditions = null,
    ) {
        $this->builder = new ConditionGroupBuilder($em);
        $this->build($conditions);
    }

    /**
     * @return ConditionGroupBuilder
     */
    public function getBuilder(): ConditionGroupBuilder
    {
        return $this->builder;
    }

    private function build(array $conditions): self
    {
        foreach ($conditions as $conditionData) {
            if ($conditionData instanceof SqlGenericDataPayload) {
                $conditionsArray = $conditionData->getData();
                $method = $conditionData->getMethod();
            } elseif (is_array($conditionData) && isset($conditionData['conditions'])) {
                $conditionsArray = $conditionData['conditions'];
                $method = $conditionData['method'];
            } elseif (isset($conditionData['onConditions'])) {
                $conditionsArray = $conditionData['onConditions'];
                $method = $conditionData['method'];
                $this->builder->addCondition($method, $conditionsArray, $conditionData);
                continue;
            } else {
                continue;
            }

            $this->builder->addCondition($method, $conditionsArray);
        }

        return $this;
    }

    private function createOnClause(array $onData): ConditionClause
    {
        $onData = $onData;
        $onClause = new ConditionClause(
            conditions: $onData,
            method: $onData['method'],
            em: $this->em,
        );

        if (isset($onData['joinContext'])) {
            $onClause->setJoinContext($onData['joinContext']);
        }

        return $onClause;
    }
}