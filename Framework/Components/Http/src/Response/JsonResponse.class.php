<?php

declare(strict_types=1);
class JsonResponse extends Response
{
    protected array|object|string|bool $data;
    protected int $encodingOptions;

    public function __construct(
        string|object|array|bool $data = [],
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK,
        array $headers = [],
        array $cookies = [],
        HttpProtocolVersion $protocolVersion = HttpProtocolVersion::HTTP_1_1,
    ) {
        parent::__construct('', $statusCode, $headers, $cookies, $protocolVersion);
        $this->encodingOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $this->setData($data); // Renamed from setDate
    }

    /**
     * @param string|object|array|bool $data
     *
     * @return void
     */
    public function setData(string|object|array|bool $data): void // Renamed from setDate
    {
        $this->data = $data;

        if (is_string($data)) {
            // Assume it's already JSON string
            $this->setContent($data);
            return;
        }

        // ✅ FIX: Proper parameter order - content first, encoding options second
        $jsonContent = json_encode($data, $this->encodingOptions);

        if ($jsonContent === false) {
            // Handle JSON encoding error
            $jsonContent = json_encode([
                'success' => false,
                'error' => 'Failed to encode response data',
                'message' => json_last_error_msg(),
            ]);
        }

        $this->setContent($jsonContent);
    }

    #[Override]
    public function prepare(Request $request): void
    {
        parent::prepare($request);

        // ✅ FIX: Proper content-type header check
        $contentType = $this->headers->get(HeaderMap::CONTENT_TYPE_HEADER);

        if (!$contentType ||
            (!str_starts_with($contentType, 'application/json') &&
             !str_contains($contentType, '+json'))) {
            $this->headers->add(HeaderMap::CONTENT_TYPE_HEADER, 'application/json; charset=utf-8');
        }
    }

    public function setEncodingOptions(int $encodingOptions): self
    {
        $this->encodingOptions = $encodingOptions;
        return $this;
    }

    public function getData(): array|object|string|bool
    {
        return $this->data;
    }
}