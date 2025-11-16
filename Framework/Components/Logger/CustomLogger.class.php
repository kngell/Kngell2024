<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class CustomLogger implements LoggerInterface
{
    private const LOG_FILE = STORAGE . 'logs' . DS . 'app.log';

    private string $logFile;
    private bool $displayInBrowser;
    private int $debugLevel;
    private array $browserLevels;
    private bool $enabled;
    private bool $headerPrinted = false;

    public function __construct(
        string $logFile = self::LOG_FILE,
        bool $displayInBrowser = true,
        int $debugLevel = 1,
        bool $enabled = true,
    ) {
        $this->logFile = $logFile;
        $this->displayInBrowser = $displayInBrowser;
        $this->debugLevel = $debugLevel ?? (int) ($_ENV['DEBUG'] ?? 0);
        $this->enabled = $enabled;
        $this->browserLevels = $this->debugLevel === 1
            ? [LogLevel::DEBUG, LogLevel::INFO, LogLevel::NOTICE, LogLevel::WARNING]
            : [LogLevel::INFO, LogLevel::NOTICE, LogLevel::WARNING, LogLevel::ERROR];

        if ($this->displayInBrowser && php_sapi_name() !== 'cli') {
            register_shutdown_function([$this, 'closeBrowserContainer']);
        }
    }

    public function log($level, $message, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!in_array($level, (new ReflectionClass(LogLevel::class))->getConstants(), true)) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        $date = date('Y-m-d H:i:s');
        $msg = $this->interpolate((string) $message, $context);
        file_put_contents($this->logFile, "[$date] " . strtoupper($level) . ": $msg\n", FILE_APPEND);

        if ($this->displayInBrowser && in_array($level, $this->browserLevels, true)) {
            $this->displayBrowserLog($level, $msg);
        }
    }

    public function closeBrowserContainer(): void
    {
        if ($this->headerPrinted) {
            echo '</div>';
        }
    }

    public function emergency($m, array $c = []): void
    {
        $this->log(LogLevel::EMERGENCY, $m, $c);
    }

    public function alert($m, array $c = []): void
    {
        $this->log(LogLevel::ALERT, $m, $c);
    }

    public function critical($m, array $c = []): void
    {
        $this->log(LogLevel::CRITICAL, $m, $c);
    }

    public function error($m, array $c = []): void
    {
        $this->log(LogLevel::ERROR, $m, $c);
    }

    public function warning($m, array $c = []): void
    {
        $this->log(LogLevel::WARNING, $m, $c);
    }

    public function notice($m, array $c = []): void
    {
        $this->log(LogLevel::NOTICE, $m, $c);
    }

    public function info($m, array $c = []): void
    {
        $this->log(LogLevel::INFO, $m, $c);
    }

    public function debug($m, array $c = []): void
    {
        $this->log(LogLevel::DEBUG, $m, $c);
    }

    // ... standard PSR-3 level methods go here ...

    private function interpolate(string $message, array $context): string
    {
        foreach ($context as $key => $val) {
            $message = str_replace('{' . $key . '}', (string) $val, $message);
        }
        return $message;
    }

    private function displayBrowserLog(string $level, string $message): void
    {
        if (!$this->headerPrinted) {
            echo "<div style='background:#111;color:#fff;font-family:monospace;padding:5px;'>Browser Log Output</div>";
            $this->headerPrinted = true;
        }
        echo "<div style='color:gray'>[$level] $message</div>";
    }
}