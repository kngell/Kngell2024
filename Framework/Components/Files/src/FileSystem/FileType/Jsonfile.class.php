<?php

declare(strict_types=1);

class JsonFile implements ContentSourceInterface
{
    private ?array $parsedContent = null;
    private FileContentInterface $fileContentManager;

    public function __construct(
        private string $source,
        ?FileContentInterface $fileContentManager = null,
    ) {
        $this->fileContentManager = $fileContentManager ?? new FileContentManager();
    }

    public function getContent(): string
    {
        if (filter_var($this->source, FILTER_VALIDATE_URL)) {
            return $this->getContentFromUrl();
        }

        if (file_exists($this->source)) {
            return $this->getContentFromFile();
        }

        // Assume it's a JSON string
        return $this->validateJsonString($this->source);
    }

    public function getContentAsArray(): array
    {
        if ($this->parsedContent === null) {
            $content = $this->getContent();
            $this->parsedContent = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->parsedContent;
    }

    public function clearCache(): void
    {
        $this->parsedContent = null;
    }

    private function getContentFromFile(): string
    {
        return $this->fileContentManager->read($this->source);
    }

    private function getContentFromUrl(): string
    {
        // FileContentManager could be extended to handle URLs
        // or we could create a separate UrlContentManager
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => 'Accept: application/json',
            ],
        ]);

        $content = file_get_contents($this->source, false, $context);
        if ($content === false) {
            throw new RuntimeException("Failed to fetch URL: {$this->source}");
        }

        return $content;
    }

    private function validateJsonString(string $jsonString): string
    {
        json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                'Invalid JSON string: ' . json_last_error_msg(),
            );
        }

        return $jsonString;
    }
}