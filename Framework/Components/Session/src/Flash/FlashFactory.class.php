<?php

declare(strict_types=1);

class FlashFactory
{
    /** @return void */
    public function __construct()
    {
    }

    /**
     * Session factory which create the session object and instantiate the chosen
     * session storage which defaults to nativeSessionStorage. This storage object accepts
     * the session environment object as the only argument.
     *
     * @param SessionInterface|null $session
     * @param string|null $flashKey
     * @return FlashInterface
     */
    public function create(?SessionInterface $session = null, ?string $flashKey = null) : FlashInterface
    {
        if (!$session instanceof SessionInterface) {
            throw new SessionUnexpectedValueException('Object does not implement session interface.');
        }
        return new Flash($session, $flashKey);
    }
}