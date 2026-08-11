<?php

declare(strict_types=1);

abstract class AbstractSessionStorage
{
    use SessionTrait;

    private bool $sessionStarted = false;

    public function __construct(
        protected SessionEnvironment $sessionEnvironment,
        private SuperGlobalsInterface $globals,
    ) {
        $this->initializeSession();
    }

    /**
     * Regenerate session ID (for login, privilege changes).
     */
    public function regenerate(): bool
    {
        if ($this->sessionStarted) {
            return session_regenerate_id(true);
        }
        return false;
    }

    /**
     * Destroy session (for logout).
     */
    public function destroy(): bool
    {
        if ($this->sessionStarted) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    $this->getSessionName(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly'],
                );
            }
            return session_destroy();
        }
        return false;
    }

    public function save(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    /**
     * Set the name of the session.
     *
     * @param string $sessionName
     *
     * @return void
     */
    public function setSessionName(string $sessionName): void
    {
        session_name($sessionName);
    }

    public function getSessionName(): string
    {
        return session_name();
    }

    public function getSessionId(): string
    {
        return session_id();
    }

    public function isSessionStarted(): bool
    {
        return $this->sessionStarted;
    }

    // In AbstractSessionStorage

    private function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (headers_sent()) {
            return;
        }

        // ✅ Set session configuration BEFORE starting session
        $this->configureSession();

        // ✅ Now start session
        session_start();
    }

    private function cookiesParams(): array
    {
        $params = [
            'lifetime' => $this->sessionEnvironment->getLifetime(),
            'path' => $this->sessionEnvironment->getPath(),
            'domain' => $this->sessionEnvironment->getDomain(),
            'secure' => $this->sessionEnvironment->isSecure(),
            'httponly' => $this->sessionEnvironment->isHttpOnly(),
        ];

        // Add SameSite parameter if supported (PHP 7.3+)
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            $params['samesite'] = $this->sessionEnvironment->getSameSite();
        }

        return $params;
    }

    private function configureSession(): void
    {
        // ✅ Set session name first
        session_name($this->sessionEnvironment->getSessionName());

        // ✅ Set session save path BEFORE session_start()
        $savePath = $this->sessionEnvironment->getFullStoragePath();
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        if (is_writable($savePath)) {
            session_save_path($savePath);
        }

        // ✅ Set cookie parameters BEFORE session_start()
        $cookieParams = $this->cookiesParams();
        session_set_cookie_params($cookieParams);

        // ✅ Set other session INI settings BEFORE session_start()
        foreach ($this->sessionEnvironment->getSessionRuntimeConfigurations() as $option) {
            $sessionKey = str_replace('session.', '', $option);
            $value = $this->sessionEnvironment->getSessionIniValues($sessionKey);
            if ($value !== null) {
                $result = ini_set($option, (string) $value);
            }
        }
    }
}