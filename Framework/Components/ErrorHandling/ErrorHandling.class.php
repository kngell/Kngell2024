<?php

declare(strict_types=1);

class ErrorHandling
{
    /**
     * Number of lines to be returned.
     */
    private const int NUM_LINES = 10;

    private static $trace = [];

    public function __construct()
    {
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error) {
                // Only throw an ErrorException if the error type is included in the current error_reporting level
                if (error_reporting() & $error['type']) {
                    throw new ErrorException($error['message'], -1, $error['type'], $error['file'], $error['line']);
                }
            }
        });
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public static function isMode()
    {
        return YamlFile::get('app')['debug_error'];
    }

    /**
     * Error Handler Convert All errors to exceptions by throwing and error exception
     * ===========================================================.
     *
     * @param int $serverity
     * @param [type] $message
     * @param [type] $file
     * @param [type] $line
     *
     * @return void
     */
    public static function errorHandle(int $serverity, string $message, string $file, int $line)
    {
        if (!error_reporting() && $serverity) {
            return;
        }
        throw new ErrorException($message, 0, $serverity, $file, $line);
    }

    /**
     * Exception handler.
     *
     * @param Throwable $exception The exception
     *
     * @throws Exception
     *
     * @return void
     */
    public static function exceptionHandle(Throwable $exception): void
    {
        static::buildStackTrace($exception);

        if ($exception instanceof PageNotFoundException) {
            $code = 404;
            $route = '/_404_error';
        } else {
            $code = $exception->getCode();
            // Ensure code is a valid HTTP status code (100-599)
            if ($code < 100 || $code > 599) {
                $code = 500;
            }
            // Use 4xx for client errors, 5xx for server errors
            $route = ($code >= 400 && $code < 500) ? '/_client_error' : '/_500_error';
        }
        http_response_code((int) $code);
        try {
            $appInstance = App::getInstance();

            if (method_exists($appInstance, 'isFullyBooted') && !$appInstance->isFullyBooted()) {
                $appInstance->reBoot();
            }

            if (self::isMode()['mode'] === 'dev' && self::isMode()['mode'] !== 'prod') {
                list($srcCode, $snippet) = self::srcCode($exception->getFile(), $exception->getLine(), 'highlight');
                $stacktrace = self::$trace;

                $appInstance->runError('/_dev_error', [
                    'exception' => $exception,
                    'snippet' => $snippet,
                    'srcCode' => $srcCode,
                    'stacktrace' => $stacktrace,
                    'code' => (int) $code,
                ]);
            } else {
                $logFile = LOG_DIR . '/error-' . date('Y-m-d') . '.log';
                ini_set('log_errors', 'On');
                ini_set('error_log', $logFile);

                $message = static::formatErrorMessageForLog($exception);
                error_log($message);

                $appInstance->runError($route);
            }
        } catch (Throwable $t) {
            error_log(static::formatErrorMessageForLog($exception));

            error_log('Critical failure in ErrorHandling::exceptionHandle: ' . $t->getMessage());

            if (self::isMode()['mode'] === 'dev') {
                echo '<!DOCTYPE html><html><head><title>Fatal Error</title></head>';
                echo '<body style="font-family: sans-serif; background: #fff; color: #333; margin: 0; padding: 20px;">';
                echo '<h1 style="color: #c00;">A critical error occurred while displaying the error page!</h1>';
                echo '<p>The application instance failed to load the error route.</p>';
                echo '<hr>';
                echo '<h2 style="font-size: 1.2em;">Error during Error Handling (Failure to route to /_dev_error):</h2>';
                echo '<pre style="background: #f8f8f8; border: 1px solid #ddd; padding: 15px; overflow-x: auto;">';
                echo '<strong>Type:</strong> ' . get_class($t) . "\n";
                echo '<strong>Message:</strong> ' . htmlspecialchars($t->getMessage()) . "\n";
                echo '<strong>File:</strong> ' . $t->getFile() . ' on line ' . $t->getLine() . "\n";
                echo '<strong>Stack Trace:</strong>' . "\n" . htmlspecialchars($t->getTraceAsString());
                echo '</pre>';
                echo '<hr>';

                echo '<h2 style="font-size: 1.2em;">Original Exception:</h2>';
                echo '<pre style="background: #f8f8f8; border: 1px solid #ddd; padding: 15px; overflow-x: auto;">';
                echo '<strong>Type:</strong> ' . get_class($exception) . "\n";
                echo '<strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . "\n";
                echo '<strong>File:</strong> ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n";
                echo '<strong>Stack Trace:</strong>' . "\n" . htmlspecialchars($exception->getTraceAsString());
                echo '</pre>';
                echo '</body></html>';
            } else {
                echo 'An unexpected server error occurred.';
            }
        }
    }

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

        // Add stack trace for more context
        $message .= "Stack trace:\n" . $exception->getTraceAsString() . "\n";
        $message .= str_repeat('-', 80) . "\n";

        return $message;
    }

    private static function buildStackTrace(Throwable $exception): void
    {
        static::$trace[] = [
            'file' => $exception->getFile(),
            'code' => self::getSrcCode($exception->getFile(), $exception->getLine(), 'error-line'),
        ];
        foreach ($exception->getTrace() as $item) {
            if (isset($item['class']) && $item['class'] == __CLASS__) {
                continue;
            }
            if (isset($item['file'])) {
                self::$trace[] = [
                    'file' => $item['file'],
                    'code' => self::getSrcCode($item['file'], $item['line'], 'switch-line'),
                ];
            }
        }
    }

    private static function srcCode($errfile, $errline)
    {
        $range = [$errline - 5, $errline + 10];
        $src = @explode(PHP_EOL, @file_get_contents($errfile) ?: '');
        $snippet = $src[$errline - 2] ?? null;
        $code = '';

        for ($i = $range[0]; $i <= $range[1]; $i++) {
            if (!isset($src[$i])) {
                continue;
            }
            if ($i === count($src)) {
                break;
            }
            if ($i === $errline - 1) {
                $code .= sprintf("%d | %s <<<<< Here is the error\n", $i + 1, $src[$i]);
            } else {
                $code .= sprintf('%d | %s', $i + 1, $src[$i]);
            }
        }

        return [
            $code,
            $snippet,
        ];
    }

    /**
     * @param $errfile
     * @param $errline
     * @param $errclass
     *
     * @return string
     */
    private static function getSrcCode($errfile, $errline, $errclass)
    {
        $start = max($errline - floor(self::NUM_LINES / 2), 1);
        $start = (int) $start;
        $lines = self::getLines($errfile, $start, self::NUM_LINES, FILE_IGNORE_NEW_LINES);
        $code = '<ul class="uk-list uk-list-divider uk-list-collapse uk-text-bolder" start="' . key($lines) . '">';
        foreach ($lines as $currentLineNumbers => $line) {
            $code .= '<li' . (($currentLineNumbers + 1) == $errline ? ' class="' . $errclass . '"' : '') . '>';
            $code .= $currentLineNumbers + 1 . ' ' . htmlspecialchars($line);
            $code .= '</li>';
        }
        $code .= '</ul>';

        return $code;
    }

    /**
     * Gets the content between given lines.
     *
     * @param string $filename
     * @param int $offset
     * @param int|null $length
     * @param int $flags
     *
     * @return array
     */
    private static function getLines(string $filename, int $offset, ?int $length, int $flags = 0): array
    {
        // Use @ to suppress file access errors if the file doesn't exist (common in stack traces)
        $fileContents = @file($filename, $flags);
        if ($fileContents === false) {
            return [];
        }
        // Offset is 1-based line number, but array_slice expects 0-based index
        return array_slice($fileContents, $offset - 1, $length, true);
    }
}