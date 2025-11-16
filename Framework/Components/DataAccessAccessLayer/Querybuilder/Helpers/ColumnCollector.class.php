<?php

declare(strict_types=1);
class ColumnCollector
{
    public function __construct(
        private array $selectMap,
        private array $joinMap,
    ) {
    }

    public function all(): array
    {
        $columnMap = [];

        $this->addSelectColumns($columnMap);
        $this->addJoinColumns($columnMap);

        return $columnMap;
    }

    private function addSelectColumns(array &$columnMap): void
    {
        if (isset($this->selectMap['select'])) {
            $config = $this->selectMap['select'];
            $columnMap[$config['table'] ?? 'main'] = [
                'columns' => $config['columns'],
                'customAlias' => $config['customAlias'],
                'withAlias' => $config['withAlias'],
            ];
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