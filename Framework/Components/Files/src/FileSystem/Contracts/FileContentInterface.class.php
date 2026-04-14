<?php

declare(strict_types=1);

interface FileContentInterface
{
    public function read(string $filePath): string;

    public function write(string $filePath, string $content, bool $append = false): void;

    public function getStream(string $filePath, string $mode = 'r');

    public function putStream(string $filePath, $stream): void;
}