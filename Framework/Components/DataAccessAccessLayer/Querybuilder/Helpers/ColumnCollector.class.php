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
        foreach ($this->selectMap as $key => $config) {
            if (is_string($config['table'])) {
                $columnMap[$config['table'] ?? 'main'] = [
                    'columns' => $config['columns'],
                    'customAlias' => $config['customAlias'],
                    'withAlias' => $config['withAlias'],
                ];
            } else {
                $columnMap['main'] = $config;
            }
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