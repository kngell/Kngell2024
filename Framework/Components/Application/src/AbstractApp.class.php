<?php

declare(strict_types=1);
abstract class AbstractApp extends Container
{
    use AppGettersAndSetter;

    protected AppConfig $appConfig;
    protected Request $request;
    protected Response $response;
    protected SessionInterface $session;
    protected CookieInterface $cookie;
    protected RooterInterface $rooter;

    public function app(): App
    {
        return self::getInstance();
    }

    public static function diGet(string $class, array $args = []): mixed
    {
        return self::getInstance()->get($class, $args);
    }

    /**
     * @return App
     */
    public static function getInstance() : self
    {
        if (! isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return string
     * @throws BaseLengthException
     */
    public function getGlobalCacheKey(): string
    {
        if ($this->appConfig->getGlobalCacheKey() !== null && strlen($this->appConfig->getGlobalCacheKey()) < 3) {
            throw new BaseLengthException($this->appConfig->getGlobalCacheKey() . ' is invalid this needs to be more than 3 characters long');
        }
        return ($this->appConfig->getGlobalCacheKey() !== null) ? $this->appConfig->getGlobalCacheKey() : 'cache_global';
    }

    /**
     * @return string
     * @throws BaseLengthException
     */
    public function getGlobalSessionKey(): string
    {
        if ($this->appConfig->getGlobalSessionKey() !== null && strlen($this->appConfig->getGlobalSessionKey()) < 3) {
            throw new BaseLengthException($this->appConfig->getGlobalSessionKey() . ' is invalid this needs to be more than 3 characters long');
        }
        return ($this->appConfig->getGlobalSessionKey() !== null) ? $this->appConfig->getGlobalSessionKey() : 'session_global';
    }

    /**
     * Get the value of appConfig.
     *
     * @return AppConfig
     */
    public function getAppConfig(): AppConfig
    {
        return $this->appConfig;
    }

    protected function createAppProperties() : void
    {
        $this->appConfig = AppConfig::getInstance()->setup();
        $this->rooter = $this->get(RooterInterface::class);
        $this->request = $this->get(Request::class);
        $this->response = $this->get(Response::class);
    }

    /**
     * Compare PHP version with the core version ensuring the correct version of
     * PHP and MagmaCore framework is being used at all time in sync.
     *
     * @return void
     */
    protected function phpVersion(): void
    {
        if (version_compare($phpVersion = PHP_VERSION, $coreVersion = $this->appConfig->getConfig()['app']['app_version'], '<')) {
            die(sprintf('You are runninig PHP %s, but the core framework requires at least PHP %s', $phpVersion, $coreVersion));
        }
    }

    /**
     * Load the framework default enviornment configuration. Most configurations
     * can be done from inside the app.yml file.
     *
     * @return void
     */
    protected function loadEnvironment(): void
    {
        $settings = $this->appConfig->getConfig()['settings'];
        ini_set('default_charset', $settings['default_charset']);
        date_default_timezone_set($settings['default_timezone']);
    }

    protected function loadErrorHandlers(): void
    {
        error_reporting($this->appConfig->getErrorHandlerLevel());
        set_error_handler($this->appConfig->getErrorHandling()['error']);
        set_exception_handler($this->appConfig->getErrorHandling()['exception']);
    }

    protected function loadCache(): CacheInterface
    {
        $cache = $this->get(CacheFacade::class)->create($this->appConfig->getCacheIdentifier(), $this->appConfig->getCache());
        if ($this->app()->isCacheGlobal() === true) {
            GlobalManager::set($this->app()->getGlobalCacheKey(), $cache);
        }
        return $cache;
    }

    /**
     * Turn on global caching from public/index.php bootstrap file to make the cache
     * object available globally throughout the application using the GlobalManager object.
     * @return bool
     */
    protected function isCacheGlobal(): bool
    {
        return $this->appConfig->isCacheGlobal();
    }

    protected function loadSession(): Object
    {
        $this->bindParams(SessionEnvironment::class, $this->appConfig->getSession());
        $this->bind(SessionStorageInterface::class, $this->appConfig->getSessionDriver());
        $this->session = $this->get(SessionInterface::class, [$this->appConfig->getSession()['session_name']]);
        if ($this->isSessionGlobal() === true) {
            GlobalManager::set($this->app()->getGlobalSessionKey(), $this->session);
        }
        return $this->session;
    }

    /**
     * Turn on global session from public/index.php bootstrap file to make the session
     * object available globally throughout the application using the GlobalManager object.
     * @return bool
     */
    protected function isSessionGlobal(): bool
    {
        return $this->appConfig->isSessionGlobal();
    }

    protected function loadCookies()
    {
        $this->bindParams(CookieEnvironment::class, $this->appConfig->getCookie());
        return $this->cookie = $this->get(CookieInterface::class);
    }
}
