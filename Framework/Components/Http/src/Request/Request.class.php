<?php

declare(strict_types=1);

readonly class Request
{
    protected HeaderMap $headers;
    protected QueryHttpMap $query;
    protected CustomHttpMap $post;
    protected CustomHttpMap $server;
    protected CookiesMap $cookies;
    protected FileMap $files;
    protected HttpMethod $method;
    protected string $protocol;
    protected string $requestedUri;
    protected float $requestStartTime;
    protected string|null $rawContent;

    public function __construct(SuperGlobalsInterface $superGlobals)
    {
        $this->server = new CustomHttpMap($superGlobals->server());
        $this->query = new QueryHttpMap($superGlobals->get());
        $this->post = new CustomHttpMap($superGlobals->post());
        $this->cookies = CookiesMap::createFromCookieGlobals($superGlobals->cookies());
        $this->headers = HeaderMap::createFromServerGlobals($superGlobals->server());
        $this->files = new FileMap($superGlobals->files());
        $this->requestStartTime = (float) $this->server->get('request_time_float') ?? 0;
        $this->method = HttpMethod::fromString($this->server->get('request_method'));
        $this->protocol = strtolower($this->server->get('server_protocol'));
        $this->requestedUri = $superGlobals->server('request_uri');
        $rawContent = file_get_contents('php://input');
        $this->rawContent = $rawContent !== false && ! StringUtils::isBlanc($rawContent) ? $rawContent : null;
        //$superGlobals->emptyGlobals();
    }

    public function hasBody() : bool
    {
        return ! $this->post->isEmpty() || ! StringUtils::isBlanc($this->rawContent);
    }

    public function hasFormDataBody() : bool
    {
        if (! $this->hasBody() || ! $this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
            return false;
        }
        $contentType = strtolower($this->headers->getContentType());
        return str_starts_with($contentType, 'multipart/formdata');
    }

    public function hasCookies() : bool
    {
        return $this->cookies->exists();
    }

    public function hasXmlBody() : bool
    {
        if (! $this->hasBody() || ! $this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
            return false;
        }
        $contentType = strtolower($this->headers->getContentType());
        return str_starts_with($contentType, 'text/xml') ||
        str_starts_with($contentType, 'application/xml') ||
        str_ends_with($contentType, '+xml');
    }

    public function hasJsonBody() : bool
    {
        if (! $this->hasBody() || ! $this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
            return false;
        }
        $contentType = strtolower($this->headers->getContentType());
        return str_starts_with($contentType, 'application/json') ||
        str_ends_with($contentType, '+json');
    }

    public function hasFormUrlEncodedBody() : bool
    {
        if (! $this->hasBody() || ! $this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
            return false;
        }
        $contentType = strtolower($this->headers->getContentType());
        return str_starts_with($contentType, 'application/x-www-form-urlencoded');
    }

    public function getHeaders(): HeaderMap
    {
        return $this->headers;
    }

    public function getQuery(): QueryHttpMap
    {
        return $this->query;
    }

    public function getPost(): CustomHttpMap
    {
        return $this->post;
    }

    public function getServer(): CustomHttpMap
    {
        return $this->server;
    }

    public function getCookies(): CookiesMap
    {
        return $this->cookies;
    }

    public function getFiles(): FileMap
    {
        return $this->files;
    }

    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getRequestedUri(): string
    {
        return $this->requestedUri;
    }

    public function getRequestStartTime(): float
    {
        return $this->requestStartTime;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }
}
