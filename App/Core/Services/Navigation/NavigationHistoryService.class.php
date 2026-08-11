<?php

declare(strict_types=1);

class NavigationHistoryService
{
    private const string PREVIOUS_URL_KEY = 'previous_url';
    private const string CURRENT_URL_KEY = 'current_url';
    private const string INVALID_URL_KEY = 'invalid_redirect_url';
    private const string INTENDED_URL_KEY = 'intended_url';

    private const array EXCLUDE_METHODS = [
        HttpMethod::POST,
        HttpMethod::PUT,
        HttpMethod::PATCH,
        HttpMethod::DELETE,
    ];

    public function __construct(
        private readonly SessionInterface $session,
        private array $previousUrlIgnore,
        private array $safeRedirectExclude,
    ) {
    }

    /**
     * Track the current URL for future redirects.
     */
    public function trackCurrentUrl(string $uri, HttpMethod $method): void
    {
        if (!$this->shouldTrackUrl($uri, $method)) {
            return;
        }

        $this->updateUrlHistory($uri);
    }

    /**
     * Get the best redirect URL (acts as a smart referer).
     */
    public function getRedirectUrl(): string
    {
        // 1. Check for intended URL
        $intendedUrl = $this->session->get(self::INTENDED_URL_KEY);
        if ($intendedUrl && $this->isSafeRedirectUrl($intendedUrl)) {
            $this->session->delete(self::INTENDED_URL_KEY);
            return $intendedUrl;
        }

        // 2. Get current and previous URLs
        $currentUrl = $this->session->get(self::CURRENT_URL_KEY);
        $previousUrl = $this->session->get(self::PREVIOUS_URL_KEY);
        $invalidUrl = $this->session->get(self::INVALID_URL_KEY);

        // 3. Clean up unsafe URLs
        $this->cleanupSessionUrls();

        // 4. Determine safest redirect
        return $this->determineSafeRedirectUrl($currentUrl, $previousUrl, $invalidUrl);
    }

    /**
     * Check if a URL is safe to redirect to
     * Uses safe_redirect_exclude from config.
     */
    public function isSafeRedirectUrl(string $url): bool
    {
        foreach ($this->safeRedirectExclude as $excludedPath) {
            if (str_starts_with($url, $excludedPath)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the previous URL (the actual referer).
     */
    public function getPreviousUrl(): ?string
    {
        return $this->session->get(self::PREVIOUS_URL_KEY);
    }

    /**
     * Get the current URL.
     */
    public function getCurrentUrl(): ?string
    {
        return $this->session->get(self::CURRENT_URL_KEY);
    }

    /**
     * Set an intended URL (for post-login redirects, etc.).
     */
    public function setIntendedUrl(string $url): void
    {
        if ($this->isSafeRedirectUrl($url)) {
            $this->session->set(self::INTENDED_URL_KEY, $url);
        }
    }

    /**
     * Get intended URL without consuming it.
     */
    public function getIntendedUrl(): ?string
    {
        return $this->session->get(self::INTENDED_URL_KEY);
    }

    /**
     * Clear intended URL.
     */
    public function clearIntendedUrl(): void
    {
        $this->session->delete(self::INTENDED_URL_KEY);
    }

    /**
     * Mark current URL as invalid (prevents redirect loops).
     */
    public function markCurrentUrlAsInvalid(): void
    {
        $currentUrl = $this->session->get(self::CURRENT_URL_KEY);
        if ($currentUrl) {
            $this->session->set(self::INVALID_URL_KEY, $currentUrl);
        }
    }

    /**
     * Clear invalid URL marker.
     */
    public function clearInvalidUrl(): void
    {
        $this->session->delete(self::INVALID_URL_KEY);
    }

    /**
     * Clear all navigation history.
     */
    public function clearNavigationHistory(): void
    {
        $this->session->delete(self::CURRENT_URL_KEY);
        $this->session->delete(self::PREVIOUS_URL_KEY);
        $this->session->delete(self::INVALID_URL_KEY);
    }

    // ─── Private Methods ─────────────────────────────────────────

    private function updateUrlHistory(string $currentUri): void
    {
        $currentUrlInSession = $this->session->get(self::CURRENT_URL_KEY);

        if ($currentUrlInSession && $currentUrlInSession !== $currentUri) {
            $this->session->set(self::PREVIOUS_URL_KEY, $currentUrlInSession);
        }

        $this->session->set(self::CURRENT_URL_KEY, $currentUri);
    }

    /**
     * Determine if a URL should be tracked
     * Uses previous_url_ignore from config.
     */
    private function shouldTrackUrl(string $uri, HttpMethod $method): bool
    {
        // Skip state-changing methods
        if (in_array($method, self::EXCLUDE_METHODS, true)) {
            return false;
        }

        // Skip URLs in previous_url_ignore list
        foreach ($this->previousUrlIgnore as $ignoredPath) {
            if (str_starts_with($uri, $ignoredPath)) {
                return false;
            }
        }

        return true;
    }

    private function determineSafeRedirectUrl(?string $currentUrl, ?string $previousUrl, ?string $invalidUrl): string
    {
        $safeUrls = [];

        // Current URL is safe and not invalid
        if ($currentUrl && $currentUrl !== $invalidUrl && $this->isSafeRedirectUrl($currentUrl)) {
            $safeUrls[] = $currentUrl;
        }

        // Previous URL is safe and not invalid
        if ($previousUrl && $previousUrl !== $invalidUrl && $this->isSafeRedirectUrl($previousUrl)) {
            $safeUrls[] = $previousUrl;
        }

        // Clean up invalid URL marker
        if ($invalidUrl) {
            $this->session->delete(self::INVALID_URL_KEY);
        }

        return !empty($safeUrls) ? $safeUrls[0] : '/';
    }

    private function cleanupSessionUrls(): void
    {
        $currentUrl = $this->session->get(self::CURRENT_URL_KEY);
        $previousUrl = $this->session->get(self::PREVIOUS_URL_KEY);

        // Remove unsafe URLs from session
        if ($currentUrl && !$this->isSafeRedirectUrl($currentUrl)) {
            $this->session->delete(self::CURRENT_URL_KEY);
        }

        if ($previousUrl && !$this->isSafeRedirectUrl($previousUrl)) {
            $this->session->delete(self::PREVIOUS_URL_KEY);
        }

        // If current and previous are the same, clear previous
        if ($currentUrl === $previousUrl) {
            $this->session->delete(self::PREVIOUS_URL_KEY);
        }

        // Clean up invalid URL
        $invalidUrl = $this->session->get(self::INVALID_URL_KEY);
        if ($invalidUrl && !$this->isSafeRedirectUrl($invalidUrl)) {
            $this->session->delete(self::INVALID_URL_KEY);
        }
    }
}