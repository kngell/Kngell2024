<?php

declare(strict_types=1);

class ErrorHandling
{
    private const int NUM_LINES = 10;
    private const int MAX_EXCEPTION_DEPTH = 3;

    private static array $trace = [];
    private static bool $handlingException = false;
    private static int $exceptionDepth = 0;

    public function __construct()
    {
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && (error_reporting() & $error['type'])) {
                // Don't throw during exception handling
                if (self::$handlingException) {
                    error_log(sprintf(
                        'Shutdown error during exception handling: %s in %s:%d',
                        $error['message'],
                        $error['file'],
                        $error['line'],
                    ));
                    return;
                }
                throw new ErrorException(
                    $error['message'],
                    -1,
                    $error['type'],
                    $error['file'],
                    $error['line'],
                );
            }
        });
    }

    public static function isMode(): array
    {
        try {
            return YamlFile::get('app')['debug_error'];
        } catch (Throwable) {
            // If we can't even read config, assume dev mode
            // so developers see the error
            return ['mode' => 'dev'];
        }
    }

    public static function errorHandle(
        int $severity,
        string $message,
        string $file,
        int $line,
    ): bool {
        // Respect error suppression operator (@)
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public static function exceptionHandle(Throwable $exception): void
    {
        // GUARD: Prevent recursive exception handling
        self::$exceptionDepth++;
        if (self::$exceptionDepth > self::MAX_EXCEPTION_DEPTH) {
            self::lastResortHandler($exception);
            return;
        }

        // Mark that we're handling an exception
        $previousState = self::$handlingException;
        self::$handlingException = true;

        try {
            static::buildStackTrace($exception);
            $code = self::resolveHttpCode($exception);
            http_response_code($code);

            $mode = self::isMode()['mode'] ?? 'prod';

            if ($mode === 'dev') {
                self::handleDevError($exception, $code);
            } else {
                self::handleProdError($exception, $code);
            }
        } catch (Throwable $t) {
            // The error handler itself failed
            self::handleErrorHandlingFailure($exception, $t);
        } finally {
            self::$handlingException = $previousState;
            self::$exceptionDepth--;
        }
    }

    /**
     * Resolve an appropriate HTTP status code from the exception.
     */
    private static function resolveHttpCode(Throwable $exception): int
    {
        if ($exception instanceof PageNotFoundException) {
            return 404;
        }

        $code = $exception->getCode();

        if (is_int($code) && $code >= 100 && $code <= 599) {
            return $code;
        }

        // Map known exception types to HTTP codes
        return match (true) {
            $exception instanceof InvalidArgumentException => 400,
            $exception instanceof ContainerException => 500,
            $exception instanceof LogicException => 500,
            $exception instanceof RuntimeException => 500,
            default => 500,
        };
    }

    /**
     * Route selection based on exception type.
     */
    private static function resolveErrorRoute(Throwable $exception, int $code): string
    {
        if ($exception instanceof PageNotFoundException) {
            return '/_404_error';
        }
        return ($code >= 400 && $code < 500) ? '/_client_error' : '/_500_error';
    }

    /**
     * TIER 1: Try to render through the full application stack.
     */
    private static function handleDevError(Throwable $exception, int $code): void
    {
        // TIER 1: Try the full app error route
        if (self::$exceptionDepth === 1 && self::tryAppErrorRoute($exception, $code)) {
            return;
        }

        // TIER 2: Render a standalone dev error page (no container needed)
        self::renderStandaloneDevError($exception, $code);
    }

    /**
     * TIER 1: Try rendering through the App's error routing.
     */
    private static function tryAppErrorRoute(
        Throwable $exception,
        int $code,
    ): bool {
        try {
            $appInstance = App::getInstance();

            // Only attempt if the app is fully booted
            // DON'T try to reBoot - that's what likely caused the error
            if (!method_exists($appInstance, 'isFullyBooted')
                || !$appInstance->isFullyBooted()
            ) {
                return false; // Fall through to standalone renderer
            }

            list($srcCode, $snippet) = self::srcCode(
                $exception->getFile(),
                $exception->getLine(),
            );

            $appInstance->runError('/_dev_error', [
                'exception' => $exception,
                'snippet' => $snippet,
                'srcCode' => $srcCode,
                'stacktrace' => self::$trace,
                'code' => $code,
            ]);

            return true;
        } catch (Throwable) {
            return false; // Fall through to standalone renderer
        }
    }

    /**
     * TIER 2: Standalone dev error page — no container, no routing.
     */
    private static function renderStandaloneDevError(
        Throwable $exception,
        int $code,
    ): void {
        // Always log
        error_log(static::formatErrorMessageForLog($exception));

        // Clean any buffered output
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        list($srcCode, $snippet) = self::srcCode(
            $exception->getFile(),
            $exception->getLine(),
        );

        // Render a self-contained error page
        echo '<!DOCTYPE html><html lang="en"><head>';
        echo '<meta charset="UTF-8">';
        echo '<title>' . $code . ' - ' . htmlspecialchars(get_class($exception)) . '</title>';
        echo '<style>';
        echo self::getErrorPageStyles();
        echo '</style></head><body>';

        echo '<div class="error-container">';
        echo '<div class="error-header">';
        echo '<h1>' . htmlspecialchars(get_class($exception)) . '</h1>';
        echo '<span class="error-code">' . $code . '</span>';
        echo '</div>';

       echo '<div class="error-message">';
        echo '<p>' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p class="error-location">';
        echo htmlspecialchars($exception->getFile());
        echo ':<strong>' . $exception->getLine() . '</strong>';
        echo '</p>';
        echo '</div>';

        // Source code context
        if ($srcCode) {
            echo '<div class="source-code">';
            echo '<h2>Source Code</h2>';
            echo '<pre><code>' . htmlspecialchars($srcCode) . '</code></pre>';
            echo '</div>';
        }

        // Container resolution context (if applicable)
        if ($exception instanceof ContainerException) {
            echo '<div class="container-context">';
            echo '<h2>Container Resolution Stack</h2>';
            $stack = $exception->getResolutionStack();
            if (!empty($stack)) {
                echo '<ol>';
                foreach ($stack as $item) {
                    echo '<li>' . htmlspecialchars($item) . '</li>';
                }
                echo '</ol>';
            }
            echo '</div>';
        }

        // Previous exceptions chain
        $prev = $exception->getPrevious();
        if ($prev) {
            echo '<div class="previous-exception">';
            echo '<h2>Caused By</h2>';
            while ($prev) {
                echo '<div class="prev-item">';
                echo '<strong>' . htmlspecialchars(get_class($prev)) . '</strong>: ';
                echo htmlspecialchars($prev->getMessage());
                echo '<br><small>' . htmlspecialchars($prev->getFile());
                echo ':' . $prev->getLine() . '</small>';
                echo '</div>';
                $prev = $prev->getPrevious();
            }
            echo '</div>';
        }

        // Full stack trace
        echo '<div class="stack-trace">';
        echo '<h2>Stack Trace</h2>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        echo '</div>';

        echo '</div></body></html>';
    }

    /**
     * Production error handler — log and show generic message.
     */
    private static function handleProdError(
        Throwable $exception,
        int $code,
    ): void {
        // Always log in production
        $logFile = (defined('LOG_DIR') ? LOG_DIR : sys_get_temp_dir())
            . '/error-' . date('Y-m-d') . '.log';
        ini_set('log_errors', 'On');
        ini_set('error_log', $logFile);
        error_log(static::formatErrorMessageForLog($exception));

        // Try to use the app's error route
        $route = self::resolveErrorRoute($exception, $code);

        try {
            $appInstance = App::getInstance();
            if (method_exists($appInstance, 'isFullyBooted')
                && $appInstance->isFullyBooted()
            ) {
                $appInstance->runError($route);
                return;
            }
        } catch (Throwable) {
            // Fall through to generic message
        }

        // Generic fallback
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        echo '<!DOCTYPE html><html><head><title>Error</title></head>';
        echo '<body style="font-family:sans-serif;text-align:center;padding:50px">';
        echo '<h1>' . $code . '</h1>';
        echo '<p>An unexpected error occurred. Please try again later.</p>';
        echo '</body></html>';
    }

    /**
     * When the error handler itself fails.
     */
    private static function handleErrorHandlingFailure(
        Throwable $original,
        Throwable $handlerError,
    ): void {
        error_log(static::formatErrorMessageForLog($original));
        error_log(
            'Error handler failure: '
            . static::formatErrorMessageForLog($handlerError),
        );

        if ((self::isMode()['mode'] ?? 'prod') === 'dev') {
            self::renderDualErrorPage($original, $handlerError);
        } else {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            echo 'An unexpected server error occurred.';
        }
    }

    /**
     * TIER 3: Absolute last resort — cannot fail.
     */
    private static function lastResortHandler(Throwable $exception): void
    {
        // This method must NEVER throw
        try {
            error_log(sprintf(
                '[CRITICAL] Recursive exception handling detected (depth %d): %s in %s:%d',
                self::$exceptionDepth,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
            ));
        } catch (Throwable) {
            // Even error_log failed, nothing we can do
        }

        // Minimal output
        if (php_sapi_name() !== 'cli') {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/plain');
            }
            echo "A critical server error occurred.\n";
        } else {
            fwrite(STDERR, 'Critical error: ' . $exception->getMessage() . "\n");
        }
    }

    /**
     * Render page showing both original error and handler error.
     */
    private static function renderDualErrorPage(
        Throwable $original,
        Throwable $handlerError,
    ): void {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        echo '<!DOCTYPE html><html><head><title>Fatal Error</title>';
        echo '<style>' . self::getErrorPageStyles() . '</style>';
        echo '</head><body>';
        echo '<div class="error-container">';

        echo '<div class="error-header" style="background:#c00">';
        echo '<h1>Error Handler Failed</h1>';
        echo '</div>';

        echo '<div class="error-message">';
        echo '<h2>Error During Error Handling:</h2>';
        echo '<p><strong>' . htmlspecialchars(get_class($handlerError)) . '</strong>: ';
        echo htmlspecialchars($handlerError->getMessage()) . '</p>';
        echo '<p class="error-location">';
        echo htmlspecialchars($handlerError->getFile()) . ':' . $handlerError->getLine();
        echo '</p>';
        echo '<pre>' . htmlspecialchars($handlerError->getTraceAsString()) . '</pre>';
        echo '</div>';

        echo '<div class="error-message">';
        echo '<h2>Original Exception:</h2>';
        echo '<p><strong>' . htmlspecialchars(get_class($original)) . '</strong>: ';
        echo htmlspecialchars($original->getMessage()) . '</p>';
        echo '<p class="error-location">';
        echo htmlspecialchars($original->getFile()) . ':' . $original->getLine();
        echo '</p>';
        echo '<pre>' . htmlspecialchars($original->getTraceAsString()) . '</pre>';
        echo '</div>';

        echo '</div></body></html>';
    }

    private static function getErrorPageStyles(): string
    {
        return <<<'CSS'
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", 
                             Roboto, monospace; 
                background: #1a1a2e; color: #e0e0e0; padding: 20px;
            }
            .error-container { 
                max-width: 1200px; margin: 0 auto; 
            }
            .error-header { 
                background: #e74c3c; color: white; padding: 20px 30px;
                border-radius: 8px 8px 0 0; display: flex; 
                justify-content: space-between; align-items: center;
            }
            .error-header h1 { font-size: 1.4em; }
            .error-code { 
                font-size: 2em; font-weight: bold; opacity: 0.8; 
            }
            .error-message { 
                background: #16213e; padding: 20px 30px; 
                border-bottom: 1px solid #0f3460; 
            }
            .error-location { 
                color: #888; font-size: 0.9em; margin-top: 8px; 
            }
            .source-code, .stack-trace, .container-context, 
            .previous-exception {
                background: #16213e; padding: 20px 30px; 
                border-bottom: 1px solid #0f3460;
            }
            h2 { 
                color: #e94560; margin-bottom: 15px; font-size: 1.1em; 
            }
            pre { 
                background: #0a0a1a; padding: 15px; border-radius: 4px;
                overflow-x: auto; font-size: 0.85em; line-height: 1.6;
            }
            .prev-item { 
                padding: 10px; border-left: 3px solid #e94560; 
                margin-bottom: 10px; padding-left: 15px; 
            }
            ol { padding-left: 20px; }
            ol li { padding: 4px 0; }
        CSS;
    }

    // Keep existing helper methods...
    private static function formatErrorMessageForLog(Throwable $exception): string
    {
        $message = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        );
        $message .= "Stack trace:\n" . $exception->getTraceAsString() . "\n";
        $message .= str_repeat('-', 80) . "\n";
        return $message;
    }

    private static function buildStackTrace(Throwable $exception): void
    {
        // Reset for new exception
        static::$trace = [];

        static::$trace[] = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => self::getSrcCode(
                $exception->getFile(),
                $exception->getLine(),
                'error-line',
            ),
        ];
        foreach ($exception->getTrace() as $item) {
            if (isset($item['class']) && $item['class'] === self::class) {
                continue;
            }
            if (isset($item['file'])) {
                self::$trace[] = [
                    'file' => $item['file'],
                    'line' => $item['line'] ?? 0,
                    'code' => self::getSrcCode(
                        $item['file'],
                        $item['line'] ?? 0,
                        'switch-line',
                    ),
                ];
            }
        }
    }

    private static function srcCode(string $errfile, int $errline): array
    {
        $range = [$errline - 5, $errline + 10];
        $src = @explode(PHP_EOL, @file_get_contents($errfile) ?: '');
        $snippet = $src[$errline - 2] ?? null;
        $code = '';

        for ($i = $range[0]; $i <= $range[1]; $i++) {
            if (!isset($src[$i]) || $i === count($src)) {
                continue;
            }
            if ($i === $errline - 1) {
                $code .= sprintf(
                    "%d | %s <<<<< Here is the error\n",
                    $i + 1,
                    $src[$i],
                );
            } else {
                $code .= sprintf('%d | %s', $i + 1, $src[$i]);
            }
        }

        return [$code, $snippet];
    }

    private static function getSrcCode(
        string $errfile,
        int $errline,
        string $errclass,
    ): string {
        $start = max((int) ($errline - floor(self::NUM_LINES / 2)), 1);
        $lines = self::getLines($errfile, $start, self::NUM_LINES, FILE_IGNORE_NEW_LINES);
        $code = '<ul class="uk-list uk-list-divider uk-list-collapse '
            . 'uk-text-bolder" start="' . key($lines) . '">';
        foreach ($lines as $currentLineNumbers => $line) {
            $isErrorLine = ($currentLineNumbers + 1) === $errline;
            $code .= '<li' . ($isErrorLine ? ' class="' . $errclass . '"' : '') . '>';
            $code .= ($currentLineNumbers + 1) . ' ' . htmlspecialchars($line);
            $code .= '</li>';
        }
        $code .= '</ul>';
        return $code;
    }

    private static function getLines(
        string $filename,
        int $offset,
        ?int $length,
        int $flags = 0,
    ): array {
        $fileContents = @file($filename, $flags);
        if ($fileContents === false) {
            return [];
        }
        return array_slice($fileContents, $offset - 1, $length, true);
    }
}