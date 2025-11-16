<?php

declare(strict_types=1);

class NavigationHistoryService
{
    private const string PREVIOUS_URL_KEY = 'previous_url';
    private const string CURRENT_URL_KEY = 'current_url';
    private const string INVALID_URL_KEY = 'invalid_redirect_url';

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

    public function trackCurrentUrl(string $uri, HttpMethod $method): void
    {
        if (!$this->shouldTrackUrl($uri, $method)) {
            return;
        }

        $this->updateUrlHistory($uri);
    }

    public function getRedirectUrl(): string
    {
        $currentUrl = $this->session->get(self::CURRENT_URL_KEY);
        $previousUrl = $this->session->get(self::PREVIOUS_URL_KEY);
        $invalidUrl = $this->session->get(self::INVALID_URL_KEY);

        $this->cleanupSessionUrls(); // ✅ IMPORTANT: Clean up before determining redirect

        return $this->determineSafeRedirectUrl($currentUrl, $previousUrl, $invalidUrl);
    }

    /**
     * Mark the current URL as invalid to avoid redirecting back to it.
     */
    public function markCurrentUrlAsInvalid(): void
    {
        $currentUrl = $this->session->get(self::CURRENT_URL_KEY);
        if ($currentUrl) {
            $this->session->set(self::INVALID_URL_KEY, $currentUrl);
        }
    }

    /**
     * Clear any invalid URL markers.
     */
    public function clearInvalidUrl(): void
    {
        $this->session->delete(self::INVALID_URL_KEY);
    }

    public function clearNavigationHistory(): void
    {
        $this->session->delete(self::CURRENT_URL_KEY);
        $this->session->delete(self::PREVIOUS_URL_KEY);
        $this->session->delete(self::INVALID_URL_KEY);
    }

    private function updateUrlHistory(string $currentUri): void
    {
        $currentUrlInSession = $this->session->get(self::CURRENT_URL_KEY);

        if ($currentUrlInSession && $currentUrlInSession !== $currentUri) {
            $this->session->set(self::PREVIOUS_URL_KEY, $currentUrlInSession);
        }

        $this->session->set(self::CURRENT_URL_KEY, $currentUri);
    }

    private function shouldTrackUrl(string $uri, HttpMethod $method): bool
    {
        return !$this->isExcludedUri($uri, $this->previousUrlIgnore) &&
               !$this->isExcludedMethod($method);
    }

    private function isExcludedUri(string $uri, array $excludedPaths): bool
    {
        foreach ($excludedPaths as $path) {
            if (str_starts_with($uri, $path)) {
                return true;
            }
        }
        return false;
    }

    private function isExcludedMethod(HttpMethod $method): bool
    {
        return in_array($method, self::EXCLUDE_METHODS, true);
    }

    private function determineSafeRedirectUrl(?string $currentUrl, ?string $previousUrl, ?string $invalidUrl): string
    {
        $safeUrls = [];

        // Check current URL (skip if it's the invalid one)
        if ($currentUrl && $currentUrl !== $invalidUrl && $this->isSafeRedirectUrl($currentUrl)) {
            $safeUrls[] = $currentUrl;
        }

        // Check previous URL (skip if it's the invalid one)
        if ($previousUrl && $previousUrl !== $invalidUrl && $this->isSafeRedirectUrl($previousUrl)) {
            $safeUrls[] = $previousUrl;
        }

        // Clear invalid URL after use
        if ($invalidUrl) {
            $this->session->delete(self::INVALID_URL_KEY);
        }

        return !empty($safeUrls) ? $safeUrls[0] : '/admin';
    }

    private function isSafeRedirectUrl(string $url): bool
    {
        // Just check against security excluded paths
        return !$this->isExcludedUri($url, $this->safeRedirectExclude);
    }

    private function cleanupSessionUrls(): void
    {
        $currentUrl = $this->session->get(self::CURRENT_URL_KEY);
        $previousUrl = $this->session->get(self::PREVIOUS_URL_KEY);

        // Remove unsafe URLs from history
        if ($currentUrl && !$this->isSafeRedirectUrl($currentUrl)) {
            $this->session->delete(self::CURRENT_URL_KEY);
        }

        if ($previousUrl && !$this->isSafeRedirectUrl($previousUrl)) {
            $this->session->delete(self::PREVIOUS_URL_KEY);
        }

        if ($currentUrl === $previousUrl) {
            $this->session->delete(self::PREVIOUS_URL_KEY);
        }

        $invalidUrl = $this->session->get(self::INVALID_URL_KEY);
        if ($invalidUrl && !$this->isSafeRedirectUrl($invalidUrl)) {
            $this->session->delete(self::INVALID_URL_KEY);
        }
    }
}