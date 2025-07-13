<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

abstract class AbstractApp extends Container
{
    use AppGettersAndSetter;

    protected AppConfig $appConfig;
    protected Request $request;
    protected Response $response;
    protected SessionInterface $session;
    protected CookieInterface $cookie;
    protected CacheInterface $cache;
    protected RooterInterface $rooter;

    public function app(): App
    {
        return self::getInstance();
    }

    /**
     * Static method for dependency injection - get service from container.
     */
    public static function diGet(string $class, array $args = []): mixed
    {
        return self::getInstance()->resolve($class, $args);
    }

    /**
     * Static method to call methods with dependency injection.
     */
    public static function diCall(callable|array|string $callback, array $parameters = []): mixed
    {
        return self::getInstance()->call($callback, $parameters);
    }

    /**
     * Static method to check if service exists in container.
     */
    public static function diHas(string $class): bool
    {
        return self::getInstance()->has($class);
    }

    /**
     * Static method to bind services to container.
     */
    public static function diBind(string $abstract, mixed $concrete = null, bool $shared = false): ContainerInterface
    {
        return self::getInstance()->bind($abstract, $concrete, $shared);
    }

    /**
     * Static method to bind singletons to container.
     */
    public static function diSingleton(string $abstract, mixed $concrete = null): ContainerInterface
    {
        return self::getInstance()->singleton($abstract, $concrete);
    }

    /**
     * @return App
     */
    public static function getInstance(): self
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

    protected function createAppProperties(): void
    {
        // Use the new resolve method for better performance and error handling
        $this->rooter = $this->resolve(RooterInterface::class);
        $this->request = $this->resolve(Request::class);
        $this->response = $this->resolve(Response::class);

        // Set up application-wide parameters
        $this->setGlobalParameters([
            'app.name' => $this->appConfig->getConfig()['app']['app_name'] ?? 'Application',
            'app.version' => $this->appConfig->getConfig()['app']['app_version'] ?? '1.0.0',
            'app.debug' => $this->appConfig->getConfig()['app']['debug'] ?? false,
            'app.environment' => $this->appConfig->getConfig()['app']['environment'] ?? 'production',
        ]);

        // Create aliases for commonly used services
        $this->alias(Request::class, 'request');
        $this->alias(Response::class, 'response');
        $this->alias(RooterInterface::class, 'router');
        $this->alias(SessionInterface::class, 'session');
        $this->alias(CacheInterface::class, 'cache');
        $this->alias(CookieInterface::class, 'cookie');
    }

    /**
     * Compare PHP version with the core version ensuring the correct version of
     * PHP and K'nGELL framework is being used at all time in sync.
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
        (new Dotenv())->load(ROOT_DIR . DS . '.env');
    }

    protected function loadErrorHandlers(): void
    {
        error_reporting($this->appConfig->getErrorHandlerLevel());
        set_error_handler($this->appConfig->getErrorHandling()['error']);
        set_exception_handler($this->appConfig->getErrorHandling()['exception']);
    }

    protected function loadCache(): CacheInterface
    {
        // Use factory binding for cache creation
        $this->factory(CacheInterface::class, function ($container) {
            $cacheFacade = $container->resolve(CacheFacade::class);
            return $cacheFacade->create(
                $this->appConfig->getCacheIdentifier(),
                $this->appConfig->getCache(),
            );
        });

        $cache = $this->resolve(CacheInterface::class);

        if ($this->app()->isCacheGlobal() === true) {
            GlobalManager::set($this->app()->getGlobalCacheKey(), $cache);
        }

        return $this->cache = $cache;
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

    protected function loadSession(): SessionInterface
    {
        // Set global parameters for session configuration
        $this->setGlobalParameters([
            'sessionConfig' => $this->appConfig->getSession(),
            'sessionIdentifier' => $this->appConfig->getSession()['session_name'],
        ]);

        // Bind session storage without specifying parameter names
        $this->bind(SessionStorageInterface::class, $this->appConfig->getSessionDriver());

        // Resolve Session
        $this->session = $this->resolve(SessionInterface::class);

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
        return $this->cookie = $this->resolve(CookieInterface::class);
    }
}