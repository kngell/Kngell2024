<?php

declare(strict_types=1);

class NativeSessionStorage extends AbstractSessionStorage implements SessionStorageInterface
{
    public function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function setArraySession(string $key, mixed $value): void
    {
        $_SESSION[$key][] = $value;
    }

    public function getSession(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function deleteSession(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function invalidateSession(): void
    {
        $this->destroy();
    }

    public function flushSession(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $value;
    }

    public function SessionExists(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}