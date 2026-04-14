<?php

declare(strict_types=1);
class ColumnCollector
{
    public function __construct(
        private array $selectMap = [],
        private array $joinMap = [],
    ) {
    }

    public function all(): array
    {
        $columnMap = [];

        $this->addSelectColumns($columnMap);
        $this->addJoinColumns($columnMap);

        return $columnMap;
    }

    public function getWithAlias(): bool
    {
        return $this->selectMap['withAlias'];
    }

    public function getDistinct(): bool
    {
        return $this->selectMap['distinct'];
    }

    public function getCustomAlias(): ?string
    {
        return $this->selectMap['customAlias'];
    }

    /**
     * @param array $selectMap
     *
     * @return ColumnCollector
     */
    public function setSelectMap(array $selectMap): ColumnCollector
    {
        $this->selectMap = $selectMap;

        return $this;
    }

    /**
     * @param array $joinMap
     *
     * @return ColumnCollector
     */
    public function setJoinMap(array $joinMap): ColumnCollector
    {
        $this->joinMap = $joinMap;

        return $this;
    }

    private function addSelectColumns(array &$columnMap): void
    {
        if (empty($this->selectMap['table'])) {
            throw new InvalidArgumentException('Main table cannot be empty in SELECT Query');
        }
        if (is_string($this->selectMap['table'])) {
            $columnMap[$this->selectMap['table']] = [
                'columns' => $this->selectMap['columns'],
                'customAlias' => $this->selectMap['customAlias'],
                'withAlias' => $this->selectMap['withAlias'],
            ];
        } else {
            $columnMap['main'] = $this->selectMap;
        }
    }

    private function addJoinColumns(array &$columnMap): void
    {
        foreach ($this->joinMap as $joinConfig) {
            $table = $joinConfig['table'];

            if (isset($columnMap[$table])) {
                $columnMap[$table]['columns'] = array_merge(
                    $columnMap[$table]['columns'],
                    $joinConfig['columns'],
                );
            } else {
                $columnMap[$table] = [
                    'columns' => $joinConfig['columns'],
                    'customAlias' => $joinConfig['customAlias'],
                    'withAlias' => $joinConfig['withAlias'],
                ];
            }
        }
    }
}