<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class CustomLogger implements LoggerInterface
{
    private const LOG_FILE = STORAGE . 'logs' . DS . 'app.log';
    private const MAX_FILE_SIZE = 10485760; // 10MB

    private string $logFile;
    private bool $displayInBrowser;
    private int $debugLevel;
    private array $enabledBrowserLevels;
    private bool $enabled;
    private bool $headerPrinted = false;
    private bool $shutdownRegistered = false;
    private array $psrLevels = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];
    private array $levelColors = [
        LogLevel::EMERGENCY => '#FF0000', // Red
        LogLevel::ALERT => '#FF3300',     // Orange Red
        LogLevel::CRITICAL => '#FF6600',  // Dark Orange
        LogLevel::ERROR => '#FF9900',     // Orange
        LogLevel::WARNING => '#FFCC00',   // Yellow
        LogLevel::NOTICE => '#99CC00',    // Light Green
        LogLevel::INFO => '#6699FF',      // Blue
        LogLevel::DEBUG => '#999999',     // Gray
    ];
    private static array $browserLogs = [];

    public function __construct(
        string $logFile = self::LOG_FILE,
        bool $displayInBrowser = false,
        int $debugLevel = 0,
        bool $enabled = false,
    ) {
        $this->logFile = $logFile;
        $this->displayInBrowser = $displayInBrowser && php_sapi_name() !== 'cli';
        $this->debugLevel = $debugLevel;
        $this->enabled = $enabled;

        $this->enabledBrowserLevels = $this->getBrowserLevels();

        $this->ensureLogDirectory();
        $this->rotateLogIfNeeded();

        // Start output buffering early
        if ($this->displayInBrowser && !$this->shutdownRegistered && !headers_sent()) {
            ob_start();
            register_shutdown_function([$this, 'flushBrowserLogs']);
            $this->shutdownRegistered = true;
        }
    }

    // public function __destruct()
    // {
    //     $this->flushBrowserLogs();
    // }

    public function log($level, $message, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!in_array($level, $this->psrLevels, true)) {
            throw new InvalidArgumentException("Invalid log level: {$level}");
        }

        $logEntry = $this->formatLogEntry($level, $message, $context);

        // Write to file
        $this->writeToFile($logEntry);

        // Store for browser display
        if ($this->displayInBrowser && in_array($level, $this->enabledBrowserLevels, true)) {
            self::$browserLogs[] = [
                'level' => $level,
                'entry' => $logEntry,
                'timestamp' => microtime(true),
            ];
        }
    }

    public function flushBrowserLogs(): void
    {
        if (!$this->displayInBrowser || empty(self::$browserLogs)) {
            return;
        }

        if (ob_get_level() === 0) {
            // No active buffer → print directly
            echo $this->getBrowserOutput();
            self::$browserLogs = [];
            return;
        }

        $output = ob_get_contents(); // 👈 DO NOT CLEAN YET
        ob_end_clean();

        echo $output;
        echo $this->getBrowserOutput();

        self::$browserLogs = [];
    }

    // public function flushBrowserLogs(): void
    // {
    //     if (!$this->displayInBrowser || empty(self::$browserLogs) || headers_sent()) {
    //         return;
    //     }

    //     // Get current buffer content
    //     $output = ob_get_clean();

    //     // Prepare browser log output
    //     $browserOutput = $this->getBrowserOutput();

    //     // Output everything
    //     echo $output . $browserOutput;

    //     // Clear stored logs
    //     self::$browserLogs = [];
    // }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function closeBrowserContainer(): void
    {
        if ($this->headerPrinted && ob_get_length() > 0) {
            echo '</div>';
            $this->headerPrinted = false;
        }
    }

    /**
     * Clear the log file.
     */
    public function clear(): bool
    {
        if (file_exists($this->logFile)) {
            return file_put_contents($this->logFile, '') !== false;
        }
        return true;
    }

    /**
     * Get recent log entries.
     */
    public function getRecent(int $lines = 100): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $content = file_get_contents($this->logFile);
        $allLines = array_filter(explode("\n", $content));

        return array_slice($allLines, -$lines);
    }

    /**
     * Get log file size in MB.
     */
    public function getSize(): float
    {
        if (!file_exists($this->logFile)) {
            return 0.0;
        }

        return round(filesize($this->logFile) / 1048576, 2);
    }

    private function getBrowserOutput(): string
    {
        if (empty(self::$browserLogs)) {
            return '';
        }

        $logEntries = '';
        foreach (self::$browserLogs as $log) {
            $logEntries .= $this->getBrowserLogEntry($log['level'], $log['entry']);
        }

        $header = $this->getBrowserHeader(count(self::$browserLogs));

        return $header . $logEntries . '</div></div>';
    }

    private function getBrowserHeader(int $logCount): string
    {
        $currentTime = date('H:i:s');

        return <<<HTML
        <div id="custom-logger-container" style="
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1a1a1a;
            color: #fff;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-height: 300px;
            overflow-y: auto;
            z-index: 999999;
            border-top: 2px solid #444;
        ">
            <div style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 12px;
                background: #2a2a2a;
                border-bottom: 1px solid #444;
                position: sticky;
                top: 0;
            ">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <strong style="color: #fff;">📊 Application Logs</strong>
                    <span style="
                        background: #666;
                        color: #fff;
                        padding: 2px 8px;
                        border-radius: 10px;
                        font-size: 11px;
                    ">{$logCount} entries</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <small style="color: #aaa;">{$currentTime}</small>
                    <button onclick="toggleLogger()" style="
                        background: #444;
                        border: none;
                        color: #fff;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                        cursor: pointer;
                    ">Toggle</button>
                    <button onclick="clearLogger()" style="
                        background: #666;
                        border: none;
                        color: #fff;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                        cursor: pointer;
                    ">Clear</button>
                </div>
            </div>
            <div id="logger-content" style="padding: 8px 12px; font-family: monospace;">
        HTML;
    }

    private function formatLogEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $interpolatedMessage = $this->interpolate($message, $context);
        $formattedContext = $this->formatContext($context);

        $logEntry = "[{$timestamp}] " . strtoupper($level) . ": {$interpolatedMessage}";

        if (!empty($formattedContext)) {
            $logEntry .= " [{$formattedContext}]";
        }

        // Add memory usage for debugging
        if ($this->debugLevel >= 2) {
            $memory = round(memory_get_usage(true) / 1048576, 2) . 'MB';
            $peak = round(memory_get_peak_usage(true) / 1048576, 2) . 'MB';
            $logEntry .= " [Memory: {$memory}, Peak: {$peak}]";
        }

        return $logEntry;
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $this->convertToString($val);
        }

        return strtr($message, $replace);
    }

    private function convertToString($value): string
    {
        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_resource($value)) {
            return 'Resource ' . get_resource_type($value);
        }

        return 'Unknown type';
    }

    private function formatContext(array $context): string
    {
        if (empty($context)) {
            return '';
        }

        $parts = [];
        foreach ($context as $key => $value) {
            $parts[] = $key . '=' . $this->convertToShortString($value);
        }

        return implode(', ', $parts);
    }

    private function convertToShortString($value): string
    {
        if (is_string($value)) {
            if (strlen($value) > 50) {
                return substr($value, 0, 47) . '...';
            }
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return 'Array[' . count($value) . ']';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }

    private function writeToFile(string $logEntry): void
    {
        // Ensure log directory exists
        $this->ensureLogDirectory();

        // Append log entry with newline
        file_put_contents($this->logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function displayBrowserLog(string $level, string $logEntry): void
    {
        if (headers_sent() || ob_get_level() === 0) {
            return;
        }

        // Start output buffering if not already started
        if (ob_get_level() === 0) {
            ob_start();
        }

        // Print header only once
        if (!$this->headerPrinted) {
            echo $this->getBrowserHeader(count(self::$browserLogs));
            $this->headerPrinted = true;
        }

        // Print log entry
        echo $this->getBrowserLogEntry($level, $logEntry);
    }

    // private function getBrowserHeader(): string
    // {
    //     return <<<HTML
    //     <div style="
    //         position: fixed;
    //         bottom: 0;
    //         left: 0;
    //         right: 0;
    //         background: #1a1a1a;
    //         color: #fff;
    //         font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    //         font-size: 12px;
    //         line-height: 1.4;
    //         max-height: 300px;
    //         overflow-y: auto;
    //         z-index: 9999;
    //         border-top: 2px solid #333;
    //         padding: 5px 10px;
    //     ">
    //         <div style="
    //             display: flex;
    //             justify-content: space-between;
    //             align-items: center;
    //             padding: 5px 0;
    //             border-bottom: 1px solid #333;
    //             margin-bottom: 5px;
    //         ">
    //             <strong>Application Log</strong>
    //             <small style="color: #999;">
    //                 Time: {$this->getCurrentTime()}
    //             </small>
    //         </div>
    //         <div style="font-family: monospace;">
    //     HTML;
    // }

    private function getBrowserLogEntry(string $level, string $logEntry): string
    {
        $color = $this->levelColors[$level] ?? '#999999';
        $time = date('H:i:s', (int) explode('.', microtime(true))[0]);
        $milliseconds = substr(explode('.', microtime(true))[1] ?? '000', 0, 3);
        $icon = $this->getLevelIcon($level);

        // Clean and format the message
        $safeEntry = htmlspecialchars($logEntry, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
            <div style="
                padding: 4px 0;
                border-bottom: 1px solid #2a2a2a;
                display: flex;
                align-items: flex-start;
                gap: 8px;
            ">
                <div style="
                    flex-shrink: 0;
                    width: 100px;
                    color: #666;
                    font-size: 11px;
                    text-align: right;
                ">{$time}.{$milliseconds}</div>
                <div style="flex-shrink: 0; width: 24px; text-align: center;">{$icon}</div>
                <div style="
                    flex: 1;
                    color: {$color};
                    word-break: break-word;
                    white-space: pre-wrap;
                ">{$safeEntry}</div>
            </div>
        HTML;
    }

    private function getLevelIcon(string $level): string
    {
        $icons = [
            LogLevel::EMERGENCY => '🔥',
            LogLevel::ALERT => '🚨',
            LogLevel::CRITICAL => '💥',
            LogLevel::ERROR => '❌',
            LogLevel::WARNING => '⚠️',
            LogLevel::NOTICE => '📝',
            LogLevel::INFO => 'ℹ️',
            LogLevel::DEBUG => '🔍',
        ];

        return $icons[$level] ?? '📌';
    }

    private function getBrowserLevels(): array
    {
        switch ($this->debugLevel) {
            case 0: // Production - only errors
                return [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR];
            case 1: // Development - errors and warnings
                return [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING];
            case 2: // Debug - everything except debug
                return [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR,
                    LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG];

            case 3: // Verbose - everything
                return $this->psrLevels;
            default:
                return [LogLevel::ERROR, LogLevel::WARNING];
        }
    }

    private function ensureLogDirectory(): void
    {
        $directory = dirname($this->logFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function rotateLogIfNeeded(): void
    {
        if (!file_exists($this->logFile)) {
            return;
        }

        if (filesize($this->logFile) >= self::MAX_FILE_SIZE) {
            $backupFile = $this->logFile . '.' . date('Y-m-d_H-i-s');
            rename($this->logFile, $backupFile);

            // Keep only last 5 backup files
            $this->cleanupOldBackups();
        }
    }

    private function cleanupOldBackups(): void
    {
        $directory = dirname($this->logFile);
        $pattern = $this->logFile . '.*';
        $backups = glob($pattern);

        if (count($backups) > 5) {
            // Sort by modification time (oldest first)
            usort($backups, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Remove oldest backups (keep only last 5)
            $toRemove = array_slice($backups, 0, count($backups) - 5);
            foreach ($toRemove as $backup) {
                @unlink($backup);
            }
        }
    }

    private function getCurrentTime(): string
    {
        return date('H:i:s');
    }
}