<?php

declare(strict_types=1);
class JsonResponse extends Response
{
    protected array|object|string $data;
    protected int $encodingOptions;

    public function __construct(
        string|object|array $data = [],
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK,
        array $headers = [],
        array $cookies = [],
        HttpProtocolVersion $protocolVersion = HttpProtocolVersion::HTTP_1_1
    ) {
        parent::__construct('', $statusCode, $headers, $cookies, $protocolVersion);
        $this->encodingOptions = 0;
        $this->setDate($data);
    }

    public function setDate(string|object|array $data) : void
    {
        $this->data = $data;
        if (is_string($data)) {
            $this->setContent($data);
            return;
        }
        $this->setContent(json_encode($data), $this->encodingOptions);
    }

    #[Override]
    public function prepare(Request $request): void
    {
        parent::prepare($request);
        if (! $this->headers->has(HeaderMap::CONTENT_TYPE_HEADER) ||
        ! str_starts_with($this->headers->getContentType(), 'application/json') ||
         ! str_ends_with($this->headers->getContentType(), '+json')
        ) {
            $this->headers->add(HeaderMap::CONTENT_TYPE_HEADER, 'application/json');
        }
    }

    /**
     * Set the value of encodingOptions.
     *
     * @return  self
     */
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = $encodingOptions;
        return $this;
    }
}
