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
                throw new ErrorException($error['message'], -1, $error['type'], $error['file'], $error['line']);
            }
        });
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function isMode()
    {
        return YamlFile::get('app')['debug_error'];
    }

    /**
     * Error Handler Convert All errors to exceptions by throwing and error exception
     * ===========================================================.
     * @param int $serverity
     * @param [type] $message
     * @param [type] $file
     * @param [type] $line
     * @return void
     */
    public static function errorHandle(int $serverity, string $message, string $file, int $line)
    {
        if (! error_reporting() && $serverity) {
            return;
        }
        throw new ErrorException($message, 0, $serverity, $file, $line);
    }

    /**
     * Exception handler.
     *
     * @param Exception $exception The exception
     * @return void
     * @throws Exception
     */
    public static function exceptionHandle(Exception $exception) : void
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
        http_response_code($code);
        if (self::isMode()['mode'] == 'dev' && self::isMode()['mode'] != 'prod') {
            list($srcCode, $snippet) = self::srcCode($exception->getFile(), $exception->getLine(), 'highlight');
            $stacktrace = self::$trace;
            App::getInstance()->runError('/_dev_error', [
                'exception' => $exception,
                'snippet' => $snippet,
                'srcCode' => $srcCode,
                'stacktrace' => $stacktrace,
                'code' => $code,
            ]);
        } else {
            $logFile = LOG_DIR . '/error-' . date('Y-m-d') . '-.log';
            ini_set('log_errors', 'On');
            ini_set('error_log', $logFile);
            // $message = 'Uncaught exception: ' . get_class($exception);
            // $message .= 'with message ' . $exception->getMessage();
            // $message .= "\nStack trace: " . $exception->getTraceAsString();
            // $message .= "\nThrown in " . $exception->getFile() . ' on line ' . $exception->getLine();
            $message = static::formatErrorMessage(
                $exception,
                '\033[31m[%s] Error:\033[0m %s: %s in %s on line %d\n'
            );
            error_log($message);
            // error_log($message, 1, $logFile);
            App::getInstance()->runError($route);
        }
    }

    private static function formatErrorMessage(Throwable $exception, string $format) : string
    {
        return sprintf(
            $format,
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    private static function buildStackTrace(Exception $exception) : void
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
        $src = explode(PHP_EOL, file_get_contents($errfile));
        $snippet = $src[$errline - 2] ?? null;
        for ($i = $range[0]; $i <= $range[1]; $i++) {
            if (! isset($src[$i])) {
                return;
            }
            if ($i === count($src)) {
                break;
            }
            if ($i === $errline - 1) {
                $code = sprintf("%d | %s <<<<< Here is the error\n", $i, $src[$i]);
            } else {
                $code = sprintf('%d | %s', $i, $src[$i]);
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
     * @return string
     */
    private static function getSrcCode($errfile, $errline, $errclass)
    {
        $start = max($errline - floor(self::NUM_LINES / 2), 1);
        $start = (int) $start;
        $lines = self::getLines($errfile, $start, self::NUM_LINES, FILE_IGNORE_NEW_LINES);
        $code = '<ul class="uk-list uk-list-divider uk-list-collapse uk-text-bolder" start="' . key($lines) . '">';
        foreach ($lines as $currentLineNumbers => $line) {
            $code .= '<li' . ($currentLineNumbers == $errline - 1 ? ' class="' . $errclass . '"' : '') . '>';
            $code .= $currentLineNumbers + 1 . ' ' . $line;
            $code .= '</li>';
        }
        $code .= '</ul>';

        return $code;
    }

    /**
     * Gets the content between given lines.
     * @param string $filename
     * @param int $offset
     * @param int|null $length
     * @param int $flags
     * @return array
     */
    private static function getLines(string $filename, int $offset, ?int $length, int $flags = 0): array
    {
        return array_slice(file($filename, $flags), $offset, $length, true);
    }
}