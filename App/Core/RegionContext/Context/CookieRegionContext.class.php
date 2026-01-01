<?php

declare(strict_types=1);

class CookieRegionContext extends AbstractRegionContext
{
    private const COOKIE_NAME = 'user_region';
    private const COOKIE_LOCALE = 'user_locale';
    private const COOKIE_CURRENCY = 'user_currency';
    private const COOKIE_EXPIRE = 2592000; // 30 days

    public function __construct(
        private CookieServiceInterface $cookieService,
        private Request $request,
        private Response $response,
    ) {
    }

    public function resolveRegion(): ?string
    {
        // First try cookie
        $region = $this->cookieService->get(self::COOKIE_NAME);
        if ($region && is_string($region)) {
            return strtoupper(trim($region));
        }

        return null;
    }

    public function setRegion(string $region): void
    {
        $region = strtoupper(trim($region));

        // Set in cookie with long expiration
        $this->cookieService->set(self::COOKIE_NAME, $region, [
            'expires' => time() + self::COOKIE_EXPIRE,
            'httponly' => true,
            'sameSite' => SameSite::LAX,
        ]);

        // Also set as HTTP-only cookie for immediate use
        $this->setHttpCookie(self::COOKIE_NAME, $region);
    }

    public function setLocale(string $locale): void
    {
        $this->cookieService->set(self::COOKIE_LOCALE, $locale, [
            'expires' => time() + self::COOKIE_EXPIRE,
            'httponly' => true,
            'sameSite' => SameSite::LAX,
        ]);
    }

    public function setCurrency(string $currencyCode): void
    {
        $this->cookieService->set(self::COOKIE_CURRENCY, $currencyCode, [
            'expires' => time() + self::COOKIE_EXPIRE,
            'httponly' => true,
            'sameSite' => SameSite::LAX,
        ]);
    }

    public function getLocale(): ?string
    {
        $locale = $this->cookieService->get(self::COOKIE_LOCALE);
        return $locale && is_string($locale) ? $locale : null;
    }

    public function getCurrency(): ?string
    {
        $currency = $this->cookieService->get(self::COOKIE_CURRENCY);
        return $currency && is_string($currency) ? strtoupper($currency) : null;
    }

    public function clearRegion(): void
    {
        $this->cookieService->delete(self::COOKIE_NAME);
        $this->cookieService->delete(self::COOKIE_LOCALE);
        $this->cookieService->delete(self::COOKIE_CURRENCY);
    }

    public function getPriority(): int
    {
        return 85; // Between session (70) and explicit headers/query (90+)
    }

    /**
     * Check if user has explicitly set their region via cookie.
     */
    public function hasExplicitRegion(): bool
    {
        return $this->cookieService->has(self::COOKIE_NAME);
    }

    /**
     * Get all region-related cookie data.
     */
    public function getRegionData(): ?array
    {
        if (!$this->hasExplicitRegion()) {
            return null;
        }

        return [
            'region' => $this->resolveRegion(),
            'locale' => $this->getLocale(),
            'currency' => $this->getCurrency(),
            'source' => 'cookie',
            'expires' => $this->getCookieExpiration(self::COOKIE_NAME),
        ];
    }

    /**
     * Migrate old cookie format to new format.
     */
    public function migrateOldCookies(): void
    {
        $oldNames = ['region', 'preferred_region', 'userRegion', 'user-region'];

        foreach ($oldNames as $oldName) {
            if ($this->cookieService->has($oldName)) {
                $region = $this->cookieService->get($oldName);
                if ($region && is_string($region)) {
                    $this->setRegion($region);
                    $this->cookieService->delete($oldName); // Remove old cookie
                }
            }
        }
    }

    public function providesExplicitChoice(): bool
    {
        return true; // Cookies represent explicit user choice
    }

    public function getName(): string
    {
        return 'cookie';
    }

    /**
     * Set cookie directly in HTTP response (for immediate use in same request).
     */
    private function setHttpCookie(string $name, string $value): void
    {
        $cookieObject = new CookieObject(
            $name,
            $value,
            time() + self::COOKIE_EXPIRE,
            '/',
            null,
            $this->request->getServer()->get('HTTPS') === 'on',
            true,
            SameSite::LAX,
        );

        $this->response->getCookies()->add($cookieObject);
    }

    /**
     * Get cookie expiration timestamp.
     */
    private function getCookieExpiration(string $name): ?int
    {
        $cookie = $this->request->getCookies()->get($name);
        return $cookie?->getExpires();
    }
}