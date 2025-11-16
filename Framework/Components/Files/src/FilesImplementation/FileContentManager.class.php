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

    public function write(string $filePath, string $content, bool $append = false): void
    {
        $flags = $append ? FILE_APPEND : 0;
        $result = file_put_contents($filePath, $content, $flags);

        if ($result === false) {
            throw new FileException("Cannot write to file: {$filePath}");
        }

        chmod($filePath, 0644);
    }

    public function getStream(string $filePath, string $mode = 'r')
    {
        $stream = fopen($filePath, $mode);
        if ($stream === false) {
            throw new FileException("Cannot open file stream: {$filePath}");
        }
        return $stream;
    }

    public function putStream(string $filePath, $stream): void
    {
        $targetStream = $this->getStream($filePath, 'w');
        stream_copy_to_stream($stream, $targetStream);
        fclose($targetStream);
    }
}