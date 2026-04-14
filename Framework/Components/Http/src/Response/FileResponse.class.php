<?php

declare(strict_types=1);
// src/Http/FileResponse.php
class FileResponse extends Response
{
    public function __construct(
        string $filePath,
        ?string $downloadName = null,
        bool $inline = false,
    ) {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        parent::__construct(
            file_get_contents($filePath),
            HttpStatusCode::HTTP_OK,
            [
                'Content-Type' => mime_content_type($filePath),
                'Content-Length' => filesize($filePath),
                'Cache-Control' => 'public, max-age=31536000',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            ],
        );

        $disposition = $inline ? 'inline' : 'attachment';
        if ($downloadName) {
            $this->headers->add(
                'Content-Disposition',
                sprintf('%s; filename="%s"', $disposition, $downloadName),
            );
        }
    }
}