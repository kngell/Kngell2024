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
    private bool $isAjax;
    private bool $isCli;
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
        LogLevel::EMERGENCY => '#FF0000',
        LogLevel::ALERT => '#FF3300',
        LogLevel::CRITICAL => '#FF6600',
        LogLevel::ERROR => '#FF9900',
        LogLevel::WARNING => '#FFCC00',
        LogLevel::NOTICE => '#99CC00',
        LogLevel::INFO => '#6699FF',
        LogLevel::DEBUG => '#999999',
    ];

    // These MUST be static to be shared across all instances
    private static array $browserLogs = [];
    private static bool $headerPrinted = false;
    private static bool $shutdownRegistered = false;

    public function __construct(
        ?string $logFile = null,
        bool $displayInBrowser = false,
        int $debugLevel = 3,
        bool $enabled = true,
        ?bool $isAjax = null,
        ?bool $isCli = null,
    ) {
        $this->logFile = $logFile ?? STORAGE . 'logs' . DS . 'app.log';
        $this->isAjax = $isAjax ?? $this->detectAjax();
        $this->isCli = $isCli ?? (php_sapi_name() === 'cli');

        // For AJAX, never display in browser (it would break JSON responses)
        $this->displayInBrowser = $displayInBrowser && !$this->isAjax && !$this->isCli;
        $this->debugLevel = $debugLevel;
        $this->enabled = $enabled;

        $this->enabledBrowserLevels = $this->getBrowserLevels();
        $this->ensureLogDirectory();
        $this->rotateLogIfNeeded();

        // Register shutdown function - using static property correctly
        if ($this->displayInBrowser && !self::$shutdownRegistered) {
            register_shutdown_function([$this, 'flushBrowserLogs']);
            self::$shutdownRegistered = true;
        }

        // Log AJAX request info if debug level is high enough
        if ($this->isAjax && $this->debugLevel >= 2 && $this->enabled) {
            $this->debug('AJAX Request', [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'data' => $this->getAjaxInput(),
            ]);
        }
    }

    public function __destruct()
    {
        $this->flushBrowserLogs();
    }

    public function test(): void
    {
        echo 'Logger Status:<br>';
        echo '- Enabled: ' . ($this->enabled ? 'Yes' : 'No') . '<br>';
        echo '- Display in Browser: ' . ($this->displayInBrowser ? 'Yes' : 'No') . '<br>';
        echo '- Debug Level: ' . $this->debugLevel . '<br>';
        echo '- Log File: ' . $this->logFile . '<br>';
        echo '- Log Directory Exists: ' . (is_dir(dirname($this->logFile)) ? 'Yes' : 'No') . '<br>';
        echo '- Log File Writable: ' . (is_writable(dirname($this->logFile)) ? 'Yes' : 'No') . '<br>';
        echo '- PHP SAPI: ' . php_sapi_name() . '<br>';
        echo '- Headers Sent: ' . (headers_sent() ? 'Yes' : 'No') . '<br>';
        echo '- OB Level: ' . ob_get_level() . '<br>';
    }

    public function handleAjaxRequest(): void
    {
        if (!$this->isAjaxRequest()) {
            return;
        }

        $action = $_GET['logger_action'] ?? $_POST['logger_action'] ?? '';

        switch ($action) {
            case 'get_logs':
                echo $this->getLogsForAjax();
                exit;
            case 'clear_logs':
                $this->clear();
                echo json_encode(['success' => true, 'message' => 'Logs cleared']);
                exit;
            default:
                // Just log the AJAX request
                $this->info('AJAX request made', [
                    'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                ]);
        }
    }

    public function logQuery($qb, string $context = ''): void
    {
        if (!$this->enabled || $this->debugLevel < 2) {
            return;
        }

        try {
            if (method_exists($qb, 'debugSql')) {
                $debugInfo = $qb->debugSql();
                $this->debug('Database Query', [
                    'context' => $context,
                    'sql' => $debugInfo->rawSql ?? 'Unknown',
                    'parameters' => $debugInfo->parameters ?? [],
                    'execution_time_ms' => $debugInfo->executionTimeMs ?? 0,
                ]);
            }
        } catch (Exception $e) {
            $this->error('Failed to log query', ['error' => $e->getMessage()]);
        }
    }

    public function logAjaxResponse(array $response, float $executionTime): void
    {
        if (!$this->enabled) {
            return;
        }

        $level = ($response['success'] ?? false) ? LogLevel::INFO : LogLevel::ERROR;
        $this->log($level, 'AJAX Response', [
            'success' => $response['success'] ?? false,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'has_redirect' => isset($response['redirect']),
            'has_flash' => isset($response['flash']),
        ]);
    }

    public function getLogsForAjax(int $lines = 100): string
    {
        header('Content-Type: application/json');

        $logs = $this->getRecent($lines);

        return json_encode([
            'success' => true,
            'logs' => $logs,
            'count' => count($logs),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    public function log($level, mixed $message, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!in_array($level, $this->psrLevels, true)) {
            throw new InvalidArgumentException("Invalid log level: {$level}");
        }

        // Ensure message is string
        if (!is_string($message)) {
            $message = $this->convertToString($message);
        }

        $logEntry = $this->formatLogEntry($level, $message, $context);

        // ALWAYS write to file (even for AJAX)
        $this->writeToFile($logEntry);

        // Log to PHP error log for debugging
        error_log($logEntry);

        // Store for browser display ONLY if not AJAX and display is enabled
        if (!$this->isAjax && $this->displayInBrowser && in_array($level, $this->enabledBrowserLevels, true)) {
            self::$browserLogs[] = [
                'level' => $level,
                'entry' => $logEntry,
                'timestamp' => microtime(true),
            ];
        }
    }

    public function injectLoggerScripts(): void
    {
        if (!$this->displayInBrowser) {
            return;
        }

        echo $this->getJavaScript();
    }

    public function flushBrowserLogs(): void
    {
        if (!$this->displayInBrowser || empty(self::$browserLogs)) {
            return;
        }

        // Don't output if headers already sent and no buffer
        if (headers_sent() && ob_get_level() === 0) {
            return;
        }

        // Get the browser output
        $browserOutput = $this->getBrowserOutput();

        // Handle output buffering
        if (ob_get_level() > 0) {
            $existingContent = ob_get_clean();
            echo $existingContent;
            echo $browserOutput;
        } else {
            echo $browserOutput;
        }

        self::$browserLogs = [];
    }

    public function emergency(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function closeBrowserContainer(): void
    {
        if (self::$headerPrinted && ob_get_length() > 0) {
            echo '</div>';
            self::$headerPrinted = false;
        }
    }

    public function clear(): bool
    {
        if (file_exists($this->logFile)) {
            return file_put_contents($this->logFile, '') !== false;
        }
        return true;
    }

    public function getRecent(int $lines = 100): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $content = file_get_contents($this->logFile);
        $allLines = array_filter(explode("\n", $content));

        return array_slice($allLines, -$lines);
    }

    public function getSize(): float
    {
        if (!file_exists($this->logFile)) {
            return 0.0;
        }

        return round(filesize($this->logFile) / 1048576, 2);
    }

    public function getDebugLevel(): int
    {
        return $this->debugLevel;
    }

    private function getJavaScript(): string
    {
        return <<<'HTML'
    <script>
    (function() {
        // Initialize logger functionality
        function initLogger() {
            const toggleBtn = document.getElementById('logger-toggle-btn');
            const clearBtn = document.getElementById('logger-clear-btn');
            const content = document.getElementById('logger-content');
            const container = document.getElementById('custom-logger-container');
            
            if (!toggleBtn || !clearBtn || !content || !container) {
                console.warn('Logger elements not found, retrying...');
                setTimeout(initLogger, 100);
                return;
            }
            
            // Set initial state
            window.loggerVisible = true;
            container.style.maxHeight = '300px';
            
            // Toggle function
            toggleBtn.onclick = function(e) {
                e.stopPropagation();
                if (window.loggerVisible) {
                    content.style.display = 'none';
                    container.style.maxHeight = '40px';
                    window.loggerVisible = false;
                    toggleBtn.textContent = 'Expand';
                } else {
                    content.style.display = 'block';
                    container.style.maxHeight = '300px';
                    window.loggerVisible = true;
                    toggleBtn.textContent = 'Collapse';
                }
                return false;
            };
            
            // Clear function
            clearBtn.onclick = function(e) {
                e.stopPropagation();
                if (content) {
                    content.innerHTML = '';
                }
                // Optional: Send AJAX to clear server logs
                if (typeof fetch !== 'undefined') {
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({action: 'clear_logs'})
                    }).catch(console.error);
                }
                return false;
            };
            
            // Also make header clickable for toggle
            const header = document.getElementById('logger-header');
            if (header) {
                header.onclick = function(e) {
                    if (e.target.tagName !== 'BUTTON') {
                        toggleBtn.click();
                    }
                };
            }
            
            // Auto-scroll to bottom when new logs are added
            const observer = new MutationObserver(function() {
                if (window.loggerVisible && content) {
                    content.scrollTop = content.scrollHeight;
                }
            });
            
            observer.observe(content, { childList: true, subtree: true });
            
            console.log('Logger initialized');
        }
        
        // Run when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLogger);
        } else {
            initLogger();
        }
    })();
    </script>
    HTML;
    }

    private function getAjaxInput(): array
    {
        $input = [];

        // Get JSON input
        $jsonInput = file_get_contents('php://input');
        if ($jsonInput) {
            $decoded = json_decode($jsonInput, true);
            if ($decoded !== null) {
                $input['json'] = $decoded;
            }
        }

        // Get POST/GET data (excluding sensitive info for security)
        $input['post_keys'] = array_keys($_POST);
        $input['get_keys'] = array_keys($_GET);

        return $input;
    }

    private function detectAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
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

        $js = !self::$headerPrinted ? $this->getJavaScript() : '';
        $header = !self::$headerPrinted ? $this->getBrowserHeader(count(self::$browserLogs)) : '';
        self::$headerPrinted = true;

        return $js . $header . $logEntries . '</div></div>';
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
        z-index: 999999;
        border-top: 2px solid #444;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
    ">
        <div style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #2a2a2a;
            border-bottom: 1px solid #444;
            cursor: pointer;
        " id="logger-header">
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
                <button id="logger-toggle-btn" style="
                    background: #444;
                    border: none;
                    color: #fff;
                    padding: 2px 8px;
                    border-radius: 3px;
                    font-size: 11px;
                    cursor: pointer;
                ">Toggle</button>
                <button id="logger-clear-btn" style="
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
        <div id="logger-content" style="
            padding: 8px 12px; 
            font-family: monospace;
            max-height: 300px;
            overflow-y: auto;
            display: block;
        ">
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

    private function convertToString(mixed $value): string
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

    private function convertToShortString(mixed $value): string
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
        $this->ensureLogDirectory();
        file_put_contents($this->logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function getBrowserLogEntry(string $level, string $logEntry): string
    {
        $color = $this->levelColors[$level] ?? '#999999';

        $microtime = microtime(true);
        $seconds = (int) $microtime;
        $milliseconds = str_pad((string) round(($microtime - $seconds) * 1000), 3, '0', STR_PAD_LEFT);
        $time = date('H:i:s', $seconds);

        $icon = $this->getLevelIcon($level);

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
            case 0:
                return [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR];
            case 1:
                return [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING];
            case 2:
                return [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR,
                    LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO];
            case 3:
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
            $this->cleanupOldBackups();
        }
    }

    private function cleanupOldBackups(): void
    {
        $directory = dirname($this->logFile);
        $pattern = $this->logFile . '.*';
        $backups = glob($pattern);

        if (count($backups) > 5) {
            usort($backups, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            $toRemove = array_slice($backups, 0, count($backups) - 5);
            foreach ($toRemove as $backup) {
                @unlink($backup);
            }
        }
    }
}