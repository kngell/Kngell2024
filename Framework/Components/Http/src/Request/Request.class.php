<?php

declare(strict_types=1);

class Request extends SuperGlobals
{
    protected HeaderMap $headers;
    protected Map $query;
    protected Map $post;
    protected Map $server;
    protected CookiesMap $cookies;
    protected FileMap $files;
    protected HttpMethod $method;
    protected string $protocol;
    protected string $requestedUri;
    protected float $requestStartTime;
    protected string|null $rawContent;

    public function __construct()
    {
        parent::__construct();
        $this->server = new Map($this->_Server());
        $this->query = new Map($this->_Get());
        $this->post = new Map($this->_Post());
        $this->cookies = CookiesMap::createFromCookieGlobals($this->_Cookies());
        $this->headers = HeaderMap::createFromServerGlobals($this->_Server());
        $this->files = new FileMap($this->_Files());
        $this->requestStartTime = (float) $this->server->get('REQUEST_TIME_FLOAT') ?? 0;
        $this->method = HttpMethod::fromString($this->server->get('REQUEST_METHOD'));
        $this->protocol = strtolower($this->server->get('SERVER_PROTOCOL'));
        $this->requestedUri = $this->url();

        $rawContent = file_get_contents('php://input');
        $this->rawContent = $rawContent !== false && ! StringUtils::isBlanc($rawContent) ? $rawContent : null;

        $this->emptyGlobals();
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

    public function getQuery(): Map
    {
        return $this->query;
    }

    public function getPost(): Map
    {
        return $this->post;
    }

    public function getServer(): Map
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

    private function url() : string
    {
        return $this->_server('request_uri');
    }
}