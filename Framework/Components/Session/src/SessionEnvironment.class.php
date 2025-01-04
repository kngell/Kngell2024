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
     * @return void
     */
    public function __construct(array $sessionConfig = [])
    {
        if (count($sessionConfig) < 0 || ! is_array($sessionConfig)) {
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
    public function getLifetime(): int|bool
    {
        return filter_var($this->getSessionParam('cookie_lifetime'), FILTER_VALIDATE_INT) ?? 120;
        // $lifetime = (isset($this->getConfig()['cookie_lifetime']) ? filter_var($this->getConfig()['cookie_lifetime'], FILTER_VALIDATE_INT) : 120);
        // if ($lifetime) {
        //     return $lifetime;
        // }
    }

    /**
     * Path on the domain where the cookie will work. Use a single slash ('/')
     * for all paths on the domain.
     *
     * @return string
     */
    public function getPath(): string|bool
    {
        return $this->getSessionParam('path') ?? '/';
    }

    /**
     * Cookie domain, for example 'www.php.net'. To make cookies visible on all
     * subdomains then the domain must be prefixed with a dot like '.php.net'.
     *
     * @return string|bool
     */
    public function getDomain(): bool|string
    {
        return $this->getSessionParam('domain') ?? $_SERVER['SERVER_NAME'];
    }

    /**
     * If TRUE cookie will only be sent over secure connections.
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return (bool) $this->getSessionParam('cookie_secure') ?? isset($_SERVER['HTTPS']);
    }

    /**
     * If set to TRUE then PHP will attempt to send the httponly flag when
     * setting the session cookie.
     *
     * @return null|bool
     */
    public function isHttpOnly(): ?bool
    {
        return (bool) $this->getSessionParam('cookie_httponly');
    }

    /**
     * Get the unique session identifier.
     *
     * @return string
     */
    public function getSessionName(): string
    {
        return strval($this->getSessionParam('session_name'));
    }

    public function storagePath() : string
    {
        return strval($this->getSessionParam('save_path'));
    }

    /**
     * PHP session runtime configuration strings.
     *
     * @return array
     */
    public function getSessionRuntimeConfigurations(): array
    {
        return [
            'session.gc_maxlifetime',
            'session.gc_divisor',
            'session.gc_probability',
            'session.cookie_lifetime',
            'session.use_cookies',
            'session.cookie_secure',
            'session.cookie_httponly',
            'session.cookie_samesite',
            'session.use_only_cookies',
            'session.save_path',
        ];
    }

    /**
     * Get the session runtime configuration values from the session environment
     * object. As the array is index with the 'session.' prefix we must handle this
     * by removing the prefix. In order to match the configuration values.
     * Values are fetched using the getConfig() method and simple calling the
     * config value within the square brackets.
     *
     * @param string $option
     * @return mixed
     */
    public function getSessionIniValues(string $sessionKey): mixed
    {
        if ($sessionKey === 'save_path') {
            return strval(ROOT_DIR . DS . $this->getConfig()[$sessionKey]);
        }
        return $this->getConfig()[$sessionKey];
    }

    private function getSessionParam(?string $key = null): mixed
    {
        if ($key !== null && array_key_exists($key, $this->getConfig())) {
            return $this->getConfig()[$key];
        }
        return false;
    }
}