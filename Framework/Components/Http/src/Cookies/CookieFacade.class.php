<?php

declare(strict_types=1);

class CookieFacade
{
    /** @var string - the namespace reference to the cookie store type */
    protected CookieStoreInterface $store;

    /**
     * Main cookie facade class which pipes the properties to the method arguments.
     * Which also defines the default cookie store.
     *
     * @param array $cookieEnvironmentArray - expecting a cookie.yaml configuration file
     * @param string $store - optional defaults to nativeCookieStore
     * @return void
     */
    public function __construct(CookieStoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * Create an instance of the cookie factory and inject all the required
     * dependencies ie. the cookie store object and the cookie environment
     * configuration.
     *
     * @return CookieInterface
     */
    public function initialize(): CookieInterface
    {
        return App::diget(CookieFactory::class)->create($this->store);
    }
}
