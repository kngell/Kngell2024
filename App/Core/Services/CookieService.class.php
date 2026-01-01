<?php

declare(strict_types=1);

class CookieService implements CookieServiceInterface
{
    private CookieInterface $appCookie;
    private CookiesMap $httpCookies;
    private Response $response;

    public function __construct(
        CookieInterface $appCookie,
        Request $request,
        Response $response,
    ) {
        $this->appCookie = $appCookie;
        $this->httpCookies = $request->getCookies();
        $this->response = $response;
    }

    /**
     * Get cookie from either HTTP or Application layer.
     */
    public function get(string $name): mixed
    {
        // First try HTTP cookies (from request)
        $httpCookie = $this->httpCookies->get($name);
        if ($httpCookie !== null) {
            return $httpCookie->getValue();
        }

        // Fall back to application cookies
        return $this->appCookie->get($name);
    }

    /**
     * Set cookie in both HTTP and Application layers.
     */
    public function set(string $name, mixed $value, array $options = []): void
    {
        // Set in application layer
        $this->appCookie->set($value, $name);

        // Also set in HTTP response
        $cookieObject = $this->createCookieObject($name, $value, $options);
        $this->response->getCookies()->add($cookieObject);
    }

    /**
     * Delete cookie from both layers.
     */
    public function delete(string $name): void
    {
        // Delete from application layer
        $this->appCookie->delete($name);

        // Also delete from HTTP response (set expired)
        $cookieObject = new CookieObject(
            $name,
            '',
            time() - 3600,
            '/',
            null,
            false,
            false,
            null,
        );
        $this->response->getCookies()->add($cookieObject);
    }

    /**
     * Check if cookie exists in either layer.
     */
    public function has(string $name): bool
    {
        return $this->httpCookies->get($name) !== null || $this->appCookie->exists($name);
    }

    /**
     * Get all cookies from both layers.
     */
    public function getAll(): array
    {
        $cookies = [];

        // Get HTTP cookies
        foreach ($this->httpCookies->all() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        // Get application cookies (limited to known names)
        $appCookieNames = ['user_region', 'user_locale', 'currency_preference'];
        foreach ($appCookieNames as $name) {
            if ($this->appCookie->exists($name)) {
                $cookies[$name] = $this->appCookie->get($name);
            }
        }

        return $cookies;
    }

    /**
     * Clear all cookies (use with caution!).
     */
    public function clearAll(): void
    {
        // Clear HTTP cookies
        foreach ($this->httpCookies->all() as $cookie) {
            $this->delete($cookie->getName());
        }

        // Clear application cookies
        $this->appCookie->invalidate();
    }

    private function createCookieObject(string $name, mixed $value, array $options = []): CookieObject
    {
        return new CookieObject(
            $name,
            (string) $value,
            $options['expires'] ?? time() + 2592000, // 30 days default
            $options['path'] ?? '/',
            $options['domain'] ?? null,
            $options['secure'] ?? false,
            $options['httponly'] ?? true,
            $options['sameSite'] ?? SameSite::LAX,
        );
    }
}