<?php

declare(strict_types=1);
class FileContentManager implements FileContentInterface
{
    public function read(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new FileException("File does not exist: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new FileException("Cannot read file: {$filePath}");
        }
        return $content;
    }

    public function requirePhp(string $filePath, string $icon = ''): mixed
    {
        if (!file_exists($filePath)) {
            throw new FileException("File does not exist: {$filePath}");
        }

        ob_start();

        try {
            $result = require $filePath;
            $output = ob_get_clean();
            return $result !== 1 ? $result : $output;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new FileException(
                "Failed to require PHP file: {$filePath}. Error: " . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    public function executePhpFile(string $filePath, string $icon = ''): mixed
    {
        if (!file_exists($filePath)) {
            throw new FileException("File does not exist: {$filePath}");
        }

        // Isolate execution in a closure to prevent variable pollution
        $executor = function ($path) {
            return include $path;
        };

        try {
            return $executor($filePath);
        } catch (Throwable $e) {
            throw new FileException(
                "Failed to execute PHP file: {$filePath}. Error: " . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    public function includePhp(string $filePath): mixed
    {
        if (!file_exists($filePath)) {
            throw new FileException("File does not exist: {$filePath}");
        }

        // Start output buffering to capture any output
        ob_start();

        try {
            // Include the file - this executes the PHP code
            $result = include $filePath;

            // Get any output that was generated
            $output = ob_get_clean();

            // If the file returns something meaningful, return that
            // Otherwise return any captured output
            return $result !== 1 ? $result : $output;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new FileException(
                "Failed to include PHP file: {$filePath}. Error: " . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    public function write(string $filePath, string $content, bool $append = false): void
    {
        $flags = $append ? FILE_APPEND : 0;
        $result = file_put_contents($filePath, $content, $flags);

        if ($result === false) {
            throw new FileException("Cannot write to file: {$filePath}");
        }
    }

    public function getStream(string $filePath, string $mode = 'r')
    {
        $stream = fopen($filePath, $mode);
        if ($stream === false) {
            throw new FileException("Cannot open file stream: {$filePath}");
        }
        return $stream;
    }

    public function putStream(string $filePath, mixed $stream): void
    {
        $targetStream = $this->getStream($filePath, 'w');
        stream_copy_to_stream($stream, $targetStream);
        fclose($targetStream);
    }
}