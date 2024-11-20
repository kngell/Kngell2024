<?php

declare(strict_types=1);

class Session implements SessionInterface
{
    /** @var string */
    protected const SESSION_PATTERN = '/^[a-zA-Z0-9_\.]{1,64}$/';

    /** @var SessionStorageInterface|null */
    protected ?SessionStorageInterface $storage;

    /** @var string */
    protected string $sessionIdentifier;

    /**
     * Class constructor.
     *
     * @param string $sessionIdentifier
     * @param SessionStorageInterface|null $storage
     */
    public function __construct(string $sessionIdentifier, ?SessionStorageInterface $storage = null)
    {
        if ($this->isSessionKeyValid($sessionIdentifier) === false) {
            throw new SessionInvalidArgumentException($sessionIdentifier . ' is not a valid session name');
        }

        $this->sessionIdentifier = $sessionIdentifier;
        $this->storage = $storage;
    }

    /**
     * Return the storage object.
     * @return SessionStorageInterface|null
     */
    public function getStorage(): ?SessionStorageInterface
    {
        return $this->storage;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws SessionException
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureSessionKeyIsValid($key);
        try {
            $this->storage->SetSession($key, $value);
        } catch (Throwable $throwable) {
            throw new SessionException('An exception was thrown in retrieving the key from the session storage. ' . $throwable);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws SessionException
     */
    public function setArray(string $key, mixed $value): void
    {
        $this->ensureSessionKeyIsValid($key);
        try {
            $this->storage->setArraySession($key, $value);
        } catch (Throwable|SessionException $throwable) {
            throw new SessionException('An exception was thrown in retrieving the key from the session storage. ' . $throwable);
        }
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return void
     * @throws SessionException|Throwable
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            return $this->storage->getSession($key, $default);
        } catch (Throwable $throwable) {
            throw new SessionException('An exception was thrown in retrieving the key from the session storage. ' . $throwable);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws SessionException|Throwable
     */
    public function delete(string $key): bool
    {
        $this->ensureSessionKeyIsValid($key);
        try {
            $this->storage->deleteSession($key);
        } catch (Throwable $throwable) {
            throw $throwable;
        }
        return true;
    }

    /**
     * Invalidate Session
     * =====================================================================.
     * @return void
     */
    public function invalidate(): void
    {
        $this->storage->invalidateSession();
    }

    /**
     * Flush the session
     * =====================================================================.
     * @param string $key
     * @param [type] $value
     * @return void
     */
    public function flush(string $key, $value = null) : void
    {
        $this->ensureSessionKeyIsValid($key);
        try {
            $this->storage->flushSession($key, $value);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws SessionInvalidArgumentException
     */
    public function exists(string $key): bool
    {
        $this->ensureSessionKeyIsValid($key);
        return $this->storage->SessionExists($key);
    }

    /**
     * Get User Agent client.
     *
     * @return string
     */
    public static function uagent_no_version() : string
    {
        $uagent = $_SERVER['HTTP_USER_AGENT'];
        $regx = '/\/[a-zA-z0-9.]+/';
        $newString = preg_replace($regx, '', $uagent);

        return $newString;
    }

    /**
     * Checks whether our session key is valid according the defined regular expression.
     *
     * @param string $key
     * @return bool
     */
    protected function isSessionKeyValid(string $key): bool
    {
        return preg_match(self::SESSION_PATTERN, $key) === 1;
    }

    /**
     * Checks whether we have session key.
     *
     * @param string $key
     * @return void
     */
    protected function ensureSessionKeyIsvalid(string $key): void
    {
        if ($this->isSessionKeyValid($key) === false) {
            throw new SessionInvalidArgumentException($key . ' is not a valid session key');
        }
    }
}