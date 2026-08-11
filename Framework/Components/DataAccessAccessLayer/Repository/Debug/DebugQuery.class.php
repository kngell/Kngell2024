<?php

declare(strict_types=1);

use Psr\Log\LogLevel;

final class DebugQuery
{
    private bool $isCli = false;
    private bool $isAjax = false;
    private ?CustomLogger $logger = null;

    public function __construct(?bool $isAjax = null, ?bool $isCli = null, ?CustomLogger $logger = null)
    {
        $this->isAjax = $isAjax ?? $this->detectAjax();
        $this->isCli = $isCli ?? (php_sapi_name() === 'cli');
        $this->logger = $logger;
    }

    public function debugSql(QueryBuilder $qb, ?string $format = null): ?array
    {
        if (!$qb->hasQuery()) {
            $this->outputError('No Query Built', $format);

            if ($this->logger) {
                $this->logger->warning('SQL Debug: No query built');
            }

            return null;
        }

        /** @var SqlDebugInfo $debugInfo */
        $debugInfo = $qb->debugSql();
        $data = $this->buildDebugData($debugInfo);

        // Log to file if logger is available
        if ($this->logger) {
            $logLevel = empty($data['alerts']) ? LogLevel::DEBUG : LogLevel::WARNING;
            $this->logger->log($logLevel, 'SQL Query Debug', [
                'sql' => $debugInfo->rawSql,
                'execution_time_ms' => $debugInfo->executionTimeMs,
                'parameters_count' => count($debugInfo->parameters),
                'alerts' => $data['alerts'],
                'has_precedence_logic' => $data['metrics']['precedence_logic'],
            ]);
        }

        if ($format === 'json' || $this->isAjax) {
            $this->outputJson($data);
        } elseif ($format === 'array' || $this->isCli) {
            return $data;
        } else {
            $this->outputHtml($data);
        }

        die();
    }

    public function getAlerts(string $sql): array
    {
        // Make this public so CustomLogger can use it
        $alerts = [];
        $sqlUpper = strtoupper($sql);

        if (str_contains($sqlUpper, "LIKE '%")) {
            $alerts[] = '⚠️ Leading Wildcard: This will cause a full table scan (unindexed).';
        }
        if (str_contains($sqlUpper, 'SELECT *')) {
            $alerts[] = '⚠️ SELECT *: Consider specific column selection for memory efficiency.';
        }
        if (!str_contains($sqlUpper, 'LIMIT') && str_contains($sqlUpper, 'SELECT')) {
            $alerts[] = "ℹ️ No LIMIT detected: Ensure this isn't a high-volume table.";
        }
        if (substr_count($sqlUpper, 'JOIN') > 5) {
            $alerts[] = '⚠️ High Join Count: Query complexity might impact execution plan.';
        }

        return $alerts;
    }

    private function buildDebugData(SqlDebugInfo $debugInfo): array
    {
        return [
            'metrics' => [
                'execution_time_ms' => $debugInfo->executionTimeMs,
                'precedence_logic' => $debugInfo->metadata['precedence_logic_detected'] ?? false,
            ],
            'sql' => [
                'raw' => $debugInfo->rawSql,
                'formatted' => $this->formatSql($debugInfo->rawSql),
            ],
            'parameters' => $debugInfo->parameters,
            'alerts' => $this->getAlerts($debugInfo->rawSql),
            'unit_test_payload' => $this->buildUnitTestPayload($debugInfo),
        ];
    }

