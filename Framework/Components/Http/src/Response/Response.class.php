<?php

declare(strict_types=1);

class Response
{
    protected HeaderMap $headers;
    protected CookiesMap $cookies;
    protected string $content;
    protected HttpStatusCode $statusCode;
    protected HttpProtocolVersion $protocolVersion;
    protected ?HeaderManager $headerManager;

    public function __construct(
        string $content = '',
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK,
        array $headers = [],
        array $cookies = [],
        HttpProtocolVersion $protocolVersion = HttpProtocolVersion::HTTP_1_1,
        ?HeaderManager $headerManager = null
    ) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = HeaderMap::createFromArray($headers);
        $this->cookies = new CookiesMap($cookies);
        $this->protocolVersion = $protocolVersion;
        $this->headerManager = $headerManager ?? new HeaderManager(
            $this->headers,
            $_SERVER['HTTP_HOST'] ?? '',
            HttpMethod::fromString($_SERVER['REQUEST_METHOD'] ?? 'GET')
        );
    }

    /**
     * Prepares the response based on the request.
     *
     * @param Request $request
     * @return void
     */
    public function prepare(Request $request) : void
    {
        if ($this->isEmpty()) {
            $this->content = '';
            $this->headers->remove(HeaderMap::CONTENT_TYPE_HEADER);
            $this->headers->remove(HeaderMap::CONTENT_LENGTH_HEADER);

            // prevents php from settng the content-type header automatically
            ini_set('default_mimetype', '');
        }
        if ($this->headers->has('Transfert-Encoding')) {
            $this->headers->remove(HeaderMap::CONTENT_LENGTH_HEADER);
        }
        if ($request->getMethod() === HttpMethod::HEAD) {
            $length = $this->headers->getContentLength() ?? strlen($this->content);
            $this->headers->add(HeaderMap::CONTENT_LENGTH_HEADER, $length);
        }
        // if ($request->hasCookies()) {
        //     $this->getCookies()->addAll($request->getCookies()->all());
        // }
        // /** @var SessionInterface */
        // $session = GlobalManager::get(App::getInstance()->getGlobalSessionKey());
        // if (! $session->exists(PREVIOUS_URL_KEY)) {
        //     $session->set(PREVIOUS_URL_KEY, $request->getRequestedUri());
        // }
        // Apply security headers
        $this->headerManager->applySecurityHeaders($this->headerManager->isDevEnvironment());
        // Special case for API responses
        if (str_starts_with($request->getRequestedUri(), '/api/')) {
            $this->headerManager->applyCorsHeaders();
        }
        if ($request->isFromWebpackDevServer()) {
            $this->headerManager->applyDevHeaders();
        } else {
            $this->headerManager->applySecurityHeaders();
        }
    }

    public function send() : void
    {
        $this->sendCookies();
        $this->sendHeaders();
        $this->sendContent();
    }

    public function isInformationnal() : bool
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    public function isSuccess() : bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirection() : bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function isClientError() : bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError() : bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    public function isEmpty() : bool
    {
        return $this->isInformationnal() ||
        $this->statusCode === HttpStatusCode::HTTP_NO_CONTENT ||
        $this->statusCode === HttpStatusCode::HTTP_NOT_MODIFIED;
    }

    public function setCookie(CookieObject $cookie) : void
    {
        $this->cookies->add($cookie);
    }

    public function setHeader(string $name, string $value) : void
    {
        $this->headers->add($name, $value);
    }

    public function setContentType(string $contentType) : void
    {
        $this->headers->add(HeaderMap::CONTENT_TYPE_HEADER, $contentType);
    }

    public function redirect(string $url) :void
    {
        $this->setHeader('Location', $url);
    }

    /**
     * @return HeaderMap
     */
    public function getHeaders(): HeaderMap
    {
        return $this->headers;
    }

    /**
     * @param HeaderMap $headers
     * @return Response
     */
    public function setHeaders(HeaderMap $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return CookiesMap
     */
    public function getCookies(): CookiesMap
    {
        return $this->cookies;
    }

    /**
     * @param CookiesMap $cookies
     * @return Response
     */
    public function setCookies(CookiesMap $cookies): self
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Response
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return HttpStatusCode
     */
    public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    /**
     * @param HttpStatusCode $statusCode
     * @return Response
     */
    public function setStatusCode(HttpStatusCode $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return HttpProtocolVersion
     */
    public function getProtocolVersion(): HttpProtocolVersion
    {
        return $this->protocolVersion;
    }

    /**
     * @param HttpProtocolVersion $protocolVersion
     * @return Response
     */
    public function setProtocolVersion(HttpProtocolVersion $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    protected function sendContent() : void
    {
        echo $this->content;
    }

    protected function sendHeaders() : void
    {
        if (headers_sent()) {
            return;
        }
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}", true, $this->statusCode->value);
            if ($name === 'Location') {
                exit;
            }
        }
        $statusText = HttpStatusCodeMessages::STATUS_CODE_TO_MESSAGE[$this->statusCode->value];
        header("HTTP/{$this->protocolVersion->value} {$this->statusCode->value} {$statusText}", true, $this->statusCode->value);
    }

    protected function sendCookies() : void
    {
        /**
         * @var CookieObject $cookie
         */
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpires() ?? 0,
                $cookie->getPath() ?? '',
                $cookie->getDomain() ?? '',
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }
}