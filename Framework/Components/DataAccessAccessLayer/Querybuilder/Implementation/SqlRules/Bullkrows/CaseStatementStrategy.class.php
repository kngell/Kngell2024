<?php class CaseStatementStrategy extends AbstractBulkRow
{
    public function getRule(array $data, ...): string
    {
        $columns = array_keys($data[0]);
        $idColumn = $this->findIdColumn($columns);
        $ids = array_column($data, $idColumn);
        
        $setClauses = [];
        foreach ($columns as $column) {
            if ($column === $idColumn) continue;
            
            $cases = [];
            foreach ($data as $row) {
                $value = $this->formatValue($row[$column], $column, ...);
                $cases[] = "WHEN {$row[$idColumn]} THEN {$value}";
            }
            
            $setClauses[] = "`$column` = CASE `$idColumn` " . implode(' ', $cases) . " ELSE `$column` END";
        }
        
        return "SET " . implode(', ', $setClauses);
    }
}?>