<?php

declare(strict_types=1);

class HeaderManager
{
    private const DEV_HOSTS = ['localhost:3003', '127.0.0.1:3003', 'localhost', '127.0.0.1'];
    private const PROD_SECURITY_HEADERS = [
        'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    public function __construct(
        private HeaderMap $headers,
        private string $host,
        private HttpMethod $method
    ) {
    }

    public function applySecurityHeaders(bool $isDev = false): void
    {
        if ($isDev) {
            $this->applyDevHeaders();
        } else {
            $this->applyProdHeaders();
        }
    }

    public function applyCorsHeaders(): void
    {
        if ($this->isPreflightRequest()) {
            $this->headers->add('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $this->headers->add('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $this->headers->add('Access-Control-Max-Age', '3600');
        }

        $this->headers->add('Access-Control-Allow-Origin', 'https://localhost:3003');
        $this->headers->add('Access-Control-Allow-Credentials', 'true');
    }

    public function getCspHeader(): string
    {
        return $this->isDevEnvironment()
            ? $this->getDevCsp()
            : $this->getProdCsp();
    }

    public function isDevEnvironment(): bool
    {
        return in_array($this->host, self::DEV_HOSTS, true);
    }

    public function applyAdminSecurityHeaders(): void
    {
        $this->headers->add('Content-Security-Policy', $this->getAdminCsp());
        $this->headers->add('X-Frame-Options', 'DENY'); // Stricter than SAMEORIGIN
    }

    public function applyDevHeaders(): void
    {
        // For Webpack Dev Server internal requests
        if ($this->isWebpackInternalRequest()) {
            $this->headers->add('Access-Control-Allow-Origin', 'https://localhost:3003');
            $this->headers->add('Access-Control-Allow-Credentials', 'true');
            return;
        }

        // Normal CORS headers
        $this->applyCorsHeaders();
        $this->headers->add('Content-Security-Policy', $this->getDevCsp());
    }

    public function applyStaticAssetHeaders(string $path): void
    {
        if ($this->isStaticAsset($path)) {
            $this->headers->add('Cache-Control', 'public, max-age=604800');
            $this->headers->remove('Content-Security-Policy');
        }
    }

    public function applyMaintenanceHeaders(): void
    {
        $this->headers->add('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->headers->add('Pragma', 'no-cache');
    }

    public function applyFileDownloadHeaders(): void
    {
        $this->headers->add('X-Content-Type-Options', 'nosniff');
        $this->headers->add('Cache-Control', 'no-store');
    }
    // src/Services/HeaderManager.php

    public static function isStaticAsset(string $path): bool
    {
        $staticExtensions = [
            'css', 'js', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'ico',
            'woff', 'woff2', 'ttf', 'eot', 'mp4', 'webm', 'pdf',
        ];

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // Check both extension and common static paths
        return $extension && in_array(strtolower($extension), $staticExtensions, true) ||
               preg_match('/\.(?:' . implode('|', $staticExtensions) . ')(?:\?.*)?$/i', $path);
    }

    private function isWebpackInternalRequest(): bool
    {
        $webpackPaths = ['/sockjs-node', '/__webpack_dev_server__'];
        return in_array(parse_url($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME), $webpackPaths);
    }

    private function applyProdHeaders(): void
    {
        foreach (self::PROD_SECURITY_HEADERS as $name => $value) {
            $this->headers->add($name, $value);
        }
        $this->headers->add('Content-Security-Policy', $this->getProdCsp());
    }

    private function getDevCsp(): string
    {
        $cspPath = ROOT_DIR . '/csp.dev.json';
        if (file_exists($cspPath)) {
            $cspJson = json_decode(file_get_contents($cspPath), true);
            if (is_array($cspJson)) {
                $parts = [];
                foreach ($cspJson as $k => $v) {
                    $parts[] = $k . ' ' . $v;
                }
                return implode('; ', $parts);
            }
        }
        // fallback to hardcoded if file missing or invalid
        return implode('; ', [
            "default-src 'self' data: https://localhost https://localhost:3003",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://localhost https://localhost:3003",
            "style-src 'self' 'unsafe-inline' https://localhost https://localhost:3003",
            "img-src 'self' data: blob: https://localhost https://localhost:3003",
            "font-src 'self' data: https://localhost https://localhost:3003",
            "connect-src 'self' ws://localhost:3003 wss://localhost:3003 ws://127.0.0.1:3003 wss://127.0.0.1:3003 https://localhost https://localhost:3003",
            "frame-src 'self' https://localhost https://localhost:3003",
            "worker-src 'self' blob: https://localhost https://localhost:3003",
        ]);
    }

    private function getProdCsp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-src 'self'",
        ]);
    }

    private function isPreflightRequest(): bool
    {
        return $this->method === HttpMethod::OPTIONS;
    }

    private function getAdminCsp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'", // Keep inline for CMS editors
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'none'", // Explicitly prevent framing
        ]);
    }
}