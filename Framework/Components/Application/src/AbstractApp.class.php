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
    protected array $bootMap = [
        'loadErrorHandlers' => false,
        'loadSession' => false,
        'phpVersion' => false,
        'loadEnvironment' => false,
        'loadCache' => false,
        'loadCookies' => false,
        'createAppProperties' => false,
    ];
    protected array $bootOrder = [
        'loadErrorHandlers',
        'phpVersion',
        'loadEnvironment',
        'loadCache',
        'loadSession',
        'loadCookies',
        'createAppProperties',
    ];
    protected bool $isCli = false;

    public function __construct()
    {
        return parent::__construct();
    }

    public function app(): App
    {
        return self::getInstance();
    }

    /**
     * @throws BaseLengthException
     *
     * @return string
     */
    public function getGlobalCacheKey(): string
    {
        if ($this->appConfig->getGlobalCacheKey() !== null && strlen($this->appConfig->getGlobalCacheKey()) < 3) {
            throw new BaseLengthException($this->appConfig->getGlobalCacheKey() . ' is invalid this needs to be more than 3 characters long');
        }
        return ($this->appConfig->getGlobalCacheKey() !== null) ? $this->appConfig->getGlobalCacheKey() : 'cache_global';
    }

    /**
     * @throws BaseLengthException
     *
     * @return string
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
        // Set up application-wide parameters
        $this->setGlobalParameters([
            'app.name' => $this->appConfig->getConfig()['app']['app_name'] ?? 'Application',
            'app.version' => $this->appConfig->getConfig()['app']['app_version'] ?? '1.0.0',
            'app.debug' => $this->appConfig->getConfig()['app']['debug'] ?? false,
            'app.environment' => $this->appConfig->getConfig()['app']['environment'] ?? 'production',
            'validationConfig' => $this->appConfig->getConfig()['validation'],
            'routes' => $this->appConfig->getRoutes(),
            'defaultRegion' => $this->appConfig->getConfig()['default_region'],
            'previousUrlIgnore' => $this->appConfig->getConfig()['security']['excluded_paths']['previous_url_ignore'],
            'safeRedirectExclude' => $this->appConfig->getConfig()['security']['excluded_paths']['safe_redirect_exclude'],
        ]);

        // Create aliases for commonly used services
        $this->alias(Request::class, 'request');
        $this->alias(Response::class, 'response');
        $this->alias(RooterInterface::class, 'router');
        $this->alias(SessionInterface::class, 'session');
        $this->alias(CacheInterface::class, 'cache');
        $this->alias(CookieInterface::class, 'cookie');

        $this->request = $this->resolve(Request::class);
        $this->response = $this->resolve(Response::class);
        $this->rooter = $this->resolve(RooterInterface::class);
        $this->bootMap[__FUNCTION__] = true;
    }

    protected function createCliProperties(): void
    {
        $this->setGlobalParameters([
            'app.name' => $this->appConfig->getConfig()['app']['app_name'] ?? 'Application',
            'app.version' => $this->appConfig->getConfig()['app']['app_version'] ?? '1.0.0',
            'app.debug' => $this->appConfig->getConfig()['app']['debug'] ?? false,
            'app.environment' => $this->appConfig->getConfig()['app']['environment'] ?? 'production',
        ]);

        // Mark session/cookie as N/A for CLI
        $this->bootMap['loadSession'] = true;
        $this->bootMap['loadCookies'] = true;
        $this->bootMap['createAppProperties'] = true;
    }

    // Fix
    protected function phpVersion(): void
    {
        $minPhpVersion = AppConfig::APP_MIN_VERSION; // '8.2.11'
        if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
            die(sprintf(
                'You are running PHP %s, but the core framework requires at least PHP %s',
                PHP_VERSION,
                $minPhpVersion,
            ));
        }
        $this->bootMap[__FUNCTION__] = true;
    }

    /**
     * Load the framework default environment configuration.
     */
    protected function loadEnvironment(): void
    {
        $settings = $this->appConfig->getConfig()['settings'];
        ini_set('default_charset', $settings['default_charset']);
        date_default_timezone_set($settings['default_timezone']);
        (new Dotenv())->load(ROOT_DIR . DS . '.env');
        $this->bootMap[__FUNCTION__] = true;
    }

    protected function loadErrorHandlers(): void
    {
        error_reporting($this->appConfig->getErrorHandlerLevel());

        $errorHandling = $this->appConfig->getErrorHandling();
        if (!is_callable($errorHandling['error'])) {
            throw new RuntimeException(sprintf(
                'Error handler [%s] is not callable. Check your app.yml error_handler configuration.',
                is_string($errorHandling['error']) ? $errorHandling['error'] : gettype($errorHandling['error']),
            ));
        }

        if (!is_callable($errorHandling['exception'])) {
            throw new RuntimeException(sprintf(
                'Exception handler [%s] is not callable. Check your app.yml error_handler configuration.',
                is_string($errorHandling['exception']) ? $errorHandling['exception'] : gettype($errorHandling['exception']),
            ));
        }
        set_error_handler($errorHandling['error']);
        set_exception_handler($errorHandling['exception']);

        new ErrorHandling();

        $this->bootMap[__FUNCTION__] = true;
    }

    protected function loadCache(): CacheInterface
    {
        $cacheConfig = $this->appConfig->getCache();

        // Create environment configuration
        $envConfig = $this->resolve(CacheEnvironmentConfigurations::class, [
            $this->appConfig->getCacheIdentifier(),
            $cacheConfig,
        ]);

        // // Use factory binding for cache creation
        // $this->factory(CacheInterface::class, function ($app) use ($envConfig): CacheInterface {
        //     $cacheFactory = new CacheFactory($envConfig, new DirectoryManager(), new FileContentManager());
        //     return $cacheFactory->create();
        // });
        // Fix: Use singleton instead of factory
        $this->singleton(CacheInterface::class, function ($app) use ($envConfig): CacheInterface {
            $cacheFactory = new CacheFactory(
                $envConfig,
                new DirectoryManager(),
                new FileContentManager(),
            );
            return $cacheFactory->create();
        });

        $cache = $this->resolve(CacheInterface::class);

        if ($this->app()->isCacheGlobal() === true) {
            GlobalManager::set($this->app()->getGlobalCacheKey(), $cache);
        }
        $this->bootMap[__FUNCTION__] = true;
        return $this->cache = $cache;
    }

    /**
     * Turn on global caching.
     */
    protected function isCacheGlobal(): bool
    {
        return $this->appConfig->isCacheGlobal();
    }

    protected function loadSession(): SessionInterface
    {
        // Only called for HTTP, not CLI
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

        $this->bootMap[__FUNCTION__] = true;
        return $this->session;
    }

    /**
     * Turn on global session.
     */
    protected function isSessionGlobal(): bool
    {
        return $this->appConfig->isSessionGlobal();
    }

    protected function loadCookies()
    {
        // Only called for HTTP, not CLI
        $this->bindParams(CookieEnvironment::class, $this->appConfig->getCookie());
        $this->bootMap[__FUNCTION__] = true;
        return $this->cookie = $this->resolve(CookieInterface::class);
    }

    /**
     * Static method for dependency injection - get service from container.
     */
    public static function diGet(string $class, array $args = []): mixed
    {
        $app = self::getInstance();
        if (!$app->isFullyBooted()) {
            $app->reBoot();
        }
        return $app->resolve($class, $args);
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
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }
}