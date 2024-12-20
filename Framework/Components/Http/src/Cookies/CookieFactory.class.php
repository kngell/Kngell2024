<?php

declare(strict_types=1);

class CookieFactory
{
    /** @return void */
    public function __construct()
    {
    }

    /**
     * Cookie factory which create the cookie object and instantiate the chosen
     * cookie store object which defaults to nativeCookieStore. This store object accepts
     * the cookie environment object as the only argument.
     *
     * @param string|null $cookieStore
     * @param CookieEnvironment $cookieEnvironment
     * @return CookieInterface
     */
    public function create(CookieStoreInterface $cookieStoreObject): CookieInterface
    {
        if (! $cookieStoreObject instanceof CookieStoreInterface) {
            throw new CookieUnexpectedValueException($cookieStoreObject::class . 'is not a valid cookie store object.');
        }

        return App::getInstance()->get(CookieInterface::class, ['cookieStore' => $cookieStoreObject]);
    }
}