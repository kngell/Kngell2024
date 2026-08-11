<?php

declare(strict_types=1);

trait SqlHavingTrait
{
    public function having(mixed ...$conditions): static
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow[] = 'having';
        return $this;
    }

    public function orHaving(mixed ...$conditions): static
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        return $this;
    }
}