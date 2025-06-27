<?php

declare(strict_types=1);

// src/Http/FileResponse.php
class FileResponse extends Response
{
    public function __construct(
        string $filePath,
        ?string $downloadName = null
    ) {
        parent::__construct(
            file_get_contents($filePath),
            HttpStatusCode::HTTP_OK,
            [
                'Content-Type' => mime_content_type($filePath),
                'Content-Length' => filesize($filePath),
            ]
        );

        if ($downloadName) {
            $this->headers->add(
                'Content-Disposition',
                sprintf('attachment; filename="%s"', $downloadName)
            );
        }

        $this->headerManager->applyFileDownloadHeaders();
    }
}