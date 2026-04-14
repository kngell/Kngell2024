<?php

declare(strict_types=1);

final class DebugQuery
{
    public function debugSql(QueryBuilder $qb): void
    {
        echo '<pre style="background: #1e1e1e; color: #9cdcfe; padding: 25px; border-radius: 12px; line-height: 1.6; font-family: \'Fira Code\', monospace; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow-x: auto;">';

        if ($qb->hasQuery()) {
            /** @var SqlDebugInfo $debugInfo */
            $debugInfo = $qb->debugSql();
            $sql = $debugInfo->rawSql;

            // 1. PERFORMANCE AUDIT (The Analysis Layer)
            $alerts = [];
            if (str_contains(strtoupper($sql), "LIKE '%")) {
                $alerts[] = '⚠️ Leading Wildcard: This will cause a full table scan (unindexed).';
            }
            if (str_contains(strtoupper($sql), 'SELECT *')) {
                $alerts[] = '⚠️ SELECT *: Consider specific column selection for memory efficiency.';
            }
            if (!str_contains(strtoupper($sql), 'LIMIT') && str_contains(strtoupper($sql), 'SELECT')) {
                $alerts[] = "ℹ️ No LIMIT detected: Ensure this isn't a high-volume table.";
            }
            if (substr_count(strtoupper($sql), 'JOIN') > 5) {
                $alerts[] = '⚠️ High Join Count: Query complexity might impact execution plan.';
            }

            // 2. HEADER & METRICS
            echo '<span style="color: #4ec9b0; font-weight: bold;">[QUERY METRICS]</span>' . PHP_EOL;
            echo 'Build Time         : <span style="color: #ce9178;">' . number_format($debugInfo->executionTimeMs, 4) . ' ms</span>' . PHP_EOL;

            $precedence = $debugInfo->metadata['precedence_logic_detected'] ?? false;
            echo 'Precedence Logic   : ' . ($precedence
                ? '<span style="color: #f44747; font-weight: bold;">ACTIVE (Parentheses injected)</span>'
                : '<span style="color: #6a9955;">INACTIVE</span>') . PHP_EOL;

            if (!empty($alerts)) {
                echo PHP_EOL . '<span style="color: #ffca28; font-weight: bold;">[PERFORMANCE ALERTS]</span>' . PHP_EOL;
                foreach ($alerts as $alert) {
                    echo '<span style="color: #ffca28;">' . $alert . '</span>' . PHP_EOL;
                }
            }

            echo '<span style="color: #569cd6;">' . str_repeat('=', 65) . '</span>' . PHP_EOL . PHP_EOL;

            // 3. FORMATTING & INDENTATION
            $keywords = ['WITH RECURSIVE', 'UNION ALL', 'LEFT JOIN', 'INNER JOIN', 'GROUP BY', 'ORDER BY', 'SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'ON', 'LIMIT'];
            usort($keywords, fn ($a, $b) => strlen($b) - strlen($a));

            foreach ($keywords as $keyword) {
                $sql = preg_replace('/\b' . $keyword . '\b/', PHP_EOL . $keyword, $sql);
            }

            $indentedSql = '';
            $depth = 0;
            foreach (explode(PHP_EOL, $sql) as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                if (str_starts_with($line, ')')) {
                    $depth = max(0, $depth - 1);
                }
                $indentedSql .= str_repeat('    ', $depth) . $line . PHP_EOL;
                if (str_ends_with($line, '(') || (str_contains($line, '(') && !str_contains($line, ')'))) {
                    $depth++;
                }
            }

            echo '<strong style="color: #dcdcdc; border-bottom: 1px solid #444;">Formatted SQL:</strong>' . PHP_EOL;
            echo '<span style="color: #d7ba7d;">' . htmlspecialchars(trim($indentedSql)) . '</span>';

            // 4. PARAMETERS
            echo PHP_EOL . PHP_EOL . '<strong style="color: #dcdcdc; border-bottom: 1px solid #444;">Parameters:</strong>' . PHP_EOL;
            foreach ($debugInfo->parameters as $name => $val) {
                $displayValue = match(true) {
                    is_null($val) => '<span style="color: #569cd6;">NULL</span>',
                    is_bool($val) => '<span style="color: #569cd6;">' . ($val ? 'TRUE' : 'FALSE') . '</span>',
                    is_numeric($val) => '<span style="color: #b5cea8;">' . $val . '</span>',
                    default => '<span style="color: #ce9178;">' . var_export($val, true) . '</span>',
                };
                echo sprintf('  <span style="color: #9cdcfe;">%-18s</span> => %s <small style="color: #6a9955; opacity: 0.7;">(%s)</small>', $name, $displayValue, gettype($val)) . PHP_EOL;
            }
        } else {
            echo '<span style="color: #f44747; font-weight: bold;">[ERROR] No Query Built</span>';
        }
        // 5. UNIT TEST PAYLOAD (Ready for copy-paste)
        echo PHP_EOL . '<strong style="color: #dcdcdc; border-bottom: 1px solid #444;">Unit Test Payload:</strong>' . PHP_EOL;

        $phpCode = '// Assert this SQL' . PHP_EOL;
        $phpCode .= '$expectedSql = "' . str_replace("\n", ' ', $debugInfo->rawSql) . '";' . PHP_EOL;
        $phpCode .= '$expectedParams = ' . $this->exportArrayForPhp($debugInfo->parameters) . ';';

        echo '<span style="color: #6a9955; font-size: 0.9em;">' . htmlspecialchars($phpCode) . '</span>';
        echo '</pre>';
        die();
    }

    /**
     * Formats a raw array into a clean PHP 8.x short-array syntax string.
     */
    private function exportArrayForPhp(array $data): string
    {
        $export = var_export($data, true);
        $export = preg_replace('/^([ ]*)(.*)/m', '$1$1$2', $export);
        $array = preg_replace(['/^array \(/', '/\)$/', '/=> [^\n ]+array \(/'], ['[', ']', '=> ['], $export);
        return str_replace('=> ', ' => ', (string) $array);
    }
}