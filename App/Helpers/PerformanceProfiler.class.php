<?php

declare(strict_types=1);
class PerformanceProfiler
{
    private static array $timers = [];
    private static array $logs = [];
    private static bool $enabled = true;

    public static function start(string $label): void
    {
        if (!self::$enabled) {
            return;
        }
        self::$timers[$label] = [
            'start' => microtime(true),
            'memory' => memory_get_usage(true),
        ];
    }

    public static function stop(string $label): void
    {
        if (!self::$enabled || !isset(self::$timers[$label])) {
            return;
        }

        $end = microtime(true);
        $memory = memory_get_usage(true);
        $duration = ($end - self::$timers[$label]['start']) * 1000;
        $memoryUsed = $memory - self::$timers[$label]['memory'];

        self::$logs[$label] = [
            'duration_ms' => round($duration, 2),
            'memory_bytes' => $memoryUsed,
            'memory_mb' => round($memoryUsed / 1024 / 1024, 2),
        ];
    }

    public static function log(): void
    {
        if (empty(self::$logs)) {
            return;
        }

        $output = "\n" . str_repeat('=', 80) . "\n";
        $output .= "PERFORMANCE PROFILE\n";
        $output .= str_repeat('=', 80) . "\n";
        $output .= sprintf("%-40s %15s %15s\n", 'Operation', 'Duration (ms)', 'Memory (MB)');
        $output .= str_repeat('-', 80) . "\n";

        foreach (self::$logs as $label => $data) {
            $output .= sprintf(
                "%-40s %15.2f %15.2f\n",
                $label,
                $data['duration_ms'],
                $data['memory_mb'],
            );
        }

        $output .= str_repeat('=', 80) . "\n";
        $output .= 'TOTAL TIME: ' . round(array_sum(array_column(self::$logs, 'duration_ms')), 2) . "ms\n";
        $output .= 'PEAK MEMORY: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB\n";
        $output .= str_repeat('=', 80) . "\n";

        error_log($output);

        // Also save to file
        $logFile = ROOT_DIR . '/storage/logs/performance_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $output . "\n", FILE_APPEND);
    }
}