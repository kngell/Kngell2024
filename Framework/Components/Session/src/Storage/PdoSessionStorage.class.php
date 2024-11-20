<?php

declare(strict_types=1);

class PdoSessionStorage extends AbstractSessionStorage
{
    /**
     * Main class constructor.
     *
     * @param object $sessionEnvironment
     */
    public function __construct(Object $sessionEnvironment)
    {
        parent::__construct($sessionEnvironment);
    }

    public function setSession(string $key, mixed $value): void
    {
        // TODO: Implement setSession() method.
    }

    public function setArraySession(string $key, mixed $value): void
    {
        // TODO: Implement setArraySession() method.
    }

    public function getSession(string $key, mixed $default = null)
    {
        // TODO: Implement getSession() method.
    }

    public function deleteSession(string $key): void
    {
        // TODO: Implement deleteSession() method.
    }

    public function invalidateSession(): void
    {
        // TODO: Implement invalidate() method.
    }

    public function flushSession(string $key, mixed $default = null)
    {
        // TODO: Implement flush() method.
    }

    public function hasSession(string $key): bool
    {
        // TODO: Implement hasSession() method.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function SessionExists(string $key) :bool
    {
        // TODO: Implement hasSession() method.
        return false;
    }
}