    private function outputJson(array $data): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    private function outputHtml(array $data): void
    {
        echo '<pre style="background: #1e1e1e; color: #9cdcfe; padding: 25px; border-radius: 12px; line-height: 1.6; font-family: \'Fira Code\', monospace; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow-x: auto;">';

        // HEADER & METRICS
        echo '<span style="color: #4ec9b0; font-weight: bold;">[QUERY METRICS]</span>' . PHP_EOL;
        echo 'Build Time         : <span style="color: #ce9178;">' . number_format($data['metrics']['execution_time_ms'], 4) . ' ms</span>' . PHP_EOL;
        echo 'Precedence Logic   : ' . ($data['metrics']['precedence_logic']
            ? '<span style="color: #f44747; font-weight: bold;">ACTIVE (Parentheses injected)</span>'
            : '<span style="color: #6a9955;">INACTIVE</span>') . PHP_EOL;

        // PERFORMANCE ALERTS
        if (!empty($data['alerts'])) {
            echo PHP_EOL . '<span style="color: #ffca28; font-weight: bold;">[PERFORMANCE ALERTS]</span>' . PHP_EOL;
            foreach ($data['alerts'] as $alert) {
                echo '<span style="color: #ffca28;">' . $alert . '</span>' . PHP_EOL;
            }
        }

        echo '<span style="color: #569cd6;">' . str_repeat('=', 65) . '</span>' . PHP_EOL . PHP_EOL;

        // FORMATTED SQL
        echo '<strong style="color: #dcdcdc; border-bottom: 1px solid #444;">Formatted SQL:</strong>' . PHP_EOL;
        echo '<span style="color: #d7ba7d;">' . htmlspecialchars($data['sql']['formatted']) . '</span>';

        // PARAMETERS
        echo PHP_EOL . PHP_EOL . '<strong style="color: #dcdcdc; border-bottom: 1px solid #444;">Parameters:</strong>' . PHP_EOL;
        foreach ($data['parameters'] as $name => $val) {
            $displayValue = match(true) {
                is_null($val) => '<span style="color: #569cd6;">NULL</span>',
                is_bool($val) => '<span style="color: #569cd6;">' . ($val ? 'TRUE' : 'FALSE') . '</span>',
                is_numeric($val) => '<span style="color: #b5cea8;">' . $val . '</span>',
                default => '<span style="color: #ce9178;">' . var_export($val, true) . '</span>',
            };
            echo sprintf('  <span style="color: #9cdcfe;">%-18s</span> => %s <small style="color: #6a9955; opacity: 0.7;">(%s)</small>', $name, $displayValue, gettype($val)) . PHP_EOL;
        }

        // UNIT TEST PAYLOAD
        echo PHP_EOL . '<strong style="color: #dcdcdc; border-bottom: 1px solid #444;">Unit Test Payload:</strong>' . PHP_EOL;
        echo '<span style="color: #6a9955; font-size: 0.9em;">' . htmlspecialchars($data['unit_test_payload']) . '</span>';

        echo '</pre>';
    }

    private function outputError(string $message, ?string $format = null): void
    {
        if ($format === 'json' || $this->isAjax) {
            $this->outputJson(['error' => $message]);
        } else {
            echo "<pre style='background: #1e1e1e; color: #f44747; padding: 25px; border-radius: 12px; font-family: monospace;'>[ERROR] $message</pre>";
        }
    }

    private function formatSql(string $sql): string
    {
        $keywords = ['WITH RECURSIVE', 'UNION ALL', 'LEFT JOIN', 'INNER JOIN', 'GROUP BY', 'ORDER BY', 'SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'ON', 'LIMIT'];
        usort($keywords, fn ($a, $b) => strlen($b) - strlen($a));

        foreach ($keywords as $keyword) {
            $sql = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/', PHP_EOL . $keyword, $sql);
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

        return trim($indentedSql);
    }

    private function buildUnitTestPayload(SqlDebugInfo $debugInfo): string
    {
        $phpCode = '// Assert this SQL' . PHP_EOL;
        $phpCode .= '$expectedSql = "' . addslashes($debugInfo->rawSql) . '";' . PHP_EOL;
        $phpCode .= '$expectedParams = ' . $this->exportArrayForPhp($debugInfo->parameters) . ';';

        return $phpCode;
    }

    private function exportArrayForPhp(array $data): string
    {
        $export = var_export($data, true);
        $export = preg_replace('/^([ ]*)(.*)/m', '$1$1$2', $export);
        $array = preg_replace(['/^array \(/', '/\)$/', '/=> [^\n ]+array \(/'], ['[', ']', '=> ['], $export);
        return str_replace('=> ', ' => ', (string) $array);
    }

    private function detectAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}