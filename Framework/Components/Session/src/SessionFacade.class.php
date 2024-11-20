<?php

declare(strict_types=1);

final class SessionFacade
{
    /** @var string|null - a string which identifies the current session */
    private ?string $sessionIdentifier;

    /** @var string|null - the namespace reference to the session storage type */
    private ?string $storage;

    /**
     * Main session facade class which pipes the properties to the method arguments.
     * @param string|null $sessionIdentifier
     * @param null|string $storage - optional defaults to nativeSessionStorage
     */
    public function __construct(
        string|null $sessionIdentifier = null,
        string|null $storage = null,
        private SessionEnvironment $sessionEnvironment,
        private FilesSystemInterface $fileSyst
    ) {
        /* Defaults are set from the BaseApplication class */
        $this->sessionIdentifier = $sessionIdentifier;
        $this->storage = $storage;
    }

    /**
     * Initialize the session component and return the session object. Also stored the
     * session object within the global manager. So session can be fetch throughout
     * the application by using the GlobalManager::get('session_global') to get
     * the session object.
     *
     * @return object
     * @throws GlobalManager\GlobalManagerException
     */
    public function setSession(): Object
    {
        try {
            return (new SessionFactory($this->sessionEnvironment, $this->fileSyst))->create($this->sessionIdentifier, $this->storage);
        } catch(SessionException $e) {
            throw new SessionException($e->getMessage());
        }
    }
}