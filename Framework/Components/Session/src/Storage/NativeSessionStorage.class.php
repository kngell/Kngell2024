<?php

declare(strict_types=1);

class NativeSessionStorage extends AbstractSessionStorage implements SessionStorageInterface
{
    public function __construct(SessionEnvironment $sessionEnvironment, SuperGlobalsInterface $globals)
    {
        parent::__construct($sessionEnvironment, $globals);
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     * @param mixed$value
     * @return void
     */
    public function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setArraySession(string $key, mixed $value): void
    {
        $_SESSION[$key][] = $value;
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSession(string $key, mixed $default = null): mixed
    {
        if ($this->hasSession($key)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     * @return void
     */
    public function deleteSession(string $key): void
    {
        if ($this->hasSession($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * @inheritdoc
     *
     * @return void
     */
    public function invalidateSession(): void
    {
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie($this->getSessionName(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        // session_gc();
        session_unset();
        session_destroy();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function flushSession(string $key, mixed $default = null): mixed
    {
        if ($this->hasSession($key)) {
            $value = $_SESSION[$key];
            $this->deleteSession($key);
            return $value;
        }
        return $default;
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     * @return bool
     */
    public function hasSession(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @inheritDoc
     */
    public function SessionExists(string $key) :bool
    {
        return isset($_SESSION[$key]);
    }
}