<?php

declare(strict_types=1);

class SessionFactory
{
    /** @return void */
    public function __construct(private SessionEnvironment $sessionEnvironment, private FilesSystemInterface $fileSyst)
    {
    }

    /**
     * Session factory which create the session object and instantiate the chosen
     * session storage which defaults to nativeSessionStorage. This storage object accepts
     * the session environment object as the only argument.
     *
     * @param string $sessionIdentifier
     * @param string $storage
     * @return SessionInterface
     * @throws SessionUnexpectedValueException
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoDefaultValueException
     */
    public function create(
        string $sessionIdentifier,
        string $storage,
    ): SessionInterface {
        $storageObject = new $storage($this->sessionEnvironment, $this->fileSyst);
        if (! $storageObject instanceof SessionStorageInterface) {
            throw new SessionUnexpectedValueException(
                $storage . ' is not a valid session storage object.'
            );
        }
        //return new Session($sessionIdentifier, $storageObject);
        return App::getInstance()->get(SessionInterface::class, [
            'storage' => $storageObject,
            'sessionIdentifier' => $sessionIdentifier,
        ]);
    }
}