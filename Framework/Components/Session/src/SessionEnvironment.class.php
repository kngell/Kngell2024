<?php

declare(strict_types=1);

/**
 * SessionEnvironment handles the session configuration from the application
 * which passes in the user define session options. This class also exposes
 * session helper methods for fetching name, path, etc...
 */
class SessionEnvironment
{
    /** @var string - the current stable session version */
    protected const SESSION_VERSION = '1.0.0';

    /** @var array */
    protected array $sessionConfig;

    /**
     * Main class constructor.
     *
     * @param array $sessionConfig
     *
     * @return void
     */
    public function __construct(array $sessionConfig)
    {
        if (empty($sessionConfig) || !is_array($sessionConfig)) {
            throw new LogicException('Session environment has failed to load. Ensure your are passing the correct yaml configuration file to the session facade class object');
        }
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * Returns the complete session configuration array.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->sessionConfig;
    }

    /**
     * The lifetime of the cookie in seconds.
     *
     * @return int
     */
    public function getLifetime(): int
    {
        $lifetime = filter_var($this->getSessionParam('cookie_lifetime'), FILTER_VALIDATE_INT);
        return $lifetime !== false ? $lifetime : 120;
    }

    /**
     * Path on the domain where the cookie will work. Use a single slash ('/')
     * for all paths on the domain.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->getSessionParam('path') ?? '/';
    }

    /**
     * Cookie domain, for example 'www.php.net'. To make cookies visible on all
     * subdomains then the domain must be prefixed with a dot like '.php.net'.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->getSessionParam('domain') ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    }

    public function isSecure(): bool
    {
        $configValue = $this->getSessionParam('cookie_secure');

        // Handle different types of values
        if ($configValue === '1' || $configValue === 1 || $configValue === true) {
            return true;
        }
        if ($configValue === '0' || $configValue === 0 || $configValue === false) {
            return false;
        }

        // Auto-detect from HTTPS
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    public function isHttpOnly(): bool
    {
        $configValue = $this->getSessionParam('cookie_httponly');

        if ($configValue === '1' || $configValue === 1 || $configValue === true) {
            return true;
        }
        if ($configValue === '0' || $configValue === 0 || $configValue === false) {
            return false;
        }

        return true; // Default to true for security
    }

    public function getSameSite(): string
    {
        $sameSite = $this->getSessionParam('cookie_samesite');
        return in_array($sameSite, ['Lax', 'Strict', 'None']) ? $sameSite : 'Lax';
    }

    /**
     * Get the unique session identifier.
     *
     * @return string
     */
    public function getSessionName(): string
    {
        return (string) ($this->getSessionParam('session_name') ?? 'kgl_xsf_session');
    }

    public function storagePath(): string
    {
        return (string) ($this->getSessionParam('save_path') ?? 'storage/sessions');
    }

    /**
     * Get the session save path with ROOT_DIR.
     */
    public function getFullStoragePath(): string
    {
        return ROOT_DIR . DS . $this->storagePath() . DS;
    }

    public function getSessionRuntimeConfigurations(): array
    {
        return [
            'session.gc_maxlifetime',
            'session.gc_divisor',
            'session.gc_probability',
            'session.cookie_lifetime',
            'session.cookie_secure',
            'session.cookie_httponly',
            'session.cookie_samesite',
            'session.use_cookies',
            'session.use_only_cookies',
            'session.use_trans_sid',
            'session.save_path',
        ];
    }

    public function getSessionIniValues(string $sessionKey): mixed
    {
        if ($sessionKey === 'save_path') {
            return $this->getFullStoragePath();
        }

        $value = $this->getSessionParam($sessionKey);

        return match($sessionKey) {
            'cookie_secure' => $this->isSecure() ? '1' : '0',
            'cookie_httponly' => $this->isHttpOnly() ? '1' : '0',
            'cookie_samesite' => $this->getSameSite(),
            'use_cookies' => '1',
            'use_only_cookies' => '1',
            'use_trans_sid' => '0',
            default => $value
        };
    }

    /**
     * Get session driver configuration.
     */
    public function getDriverConfig(?string $driver = null): array
    {
        $driver = $driver ?? $this->getSessionParam('default_driver') ?? 'native_storage';
        $drivers = $this->getSessionParam('drivers') ?? [];

        return $drivers[$driver] ?? $drivers['native_storage'] ?? [];
    }

    /**
     * Check if session should be globalized.
     */
    public function isGlobalized(): bool
    {
        return (bool) ($this->getSessionParam('globalized') ?? false);
    }

    private function getSessionParam(?string $key = null): mixed
    {
        if ($key !== null && array_key_exists($key, $this->sessionConfig)) {
            return $this->sessionConfig[$key];
        }
        return null;
    }
}