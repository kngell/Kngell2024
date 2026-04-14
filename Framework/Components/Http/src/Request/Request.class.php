<?php

declare(strict_types=1);

final readonly class Request
{
    protected HeaderMap $headers;
    protected QueryHttpMap $query;
    protected CustomHttpMap $post;
    protected CustomHttpMap $server;
    protected CookiesMap $cookies;
    protected FileUploadMap $files;
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
        $this->files = new FileUploadMap($superGlobals->files());
        $this->requestStartTime = (float) $this->server->get('request_time_float') ?? 0;

        $requestMethod = $this->server->get('request_method');

        // Handle false or invalid values
        if ($requestMethod === false || !is_string($requestMethod)) {
            $requestMethod = 'GET'; // Default to GET
        }

        $this->method = HttpMethod::fromString($requestMethod);

        $this->protocol = strtolower($this->server->get('server_protocol') ?? 'HTTP/1.1');
        $this->requestedUri = $superGlobals->server('request_uri') ?? '/';
        $rawContent = file_get_contents('php://input');
        $this->rawContent = $rawContent !== false && !StringUtils::isBlank($rawContent) ? $rawContent : null;
        $superGlobals->emptyGlobals();
    }

    public function hasBody(): bool
    {
        return !$this->post->isEmpty() || !StringUtils::isBlank($this->rawContent);
    }

    public function hasFormDataBody(): bool
    {
        if (!$this->hasBody() || !$this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
            return false;
        }
        $contentType = strtolower($this->headers->getContentType());
        return str_starts_with($contentType, 'multipart/form-data');
    }

    public function hasCookies(): bool
    {
        return $this->cookies->exists();
    }

    public function hasXmlBody(): bool
    {
        if (!$this->hasBody() || !$this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
            return false;
        }
        $contentType = strtolower($this->headers->getContentType());
        return str_starts_with($contentType, 'text/xml') ||
        str_starts_with($contentType, 'application/xml') ||
        str_ends_with($contentType, '+xml');
    }

    public function hasJsonBody(): bool
    {
        if (!$this->hasBody() || !$this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
            return false;
        }
        $contentType = strtolower($this->headers->getContentType());
        return str_starts_with($contentType, 'application/json') ||
        str_ends_with($contentType, '+json');
    }

    public function hasFormUrlEncodedBody(): bool
    {
        if (!$this->hasBody() || !$this->headers->has(HeaderMap::CONTENT_TYPE_HEADER)) {
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

    public function getFiles(): FileUploadMap
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

    public function getPathFromUri(): string
    {
        $parts = parse_url($this->getRequestedUri());

        return $parts['path'] ?? '/';
    }

    public function getRequestStartTime(): float
    {
        return $this->requestStartTime;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    public function isGet(): bool
    {
        return $this->getMethod() === HttpMethod::GET;
    }

    public function isPost(): bool
    {
        return $this->getMethod() === HttpMethod::POST;
    }

    public function get(string $key): mixed
    {
        if ($this->getServer()->get($key)) {
            return $this->getServer()->get($key);
        }
        if ($this->getQuery()->get($key)) {
            return $this->getQuery()->get($key);
        }
        if ($this->getPost()->get($key)) {
            return $this->getPost()->get($key);
        }
        if ($this->getCookies()->get($key)) {
            return $this->getCookies()->get($key);
        }
        $key = ucfirst(strtolower($key));
        if ($this->getHeaders()->get($key)) {
            return $this->getCookies()->get($key);
        }

        return null;
    }

    public function isFromWebpackDevServer(): bool
    {
        $origin = $this->headers->get('Origin');
        $host = $this->headers->get('Host');
        $devHosts = ['localhost', '127.0.0.1', 'localhost:3003', '127.0.0.1:3003'];
        foreach ($devHosts as $devHost) {
            if (($origin && str_contains($origin, $devHost)) || ($host && str_contains($host, $devHost))) {
                return true;
            }
        }
        return false;
    }

    public function getClientIp(): string
    {
        $possibleHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        ];

        foreach ($possibleHeaders as $header) {
            $ip = $this->server->get($header);
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                if ($header === 'HTTP_X_FORWARDED_FOR' && str_contains($ip, ',')) {
                    $ips = explode(',', $ip);
                    return trim($ips[0]);
                }
                return $ip;
            }
        }

        return '127.0.0.1';
    }

    public function hasRegionParameter(): bool
    {
        return $this->getQuery()->has('region') || $this->getPost()->has('region');
    }

    public function getPreferredLanguage(): string
    {
        if (!$this->headers->has('Accept-Language')) {
            return 'en';
        }

        $acceptLanguage = $this->headers->get('Accept-Language');
        $locales = explode(',', (string) $acceptLanguage);
        $primaryLocale = trim($locales[0]);

        // Extract language code (e.g., "en-US" -> "en")
        if (str_contains($primaryLocale, '-')) {
            return strtolower(explode('-', $primaryLocale)[0]);
        }

        return strtolower($primaryLocale);
    }

    public function getRegion(): ?string
    {
        // 1. Check explicit region parameter (highest priority)
        $region = $this->getQuery()->get('region') ?? $this->getPost()->get('region');
        if ($region && is_string($region)) {
            return strtoupper(trim($region));
        }

        // 2. Check Accept-Language header
        $regionFromLanguage = $this->getRegionFromAcceptLanguage();
        if ($regionFromLanguage) {
            return $regionFromLanguage;
        }

        // 3. Check from custom header (e.g., X-Region)
        $regionFromHeader = $this->headers->get('X-Region');
        if ($regionFromHeader && is_string($regionFromHeader)) {
            return strtoupper(trim($regionFromHeader));
        }

        return null;
    }

    public function isAjax(): bool
    {
        // Check for standard AJAX header
        $xRequestedWith = $this->headers->get('X-Requested-With');
        if ($xRequestedWith && strtolower($xRequestedWith) === 'xmlhttprequest') {
            return true;
        }

        // Check Accept header
        $accept = $this->headers->get('Accept');
        if ($accept && str_contains(strtolower($accept), 'application/json')) {
            return true;
        }

        // Check for fetch API (this is what your client uses!)
        $secFetchMode = $this->headers->get('Sec-Fetch-Mode');
        if ($secFetchMode === 'cors') {
            return true;
        }

        // Check for PJAX
        if ($this->headers->get('X-PJAX') || $this->headers->get('X-PJAX-Container')) {
            return true;
        }

        return false;
    }

    public function isXmlHttpRequest(): bool
    {
        return $this->isAjax();
    }

    /**
     * Extract region from Accept-Language header.
     */
    private function getRegionFromAcceptLanguage(): ?string
    {
        if (!$this->headers->has('Accept-Language')) {
            return null;
        }

        $acceptLanguage = $this->headers->get('Accept-Language');
        if (empty($acceptLanguage)) {
            return null;
        }

        // Parse the first language locale (e.g., "en-US,en;q=0.9" -> "US")
        $locales = explode(',', (string) $acceptLanguage);
        $primaryLocale = trim($locales[0]);

        // Extract region from locale (e.g., "en-US" -> "US", "fr-FR" -> "FR")
        if (str_contains($primaryLocale, '-')) {
            $parts = explode('-', $primaryLocale);
            if (isset($parts[1]) && strlen($parts[1]) === 2) {
                return strtoupper($parts[1]);
            }
        }

        return null;
    }
}